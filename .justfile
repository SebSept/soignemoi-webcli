set dotenv-load
docker_php_exec := "docker compose -f compose-dev.yaml exec -it php"
composer := docker_php_exec + " composer "
console := docker_php_exec + " ./bin/console "
docker_exec_nginx := "docker compose -f compose-dev.yaml exec -it -u root nginx"
browser := "firefox"

up:
    docker compose -f compose-dev.yaml up -d

# update source files + docker compose down+up
update: && tests
    git pull
    docker compose -f compose-dev.yaml down
    docker compose -f compose-dev.yaml pull
    docker compose -f compose-dev.yaml up -d --build
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
    docker compose -f compose-dev.yaml exec -it -u root php fish

rebuild-php:
    docker compose -f compose-dev.yaml down php
    docker compose -f compose-dev.yaml up php

new-controller:
    {{console}} make:controller

db-create-test:
    {{console}} doctrine:database:drop --env=test --force --if-exists
    {{console}} doctrine:database:create --env=test
    {{console}} doctrine:schema:create --env=test

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
    echo "docker compose -f compose-dev.yaml exec php composer run-script pre-commit" > .git/hooks/pre-commit
    chmod +x .git/hooks/pre-commit

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
