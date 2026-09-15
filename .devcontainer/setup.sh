#!/bin/bash

# 1. Instalar dependências do PHP
composer install

# 2. Criar .env se não existir
if [ ! -f .env ]; then
    cp .env.example .env
fi

# 3. Preparar o SQLite
touch database/database.sqlite

# 4. Configurar o .env dinamicamente
# Pegamos o caminho atual do workspace para o SQLite não falhar
WORKSPACE_PATH=$(pwd)

sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=sqlite|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$WORKSPACE_PATH/database/database.sqlite|" .env

# Remove as linhas de MySQL/Postgres para não confundir o Laravel
sed -i '/^DB_HOST=/d' .env
sed -i '/^DB_PORT=/d' .env
sed -i '/^DB_USERNAME=/d' .env
sed -i '/^DB_PASSWORD=/d' .env

# 5. Gerar chave e rodar banco
php artisan key:generate
php artisan migrate --seed

# 6. Setup Frontend
npm install

echo "✅ Ambiente Laravel pronto com SQLite!"