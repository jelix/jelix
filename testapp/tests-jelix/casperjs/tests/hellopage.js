

casper.start(testUrl+'/index.php/testapp/main/hello', function() {
    this.test.assertEquals(this.fetchText('h1'), 'Hello YOU !', "Check page title");
    this.click('h1+p+p a');
});

casper.then(function(){
    this.test.assertEquals(this.fetchText('h1'), 'Hello BOB !', "Check page title with a given name");
})

casper.thenOpen(testUrl+'/index.php/testapp/main/hello?output=text', function(){
    this.test.assertEquals(this.getPageContent(), 'Hello World !', "load a page using a text response");
})

casper.run(function() {
    this.test.done(3);
});