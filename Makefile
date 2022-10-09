# set all to phony
SHELL=bash

.PHONY: *

mkfile_path := $(abspath $(lastword $(MAKEFILE_LIST)))
current_dir := $(abspath $(patsubst %/,%,$(dir $(mkfile_path))))

THREADS := $(shell docker info -f '{{ .NCPU }}')

export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1
export LOCAL_COMPOSER_HOME=$(shell composer config --global home 2> /dev/null || echo ${HOME}/.config/composer)
export LOCAL_COMPOSER_CACHE_DIR=$(shell composer config --global cache-dir 2> /dev/null || echo ${HOME}/.config/composer/cache)

DOCKER_RUN=@docker run -it --rm \
    --volume=$(shell pwd):/opt/project \
	amsphp-console-cli

DOCKER_RUN_COMPOSER=@docker run -it --rm \
	--volume=${LOCAL_COMPOSER_CACHE_DIR}:/tmp/composer/cache \
	--volume=${LOCAL_COMPOSER_HOME}:/.config/composer \
	--volume=$(shell pwd):/opt/project \
	amsphp-console-cli

DOCKER_RUN_TEST=@docker run -it --rm \
	--volume=$(shell pwd):/opt/project \
	amsphp-console-cli

DOCKER_RUN_XDEBUG_COVERAGE=@XDEBUG_MODE=coverage docker run -it --rm \
	--volume=$(shell pwd):/opt/project \
	amsphp-console-cli

all: build composer-install test

install: env-check docker-lint docker-build composer-install ## Builds the project

build:
	@echo -e "\033[33mBuilding PHP docker images\033[0m"
	@docker build -f docker/cli.Dockerfile -t amsphp-console-cli .

composer-install:  ## Install dependencies with composer, according to the existing composer.lock
	@echo -e "\033[33mInstalling dependencies\033[0m"
	$(DOCKER_RUN_COMPOSER) composer install -n -o

composer-require-checker: env-check ## Checks if all root dependencies are declared
	@echo -e "\033[33mChecking composer requirements\033[0m"
	$(DOCKER_RUN_COMPOSER) vendor/bin/composer-require-checker check --config-file=composer-require-checker.json

composer-validate: env-check ## Runs composer validate
	@echo -e "\033[33mValidating composer.json\033[0m"
	$(DOCKER_RUN_COMPOSER) composer validate --no-check-all --strict

test:
	@echo -e "\033[33mRunning Tests\033[0m"
	$(DOCKER_RUN_TEST) ./vendor/bin/phpunit

run-cmd:
	$(DOCKER_RUN) bin/console $(ARGS)

shell: ## Gives shell access inside the container
	$(DOCKER_RUN) sh

help:
	@echo "\033[33mUsage:\033[0m\n  make [target] [FLAGS=\"val\"...]\n\n\033[33mTargets:\033[0m"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}'
