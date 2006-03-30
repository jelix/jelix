<h1>Test de formulaire</h1>
<p>Voici un formulaire de test</p>

<form action="{jurl 'forms_save'}" method="POST">
<fieldset>
   <legend>Votre identité</legend>
    <input type="hidden" name="id" value="{$form->id}" />
    <p><label for="nom">Nom :</label> <input type="text" name="nom" id="nom" /></p>
    <p><label for="prenom">Prenom :</label> <input type="text" name="prenom" id="prenom" /></p>

</fieldset>
<p><input type="submit" value="ok" /></p>
</form>
