# Layout da aplicação JEMP

## Referência visual

O layout compartilhado segue o frame **Home** e o componente **header+sidebar** da página **Protótipo** no arquivo JEMP Design do Figma. A estrutura usa uma navegação lateral escura, uma barra superior com o perfil e uma área de conteúdo sobre o fundo claro do sistema. O Figma é referência de hierarquia e proporção; as telas continuam responsáveis pelo próprio conteúdo.

## Estrutura implementada

- `resources/views/components/layouts/app.blade.php` define o documento HTML, carrega os assets Vite e envolve páginas internas no shell. O caminho `components/layouts` é necessário para resolver a tag Blade `<x-layouts.app>`. Páginas sem a prop `page`, como o login, continuam fora do shell.
- `resources/views/components/layout/app-shell.blade.php` organiza sidebar, header e conteúdo.
- `resources/views/components/layout/sidebar.blade.php` mantém links diretos para as telas existentes, agrupados por divisores como no protótipo, e marca a rota atual. No desktop, o botão recolhe a barra a uma faixa de ícones.
- `resources/views/components/layout/header.blade.php` mostra o título da seção no mobile e o menu de perfil.
- `resources/views/components/layout/icon.blade.php` mapeia os nomes semânticos da aplicação para as classes do Phosphor Web. A fonte Regular 2.1.2 é importada via CDN em `resources/css/app.css`.
- A marca usada na sidebar é `public/images/logo-simbolo.jpg`; a cópia original permanece em `resources/views/images/`.
- `resources/css/tokens.css` guarda cores, tipografia, espaçamentos e raios compartilhados. `resources/css/components/layout.css` contém os estilos responsivos do shell.
- `resources/js/components/layout.js` controla o recolhimento persistente no desktop e a abertura da gaveta em telas menores, incluindo Escape, clique no fundo e fechamento após navegação.

## Como aplicar em uma página Blade

Passe a chave da página para o layout. O shell adiciona o header e a sidebar; deixe no slot apenas o conteúdo específico daquela página.

```blade
<x-layouts.app title="Produtos · JEMP" page="products">
    <div class="page-head">
        <div>
            <h1>Catálogo</h1>
            <p class="sub">Gerencie os produtos da operação.</p>
        </div>
    </div>

    {{-- Conteúdo específico da tela --}}
</x-layouts.app>
```

As chaves de navegação são `home`, `products`, `customers`, `invoice-consult`, `sales`, `entrance`, `exits`, `internal` e `reports`; elas correspondem às rotas nomeadas em `routes/web.php`. A sidebar usa Phosphor Web Regular 2.1.2; o mapeamento dos ícones está em `resources/views/components/icons/README.md`.

## Responsividade e acessibilidade

Em telas largas, a sidebar ocupa 232 px e o header 84 px, seguindo as proporções do frame do Figma. O botão ao lado da marca recolhe a barra para 76 px e salva essa preferência no navegador. Abaixo de 900 px, a sidebar funciona como uma gaveta acionada pelo botão do header; clicar no fundo, pressionar Escape ou navegar fecha a gaveta. Os controles usam elementos semânticos, rótulos acessíveis, estado atual da rota e foco visível.

## Dados de demonstração

O nome e as iniciais do perfil no header são conteúdo estático de demonstração. Quando houver autenticação, substitua-os pelos dados enviados pela camada de aplicação. O menu “Central de ajuda” é informativo até existir uma rota de destino.
