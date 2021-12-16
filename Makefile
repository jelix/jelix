
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

.PHONY: default nightlies deploy_unstable build_unstable build_release deploy_release docs deploy_docs

default:
	@echo "possible targets:"
	@echo " build_unstable, build_release, docs, deploy_unstable, deploy_release,"
	@echo " deploy_docs"
	@echo ""
	@echo "Environment variables you can set:"
	@echo "   DISTPATH : target directory for builds (" $(DISTPATH) ")"
	@echo "   TESTPATH : target directory to develop (" $(TESTPATH) ")"


build_unstable:
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist-dev.ini
	mv $(DISTPATH)/PACKAGE_NAME  $(DISTPATH)/PACKAGE_NAME_DEV
	rm -rf $(DISTPATH)/jelix-$(VERSION)-dev
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist-opt.ini
	mv $(DISTPATH)/PACKAGE_NAME  $(DISTPATH)/PACKAGE_NAME_OPT
	rm -rf $(DISTPATH)/jelix-$(VERSION)-opt
	$(PHP) build/buildapp.php -D $(DISTPATHSWITCH) ./build/config/testapp-dist.ini
	rm -rf $(DISTPATH)/temp $(DISTPATH)/testapp
	$(PHP) build/buildmodules.php -D $(DISTPATHSWITCH) ./build/config/modules-dist.ini
	rm -rf $(DISTPATH)/additionnal-modules

#deprecated
nightlies: build_unstable


deploy_unstable:
	jelix_upload_unstable_package.sh $(DISTPATH)/ "$(CI_DEPLOY_USER)@$(CI_DEPLOY_SERVER)" $(JELIX_BRANCH)

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

phpdoc:
	curl -o phpdoc --location https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.1.2/phpDocumentor.phar
	chmod +x phpdoc

docs: phpdoc
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) ./build/config/jelix-test.ini
	cp build/phpdoc/phpdoc.xml $(TESTPATH)
	sed -i -- s!__TEMPLATE_PATH__!$(CURRENT_PATH)/build/phpdoc/templates!g $(TESTPATH)/phpdoc.xml
	(cd $(TESTPATH) && ../phpdoc)

deploy_docs:
	jelix_publish_apidoc.sh $(DOCSTARGETPATH) "$(CI_DEPLOY_USER)@$(CI_DEPLOY_SERVER)" $(JELIX_BRANCH)

build_release:
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist-dev.ini
	mv $(DISTPATH)/PACKAGE_NAME  $(DISTPATH)/PACKAGE_NAME_DEV
	rm -rf $(DISTPATH)/jelix-$(VERSION)-dev
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist-opt.ini
	mv $(DISTPATH)/PACKAGE_NAME  $(DISTPATH)/PACKAGE_NAME_OPT
	rm -rf $(DISTPATH)/jelix-$(VERSION)-opt
	$(PHP) build/buildapp.php -D $(DISTPATHSWITCH) ./build/config/testapp-dist.ini
	rm -rf $(DISTPATH)/temp $(DISTPATH)/testapp
	$(PHP) build/buildmodules.php -D $(DISTPATHSWITCH) ./build/config/modules-dist.ini
	rm -rf $(DISTPATH)/additionnal-modules

deploy_release:
	jelix_upload_stable_package.sh $(DISTPATH)/ "$(CI_DEPLOY_USER)@$(CI_DEPLOY_SERVER)" $(JELIX_BRANCH) $(VERSION)

