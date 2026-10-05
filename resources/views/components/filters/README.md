# Filtros

`<x-filters.filter-bar>` organiza campos de busca e filtros em um formulário acessível. Recebe `action`, `method` (`GET` ou `POST`) e `label` para nomear a região de busca. Os campos são fornecidos pelo slot principal e as ações pelo slot nomeado `actions`.

```blade
<x-filters.filter-bar action="{{ route('products.index') }}" label="Filtrar produtos">
    <x-inputs.input name="search" label="Buscar" type="search" />
    <x-inputs.input name="warehouse" label="Barracão" type="select" :options="$warehouses" />

    <x-slot:actions>
        <x-buttons.button type="submit">Filtrar</x-buttons.button>
    </x-slot:actions>
</x-filters.filter-bar>
```

O filtro não acessa o banco nem interpreta parâmetros; a rota/controller recebe e processa os campos enviados.

## Filtro vertical

`<x-filters.vertical-filter>` apresenta busca e opções pré-definidas em uma coluna para acompanhar relatórios. Aceita `filters` com `value` e `label`, `name`, `selected`, `search-name`, `search-label`, `search-placeholder`, `action`, `method` e `label`.

```blade
<x-filters.vertical-filter
    name="status"
    selected="all"
    :filters="[
        ['value' => 'all', 'label' => 'Todos'],
        ['value' => 'due', 'label' => 'A vencer'],
    ]">
    <x-buttons.button type="submit">Aplicar</x-buttons.button>
</x-filters.vertical-filter>
```
