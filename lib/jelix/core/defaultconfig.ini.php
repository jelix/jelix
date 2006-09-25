;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

defaultModule = "jelix"
defaultAction = "default_index"
defaultLocale = "fr_FR"
defaultCharset = "ISO-8859-1"
defaultTimeZone = "Europe/Paris"

checkTrustedModules = off

; list of modules : module,module,module
trustedModules =

pluginsPath = lib:jelix-plugins/,app:plugins/
modulesPath = lib:jelix-modules/,app:modules/
tplpluginsPath = lib:jelix/tpl/plugins/

dbProfils = dbprofils.ini.php

defaultTheme = default
use_error_handler = on
shared_session = off

[plugins]

[responses]
html = jResponseHtml
redirect = jResponseRedirect
redirectUrl = jResponseRedirectUrl
binary = jResponseBinary
text = jResponseText
jsonrpc = jResponseJsonrpc
xmlrpc = jResponseXmlrpc
xul = jResponseXul
xuloverlay = jResponseXulOverlay
xuldialog = jResponseXulDialog
xulpage = jResponseXulPage
rdf = jResponseRdf
xml = jResponseXml
zip = jResponseZip


[_coreResponses]
html = jResponseHtml
redirect = jResponseRedirect
redirectUrl = jResponseRedirectUrl
binary = jResponseBinary
text = jResponseText
jsonrpc = jResponseJsonrpc
xmlrpc = jResponseXmlrpc
xul = jResponseXul
xuloverlay = jResponseXulOverlay
xuldialog = jResponseXulDialog
xulpage = jResponseXulPage
rdf = jResponseRdf
xml = jResponseXml
zip = jResponseZip

[error_handling]
messageLogFormat = "%date%\t[%code%]\t%msg%\t%file%\t%line%\n"
logFile = error.log
email = root@localhost
emailHeaders = "From: webmaster@yoursite.com\nX-Mailer: Jelix\nX-Priority: 1 (Highest)\n"
quietMessage="Une erreur technique est survenue. Désolé pour ce désagrément."

; mots clés que vous pouvez utiliser : ECHO, ECHOQUIET, EXIT, LOGFILE, SYSLOG, MAIL, TRACE
default      = ECHO EXIT
error        = ECHO EXIT
warning      = ECHO
notice       = ECHO
strict       = ECHO
; pour les exceptions, il y a implicitement un EXIT
exception    = ECHO

[compilation]
checkCacheFiletime  = on
force  = off

[urlengine]
; nom du moteur d'url :  simple ou significant
engine        = simple

; active l'analyse d'url (mettre à off si vous utilisez le mod_rewrite d'apache)
enableParser = on

multiview = off

; chemin url jusqu'au repertoire www (celui que vous tapez dans le navigateur pour accéder à index.php etc.)
; peut être égale à "/" si vous spécifiez www comme étant le documentRoot de votre site au niveau du serveur
basePath = "/"

defaultEntrypoint= index

entrypointExtension= .php

notfoundAct = "jelix~notfound"

;indique si vous utilisez IIS comme serveur
useIIS = off

;indique le paramètre dans $_GET où est indiqué le path_info
IISPathKey = __JELIX_URL__

;indique si il faut stripslashé le path_info récupéré par le biais de IISPathKey
IISStripslashes_path_key = on


[simple_urlengine_entrypoints]
; paramètres pour le moteur d'url simple : liste des points d'entrées avec les actions
; qui y sont rattachées


; nom_script_sans_suffix = "liste de selecteur d'action séparé par un espace"
; selecteurs :
;   m~a@r    -> pour action "a" du module "m" répondant au type de requete "r"
;   m~*@r    -> pour toute action du module "m" répondant au type de requete "r"
;   @r       -> toute action de tout module répondant au type de requete "r"

index = "@classic"
xmlrpc = "@xmlrpc"
jsonrpc = "@jsonrpc"
rdf = "@rdf"


[logfiles]
default=messages.log
