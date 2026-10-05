# Tooltips

`<x-tooltips.tooltip>` mostra uma dica curta ao passar o mouse ou focar o componente. O conteúdo também é ligado ao gatilho com `aria-describedby`.

```blade
<x-tooltips.tooltip text="Adicionar ao cadastro">
    <button type="button">?</button>
</x-tooltips.tooltip>
```

Props: `text` e `placement` (`top` ou `bottom`). Use frases curtas; o tooltip não substitui o label ou instrução persistente de um campo.
