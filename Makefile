
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
VERSION=$(shell cat lib/jelix/VERSION)

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

ifndef PHPDOC
PHPDOC="$(CURRENT_PATH)/build/vendor/bin/phpdoc"
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

docs:
	composer update --working-dir=build/ --prefer-dist --no-ansi --no-interaction --ignore-platform-reqs --no-suggest --no-progress
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) ./build/config/jelix-test.ini
	cp build/phpdoc/phpdoc.xml $(TESTPATH)
	sed -i -- s!__PARSER_CACHE__!$(DOCSCACHEPATH)!g $(TESTPATH)/phpdoc.xml
	sed -i -- s!__TARGET_PATH__!$(DOCSTARGETPATH)!g $(TESTPATH)/phpdoc.xml
	(cd $(TESTPATH) && $(PHPDOC) project:run)
	cp build/phpdoc/template.css $(DOCSTARGETPATH)/css/

deploy_docs:
	jelix_publish_apidoc.sh $(DOCSTARGETPATH) "$(CI_DEPLOY_USER)@$(CI_DEPLOY_SERVER)" $(JELIX_BRANCH)

build_release:
	composer update --working-dir=build/ --prefer-dist --no-ansi --no-interaction --ignore-platform-reqs --no-suggest --no-progress
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist.ini
	rm -rf $(DISTPATH)/jelix-$(VERSION)

deploy_release:
	jelix_upload_stable_package.sh $(DISTPATH)/ "$(CI_DEPLOY_USER)@$(CI_DEPLOY_SERVER)" $(JELIX_BRANCH) $(VERSION)
