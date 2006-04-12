
<table>
   <tr><th>key</th><th>value</th></tr>
  {foreach $config as $conf}
  <tr><td>{$conf->ckey}</td><td>{$conf->cvalue}</td></tr>
  {/foreach}
</table>
<p>Count = {$nombre}</p>
<p>Count of values that contains "value" = {$nombrevalue}</p>

<p>Selection de deux enregistrements:</p>
<table>
   <tr><th>key</th><th>value</th></tr>
  {foreach $petitconfig as $conf}
  <tr><td>{$conf->ckey}</td><td>{$conf->cvalue}</td></tr>
  {/foreach}
</table>

<p>one conf key={$oneconf->ckey} value={$oneconf->cvalue}</p>

<form action="{jurl 'testapp~main_testdao'}" method="POST">
<fieldset><legend>Ajouter une nouvelle clé</legend>
<p><label for="newid">id :</label><input type="text" name="newid"  id="newid"/></p>
<p><label for="newvalue">Valeur :</label><input type="text" name="newvalue" id="newvalue" /></p>
<p><input type="submit" value="enregistrer" /></p>
</form>

