;<?php die(''); ?>
;for security reasons , don't remove or modify the first line
;this file doesn't list all possible properties. See lib/jelix/core/defaultconfig.ini.php for that

locale = "%%default_locale%%"
charset = "UTF-8"

; see http://www.php.net/manual/en/timezones.php for supported values
timeZone = "%%default_timezone%%"

theme = default

pluginsPath = app:plugins/,lib:jelix-plugins/

modulesPath = lib:jelix-modules/,app:modules/

; says if jelix should check trustedModules
checkTrustedModules = off

; list of modules which can be accessed from the web
;    module,module,module
trustedModules =

; list of modules which are not used by the application
; or not installed.
unusedModules = jacldb

[coordplugins]
;nom = nom_fichier_ini

[tplplugins]
defaultJformsBuilder = html

[responses]
html=myHtmlResponse

[error_handling]
messageLogFormat = "%date%\t[%code%]\t%msg%\t%file%\t%line%\n"
logFile = error.log
email = root@localhost
emailHeaders = "Content-Type: text/plain; charset=UTF-8\nFrom: webmaster@yoursite.com\nX-Mailer: Jelix\nX-Priority: 1 (Highest)\n"
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
; name of url engine :  "simple", "basic_significant" or "significant"
engine        = basic_significant

; this is the url path to the jelix-www content (you can found this content in lib/jelix-www/)
; because the jelix-www directory is outside the yourapp/www/ directory, you should create a link to
; jelix-www, or copy its content in yourapp/www/ (with a name like 'jelix' for example)
; so you should indicate the relative path of this link/directory to the basePath, or an absolute path.
jelixWWWPath = "jelix/"


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

entrypointExtension= .php

; leave empty to have jelix error messages
notfoundAct =
;notfoundAct = "jelix~error:notfound"

; list of actions which require https protocol for the simple url engine
; syntax of the list is the same as explained in the simple_urlengine_entrypoints
simple_urlengine_https =

[simple_urlengine_entrypoints]
; parameters for the simple url engine. This is the list of entry points
; with list of actions attached to each entry points

; script_name_without_suffix = "list of action selectors separated by a space"
; selector syntax :
;   m~a@r    -> for the action "a" of the module "m" and for the request of type "r"
;   m~*@r    -> for all actions of the module "m" and for the request of type "r"
;   @r       -> for all actions for the request of type "r"

index = "@classic"


[basic_significant_urlengine_entrypoints]
; for each entry point, it indicates if the entry point name
; should be include in the url or not
index = on
xmlrpc = on
jsonrpc = on
rdf = on

[logfiles]
default=messages.log

[mailer]
webmasterEmail = root@localhost
webmasterName =

; how to send mail : "mail" (mail()), "sendmail" (call sendmail), or "smtp" (send directly to a smtp)
mailerType = mail
; Sets the hostname to use in Message-Id and Received headers
; and as default HELO string. If empty, the value returned
; by SERVER_NAME is used or 'localhost.localdomain'.
hostname =
sendmailPath = "/usr/sbin/sendmail"

; if mailer = file, fill the following parameters
; this should be the directory in the var/ directory, where to store mail as files
filesDir = "mails/"

; if mailer = smtp , fill the following parameters

; SMTP hosts.  All hosts must be separated by a semicolon : "smtp1.example.com:25;smtp2.example.com"
smtpHost = "localhost"
; default SMTP server port
smtpPort = 25
; secured connection or not. possible values: "", "ssl", "tls"
smtpSecure = 
; SMTP HELO of the message (Default is hostname)
smtpHelo =
; SMTP authentication
smtpAuth = off
smtpUsername =
smtpPassword =
; SMTP server timeout in seconds
smtpTimeout = 10



[acl2]
; example of driver: "db"
driver =

[sessions]
; to disable sessions, set the following parameter to 0
start = 1
; You can change the session name by setting the following parameter (only accepts alpha-numeric chars) :
; name = "mySessionName"
; Use alternative storage engines for sessions
;
; usage :
;
; storage = "files"
; files_path = "app:var/sessions/"
;
; or
;
; storage = "dao"
; dao_selector = "jelix~jsession"
; dao_db_profile = ""


[forms]
; define input type for datetime widgets : "textboxes" or "menulists"
controls.datetime.input = "menulists"
; define the way month labels are displayed widgets: "numbers", "names" or "shortnames"
controls.datetime.months.labels = "names"
; define the default config for datepickers in jforms
datepicker = default

[datepickers]
default = jelix/js/jforms/datepickers/default/init.js