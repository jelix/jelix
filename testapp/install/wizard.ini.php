
; path related to the ini file. By default, the ini file is expected to be into the myapp/install/ directory.
pagesPath = "../../lib/installwizard/pages/"
customPath =
;"custom/"
start = welcome
tempPath = "../../temp/testapp/"
supportedLang = en

[welcome.step]
next=checkjelix

[checkjelix.step]
next=end

[end.step]
noprevious = on