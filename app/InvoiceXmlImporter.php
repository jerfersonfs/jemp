<?php

namespace App;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;

class InvoiceXmlImporter
{
    public function __construct(private readonly StockLedger $stockLedger) {}

    public function preview(string $contents): array
    {
        $document = $this->parse($contents);
        $this->assertAvailable($document);
        $mappings = DB::table('product_xml_mappings')->where('issuer_document', $document['issuer_document'])->get();
        $products = Product::query()->with('inventory')->orderBy('product_name')->get();
        $customers = Customer::query()->orderBy('customer_name')->get();

        return [
            'invoice' => collect($document)->except('items')->all(),
            'items' => array_map(function (array $item) use ($mappings): array {
                $mapping = $mappings->first(fn ($mapping): bool => $mapping->external_code === $item['external_code']);

                return $item + ['product_id' => $mapping?->product_id];
            }, $document['items']),
            'customer_id' => $customers->first(fn (Customer $customer): bool => $this->digits($customer->customer_document) === $document['recipient_document'])?->customer_id,
            'customers' => $customers->map(fn (Customer $customer): array => [
                'id' => $customer->customer_id, 'name' => $customer->customer_name,
                'document' => $customer->customer_document,
            ])->all(),
            'products' => $products->map(fn (Product $product): array => [
                'id' => $product->product_id, 'sku' => $product->sku, 'name' => $product->product_name,
                'inventory' => $product->inventory->map(fn ($stock): array => [
                    'warehouse_id' => $stock->warehouse_id, 'condition' => $stock->product_condition,
                    'balance' => (int) $stock->theoretical_balance,
                ])->all(),
            ])->all(),
        ];
    }

