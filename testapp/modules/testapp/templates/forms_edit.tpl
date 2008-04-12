<h1>Test de formulaire (instances multiples)</h1>
<p>Remplissez le formulaire</p>

<form action="{formurl 'forms:save',array()}" method="POST">

<fieldset>
   <legend>Votre identité</legend>
    <p><label for="nom">Nom :</label> <input type="text" name="nom" id="nom" value="{$form->data['nom']}"/></p>
    <p><label for="prenom">Prenom :</label> <input type="text" name="prenom" id="prenom" value="{$form->data['prenom']}" /></p>

</fieldset>
<p>id form : <input type="text" name="newid" value="{$id}" 
    {if $id!='0'}readonly="readonly" style="color:#aaa"{/if}/><br/>
{formurlparam 'forms:save',array()}
<input type="hidden" value="{$id}" name="id" />
<input type="submit" value="ok" /></p>
</form>

<p><a href="{jurl 'forms:listform'}">Annuler</a></p>
