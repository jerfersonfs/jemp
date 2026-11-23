<div class="invoice-dialog-layer" data-dialog-layer="invoice-create" hidden>
    <section class="jemp-dialog jemp-dialog--large invoice-import-dialog" role="dialog" aria-modal="true" aria-labelledby="manual-title">
        <header class="jemp-dialog__header">
            <h2 class="jemp-dialog__title" id="manual-title">Nova nota manual</h2>
            <button class="jemp-dialog__close" type="button" data-dialog-dismiss aria-label="Fechar">×</button>
        </header>
        <form method="post" action="{{ route('api.invoices.store') }}" data-manual-invoice data-financial-form>
            @csrf
            <div class="invoice-import-fields">
                <label>Número da nota <input name="invoice_number" type="number" min="1" required></label>
                <label>Série <input name="invoice_serie" type="number" min="1" value="1" required></label>
                <label>Emissão <input name="issue_date" type="date" value="{{ \App\Models\Payment::today() }}" required></label>
                <label>Cliente <select name="customer_id" required><option value="">Selecione</option>@foreach ($customers as $customer)<option value="{{ $customer->customer_id }}">{{ $customer->customer_name }} · {{ $customer->customer_document }}</option>@endforeach</select></label>
            </div>
            <fieldset class="invoice-import-payment">
                <legend>Itens da nota</legend>
                <div data-manual-items></div>
                <template data-item-template>
                    <div class="invoice-import-fields">
                        <label>Produto <select data-product required><option value="">Selecione</option>@foreach ($products as $product)<option value="{{ $product->product_id }}" data-price="{{ $product->cost_price }}">{{ $product->sku }} · {{ $product->product_name }} · R$ {{ number_format($product->cost_price, 2, ',', '.') }}</option>@endforeach</select></label>
                        <label>Quantidade <input data-quantity type="number" min="1" step="1" value="1" required></label>
                        <button type="button" class="btn btn-secondary" data-remove-item>Remover item</button>
                    </div>
                </template>
                <button type="button" class="btn btn-secondary" data-add-item>Adicionar item</button>
            </fieldset>
            @include('invoice_consult.sale-payment')
            <footer class="jemp-dialog__footer"><button type="button" class="btn btn-secondary" data-dialog-dismiss>Cancelar</button><button type="submit" class="btn btn-primary">Confirmar nota</button></footer>
        </form>
    </section>
</div>
