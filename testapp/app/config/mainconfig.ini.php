;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

locale=en_US
charset=UTF-8

; see http://www.php.net/manual/en/timezones.php for supported values
; if empty, jelix will try to get the default timezone
timeZone=

theme=default

[modules]
jelix.access=2
jelix_tests.access=2
testapp.access=2
testurls.access=2
jsoap.access=2
jacl.access=1
jacldb.access=1
jacl2.access=1
jacl2db.access=1
jauthdb.access=1
jauth.access=1
jpref.access=1
jminify.access=1
jfeeds.access=2
jsitemap.access=2
news.access=2
articles.access=2

[coordplugins]
auth=auth.coord.ini.php

[coordplugin_jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"

[coordplugin_jacl]
on_error=2
error_message="jacl~errors.action.right.needed"
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


[jResponseHtml]
; list of active plugins for jResponseHtml
plugins="debugbar,minify"
minifyCSS=off
minifyJS=on
minifyExcludeCSS=
minifyExcludeJS="jelix/wymeditor/jquery.wymeditor.js"
minifyEntryPoint=minify.php


[logger]
soap=file

[fileLogger]
soap=messages.log

[mailLogger]
;email = root@localhost
;emailHeaders = "Content-Type: text/plain; charset=UTF-8\nFrom: webmaster@yoursite.com\nX-Mailer: Jelix\nX-Priority: 1 (Highest)\n"

[acl]
driver=db

[acl2]
driver=db

[rootUrls]
/themes = "http://themes.junittest.com/"
test="http://www.junittest.com/"
secure_test="https://www.junittest.com/"
foo_relPath=foo
foo_absPath="/foo"
soap="http://testapp20.local"
localapp="http://testapp20.local"

[jforms_builder_html]
;control = plugin

