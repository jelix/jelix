;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

locale = "en_EN"
charset = "UTF-8"

; see http://www.php.net/manual/en/timezones.php for supported values
; if empty, jelix will try to get the default timezone
timeZone = 

pluginsPath = app:plugins/,lib:jelix-plugins/,module:jacl2db/plugins,module:jacldb/plugins
modulesPath = lib:jelix-modules/,app:modules/

theme = default

; for junittests module
enableTests = on

[modules]
jelix.access = 2
jelix_tests.access = 2
testapp.access = 2
testurls.access = 2
junittests.access = 2
jsoap.access = 2
jacl.access = 1
jacldb.access = 1
jacl2.access = 1
jacl2db.access = 1
jauthdb.access = 1
jauth.access = 1
jpref.access=1

[coordplugins]
auth = auth.coord.ini.php

[coordplugin_jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"

[coordplugin_jacl]
on_error=2
error_message="jacl~errors.action.right.needed"
on_error_action="jelix~error:badright"

[responses]

[error_handling]
;errorMessage="A technical error has occured (code: %code%). Sorry for this inconvenience."

[compilation]
checkCacheFiletime  = on
force  = off

[urlengine]
engine        = basic_significant

; enable the parsing of the url. Set it to off if the url is already parsed by another program
; (like mod_rewrite in apache), if the rewrite of the url corresponds to a simple url, and if
; you use the significant engine. If you use the simple url engine, you can set to off.
enableParser = on

multiview = off

; basePath corresponds to the path to the base directory of your application.
; so if the url to access to your application is http://foo.com/aaa/bbb/www/index.php, you should
; set basePath = "/aaa/bbb/www/". 
; if it is http://foo.com/index.php, set basePath="/"
; Jelix can guess the basePath, so you can keep basePath empty. But in the case where there are some
; entry points which are not in the same directory (ex: you have two entry point : http://foo.com/aaa/index.php 
; and http://foo.com/aaa/bbb/other.php ), you MUST set the basePath (ex here, the higher entry point is index.php so
; : basePath="/aaa/" )
basePath = ""

defaultEntrypoint= index

; liste des actions requerant https (syntaxe expliquée dessous), pour le moteur
d'url simple simple_urlengine_https = "unittest~urlsig:url8@classic @xmlrpc"

[simple_urlengine_entrypoints]
; paramètres pour le moteur d'url simple : liste des points d'entrées avec les actions
; qui y sont rattachées

; nom_script_sans_suffix = "liste de selecteur séparé par un espace"
; selecteurs :
;   m~a@r    -> pour action a du module m répondant au type de requete r
;   m~c:*@r  -> for all actions of the controller "c" of the module "m" and for the request of type "r"
;   m~*@r    -> pour toute action du module m répondant au type de requete r
;   @r       -> toute action de tout module répondant au type de requete r

index = "@classic"
xmlrpc = "@xmlrpc"
jsonrpc = "@jsonrpc"
testnews = "jelix_tests~urlsig:url2@classic jelix_tests~urlsig:url3@classic"
foo__bar = "jelix_tests~urlsig:url4@classic"
news = "new~*@classic"
soap = "@soap"
actu = "jelix_tests~actu:*@classic"
noep = "jelix_tests~urlsig:bug1488@classic"

[basic_significant_urlengine_entrypoints]
; for each entry point, it indicates if the entry point name
; should be include in the url or not
index = on
xmlrpc = on
jsonrpc = on
testnews = off
foo__bar = on
news = on
soap = on
noep = on

[jResponseHtml]
; list of active plugins for jResponseHtml
plugins = debugbar


[mailLogger]
;email = root@localhost
;emailHeaders = "Content-Type: text/plain; charset=UTF-8\nFrom: webmaster@yoursite.com\nX-Mailer: Jelix\nX-Priority: 1 (Highest)\n"

[acl]
driver = db

[acl2]
driver = db

[rootUrls]
/themes = "http://themes.junittest.com/"
test = "http://www.junittest.com/"
secure_test = "https://www.junittest.com/"
foo_relPath="foo"
foo_absPath="/foo"

[jforms_builder_html]
;control = plugin
