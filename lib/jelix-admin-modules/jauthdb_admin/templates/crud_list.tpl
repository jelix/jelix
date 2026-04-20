{meta_html assets 'jauthdb_admin'}

<h1>{@jauthdb_admin~crud.title.list@}</h1>

{if $showfilter}
<form action="{formurl 'jauthdb_admin~default:index'}" method="get">
    <div>
        <!--<label for="user-list-filter">{@jauthdb_admin~crud.search.form.keyword.label@}</label>-->
        <input type="text" name="filter" value="{$filter|eschtml}" id="user-list-filter" />
        <button type="submit">{@jauthdb_admin~crud.search.button.label@}</button>
    </div>
</form>
{/if}

{if $canview}
<form action="{formurl 'jauthdb_admin~default:view'}" method="get">
    <div>
        <label for="search-login">{@jauthdb_admin~crud.title.view@}</label>
        <input id="search-login" name="j_user_login" data-link="{jurl 'jauthdb_admin~default:autocomplete'}">
        <button type="submit">{@jauthdb_admin~crud.link.view.record@}</button>
    </div>
</form>
{/if}


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
        <a href="{jurl 'jauthdb_admin~default:view',array('j_user_login'=>$record->login)}">{@jauthdb_admin~crud.link.view.record@}</a>
        {/if}
    </td>
</tr>
{/foreach}
</tbody>
</table>
{if $allowLoginWithEmail}
{@jauthdb_admin~crud.list.allow.login.with.email@}
{/if}
{if $recordCount > $listPageSize}
<div class="record-pages-list">Pages : {pagelinks 'jauthdb_admin~default:index', array('filter'=>$filter),  $recordCount, $page, $listPageSize, 'offset' }</div>
{/if}
{if $cancreate}
<p><a href="{jurl 'jauthdb_admin~default:precreate'}" class="crud-link">{@jauthdb_admin~crud.link.create.record@}</a>.</p>
{/if}

