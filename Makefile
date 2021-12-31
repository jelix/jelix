
SHELL=/bin/sh
ifdef PHPPATH
PHP=$(PHPPATH)
else
PHP=/usr/bin/php
endif

CURRENT_PATH = $(shell pwd)
ifndef JELIX_BRANCH
JELIX_BRANCH=$(shell git rev-parse --abbrev-ref HEAD)
endif
VERSION=$(shell cat lib/jelix-legacy/VERSION)

ifndef DISTPATH
DISTPATH=$(CURRENT_PATH)/_dist
endif
DISTPATHSWITCH="MAIN_TARGET_PATH=$(DISTPATH)"

ifndef DOCSTARGETPATH
DOCSTARGETPATH=$(CURRENT_PATH)/_docs
endif

ifndef DOCSCACHEPATH
DOCSCACHEPATH=$(CURRENT_PATH)/_docs.cache
endif

ifdef TESTPATH
TESTPATHSWITCH="MAIN_TARGET_PATH=$(TESTPATH)"
else
TESTPATH=_dev
TESTPATHSWITCH="MAIN_TARGET_PATH=_dev"
endif

.PHONY: default nightlies deploy_unstable build_unstable build_release deploy_release docs deploy_docs

default:
	@echo "possible targets:"
	@echo " build_unstable, build_release, docs, deploy_unstable, deploy_release,"
	@echo " deploy_docs"

build_unstable:
	composer update --working-dir=build/ --prefer-dist --no-ansi --no-interaction --ignore-platform-reqs --no-progress
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) -D IS_NIGHTLY=1 -D ENABLE_DEVELOPER=1 ./build/config/jelix-dist.ini
	rm -rf $(DISTPATH)/jelix-$(VERSION)

#deprecated
nightlies: build_unstable


deploy_unstable:
	jelix_upload_unstable_package.sh $(DISTPATH)/ "$(CI_DEPLOY_USER)@$(CI_DEPLOY_SERVER)" $(JELIX_BRANCH)

phpdoc:
	curl -o phpdoc --location https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.1.2/phpDocumentor.phar
	chmod +x phpdoc

docs: phpdoc
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) ./build/config/jelix-test.ini
	cp build/phpdoc/phpdoc.xml $(TESTPATH)
	sed -i -- s!__TEMPLATE_PATH__!$(CURRENT_PATH)/build/phpdoc/templates!g $(TESTPATH)/phpdoc.xml
	sed -i -- s!__VERSION__!$(VERSION)!g $(TESTPATH)/phpdoc.xml
	(cd $(TESTPATH) && ../phpdoc)

deploy_docs:
	jelix_publish_apidoc.sh $(DOCSTARGETPATH) "$(CI_DEPLOY_USER)@$(CI_DEPLOY_SERVER)" $(JELIX_BRANCH)

build_release:
	composer update --working-dir=build/ --prefer-dist --no-ansi --no-interaction --ignore-platform-reqs --no-suggest --no-progress
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist.ini
	rm -rf $(DISTPATH)/jelix-$(VERSION)

deploy_release:
	jelix_upload_stable_package.sh $(DISTPATH)/ "$(CI_DEPLOY_USER)@$(CI_DEPLOY_SERVER)" $(JELIX_BRANCH) $(VERSION)
