# Campos de entrada

`<x-inputs.input>` cria campos acessíveis com label, indicação obrigatória, dica, erro de validação e slots opcionais para complementos visuais.

```blade
<x-inputs.input name="customer" label="Cliente" placeholder="Buscar cliente" required />

<x-inputs.input name="category" label="Categoria" type="select" :options="$categories" placeholder="Selecione" />

<x-inputs.input name="description" label="Descrição" type="textarea" hint="Descreva o item." />
```

Tipos disponíveis: tipos nativos de `<input>`, `select` com `options` e `textarea`. Props: `name`, `label`, `type`, `value`, `placeholder`, `hint`, `error`, `required`, `disabled`, `readonly`, `size` (`sm`, `md`, `lg`) e `rows`. O valor antigo e os erros da sessão Laravel são reaproveitados automaticamente.

Use os slots `leading` e `trailing` para adornos que não substituem o label acessível do campo.
