SHELL=/bin/sh
PHP=/usr/bin/php
VERSION=SVN

DIST=_dist

LIBPATH="$(DIST)/jelix-lib-$(VERSION)"



default:
	@echo "make all"

jelix-lib: common
	if [ ! -d "$(LIBPATH)" ] ; then mkdir $(LIBPATH) ; else rm -rf $(LIBPATH)/* ; fi
	$(PHP) build/mkdist.php build/fld/jelix-lib.fld . $(LIBPATH)

common:
	if [ ! -d "$(DIST)" ] ; then mkdir $(DIST) ; fi

