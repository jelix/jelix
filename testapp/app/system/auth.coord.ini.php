;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

;============= Main parameters

; driver name : "ldap", "Db", "Class" (respect the case of characters)
driver=Db

;============ Parameters for the plugin
; session variable name
session_name=JELIX_USER

; Says if there is a check on the ip address : verify if the ip
; is the same when the user has been connected
secure_with_ip=0

;Timeout. After the given time (in minutes) without activity, the user is disconnected.
; If the value is 0 : no timeout
timeout=0

; If the value is "on", the user must be authentificated for all actions, except those
; for which a plugin parameter  auth.required is false
; If the value is "off", the authentification is not required for all actions, except those
; for which a plugin parameter  auth.required is true
auth_required=off

; What to do if an authentification is required but the user is not authentificated
; 1 = generate an error. This value should be set for web services (xmlrpc, jsonrpc...)
; 2 = redirect to an action
on_error=2

; locale key for the error message when on_error=1
error_message="jauth~autherror.notlogged"

; action to execute on a missing authentification when on_error=2
on_error_action="jauth~login:out"

; action to execute when a bad ip is checked with secure_with_ip=1 and on_error=2
bad_ip_action="jauth~login:out"


;=========== Parameters for jauth module

; number of second to wait after a bad authentification.
; deprecated. Not recommended to use it, as it eases a DDOS attack
on_error_sleep=0

; action to redirect after the login
after_login="jauth~login:form"

; action to redirect after a logout
after_logout="jauth~login:form"

; says if after_login can be overloaded by a "auth_url_return" parameter in the url/form for the login
enable_after_login_override=off

; says if after_logout can be overloaded by a "auth_url_return" parameter in the url/form for the login
enable_after_logout_override=off

; list of domains allowed for url indicated into auth_url_return.
; should be a string for a single domain
url_return_external_allowed_domains=
; or a list like that:
;url_return_external_allowed_domains[]=

;============ Parameters for the persistance of the authentification

; enable the persistance of the authentification between two sessions
persistant_enable=off

; the name of the cookie which is used to store data for the authentification
persistant_cookie_name=jauthSession

; duration of the validity of the cookie (in days). default is 1 day.
persistant_duration=1

;=========== Parameters for drivers

;------- parameters for the "Db" driver
[Db]
; name of the dao to get user data
dao="jauthdb~jelixuser"

; profile to use for jDb 
profile=

; name of the php function to crypt the password in the database
password_crypt_function=md5

; name of the form for the jauthdb_admin module
form=

; path of the directory where to store files uploaded by the form (jauthdb_admin module)
; should be related to the var directory of the application
uploadsDirectory=

;------- parameters for the "Class" driver
[Class]
; selector of the class
class=

; name of the php function to crypt the password in the database
password_crypt_function=md5

;------- parameters for the "ldap" driver
[ldap]
profile=

; LDAP search params 
; search base, example for Active Directory: "ou=ADAM users,o=Microsoft,c=US"
searchBaseDN=
; search filter, example for Active Directory: "(objectClass=user)"
searchFilter=
; attributes to retrieve for the search, example for Active Directory: "cn,distinguishedName,name"
searchAttributes=

; name of the php function to crypt the password in the database
password_crypt_function=md5

