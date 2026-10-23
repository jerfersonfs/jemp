# Lista de avisos

`<x-lists.notice-list>` mostra avisos como itens de uma lista, com metadados e status, sem estrutura de tabela.

```blade
<x-lists.notice-list
    title="Estoque Crítico"
    description="Produtos que precisam de atenção"
    :items="$criticalStock"
/>
```

Cada item recebe `title`, `reference`, `description`, `details` (uma lista de `label` e `value`), `status` e `variant`. As variantes do status seguem o componente `<x-status.badge>`.

O estilo do componente fica em `resources/css/components/lists/lists.css`; o exemplo da Home está em `resources/views/jemp.blade.php`.
