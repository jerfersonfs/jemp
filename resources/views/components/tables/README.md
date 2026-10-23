# Tabelas

`<x-tables.table>` renderiza tabelas semânticas com colunas e linhas recebidas da página ou do controller. A tabela permanece responsiva em telas estreitas por meio de rolagem horizontal acessível.

```blade
<x-tables.table
    caption="Notas fiscais"
    :columns="[
        ['key' => 'number', 'label' => 'N° NF-e'],
        ['key' => 'customer', 'label' => 'Cliente'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
    ]"
    :rows="$invoices"
    :status-variants="['A Vencer' => 'warning', 'Vencido' => 'danger', 'Pago' => 'success']" />
```

Cada coluna aceita `key`, `label` e `class`; tipos adicionais incluem `status`, `detail`, `actions`, `input` e `computed-stock`. As linhas podem trazer uma chave `_data` para atributos `data-*` usados por filtros no navegador. A página define os dados; o componente não consulta banco de dados. `empty-message` altera a mensagem quando não houver registros.

### Variações

- `variant="rounded"` (padrão): linhas separadas com cantos suaves, como a tabela de relatórios.
- `variant="filtered"`: mesma superfície arredondada, com espaço para um formulário no slot `filters` e paginação/rodapé opcional no slot `footer`.
- `variant="spreadsheet"`: grade compacta com bordas entre todas as células.

Colunas com `type="detail"` renderizam uma ação que abre um dialog. Informe `targetKey` com a chave da linha que contém o identificador `data-dialog-layer` (ou use `target` fixo).

`type="actions"` exibe ações de editar/excluir/visualizar com ícones Phosphor e rótulos acessíveis; `type="input"` renderiza um campo editável inline; `type="computed-stock"` mostra um resultado calculado. A página deve implementar os comportamentos e informar quando os dados forem apenas demonstrativos.

```blade
<x-tables.table variant="filtered" :columns="$columns" :rows="$rows">
    <x-slot:filters><x-filters.filter-bar>...</x-filters.filter-bar></x-slot:filters>
    <x-slot:footer>...</x-slot:footer>
</x-tables.table>
```

Variantes de status disponíveis: `success`, `warning`, `danger`, `info` e `neutral`. O mesmo padrão está disponível como `<x-status.badge>` para outros componentes.
