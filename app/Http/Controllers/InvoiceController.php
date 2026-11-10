<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Inventory;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Lista todas as notas fiscais
     */
    public function index()
    {
        $invoices = Invoice::with('items')->get();

        return InvoiceResource::collection($invoices);
    }

    /**
     * Guarda uma nova nota fiscal
     */
    public function store(StoreInvoiceRequest $request)
    {
        $dados = $request->validated();
        $invoice = DB::transaction(function () use ($dados): Invoice {
            return Invoice::query()->create(array_merge($dados, [
                'issue_date' => $dados['issue_date'] ?? now()->toDateString(),
                'payment_status' => $dados['payment_status'] ?? 'A vencer',
                'amount' => 0,
            ]));
        });

        return response()->json(new InvoiceResource($invoice->load('items')), 201);
    }

    public function importXml(Request $request)
    {
        $request->validate([
            'xml_file' => ['required', 'file', 'mimes:xml', 'max:2048'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,warehouse_id'],
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $xmlString = file_get_contents($request->file('xml_file')->getRealPath());
                $xml = simplexml_load_string($xmlString);

                if ($xml === false || ! isset($xml->NFe->infNFe)) {
                    throw new \Exception('Ficheiro XML inválido ou não é uma NFe brasileira padrão.');
                }

                $infNFe = $xml->NFe->infNFe;
                $warehouseId = $request->input('warehouse_id');

                $invoice = Invoice::create([
                    'invoice_number' => (int) $infNFe->ide->nNF,
                    'invoice_serie' => (int) $infNFe->ide->serie,
                    'customer_id' => 1, // ID temporário para passar na chave estrangeira (fk_invoices_customer)
                    'issue_date' => date('Y-m-d', strtotime((string) $infNFe->ide->dhEmi)),
                    'due_date' => date('Y-m-d'), // Obrigatório na migration
                    'payment_method' => 'Dinheiro', // Obrigatório (varchar 50)
                    'payment_terms_days' => 0, // Obrigatório (inteiro)
                    'payment_status' => 'Paga', // TEM de ser exatamente 'Paga' para bater com o ENUM da migration
                    'payment_date' => date('Y-m-d'), // Opcional, mas faz sentido se está 'Paga'
                    'amount' => 0,
                ]);

                $calculatedAmount = 0.0;
                foreach ($infNFe->det as $itemXML) {
                    $productId = (string) $itemXML->prod->cProd;
                    $quantity = (float) $itemXML->prod->qCom;
                    $unitPrice = (float) $itemXML->prod->vUnCom;

                    if ($quantity < 1 || floor($quantity) !== $quantity || $unitPrice < 0) {
                        throw new \Exception('O XML contém item com quantidade fracionada ou preço inválido para o cadastro atual.');
                    }

                    $invoice->items()->create([
                        'product_id' => $productId,
                        'quantity' => (int) $quantity,
                        'unit_price' => $unitPrice,
                    ]);
                    $calculatedAmount += $quantity * $unitPrice;

                    $inventory = Inventory::firstOrCreate(
                        [
                            'warehouse_id' => $warehouseId,
                            'product_id' => $productId,
                            'product_condition' => 'Novo',
                        ],
                        ['theoretical_balance' => 0]
                    );

                    $inventory->increment('theoretical_balance', $quantity);
                }

                $invoice->forceFill(['amount' => $calculatedAmount])->save();

                return $invoice;
            });

            return response()->json([
                'message' => 'NFe importada e estoque atualizado com sucesso!',
                'data' => clone new InvoiceResource($result),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Falha ao importar a Nota Fiscal.',
                'details' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mostra os detalhes de uma nota fiscal específica
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('items');
        return new InvoiceResource($invoice);
    }

    /**
     * Atualiza uma nota fiscal existente
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $dados = $request->validated();

        $invoice->update($dados);

        return new InvoiceResource($invoice->fresh());
    }

    /**
     * Remove uma nota fiscal
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json([
            'message' => 'Nota fiscal excluída com sucesso.',
        ], 200);
    }
}
