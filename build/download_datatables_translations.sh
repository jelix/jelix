#!/bin/bash





LANG_LIST="cs=cs_CZ de-DE=de_DE el=el_GR en-GB=en_US en-GB=en_GB es-ES=es_ES eu=eu_ES fi=fi_FI fr-FR=fr_FR gl=gl_ES hu=hu_HU it-IT=it_IT ja=ja_JP nl-NL=nl_NL pl=pl_PL pt-BR=pt_BR pt-PT=pt_PT ro=ro_RO ru=ru_RU sl=sl_SI sb-SE=sv_SE sk=sk_SK uk=uk_UA"

for LANGDEF in $LANG_LIST; do

  DT_FIC=$(echo $LANGDEF | cut -d"=" -f 1)
  JLX_FIC=$(echo $LANGDEF | cut -d"=" -f 2).js
  echo $DT_FIC " -> " $JLX_FIC
  TARGET_NEW=../lib/jelix-www/datatables/i18n/$JLX_FIC.new
  TARGET=../lib/jelix-www/datatables/i18n/$JLX_FIC
  wget https://cdn.datatables.net/plug-ins/1.12.0/i18n/$DT_FIC.json -O $TARGET_NEW
  if [ -f "$TARGET_NEW" ]; then

    CONTENT=$(cat $TARGET_NEW)
    if [ "$CONTENT" != "" ]; then
      echo -n "var DatatablesTranslations = " > $TARGET
      cat $TARGET_NEW >> $TARGET
      echo ";" >> $TARGET
    else
      rm -f $TARGET
    fi
    rm $TARGET_NEW
  fi
done