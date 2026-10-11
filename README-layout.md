# Layout da aplicação JEMP

## Referência visual

O layout compartilhado segue o frame **Home** e o componente **header+sidebar** da página **Protótipo** no arquivo JEMP Design do Figma. A estrutura usa uma navegação lateral escura, uma barra superior com o perfil e uma área de conteúdo sobre o fundo claro do sistema. O Figma é referência de hierarquia e proporção; as telas continuam responsáveis pelo próprio conteúdo.

## Estrutura implementada

- `resources/views/layouts/app.blade.php` define o documento HTML, carrega os assets Vite e envolve páginas internas no shell. Páginas sem a prop `page`, como o login, continuam fora dele.
- `resources/views/components/layout/app-shell.blade.php` organiza sidebar, header e conteúdo.
- `resources/views/components/layout/sidebar.blade.php` mantém a navegação e marca a rota atual. O grupo Produtos usa `<details>` e contém Catálogo, Inventário e Novo produto.
- `resources/views/components/layout/header.blade.php` mostra o título da seção no mobile e o menu de perfil.
- `resources/views/components/layout/icon.blade.php` centraliza os ícones vetoriais usados pelo shell, sem dependência de serviço externo.
- `resources/css/tokens.css` guarda cores, tipografia, espaçamentos e raios compartilhados. `resources/css/components/layout.css` contém os estilos responsivos do shell.
- `resources/js/components/layout.js` controla a abertura do menu lateral em telas menores, incluindo Escape, botão de fechar e foco de retorno.

## Como aplicar em uma página Blade

Passe a chave da página para o layout. O shell adiciona o header e a sidebar; deixe no slot apenas o conteúdo específico daquela página.

```blade
<x-layouts.app title="Catálogo · JEMP" page="catalogo">
    <div class="page-head">
        <div>
            <h1>Catálogo</h1>
            <p class="sub">Gerencie os produtos da operação.</p>
        </div>
    </div>

    {{-- Conteúdo específico da tela --}}
</x-layouts.app>
```

As chaves de navegação atualmente implementadas são `home`, `catalogo`, `produto`, `inventario` e `saidas`; elas correspondem às rotas nomeadas em `routes/web.php`. Ao adicionar uma nova página, crie sua rota, acrescente sua chave e label nos componentes de navegação/header e envolva a view com `<x-layouts.app page="chave">`.

## Responsividade e acessibilidade

Em telas largas, a sidebar ocupa 232 px e o header 84 px, seguindo as proporções do frame do Figma. Abaixo de 900 px, a sidebar vira um painel recolhível, acionado pelo botão do header; o fundo pode ser clicado para fechar, Escape fecha o painel e os links fecham o menu após a navegação. Os controles usam elementos semânticos, rótulos acessíveis, estado atual da rota e foco visível.

## Dados de demonstração

O nome e as iniciais do perfil no header são conteúdo estático de demonstração. Quando houver autenticação, substitua-os pelos dados enviados pela camada de aplicação. O menu “Central de ajuda” é informativo até existir uma rota de destino.
