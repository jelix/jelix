<h1>Test de formulaire (instances multiples)</h1>
<p>Remplissez le formulaire</p>

<form action="{jurl 'forms:save'}" method="POST">
<fieldset>
   <legend>Votre identité</legend>
    <p><label for="nom">Nom :</label> <input type="text" name="nom" id="nom" value="{$form->datas['nom']}"/></p>
    <p><label for="prenom">Prenom :</label> <input type="text" name="prenom" id="prenom" value="{$form->datas['prenom']}" /></p>

</fieldset>
<p>id form : <input type="text" name="newid" value="{$id}" 
    {if $id!='0'}readonly="readonly" style="color:#aaa"{/if}/><br/>
<input type="hidden" value="{$id}" name="id" />
<input type="submit" value="ok" /></p>
</form>

<p><a href="{jurl 'forms:listform'}">Annuler</a></p>
