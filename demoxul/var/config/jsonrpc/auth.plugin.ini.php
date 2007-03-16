;<?php die(''); ?>
;for security reasons , don't remove or modify the first line


;============= Paramètres généraux

; Db ou Class
driver = Db

;============ Paramètres pour le plugin
; indique si on effectue un contrôle sur l'adresse ip
; qui a démarré la session.
secure_with_ip = 0

; action en cas de piratage de la session et si onError = 2
bad_ip_action = "jxxulapp~xul_badlogin"

;Timeout. Permet de forcer une authentification aprés un certain temps écoulé
;sans action . temps en minutes. 0 = pas de timeout.
timeout = 0

; indique si il faut absolument ou non une authentification
; on = authentification necessaire pour toute action
;   sauf celles qui l'indiquent spécifiquement   (parametre action auth.required=false)
; off = authentification non requise pour toute action
;   sauf celles qui l'indiquent spécifiquement   (parametre action auth.required=true)
auth_required = on

; indique quoi faire en cas de défaut d'authentification
; 1 = erreur. Valeur à mettre impérativement pour les web services
; 2 = redirection vers une action
on_error = 1

; action à executer en cas de défaut d'authentification quand onError = 2
on_error_action = "jxxulapp~xul_badlogin"

;selecteur de la locale
error_message = "jxauth~autherror.notlogged"


;=========== Paramètres pour le module jxauth

; nombre de secondes d'attentes aprés un défaut d'authentification
on_error_sleep = 3

enable_after_login_override = off
after_login =

enable_after_logout_override = off
after_logout = "jxxulapp~xul_badlogin"


;=========== Paramètres pour les drivers

; paramètres pour le driver db
[Db]
dao = "jxauth~jelixuser"

; nom de la fonction globale qui sert à crypter le mot de passe
password_crypt_function = md5
