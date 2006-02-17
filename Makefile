SHELL=/bin/sh
PHP=/usr/bin/php

ifndef LIB_VERSION
LIB_VERSION = $(shell cat lib/jelix/VERSION)
endif

ifeq ($(LIB_VERSION),SVN)
SVN_REVISION = $(shell svn info | grep "Revision" | cut -d " " -f 2)
LIB_VERSION=SVN-$(SVN_REVISION)
endif

ifndef DIST
DIST=_dist
endif

DISTJELIX="$(DIST)/jelix-$(LIB_VERSION)"
DISTHACKER="$(DIST)/jelix-svn"


default:
	@echo "target:  dist-all dist-jelix dist-testapp dist-myapp dev-all dev-jelix dev-myapp dev-testapp"

dist-all: dist-jelix dist-testapp dist-myapp

dev-all: dev-jelix dev-myapp dev-testapp

dist-jelix: common
	export LIB_VERSION=$(LIB_VERSION) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-lib.mn . $(DISTJELIX) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-dev.mn . $(DISTJELIX) \
	&& echo "$(LIB_VERSION)" > "$(DISTJELIX)/lib/jelix/VERSION"
	if [ ! -d "$(DISTJELIX)/temp" ] ; then mkdir $(DISTJELIX)/temp ; fi
	tar czf $(DIST)/jelix-lib-$(LIB_VERSION).tar.gz  -C $(DISTJELIX) lib/ temp/

dist-testapp: common
	$(PHP) build/mkdist.php build/manifests/testapp.mn . $(DISTJELIX)
	tar czf $(DIST)/testapp-$(LIB_VERSION).tar.gz  -C $(DISTJELIX) testapp/

dist-myapp: common
	$(PHP) build/mkdist.php build/manifests/myapp.mn . $(DISTJELIX)
	tar czf $(DIST)/myapp-$(LIB_VERSION).tar.gz  -C $(DISTJELIX) myapp/

dev-jelix: common
	export LIB_VERSION=$(LIB_VERSION) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-lib.mn . $(DISTHACKER) \
	&& $(PHP) build/mkdist.php build/manifests/jelix-dev.mn . $(DISTHACKER) \
	&& echo "$(LIB_VERSION)" > "$(DISTHACKER)/lib/jelix/VERSION"

dev-testapp: common
	$(PHP) build/mkdist.php build/manifests/testapp.mn . $(DISTHACKER)

dev-myapp: common
	$(PHP) build/mkdist.php build/manifests/myapp.mn . $(DISTHACKER)

common:
	if [ ! -d "$(DIST)" ] ; then mkdir $(DIST) ; fi
	if [ ! -d "$(DISTJELIX)" ] ; then mkdir $(DISTJELIX) ; fi
	if [ ! -d "$(DISTHACKER)" ] ; then mkdir $(DISTHACKER) ; fi
	

