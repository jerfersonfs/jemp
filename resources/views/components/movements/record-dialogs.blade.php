@props([
    'entity',
    'createTitle',
    'description',
    'fields' => [],
])

<div class="movement-dialog-layer" data-movement-layer="form" hidden>
    <x-dialogs.form-dialog
        id="movement-record-form-dialog"
        :title="$createTitle"
        :description="$description"
        :fields="$fields"
        layout="grid"
        action="#"
        method="GET"
        size="large"
        :submit-label="'Cadastrar ' . $entity"
        data-movement-dialog="form"
        data-create-title="{{ $createTitle }}"
        data-edit-title="Editar {{ $entity }}"
        data-create-label="Cadastrar {{ $entity }}"
        data-edit-label="Salvar alterações" />
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
