#!/bin/bash

set -e  # Прерываем выполнение при ошибке

echo "🚀 Начинаем деплой на $(date)"

BRANCH="master"

echo "📂 Переходим в директорию проекта: $PROD_DIR"
cd "$PROD_DIR"

echo "🔄 Обновляем код из GitHub..."
git pull git@github.com:D1skord/SiteMonitoring.git $BRANCH

echo "🚀 Перезапускаем контейнеры..."
make COMPOSE="docker compose --env-file .env -f docker-compose.prod.yml" build
echo "🚀 Запуск очередей..."
make COMPOSE="docker compose --env-file .env -f docker-compose.prod.yml" msg


echo "✅ Деплой завершён!"
