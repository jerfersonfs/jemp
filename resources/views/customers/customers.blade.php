<x-layouts.app title="Clientes · JEMP" page="customers">
    @php
        $customers = [
            ['name' => 'Penha', 'document' => '12.345.678/0001-90', 'city' => 'Itapira - SP', 'phone' => '(19) 3863-0000', 'email' => 'contato@penha.com.br', 'status' => 'Ativo'],
            ['name' => 'Cristália', 'document' => '98.765.432/0001-12', 'city' => 'Itapira - SP', 'phone' => '(19) 3863-1000', 'email' => 'contato@cristalia.com.br', 'status' => 'Ativo'],
            ['name' => 'Jampac', 'document' => '55.666.777/0001-88', 'city' => 'São Paulo - SP', 'phone' => '(11) 3000-0000', 'email' => 'contato@jampac.com.br', 'status' => 'Ativo'],
            ['name' => 'MAMÃ', 'document' => '22.333.444/0001-11', 'city' => 'Campinas - SP', 'phone' => '(19) 3000-0000', 'email' => 'contato@mama.com.br', 'status' => 'Ativo'],
            ['name' => 'AQUI', 'document' => '88.777.666/0001-22', 'city' => 'Mogi Mirim - SP', 'phone' => '(19) 3500-0000', 'email' => 'contato@aqui.com.br', 'status' => 'Ativo'],
            ['name' => 'NB', 'document' => '11.222.333/0001-44', 'city' => 'Campinas - SP', 'phone' => '(19) 3600-0000', 'email' => 'contato@nb.com.br', 'status' => 'Ativo'],
            ['name' => 'SCL', 'document' => '33.444.555/0001-33', 'city' => 'Limeira - SP', 'phone' => '(19) 3700-0000', 'email' => 'contato@scl.com.br', 'status' => 'Ativo'],
            ['name' => 'JACTO', 'document' => '44.555.666/0001-77', 'city' => 'Pompéia - SP', 'phone' => '(14) 3800-0000', 'email' => 'contato@jacto.com.br', 'status' => 'Ativo'],
            ['name' => 'ROWEU', 'document' => '66.777.888/0001-99', 'city' => 'Itapira - SP', 'phone' => '(19) 3900-0000', 'email' => 'contato@roweu.com.br', 'status' => 'Ativo'],
            ['name' => 'Cristália', 'document' => '77.888.999/0001-00', 'city' => 'São Paulo - SP', 'phone' => '(11) 3900-0000', 'email' => 'contato@empresa.com.br', 'status' => 'Inativo'],
        ];
    @endphp

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
            <label class="customers-search__input"><i class="ph ph-magnifying-glass" aria-hidden="true"></i><span class="jemp-visually-hidden">Buscar clientes</span><input type="search" placeholder="Buscar por nome, CNPJ, cidade ou código..." data-customer-search></label>
            <label class="customers-search__filter"><i class="ph ph-funnel" aria-hidden="true"></i><span class="jemp-visually-hidden">Filtrar status dos clientes</span><select data-customer-status><option value="">Todos</option><option value="Ativo">Ativos</option><option value="Inativo">Inativos</option></select></label>
        </div>

        <div class="customers-table-scroll" role="region" aria-label="Lista de clientes" tabindex="0">
            <table class="customers-table">
                <thead><tr><th scope="col">Cliente <span>↕</span></th><th scope="col">CNPJ <span>↕</span></th><th scope="col">Cidade <span>↕</span></th><th scope="col">Telefone</th><th scope="col">E-mail</th><th scope="col">Status</th><th scope="col">Ações</th></tr></thead>
                <tbody data-customer-rows>
                    @foreach ($customers as $customer)
                        <tr data-customer-row data-status="{{ $customer->status }}" data-search="{{ strtolower(implode(' ', $customer->only(['name', 'document', 'city', 'phone', 'email']))) }}" data-update-url="{{ route('customers.update', $customer) }}" data-delete-url="{{ route('customers.destroy', $customer) }}" data-record="{{ $customer->toJson(JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}">
                            <td>{{ $customer->name }}</td><td>{{ $customer->document }}</td><td>{{ $customer->city }}</td><td>{{ $customer->phone }}</td><td>{{ $customer->email }}</td>
                            <td><x-status.badge :label="$customer->status" :variant="$customer->status === 'Ativo' ? 'success' : 'danger'" /></td>
                            <td><div class="customers-table__actions">
                                <button class="customers-table__action customers-table__action--view" type="button" data-customer-view aria-label="Ver detalhes de {{ $customer['name'] }}" title="Ver detalhes"><i class="ph ph-eye" aria-hidden="true"></i></button>
                                <button class="customers-table__action customers-table__action--edit" type="button" data-customer-edit aria-label="Editar cliente {{ $customer['name'] }}" title="Editar cliente"><i class="ph ph-pencil-simple-line" aria-hidden="true"></i></button>
                                <button class="customers-table__action customers-table__action--delete" type="button" data-customer-delete aria-label="Excluir cliente {{ $customer['name'] }}" title="Excluir cliente"><i class="ph ph-trash" aria-hidden="true"></i></button>
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
                <label>Nome / Empresa<input name="name" value="{{ old('name') }}" required></label><label>CNPJ<input name="document" value="{{ old('document') }}" placeholder="00.000.000/0000-00" required></label>
                <label>Cidade<input name="city" value="{{ old('city') }}" required></label><label>Telefone<input name="phone" type="tel" value="{{ old('phone') }}"></label>
                <label>E-mail<input name="email" type="email" value="{{ old('email') }}"></label><label>Status<select name="status"><option value="Ativo" @selected(old('status', 'Ativo') === 'Ativo')>Ativo</option><option value="Inativo" @selected(old('status') === 'Inativo')>Inativo</option></select></label>
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
    @if (session('status'))
        <p class="customers-note" role="status">{{ session('status') }}</p>
    @endif
</x-layouts.app>
