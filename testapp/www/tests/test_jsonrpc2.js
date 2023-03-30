jQuery(document).ready(function(){

    var urljsonrpc = document.getElementById('data').dataset.urljsonrpc;

    QUnit.test("test first call", function(assert) {

        var jsonrpc = { method : "jelix_tests~jstests:first", id:"", params: {}};

        var toSend = JSON.stringify(jsonrpc);
        var p = new XMLHttpRequest();
        p.onload = null;
        p.open("POST", urljsonrpc, false);
        p.send(toSend);
        if(p.status == 200) {
            assert.equal(p.responseText, '{"result":["coucou"],"error":null,"id":""}', "response first text");
        }
        else assert.ok(false, "bad http response ("+p.responseText+")");

    });


    QUnit.test("test second call", function(assert) {

        var jsonrpc = { method : "jelix_tests~jstests:second", id:"", params: {}};

        var toSend = JSON.stringify(jsonrpc);
        var p = new XMLHttpRequest();
        p.onload = null;
        p.open("POST", urljsonrpc, false);
        p.send(toSend);
        if(p.status == 200) {
            assert.equal(p.responseText, '{"result":1564,"error":null,"id":""}', "response second text");
        }
        else assert.ok(false, "bad http response ("+p.responseText+")");

    });


    QUnit.test("test third call with response parsing", function(assert) {

        var jsonrpc = { method : "jelix_tests~jstests:first", id:"", params: {}};

        var toSend = JSON.stringify(jsonrpc);
        var p = new XMLHttpRequest();
        p.onload = null;
        p.open("POST", urljsonrpc, false);
        p.send(toSend);
        if(p.status == 200) {
            var resp = JSON.parse(p.responseText);
            assert.equal(resp.result[0], 'coucou', 'response third test');
        }
        else assert.ok(false, "bad http response ("+p.responseText+")");

    });


}); // end of ready()