    public function import(string $contents, array $data, int $userId): Invoice
    {
        $document = $this->parse($contents);
        $customer = Customer::query()->findOrFail($data['customer_id']);
        $this->ensure($this->digits($customer->customer_document) === $document['recipient_document'], 'O CPF/CNPJ do cliente selecionado deve ser o destinatário do XML.', 'customer_id');
        $this->ensure($data['due_date'] >= $document['issue_date'], 'O vencimento não pode ser anterior à emissão.', 'due_date');
        $choices = collect($data['items'])->keyBy('line_number');
        $this->ensure($choices->count() === count($document['items']), 'Associe todos os itens do XML exatamente uma vez.', 'items');
        $byExternalCode = [];
        $quantities = [];
        foreach ($document['items'] as &$item) {
            $choice = $choices->get($item['line_number']);
            $this->ensure($choice !== null, 'A associação contém linhas diferentes das linhas do XML.', 'items');
            $item['product_id'] = (int) $choice['product_id'];
            $codeKey = 'code:'.$item['external_code'];
            $this->ensure(! isset($byExternalCode[$codeKey]) || $byExternalCode[$codeKey]['product_id'] === $item['product_id'], 'Um mesmo código do emitente deve apontar para o mesmo produto em todas as linhas.', 'items');
            $byExternalCode[$codeKey] = $item;
            $quantities[$item['product_id']] = ($quantities[$item['product_id']] ?? 0) + $item['quantity'];
        }
        unset($item);
        ksort($quantities, SORT_NUMERIC);

        return DB::transaction(function () use ($document, $data, $userId, $quantities, $byExternalCode): Invoice {
            $this->assertAvailable($document);
            foreach ($quantities as $productId => $quantity) {
                $this->stockLedger->change((int) $productId, (int) $data['warehouse_id'], $data['product_condition'], -$quantity);
            }

            $invoice = Invoice::query()->create([
                'issuer_document' => $document['issuer_document'], 'access_key' => $document['access_key'],
                'invoice_number' => $document['number'], 'invoice_serie' => $document['series'],
                'customer_id' => $data['customer_id'], 'issue_date' => $document['issue_date'],
                'due_date' => $data['due_date'], 'payment_method' => $data['payment_method'],
                'payment_terms_days' => $data['payment_terms_days'], 'payment_status' => $data['payment_status'],
                'payment_date' => $data['payment_status'] === 'Paga' ? $data['payment_date'] : null,
                'amount' => $document['amount'], 'stock_posted_at' => now(),
            ]);

            foreach ($document['items'] as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'], 'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'], 'xml_line_number' => $item['line_number'],
                    'external_code' => $item['external_code'], 'external_description' => $item['description'],
                ]);
                DB::table('stock_movements')->insert([
                    'origin_warehouse_id' => $data['warehouse_id'], 'dest_warehouse_id' => null,
                    'product_id' => $item['product_id'], 'product_condition' => $data['product_condition'],
                    'user_id' => $userId, 'invoice_id' => $invoice->invoice_id,
                    'quantity' => $item['quantity'], 'movement_type' => 'Saída', 'is_fiscal' => true,
                    'movement_justification' => 'Venda por XML NF-e '.$document['number'].' / '.$document['series'],
                    'movement_date' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            foreach ($byExternalCode as $item) {
                $query = DB::table('product_xml_mappings')->where('issuer_document', $document['issuer_document'])
                    ->where('external_code', $item['external_code']);
                $existing = (clone $query)->lockForUpdate()->first();
                if ($existing !== null) {
                    $query->update(['product_id' => $item['product_id'], 'updated_at' => now()]);
                } else {
                    DB::table('product_xml_mappings')->insert([
                        'issuer_document' => $document['issuer_document'], 'external_code' => $item['external_code'],
                        'product_id' => $item['product_id'], 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }

            return $invoice;
        }, 3);
    }

    public function parse(string $contents): array
    {
        $this->ensure(strlen($contents) <= 2097152 && ! preg_match('/<!\s*(DOCTYPE|ENTITY)/i', $contents), 'O XML não pode conter DTD ou entidades externas e deve ter até 2 MB.');
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($contents, SimpleXMLElement::class, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $this->ensure($xml !== false, 'Arquivo XML inválido.');
        $nodes = $xml->xpath('/*[local-name()="nfeProc"]/*[local-name()="NFe"]/*[local-name()="infNFe"] | /*[local-name()="NFe"]/*[local-name()="infNFe"]');
        $this->ensure(count($nodes ?: []) === 1, 'Envie uma única NF-e, no formato NFe ou nfeProc.');
        $node = $nodes[0];
        $this->ensure((dom_import_simplexml($node)->namespaceURI ?? '') === 'http://www.portalfiscal.inf.br/nfe', 'O namespace do documento não corresponde ao leiaute da NF-e.');
        $info = $node->children('http://www.portalfiscal.inf.br/nfe');
        $this->ensure((string) $info->ide->mod === '55' && (string) $info->ide->tpNF === '1', 'Esta importação aceita NF-e modelo 55 de vendas emitidas (saída).');
        $protocol = $xml->xpath('//*[local-name()="infProt"]/*[local-name()="cStat"]');
        $this->ensure(! $protocol || (string) $protocol[0] === '100', 'O protocolo do XML não indica autorização da NF-e.');
        $key = preg_replace('/^NFe/', '', (string) $node->attributes()['Id']);
        $this->ensure((bool) preg_match('/^\d{44}$/', $key), 'A NF-e precisa de uma chave de acesso de 44 dígitos.');
        $sum = 0;
        for ($position = 42, $weight = 2; $position >= 0; $position--, $weight = $weight === 9 ? 2 : $weight + 1) {
            $sum += (int) $key[$position] * $weight;
        }
        $digit = 11 - ($sum % 11);
        $this->ensure((int) $key[43] === ($digit >= 10 ? 0 : $digit), 'O dígito da chave de acesso do XML é inválido.');
        $issuer = $this->digits((string) $info->emit->CNPJ);
        $recipient = $this->digits((string) ($info->dest->CNPJ ?: $info->dest->CPF));
        $this->ensure(strlen($issuer) === 14 && in_array(strlen($recipient), [11, 14], true), 'Informe um XML com CNPJ do emitente e CPF/CNPJ do destinatário.');
        $number = $this->positiveInteger((string) $info->ide->nNF, 'Número da nota');
        $series = $this->nonnegativeInteger((string) $info->ide->serie, 'Série');
        $issueDate = substr((string) $info->ide->dhEmi, 0, 10);
        $this->ensure((bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $issueDate) && checkdate((int) substr($issueDate, 5, 2), (int) substr($issueDate, 8, 2), (int) substr($issueDate, 0, 4)), 'A data de emissão do XML é inválida.');
        $this->ensure(substr($key, 6, 14) === $issuer && substr($key, 20, 2) === '55'
            && (int) substr($key, 22, 3) === $series && (int) substr($key, 25, 9) === $number
            && substr($key, 2, 4) === substr($issueDate, 2, 2).substr($issueDate, 5, 2), 'Os dados da nota não correspondem à chave de acesso.');

        $items = [];
        $lines = [];
        $totalCents = 0;
        foreach ($info->det as $detail) {
            $line = $this->positiveInteger((string) $detail->attributes()['nItem'], 'Número do item');
            $this->ensure($line <= 990 && ! isset($lines[$line]), 'O XML contém números de itens inválidos ou repetidos.');
            $lines[$line] = true;
            $code = trim((string) $detail->prod->cProd);
            $description = trim((string) $detail->prod->xProd);
            $this->ensure($code !== '' && mb_strlen($code) <= 60 && $description !== '' && mb_strlen($description) <= 120, "Código ou descrição inválidos no item {$line}.");
            $quantity = $this->positiveInteger((string) $detail->prod->qCom, "Quantidade do item {$line}");
            $unit = mb_strtoupper(trim((string) $detail->prod->uCom));
            $this->ensure(in_array($unit, ['UN', 'UND', 'UNID', 'UNIDADE', 'UNIDADES', 'PC', 'PÇ', 'PECA', 'PEÇA'], true), "A unidade {$unit} do item {$line} exige conversão de estoque, que ainda não é suportada. Use itens contados em unidades ou peças.");
            $priceCents = $this->cents((string) $detail->prod->vUnCom);
            $this->ensure($priceCents === 0 || $quantity <= intdiv(9999999999, $priceCents), 'O total excede a capacidade monetária atual do sistema.');
            $subtotal = $quantity * $priceCents;
            $this->ensure($this->cents((string) $detail->prod->vProd) === $subtotal, "O subtotal do item {$line} não corresponde a quantidade × preço unitário.");
            $totalCents += $subtotal;
            $this->ensure($totalCents <= 9999999999, 'O total excede a capacidade monetária atual do sistema.');
            $items[] = [
                'line_number' => $line, 'external_code' => $code, 'description' => $description,
                'quantity' => $quantity, 'unit_price' => $this->money($priceCents), 'subtotal' => $this->money($subtotal),
            ];
        }
        $this->ensure(count($items) >= 1 && count($items) <= 990, 'A nota precisa conter de 1 a 990 itens.');
        $this->ensure($this->cents((string) $info->total->ICMSTot->vNF) === $totalCents, 'O total fiscal diverge da soma dos itens. Descontos, frete e outros componentes que alterem esse total ainda não são suportados.');
        $paymentCodes = ['01' => 'Dinheiro', '02' => 'Cheque', '03' => 'Cartão de crédito', '04' => 'Cartão de débito', '15' => 'Boleto', '17' => 'PIX', '90' => 'Sem pagamento'];

        return [
            'issuer_document' => $issuer, 'issuer_name' => trim((string) $info->emit->xNome),
            'recipient_document' => $recipient, 'recipient_name' => trim((string) $info->dest->xNome),
            'access_key' => $key, 'number' => $number, 'series' => $series, 'issue_date' => $issueDate,
            'due_date' => (string) $info->cobr->dup->dVenc,
            'payment_method' => $paymentCodes[(string) $info->pag->detPag->tPag] ?? '',
            'amount' => $this->money($totalCents), 'items' => $items,
        ];
    }

    private function assertAvailable(array $document): void
    {
        if (Invoice::query()->where('access_key', $document['access_key'])->exists()) {
            abort(409, 'Esta NF-e já foi importada. Nenhuma baixa adicional foi feita.');
        }
        $query = Invoice::query()->where('invoice_number', $document['number'])->where('invoice_serie', $document['series']);
        if ((clone $query)->where('issuer_document', '')->exists()) {
            abort(409, 'Existe uma nota com este número e série sem emitente informado. Complete o CNPJ do emitente no cadastro existente antes de importar.');
        }
        if ($query->where('issuer_document', $document['issuer_document'])->exists()) {
            abort(409, 'Já existe uma nota deste emitente com o mesmo número e série.');
        }
    }

    private function positiveInteger(string $value, string $label): int
    {
        $number = $this->nonnegativeInteger($value, $label);
        $this->ensure($number > 0, "{$label} deve ser maior que zero.");

        return $number;
    }

    private function nonnegativeInteger(string $value, string $label): int
    {
        $this->ensure((bool) preg_match('/^\d+(?:\.0+)?$/', $value) && (float) $value <= 2147483647, "{$label}: somente valores inteiros dentro do limite atual são suportados.");

        return (int) $value;
    }

    private function cents(string $value): int
    {
        $this->ensure((bool) preg_match('/^\d+(?:\.\d+)?$/', $value), 'O XML contém valor monetário inválido.');
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $fraction = rtrim($fraction, '0');
        $this->ensure(strlen(ltrim($whole, '0')) <= 8 && strlen($fraction) <= 2, 'O XML exige uma precisão monetária não suportada pelo cadastro atual (duas casas decimais).');

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    private function money(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    private function ensure(bool $condition, string $message, string $field = 'xml_file'): void
    {
        if (! $condition) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }
}
