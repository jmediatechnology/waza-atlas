# Shortcuts for the Docker setup. Run `make` to list them.
DC      = docker compose
DC_PROD = docker compose -f compose.yaml -f compose.prod.yaml
EXEC    = $(DC) exec app

.DEFAULT_GOAL := help
.PHONY: help build up down logs sh console test seed prod prod-down prod-logs

help: ## List the commands
	@grep -E '^[a-z-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-11s\033[0m %s\n", $$1, $$2}'

build: ## Build the development image
	$(DC) build --pull

up: ## Start the dev stack on http://localhost:8080
	$(DC) up --detach --wait

down: ## Stop the dev stack
	$(DC) down --remove-orphans

logs: ## Follow the app logs
	$(DC) logs --tail=100 --follow app

sh: ## Open a shell in the app container
	$(EXEC) bash

console: ## Run a Symfony command, e.g. make console c="debug:router"
	$(EXEC) php bin/console $(c)

test: ## Run the PHPUnit suite inside the container
	$(EXEC) php vendor/bin/phpunit

seed: ## Reload the catalogue and the bundled motions
	$(EXEC) php bin/console app:seed --replace-motions

prod: ## Build and start the production image (needs APP_SECRET)
	$(DC_PROD) up --detach --build --wait

prod-down: ## Stop the production stack (keeps the data volume)
	$(DC_PROD) down

prod-logs: ## Follow the production logs
	$(DC_PROD) logs --tail=100 --follow app
