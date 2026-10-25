<x-layouts.app title="Clientes · JEMP" page="customers">
    <div class="page-head customers-page__head">
        <div><h1>Clientes</h1><p>Gerencie e consulte os clientes cadastrados no sistema.</p></div>
    </div>

    <section class="customers-card" aria-labelledby="customers-list-title" data-customers-list data-validation-errors="{{ $errors->any() ? 'true' : 'false' }}" data-old-record-id="{{ old('_record_id', '') }}">
        <header class="customers-card__header">
            <span class="customers-card__icon"><i class="ph ph-user" aria-hidden="true"></i></span>
            <div><h2 id="customers-list-title">Lista de Clientes</h2><p>Visualize, filtre e gerencie todos os clientes cadastrados.</p></div>
            <button class="btn btn-primary customers-add" type="button" data-customer-open><i class="ph ph-plus" aria-hidden="true"></i> Adicionar Cliente</button>
        </header>

        <div class="customers-search">
            <label class="customers-search__input"><i class="ph ph-magnifying-glass" aria-hidden="true"></i><span class="jemp-visually-hidden">Buscar clientes</span><input type="search" placeholder="Buscar por nome, documento, contato ou e-mail..." data-customer-search></label>
            <label class="customers-search__filter"><i class="ph ph-funnel" aria-hidden="true"></i><span class="jemp-visually-hidden">Filtrar condição de pagamento</span><select data-customer-payment><option value="">Todas as condições</option>@foreach ($customers->pluck('customer_payment_terms')->filter()->unique()->sort() as $paymentTerms)<option value="{{ $paymentTerms }}">{{ $paymentTerms }}</option>@endforeach</select></label>
        </div>

        <div class="customers-table-scroll" role="region" aria-label="Lista de clientes" tabindex="0">
            <table class="customers-table">
                <thead><tr><th scope="col">Cliente <span>↕</span></th><th scope="col">Documento <span>↕</span></th><th scope="col">Cidade <span>↕</span></th><th scope="col">Contato</th><th scope="col">E-mail</th><th scope="col">Condição de pagamento</th><th scope="col">Ações</th></tr></thead>
                <tbody data-customer-rows>
                    @foreach ($customers as $customer)
                        <tr data-customer-row data-payment="{{ $customer->customer_payment_terms }}" data-search="{{ strtolower(implode(' ', $customer->only(['customer_name', 'customer_document', 'customer_contact', 'customer_email', 'customer_payment_terms']))) }}" data-update-url="{{ route('customers.update', $customer) }}" data-delete-url="{{ route('customers.destroy', $customer) }}" data-record="{{ $customer->toJson(JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}">
                            <td>{{ $customer->customer_name }}</td><td>{{ $customer->customer_document }}</td><td>—</td><td>{{ $customer->customer_contact }}</td><td>{{ $customer->customer_email }}</td><td>{{ $customer->customer_payment_terms }}</td>
                            <td><div class="customers-table__actions">
                                <button class="customers-table__action customers-table__action--view" type="button" data-customer-view aria-label="Ver detalhes de {{ $customer->customer_name }}" title="Ver detalhes"><i class="ph ph-eye" aria-hidden="true"></i></button>
                                <button class="customers-table__action customers-table__action--edit" type="button" data-customer-edit aria-label="Editar cliente {{ $customer->customer_name }}" title="Editar cliente"><i class="ph ph-pencil-simple-line" aria-hidden="true"></i></button>
                                <button class="customers-table__action customers-table__action--delete" type="button" data-customer-delete aria-label="Excluir cliente {{ $customer->customer_name }}" title="Excluir cliente"><i class="ph ph-trash" aria-hidden="true"></i></button>
                            </div></td>
                        </tr>
                    @endforeach
                    <tr data-customer-empty hidden><td colspan="7">Nenhum cliente corresponde aos filtros selecionados.</td></tr>
                </tbody>
            </table>
        </div>

        <footer class="customers-footer"><span data-customer-summary>Mostrando {{ $customers->count() }} clientes</span><nav class="customers-pagination" aria-label="Paginação de clientes"><button type="button" data-customer-previous disabled>Anterior</button><span data-customer-pages></span><button type="button" data-customer-next disabled aria-label="Próxima página">Próximo</button></nav></footer>
    </section>

    <div class="customers-dialog-layer" data-customer-dialog hidden>
        <section class="jemp-dialog jemp-dialog--large" role="dialog" aria-modal="true" aria-labelledby="customer-dialog-title" data-customer-edit-dialog>
            <header class="jemp-dialog__header"><div><h2 class="jemp-dialog__title" id="customer-dialog-title">Adicionar Cliente</h2><p class="jemp-dialog__description">Preencha os dados cadastrais do cliente.</p></div><button class="jemp-dialog__close" type="button" data-customer-close aria-label="Fechar">×</button></header>
            <form class="customers-dialog-form jemp-form-dialog--create" data-customer-form action="{{ route('customers.store') }}" method="POST" data-customer-store-url="{{ route('customers.store') }}">
                @csrf
                <input type="hidden" name="_record_id" value="{{ old('_record_id', '') }}">
                @if ($errors->any())
                    <p class="jemp-form-dialog__error" role="alert">{{ $errors->first() }}</p>
                @endif
                <label>Nome / Empresa<input name="customer_name" value="{{ old('customer_name') }}" required></label><label>Documento<input name="customer_document" value="{{ old('customer_document') }}" required></label>
                <label>Contato<input name="customer_contact" type="tel" value="{{ old('customer_contact') }}"></label><label>E-mail<input name="customer_email" type="email" value="{{ old('customer_email') }}"></label>
                <label>Condição de pagamento<input name="customer_payment_terms" value="{{ old('customer_payment_terms') }}" required></label>
                <footer class="jemp-dialog__footer"><button class="jemp-dialog__button jemp-dialog__button--secondary" type="button" data-customer-close>Cancelar</button><button class="jemp-dialog__button jemp-dialog__button--primary" type="submit">Salvar Cliente</button></footer>
            </form>
        </section>
    </div>

    <div class="customers-dialog-layer" data-customer-details-layer hidden>
        <x-dialogs.details-dialog title="Detalhes do Cliente" description="Dados do cadastro selecionado." :details="[]" data-customer-details />
    </div>

    <div class="customers-dialog-layer" data-customer-confirm-layer hidden>
        <form method="POST" data-customer-delete-form>
            @csrf
            @method('DELETE')
            <x-dialogs.confirmation title="Excluir cliente?" message="Esta ação não poderá ser desfeita." confirm-label="Excluir cliente" cancel-label="Cancelar" data-customer-confirm-dialog />
        </form>
    </div>
    <p class="customers-note" @if (session('status')) role="status" @endif>{{ session('status', 'Dados carregados do cadastro do banco.') }}</p>
</x-layouts.app>
