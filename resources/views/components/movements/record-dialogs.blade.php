@props([
    'entity',
    'createTitle',
    'description',
    'fields' => [],
    'action' => '#',
    'method' => 'POST',
])

@php
    $warehouseFields = collect($fields)->pluck('name')->all();
    $balanceSides = [];
    if (in_array('origin_warehouse_id', $warehouseFields, true)) {
        $balanceSides['origin'] = 'Saldo atual na origem';
    }
    if (in_array('dest_warehouse_id', $warehouseFields, true)) {
        $balanceSides['destination'] = 'Saldo atual no destino';
    }
@endphp

<div class="movement-dialog-layer" data-movement-layer="form" hidden>
    <x-dialogs.form-dialog
        id="movement-record-form-dialog"
        :title="$createTitle"
        :description="$description"
        :fields="$fields"
        layout="grid"
        :action="$action"
        :method="$method"
        size="large"
        :submit-label="'Cadastrar ' . $entity"
        data-movement-dialog="form"
        data-create-title="{{ $createTitle }}"
        data-edit-title="Editar {{ $entity }}"
        data-create-label="Cadastrar {{ $entity }}"
        data-edit-label="Salvar alterações">
        @if ($balanceSides)
            <section class="movement-stock-balances" data-movement-balances data-balance-url="{{ route('movements.balances') }}" aria-label="Saldos atuais do produto">
                <div class="movement-stock-balances__fields">
                    @foreach ($balanceSides as $side => $label)
                        <div class="jemp-form-dialog__field">
                            <label for="movement-{{ $side }}-balance">{{ $label }}</label>
                            <input id="movement-{{ $side }}-balance" type="text" data-movement-balance="{{ $side }}" readonly value="—" aria-describedby="movement-balance-help">
                        </div>
                    @endforeach
                </div>
                <div class="movement-stock-balances__footer">
                    <div>
                        <p id="movement-balance-help">Saldo do produto, condição e armazém selecionados. A disponibilidade é conferida novamente ao registrar.</p>
                        <p data-movement-balance-status role="status">Selecione o produto e o armazém para consultar.</p>
                    </div>
                    <button class="btn btn-secondary" type="button" data-movement-balances-refresh>Atualizar saldos</button>
                </div>
            </section>
        @endif
    </x-dialogs.form-dialog>
</div>

<div class="movement-dialog-layer" data-movement-layer="details" hidden>
    <x-dialogs.details-dialog
        title="Detalhes do registro"
        :details="[]"
        data-movement-dialog="details" />
</div>

<div class="movement-dialog-layer" data-movement-layer="confirm" hidden>
    <x-dialogs.confirmation
        title="Excluir registro?"
        message="Esta ação não poderá ser desfeita."
        confirm-label="Excluir registro"
        cancel-label="Cancelar"
        data-movement-dialog="confirm" />
</div>
