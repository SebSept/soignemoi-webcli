! This repository is at an early stage of development.
I use it to fast start my own projects/tests.  

---
# PHP / Docker / Composer Project Bedrock

Ready to use repository for any symfony project.
This provides a Docker image and files to bootstrap a Symfony project.

## Motivation

Avoid doing always the same things when starting a composer project.
Learn Docker, php server admin.

## Features

- based on php-fpm (alpine linux)
- xdebug, intl, pdo_pgsql
- composer
- fish shell
- symfony cli
- psysh
- just file for just task runner

### Justfile

Shortcuts to run commands in the container using [just task runner](https://github.com/casey/just) 
in `.justfile`  to help with common tasks.

- up # docker-compose up -d
- update: && tests # update source files + docker compose down+up
- fish # open a fish shell on the container
- serve # start symfony dev server
- new-controller
- new-api # new api controller + migrations
- db-create # drop and recreates the db (for dev)
- db-migrate # {{console}} doctrine:migrations:migrate --no-interaction
- db-create-test # create test db
- db-fixtures-make # create fixtures in dev db
- make:fixtures
- db-fixtures-load
- console # run a symfony console
- req package # composer req
- req-dev package
- tests # run phpunit tests
- test # run a single test
- make-test
- sql # run sql command using {{console}} dbal:run-sql
- psysh

## Usage - Init

!needs improvements

```shell
# create a new project
git clone https://github.com/SebSept/php-starter --depth 1
mv php-starter/ my-project/
cd my-project/
rm -rf .git
git init
git add .
git commit -m "Initial commit"

# check the port in docker-compose.yml # @todo use an override file or .env
docker-compose up -d
# install composer dependencies
docker compose exec -it -u climber php composer install
# start php dev server
docker composer exec -it -u climber php symfony server:start --no-tls --daemon 
# access the app in browser
xdg-open http://localhost:8002 # change the port according to previous step
```

