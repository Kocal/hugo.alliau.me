ROOT_DIR := $(dir $(realpath $(lastword $(MAKEFILE_LIST))))
include $(ROOT_DIR)/tools/make/text.mk
include $(ROOT_DIR)/tools/make/help.mk
include $(ROOT_DIR)/tools/make/os.mk
include $(ROOT_DIR)/tools/make/git.mk

.DEFAULT_GOAL := help

# Executables
SF := symfony
SF_PROXY = $(shell $(SF) local:proxy:url)
SF_CONSOLE := $(SF) console
PHP := $(SF) php
COMPOSER := $(SF) composer

USER := $(shell id -u)
GROUP := $(shell id -g)
DC := USER_ID=$(USER) GROUP_ID=$(GROUP) $(shell docker compose --env-file /dev/null > /dev/null 2>&1 && echo 'docker compose --env-file /dev/null' || echo 'docker-compose --env-file /dev/null')

## Install everything needed to start the project
install:
	$(SF) local:server:ca:install
	$(MAKE) start
	$(MAKE) app.install

## Start the environment
start:
	$(SF) proxy:start
	$(SF) serve

## Stop the environment
stop:
	$(DC) kill

## Stop and delete the environment and project data (database, logs, etc.)
delete:
	$(DC) down -v --remove-orphans

## App - Install the application
app.install:
	@$(call action, Installing PHP dependencies...)
	$(COMPOSER) install --prefer-dist

	@$(call action, Running DB migrations...)
	$(SF_CONSOLE) doctrine:migrations:migrate --no-interaction --all-or-nothing

## App - Install the application (alias to "make app.install")
app.update: app.install

######
# QA #
######

## QA - Run all QA checks
qa: cs lint phpstan

################
# Coding style #
################

## Coding style - Run all coding style checks
cs: cs.back cs.front

## Coding style - Run all coding style checks and fix issues
cs.fix: cs.back.fix cs.front.fix

## Coding style - Check backend coding style
cs.back:
	$(PHP) vendor/bin/ecs check
	$(PHP) vendor/bin/twig-cs-fixer

## Coding style - Check backend coding style and fix issues
cs.back.fix:
	$(PHP) vendor/bin/ecs check --fix
	$(PHP) vendor/bin/twig-cs-fixer --fix

## Coding style - Check frontend coding style
cs.front:
ifdef CI
	$(SF_CONSOLE) biomejs:ci . --linter-enabled=false
else
	$(SF_CONSOLE) biomejs:check . --linter-enabled=false
endif

## Coding style - Check frontend coding style and fix issues
cs.front.fix:
	$(SF_CONSOLE) biomejs:check . --linter-enabled=false --apply-unsafe

##########
# Linter #
##########

## Linter - Run all linters
lint: lint.back lint.front

## Linter - Run all linters and fix issues
lint.fix: lint.back lint.front.fix

## Linter - Run linters for backend
lint.back:
	$(SF_CONSOLE) lint:container
	$(SF_CONSOLE) lint:xliff translations
	$(SF_CONSOLE) lint:yaml --parse-tags config
	$(SF_CONSOLE) lint:twig templates
	#$(SF_CONSOLE) doctrine:schema:validate

## Coding style - Check frontend coding style
lint.front:
ifdef CI
	$(SF_CONSOLE) biomejs:ci . --formatter-enabled=false
else
	$(SF_CONSOLE) biomejs:check . --formatter-enabled=false
endif

## Coding style - Check frontend coding style and fix issues
lint.front.fix:
	$(SF_CONSOLE) biomejs:check . --formatter-enabled=false --apply-unsafe

###########
# PHPStan #
###########

## PHPStan - Run PHPStan
phpstan:
	$(PHP) vendor/bin/phpstan analyse

## PHPStan - Run PHPStan and update the baseline
phpstan.generate-baseline:
	$(PHP) vendor/bin/phpstan analyse --generate-baseline

-include $(ROOT_DIR)/Makefile.local
