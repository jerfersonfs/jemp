<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportInvoiceXmlRequest;
use App\Http\Requests\PreviewInvoiceXmlRequest;
use App\InvoiceXmlImporter;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;

class InvoiceXmlController extends Controller
{
    public function preview(PreviewInvoiceXmlRequest $request, InvoiceXmlImporter $importer): JsonResponse
    {
        return response()->json($importer->preview($request->file('xml_file')->getContent()));
    }

    public function store(ImportInvoiceXmlRequest $request, InvoiceXmlImporter $importer): JsonResponse
    {
        try {
            $invoice = $importer->import(
                $request->file('xml_file')->getContent(), $request->validated(), (int) $request->user()->getAuthIdentifier(),
            );
        } catch (UniqueConstraintViolationException $exception) {
            report($exception);

            return response()->json(['message' => 'A nota ou uma associação já foi registrada em outra operação. Atualize a revisão; nenhuma baixa adicional foi feita.'], 409);
        }

        return response()->json([
            'message' => 'NF-e importada e estoque baixado com sucesso.',
            'data' => ['invoice_id' => $invoice->invoice_id, 'amount' => $invoice->amount],
        ], 201);
    }
}
