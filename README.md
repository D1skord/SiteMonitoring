# Site Monitoring Project

## Описание

Site Monitoring Project - это Symfony-приложение для мониторинга сайтов.

Проект умеет:

- хранить список сайтов;
- проверять HTTP-статусы сайтов;
- вести историю проверок статусов;
- проверять срок действия домена;
- проверять срок действия SSL-сертификата;
- отправлять уведомления через асинхронную очередь.

## Стек технологий

- **PHP `^8.4`** - версия указана в `composer.json`
- **Symfony 7.2** - основной backend-фреймворк
- **Doctrine ORM / Migrations** - работа с PostgreSQL и миграциями
- **Twig / Symfony Forms** - интерфейс и формы
- **Symfony Messenger** - асинхронные уведомления
- **Nginx** - веб-сервер и проксирование запросов
- **PostgreSQL** - база данных
- **RabbitMQ** - брокер сообщений
- **PHPUnit / PHPStan** - тесты и статический анализ

## Структура проекта

- `src/` - исходный код приложения
- `config/` - конфигурация Symfony и пакетов
- `templates/` - Twig-шаблоны
- `migrations/` - Doctrine migrations
- `tests/` - unit, functional и integration-тесты
- `docker/` - Dockerfile и настройки образов
- `docker-compose.yml` - базовое локальное окружение разработки
- `docker-compose.test.yml` - изолированное окружение для тестов
- `docker-compose.prod.yml` - compose-файл для production-сборки
- `docker-compose.override.yml.example` - пример локальных override-настроек
- `.env.example` - пример локального окружения разработки
- `.env.test.example` - пример тестового окружения
- `AGENTS.md` - onboarding и карта проекта для AI-агентов

## Для AI-Агентов

Перед работой с проектом агентам нужно прочитать:

```text
AGENTS.md
```

В этом файле описаны архитектура, доменная модель, команды, проверки, риски окружения и правила безопасной работы с репозиторием.

## Подготовка к публикации на GitHub

В репозиторий должны попадать только файлы проекта, примеры окружения и workflow:

- коммитятся `.env.example`, `.env.test.example`, `docker-compose.yml`, `docker-compose.test.yml`, `docker-compose.prod.yml`, `.github/workflows/*`;
- не коммитятся `.env`, `.env.test`, `.env.local`, `docker-compose.override.yml`, `vendor/`, `var/`, `docker/data/`, `.idea/`;
- реальные токены, пароли, chat id и peer id хранятся только локально или в GitHub Secrets.

Перед публикацией полезно проверить состояние:

```sh
git status --short --ignored
docker compose --env-file .env.example config --quiet
docker compose --env-file .env.test.example -f docker-compose.test.yml config --quiet
docker compose --env-file .env.example -f docker-compose.prod.yml config --quiet
```

## Запуск проекта

1. Создайте локальный `.env` из примера:
   ```sh
   cp .env.example .env
   ```
2. При необходимости создайте локальный override:
   ```sh
   cp docker-compose.override.yml.example docker-compose.override.yml
   ```
3. Запустите проект командой:
   ```sh
   make build
   ```
4. После успешного запуска сервис будет доступен по адресу (прописать в hosts):
   ```
   http://site-monitoring.local
   ```

## Команды управления

- **Запуск контейнеров:**
  ```sh
  make up
  ```
- **Остановка контейнеров:**
  ```sh
  make down
  ```
- **Запуск тестов:**
  ```sh
  make phpunit
  ```
- **Запуск обработки всех очередей:**
  ```sh
  make msg
  ```
- **Статический анализ:**
  ```sh
  make phpstan
  ```
- **Все проверки:**
  ```sh
  make test
  ```
- **Создать тестовый env:**
  ```sh
  make test-env-init
  ```
- **Поднять тестовые контейнеры:**
  ```sh
  make test-up
  ```
- **Остановить тестовые контейнеры:**
  ```sh
  make test-down
  ```

## Доменные команды

- **Проверка статусов сайтов:**
  ```sh
  make check-site-status
  ```
- **Проверка срока домена:**
  ```sh
  make check-site-domain-expire siteId=1
  ```
- **Проверка срока SSL-сертификата:**
  ```sh
  make check-site-ssl-expire siteId=1
  ```

## Тесты и CI

GitHub Actions workflow `.github/workflows/ci.yml` запускается на push в `master` и на pull request.

CI выполняет:

1. checkout репозитория;
2. создание `.env` и `.env.test` из example-файлов;
3. запуск `make test` в Docker Compose test-окружении;
4. остановку test-контейнеров.

Локально тот же набор проверок запускается командой:

```sh
cp .env.example .env
cp .env.test.example .env.test
make test
```

## Docker-схема

В проекте три compose-файла:

- `docker-compose.yml` - локальная разработка: `nginx`, `php`, `postgres`, `rabbitmq`;
- `docker-compose.test.yml` - изолированное test-окружение: `php`, `postgres_test`, `rabbitmq_test`;
- `docker-compose.prod.yml` - production: `nginx`, `php`, `worker`, `scheduler`, `postgres`, `rabbitmq`.

Production compose оставлен совместимым со старым `docker-compose`, поэтому в нем используется `version: "3.3"` и переменные без `${VAR:-default}`.

## Deploy

Deploy workflow `.github/workflows/deploy.yml` запускается вручную через `workflow_dispatch`.

Схема деплоя:

1. GitHub Actions собирает production `.env` из GitHub Secrets.
2. `.env` загружается на VDS в `PROD_DIR`.
3. На сервере репозиторий обновляется до `origin/master`.
4. Запускается `docker-compose.prod.yml`, устанавливаются production Composer-зависимости, применяются миграции и очищается prod-cache.
5. Пересоздаются `nginx`, `worker` и `scheduler`.

Для deploy нужны GitHub Secrets:

```text
APP_ENV
APP_SECRET
DATABASE_URL
MESSENGER_TRANSPORT_DSN
TELEGRAM_BOT_TOKEN
TELEGRAM_CHAT_ID
VK_BOT_TOKEN
VK_PEER_ID
VK_API_VERSION
DOMAIN_NAME
NGINX_PORT
TIMEZONE
UID
GID
POSTGRES_USER
POSTGRES_PASSWORD
POSTGRES_DB
POSTGRES_VERSION
POSTGRES_PORT
RABBITMQ_PASS
RABBITMQ_MANAGEMENT_PORT
VDS_HOST
VDS_USER
VDS_PORT
VDS_PASSWORD
PROD_DIR
```

Deploy использует password-based SSH через `sshpass`. Если сервер будет переведен на SSH key-based auth, workflow нужно обновить отдельно.
