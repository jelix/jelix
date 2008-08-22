<h1>jForms test (multiple instances)</h1>
<p>Here is the list of instances of the "sample" form</p>

{if count($liste)}
<table border="1">
{foreach $liste as $id=>$form}
    <tr>
    <td>{$id}</td>
    <td>{$form->data['nom']}</td>
    <td>{$form->data['prenom']}</td>
    <td>
        <a href="{jurl 'forms:view',array('id'=>$id)}">see</a>
        <a href="{jurl 'forms:showform',array('id'=>$id)}">edit</a>
        <a href="{jurl 'forms:destroy',array('id'=>$id)}">destroy</a>
    </tr>
{/foreach}
</table>
{else}
<p>no form</o>
{/if}


<ul>
    <li><a href="{jurl 'forms:edit',array('id'=>1)}">create a instance for the record 1</a></li>
    <li><a href="{jurl 'forms:edit',array('id'=>2)}">create a instance for the record 2</a></li>
    <li><a href="{jurl 'forms:newform'}">create an instance for a new record</a></li>
</ul>

