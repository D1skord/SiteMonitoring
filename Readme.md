# Site Monitoring Project

## Описание

Site Monitoring Project - это Symfony-приложение для мониторинга сайтов.

Проект умеет:

- хранить список сайтов;
- проверять HTTP-статусы сайтов;
- вести историю проверок статусов;
- проверять срок действия домена;
- проверять срок действия SSL-сертификата;
- хранить дату следующей оплаты поддержки сайта;
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
- `.agents/AGENTS.md` - onboarding и карта проекта для AI-агентов

## Для AI-Агентов

Перед работой с проектом агентам нужно прочитать:

```text
.agents/AGENTS.md
```

В этом файле описаны архитектура, доменная модель, команды, проверки, риски окружения и правила безопасной работы с репозиторием.

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
- **Проверка даты оплаты поддержки:**
  ```sh
  make check-site-payment-date
  ```
