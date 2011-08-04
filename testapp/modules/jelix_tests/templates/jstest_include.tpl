
<script>
var basepath = '{$j_basepath}';
var wwwpath = '{$j_jelixwww}';
var jquerypath = '{$j_jquerypath}';
var test3 = {urljsstring "jelix_tests~jstests:testincludejsinc3"};
{literal}
jQuery(document).ready(function(){

/*
module("Module A");
test("some other test", function() {
  ok( true, "this test is fine" );
  var value = "hello";
  equals( "hello", value, "We expect value to be hello" );
  expect(1);
});
*/

var callbackCalled = false;
var callbackCalled2 = false;

test("include", function() {
    jQuery.include(
        [
            basepath+'tests/testinc1.js',
            [test3, 'js']
        ],
        function() {
            callbackCalled = true;
            jQuery.include(basepath+'tests/testinc2.js', function() {
                callbackCalled2 = true;
            })
        }
    );
});


setTimeout(function(){
    test("callback called", function()  {
        ok(callbackCalled, "callback loaded");
        ok(callbackCalled2, "callback loaded");
        equals($("#includeresult").text(), 'INC1INC3INC2', "include loaded");
    });
}, 1800);

/*test("", function() {
});
*/


});

</script>


{/literal}


 
 <h2 id="banner">Unit tests on jquery include plugin</h2>
 <h2 id="userAgent"></h2>

 <ol id="tests"></ol>

 <div id="main"></div>
 
  <div id="includeresult"></div>