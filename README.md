# Quizur 🧪✨
> **O melhor grupo do 1° química manhã**

O **Quizur** é uma plataforma web completa e interativa de quizzes desenvolvida em conformidade com todas as diretrizes e regras de negócio do `requisitos.md`. Os usuários podem criar contas, fazer login, buscar quizzes por tema (com conteúdo inspirado no [quizonline.com.br](https://quizonline.com.br/) e especialidades de química), criar e gerenciar seus próprios quizzes com alternativas e explicações didáticas, além de visualizar gráficos interativos de desempenho ao final de cada teste.

---

## 🎨 Identidade Visual e Estilo
Conforme as regras do projeto, as cores são majoritariamente **rosa, azul e roxo**, com estética moderna, efeitos de brilho (glow), glassmorphism e design totalmente responsivo priorizando o **Mobile-First** com **Tailwind CSS**.

- **Rosa:** `#ec4899` / `#f43f5e`
- **Roxo:** `#8b5cf6` / `#7c3aed`
- **Azul:** `#3b82f6` / `#06b6d4`

---

## 🚀 Funcionalidades Principais

1. **Autenticação Segura com Bcrypt:**
   - Cadastro e login de usuários com hash seguro de senhas via `password_hash($senha, PASSWORD_BCRYPT)`.
   - Sessões nativas do PHP com regeneração de ID de sessão contra *Session Fixation*.

2. **Criação e Gestão de Quizzes:**
   - Criação de novos quizzes com categorias, nível de dificuldade, pontuação por pergunta e imagem de capa.
   - Construtor interativo de perguntas dinâmicas e alternativas (A, B, C, D...), com seleção da resposta correta e explicação didática.
   - Edição e exclusão de quizzes pelo autor ou administrador.

3. **Busca e Exploração por Tema Desejado:**
   - Busca em tempo real na página inicial e na página dedicada `/quizzes.php`.
   - Filtros por categoria (Química & Ciências, Conhecimentos Gerais, Cultura Pop, Geografia, Tecnologia & Games) e por nível de dificuldade.
   - Quizzes iniciais já populados e prontos para jogar, inspirados no *quizonline.com.br* e temas de química da turma!

4. **Sessão de Resultados com Gráfico de Desempenho:**
   - Pontuação calculada com base na quantidade de perguntas respondidas.
   - **Gráfico Interativo (Chart.js)** exibindo proporção de acertos, erros e precisão percentual com as cores da identidade visual (rosa, azul e roxo).
   - Gabarito didático completo comparando a resposta assinalada pelo usuário com a resposta correta e exibindo explicações científicas/curiosidades.

5. **Página de Históricos e Logs de Auditoria (Exclusivo Administrador):**
   - Rastreamento contínuo de **qualquer alteração** realizada no sistema (cadastros, logins, tentativas falhas, criação, edição e exclusão de quizzes, partidas jogadas).
   - Acesso estritamente bloqueado para usuários comuns e visitantes (redirecionamento com notificação Toast).
   - Tabela com filtros de pesquisa por ação, usuário, IP e data.

6. **Interface com Toasts e Modais Nativos:**
   - Mensagens de sucesso, aviso e erro apresentadas via **Toasts flutuantes elegantes**, sem recarregar a tela desnecessariamente.
   - Substituição total de caixas de diálogo nativas do navegador (`alert`, `confirm`, `prompt`) por **Modais Universais personalizados**.

---

## 🛠️ Stack Tecnológica

- **Backend:** PHP 8+ estruturado com sessões nativas (`session_start()`).
- **Banco de Dados:** MySQL com **PDO** (Prepared Statements) para segurança total contra SQL Injection. Possui camada com fallback transparente para SQLite caso o servidor MySQL local esteja desligado durante testes rápidos.
- **Frontend:** HTML5, PHP, CSS3 customizado, **Tailwind CSS** (CDN), FontAwesome 6 Icons e **Chart.js**.

---

## 📁 Estrutura de Arquivos Modular (Não-Monolítica)

```
quizur/
├── config/
│   └── bd.php             # Conexão PDO com MySQL (prepared statements e migração automática)
├── bd.php                 # Atalho raiz para inclusão da conexão
├── helpers/
│   ├── auth.php           # Funções de autenticação, checagem de admin, CSRF e Toasts
│   └── logger.php         # Sistema de auditoria e registro de logs no banco
├── actions/
│   ├── auth_login.php     # Processamento de login com verificação bcrypt
│   ├── auth_register.php  # Cadastro com validação e hash bcrypt
│   ├── auth_logout.php    # Encerramento de sessão
│   ├── quiz_create.php    # Criação de quiz e perguntas em transação segura
│   ├── quiz_edit.php      # Atualização de quiz pelo autor ou admin
│   ├── quiz_delete.php    # Exclusão segura com log de auditoria
│   └── quiz_submit.php    # Cálculo da pontuação e registro de desempenho
├── includes/
│   ├── header.php         # Cabeçalho HTML, Tailwind, Chart.js e fontes
│   ├── navbar.php         # Barra de navegação responsiva Mobile First
│   ├── footer.php         # Rodapé com branding do 1° Química Manhã
│   ├── toasts.php         # Componente de notificações Toast
│   └── modals.php         # Modal universal do sistema
├── assets/
│   ├── css/custom.css     # Paleta rosa/azul/roxo, gradientes e estilos
│   └── js/main.js         # Manipulação dos Toasts e Modais
├── index.php              # Página inicial com busca rápida e destaques
├── login.php              # Tela de login
├── registro.php           # Tela de cadastro
├── quizzes.php            # Catálogo e busca do quiz pelo tema desejado
├── quiz_view.php          # Visualização e detalhes antes de jogar
├── quiz_play.php          # Interface dinâmica para responder as perguntas
├── quiz_resultado.php     # Sessão com gráfico Chart.js e desempenho
├── quiz_criar.php         # Formulário de criação de quiz
├── quiz_editar.php        # Formulário de edição de quiz
├── meus_quizzes.php       # Painel do aluno com seus quizzes e partidas
├── historico.php          # Página de auditoria exclusiva para Administrador
├── database.sql           # Script SQL completo para MySQL / phpMyAdmin
└── README.md
```

---

## 🏃 Como Executar Localmente

### Opção 1: Servidor Embutido do PHP (Rápido e Imediato)
No terminal, dentro da pasta do projeto:
```bash
php -S localhost:8000
```
Acesse no seu navegador: **`http://localhost:8000`**

### Opção 2: XAMPP / WampServer / Laragon
1. Copie a pasta `quizur` para a pasta `htdocs` ou `www`.
2. No phpMyAdmin ou terminal MySQL, importe o arquivo **`database.sql`**.
3. Acesse **`http://localhost/quizur`**.
*(Por padrão, `config/bd.php` conecta em `localhost:3306`, usuário `root` e senha vazia).*

---

## 🔑 Contas Pré-Cadastradas para Avaliação Rápida

| Perfil | E-mail | Senha | Acesso |
| :--- | :--- | :--- | :--- |
| **👑 Administrador** | `admin@quizur.com` | `admin123` | Acesso total + Página de Auditoria / Logs (`historico.php`) |
| **🎓 Aluno (1° Química)** | `quimica@quizur.com` | `quimica123` | Criar quizzes, jogar e acompanhar desempenho |
| **👩 Aluna (Sofia)** | `sofia@quizur.com` | `aluno123` | Criar quizzes, jogar e acompanhar desempenho |

*Também é possível cadastrar novos usuários livremente na tela de registro.*
