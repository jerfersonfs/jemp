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

A página de demonstração dos componentes está disponível em `/components-preview` e usa estilos e scripts próprios em `resources/css/preview/` e `resources/js/preview/`. Os dados dos formulários nessa página são apenas de demonstração.

Use `<x-dialogs.form-dialog>` para formulários de perfil, cadastro e edição, configurando o título, os campos, o método e a ação recebida da rota/controlador. Para edição, informe `mode="edit"` para aplicar os detalhes azuis. Consulte os READMEs dentro das pastas `cards` e `dialogs` para os exemplos atualizados.

Os demais componentes reutilizáveis incluem campos (`inputs`), filtros (`filters`), tabelas (`tables`), abas (`tabs`), tooltips (`tooltips`), popovers (`popovers`) e ícones (`icons`). Cada pasta contém um README com exemplos e opções. A página `/components-preview` apresenta esses componentes com dados fictícios e interações demonstrativas.

Os ícones da preview usam a fonte Phosphor Web via CDN, fixada na versão 2.1.2, sem baixar arquivos de ícones para o projeto. O inventário e as variações preto/branco estão documentados em `resources/views/components/icons/README.md`. O uso do CDN requer conexão com a internet.

#### Colaboradores:
  - Enzo Alberti:
  - Jerferson Freitas:jeefreitas315@gmail.com
  - Pedro Pinheiro:
  - Marcelo Monteiro:
  - Marcos Paulo Cruz:
  - Miguel Beral:
