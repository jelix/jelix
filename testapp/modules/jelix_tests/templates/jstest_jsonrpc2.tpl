
<script>

var urljsonrpc = "{$j_basepath}jsonrpc.php";

{literal}
jQuery(document).ready(function(){

test("test first call", function() {
  
  var jsonrpc = { method : "jelix_tests~jstests:first", id:"", params: {}};
  
  var toSend = JSON.stringify(jsonrpc);
  var p = new XMLHttpRequest();
  p.onload = null;
  p.open("POST", urljsonrpc, false);
  p.send(toSend);
  if(p.status == 200) {  
    equals(p.responseText, '{"result":["coucou"],"error":null,"id":""}', "response first text");
  }
  else ok(false, "bad http response ("+p.responseText+")");

});


test("test second call", function() {
  
  var jsonrpc = { method : "jelix_tests~jstests:second", id:"", params: {}};
  
  var toSend = JSON.stringify(jsonrpc);
  var p = new XMLHttpRequest();
  p.onload = null;
  p.open("POST", urljsonrpc, false);
  p.send(toSend);
  if(p.status == 200) {  
    equals(p.responseText, '{"result":1564,"error":null,"id":""}', "response second text");
  }
  else ok(false, "bad http response ("+p.responseText+")");

});


test("test third call with response parsing", function() {
  
  var jsonrpc = { method : "jelix_tests~jstests:first", id:"", params: {}};
  
  var toSend = JSON.stringify(jsonrpc);
  var p = new XMLHttpRequest();
  p.onload = null;
  p.open("POST", urljsonrpc, false);
  p.send(toSend);
  if(p.status == 200) {
    var resp = JSON.parse(p.responseText);
    equals(resp.result[0], 'coucou', 'response third test');
  }
  else ok(false, "bad http response ("+p.responseText+")");

});



/*test("", function() {
});
*/


}); // end of ready()
</script>
{/literal}

 <h2 id="banner">Unit tests for jsonRpc (with json2.js)</h2>
 <h2 id="userAgent"></h2>
 <ol id="tests"></ol>
 <div id="main"></div>
 
