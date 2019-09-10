
var testUrl = 'http://testapp18.local';

if (casper.cli.has("testurl"))
    testUrl = casper.cli.get('testurl');


casper.echo("Tests will be done on "+testUrl);
casper.test.done();