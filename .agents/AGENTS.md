# AGENTS.md

Инструкции и карта проекта для AI-агентов, работающих с Site Monitoring Project.

## Быстрый Старт

- Рабочий стек: Symfony 7.2, PHP `^8.4` по `composer.json`, Doctrine ORM, Twig, Messenger, PostgreSQL, RabbitMQ, Graylog, Docker.
- Приложение мониторит сайты: хранит сайты, HTTP-статусы, даты окончания домена/SSL и даты оплаты поддержки, затем отправляет уведомления через Messenger.
- Основные проверки: `make phpunit`, `make phpstan`, `make test`.
- Основной запуск: `make build`, затем `make up`; приложение ожидается на `http://site-monitoring.local`.

## Правила Работы

- Общайся с пользователем на русском, если он не попросил иначе.
- Перед изменениями изучи релевантные файлы и сформулируй проверяемый критерий успеха.
- Меняй только файлы, напрямую связанные с задачей. Не правь форматирование, имена и соседний код без необходимости.
- Не трогай чужие незакоммиченные изменения. Перед правками проверяй `git status --short`.
- Не читай, не изменяй и не коммить содержимое `docker/data/` без прямого запроса, если оно появилось локально: там могут быть данные PostgreSQL/Graylog.
- Не раскрывай значения секретов из `.env*`, Docker compose и CI. В документации описывай только имена переменных.
- Не запускай destructive-команды, миграции на живой БД, очистку volume/cache/data и деплой без явного подтверждения.
- Для поиска используй `rg`/`rg --files`. Исключай `vendor/`, `var/`, `docker/data/`, `.git/`, `.idea/`.

## Частые Команды

```sh
make help
make build
make up
make down
make phpunit
make phpstan
make test
make msg
make check-site-status
make check-site-domain-expire siteId=1
make check-site-ssl-expire siteId=1
make check-site-payment-date
```

## Проверка После Изменений

- Для PHP-кода минимум запусти `php -l` по измененным PHP-файлам, если контейнер/зависимости недоступны.
- Для бизнес-логики запускай релевантные PHPUnit-тесты или `make phpunit`.
- Для широких изменений запускай `make test`.
- Если проверку нельзя выполнить из-за окружения, явно скажи, какая команда не была выполнена и почему.

## Назначение

Site Monitoring Project - Symfony-приложение для мониторинга сайтов. Оно:

- хранит список сайтов;
- проверяет HTTP-статусы сайтов;
- сохраняет историю статусов;
- проверяет срок действия домена через `whois`;
- проверяет срок действия SSL через `openssl`;
- хранит дату следующей оплаты поддержки;
- отправляет уведомления через Symfony Messenger и обработчики notifier.

## Стек

- PHP `^8.4` из `composer.json`.
- Symfony 7.2.
- Doctrine ORM и Doctrine Migrations.
- Twig и Symfony Forms.
- Symfony Security для логина/регистрации.
- Symfony Messenger с AMQP transport.
- PostgreSQL.
- RabbitMQ.
- Graylog, MongoDB и Elasticsearch для логирования.
- PHPUnit 12, Symfony тестовые пакеты, Liip fixtures, PHPStan level 6.

## Входные Файлы

- `public/index.php` - HTTP entrypoint.
- `bin/console` - Symfony Console.
- `src/Kernel.php` - Symfony kernel.
- `config/services.yaml` - autowire/autoconfigure и регистрация сервисов.
- `config/packages/*.yaml` - Doctrine, Messenger, Security, Twig, Monolog и прочие настройки.
- `docker-compose.yml` - базовое локальное окружение разработки, хранится в Git.
- `docker-compose.test.yml` - изолированное окружение для тестов, хранится в Git.
- `docker-compose.prod.yml` - production compose-файл, хранится в Git.
- `docker-compose.override.yml.example` - пример локального override-файла.
- `.env.example`, `.env.test.example` - примеры окружений.
- `.env`, `.env.test`, `.env.local`, `.env.*.local` - локальные файлы разработчика, не коммитить.

## Доменная Модель

- `App\Entity\Site`
  - `name`, `url`, текущий `status`, массив `transport`;
  - `OneToMany` к `StatusLog`;
  - `OneToOne` к `ExpireDate`;
  - `OneToOne` к `PaymentInfo`.
- `App\Entity\StatusLog`
  - HTTP status, timestamp, ссылка на `Site`.
- `App\Entity\ExpireDate`
  - даты `domain`, `ssl`, `updatedAt`;
  - `updatedAt` обновляется lifecycle callback на persist/update.
