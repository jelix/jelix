<h1>{@jelix~crud.title.list@}</h1>

<table class="crud-record-list">
{foreach $list as $record}
<tr>
    {foreach $properties as $propname}
    <td>{$record->$propname|eschtml}</td>
    {/foreach}
    <td>
        <a href="{jurl $viewAction,array('id'=>$record->$primarykey)}">{@jelix~crud.link.view.record@}</a>
    </td>
</tr>
{/foreach}
</table>
<p class="crud-pages">Pages : {pagelinks $listAction, array(),  $recordCount, $page, $listPageSize, $offsetParameterName }</p>
<p><a href="{jurl $createAction}" class="crud-link">{@jelix~crud.link.create.record@}</a>.</p>

