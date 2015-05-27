
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
ifndef DOCSPATH
DOCSPATH=_docs
endif

ifndef PHPDOC
PHPDOC=../../phpdoc/
endif

default:
	@echo "target:"
	@echo " nightlies : "
	@echo "     générations des packages des nightly build"
	@echo " docs : "
	@echo "     Génération de la doc"

nightlies:
	composer install
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist-dev.ini
	mv $(DISTPATH)/PACKAGE_NAME  $(DISTPATH)/PACKAGE_NAME_DEV
	$(PHP) build/buildjelix.php -D $(DISTPATHSWITCH) ./build/config/jelix-dist-opt.ini
	mv $(DISTPATH)/PACKAGE_NAME  $(DISTPATH)/PACKAGE_NAME_OPT

docs: 
	$(PHP) build/buildjelix.php -D $(TESTPATHSWITCH) ./build/config/jelix-test.ini
#	cp -R -f build/phpdoc/Converters/HTML/frames $(PHPDOC)phpDocumentor/Converters/HTML/
	$(PHPDOC)phpdoc \
	-d $(TESTPATH)/lib/jelix-legacy/ \
	-t $(DOCSPATH) \
	-o "HTML:frames:DOM/jelix" -s on -ct "contributor,licence" -i *.ini.php \
	-ti "Jelix API Reference" -ric "README,INSTALL,CHANGELOG,CREDITS,LICENCE,VERSION,BUILD"
	# -tb $(CURRENT_PATH)/build/phpdoc/
