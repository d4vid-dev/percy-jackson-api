# Percy Jackson API

API REST desenvolvida em **Laravel 12**, baseada na estrutura do projeto de exemplo enviado.

A API contém 60 personagens de **Percy Jackson e os Olimpianos**, com os seguintes campos:

- nome
- descrição
- data de nascimento
- idade
- poderes
- raça
- parentesco divino
- imagem

> O campo `imagem` foi mantido conforme os dados fornecidos. Ele contém a referência/nome da imagem e pode ser substituído depois por uma URL pública.

## Requisitos

- PHP 8.2 ou superior
- Composer
- SQLite (configuração padrão do projeto) ou outro banco suportado pelo Laravel

## Como executar

```bash
composer install
```

Copie o arquivo de ambiente caso necessário:

```bash
cp .env.example .env
```

No Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

Crie o banco SQLite caso ainda não exista:

Linux/macOS:

```bash
touch database/database.sqlite
```

Windows PowerShell:

```powershell
New-Item database/database.sqlite -ItemType File
```

Execute as migrations e popule os 60 personagens:

```bash
php artisan migrate:fresh --seed
```

Inicie a API:

```bash
php artisan serve
```

A API ficará normalmente disponível em:

```text
http://127.0.0.1:8000/api
```

## Endpoints

### Listar todos

```http
GET /api/personagens
```

### Buscar por ID

```http
GET /api/personagens/{id}
```

Exemplo:

```http
GET /api/personagens/1
```

### Filtros

Por nome:

```http
GET /api/personagens?nome=Percy
```

Por raça:

```http
GET /api/personagens?raca=Semideus
```

Por parentesco divino:

```http
GET /api/personagens?parentesco_divino=Poseidon
```

Os filtros podem ser combinados.

### Criar personagem

```http
POST /api/personagens
Content-Type: application/json
```

Exemplo:

```json
{
  "nome": "Novo Personagem",
  "descricao": "Descrição do personagem",
  "data_nascimento": "Não informada",
  "idade": "Adolescente",
  "poderes": "Poderes do personagem",
  "raca": "Semideus grego",
  "parentesco_divino": "Filho de uma divindade",
  "imagem": "Novo Personagem"
}
```

### Atualizar completamente

```http
PUT /api/personagens/{id}
```

### Atualizar parcialmente

```http
PATCH /api/personagens/{id}
```

### Excluir

```http
DELETE /api/personagens/{id}
```

## Estrutura principal criada

```text
app/
├── Http/
│   └── Controllers/
│       └── PersonagemController.php
└── Models/
    └── Personagem.php

database/
├── migrations/
│   └── 2026_09_14_000001_create_personagens_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── PersonagemSeeder.php

routes/
└── api.php
```

## Banco

A tabela `personagens` possui:

```text
id
nome
descricao
data_nascimento
idade
poderes
raca
parentesco_divino
imagem
created_at
updated_at
```
