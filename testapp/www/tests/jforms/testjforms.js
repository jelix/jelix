
function testErrorDecorator(){};
testErrorDecorator.prototype = {
    start : function(){
    },
    addError : function(control, messageType){
        if(messageType == 1){
            //this.message  +="* "+control.errRequired + "\n";
        }else if(messageType == 2){
            //this.message  +="* "+control.errInvalid + "\n";
        }else{
            //this.message  += "* Error on '"+control.label+"' field\n";
        }
    },
    end : function(){
    }
};

jQuery(document).ready(function(){

    var jfo = new jFormsJQForm('jf', 'jf','0');
    jfo.itIsReady = false;
    var jfoElt = $("#jf").get(0);
    jfo.setErrorDecorator(new testErrorDecorator());

    // let's call onFormReady BEFORE declareForm
    jFormsJQ.onFormReady('jf', function(ev) {
       jfo.itIsReady = true
    });

    jFormsJQ.declareForm(jfo);


    var jfo2 = new jFormsJQForm('jf2', 'jf2','0');
    jfo2.itIsReady = false;
    var jfo2Elt = $("#jf2").get(0);
    jfo2.setErrorDecorator(new testErrorDecorator());
    jFormsJQ.declareForm(jfo2);

    // let's call onFormReady AFTER declareForm
    jFormsJQ.onFormReady('jf2', function(ev) {
        jfo2.itIsReady = true
    });


    /*
    module("Module A");
    test("some other test", function(assert) {
      assert.ok( true, "this test is fine" );
      var value = "hello";
      assert.equal( "hello", value, "We expect value to be hello" );
      expect(1);
    });
    */

    QUnit.test("test jFormsJQForm", function(assert) {
        assert.equal(jfo.element, jfoElt, "jfo.element is a form element");
    });

    QUnit.test("test onFormReady", function(assert) {
        assert.ok(jfo.itIsReady);
        assert.ok(jfo2.itIsReady);
    });


    QUnit.test("Input + ControlString", function(assert) {
        var c;
        c = new jFormsJQControlString('nom', 'Your name');
        c.required = true;
        c.errRequired='"Your name" field is required';
        c.errInvalid='"Your name" field is invalid';
        jfo.addControl(c);

        var e = $("#jf_nom").get(0);

        e.setAttribute('value','');
        assert.ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+required should return false");
        assert.ok(!jFormsJQ.verifyForm(jfo.element), "test jFormsJQ.verifyForm, empty value+required should return false");

        e.setAttribute('value','aaa');
        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+required should return true");
        assert.ok(jFormsJQ.verifyForm(jfo.element), "test jFormsJQ.verifyForm, empty value+required should return true");

        c.required = false;
        e.setAttribute('value','');
        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+no required should return true");
        assert.ok(jFormsJQ.verifyForm(jfo.element), "test jFormsJQ.verifyForm, empty value+no required should return true");

        e.setAttribute('value','aaa');
        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, should return true");
        assert.ok(jFormsJQ.verifyForm(jfo.element), "test jFormsJQ.verifyForm, should return true");

        assert.ok(!jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return false");
        assert.equal("aaa",jFormsJQ.getValue(e), "test jFormsJQ.getValue, we expect to have aaa");
        assert.ok(c.check('aaa', jfo), "jFormsJQControlString.check returns true");

        c.minLength = 2;
        assert.ok(c.check('aaa', jfo), "jFormsJQControlString.check with minLength=2 returns true");
        c.minLength = 2;
        assert.ok(c.check('aaa', jfo), "jFormsJQControlString.check with minLength=3 returns true");
        c.minLength = 4;
        assert.ok(!c.check('aaa', jfo), "jFormsJQControlString.check with minLength=4 returns false");
        c.minLength = -1;
        c.maxLength = 5;
        assert.ok(c.check('aaa', jfo), "jFormsJQControlString.check with maxLength=4 returns true");
        c.maxLength = 2;
        assert.ok(!c.check('aaa', jfo), "jFormsJQControlString.check with maxLength=2 returns false");
        c.maxLength = 3;
        assert.ok(c.check('aaa', jfo), "jFormsJQControlString.check with maxLength=3 returns true");


    });


    QUnit.test("Input + ControlSecret", function(assert) {
        var c;
        c = new jFormsJQControlSecret('pwd', 'A password');
        c.errInvalid='"A password" field is invalid';
        jfo.addControl(c);

        var e = $("#jf_pwd").get(0);

        e.setAttribute('value','');
        c.required = true;
        assert.ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+required should return false");
        c.required = false;
        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+no required should return true");
        e.setAttribute('value','aaa');
        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, should return true");

        assert.ok(!jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return false");
        assert.equal("aaa",jFormsJQ.getValue(e), "test jFormsJQ.getValue, we expect to have aaa");
        assert.ok(c.check('aaa', jfo), "jFormsJQControlSecret.check returns true");

        c.minLength = 2;
        assert.ok(c.check('aaa', jfo), "jFormsJQControlSecret.check with minLength=2 returns true");
        c.minLength = 2;
        assert.ok(c.check('aaa', jfo), "jFormsJQControlSecret.check with minLength=3 returns true");
        c.minLength = 4;
        assert.ok(!c.check('aaa', jfo), "jFormsJQControlSecret.check with minLength=4 returns false");
        c.minLength = -1;
        c.maxLength = 5;
        assert.ok(c.check('aaa', jfo), "jFormsJQControlSecret.check with maxLength=4 returns true");
        c.maxLength = 2;
        assert.ok(!c.check('aaa', jfo), "jFormsJQControlSecret.check with maxLength=2 returns false");
        c.maxLength = 3;
        assert.ok(c.check('aaa', jfo), "jFormsJQControlSecret.check with maxLength=3 returns true");
    });


    QUnit.test("Input + ControlConfirm", function(assert) {
        var c;
        c = new jFormsJQControlConfirm('pwd_confirm', 'Type again the password to confirm');
        c.errInvalid='"Type again the password to confirm" field is invalid';
        jfo.addControl(c);

        assert.equal("pwd", c._masterControl, "verify masterControl name");

        var e = $("#jf_pwd_confirm").get(0);
        e.setAttribute('value','');
        c.required = true;
        assert.ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+required should return false");
        c.required = false;
        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+no required should return true");
        e.setAttribute('value','aaa');
        $("#jf_pwd").get(0).setAttribute('value','aaa');
        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, should return true");

        assert.ok(!jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return false");
        assert.equal("aaa",jFormsJQ.getValue(e), "test jFormsJQ.getValue, we expect to have aaa");
        assert.ok(c.check('aaa', jfo), "jFormsJQControlConfirm.check returns true");

        assert.ok(!c.check('a', jfo), "jFormsJQControlConfirm.check returns false");
        assert.ok(!c.check('', jfo), "jFormsJQControlConfirm.check returns false");

    });


    QUnit.test("Radio buttons + ControlString", function(assert) {
        var c;
        c = new jFormsJQControlString('sexe', 'You are ');
        c.errRequired='You should indicate your sex, even if\n              you don\'t know :-)';
        c.errInvalid='"You are " field is invalid';
        jfo.addControl(c);

        var e = jfo.element.elements['sexe'];
        assert.ok(e != null, "the retrieve of the radiobuttons element should not be null");
        assert.ok(jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return true");

        assert.equal(jFormsJQ.getValue(e), null, "jFormsJQ.getValue should return null");

        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, unchecked+not required should return true");
        c.required = true;
        assert.ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, unchecked+required should return false");

        $("#jf_sexe_0").click();
        assert.equal(jFormsJQ.getValue(e), "h", "first item selected, jFormsJQ.getValue should return 'h'");
        $("#jf_sexe_1").click();
        assert.equal(jFormsJQ.getValue(e), "f", "second item selected, jFormsJQ.getValue should return 'f'");
        $("#jf_sexe_2").click();
        assert.equal(jFormsJQ.getValue(e), "no", "third item selected, jFormsJQ.getValue should return 'no'");

        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, checked+required should return true");

    });


    QUnit.test("Single checkbox", function(assert) {
        var c;

        c = new jFormsJQControlBoolean('geek', 'Are you a geek ?');
        c.help='A geek is a person which is extremely keen on computer science';
        c.errInvalid='"Are you a geek ?" field is invalid';
        jfo.addControl(c);

        var e = jfo.element.elements['geek'];

        assert.ok(!jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return false");

        assert.equal(jFormsJQ.getValue(e), false, "jFormsJQ.getValue should return empty array");

        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, unchecked+not required should return true");
        c.required = true;
        assert.ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, unchecked+required should return false");

        $("#jf_geek").click();
        assert.ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, checked+required should return true");

    });

    QUnit.test("DateTime", function(assert) {

        var c = new jFormsJQControlDatetime("meeting", "Next meeting");
        c.multiFields = true;
        jfo.addControl(c);

        var idm = "#jf_meeting_";

        assert.equal(c.getValue(), null, "jFormsJQControlDatetime.getValue on a unfilled datetime");

        $(idm+"month").get(0).selectedIndex = 4;
        $(idm+"day").get(0).selectedIndex = 5;
        $(idm+"year").get(0).value = "1982";
        $(idm+"hour").get(0).selectedIndex = 13;
        $(idm+"minutes").get(0).selectedIndex = 9;
        $(idm+"seconds").get(0).value = "45";

        var val = c.getValue();
        assert.equal(val, "1982-04-05 12:08", "jFormsJQControlDatetime.getValue on a filled datetime");

        assert.ok(c.check('1982-04-05 12:08', jfo), "jFormsJQControlDatetime.check with '1982-04-05 12:08'");

        assert.ok(c.check('1001-04-05 12:08', jfo), "jFormsJQControlDatetime.check with '1001-04-05 12:08'");

        assert.ok(!c.check('1982-04-05 12:93', jfo), "jFormsJQControlDatetime.check with '1982-04-05 12:93'");

    });


    QUnit.test("list build for elements updates", function(assert) {

        jfo.allDependencies = {
            "g": ['e','f'],
            "h": ['f'],
            'e': ['c','d'],
            'f': ['e','d'],
            'd' : ['b'],
            'c' : ['a']
        };
        jfo.controlsToUpdate = [];
        jfo.buildOrderedControlsList('a');
        console.log(jfo.controlsToUpdate)
        assert.deepEqual(jfo.controlsToUpdate, [], "for a, controlsToUpdate should be empty");

        jfo.buildOrderedControlsList('b');
        assert.deepEqual(jfo.controlsToUpdate, [], "for b, controlsToUpdate should be empty");

        jfo.buildOrderedControlsList('c');
        assert.deepEqual(jfo.controlsToUpdate, ['a'], "for c, controlsToUpdate should contain ['a']");

        jfo.buildOrderedControlsList('d');
        assert.deepEqual(jfo.controlsToUpdate, ['b'], "for d, controlsToUpdate should contain ['b']");

        jfo.buildOrderedControlsList('e');
        assert.deepEqual(jfo.controlsToUpdate, ['a','c','b','d'], "for e, controlsToUpdate should contain ['a','c','b','d']");

        jfo.buildOrderedControlsList('f');
        assert.deepEqual(jfo.controlsToUpdate, ['a','c','b','d', 'e'], "for f, controlsToUpdate should contain ['a','c','b','d','e']");

        jfo.buildOrderedControlsList('g');
        assert.deepEqual(jfo.controlsToUpdate, ['a','c','b','d', 'e', 'f'], "for g, controlsToUpdate should contain ['a','c','b','d', 'e', 'f']");

        jfo.buildOrderedControlsList('h');
        assert.deepEqual(jfo.controlsToUpdate, ['a','c','b','d', 'e', 'f'], "for h, controlsToUpdate should contain ['a','c','b','d', 'e', 'f']");

    });

    QUnit.test("submit handlers", function(assert) {
        var ev = {
            target: jfo2Elt
        }
        assert.ok(jFormsJQ._submitListener(ev));

        var result1 = false;
        var result2 = false;
        var hasException = false;
        var called1 = '';
        var called2 = '';

        jfo2.addSubmitHandler(function(ev) { called1 = 'yes'; return result1; });
        assert.ok(!jFormsJQ._submitListener(ev), '1.1 the first handler returns false: the result should be false');
        assert.ok(called1 == 'yes' , '1.2 verify the first handler has been really called');

        result1 = true;
        called1 = '';
        assert.ok(jFormsJQ._submitListener(ev),'2.1 the first handler returns true: the result should be true');
        assert.ok(called1 == 'yes', '2.2 verify the first handler has been really called');

        jfo2.addSubmitHandler(function(ev) { called2 = 'yes';  if(hasException) throw "error"; return result2; }, true);

        result1 = false;
        result2 = false;
        called1 = '';
        called2 = '';
        assert.ok(!jFormsJQ._submitListener(ev), '3.1 two handlers. the first handler returns false: the result should be false');
        assert.ok(called1 == 'yes', '3.2 verify the first handler has been really called');
        assert.ok(called2 == 'yes', '3.3 verify the second handler has been really called');

        result1 = true;
        called1 = '';
        assert.ok(!jFormsJQ._submitListener(ev), '4.1 two handlers. the first handler returns true but not the second: the result should be false');
        assert.ok(called1 == 'yes', '4.2 verify the first handler has been really called');
        assert.ok(called2 == 'yes', '4.3  verify the second handler has been really called');

        result1 = true;
        result2 = true;
        hasException = true;
        called1 = '';
        called2 = '';
        assert.ok(!jFormsJQ._submitListener(ev), '5.1 two handlers. the second handler throw an exception: the result should be false');
        assert.ok(called1 == '', '5.2 verify the first handler has never been really called');
        assert.ok(called2 == 'yes', '5.3 verify the second handler has been really called');


    });


    /*QUnit.test("", function() {
    });
    */


});
