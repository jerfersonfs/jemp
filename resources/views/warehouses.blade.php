<x-layouts.app title="Armazéns · JEMP" page="warehouses">
    <div data-warehouse-page>
        <header class="page-head">
            <div><h1>Armazéns</h1><p>Consulte os locais de estoque e acompanhe as solicitações da empresa.</p></div>
        </header>

        <x-cards.panel title="Lista de armazéns">
            <x-slot:header><i class="ph ph-warehouse" aria-hidden="true"></i></x-slot:header>
            <x-slot:actions>
                <x-buttons.button data-dialog-open="warehouse-request"><i class="ph ph-plus" aria-hidden="true"></i> Solicitar armazém</x-buttons.button>
            </x-slot:actions>
            <x-slot:filters>
                <x-filters.filter-bar :action="route('warehouses.index')" label="Filtrar armazéns" :wide-search="true">
                    <x-inputs.input type="search" name="busca" label="Buscar armazém" placeholder="ID, nome ou descrição" :value="$search" maxlength="100">
                        <x-slot:leading><i class="ph ph-magnifying-glass" aria-hidden="true"></i></x-slot:leading>
                    </x-inputs.input>
                    <x-inputs.input type="select" label="Situação" name="situacao" :value="$situation" :options="['Todas' => 'Todas as situações'] + $situations" />
                    <x-slot:actions><x-buttons.button variant="secondary" type="submit">Filtrar</x-buttons.button></x-slot:actions>
                </x-filters.filter-bar>
            </x-slot:filters>

            @php
                $columns = [
                    ['key' => 'warehouse_id', 'label' => 'ID'],
                    ['key' => 'warehouse_name', 'label' => 'Nome'],
                    ['key' => 'warehouse_description', 'label' => 'Descrição', 'class' => 'whitespace-normal! wrap-anywhere'],
                    ['key' => 'warehouse_situation', 'label' => 'Situação', 'type' => 'status'],
                    ['key' => 'details', 'label' => 'Ações', 'type' => 'detail', 'targetKey' => 'details'],
                ];
                $rows = $warehouses->getCollection()->map(fn ($warehouse) => [
                    'warehouse_id' => $warehouse->getKey(),
                    'warehouse_name' => $warehouse->warehouse_name,
                    'warehouse_description' => $warehouse->warehouse_description ?: '—',
                    'warehouse_situation' => $warehouse->warehouse_situation,
                    'details' => 'warehouse-'.$warehouse->getKey(),
                ]);
            @endphp
            <x-tables.table caption="Armazéns cadastrados" :columns="$columns" :rows="$rows"
                :status-variants="['Ativo' => 'success', 'Solicitado' => 'warning', 'Inativo' => 'neutral', 'Recusado' => 'danger']"
                empty-message="Nenhum armazém encontrado para estes filtros." />
            <x-slot:footer>
                <div class="movement-table-footer">
                    <span>{{ $warehouses->total() }} {{ $warehouses->total() === 1 ? 'armazém' : 'armazéns' }}</span>
                    <nav class="movement-pagination" aria-label="Páginas de armazéns">
                        @if ($warehouses->onFirstPage())
                            <button type="button" class="btn btn-secondary" disabled>Anterior</button>
                        @else
                            <a class="btn btn-secondary" href="{{ $warehouses->previousPageUrl() }}">Anterior</a>
                        @endif
                        <span class="movement-pagination__current">{{ $warehouses->currentPage() }} / {{ $warehouses->lastPage() }}</span>
                        @if ($warehouses->hasMorePages())
                            <a class="btn btn-secondary" href="{{ $warehouses->nextPageUrl() }}">Próximo</a>
                        @else
                            <button type="button" class="btn btn-secondary" disabled>Próximo</button>
                        @endif
                    </nav>
                </div>
            </x-slot:footer>
        </x-cards.panel>

        <x-dialogs.modal id="warehouse-request" label="Solicitar armazém" :data-reopen="$errors->hasAny(['warehouse_name', 'warehouse_description', 'warehouse_request_reason']) ? 'true' : null">
            <x-dialogs.form-dialog title="Solicitar armazém" description="O armazém ficará disponível para uso após a aprovação da administração."
                :action="route('warehouses.store')" submit-label="Enviar solicitação" layout="grid">
                <x-inputs.input name="warehouse_name" label="Nome" maxlength="100" required />
                <x-inputs.input id="warehouse-request-status" label="Situação" value="Solicitado" disabled />
                <div class="col-span-full"><x-inputs.input name="warehouse_description" label="Descrição" type="textarea" maxlength="4000" :rows="2" required /></div>
                <div class="col-span-full"><x-inputs.input name="warehouse_request_reason" label="Motivo da solicitação" type="textarea" maxlength="4000" :rows="3" required /></div>
            </x-dialogs.form-dialog>
        </x-dialogs.modal>

        @foreach ($warehouses as $warehouse)
            @php
                $details = [
                    ['label' => 'ID', 'value' => $warehouse->getKey()],
                    ['label' => 'Situação', 'value' => $warehouse->warehouse_situation],
                    ['label' => 'Nome', 'value' => $warehouse->warehouse_name],
                    ['label' => 'Descrição', 'value' => $warehouse->warehouse_description],
                    ['label' => 'Solicitado por', 'value' => $warehouse->requester?->name],
                    ['label' => 'Motivo da solicitação', 'value' => $warehouse->warehouse_request_reason],
                ];
                if ($warehouse->warehouse_situation === 'Recusado') {
                    $details[] = ['label' => 'Motivo da recusa', 'value' => $warehouse->warehouse_cancelation_reason];
                }
            @endphp
            <x-dialogs.modal :id="'warehouse-'.$warehouse->getKey()" :label="$warehouse->warehouse_name">
                <x-dialogs.details-dialog :title="$warehouse->warehouse_name" :details="$details" class="wrap-anywhere" />
            </x-dialogs.modal>
        @endforeach
    </div>
</x-layouts.app>
