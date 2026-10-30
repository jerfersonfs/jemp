# Abas

`<x-tabs.tabs>` cria uma navegação por abas com suporte a clique, setas do teclado, Home e End. O JavaScript correspondente é carregado pelo ponto de entrada `resources/js/app.js`.

```blade
<x-tabs.tabs id="inventory-tabs" :tabs="[
    ['id' => 'stock', 'label' => 'Estoque'],
    ['id' => 'movements', 'label' => 'Movimentações'],
]">
    <section data-tab-panel="stock">Conteúdo do estoque.</section>
    <section data-tab-panel="movements" hidden>Conteúdo das movimentações.</section>
</x-tabs.tabs>
```

Props: `tabs` (lista com `id` e `label`), `active` (id inicial opcional), `label` (nome acessível da lista de abas) e `variant`. Use `variant="pills"` para as abas segmentadas no topo dos relatórios (padrão visual do Figma) ou `variant="underline"` para abas de conteúdo/detalhes. O conteúdo de cada painel deve usar `data-tab-panel` com o mesmo id da aba.
