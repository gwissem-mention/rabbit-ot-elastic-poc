.DEFAULT_GOAL := help
.PHONY: clean help

DOCKER_COMPOSE=docker-compose
TOOLS=$(DOCKER_COMPOSE) run --rm app

start: docker-build docker-up install ## Install and start the project

stop: ## Remove docker containers
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) rm -v --force

install: vendor ## Install Symfony (dependencies, the database)
	$(TOOLS) chmod -R 777 var
	$(TOOLS) wait-for-it mysql:3306
	$(TOOLS) bin/console doctrine:database:create --if-not-exists
	$(TOOLS) bin/console doctrine:schema:update --force --dump-sql
	
clean: ## Remove generated files (vendors, cache, logs, ...)
	$(TOOLS) rm -rf log/*.log web/bundles/*

reset: stop start ## Reset the whole project

docker-build:
	$(DOCKER_COMPOSE) build

docker-up:
	$(DOCKER_COMPOSE) up -d --remove-orphans

vendor:
	$(TOOLS) composer install

help:
	@grep -E '^[a-zA-Z0-9_-%]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'


