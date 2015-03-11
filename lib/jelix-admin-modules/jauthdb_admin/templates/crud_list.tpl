<h1>{@jauthdb_admin~crud.title.list@}</h1>

<table class="records-list">
<thead>
<tr>
    {foreach $propertiesList as $col}
    <th>
        <a href="{jurl '#~#', array('offset'=>$page, 'listorder'=>$col)}"
            class="view-order{if isset($listOrder[$col])} {if $listOrder[$col] == 'asc'} order-asc{elseif $listOrder[$col] == 'desc'} order-desc{/if}{/if}">
        {$controls[$col]->label|eschtml}</a></th>
    {/foreach}
    <th>&nbsp;</th>
</tr>
</thead>
<tbody>
{foreach $list as $record}
<tr class="{cycle array('odd','even')}">
    {foreach $propertiesList as $col}
    <td>{$record->$col|eschtml}</td>
    {/foreach}
    <td>
        {if $canview}
        <a href="{jurl 'jauthdb_admin~default:view',array('j_user_login'=>$record->$primarykey)}">{@jauthdb_admin~crud.link.view.record@}</a>
        {/if}
    </td>
</tr>
{/foreach}
</tbody>
</table>
{if $recordCount > $listPageSize}
<div class="record-pages-list">Pages : {pagelinks 'jauthdb_admin~default:index', array(),  $recordCount, $page, $listPageSize, 'offset' }</div>
{/if}
{if $cancreate}
<p><a href="{jurl 'jauthdb_admin~default:precreate'}" class="crud-link">{@jauthdb_admin~crud.link.create.record@}</a>.</p>
{/if}

