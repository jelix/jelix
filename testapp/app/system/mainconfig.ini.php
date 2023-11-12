;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

locale=en_US
charset=UTF-8

; see http://www.php.net/manual/en/timezones.php for supported values
; if empty, jelix will try to get the default timezone
timeZone="Europe/Paris"

theme=default

[modules]

jelix_tests.enabled=on
testapp.enabled=on
testurls.enabled=on
jsoap.enabled=on
jacl2.enabled=on
jacl2db.enabled=on
jauthdb.enabled=on
jauth.enabled=on
jpref.enabled=on
jminify.enabled=on
jfeeds.enabled=on
jsitemap.enabled=on
news.enabled=on
articles.enabled=on

jelix.enabled=on

jelix.installparam[wwwfiles]=vhost

jacl2db.installparam[defaultgroups]=on
jacl2db.installparam[defaultuser]=on

[coordplugins]
auth=auth.coord.ini.php

[jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"

[responses]
rss2.0="jfeeds~jResponseRss20"
atom1.0="jfeeds~jResponseAtom10"
sitemap="jsitemap~jResponseSitemap"

soap="jsoap~jResponseSoap"

[error_handling]
;errorMessage="A technical error has occured (code: %code%). Sorry for this inconvenience."

[compilation]
checkCacheFiletime=on
force=off

[urlengine]

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

jelixWWWPath="jelix/"

[jResponseHtml]
; list of active plugins for jResponseHtml
plugins="debugbar,minify"
minifyCSS=off
minifyJS=off
minifyExcludeCSS=
minifyExcludeJS="jelix/ckeditor5/ckeditor.js"
minifyEntryPoint=minify.php


[logger]
soap=file
auth=file

[fileLogger]
soap=soap.log
auth=auth.log

[mailLogger]
;email = root@localhost
;emailHeaders = "Content-Type: text/plain; charset=UTF-8\nFrom: webmaster@yoursite.com\nX-Mailer: Jelix\nX-Priority: 1 (Highest)\n"

[acl]
driver=db

[acl2]
driver=db
hiddenRights=
hideRights=off
authAdapterClass=jAcl2JAuthAdapter

[rootUrls]
/themes = "http://themes.junittest.com/"
test="http://www.junittest.com/"
secure_test="https://www.junittest.com/"
foo_relPath=foo
foo_absPath="/foo"
soap="http://testapp.local"
localapp="http://testapp.local"

[jforms_builder_html]
;control = plugin


[session]
storage=


[webassets_common]
jforms_html.js[]="$jelix/js/jforms_jquery.js"
jforms_html.css="$jelix/design/jform.css"
jforms_html.require=jquery

qunit.js[]="qunit/qunit.js"
qunit.css[]="qunit/qunit.css"
qunit.require=jquery
