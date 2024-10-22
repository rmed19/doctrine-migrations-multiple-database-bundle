UID=$(shell id -u)
GID=$(shell id -g)
DOCKER_PHP_SERVICE=php

SHELL=/bin/bash

.DEFAULT_GOAL := start

start: build composer-install ## Initialize project

.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

build:
		docker build -t doctrine_migrations .

erase: ## Remove the Docker containers of the project.
		docker rmi doctrine_migrations

composer-install: ## Install composer dependencies
		docker run --rm -v .:/app doctrine_migrations composer install

composer-update: ## Update composer dependencies
		docker run --rm -v .:/app doctrine_migrations composer update

phpstan: ## Run PHPStan
		docker run --rm -v ${PWD}:/app ghcr.io/phpstan/phpstan analyse ./src

fix-cs: ## Fix code standards
		docker run --rm -v ${PWD}:/data cytopia/php-cs-fixer fix --verbose --show-progress=dots --rules=@Symfony,-@PSR2 -- src

validate-cs: ## Validate code standards
		docker run --rm -v ${PWD}:/data cytopia/php-cs-fixer fix --dry-run --verbose --show-progress=dots --rules=@Symfony,-@PSR2 -- src

.PHONY: tests
tests: ## Run cs validation, PHPStan and PHPUnit
	make validate-cs
	make phpstan