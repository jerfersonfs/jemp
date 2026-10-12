# Views de login e home

## Login

`login.blade.php` apresenta o acesso conforme a tela Login do Figma: painel escuro com a mensagem de produto, área clara com grafismos geométricos e cartão central de autenticação. O CSS e o JavaScript ficam em `resources/css/pages/login.css` e `resources/js/pages/login.js`, carregados pelos pontos de entrada Vite. Cores e tipografia vêm de `resources/css/tokens.css`; os grafismos e a marca vetorial são locais.

O botão de olho alterna a visibilidade da senha. O formulário valida os campos obrigatórios no navegador e informa que autenticação e recuperação ainda não estão habilitadas; ele não realiza login nem persiste credenciais.

## Home

`jemp.blade.php` compõe a Home com `<x-cards.stat-card>` para os quatro indicadores e duas listas de avisos (`<x-lists.notice-list>`) para estoque crítico e contas a receber. Os valores são demonstrativos; “Ver dados” leva às áreas correspondentes. Não há gráficos na Home. A rota `/` (`home`) é a entrada do sistema.

O cabeçalho, navegação, perfil e estado recolhido da sidebar pertencem ao componente compartilhado descrito em `components/layout/README-layout.md`.
