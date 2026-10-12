# JEMP
---
### Objetivo
> O sistema tem como objetivo auxiliar o cliente no controle de notas fiscais e estoque, centralizando essas informações e facilitando o acompanhamento das movimentações.
---
### Funcionalidades
> O sistema permitirá cadastrar e consultar notas fiscais, registrar entradas e saídas de produtos e acompanhar as quantidades disponíveis em estoque.
---
### Tecnologias
  - Banco de dados:MySQL
  - Design: Figma
  - Back-end:Laravel
  - Front-end:Blade
    ---

### Componentes Blade

Componentes reutilizáveis ficam em `resources/views/components/`, agrupados por domínio. Seus estilos e comportamentos ficam separados em `resources/css/components/` e `resources/js/`; os estilos são importados por `resources/css/app.css`.

A Home é a rota inicial `/`; a navegação leva às views que já existem em cada domínio. As páginas de domínio ainda não têm conteúdo implementado. A página de demonstração de componentes pode não estar habilitada por rota neste estado do projeto.

Use `<x-dialogs.form-dialog>` para formulários de perfil, cadastro e edição, configurando o título, os campos, o método e a ação recebida da rota/controlador. Para edição, informe `mode="edit"` para aplicar os detalhes azuis. Consulte os READMEs dentro das pastas `cards` e `dialogs` para os exemplos atualizados.

Os demais componentes reutilizáveis incluem campos (`inputs`), filtros (`filters`), tabelas (`tables`), abas (`tabs`), tooltips (`tooltips`), popovers (`popovers`) e ícones (`icons`). Cada pasta contém um README com exemplos e opções. A página `/components-preview` apresenta esses componentes com dados fictícios e interações demonstrativas.

Os ícones da navegação, do login e da preview usam Phosphor Web Regular via CDN, fixado na versão 2.1.2, sem baixar arquivos de ícones para o projeto. O inventário e as variações preto/branco estão documentados em `resources/views/components/icons/README.md`. O uso do CDN requer conexão com a internet.

### Executar o protótipo

Requisitos: PHP **8.4.1 ou superior** (o conjunto de dependências instalado neste projeto exige essa versão), Composer, Node.js e npm.

Na raiz do repositório, copie o exemplo de ambiente apenas se ainda não existir um `.env`. No PowerShell:

```sh
if (!(Test-Path .env)) { Copy-Item .env.example .env }
```

Prepare o banco SQLite caso o arquivo ainda não exista:

```sh
if (!(Test-Path database/database.sqlite)) { New-Item -Path database/database.sqlite -ItemType File }
```

Instale as dependências do Laravel:

```sh
composer install
```

Depois gere a chave, execute as migrations e instale as dependências do front-end:

```sh
php artisan key:generate
php artisan migrate
npm install
```

Mantenha `DB_CONNECTION=sqlite` para usar a configuração de demonstração. Se o ambiente já tiver `.env`, não o substitua.

Inicie o servidor Laravel e o Vite em terminais separados:

```sh
php artisan serve
npm run dev
```

Abra **http://127.0.0.1:8000/** para a Home e a navegação com sidebar. Rotas disponíveis: `/login`, `/produtos`, `/clientes`, `/consulta-nfe`, `/vendas`, `/entradas`, `/saidas`, `/internas` e `/relatorios`. A rota inicial (`/`) é a Home.

O login e os dados das telas são demonstrativos; a autenticação ainda não está conectada. O login demonstra validação nativa dos campos e exibição/ocultação da senha. Consulte `resources/views/components/layout/README-layout.md` para o comportamento do shell e `resources/views/README.md` para as telas de login e home.

#### Colaboradores:
  - Enzo Alberti:
  - Jerferson Freitas:jeefreitas315@gmail.com
  - Pedro Pinheiro:
  - Marcelo Monteiro:
  - Marcos Paulo Cruz:
  - Miguel Beral:
