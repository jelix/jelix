<h1>Test de formulaire (instances multiples)</h1>
<p>Voici la liste des instances du formulaire sample</p>

{if count($liste)}
<table border="1">
{foreach $liste as $id=>$form}
    <tr>
    <td>{$id}</td>
    <td>{$form->datas['nom']}</td>
    <td>{$form->datas['prenom']}</td>
    <td>
        <a href="{jurl 'forms_view',array('id'=>$id)}">voir</a>
        <a href="{jurl 'forms_showform',array('id'=>$id)}">éditer</a>
        <a href="{jurl 'forms_destroy',array('id'=>$id)}">détruire</a>
    </tr>
{/foreach}
</table>
{else}
<p> pas de formulaire</o>
{/if}


<ul>
    <li><a href="{jurl 'forms_edit',array('id'=>1)}">créer une instance pour l'enregistrement 1</a></li>
    <li><a href="{jurl 'forms_edit',array('id'=>2)}">créer une instance pour l'enregistrement 2</a></li>
    <li><a href="{jurl 'forms_newform'}">créer une instance pour un nouvel enregistrement</a></li>
</ul>

