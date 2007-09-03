<h1>List</h1>

<table>
{foreach $list as $record}
<tr>
    {foreach $properties as $propname}
    <td>{$record->$propname|eschtml}</td>
    {/foreach}
    <td>
        <a href="{jurl $viewAction,array('id'=>$record->$primarykey)}">View</a>
    </td>
</tr>
{/foreach}
</table>

<p><a href="{jurl $createAction}">Create a new record</a>.</p>