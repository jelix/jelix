<h3>Results of findAll</h3>
<table id="findall">
   <tr><th>key</th><th>value</th></tr>
  {foreach $config as $conf}
  <tr><td>{$conf->ckey}</td><td>{$conf->cvalue}</td></tr>
  {/foreach}
</table>
<p>CountAll gives : <span id="countall">{$nombre}</span></p>
<p>getCountValue gives : <span id="getcountvalue">{$nombrevalue}</span> (number of values which contain the word "value")</p>

<h3>Using findBy</h3>
<p>It searches keys "foo" or "bar"</p>
<table id="findby">
   <tr><th>key</th><th>value</th></tr>
  {foreach $petitconfig as $conf}
  <tr><td>{$conf->ckey}</td><td>{$conf->cvalue}</td></tr>
  {/foreach}
</table>
<h3>Results of get('foo')</h3>
<p id="getfoo">{if $oneconf}key={$oneconf->ckey} value={$oneconf->cvalue}{else}inexistant{/if}</p>

<h3>Insert Test</h3>
<form action="{jurl 'testapp~main:testdao'}" method="POST" id="configform">
<fieldset><legend>Add a new key</legend>
<p><label for="newid">id :</label><input type="text" name="newid"  id="newid"/></p>
<p><label for="newvalue">Value :</label><input type="text" name="newvalue" id="newvalue" /></p>
<p><input type="submit" value="Save" id="submitsave"/></p>
</form>
Note: it is not using jforms here.
