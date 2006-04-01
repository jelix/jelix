SHELL=/bin/sh
PHP=/usr/bin/php

ifndef LIB_VERSION
LIB_VERSION = $(shell cat lib/jelix/VERSION)
endif

ifndef JTPL_VERSION
JTPL_VERSION = $(shell cat lib/jelix/tpl/VERSION)
endif

ifeq ($(LIB_VERSION),SVN)
SVN_REVISION = $(shell svn info | grep -E "Revision|Révision" -m 1 | cut -d ":" -f 2 | cut -d " " -f 2)
LIB_VERSION=SVN-$(SVN_REVISION)
endif

ifeq ($(JTPL_VERSION),SVN)
SVN_REVISION = $(shell svn info | grep -E "Revision|Révision" -m 1 | cut -d ":" -f 2  | cut -d " " -f 2)
JTPL_VERSION=SVN-$(SVN_REVISION)
endif

ifndef DIST
DIST=_dist
endif

DISTJELIX="$(DIST)/jelix-$(LIB_VERSION)"
DISTHACKER="$(DIST)/jelix-svn"
DISTJTPL="$(DIST)/jtpl"

default:
	@echo "target:  "
	@echo "   dist-all dist-jelix dist-testapp dist-myapp"
	@echo "   dev-all dev-jelix dev-jelix-lib dev-myapp dev-testapp"
	@echo "   jtpl jtpl-dist"
	@echo "paramètres facultatifs (valeurs actuelles) :"
	@echo "   DIST : repertoire cible (" $(DIST) ")"
	@echo "   LIB_VERSION : numéro de version de Jelix (" $(LIB_VERSION) ")"
	@echo "   JTPL_VERSION : numéro de version de jtpl standalone (" $(JTPL_VERSION) ")"
	@echo "répertoire de construction des projets:"
	@echo "   distributions jelix testapp myapp : " $(DISTJELIX)
	@echo "   developpement jelix testapp myapp : " $(DISTHACKER)
	@echo "   distribution jtpl : " $(DISTJTPL)

dist-all: dist-jelix dist-testapp dist-myapp jtpl-dist

dev-all: dev-jelix dev-myapp dev-testapp jtpl

dist-jelix: common
	if [ ! -d "$(DISTJELIX)" ] ; then mkdir $(DISTJELIX) ; fi
	export LIB_VERSION=$(LIB_VERSION) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-lib.mn . $(DISTJELIX) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-dev.mn . $(DISTJELIX) \
	&& echo "$(LIB_VERSION)" > "$(DISTJELIX)/lib/jelix/VERSION"
	if [ ! -d "$(DISTJELIX)/temp" ] ; then mkdir $(DISTJELIX)/temp ; fi
	tar czf $(DIST)/jelix-lib-$(LIB_VERSION).tar.gz  -C $(DISTJELIX) lib/ temp/

dist-testapp: common
	if [ ! -d "$(DISTJELIX)" ] ; then mkdir $(DISTJELIX) ; fi
	$(PHP) build/mkdist.php build/manifests/testapp.mn . $(DISTJELIX)
	tar czf $(DIST)/testapp-$(LIB_VERSION).tar.gz  -C $(DISTJELIX) testapp/

dist-myapp: common
	if [ ! -d "$(DISTJELIX)" ] ; then mkdir $(DISTJELIX) ; fi
	$(PHP) build/mkdist.php build/manifests/myapp.mn . $(DISTJELIX)
	tar czf $(DIST)/myapp-$(LIB_VERSION).tar.gz  -C $(DISTJELIX) myapp/

dev-jelix: common
	if [ ! -d "$(DISTHACKER)" ] ; then mkdir $(DISTHACKER) ; fi
	export LIB_VERSION=$(LIB_VERSION) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-lib.mn . $(DISTHACKER) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-dev.mn . $(DISTHACKER) \
	&& echo "$(LIB_VERSION)" > "$(DISTHACKER)/lib/jelix/VERSION"
	if [ ! -d "$(DISTHACKER)/temp" ] ; then mkdir $(DISTHACKER)/temp ; fi

dev-jelix-lib: common
	if [ ! -d "$(DISTHACKER)" ] ; then mkdir $(DISTHACKER) ; fi
	export LIB_VERSION=$(LIB_VERSION) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-lib.mn . $(DISTHACKER) \
	&& echo "$(LIB_VERSION)" > "$(DISTHACKER)/lib/jelix/VERSION"

dev-testapp: common
	if [ ! -d "$(DISTHACKER)" ] ; then mkdir $(DISTHACKER) ; fi
	$(PHP) build/mkdist.php build/manifests/testapp.mn . $(DISTHACKER)

dev-myapp: common
	if [ ! -d "$(DISTHACKER)" ] ; then mkdir $(DISTHACKER) ; fi
	$(PHP) build/mkdist.php build/manifests/myapp.mn . $(DISTHACKER)
	
jtpl: common 
	if [ ! -d "$(DISTJTPL)" ] ; then mkdir $(DISTJTPL) ; fi
	export JTPL_STANDALONE=1 \
	&& $(PHP) build/mkdist.php build/manifests/jtpl-standalone.mn . $(DISTJTPL) \
	&& echo "$(JTPL_VERSION)" > "$(DISTJTPL)/VERSION"
	if [ ! -d "$(DISTJTPL)/temp" ] ; then mkdir $(DISTJTPL)/temp ; fi
	if [ ! -d "$(DISTJTPL)/templates" ] ; then mkdir $(DISTJTPL)/templates ; fi

jtpl-dist: jtpl
	tar czf $(DIST)/jtpl-$(JTPL_VERSION).tar.gz  -C $(DIST) jtpl/


common:
	if [ ! -d "$(DIST)" ] ; then mkdir $(DIST) ; fi

