;<?php die(''); ?>
;for security reasons , don't remove or modify the first line


;============= Paramètres généraux

; Db, Class ou LDS  ( respecter la casse des caractères)
driver = Db

;============ Paramètres pour le plugin
; indique si on effectue un contrôle sur l'adresse ip
; qui a démarré la session.
secure_with_ip = 0

; action en cas de piratage de la session et si onError = 2
bad_ip_action = "jauth~loginsw_out"

;Timeout. Permet de forcer une authentification aprés un certain temps écoulé
;sans action . temps en minutes. 0 = pas de timeout.
timeout = 0

; indique si il faut absolument ou non une authentification pour chaque action
; on = authentification necessaire pour toute action
;   sauf celles qui l'indiquent spécifiquement   (parametre action auth.required=false)
; off = authentification non requise pour toute action
;   sauf celles qui l'indiquent spécifiquement   (parametre action auth.required=true)
auth_required = on

; indique quoi faire en cas de défaut d'authentification
; 1 = erreur. Valeur à mettre impérativement pour les web services (xmlrpc, jsonrpc...)
; 2 = redirection vers une action
on_error = 1

; action à executer en cas de défaut d'authentification quand on_error = 2
on_error_action = "jauth~loginsw_out"

;selecteur de la clé de locale du message d'erreur
error_message = "jauth~autherror.notlogged"


;=========== Paramètres pour le module jauth

; nombre de secondes d'attentes aprés un défaut d'authentification
on_error_sleep = 3



;=========== Paramètres pour les drivers

; paramètres pour le driver db
[Db]
dao = ""

; nom de la fonction globale qui sert à crypter le mot de passe
password_crypt_function = md5

; paramètres pour le driver class
[Class]
class = ""
password_crypt_function = md5

[LDS]

