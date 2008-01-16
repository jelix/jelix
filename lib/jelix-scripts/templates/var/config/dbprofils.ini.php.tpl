;<?php die(''); ?>
;for security reasons, don't remove or modify the first line

; name of the default profil to use for any connection
;default = myapp
default =

; each section correspond to a connection
; the name of the section is the name of the connection, to use as an argument
; for jDb and jDao methods
; Parameters in each sections depends of the driver type

;[myapp]

; For the most of drivers:
;driver="mysql"
;database="jelix"
;host= "localhost"
;user= "root"
;password=
;persistent= on
; when you have charset issues, enable force_encoding so the connection will be
; made with the charset indicated in jelix config
;force_encoding = on

; For pdo :
;driver=pdo
;dsn=mysql:host=localhost;dbname=test
;user=
;password=
