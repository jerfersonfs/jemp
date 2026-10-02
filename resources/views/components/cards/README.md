# Componentes Blade — Cards

Componentes Blade baseados nas referências de Cards do Figma.

## 1. Message Card

### Uso básico

<x-cards.message-card />

### Com variação

<x-cards.message-card variant="outlined" trend="3,46%"/>

### Variantes

- `filled`
- `outlined`

---

## 2. Stat Card

### Uso básico

<x-cards.stat-card />

### Com propriedades

<x-cards.stat-card variant="mint" size="compact" label="Total customers" value="2,420"/>

### Card grande

<x-cards.stat-card variant="dark" size="large"/>

### Variantes

- `default`
- `mint`
- `dark`

### Tamanhos

- `regular`
- `large`
- `compact`

---

## 3. Finance Card

### Total a vencer

<x-cards.finance-card variant="overdue" title="Total a Vencer" value="R$12.300,00" indicator="↘"/>

### Faturas pendentes

<x-cards.finance-card variant="pending" title="Faturas Pendentes" value="14" indicator=""/>

### Variantes

- `receivable`
- `overdue`
- `pending`

---

## Observação

A área destinada ao ícone do `message-card` permanece vazia até que os ícones do projeto estejam disponíveis.