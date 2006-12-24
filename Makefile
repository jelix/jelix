
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
	@echo " nightlies : "
	@echo "     génerations des packages des nightly build"
	@echo " docs : "
	@echo "     Génération de la doc"
	@echo "paramètres facultatifs (valeurs actuelles) :"
	@echo "   DISTPATH : repertoire cible pour les distributions (" $(DISTPATH) ")"
	@echo "   TESTPATH : repertoire cible pour developper (" $(TESTPATH) ")"

nightlies:
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/jelix-dist-dev.ini
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/jelix-dist-opt.ini
	$(PHP) build/buildapp.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/testapp-dist.ini
	$(PHP) build/buildjbt.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/jbt-dist.ini
	$(PHP) build/buildjtpl.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/jtpl-dist.ini
	$(PHP) build/buildapp.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/myapp-dist.ini
	$(PHP) build/buildmodules.php -D $(DISTPATHSWITCH) -D NIGHTLY_NAME=1 build/config/modules-dist.ini


docs: 
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) build/config/jelix-test.ini
	cp -R -f build/phpdoc/Converters/HTML/frames $(PHPDOC)phpDocumentor/Converters/HTML/
	$(PHPDOC)phpdoc  -d $(TESTPATH)/lib/jelix/ -t $(DOCSPATH) \
	-o "HTML:frames:DOM/jelix" -s on -ct "contributor,licence" -i *.ini.php \
	-ti "Jelix API Reference" -ric "README,INSTALL,CHANGELOG,CREDITS,LICENCE,VERSION,BUILD"
	# -tb $(CURRENT_PATH)/build/phpdoc/
