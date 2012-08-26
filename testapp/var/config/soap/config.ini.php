;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

startModule="testapp"
startAction="soap:hello"

[coordplugins]
auth = auth_ws.coord.ini.php
jacl2 = jacl2_ws.coord.ini.php
jacl = jacl_ws.coord.ini.php


[responses]
soap="jsoap~jResponseSoap"

