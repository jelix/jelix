<h3>Résultat d'un findAll</h3>
<table>
   <tr><th>key</th><th>value</th></tr>
  {foreach $config as $conf}
  <tr><td>{$conf->ckey}</td><td>{$conf->cvalue}</td></tr>
  {/foreach}
</table>
<p>CountAll donne : {$nombre}</p>
<p>getCountValue donne : {$nombrevalue} (nombre de valeur contenant le mot "value")</p>

<h3>Utilisation d'un findBy</h3>
<p>cherchant les clés foo ou bar</p>
<table>
   <tr><th>key</th><th>value</th></tr>
  {foreach $petitconfig as $conf}
  <tr><td>{$conf->ckey}</td><td>{$conf->cvalue}</td></tr>
  {/foreach}
</table>
<h3>Résultat d'un get('foo')</h3>
<p>key={$oneconf->ckey} value={$oneconf->cvalue}</p>

<h3>Test insertion</h3>
<form action="{jurl 'testapp~main_testdao'}" method="POST">
<fieldset><legend>Ajouter une nouvelle clé</legend>
<p><label for="newid">id :</label><input type="text" name="newid"  id="newid"/></p>
<p><label for="newvalue">Valeur :</label><input type="text" name="newvalue" id="newvalue" /></p>
<p><input type="submit" value="enregistrer" /></p>
</form>

