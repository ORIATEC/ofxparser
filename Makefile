# Docker
.PHONY: init
init: start composer-install

.PHONY: shell
shell:
	docker exec -ti $(shell docker ps -qf "name=php") /bin/sh

.PHONY: down
down:
	@echo -e '\n\e[1;96m>>Stop containers\e[0m'
	docker compose down --remove-orphans

.PHONY: start
start:
	@echo -e '\n\e[1;96m>> Start containers\e[0m'
	docker compose up -d

.PHONY: build
build:
	@echo -e '\n\e[1;96m>> Build containers\e[0m'
	docker compose build --no-cache

# Deploy vendor
.PHONY: composer-install
composer-install:
	@echo -e '\n\e[1;96m>> Install composer dependencies\e[0m'
	docker exec $(shell docker ps -qf "name=php") composer install --prefer-source --no-interaction


# Code quality
.PHONY: unit
unit:
	@echo -e '\n\e[1;96m>> Run unit tests\e[0m'
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpunit

.PHONY: fix
fix:
	@echo -e '\n\e[1;96m>> Fix code style\e[0m'
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpcbf

.PHONY: phpcs
phpcs:
	@echo -e '\n\e[1;96m>> Check code style\e[0m'
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpcs

.PHONY: coverage
coverage:
	@echo -e '\n\e[1;96m>> Test coverage\e[0m'
	docker exec $(shell docker ps -qf "name=php") vendor/bin/phpunit --coverage-text
