
SHELL=/bin/sh
PHP=/usr/bin/php
PHPDOC=../../phpdoc/

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
ifndef DOCSPATH
DOCSPATH=_docs
endif

default:
	@echo "target:"
	@echo " tests editions : "
	@echo "     jelix-test, jtpl-test, testapp-test, myapp-test, all-test"
	@echo " developers editions : (generate package for users)"
	@echo "     jelix-dist, testapp-dist, myapp-dist, jtpl-dist, jbt-dist, docs-dist, all-dist"
	@echo " productions serveur editions : (generate optimized version with package)"
	@echo "     jelix-dist-opt all-dist-opt"
	@echo " Génération de la doc: "
	@echo "     docs"
	@echo "paramètres facultatifs (valeurs actuelles) :"
	@echo "   DISTPATH : repertoire cible pour les distributions (" $(DISTPATH) ")"
	@echo "   TESTPATH : repertoire cible pour developper (" $(TESTPATH) ")"

all-dist: jelix-dist testapp-dist myapp-dist jtpl-dist jbt-dist

all-dist-opt: jelix-dist-opt

all-test: jelix-test  myapp-test testapp-test jtpl-test

jelix-dist:
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) build/config/jelix-dist-dev.ini

jelix-dist-opt:
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) build/config/jelix-dist-opt.ini

jelix-test:
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) build/config/jelix-test.ini

testapp-dist:
	$(PHP) build/buildapp.php -D $(DISTPATHSWITCH) build/config/testapp-dist.ini

testapp-test:
	$(PHP) build/buildapp.php -D $(TESTPATHSWITCH) build/config/testapp-test.ini

myapp-dist:
	$(PHP) build/buildapp.php -D $(DISTPATHSWITCH) build/config/myapp-dist.ini

myapp-test:
	$(PHP) build/buildapp.php -D $(TESTPATHSWITCH) build/config/myapp-test.ini

jtpl-dist:
	$(PHP) build/buildjtpl.php -D $(DISTPATHSWITCH) build/config/jtpl-dist.ini

jtpl-test:
	$(PHP) build/buildjtpl.php -D $(TESTPATHSWITCH) build/config/jtpl-test.ini

jbt-dist:
	$(PHP) build/buildjbt.php -D $(DISTPATHSWITCH) build/config/jbt-dist.ini

jbt-test:
	$(PHP) build/buildjbt.php -D $(TESTPATHSWITCH) build/config/jbt-test.ini

modules-dist:
	$(PHP) build/buildmodules.php -D $(DISTPATHSWITCH) build/config/modules-dist.ini

modules-test:
	$(PHP) build/buildmodules.php -D $(TESTPATHSWITCH) build/config/modules-test.ini

nightlies:
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/jelix-dist-dev.ini
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/jelix-dist-opt.ini
	$(PHP) build/buildapp.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/testapp-dist.ini
	$(PHP) build/buildjbt.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/jbt-dist.ini
	$(PHP) build/buildjtpl.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/jtpl-dist.ini
	$(PHP) build/buildapp.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/myapp-dist.ini
	$(PHP) build/buildmodules.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/modules-dist.ini


docs: jelix-test
	cp -R -f build/phpdoc/Converters/HTML/frames $(PHPDOC)phpDocumentor/Converters/HTML/
	$(PHPDOC)phpdoc  -d $(TESTPATH)/lib/jelix/ -t $(DOCSPATH) \
	-o "HTML:frames:DOM/jelix" -s on -ct "contributor,licence" -i *.ini.php \
	-ti "Jelix API Reference" -ric "README,INSTALL,CHANGELOG,CREDITS,LICENCE,VERSION,BUILD"
	# -tb $(CURRENT_PATH)/build/phpdoc/
