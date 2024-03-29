;<?php exit(''); ?>
;for security reasons , don't remove or modify the first line

;============= Main parameters

; driver name : "ldap", "Db", "Class" (respect the case of characters)
driver = 

;============ Parameters for the plugin
; session variable name
session_name = "JELIX_USER"

; Says if there is a check on the ip address : verify if the ip
; is the same when the user has been connected
secure_with_ip = 0

;Timeout. After the given time (in minutes) without activity, the user is disconnected.
; If the value is 0 : no timeout
timeout = 0

; If the value is "on", the user must be authentificated for all actions, except those
; for which a plugin parameter  auth.required is false
; If the value is "off", the authentification is not required for all actions, except those
; for which a plugin parameter  auth.required is true
auth_required = on

; What to do if an authentification is required but the user is not authentificated
; 1 = generate an error. This value should be set for web services (xmlrpc, jsonrpc...)
; 2 = redirect to an action
on_error = 1

; locale key for the error message when on_error=1
error_message = "jauth~autherror.notlogged"

; action to execute on a missing authentification when on_error=2
on_error_action = "jauth~loginsw:out"

; action to execute on a missing authentification when on_error=2 and request is ajax
on_ajax_error_action=

; action to execute when a bad ip is checked with secure_with_ip=1 and on_error=2
bad_ip_action = "jauth~loginsw:out"

;=========== Parameters for jauth module

; number of second to wait after a bad authentification.
; deprecated. Not recommended to use it, as it eases a DDOS attack
on_error_sleep = 0

; action to redirect after the login
after_login = ""

; action to redirect after a logout
after_logout = ""

; says if after_login can be overloaded by a "auth_url_return" parameter in the url/form for the login
enable_after_login_override = off

; says if after_logout can be overloaded by a "auth_url_return" parameter in the url/form for the login
enable_after_logout_override = off


;============ Parameters for the persistance of the authentification

; enable the persistance of the authentification between two sessions
persistant_enable=off

; the name of the cookie which is used to store data for the authentification
persistant_cookie_name=jauthSession

; duration of the validity of the cookie (in days). default is 1 day.
persistant_duration = 1

;=========== parameters for password hashing

; method of the hash. 0 or "" means old hashing behavior of jAuth
; (using password_* parameters in drivers ).
; Prefer to choose 1, which is the default hash method (bcrypt).
password_hash_method = 1

; options for the hash method. list of "name:value" separated by a ";"
password_hash_options = 

;=========== Parameters for drivers

;------- parameters for the "Db" driver
[Db]
; name of the dao to get user data
dao = ""

; profile to use for jDb 
profile = ""

; name of the php function used to hash password in the database
; It is deprecated but still used to convert password hash
; to new hashes with password_hash_method
password_crypt_function = sha1
; if you want to use a salt with sha1:
;password_crypt_function = "1:sha1WithSalt"
;password_salt = "here_your_salt"

;------- parameters for the "Class" driver
[Class]
; selector of the class
class = ""

; name of the php function used to hash password
; It is deprecated but still used to convert password hash
; to new hashes with password_hash_method
password_crypt_function = sha1
; if you want to use a salt with sha1:
;password_crypt_function = "1:sha1WithSalt"
;password_salt = "here_your_salt"

;------- parameters for the "ldap" driver
[ldap]
; profile in profiles.ini.php containing ldap connection informations
profile=jauth

; following parameters can be stored in the profile too.

; LDAP search params 
; search base, example for Active Directory: "ou=ADAM users,o=Microsoft,c=US"
searchBaseDN=
; search filter, example for Active Directory: "(objectClass=user)"
searchFilter=
; attributes to retrieve for the search, example for Active Directory: "cn,distinguishedName,name"
searchAttributes=

; the name of the ldap property used for the login field
uidProperty=cn
; the objectclass to use for a user
ldapUserObjectClass=user

