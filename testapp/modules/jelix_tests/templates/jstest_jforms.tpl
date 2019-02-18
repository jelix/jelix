{literal}
<script>


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
var jfoElt = $("#jf").get(0);
jfo.setErrorDecorator(new testErrorDecorator());
jFormsJQ.declareForm(jfo);


var jfo2 = new jFormsJQForm('jf2', 'jf2','0');
var jfo2Elt = $("#jf2").get(0);
jfo2.setErrorDecorator(new testErrorDecorator());
jFormsJQ.declareForm(jfo2);

/*
module("Module A");
test("some other test", function() {
  ok( true, "this test is fine" );
  var value = "hello";
  equals( "hello", value, "We expect value to be hello" );
  expect(1);
});
*/

test("test jFormsJQForm", function() {
    equals(jfo.element, jfoElt, "jfo.element is a form element");
});


test("Input + ControlString", function() {
    var c;
    c = new jFormsJQControlString('nom', 'Your name');
    c.required = true;
    c.errRequired='"Your name" field is required';
    c.errInvalid='"Your name" field is invalid';
    jfo.addControl(c);
    
    var e = $("#jf_nom").get(0);

    e.setAttribute('value','');
    ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+required should return false");
    ok(!jFormsJQ.verifyForm(jfo.element), "test jFormsJQ.verifyForm, empty value+required should return false");

    e.setAttribute('value','aaa');
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+required should return true");
    ok(jFormsJQ.verifyForm(jfo.element), "test jFormsJQ.verifyForm, empty value+required should return true");

    c.required = false;
    e.setAttribute('value','');
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+no required should return true");
    ok(jFormsJQ.verifyForm(jfo.element), "test jFormsJQ.verifyForm, empty value+no required should return true");

    e.setAttribute('value','aaa');
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, should return true");
    ok(jFormsJQ.verifyForm(jfo.element), "test jFormsJQ.verifyForm, should return true");
    
    ok(!jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return false");
    equals("aaa",jFormsJQ.getValue(e), "test jFormsJQ.getValue, we expect to have aaa");    
    ok(c.check('aaa', jfo), "jFormsJQControlString.check returns true");

    c.minLength = 2;
    ok(c.check('aaa', jfo), "jFormsJQControlString.check with minLength=2 returns true");
    c.minLength = 2;
    ok(c.check('aaa', jfo), "jFormsJQControlString.check with minLength=3 returns true");
    c.minLength = 4;
    ok(!c.check('aaa', jfo), "jFormsJQControlString.check with minLength=4 returns false");
    c.minLength = -1;
    c.maxLength = 5;
    ok(c.check('aaa', jfo), "jFormsJQControlString.check with maxLength=4 returns true");
    c.maxLength = 2;
    ok(!c.check('aaa', jfo), "jFormsJQControlString.check with maxLength=2 returns false");
    c.maxLength = 3;
    ok(c.check('aaa', jfo), "jFormsJQControlString.check with maxLength=3 returns true");
    
    
});


test("Input + ControlSecret", function() {
    var c;
    c = new jFormsJQControlSecret('pwd', 'A password');
    c.errInvalid='"A password" field is invalid';
    jfo.addControl(c);
    
    var e = $("#jf_pwd").get(0);

    e.setAttribute('value','');
    c.required = true;
    ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+required should return false");
    c.required = false;
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+no required should return true");
    e.setAttribute('value','aaa');
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, should return true");
    
    ok(!jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return false");
    equals("aaa",jFormsJQ.getValue(e), "test jFormsJQ.getValue, we expect to have aaa");    
    ok(c.check('aaa', jfo), "jFormsJQControlSecret.check returns true");

    c.minLength = 2;
    ok(c.check('aaa', jfo), "jFormsJQControlSecret.check with minLength=2 returns true");
    c.minLength = 2;
    ok(c.check('aaa', jfo), "jFormsJQControlSecret.check with minLength=3 returns true");
    c.minLength = 4;
    ok(!c.check('aaa', jfo), "jFormsJQControlSecret.check with minLength=4 returns false");
    c.minLength = -1;
    c.maxLength = 5;
    ok(c.check('aaa', jfo), "jFormsJQControlSecret.check with maxLength=4 returns true");
    c.maxLength = 2;
    ok(!c.check('aaa', jfo), "jFormsJQControlSecret.check with maxLength=2 returns false");
    c.maxLength = 3;
    ok(c.check('aaa', jfo), "jFormsJQControlSecret.check with maxLength=3 returns true");
});


test("Input + ControlConfirm", function() {
    var c;
    c = new jFormsJQControlConfirm('pwd_confirm', 'Type again the password to confirm');
    c.errInvalid='"Type again the password to confirm" field is invalid';
    jfo.addControl(c);

    equals("pwd", c._masterControl, "verify masterControl name");
    
    var e = $("#jf_pwd_confirm").get(0);
    e.setAttribute('value','');
    c.required = true;
    ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+required should return false");
    c.required = false;
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, empty value+no required should return true");
    e.setAttribute('value','aaa');
    $("#jf_pwd").get(0).setAttribute('value','aaa');
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, should return true");
    
    ok(!jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return false");
    equals("aaa",jFormsJQ.getValue(e), "test jFormsJQ.getValue, we expect to have aaa");    
    ok(c.check('aaa', jfo), "jFormsJQControlConfirm.check returns true");

    ok(!c.check('a', jfo), "jFormsJQControlConfirm.check returns false");
    ok(!c.check('', jfo), "jFormsJQControlConfirm.check returns false");

});


test("Radio buttons + ControlString", function() {
    var c;
    c = new jFormsJQControlString('sexe', 'You are ');
    c.errRequired='You should indicate your sex, even if\n              you don\'t know :-)';
    c.errInvalid='"You are " field is invalid';
    jfo.addControl(c);

    var e = jfo.element.elements['sexe'];
    ok(e != null, "the retrieve of the radiobuttons element should not be null");
    ok(jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return true");

    equals(jFormsJQ.getValue(e), null, "jFormsJQ.getValue should return null");
    
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, unchecked+not required should return true");
    c.required = true;    
    ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, unchecked+required should return false");

    $("#jf_sexe_0").click();
    equals(jFormsJQ.getValue(e), "h", "first item selected, jFormsJQ.getValue should return 'h'");
    $("#jf_sexe_1").click();
    equals(jFormsJQ.getValue(e), "f", "second item selected, jFormsJQ.getValue should return 'f'");
    $("#jf_sexe_2").click();
    equals(jFormsJQ.getValue(e), "no", "third item selected, jFormsJQ.getValue should return 'no'");

    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, checked+required should return true");

});


test("Single checkbox", function() {
    var c;

    c = new jFormsJQControlBoolean('geek', 'Are you a geek ?');
    c.help='A geek is a person which is extremely keen on computer science';
    c.errInvalid='"Are you a geek ?" field is invalid';
    jfo.addControl(c);

    var e = jfo.element.elements['geek'];
    
    ok(!jFormsJQ.isCollection(e), "test jFormsJQ.isCollection, should return false");

    equals(jFormsJQ.getValue(e), false, "jFormsJQ.getValue should return empty array");
    
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, unchecked+not required should return true");
    c.required = true;
    ok(!jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, unchecked+required should return false");
    
    $("#jf_geek").click();
    ok(jFormsJQ.verifyControl(c, jfo), "test jFormsJQ.verifyControl, checked+required should return true");

});

test("DateTime", function() {
  
  var c = new jFormsJQControlDatetime("meeting", "Next meeting");
  c.multiFields = true;
  jfo.addControl(c);
  
  var idm = "#jf_meeting_";
  
  equals(c.getValue(), null, "jFormsJQControlDatetime.getValue on a unfilled datetime");

  $(idm+"month").get(0).selectedIndex = 4;
  $(idm+"day").get(0).selectedIndex = 5;
  $(idm+"year").get(0).value = "1982";
  $(idm+"hour").get(0).selectedIndex = 13;
  $(idm+"minutes").get(0).selectedIndex = 9;
  $(idm+"seconds").get(0).value = "45";

  var val = c.getValue();
  equals(val, "1982-04-05 12:08", "jFormsJQControlDatetime.getValue on a filled datetime");

  ok(c.check('1982-04-05 12:08', jfo), "jFormsJQControlDatetime.check with '1982-04-05 12:08'");

  ok(c.check('1001-04-05 12:08', jfo), "jFormsJQControlDatetime.check with '1001-04-05 12:08'");

  ok(!c.check('1982-04-05 12:93', jfo), "jFormsJQControlDatetime.check with '1982-04-05 12:93'");

});


test("list build for elements updates", function() {

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
    isSet(jfo.controlsToUpdate, [], "for a, controlsToUpdate should be empty");

    jfo.buildOrderedControlsList('b');
    isSet(jfo.controlsToUpdate, [], "for b, controlsToUpdate should be empty");
    
    jfo.buildOrderedControlsList('c');
    isSet(jfo.controlsToUpdate, ['a'], "for c, controlsToUpdate should contain ['a']");
    
    jfo.buildOrderedControlsList('d');
    isSet(jfo.controlsToUpdate, ['b'], "for d, controlsToUpdate should contain ['b']");

    jfo.buildOrderedControlsList('e');
    isSet(jfo.controlsToUpdate, ['a','c','b','d'], "for e, controlsToUpdate should contain ['a','c','b','d']");

    jfo.buildOrderedControlsList('f');
    isSet(jfo.controlsToUpdate, ['a','c','b','d', 'e'], "for f, controlsToUpdate should contain ['a','c','b','d','e']");

    jfo.buildOrderedControlsList('g');
    isSet(jfo.controlsToUpdate, ['a','c','b','d', 'e', 'f'], "for g, controlsToUpdate should contain ['a','c','b','d', 'e', 'f']");

    jfo.buildOrderedControlsList('h');
    isSet(jfo.controlsToUpdate, ['a','c','b','d', 'e', 'f'], "for h, controlsToUpdate should contain ['a','c','b','d', 'e', 'f']");

});


test("submit handlers", function() {
    var ev = {
        target: jfo2Elt
    }
    ok(jFormsJQ._submitListener(ev));
    
    var result1 = false;
    var result2 = false;
    var hasException = false;
    var called1 = '';
    var called2 = '';
    
    jfo2.addSubmitHandler(function(ev) { called1 = 'yes'; return result1; });
    ok(!jFormsJQ._submitListener(ev), '1.1 the first handler returns false: the result should be false');
    ok(called1 == 'yes' , '1.2 verify the first handler has been really called');

    result1 = true;
    called1 = '';
    ok(jFormsJQ._submitListener(ev),'2.1 the first handler returns true: the result should be true');
    ok(called1 == 'yes', '2.2 verify the first handler has been really called');

    jfo2.addSubmitHandler(function(ev) { called2 = 'yes';  if(hasException) throw "error"; return result2; }, true);

    result1 = false;
    result2 = false;
    called1 = '';
    called2 = '';
    ok(!jFormsJQ._submitListener(ev), '3.1 two handlers. the first handler returns false: the result should be false');
    ok(called1 == 'yes', '3.2 verify the first handler has been really called');
    ok(called2 == 'yes', '3.3 verify the second handler has been really called');

    result1 = true;
    called1 = '';
    ok(!jFormsJQ._submitListener(ev), '4.1 two handlers. the first handler returns true but not the second: the result should be false');
    ok(called1 == 'yes', '4.2 verify the first handler has been really called');
    ok(called2 == 'yes', '4.3  verify the second handler has been really called');

    result1 = true;
    result2 = true;
    hasException = true;
    called1 = '';
    called2 = '';
    ok(!jFormsJQ._submitListener(ev), '5.1 two handlers. the second handler throw an exception: the result should be false');
    ok(called1 == '', '5.2 verify the first handler has never been really called');
    ok(called2 == 'yes', '5.3 verify the second handler has been really called');


});


/*test("", function() {
});
*/


});

</script>


{/literal}


 
 <h2 id="banner">Unit tests on jforms.js</h2>
 <h2 id="userAgent"></h2>

 <ol id="tests"></ol>

 <div id="main"></div>
 

 <hr />
 <p>Test form, don't use it.</p>
 
 <form action="/index.php" method="post" id="jf" enctype="multipart/form-data">
<table class="jforms-table" border="0"><tr><td colspan="2"><fieldset><legend>Your identity</legend>
<table class="jforms-table-group" border="0">
<tr><th scope="row"><label class="jforms-label jforms-required" for="jf_nom">Your name</label></th>
    <td><input type="text" name="nom" id="jf_nom" class=" jforms-required" value=""></td></tr>

<tr><th scope="row"><span class="jforms-label jforms-required">You are </span></th>
    <td><span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="jf_sexe_0" value="h" class=" jforms-required"><label for="jf_sexe_0">a man</label></span>
    <span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="jf_sexe_1" value="f" class=" jforms-required"><label for="jf_sexe_1">a woman</label></span>
    <span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="jf_sexe_2" value="no" class=" jforms-required"><label for="jf_sexe_2">you don't know</label></span>
    </td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_mail">Your email</label></th>
    <td><input type="text" name="mail" id="jf_mail" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_geek" title="Check the box if you are a geek">Are you a geek ?</label></th>
    <td><input type="checkbox" name="geek" id="jf_geek" title="Check the box if you are a geek" value="1"><span class="jforms-help">A geek is a person which is extremely keen on computer science</span></td></tr>

</table></fieldset></td></tr>


<tr><th scope="row"><label class="jforms-label" for="jf_conf">Select one value</label></th>
  <td><select name="conf" id="jf_conf" size="1">
        <option value="" selected="selected">-- choices --</option>
        <option value="foo">foovalue</option>
        <option value="bar">barvalue</option>
        <option value="name">laurent</option>
        <option value="engine">jelix</option>
        <option value="browser">firefox</option>
        <option value="33">456ghjk</option>
        <option value="test">33</option>
        <option value="ddd">ffff</option>
  </select></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_home">You leave at</label></th>
<td><select name="home" id="jf_home" size="4">
<option value="pa">Paris</option>
<option value="ma">Marseille</option>
<option value="ly">Lyon</option>
<option value="br">Brest</option>
<option value="li">Lille</option>
<option value="bo">Bordeaux</option>
</select></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_address">Your address</label></th>
<td><textarea name="address" id="jf_address" rows="5" cols="40"></textarea></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_description">Description in html</label></th>
 <td><textarea name="description" id="jf_description" rows="5" cols="40"></textarea></td></tr>

<tr><th scope="row"><span class="jforms-label jforms-required">You have </span></th>
<td><span class="jforms-chkbox jforms-ctl-objets">
<input type="checkbox" name="objets[]" id="jf_objets_0" value="maison" class=" jforms-required"><label for="jf_objets_0">a house</label></span>
<span class="jforms-chkbox jforms-ctl-objets"><input type="checkbox" name="objets[]" id="jf_objets_1" value="voiture" class=" jforms-required"><label for="jf_objets_1">a car</label></span>
<span class="jforms-chkbox jforms-ctl-objets"><input type="checkbox" name="objets[]" id="jf_objets_2" value="bateau" class=" jforms-required"><label for="jf_objets_2">a boat</label></span>
<span class="jforms-chkbox jforms-ctl-objets"><input type="checkbox" name="objets[]" id="jf_objets_3" value="assiette" class=" jforms-required"><label for="jf_objets_3">a plate</label></span></td>
</tr>

<tr><th scope="row"><span class="jforms-label">Your birthday</span></th>
<td><select id="jf_datenaissance_month" name="datenaissance[month]">
<option value="">Month</option>
<option value="01">January</option>
<option value="02">February</option>
<option value="03">March</option>
<option value="04">April</option>
<option value="05">May</option>
<option value="06">June</option>
<option value="07">July</option>
<option value="08">August</option>
<option value="09">September</option>
<option value="10">October</option>
<option value="11">November</option>
<option value="12">December</option>
</select>

<select id="jf_datenaissance_day" name="datenaissance[day]">
    <option value="">Day</option><option value="01">01</option>
    <option value="02">02</option><option value="03">03</option><option value="04">04</option>
    <option value="05">05</option><option value="06">06</option><option value="07">07</option>
    <option value="08">08</option><option value="09">09</option><option value="10">10</option>
    <option value="11">11</option><option value="12">12</option><option value="13">13</option>
    <option value="14">14</option><option value="15">15</option><option value="16">16</option>
    <option value="17">17</option><option value="18">18</option><option value="19">19</option>
    <option value="20">20</option><option value="21">21</option><option value="22">22</option>
    <option value="23">23</option><option value="24">24</option><option value="25">25</option>
    <option value="26">26</option><option value="27">27</option><option value="28">28</option>
    <option value="29">29</option><option value="30">30</option><option value="31">31</option>
</select>

<input type="text" size="4" maxlength="4" id="jf_datenaissance_year" name="datenaissance[year]" value=""></td></tr>



<tr><th scope="row"><span class="jforms-label">Next meeting</span></th>
<td>
  <select id="jf_meeting_month" name="meeting[month]">
    <option value="">Month</option>
    <option value="01">January</option>
    <option value="02">February</option>
    <option value="03">March</option>
    <option value="04">April</option>
    <option value="05">May</option>
    <option value="06">June</option>
    <option value="07">July</option>
    <option value="08">August</option>
    <option value="09">September</option>
    <option value="10">October</option>
    <option value="11">November</option>
    <option value="12">December</option>
  </select>
  <select id="jf_meeting_day" name="meeting[day]">
    <option value="">Day</option><option value="01">01</option><option value="02">02</option>
    <option value="03">03</option><option value="04">04</option><option value="05">05</option>
    <option value="06">06</option><option value="07">07</option><option value="08">08</option>
    <option value="09">09</option><option value="10">10</option><option value="11">11</option>
    <option value="12">12</option><option value="13">13</option><option value="14">14</option>
    <option value="15">15</option><option value="16">16</option><option value="17">17</option>
    <option value="18">18</option><option value="19">19</option><option value="20">20</option>
    <option value="21">21</option><option value="22">22</option><option value="23">23</option>
    <option value="24">24</option><option value="25">25</option><option value="26">26</option>
    <option value="27">27</option><option value="28">28</option><option value="29">29</option>
    <option value="30">30</option><option value="31">31</option>
  </select>
  <input type="text" size="4" maxlength="4" id="jf_meeting_year" name="meeting[year]" value=""/>
  
  <select id="jf_meeting_hour" name="meeting[hour]">
    <option value="">Hour</option><option value="00">00</option>
    <option value="01">01</option><option value="02">02</option>
    <option value="03">03</option><option value="04">04</option>
    <option value="05">05</option><option value="06">06</option>
    <option value="07">07</option><option value="08">08</option>
    <option value="09">09</option><option value="10">10</option>
    <option value="11">11</option><option value="12">12</option>
    <option value="13">13</option><option value="14">14</option>
    <option value="15">15</option><option value="16">16</option><option value="17">17</option>
    <option value="18">18</option><option value="19">19</option><option value="20">20</option>
    <option value="21">21</option><option value="22">22</option><option value="23">23</option>
  </select>
  
  <select id="jf_meeting_minutes" name="meeting[minutes]">
    <option value="">Minutes</option><option value="00">00</option><option value="01">01</option>
    <option value="02">02</option><option value="03">03</option><option value="04">04</option>
    <option value="05">05</option><option value="06">06</option><option value="07">07</option>
    <option value="08">08</option><option value="09">09</option><option value="10">10</option>
    <option value="11">11</option><option value="12">12</option><option value="13">13</option>
    <option value="14">14</option><option value="15">15</option><option value="16">16</option>
    <option value="17">17</option><option value="18">18</option><option value="19">19</option>
    <option value="20">20</option><option value="21">21</option><option value="22">22</option>
    <option value="23">23</option><option value="24">24</option><option value="25">25</option>
    <option value="26">26</option><option value="27">27</option><option value="28">28</option>
    <option value="29">29</option><option value="30">30</option><option value="31">31</option>
    <option value="32">32</option><option value="33">33</option><option value="34">34</option>
    <option value="35">35</option><option value="36">36</option><option value="37">37</option>
    <option value="38">38</option><option value="39">39</option><option value="40">40</option>
    <option value="41">41</option><option value="42">42</option><option value="43">43</option>
    <option value="44">44</option><option value="45">45</option><option value="46">46</option>
    <option value="47">47</option><option value="48">48</option><option value="49">49</option>
    <option value="50">50</option><option value="51">51</option><option value="52">52</option>
    <option value="53">53</option><option value="54">54</option><option value="55">55</option>
    <option value="56">56</option><option value="57">57</option><option value="58">58</option>
    <option value="59">59</option></select>
  
  <input type="hidden" id="jf_meeting_seconds" name="meeting[seconds]" value=""/>
</td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_pwd">A password</label></th>
<td><input type="password" name="pwd" id="jf_pwd" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_pwd_confirm">Type again the password to confirm</label></th>
<td><input type="password" name="pwd_confirm" id="jf_pwd_confirm" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_image">Your photo (gif/png &lt; 50ko)</label></th>
<td><input type="hidden" name="MAX_FILE_SIZE" value="50000"><input type="file" name="image" id="jf_image" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label jforms-required" for="jf_unwanted">This field shouldn't appear, it is deactivated</label></th>
<td><input type="text" name="unwanted" id="jf_unwanted" class=" jforms-required" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_task">Task status</label></th>
<td><ul class="jforms-choice jforms-ctl-task" >

<li><label><input type="radio" name="task" id="jf_task_0" value="new" onclick="jFormsJQ.getForm('jforms_jelix_tests_formtest').getControl('task').activate('new')">New</label></li>

<li><label><input type="radio" name="task" id="jf_task_1" value="assigned" onclick="jFormsJQ.getForm('jforms_jelix_tests_formtest').getControl('task').activate('assigned')">Assigned</label>
<span class="jforms-item-controls"><label class="jforms-label jforms-required" for="jf_assignee">assignee name</label>
<input type="text" name="assignee" id="jf_assignee" class=" jforms-required" value=""></span>
</li>

<li><label><input type="radio" name="task" id="jf_task_2" value="closed" onclick="jFormsJQ.getForm('jforms_jelix_tests_formtest').getControl('task').activate('closed')">Closed</label>
<span class="jforms-item-controls"><label class="jforms-label" for="jf_task-done">Status</label>
<select name="task-done" id="jf_task-done" size="1">
<option value="" selected="selected"></option><option value="done">Done</option>
<option value="cancelled">Cancelled</option><option value="later">Later</option></select></span>
</li>
</ul>
</td></tr>

<tr><th scope="row"><label class="jforms-label jforms-required" for="jf_cap">Antispam filter</label></th>
<td><span class="jforms-captcha-question">How much do 5 plus 3</span> <input type="text" name="cap" id="jf_cap" class=" jforms-required" value=""></td></tr></table>

<div class="jforms-submit-buttons">
    <button type="reset" name="cancel" id="jf_cancel" class="jforms-reset">Cancel</button>
<input type="submit" name="valid" id="jf_valid_svg" class="jforms-submit" value="Save"/>
<input type="submit" name="valid" id="jf_valid_prev" class="jforms-submit" value="Preview"/>

</div>
{literal}
<script type="text/javascript">
//<![CDATA[
/*

c = new jFormsJQControlEmail('mail', 'Your email');
c.errInvalid='"Your email" field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlString('conf', 'Select one value');
c.errInvalid='"Select one value" field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlString('home', 'You leave at');
c.errInvalid='"You leave at" field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlString('address', 'Your address');
c.errInvalid='"Your address" field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlString('description', 'Description in html');
c.errInvalid='"Description in html" field is invalid';
jFormsJQ.tForm.addControl(c);
jelix_ckeditor_ckdefault("jf_description","jforms_jelix_tests_formtest");

c = new jFormsJQControlString('objets[]', 'You have ');
c.required = true;
c.errRequired='"You have " field is required';
c.errInvalid='"You have " field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlDate('datenaissance', 'Your birthday');
c.multiFields = true;
c.errInvalid='"Your birthday" field is invalid';
jelix_datepicker_default(c,'en_US','/');
jFormsJQ.tForm.addControl(c);



c = new jFormsJQControlString('image', 'Your photo (gif/png < 50ko)');
c.errInvalid='The file should be a gif or png image, under 50000 octets';
jFormsJQ.tForm.addControl(c);
c = new jFormsJQControlDatetime('unwanted', 'This field shouldn\'t appear, it is deactivated');
c.required = true;
c.errRequired='"This field shouldn\'t appear, it is deactivated" field is required';
c.errInvalid='"This field shouldn\'t appear, it is deactivated" field is invalid';
jFormsJQ.tForm.addControl(c);
c = new jFormsJQControlChoice('task', 'Task status');
c.errInvalid='"Task status" field is invalid';
jFormsJQ.tForm.addControl(c);
c2 = c;
c2.items['new']=[];
c = new jFormsJQControlString('assignee', 'assignee name');
c.required = true;
c.errRequired='"assignee name" field is required';
c.errInvalid='"assignee name" field is invalid';
c2.addControl(c, 'assigned');
c = new jFormsJQControlString('task-done', 'Status');
c.errInvalid='"Status" field is invalid';
c2.addControl(c, 'closed');
c2.activate('');
c = new jFormsJQControlString('cap', 'Antispam filter');
c.required = true;
c.errRequired='"Antispam filter" field is required';
c.errInvalid='"Antispam filter" field is invalid';
jFormsJQ.tForm.addControl(c);

})();*/
//]]>
</script>{/literal}</form> 

  <form action="/index.php" method="post" id="jf2">
    
  </form>
 
