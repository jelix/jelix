
SHELL=/bin/sh
ifdef PHPPATH
PHP=$(PHPPATH)
else
PHP=/usr/bin/php
endif

CURRENT_PATH = $(shell pwd)

ifdef DISTPATH
DISTPATHSWITCH="MAIN_TARGET_PATH=$(DISTPATH)"
else
DISTPATH=_dist
DISTPATHSWITCH="MAIN_TARGET_PATH=_dist"
endif

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
PHPDOC=phpdoc
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
	composer install --prefer-dist --no-ansi --no-interaction --ignore-platform-reqs --no-suggest --no-progress
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist.ini
	mv $(DISTPATH)/PACKAGE_NAME  $(DISTPATH)/PACKAGE_NAME

.PHONY: docs
docs: 
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) ./build/config/jelix-test.ini
	composer install --working-dir $(TESTPATH) --prefer-dist --no-ansi --no-interaction --ignore-platform-reqs --no-suggest --no-progress
	cp build/phpdoc/phpdoc.xml $(TESTPATH)
	sed -i -- s!__PARSER_CACHE__!$(DOCSCACHEPATH)!g $(TESTPATH)/phpdoc.xml
	sed -i -- s!__TARGET_PATH__!$(DOCSTARGETPATH)!g $(TESTPATH)/phpdoc.xml
	(cd $(TESTPATH) && $(PHPDOC) project:run)
	cp build/phpdoc/template.css $(DOCSTARGETPATH)/css/
