.PHONY: help start stop restart build install db jwt cache logs shell test migration db-reset

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

##— 🐳 Docker ——————————————————————————————————————
start: ## Démarre les containers
	docker compose up -d

stop: ## Arrête les containers
	docker compose down

restart: ## Redémarre les containers
	docker compose down && docker compose up -d

build: ## Build et démarre les containers
	docker compose up -d --build

##— 📦 Installation ————————————————————————————————
install: build vendor db jwt cache ## Installation complète du projet

vendor: ## Installe les dépendances PHP
	docker compose exec php composer install

##— 🗄️ Base de données —————————————————————————————
db: ## Crée la BDD et lance les migrations
	docker compose exec php php bin/console doctrine:database:create --if-not-exists
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

db-reset: ## Recrée la BDD from scratch
	docker compose exec php php bin/console doctrine:database:drop --force --if-exists
	docker compose exec php php bin/console doctrine:database:create
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
	docker compose exec php php bin/console doctrine:fixtures:load --no-interaction

##— 🔑 Sécurité ————————————————————————————————————
jwt: ## Génère les clés JWT
	docker compose exec php mkdir -p config/jwt
	docker compose exec php php bin/console lexik:jwt:generate-keypair --overwrite

##— ⚡ Symfony ——————————————————————————————————————
cache: ## Vide le cache
	docker compose exec php php bin/console cache:clear

migration: ## Crée une nouvelle migration
	docker compose exec php php bin/console make:migration

##— 🔍 Utilitaires —————————————————————————————————
logs: ## Affiche les logs en temps réel
	docker compose logs -f

shell: ## Ouvre un shell dans le container PHP
	docker compose exec php sh

test: ## Lance les tests
	docker compose exec php php bin/phpunit