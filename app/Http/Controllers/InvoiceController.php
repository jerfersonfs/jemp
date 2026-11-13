<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Invoice::refreshOverdueStatuses();

        return InvoiceResource::collection(Invoice::with('items')->get());
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $invoice = DB::transaction(fn (): Invoice => Invoice::query()->create(array_merge($data, [
            'issue_date' => $data['issue_date'] ?? now()->toDateString(),
            'payment_status' => $data['payment_status'] ?? 'A vencer', 'amount' => 0,
        ])));

        return response()->json(new InvoiceResource($invoice->load('items')), 201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        Invoice::refreshOverdueStatuses();

        return new InvoiceResource($invoice->refresh()->load('items'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        $invoice = DB::transaction(function () use ($request, $invoice): Invoice {
            $locked = Invoice::query()->whereKey($invoice->invoice_id)->lockForUpdate()->firstOrFail();
            $data = $request->validated();
            if ($locked->access_key !== null) {
                if (! in_array($data['payment_status'] ?? null, ['A vencer', 'Paga'], true)
                    || array_diff(array_keys($data), ['payment_status']) !== []) {
                    throw ValidationException::withMessages(['payment_status' => 'Altere apenas se a nota importada está paga ou não paga.']);
                }
                $locked->changePaymentState($data['payment_status'] === 'Paga');

                return $locked->fresh();
            }
            $number = $data['invoice_number'] ?? $locked->invoice_number;
            $series = $data['invoice_serie'] ?? $locked->invoice_serie;
            $issuer = $data['issuer_document'] ?? $locked->issuer_document;
            if (Invoice::query()->where('invoice_number', $number)->where('invoice_serie', $series)
                ->where('issuer_document', $issuer)->where('invoice_id', '!=', $locked->invoice_id)->exists()) {
                throw ValidationException::withMessages(['invoice_number' => 'Já existe uma nota deste emitente com o mesmo número e série.']);
            }
            $locked->update($data);

            return $locked->fresh();
        });

        return new InvoiceResource($invoice);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        DB::transaction(function () use ($invoice): void {
            $locked = Invoice::query()->whereKey($invoice->invoice_id)->lockForUpdate()->firstOrFail();
            if ($locked->access_key !== null || $locked->stockMovements()->exists()) {
                throw ValidationException::withMessages(['invoice_id' => 'Notas importadas ou vinculadas a movimentações de estoque não podem ser excluídas.']);
            }
            $locked->delete();
        });

        return response()->json(['message' => 'Nota fiscal excluída com sucesso.']);
    }
}
