
SHELL=/bin/sh
PHP=/usr/bin/php
PHPDOC=../../phpdoc/phpdoc

CURRENT_PATH = $(shell pwd)

ifdef DISTPATH
DISTPATHSWITCH="-D MAIN_TARGET_PATH=$(DISTPATH)"
else
DISTPATH=_dist
DISTPATHSWITCH=
endif
ifdef TESTPATH
TESTPATHSWITCH="-D MAIN_TARGET_PATH=$(TESTPATH)"
else
TESTPATH=_dev
TESTPATHSWITCH=
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
	$(PHP) build/buildjelix.php $(DISTPATHSWITCH) build/config/jelix-dist-dev.ini

jelix-dist-opt:
	$(PHP) build/buildjelix.php $(DISTPATHSWITCH) build/config/jelix-dist-opt.ini

jelix-test:
	$(PHP) build/buildjelix.php $(TESTPATHSWITCH) build/config/jelix-test.ini

testapp-dist:
	$(PHP) build/buildapp.php $(DISTPATHSWITCH) build/config/testapp-dist.ini

testapp-test:
	$(PHP) build/buildapp.php $(TESTPATHSWITCH) build/config/testapp-test.ini

myapp-dist:
	$(PHP) build/buildapp.php $(DISTPATHSWITCH) build/config/myapp-dist.ini

myapp-test:
	$(PHP) build/buildapp.php $(TESTPATHSWITCH) build/config/myapp-test.ini

jtpl-dist:
	$(PHP) build/buildjtpl.php $(DISTPATHSWITCH) build/config/jtpl-dist.ini

jtpl-test:
	$(PHP) build/buildjtpl.php $(TESTPATHSWITCH) build/config/jtpl-test.ini

jbt-dist:
	$(PHP) build/buildjbt.php $(DISTPATHSWITCH) build/config/jbt-dist.ini

jbt-test:
	$(PHP) build/buildjbt.php $(TESTPATHSWITCH) build/config/jbt-test.ini

modules-dist:
	$(PHP) build/buildmodules.php $(DISTPATHSWITCH) build/config/modules-dist.ini

modules-test:
	$(PHP) build/buildmodules.php $(TESTPATHSWITCH) build/config/modules-test.ini


docs: jelix-test
	$(PHPDOC)  -d $(TESTPATH)/lib/jelix/ -t $(DOCSPATH) \
	-o "HTML:frames:DOM/jelix" -s on -ct "contributor,licence" -i *.ini.php \
	-ti "Jelix API Reference" -ric "README,INSTALL,CHANGELOG,CREDITS,LICENCE,VERSION"
	# -tb $(CURRENT_PATH)/build/phpdoc/
