
; path related to the ini file. By default, the ini file is expected to be into the myapp/install/ directory.
pagesPath = "../../lib/installwizard/pages/"
customPath = "wizard/"
start = welcome
tempPath = "../../temp/testapp/"
supportedLang = en

appname = TestApp

[welcome.step]
next=checkjelix

[checkjelix.step]
next=dbprofile

[dbprofile.step]
next=end
availabledDrivers="mysql,sqlite,pgsql"
ignoreProfiles="jelix_tests_mysql,jelix_tests_forward"
messageHeader="message.header.dbProfile"

[end.step]
noprevious = on