SHELL=/bin/sh
PHP=/usr/bin/php

ifndef LIB_VERSION
LIB_VERSION = $(shell cat lib/jelix/VERSION)
endif

ifndef JTPL_VERSION
JTPL_VERSION = $(shell cat lib/jelix/tpl/VERSION)
endif

ifndef JBT_VERSION
JBT_VERSION = $(shell cat build/VERSION)
endif

SVN_REVISION = $(shell svn info | grep -E "Revision|Révision" -m 1 | cut -d ":" -f 2 | cut -d " " -f 2)

ifeq ($(LIB_VERSION),SVN)
LIB_VERSION=SVN-$(SVN_REVISION)
endif

ifeq ($(JTPL_VERSION),SVN)
JTPL_VERSION=SVN-$(SVN_REVISION)
endif

ifeq ($(JBT_VERSION),SVN)
JBT_VERSION=SVN-$(SVN_REVISION)
endif

ifndef DIST
DIST=_dist
endif
ifndef DEV
DEV=_dev
endif

DISTJELIX="$(DIST)/jelix-$(LIB_VERSION)"
DISTHACKER="$(DEV)"
DISTJTPL="$(DIST)/jtpl"
DEVJTPL="$(DEV)/jtpl"
DISTJBT="$(DIST)/jbuildtools"

default:
	@echo "target:  "
	@echo "   dist-all dist-jelix dist-testapp dist-myapp"
	@echo "   dev-all dev-jelix dev-jelix-lib dev-myapp dev-testapp"
	@echo "   jtpl jtpl-dist"
	@echo "   jbt-dist"
	@echo "paramètres facultatifs (valeurs actuelles) :"
	@echo "   DIST : repertoire cible pour les distributions (" $(DIST) ")"
	@echo "   DEV : repertoire cible pour developper (" $(DEV) ")"
	@echo "   LIB_VERSION : numéro de version de Jelix (" $(LIB_VERSION) ")"
	@echo "   JTPL_VERSION : numéro de version de jtpl standalone (" $(JTPL_VERSION) ")"
	@echo "répertoire de construction des projets:"
	@echo "   distributions jelix testapp myapp : " $(DISTJELIX)
	@echo "   developpement jelix testapp myapp : " $(DISTHACKER)
	@echo "   distribution jtpl : " $(DISTJTPL)

dist-all: dist-jelix dist-testapp dist-myapp jtpl-dist jbt-dist

dev-all: dev-jelix dev-myapp dev-testapp jtpl

dist-jelix: common-dist
	export LIB_VERSION=$(LIB_VERSION) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-lib.mn . $(DISTJELIX) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-dev.mn . $(DISTJELIX) \
	&& echo "$(LIB_VERSION)" > "$(DISTJELIX)/lib/jelix/VERSION"
	tar czf $(DIST)/jelix-lib-$(LIB_VERSION).tar.gz  -C $(DISTJELIX) lib/ temp/

dist-testapp: common-dist
	$(PHP) build/mkdist.php build/manifests/testapp.mn . $(DISTJELIX)
	tar czf $(DIST)/testapp-$(LIB_VERSION).tar.gz  -C $(DISTJELIX) testapp/ temp/testapp/

dist-myapp: common-dist
	$(PHP) build/mkdist.php build/manifests/myapp.mn . $(DISTJELIX)
	tar czf $(DIST)/myapp-$(LIB_VERSION).tar.gz  -C $(DISTJELIX) myapp/ temp/myapp/

common-dist:
	if [ ! -d "$(DIST)" ] ; then mkdir $(DIST) ; fi
	if [ ! -d "$(DISTJELIX)" ] ; then mkdir $(DISTJELIX) ; fi

dev-jelix: 
	if [ ! -d "$(DISTHACKER)" ] ; then mkdir $(DISTHACKER) ; fi
	export LIB_VERSION=$(LIB_VERSION) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-lib.mn . $(DISTHACKER) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-dev.mn . $(DISTHACKER) \
	&& echo "$(LIB_VERSION)" > "$(DISTHACKER)/lib/jelix/VERSION"

dev-jelix-lib:
	if [ ! -d "$(DISTHACKER)" ] ; then mkdir $(DISTHACKER) ; fi
	export LIB_VERSION=$(LIB_VERSION) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-lib.mn . $(DISTHACKER) \
	&& echo "$(LIB_VERSION)" > "$(DISTHACKER)/lib/jelix/VERSION"

dev-testapp: 
	if [ ! -d "$(DISTHACKER)" ] ; then mkdir $(DISTHACKER) ; fi
	$(PHP) build/mkdist.php build/manifests/testapp.mn . $(DISTHACKER)

dev-myapp: 
	if [ ! -d "$(DISTHACKER)" ] ; then mkdir $(DISTHACKER) ; fi
	$(PHP) build/mkdist.php build/manifests/myapp.mn . $(DISTHACKER)
	
jtpl:
	if [ ! -d "$(DEVJTPL)" ] ; then mkdir $(DEVJTPL) ; fi
	export JTPL_STANDALONE=1 \
	&& $(PHP) build/mkdist.php build/manifests/jtpl-standalone.mn . $(DEVJTPL) \
	&& echo "$(JTPL_VERSION)" > "$(DEVJTPL)/VERSION"

jtpl-dist:
	if [ ! -d "$(DISTJTPL)" ] ; then mkdir $(DISTJTPL) ; fi
	export JTPL_STANDALONE=1 \
	&& $(PHP) build/mkdist.php build/manifests/jtpl-standalone.mn . $(DISTJTPL) \
	&& echo "$(JTPL_VERSION)" > "$(DISTJTPL)/VERSION"
	tar czf $(DIST)/jtpl-$(JTPL_VERSION).tar.gz  -C $(DIST) jtpl/

jbt-dist:
	if [ ! -d "$(DISTJBT)" ] ; then mkdir $(DISTJBT) ; fi
	$(PHP) build/mkdist.php build/manifests/jbuildtools.mn build/ $(DISTJBT) \
	&& echo "$(JBT_VERSION)" > "$(DISTJBT)/VERSION"
	tar czf $(DIST)/jbuildtools-$(JBT_VERSION).tar.gz  -C $(DIST) jbuildtools/


