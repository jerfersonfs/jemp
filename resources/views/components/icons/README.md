# Ícones

O projeto utiliza os ícones Phosphor Web por meio do CSS hospedado no jsDelivr. O arquivo carregado está fixado em `@phosphor-icons/web@2.1.2` e inclui somente o peso Regular; nenhum SVG ou pacote de ícones é baixado para o repositório.

```blade
<x-icons.icon name="magnifying-glass" color="black" label="Buscar" />
<x-icons.icon name="plus-circle" color="white" />
```

## Ícones usados atualmente

| Ícone Phosphor | Uso no JEMP | Cor utilizada | Classe Phosphor |
| --- | --- | --- | --- |
| Magnifying Glass | Campo de busca da preview de componentes | Preta | `ph-magnifying-glass` |
| Plus Circle | Ação de adicionar na preview de tabelas | Branca | `ph-plus-circle` |

O componente aceita `color="black"` ou `color="white"` e tamanhos `sm`, `md`, `lg` e `xl`. A cor é aplicada pelo CSS do projeto; o ícone usa `currentColor` do webfont.

## Integração externa

O CSS da família Regular é referenciado em `resources/css/components/icons/icons.css`. A página precisa de conexão com a internet para carregar a folha de estilo e o webfont servidos pelo CDN. Caso a aplicação precise funcionar sem acesso externo, será necessário trocar essa integração por assets locais ou um pacote gerenciado pelo projeto.
