# Dialogs

Componentes Blade reutilizáveis baseados na seção de Dialogs do Figma.

## 1. Alert

### Uso básico

<x-dialogs.alert variant="warning" message="Operação cancelada."/>

### Exemplos

<x-dialogs.alert variant="error" message="Não foi possível concluir a operação."/>

<x-dialogs.alert variant="success" message="Produto cadastrado com sucesso."/>

<x-dialogs.alert variant="info" message="Produto editado."/>

<x-dialogs.alert variant="bell" message="40 novas pendências para pagamento."/>

### Variantes

- `warning`
- `error`
- `success`
- `info`
- `bell`

---

## 2. Confirmation

### Uso básico

<x-dialogs.confirmation  title="Excluir produto" message="Esta ação não poderá ser desfeita." confirm-label="Excluir produto"/>

---

## 3. Dialog

### Uso básico

<x-dialogs.dialog title="Cadastrar produto" description="Informe as características do produto.">
    Conteúdo do dialog
</x-dialogs.dialog>

---

## 4. Product Form

### Uso básico

<x-dialogs.form />

### Modo de edição

<x-dialogs.form mode="edit" :values="$product"/>

### Observações

O componente suporta os modos de criação e edição.

---

## 5. Profile Dialog

### Uso básico

<x-dialogs.profile-dialog :name="$name" :email="$email" :username="$username"/>

---

## 6. Tabbed Dialog

### Uso básico

<x-dialogs.tabbed-dialog first-label="Acesso" second-label="Senha">
    <x-slot:second>
        Conteúdo da aba Senha
    </x-slot:second>

    Conteúdo da aba Acesso
</x-dialogs.tabbed-dialog>

---

## 7. Popover

### Uso básico

<x-dialogs.popover trigger="Cadastrar produto" title="Cadastro rápido">
    Conteúdo do popover
</x-dialogs.popover>

---

## 8. Date Picker

### Uso básico

<x-dialogs.date-picker month="1" year="2025" selected-day="5"/>

---

## Observações

Os componentes de formulário fornecem a estrutura visual e os campos nativos necessários.

Ao integrar os componentes em uma tela, conecte seus métodos, validações e bindings aos fluxos correspondentes da aplicação.
