# Popovers

`<x-popovers.popover>` exibe um painel contextual não modal, ancorado em um botão. Ele fecha pelo botão de fechar, por `Escape` ou ao clicar fora; ao fechar, o foco retorna ao gatilho.

```blade
<x-popovers.popover title="Cadastrar produto" description="Informe as características.">
    <x-inputs.input name="product" label="Produto" required />
    <x-slot:actions>
        <x-buttons.button type="submit">Cadastrar</x-buttons.button>
    </x-slot:actions>
</x-popovers.popover>
```

Props: `title`, `description`, `trigger-label` e `size` (`regular` ou `wide`). O slot `trigger` substitui o conteúdo visual do gatilho; não coloque outro botão dentro dele. O slot `actions` adiciona ações no rodapé. O conteúdo padrão pode conter campos; a integração com rotas permanece sob responsabilidade da página/controller.
