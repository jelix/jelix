
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

ifdef TESTPATH
TESTPATHSWITCH="MAIN_TARGET_PATH=$(TESTPATH)"
else
TESTPATH=_dev
TESTPATHSWITCH="MAIN_TARGET_PATH=_dev"
endif

ifndef DOCSTARGETPATH
DOCSTARGETPATH=$(CURRENT_PATH)/_docs
endif

ifndef DOCSCACHEPATH
DOCSCACHEPATH=$(CURRENT_PATH)/_docs.cache
endif

ifndef TESTS_PROFILES
TESTS_PROFILES=testapp/var/config/profiles.ini.php.dist
endif

ifndef PHPDOC
PHPDOC=phpdoc
endif

ifndef PHPUNIT
PHPUNIT=phpunit
endif

PHPUNITLOG=${CURRENT_PATH}/${TESTPATH}/temp/tests-phpunit.output.xml
PHPUNITDOXDIR=${CURRENT_PATH}/${TESTPATH}/temp/testdox/
PHPUNITDOX=${PHPUNITDOXDIR}/tests-phpunit.dox.html
PHPUNITCLOVER=${CURRENT_PATH}/${TESTPATH}/temp/tests-phpunit.clover.xml
PHPUNITCOVERAGE=${CURRENT_PATH}/${TESTPATH}/temp/coverage/

ifdef XUNIT_OUTPUT
SIMPLETEST_OUTPUT=--junitoutput
BUILDTESTLOG=> ${CURRENT_PATH}/${TESTPATH}/temp/tests-output.xml
else
SIMPLETEST_OUTPUT=
BUILDTESTLOG=
endif

.PHONY: default
default:
	@echo "target:"
	@echo " nightlies : "
	@echo "     générations des packages des nightly build"
	@echo " docs : "
	@echo "     Génération de la doc"
	@echo "paramètres facultatifs (valeurs actuelles) :"
	@echo "   DISTPATH : repertoire cible pour les distributions (" $(DISTPATH) ")"
	@echo "   TESTPATH : repertoire cible pour developper (" $(TESTPATH) ")"

.PHONY: nightlies
nightlies:
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist-dev.ini
	mv $(DISTPATH)/PACKAGE_NAME  $(DISTPATH)/PACKAGE_NAME_DEV
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist-opt.ini
	mv $(DISTPATH)/PACKAGE_NAME  $(DISTPATH)/PACKAGE_NAME_OPT
	$(PHP) build/buildapp.php -D $(DISTPATHSWITCH) ./build/config/testapp-dist.ini
	$(PHP) build/buildmodules.php -D $(DISTPATHSWITCH) ./build/config/modules-dist.ini

.PHONY: preparetestapp
preparetestapp:
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) ./build/config/jelix-test.ini
	$(PHP) build/buildapp.php -D $(TESTPATHSWITCH) ./build/config/testapp-test.ini
	cd $(TESTPATH) \
	&& cp $(TESTS_PROFILES) testapp/var/config/profiles.ini.php
	cd $(TESTPATH)/testapp/install && $(PHP) installer.php

.PHONY: phpunit
phpunit:
	mkdir -p ${PHPUNITCOVERAGE} ${PHPUNITDOXDIR}
	cd $(TESTPATH)/testapp/tests-jelix/ && $(PHPUNIT) --testdox --log-junit ${PHPUNITLOG} --testdox-html ${PHPUNITDOX} --coverage-clover ${PHPUNITCLOVER} --coverage-html ${PHPUNITCOVERAGE}

.PHONY: simpletest
simpletest:
	cd $(TESTPATH)/testapp/scripts/ && $(PHP) tests.php default:index ${SIMPLETEST_OUTPUT} ${BUILDTESTLOG}

.PHONY: runtests
runtests: phpunit simpletest
	echo "phpunit and simpletest run"

.PHONY: tests
tests: preparetestapp runtests
	echo "Tests complete"

.PHONY: docs
docs: 
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) ./build/config/jelix-test.ini
	cp build/phpdoc/phpdoc.xml $(TESTPATH)
	sed -i -- s!__PARSER_CACHE__!$(DOCSCACHEPATH)!g $(TESTPATH)/phpdoc.xml
	sed -i -- s!__TARGET_PATH__!$(DOCSTARGETPATH)!g $(TESTPATH)/phpdoc.xml
	(cd $(TESTPATH) && $(PHPDOC) project:run)
	cp build/phpdoc/template.css $(DOCSTARGETPATH)/css/
