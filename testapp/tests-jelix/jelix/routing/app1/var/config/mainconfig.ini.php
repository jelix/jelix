;---

modulesPath = app:modules/

disableInstallers = on
;enableAllModules = on

[modules]
jelix.access = 2
aaa.access = 2 

[urlengine]
engine        = basic_significant
enableParser = on
multiview = off
scriptNameServerVariable =
pathInfoInQueryParameter =
basePath = ""
backendBasePath =
checkHttpsOnParsing = on

jelixWWWPath = "jelix/"
jqueryPath="jelix/jquery/"

defaultEntrypoint= index

simple_urlengine_https =

significantFile = "urls.xml"

[simple_urlengine_entrypoints]
index = "@classic"

[basic_significant_urlengine_entrypoints]
index = on
