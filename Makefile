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

LIBPATH="$(DIST)/jelix-$(LIB_VERSION)"



default:
	@echo "target:  jelix-lib"

jelix-lib: common
	if [ ! -d "$(LIBPATH)" ] ; then mkdir $(LIBPATH) ; else rm -rf $(LIBPATH)/* ; fi
	export LIB_VERSION=$(LIB_VERSION) \
	&& $(PHP) build/mkdist.php build/fld/jelix-lib.fld . $(LIBPATH) \
	&& $(PHP) build/mkdist.php build/fld/jelix-dev.fld . $(LIBPATH) \
	&& echo "$(LIB_VERSION)" > "$(LIBPATH)/lib/jelix/VERSION"




common:
	if [ ! -d "$(DIST)" ] ; then mkdir $(DIST) ; fi
	

