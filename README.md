# projeto_defeso

# 🌊 Site Educativo sobre o Período de Defeso

Este site foi desenvolvido como parte de um projeto interdisciplinar para informar a população sobre o **Período de Defeso**, com foco em **educação ambiental**, **regras legais**, **direitos do pescador** e **denúncias de pesca ilegal**.

## 🧰 Tecnologias Utilizadas

- HTML5
- CSS3
- PHP (com XAMPP)
- MySQL (phpMyAdmin)
- Bootstrap 5
- Font Awesome (ícones)

## 📁 Estrutura de Páginas

| Página             | Descrição                                                                 |
|--------------------|---------------------------------------------------------------------------|
| `index.html`       | Página inicial com informações gerais e navegação entre seções.           |
| `regras.html`      | Explica as regras do defeso, com tabela de multas e quiz interativo.      |
| `denuncia.html`    | Página para denúncias, com formulário direto e links oficiais.            |
| `direitos.html`    | Detalha os direitos do pescador e verifica elegibilidade ao auxílio.      |
| `educacao.html`    | Página educativa com conteúdo ambiental e espaço para envio de sugestões. |

## 🗂️ Funcionalidades com PHP

- **Cadastro de Denúncia (denuncia.html + PHP)**  
  Envia dados de denúncia diretamente para o banco de dados `site_defeso`.

- **Verificação de Direito ao Auxílio (direitos.html + PHP)**  
  Usuário responde perguntas e, se elegível, recebe confirmação e link oficial.

- **Envio de Sugestões Ambientais (educacao.html + PHP)**  
  Formulário de contribuição com ideias de preservação e boas práticas.

## 🛢️ Banco de Dados

Nome do banco: `site_defeso`

### Tabela: `denuncias`

| Campo        | Tipo         |
|--------------|--------------|
| id           | INT (PK, AI) |
| nome         | VARCHAR(100) |
| estado       | VARCHAR(50)  |
| local        | VARCHAR(100) |
| descricao    | TEXT         |
| data_envio   | TIMESTAMP    |

### Tabela: `sugestoes`

| Campo        | Tipo         |
|--------------|--------------|
| id           | INT (PK, AI) |
| nome         | VARCHAR(100) |
| sugestao     | TEXT         |
| data_envio   | TIMESTAMP    |


## ⚙️ Como Executar Localmente (XAMPP)

1. Baixe e instale o [XAMPP](https://www.apachefriends.org/index.html)
2. Coloque a pasta do site em:  
   `C:\xampp\htdocs\site-defeso`
3. Inicie os serviços do Apache e MySQL
4. Acesse via navegador:  
   `http://localhost/site-defeso/`
5. Crie o banco `site_defeso` via phpMyAdmin

## 🔒 Observações sobre a LGPD

Nenhum dado sensível (como CPF ou telefone) é coletado no site. Todas as denúncias são registradas anonimamente ou de forma opcional.

## 🎯 Objetivo

Informar, conscientizar e ajudar no cumprimento da legislação ambiental, com recursos educativos e práticos, respeitando os direitos dos pescadores e promovendo a fiscalização cidadã.

---

Desenvolvido por: **[Jonatas Gabriel (Desenvolvedor – Trabalho em equipe)]**  
Projeto Acadêmico | Universidade CEUMA  
Ano: 2025
