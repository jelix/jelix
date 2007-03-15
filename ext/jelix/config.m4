dnl config.m4 for extension jelix

PHP_ARG_ENABLE(jelix, whether to enable jelix framework support,
[  --enable-jelix       Enable jelix framework support], yes)

if test "$PHP_JELIX" != "no"; then
  AC_DEFINE([HAVE_JELIX], 1 ,[whether to enable jelix framework support])
  PHP_NEW_EXTENSION(jelix, jelix.c jelix_interfaces.c, $ext_shared)
  PHP_ADD_MAKEFILE_FRAGMENT
fi

