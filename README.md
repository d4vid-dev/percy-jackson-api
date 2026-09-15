# ⚡ Percy Jackson API

API REST desenvolvida em **Laravel** para disponibilizar informações sobre personagens do universo de **Percy Jackson e os Olimpianos**.

O projeto permite consultar, cadastrar, atualizar e remover personagens, além de realizar buscas utilizando diferentes filtros.

---

## 📖 Sobre o projeto

A **Percy Jackson API** foi desenvolvida com o objetivo de praticar o desenvolvimento de APIs REST utilizando Laravel e organizar informações dos personagens da saga de forma estruturada e acessível.

Atualmente, a API possui **60 personagens cadastrados**, incluindo semideuses, deuses, titãs, monstros, criaturas mitológicas e personagens humanos.

Cada personagem pode possuir as seguintes informações:

* 👤 Nome
* 📝 Descrição
* 🎂 Data de nascimento
* ⏳ Idade
* ⚡ Poderes
* 🧬 Raça
* 🔱 Parentesco divino
* 🖼️ Imagem

---

## 🚀 Tecnologias utilizadas

* **PHP**
* **Laravel 12**
* **SQLite**
* **Composer**
* **REST API**

---

## 📂 Estrutura principal

```text
percy-jackson-api/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── PersonagemController.php
│   │
│   └── Models/
│       └── Personagem.php
│
├── database/
│   ├── migrations/
│   └── seeders/
│       └── PersonagemSeeder.php
│
├── routes/
│   └── api.php
│
├── .env.example
├── artisan
├── composer.json
└── README.md
```

---

## 👥 Personagens

Os personagens iniciais da API estão cadastrados através do:

```text
database/seeders/PersonagemSeeder.php
```

Entre os personagens disponíveis estão:

* Percy Jackson
* Annabeth Chase
* Grover Underwood
* Luke Castellan
* Clarisse La Rue
* Tyson
* Thalia Grace
* Nico di Angelo
* Bianca di Angelo
* Rachel Elizabeth Dare
* Poseidon
* Zeus
* Hades
* Atena
* Ares
* Cronos

E muitos outros.

---

# 🔗 Endpoints

URL base durante o desenvolvimento:

```text
http://127.0.0.1:8000/api
```

## Listar todos os personagens

```http
GET /api/personagens
```

Exemplo:

```text
http://127.0.0.1:8000/api/personagens
```

---

## Buscar personagem por ID

```http
GET /api/personagens/{id}
```

Exemplo:

```text
http://127.0.0.1:8000/api/personagens/1
```

---

## Cadastrar personagem

```http
POST /api/personagens
```

Exemplo de corpo da requisição:

```json
{
    "nome": "Percy Jackson",
    "descricao": "Semideus filho de Poseidon.",
    "data_nascimento": "18 de agosto",
    "idade": "16 anos",
    "poderes": "Hidrocinese, respiração subaquática e comunicação com criaturas marinhas.",
    "raca": "Semideus grego",
    "parentesco_divino": "Filho de Poseidon",
    "imagem": "Percy Jackson"
}
```

---

## Atualizar personagem

```http
PUT /api/personagens/{id}
```

ou:

```http
PATCH /api/personagens/{id}
```

Exemplo:

```text
PUT /api/personagens/1
```

---

## Excluir personagem

```http
DELETE /api/personagens/{id}
```

Exemplo:

```text
DELETE /api/personagens/1
```

---

# 🔎 Filtros

A API também permite realizar pesquisas utilizando parâmetros na URL.

### Buscar por nome

```text
GET /api/personagens?nome=Percy
```

### Buscar por raça

```text
GET /api/personagens?raca=Semideus
```

### Buscar por parentesco divino

```text
GET /api/personagens?parentesco_divino=Poseidon
```

Os filtros também podem ser combinados.

Exemplo:

```text
GET /api/personagens?raca=Semideus&parentesco_divino=Poseidon
```

---

# 📦 Instalação

## 1. Clone o repositório

```bash
git clone https://github.com/SEU-USUARIO/percy-jackson-api.git
```

## 2. Entre na pasta

```bash
cd percy-jackson-api
```

## 3. Instale as dependências

```bash
composer install
```

## 4. Crie o arquivo de ambiente

No Linux/macOS:

```bash
cp .env.example .env
```

No Windows:

```powershell
copy .env.example .env
```

## 5. Gere a chave da aplicação

```bash
php artisan key:generate
```

## 6. Crie e popule o banco de dados

```bash
php artisan migrate:fresh --seed
```

Esse comando executará as migrations e cadastrará os personagens presentes no `PersonagemSeeder`.

## 7. Inicie a API

```bash
php artisan serve
```

O servidor normalmente será iniciado em:

```text
http://127.0.0.1:8000
```

Para acessar os personagens:

```text
http://127.0.0.1:8000/api/personagens
```

---

# 📄 Exemplo de resposta

Uma requisição para:

```http
GET /api/personagens/1
```

pode retornar:

```json
{
    "id": 1,
    "nome": "Perseus \"Percy\" Jackson",
    "descricao": "Protagonista da saga Percy Jackson e os Olimpianos.",
    "data_nascimento": "18 de agosto",
    "idade": "12 anos no início da série e 16 anos em O Último Olimpiano",
    "poderes": "Hidrocinese, respirar debaixo d'água, cura através da água, resistência à pressão submarina e comunicação com criaturas marinhas.",
    "raca": "Semideus grego",
    "parentesco_divino": "Filho de Poseidon",
    "imagem": "Percy Jackson"
}
```

---

# 🛠️ Comandos úteis

Iniciar servidor:

```bash
php artisan serve
```

Executar migrations:

```bash
php artisan migrate
```

Recriar o banco e executar os seeders:

```bash
php artisan migrate:fresh --seed
```

Listar as rotas:

```bash
php artisan route:list
```

---

# 🎯 Objetivo

Este projeto foi desenvolvido para fins de **estudo e portfólio**, colocando em prática conceitos como:

* Desenvolvimento de APIs REST
* Laravel
* Rotas
* Controllers
* Models
* Migrations
* Seeders
* Banco de dados
* CRUD
* Filtros e consultas
* Respostas em JSON

---

# ⚠️ Aviso

Este é um projeto **não oficial**, desenvolvido para fins educacionais.

**Percy Jackson e os Olimpianos** e seus personagens pertencem aos seus respectivos autores e detentores dos direitos autorais.

O projeto não possui vínculo oficial com Rick Riordan ou com os detentores da franquia.

---

# 👨‍💻 Autores

**David do Carmo Rodrigues Vieira**
----------------------
**Beatriz de Barros Souza**
----------------------
**Kayk Eduardo Stefano**
----------------------
**Martha Beatriz Soares Valerio**
