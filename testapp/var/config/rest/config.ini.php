;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

startModule=testapp
startAction="main:index"

[coordplugins]
jacl2=1
jacl=1

[responses]
html=myHtmlResponse
soap="jsoap~jResponseSoap"


[urlengine]
engine=significant
significantFile=urls_rest.xml

