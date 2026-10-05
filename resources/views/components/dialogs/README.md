# Dialogs

Componentes Blade reutilizáveis de diálogo, formulário, confirmação, abas, data e notificação.

## Dialog básico

```blade
<x-dialogs.dialog title="Novo registro" description="Preencha os dados.">
    Conteúdo do dialog
</x-dialogs.dialog>
```

Props principais: `title`, `description`, `size` (`small`, `medium`, `large`), `submit-label`, `cancel-label` e `show-actions`.

## Confirmação

```blade
<x-dialogs.confirmation title="Excluir produto" message="Esta ação não poderá ser desfeita." confirm-label="Excluir" />
```

## Formulário compartilhado

O componente `<x-dialogs.form-dialog>` pode ser usado para formulários de perfil, inclusão e edição de registros. Informe os campos por `fields`; o controller deve fornecer `action`, valores e processamento do formulário.

```blade
<x-dialogs.form-dialog
    title="Cadastrar produto"
    action="{{ route('products.store') }}"
    submit-label="Cadastrar"
    :fields="[
        ['name' => 'name', 'label' => 'Produto', 'type' => 'text', 'required' => true],
        ['name' => 'quantity', 'label' => 'Quantidade', 'type' => 'number', 'required' => true],
    ]" />
```

Para edição, use `mode="edit"` com `method="PUT"` ou `method="PATCH"` e forneça `action`. Essa variação aplica detalhes azuis usando `--color-info` no título, borda, foco dos campos e ação principal. O modo padrão é `create`, com detalhes na cor primária do JEMP. Os tipos aceitos são inputs HTML, `select` com `options` e `textarea`. Há suporte a `required`, `readonly`, `disabled`, `placeholder`, valores antigos e erros de validação.

```blade
<x-dialogs.form-dialog
    mode="edit"
    title="Editar produto"
    method="PUT"
    action="{{ route('products.update', $product) }}"
    :fields="$fields" />
```

## Seletor de data

Usa o input nativo de data do navegador para permitir entrada pelo teclado e calendário nativo.

```blade
<x-dialogs.date-picker name="due_date" label="Data de vencimento" :value="$dueDate" min="2025-01-01" />
```

Props: `name`, `label`, `value`, `min`, `max`, `required` e `disabled`.

## Dialog com abas

```blade
<x-dialogs.tabbed-dialog first-label="Dados" second-label="Acesso">
    Conteúdo da primeira aba
    <x-slot:second>Conteúdo da segunda aba</x-slot:second>
</x-dialogs.tabbed-dialog>
```

## Dialog de detalhes

`<x-dialogs.details-dialog>` apresenta informações de um registro em uma grade de rótulos/valores, com seções opcionais em abas. Use `variant="indicator"` para mostrar a lista tabular de registros que compõem um indicador; o padrão é `record`.

```blade
<x-dialogs.details-dialog
    title="Detalhes do cliente"
    :details="[
        ['label' => 'Nome', 'value' => $customer->name],
        ['label' => 'Documento', 'value' => $customer->document],
    ]" />

<x-dialogs.details-dialog
    variant="indicator"
    title="Notas que compõem o indicador"
    :columns="$columns"
    :rows="$invoices" />
```

Associe `data-dialog-open="id"` no gatilho à camada `data-dialog-layer="id"` que envolve o componente. A preview demonstra abertura por uma ação na linha e pela ação "Ver dados" do indicador.

## Alertas

`<x-dialogs.alert>` oferece as variantes `warning`, `error`, `success`, `info` e `bell`, além de `title`, `message` e `dismissible`.

## Integração e preview

Formulários devem receber suas rotas e dados do controller. A página `preview/components-preview` usa valores de demonstração e evita enviar os formulários. Para abrir uma camada de diálogo na preview, associe `data-dialog-open="id"` no botão a uma camada `data-dialog-layer="id"`.
