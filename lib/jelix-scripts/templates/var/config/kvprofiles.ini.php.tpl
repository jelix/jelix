;<?php die(''); ?>
;for security reasons, don't remove or modify the first line

; default profile
default =

; each section correspond to a kvdb profile
; the name of the section is the name of a profile, to use as an argument
; for jKVDb::getConnection()
; Parameters in each sections depends of the driver type


;[sectionname] change this

; ----------- Parameters common to all drivers :

; driver type (file, db, memcached)
; driver =  

; ---------- memcached driver
;driver = memcached

; servers list
; Can be a list of HOST_NAME:PORT e.g
;  host = memcache_host1:11211;memcache_host2:11211;memcache_host3:11211
; or
;  host[] = memcache_host1:11211
;  host[] = memcache_host2:11211
;  ...
host = "localhost:11211"

; -------- files driver
;driver = file
;storage_dir = temp:kvfiles/mydatabase/

; Automatic cleaning configuration (not necessary with memcached. 0 means disabled, 1 means systematic cache cleaning of expired data (at each set call), greater values mean less frequent cleaning)
;automatic_cleaning_factor = 0
; enable / disable locking file
;file_locking = 1
; directory level. Set the directory structure level. 0 means "no directory structure", 1 means "one level of directory", 2 means "two levels"...
;directory_level = 2
; umask for directory structure (default '0700')
;directory_umask = ""
; umask for cache files (default '0600')
;file_umask = 

