set dotenv-load
docker_php_exec := "docker compose exec -it -u climber php"
symfony := docker_php_exec + " symfony "
composer := symfony + " composer "
console := symfony + "console "
docker_exec_nginx := "docker compose exec -it -u root nginx"
browser := "firefox"

up:
    docker compose up -d

# update source files + docker compose down+up
update: && tests
    git pull
    docker compose down
    docker compose up -d --build
    {{composer}} install
    {{console}} importmap:install
    {{console}} importmap:outdated

# open web browser
browser:
    {{browser}} http://localhost:$NGINX_PORT

# open a fish shell on the container
fish:
    {{docker_php_exec}} fish

[private]
fish_root:
    docker compose exec -it -u root php fish

new-controller:
    {{console}} make:controller

new-api:
    {{console}} make:entity --api-resource
    {{console}} make:migration
    {{console}} doctrine:migrations:migrate --no-interaction

# recréer une base de données
db-create:
    {{console}} doctrine:database:drop --quiet --no-interaction --if-exists --force
    {{console}} doctrine:database:create --quiet --no-interaction
    {{console}} doctrine:schema:create --quiet --no-interaction
    echo "Base de données recréée"

db-migrate:
    {{console}} doctrine:migrations:migrate --no-interaction

db-create-test:
    {{console}} doctrine:database:drop --env=test --force --if-exists
    {{console}} doctrine:database:create --env=test
    {{console}} doctrine:schema:create --env=test

# Création des classes de fixtures
db-fixtures-make entity:
    {{console}} make:fixtures {{entity}}Fixtures

# Insertion des fixtures en base de données
db-fixtures-load:
    {{console}} doctrine:fixture:load --no-interaction

# Lancement scripts d'outil de qualité via composer
composer script:
    {{composer}} {{script}}

rector:
    {{docker_php_exec}} vendor/bin/rector

phpstan:
    {{docker_php_exec}} vendor/bin/phpstan

# Run command in Symfony console
console command:
    {{console}} {{command}}

# composer require
req package:
    {{composer}} req {{package}}

# composer require --dev
req-dev package:
    {{composer}} req {{package}} --dev

# Lancement scripts d'outil de qualité via composer
quality:
    {{composer}} quality

tests format='--testdox':
    {{docker_php_exec}} php vendor/bin/phpunit {{format}}

test filter:
    {{docker_php_exec}} php vendor/bin/phpunit --filter {{filter}}

# création d'un test
# The test type must be one of "TestCase", "KernelTestCase", "WebTestCase", "PantherTestCase"
make-test name type='WebTestCase':
    {{console}} make:test {{type}} {{name}}

# exécution d'une requête SQL
sql query env='dev':
    {{console}} dbal:run-sql "{{query}}" --env {{env}}

# interactive php shell
psysh:
    {{docker_php_exec}} psysh

pre-commit:
    {{composer}} run-script pre-commit

[private]
[confirm("Écraser .git/hooks/pre-commit ?")]
install-pre-commit-hook:
    echo "docker compose exec -u climber php symfony composer run-script pre-commit" > .git/hooks/pre-commit
    {{docker_php_exec}} chmod +x .git/hooks/pre-commit

# firt run docker compose up + composer install + open browser
#[confirm("review settings in .env before continue")]
init:
    @echo press any key to review settings in .env
    @read
    xdg-open .env
    just up
    {{composer}} install
    just db-create-test
    just db-create
    just browser
