

casper.start(testUrl+'/index.php/testapp/main/resetdao', function() {
    this.test.assertEquals(this.fetchText('#main'), '\nreset done\n', "reset ok?");
});

casper.thenOpen(testUrl+'/index.php/testapp/main/testdao', function() {
    var r = this.evaluate(function() {
        var result=[]
        var listtd = document.querySelectorAll('#findall tr td');
        for(var i=0; i < listtd.length; i++)
            result.push(listtd[i].textContent);
        return result;
    });
    this.test.assertEquals(r.length, 0, 'table findall should be empty');   

    var r = this.evaluate(function() {
        var result=[]
        var listtd = document.querySelectorAll('#findby tr td');
        for(var i=0; i < listtd.length; i++)
            result.push(listtd[i].textContent);
        return result;
    });
    this.test.assertEquals(r.length, 0, 'table findby should be empty');

    this.test.assertEquals(this.fetchText('#countall'), '0', "countall should be 0");
    this.test.assertEquals(this.fetchText('#getcountvalue'), '0', "getcountvalue should be 0");
    this.test.assertEquals(this.fetchText('#getfoo'), 'inexistant', "getfoo should be empty");

    this.fill('form#configform', {
        'newid':    'hello',
        'newvalue': 'world',
    }, true);
});

casper.then(function(){
    var r = this.evaluate(function() {
        var result=[]
        var listtd = document.querySelectorAll('#findall tr td');
        for(var i=0; i < listtd.length; i++)
            result.push(listtd[i].textContent);
        return result;
    });
    this.test.assertEquals(r.length, 2, 'table findall should have 1 row');   
    this.test.assertEquals(r, ['hello', 'world'], 'table findall contain the new record');

    var r = this.evaluate(function() {
        var result=[]
        var listtd = document.querySelectorAll('#findby tr td');
        for(var i=0; i < listtd.length; i++)
            result.push(listtd[i].textContent);
        return result;
    });
    this.test.assertEquals(r.length, 0, 'table findby should be empty');

    this.test.assertEquals(this.fetchText('#countall'), '1', "countall should be 1");
    this.test.assertEquals(this.fetchText('#getcountvalue'), '0', "getcountvalue should be 0");
    this.test.assertEquals(this.fetchText('#getfoo'), 'inexistant', "getfoo should be empty");

    this.fill('form#configform', {
        'newid':    'foo',
        'newvalue': 'this is a value',
    }, true);
    
});


casper.then(function(){
    var r = this.evaluate(function() {
        var result=[]
        var listtd = document.querySelectorAll('#findall tr td');
        for(var i=0; i < listtd.length; i++)
            result.push(listtd[i].textContent);
        return result;
    });
    this.test.assertEquals(r.length, 4, 'table findall should have 2 row');   
    this.test.assertEquals(r, ['hello', 'world', 'foo', 'this is a value'], 'table findall contain the new record');

    var r = this.evaluate(function() {
        var result=[]
        var listtd = document.querySelectorAll('#findby tr td');
        for(var i=0; i < listtd.length; i++)
            result.push(listtd[i].textContent);
        return result;
    });
    this.test.assertEquals(r.length, 2, 'table findby should have 1 row');
    this.test.assertEquals(r, ['foo', 'this is a value'], 'table findby contain the new record');

    this.test.assertEquals(this.fetchText('#countall'), '2', "countall should be 2");
    this.test.assertEquals(this.fetchText('#getcountvalue'), '1', "getcountvalue should be 1");
    this.test.assertEquals(this.fetchText('#getfoo'), 'key=foo value=this is a value', "getfoo should not be empty");

});


casper.run(function() {
    this.test.done();
});