- `App\Entity\PaymentInfo`
  - стоимость поддержки и дата оплаты.
- `App\Entity\User`
  - email, password, roles;
  - `getRoles()` всегда добавляет `ROLE_ADMIN`.

## Основные Сценарии

### CRUD сайтов

- Контроллер: `src/Controller/SiteController.php`.
- Форма: `src/Form/SiteType.php`.
- Twig: `templates/site/*.html.twig`.
- После создания сайта dispatch-ится `SiteCheckExpireDateEvent`, listener сразу проверяет домен и SSL.

### Проверка HTTP-статусов

- Команда: `site:check-status`.
- Make target: `make check-site-status`.
- Сервис: `App\Service\StatusLogService`.
- Логика:
  - берет все сайты из `SiteRepository`;
  - делает GET на `Site::url`;
  - если статус не `200`, dispatch-ит `App\Message\Notifier`;
  - пишет `StatusLog`.

### Проверка оплаты поддержки

- Команда: `site:check-payment-date`.
- Make target: `make check-site-payment-date`.
- Сервис: `App\Service\PaymentInfoService`.
- Логика:
  - смотрит `Site::paymentInfo.paymentDate`;
  - если дата меньше или равна текущей дате, отправляет уведомление;
  - переносит дату на период из `App\Model\PaymentInfoModel::PAYMENT_PERIOD_MODIFIER`.

### Проверка домена и SSL

- Команды:
  - `site:check-domain-expire [siteId]`;
  - `site:check-ssl-expire [siteId]`.
- Make targets:
  - `make check-site-domain-expire siteId=1`;
  - `make check-site-ssl-expire siteId=1`.
- Сервис-оркестратор: `App\Service\Site\ExpireDateService`.
- Парсеры:
  - `SiteExpireDateDomain` парсит `paid-till` из `whois`;
  - `SiteExpireDateSSL` парсит `notAfter` из `openssl`.
- Базовый класс `SiteExpireDate` запускает shell-команду через Symfony Process.

### Уведомления

- Сообщение: `App\Message\Notifier`.
- Handler: `App\MessageHandler\NotifierHandler`.
- Transport: `async` из `config/packages/messenger.yaml`.
- Коллекция обработчиков: `App\Service\Notifier\NotifierHandlerCollection`.
- Интерфейс обработчика: `NotifierHandlerInterface`.
- Реализация: `TelegramHandler`.
- Настройки Telegram приходят из env-переменных `TELEGRAM_BOT_TOKEN` и `TELEGRAM_CHAT_ID`; значения токенов/чатов не дублировать в ответах.

## База Данных И Миграции

- Миграции лежат в `migrations/`.
- Основные таблицы: `site`, `status_log`, `expire_date`, `payment_info`, `user`.
- Production-пользователя создавать или сбрасывать через `php bin/console app:user:create email@example.com`; команда интерактивно спросит пароль и работает без dev-зависимостей.
- Создание и применение миграций:

```sh
make migration-create
make migration-migrate
```

- Для тестовой БД:

```sh
make create-test-db
```

## Тесты

- Unit tests: `tests/Unit`.
- Functional tests: `tests/Functional`.
- Integration tests: `tests/Integration`.
- Общие base-классы:
  - `tests/Utils/UnitTest.php`;
  - `tests/Utils/WebTest.php`.
- `phpunit.xml.dist` сейчас включает suites `Unit` и `Functional`; `Integration` не входит в объявленные suites, но тесты физически есть.
- Fixtures:
  - `src/DataFixtures/AppFixtures.php`;
  - `src/DataFixtures/SiteFixture.php`;
  - `src/DataFixtures/StatusLogFixture.php`;
  - `src/DataFixtures/PaymentInfoFixture.php`.

Команды:

```sh
make phpunit
make phpstan
make test
make test-up
make test-down
```

## Docker И Окружение

Сервисы compose:

- `nginx`;
- `php`;
- `postgres`;
- `postgres_test`;
- `rabbitmq`;
- `graylog`;
- `graylog_mongo`;
- `graylog_elasticsearch`;
- в dev compose также есть `phppgadmin`.

Типовой локальный запуск:

```sh
cp .env.example .env
make build
```

Если нужны локальные отличия compose:

```sh
cp docker-compose.override.yml.example docker-compose.override.yml
```

Приложение ожидает host `site-monitoring.local`.

Важно:

