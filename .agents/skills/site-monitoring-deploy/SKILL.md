---
name: site-monitoring-deploy
description: Use when working in the Site Monitoring project and the user asks to deploy, release, "раскатай", "отправляем и раскатываем", "выкати на prod", or run the production rollout flow.
---

# Site Monitoring Deploy

Используй этот skill только для проекта Site Monitoring.

## Правила

- Общайся с пользователем на русском, если он не попросил иначе.
- Не запускай deploy без явного подтверждения пользователя.
- Не раскрывай значения secrets из `.env*`, GitHub Secrets, Docker compose и CI logs.
- Не трогай `docker/data/`.
- Не откатывай и не удаляй чужие незакоммиченные изменения.
- Если есть несвязанные изменения в worktree, остановись и уточни у пользователя, какие файлы входят в релиз.

## Production-схема

- GitHub repo: `D1skord/SiteMonitoring`.
- Production domain: `sitemonitoring.vinichenko-ivan.ru`.
- GitHub secret `VDS_HOST` должен быть IP VDS, не домен.
- GitHub secret `DOMAIN_NAME` должен быть `sitemonitoring.vinichenko-ivan.ru`.
- Верхний nginx на VDS проксирует `sitemonitoring.vinichenko-ivan.ru` на `127.0.0.1:8082`.
- GitHub secret `NGINX_PORT` должен быть `8082`.
- Production project path: `/var/www/vinichenko/data/www/sitemonitoring.vinichenko-ivan.ru`.
- Production deploy использует `docker-compose.prod.yml`.
- На сервере может быть старый `docker-compose`, поэтому `docker-compose.prod.yml` должен оставаться совместимым с `version: "3.3"` и без `${VAR:-default}`.
- Production queue worker - отдельный service `worker`, который запускает `php bin/console messenger:consume async -vv`.

## Flow

1. Проверить состояние:
   - `git status --short --branch`;
   - убедиться, что в worktree нет несвязанных чужих правок.
2. Проверить изменения:
   - для PHP-файлов запустить `php -l path/to/file.php`;
   - если менялся `docker-compose.prod.yml`, запустить `docker compose --env-file .env.example -f docker-compose.prod.yml config --quiet`;
   - для широких изменений запустить `cp .env.example .env && cp .env.test.example .env.test && make test`.
3. После успешных проверок:
   - `git add` только релевантных файлов;
   - `git commit -m "..."`;
   - `git push origin master`.
4. Проверить GitHub Actions:
   - `gh run list --repo D1skord/SiteMonitoring --limit 5`;
   - найти CI run для текущего commit;
   - дождаться зеленого CI через `gh run watch <run_id> --repo D1skord/SiteMonitoring --exit-status`.
5. Запустить ручной deploy:
   - `gh workflow run Deploy --repo D1skord/SiteMonitoring --ref master`;
   - найти deploy run через `gh run list --repo D1skord/SiteMonitoring --workflow Deploy --limit 3`;
   - дождаться результата через `gh run watch <run_id> --repo D1skord/SiteMonitoring --exit-status`.
6. После deploy проверить сайт:
   - `curl --max-time 15 -I http://sitemonitoring.vinichenko-ivan.ru/`;
   - `curl --max-time 15 -k -I https://sitemonitoring.vinichenko-ivan.ru/`;
   - если TLS-сертификат невалиден, отдельно сказать, что приложение отвечает, но верхний nginx или сертификат требуют настройки.

## Отчет пользователю

В финальном ответе кратко укажи:

- какие проверки прошли;
- какой commit был отправлен;
- какой GitHub Actions run прошел;
- прошел ли deploy;
- что вернули production smoke checks.
