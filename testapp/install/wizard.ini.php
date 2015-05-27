
; path related to the ini file. By default, the ini file is expected to be into the myapp/install/ directory.
pagesPath = "../../lib/installwizard/pages/"
customPath = "wizard/"
start = welcome
tempPath = "../temp/"
supportedLang = en

appname = TestApp

[welcome.step]
next=checkjelix

[checkjelix.step]
next=dbprofile

[dbprofile.step]
next=installapp
availabledDrivers="mysql,sqlite3,pgsql"
ignoreProfiles="jelix_tests_mysql,jelix_tests_forward"
messageHeader="message.header.dbProfile"

[installapp.step]
next=end
level=notice

[end.step]
noprevious = on
messageFooter = "message.footer.end"