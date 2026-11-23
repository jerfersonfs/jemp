@if ($invoice['payment'])
    <section class="jemp-details-dialog__body invoice-payment-detail">
        <h3>Pagamento da venda</h3>
        <dl class="jemp-details-dialog__grid">
            <div class="jemp-details-dialog__item"><dt>Valor total</dt><dd>{{ $invoice['total'] }}</dd></div>
            <div class="jemp-details-dialog__item"><dt>Parcelamento</dt><dd>{{ $invoice['installments'] }}</dd></div>
            <div class="jemp-details-dialog__item"><dt>Forma de pagamento</dt><dd>{{ $invoice['payment']->payment_method ?: 'Não informada' }}</dd></div>
            <div class="jemp-details-dialog__item"><dt>Recebimento</dt><dd>{{ $invoice['payment_date'] }}</dd></div>
        </dl>
        @if (! $invoice['payment']->paid_at && $invoice['status'] !== 'Cancelada')
            <form class="invoice-import-fields" method="post" action="{{ route('payments.settle', $invoice['id']) }}" data-financial-form>
                @csrf
                <label>Data do recebimento <input type="date" name="paid_at" value="{{ \App\Models\Payment::today() }}" max="{{ \App\Models\Payment::today() }}" required></label>
                <label>Forma de pagamento <select name="payment_method" required><option value="">Selecione</option>@foreach (\App\Models\Payment::METHODS as $method)<option @selected($invoice['payment']->payment_method === $method)>{{ $method }}</option>@endforeach</select></label>
                <button type="submit" class="btn btn-primary">Registrar recebimento integral</button>
            </form>
        @endif
    </section>
@endif
