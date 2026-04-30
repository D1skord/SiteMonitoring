.DEFAULT_GOAL := help

# COLORS
GREEN  := $(shell tput -Txterm setaf 2)
WHITE  := $(shell tput -Txterm setaf 7)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

COMPOSE=docker compose --env-file .env
TEST_COMPOSE=docker compose --env-file .env.test -f docker-compose.test.yml

PHP_SERVICE=php
EXEC=$(COMPOSE) exec -T $(PHP_SERVICE)
EXEC_TTY=$(COMPOSE) exec $(PHP_SERVICE)
CONSOLE=$(EXEC) bin/console
MESSENGER=$(CONSOLE) messenger:consume

TEST_EXEC=$(TEST_COMPOSE) exec -T $(PHP_SERVICE)
TEST_CONSOLE=$(TEST_EXEC) bin/console --env=test

HELP_FUN = \
    %help; \
    while(<>) { push @{$$help{$$2 // 'options'}}, [$$1, $$3] if /^([a-zA-Z\-]+)\s*:.*\#\#(?:@([a-zA-Z\-]+))?\s(.*)$$/ }; \
    print "usage: make [command]\n\n"; \
    for (sort keys %help) { \
    print "${WHITE}$$_:${RESET}\n"; \
    for (@{$$help{$$_}}) { \
    $$sep = " " x (32 - length $$_->[0]); \
    print "  ${YELLOW}$$_->[0]${RESET}$$sep${GREEN}$$_->[1]${RESET}\n"; \
    }; \
    print "\n"; }

help: ##@other Show this help.
	@perl -e '$(HELP_FUN)' $(MAKEFILE_LIST)

env-init: ##@env Create .env from .env.example if it does not exist
	test -f .env || cp .env.example .env

test-env-init: ##@env Create .env.test from .env.test.example if it does not exist
	test -f .env.test || cp .env.test.example .env.test

docker-compose-build: env-init
	$(COMPOSE) down
	$(COMPOSE) up -d --build --force-recreate --remove-orphans

composer-install: env-init
	$(EXEC) git config --global --add safe.directory /var/www/symfony
	$(EXEC) composer install -n

build: docker-compose-build composer-install migration-migrate ##@container Build container

down: env-init ##@container Down container
	$(COMPOSE) down

up: env-init ##@container Start container or restart
	$(COMPOSE) up -d

ps: env-init ##@container Status
	$(COMPOSE) ps

bash: env-init ##@container Bash
	$(EXEC_TTY) bash

cc: env-init ##@symfony Reset cache
	$(EXEC) bin/console cache:clear

deploy: build ##@deploy project

perm: env-init ##Обновление прав на папку `var`
	$(EXEC) chown -R www-data:www-data var

migration-create: env-init ##@symfony Make migration
	$(EXEC) bin/console make:migration

migration-migrate: env-init ##@symfony Run migrations
	$(EXEC) bin/console doctrine:migrations:migrate -n

fixtures-add-all: env-init ##Команда для загрузки всех фикстур
	$(EXEC) bin/console doctrine:fixtures:load

test-up: test-env-init ##@test Start test containers
	$(TEST_COMPOSE) up -d --build --force-recreate --remove-orphans

test-down: test-env-init ##@test Down test containers
	$(TEST_COMPOSE) down

test-composer-install: test-up ##@test Install dependencies in test container
	$(TEST_EXEC) git config --global --add safe.directory /var/www/symfony
	$(TEST_EXEC) composer install -n

create-test-db: test-up ##@test Create the test database
	$(TEST_CONSOLE) doctrine:database:drop --if-exists --force
	$(TEST_CONSOLE) doctrine:database:create
	$(TEST_CONSOLE) doctrine:migrations:migrate --allow-no-migration -n

fixtures-add-all-test: create-test-db ##@test Load fixtures into the test database
	$(TEST_CONSOLE) doctrine:fixtures:load -n

phpunit: create-test-db ##@test Run PHPUnit
	$(TEST_EXEC) rm -rf var/cache/test/*
	$(TEST_CONSOLE) cache:warmup --ansi
	$(TEST_EXEC) bin/phpunit --colors=always

phpstan: test-composer-install ##@test Run PHPStan
	$(TEST_EXEC) vendor/bin/phpstan analyse -c phpstan.neon --memory-limit 2G src tests --ansi

test: test-composer-install create-test-db phpunit phpstan ##@test Run all checks

check-site-status: env-init ##@commands Проверка статуса сайтов
	$(CONSOLE) site:check-status

check-site-domain-expire: env-init ##@commands Проверка даты окончания домен(а/ов)
	$(CONSOLE) site:check-domain-expire $(siteId)

check-site-ssl-expire: env-init ##@commands Проверка даты окончания ssl-сертификата
	$(CONSOLE) site:check-ssl-expire $(siteId)

check-site-payment-date: env-init ##@commands Проверка даты оплаты поддержки
	$(CONSOLE) site:check-payment-date

msg: env-init ##Запуск обработки всех очередей
	$(MESSENGER) async -vv
