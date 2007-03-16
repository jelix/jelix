<h1>Test de formulaire</h1>
<p>Voici un formulaire de test</p>

<form action="{jurl 'sampleform_save'}" method="POST">
<fieldset>
   <legend>Votre identité</legend>
    <p><label for="nom">Nom :</label> <input type="text" name="nom" id="nom" value="{$form->datas['nom']}"/></p>
    <p><label for="prenom">Prenom :</label> <input type="text" name="prenom" id="prenom" value="{$form->datas['prenom']}" /></p>

</fieldset>
<p><input type="submit" value="ok" /></p>
</form>