- `docker-compose.yml`, `docker-compose.test.yml`, `docker-compose.prod.yml` хранятся в Git.
- `docker-compose.override.yml`, `.env`, `.env.test` игнорируются Git и являются локальными.
- `docker/data/` игнорируется Git; если папка есть локально, не трогай без явного запроса.
- `Makefile` работает через `docker compose exec` по service name, без зависимости от `container_name`.

## CI/CD

- GitHub Actions:
  - `.github/workflows/ci.yml` запускается на push в `master` и pull request;
  - `.github/workflows/deploy.yml` запускается вручную через `workflow_dispatch`.
- CI собирает test Docker-окружение, ставит Composer-зависимости, создает test DB, применяет миграции, запускает PHPUnit и PHPStan через `make test`.
- Deploy работает через GitHub Secrets, password-based SSH на VDS и production `.env`, который создается из secrets во время workflow.
- Не запускать деплой без прямого подтверждения пользователя.

### Операция "Раскатай"

Если пользователь просит "раскатай", "отправляем и раскатываем" или аналогично, действуй по этому чеклисту:

1. Проверить состояние:
   - `git status --short --branch`;
   - убедиться, что в worktree нет несвязанных чужих правок.
2. Проверить изменения:
   - для PHP-файлов `php -l path/to/file.php`;
   - для compose `docker compose --env-file .env.example -f docker-compose.prod.yml config --quiet`, если менялся prod compose;
   - для широких изменений `cp .env.example .env && cp .env.test.example .env.test && make test`.
3. После успешных проверок:
   - `git add` только релевантных файлов;
   - `git commit -m "..."`;
   - `git push origin master`.
4. Проверить GitHub Actions:
   - `gh run list --repo D1skord/SiteMonitoring --limit 5`;
   - дождаться зеленого CI run для текущего commit через `gh run watch <run_id> --repo D1skord/SiteMonitoring --exit-status`.
5. Запустить ручной deploy:
   - `gh workflow run Deploy --repo D1skord/SiteMonitoring --ref master`;
   - найти run через `gh run list --repo D1skord/SiteMonitoring --workflow Deploy --limit 3`;
   - дождаться результата через `gh run watch <run_id> --repo D1skord/SiteMonitoring --exit-status`.
6. После deploy проверить сайт:
   - `curl --max-time 15 -I http://sitemonitoring.vinichenko-ivan.ru/`;
   - `curl --max-time 15 -k -I https://sitemonitoring.vinichenko-ivan.ru/`;
   - если TLS-сертификат невалиден, отдельно сказать, что приложение работает, но верхний nginx/сертификат требуют настройки.

Текущая production-схема:

- GitHub secret `VDS_HOST` должен быть IP VDS, не домен.
- GitHub secret `DOMAIN_NAME` должен быть `sitemonitoring.vinichenko-ivan.ru`.
- Верхний nginx на VDS проксирует `sitemonitoring.vinichenko-ivan.ru` на `127.0.0.1:8082`.
- GitHub secret `NGINX_PORT` должен быть `8082`.
- Production project path: `/var/www/vinichenko/data/www/sitemonitoring.vinichenko-ivan.ru`.
- Production deploy использует `docker-compose.prod.yml`; на сервере может быть старый `docker-compose`, поэтому prod compose держится совместимым с `version: "3.3"` и без `${VAR:-default}`.
- Production queue worker - отдельный service `worker`, который запускает `php bin/console messenger:consume async -vv`.

## Правила Изменений

- Для новой бизнес-логики сначала ищи существующий сервис/команду/тест рядом с нужным сценарием.
- Для CRUD и форм держись Symfony patterns из `SiteController`, `SiteType`, Twig-шаблонов.
- Для новых уведомителей реализуй `NotifierHandlerInterface`.
- Для новых фоновых сценариев используй Symfony Command и при необходимости Messenger.
- Для изменений схемы создавай Doctrine migration, не редактируй БД вручную.
- Для проверки дат/HTTP/уведомлений добавляй unit-тесты на чистую логику и functional/integration-тесты только если нужна БД или контейнер Symfony.

## Зоны Осторожности

- Команды `whois` и `openssl` зависят от внешней сети и системных утилит.
- `PaymentInfoService::isPaymentDateNeedToUpdate()` сравнивает строки формата `d/m/Y`; менять осторожно и только с тестами.
- `NotifierHandler` ожидает, что у сайта есть `transport` и соответствующие notifier handlers.
- В `services.yaml` и `NotifierHandlerInterface` используются похожие, но разные имена тегов (`notifier_handler` и `notifier.handler`); перед правками notifier-системы проверь контейнер и тесты.
- README может отставать от кода: например, версия PHP в README отличается от требования `composer.json`.
