# Ícones

O projeto usa Phosphor Web Regular 2.1.2 via CDN. A folha é carregada em `resources/css/app.css`; a aplicação precisa de internet para buscar o CSS e o webfont.

## Ícones da navegação

`resources/views/components/layout/icon.blade.php` mapeia nomes semânticos para classes Phosphor. O `currentColor` herda a cor da sidebar, do header ou do controle em que o ícone aparece.

| Uso | Ícone | Classe |
| --- | --- | --- |
| Home | House | `ph-house` |
| Produtos e saídas | Cube | `ph-cube` |
| Clientes | Users Three | `ph-users-three` |
| Consulta NF-e | Note Pencil | `ph-note-pencil` |
| Vendas | Handshake | `ph-handshake` |
| Entradas | Tray Arrow Down | `ph-tray-arrow-down` |
| Movimentações internas | Arrows Left Right | `ph-arrows-left-right` |
| Relatórios | Clipboard Text | `ph-clipboard-text` |
| Recolher sidebar | Sidebar Simple | `ph-sidebar-simple` |
| Menu mobile | List | `ph-list` |
| Ajuda | Question | `ph-question` |
| Login | User, Lock Key e Eye | `ph-user`, `ph-lock-key`, `ph-eye` |

## Ícones da preview

O componente `<x-icons.icon>` também está disponível para a página de preview. Exemplos documentados: `magnifying-glass` e `plus-circle`. Ele aceita `color="black"` ou `color="white"` e tamanhos `sm`, `md`, `lg` e `xl`.
