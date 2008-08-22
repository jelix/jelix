<h1>jForms test (multiple instances)</h1>
<p>Fill the form.</p>

<form action="{formurl 'forms:save',array()}" method="POST">

<fieldset>
   <legend>Your identity</legend>
    <p><label for="nom">Lastname:</label> <input type="text" name="nom" id="nom" value="{$form->data['nom']}"/></p>
    <p><label for="prenom">Firstname:</label> <input type="text" name="prenom" id="prenom" value="{$form->data['prenom']}" /></p>

</fieldset>
<p>id form : <input type="text" name="newid" value="{$id}" 
    {if $id!='0'}readonly="readonly" style="color:#aaa"{/if}/><br/>
{formurlparam 'forms:save',array()}
<input type="hidden" value="{$id}" name="id" />
<input type="submit" value="ok" /></p>
</form>

<p><a href="{jurl 'forms:listform'}">Cancel</a></p>
