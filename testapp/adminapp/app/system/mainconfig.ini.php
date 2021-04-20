;<?php die(''); ?>
;for security reasons , don't remove or modify the first line
;this file doesn't list all possible properties. See lib/jelix/core/defaultconfig.ini.php for that

locale=en_US
availableLocales=en_US
charset=UTF-8

; see http://www.php.net/manual/en/timezones.php for supported values
timeZone="Europe/Paris"

theme=default

; default domain name to use with jfullurl for example.
; Let it empty to use $_SERVER['SERVER_NAME'] value instead.
domainName=


[modules]
jauthdb.installparam=defaultuser
jacl2db.installparam=defaultuser

jelix.enabled=on
jelix.installparam="wwwfiles=vhost"

jacl.enabled=off
jacldb.enabled=off
jsoap.enabled=off
jauth.enabled=on
master_admin.enabled=on
jauthdb.enabled=on
jauthdb_admin.enabled=on
jacl2.enabled=on
jacl2db.enabled=on
jacl2db_admin.enabled=on
jpref.enabled=on
jpref_admin.enabled=on
[coordplugins]
;name = file_ini_name or var:file_ini_name or 1

[tplplugins]
defaultJformsBuilder=html

[responses]
html=adminHtmlResponse
htmlauth=adminLoginHtmlResponse

[error_handling]
;errorMessage="A technical error has occured (code: %code%). Sorry for this inconvenience."

;[compilation]
;checkCacheFiletime  = on
;force  = off

[urlengine]

; this is the url path to the jelix-www content (you can found this content in lib/jelix-www/)
; because the jelix-www directory is outside the yourapp/www/ directory, you should create a link to
; jelix-www, or copy its content in yourapp/www/ (with a name like 'jelix' for example)
; so you should indicate the relative path of this link/directory to the basePath, or an absolute path.
; if you change it, you probably want to change path in datepickers, wikieditors and htmleditors sections
jelixWWWPath="/jelix/"
jqueryPath="/jelix/jquery/"

; enable the parsing of the url. Set it to off if the url is already parsed by another program
; (like mod_rewrite in apache)
enableParser=on

multiview=off

; basePath corresponds to the path to the base directory of your application.
; so if the url to access to your application is http://foo.com/aaa/bbb/www/index.php, you should
; set basePath = "/aaa/bbb/www/".
; if it is http://foo.com/index.php, set basePath="/"
; Jelix can guess the basePath, so you can keep basePath empty. But in the case where there are some
; entry points which are not in the same directory (ex: you have two entry point : http://foo.com/aaa/index.php
; and http://foo.com/aaa/bbb/other.php ), you MUST set the basePath (ex here, the higher entry point is index.php so
; : basePath="/aaa/" )
basePath=

; action to show the 'page not found' error
notFoundAct="jelix~error:notfound"

[jResponseHtml]
; list of active plugins for jResponseHtml
; remove the debugbar plugin on production server, and in this case don't forget
; to remove the memory logger from the logger section
plugins=debugbar


[logger]
; list of loggers for each categories of log messages
; available loggers : file, syslog, firebug, mail, memory. see plugins for others

; _all category is the category containing loggers executed for any categories
_all=

; default category is the category used when a given category is not declared here
default=file
error=file
warning=file
notice=file
deprecated=
strict=
debug=
sql=
soap=

[fileLogger]
default=messages.log

[mailLogger]
;email = root@localhost
;emailHeaders = "Content-Type: text/plain; charset=UTF-8\nFrom: webmaster@yoursite.com\nX-Mailer: Jelix\nX-Priority: 1 (Highest)\n"

[mailer]
webmasterEmail="root@localhost"
webmasterName=

; How to send mail : "mail" (mail()), "sendmail" (call sendmail), "smtp" (send directly to a smtp)
;                   or "file" (store the mail into a file, in filesDir directory)
mailerType=file
; Sets the hostname to use in Message-Id and Received headers
; and as default HELO string. If empty, the value returned
; by SERVER_NAME is used or 'localhost.localdomain'.
hostname=
sendmailPath="/usr/sbin/sendmail"

; if mailer = file, fill the following parameters
; this should be the directory in the var/ directory, where to store mail as files
filesDir="mails/"

; if mailer = smtp , fill the following parameters

; SMTP hosts.  All hosts must be separated by a semicolon : "smtp1.example.com:25;smtp2.example.com"
smtpHost=localhost
; default SMTP server port
smtpPort=25
; secured connection or not. possible values: "", "ssl", "tls"
smtpSecure=
; SMTP HELO of the message (Default is hostname)
smtpHelo=
; SMTP authentication
smtpAuth=off
smtpUsername=
smtpPassword=
; SMTP server timeout in seconds
smtpTimeout=10



[acl2]
; example of driver: "db"
driver=db

[sessions]
; If several applications are installed in the same documentRoot but with
; a different basePath, shared_session indicates if these application
; share the same php session
shared_session=off

; indicate a session name for each applications installed with the same
; domain and basePath, if their respective sessions shouldn't be shared
name=admin

; Use alternative storage engines for sessions
;storage = "files"
;files_path = "app:var/sessions/"
;
; or
;
;storage = "dao"
;dao_selector = "jelix~jsession"
;dao_db_profile = ""


[forms]
; define input type for datetime widgets : "textboxes" or "menulists"
;controls.datetime.input = "menulists"
; define the way month labels are displayed widgets: "numbers", "names" or "shortnames"
;controls.datetime.months.labels = "names"
; define the default config for datepickers in jforms
;datepicker = default

[datepickers]
;default = jelix/js/jforms/datepickers/default/init.js

[webassets_common]
master_admin.css[]="$jelix/design/master_admin.css"
jacl2_admin.require = jquery_ui
jacl2_admin.css[]="$jelix/design/jacl2.css"
jacl2_admin.js[]="$jelix/js/acl2db_admin.js"
jacl2_admin.js[]="$jelix/js/jacl2db_admin.js"
