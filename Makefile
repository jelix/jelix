
SHELL=/bin/sh
ifdef PHPPATH
PHP=$(PHPPATH)
else
PHP=/usr/bin/php
endif

CURRENT_PATH = $(shell pwd)

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

.PHONY: default
default:
	@echo "target:"
	@echo " nightlies : "
	@echo "     générations des packages des nightly build"
	@echo " docs : "
	@echo "     Génération de la doc"

.PHONY: nightlies
nightlies:
	composer update --working-dir=build/ --prefer-dist --no-ansi --no-interaction --ignore-platform-reqs --no-suggest --no-progress
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) -D IS_NIGHTLY=1 -D ENABLE_DEVELOPER=1 ./build/config/jelix-dist.ini

.PHONY: docs
docs:
	composer update --working-dir=build/ --prefer-dist --no-ansi --no-interaction --ignore-platform-reqs --no-suggest --no-progress
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) ./build/config/jelix-test.ini
	cp build/phpdoc/phpdoc.xml $(TESTPATH)
	sed -i -- s!__PARSER_CACHE__!$(DOCSCACHEPATH)!g $(TESTPATH)/phpdoc.xml
	sed -i -- s!__TARGET_PATH__!$(DOCSTARGETPATH)!g $(TESTPATH)/phpdoc.xml
	(cd $(TESTPATH) && $(PHPDOC) project:run)
	cp build/phpdoc/template.css $(DOCSTARGETPATH)/css/

.PHONY: release
release:
	composer update --working-dir=build/ --prefer-dist --no-ansi --no-interaction --ignore-platform-reqs --no-suggest --no-progress
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist.ini
