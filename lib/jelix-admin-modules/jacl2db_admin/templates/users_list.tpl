{meta_html assets 'jacl2_admin'}

<h1>{@jacl2db_admin~acl2.users.title@}</h1>

<form action="{formurl 'jacl2db_admin~rights:rights'}" method="get" class="form-inline">
    <div>
        <label>{@jacl2db_admin~acl2.user.rights.title@}</label>
        <input name="user"></input>
        <button type="submit">{@jacl2db_admin~acl2.show.button@}</button>
    </div>
</form>

<form action="{formurl 'jacl2db_admin~rights:index'}" method="get" name="filterForm">
<fieldset><legend>{@jacl2db_admin~acl2.filter.title@}</legend>
{formurlparam 'jacl2db_admin~rights:index'}
    <label for="type-list">{@jacl2db_admin~acl2.filter.type@}</label>
    <select name="typeName" id="type-list" onChange="document.getElementById('hideField').style.display = document.filterForm.typeName.selectedIndex ? 'none' : 'inline'">
        <option value="user" {if $type == "user"}selected="selected"{/if}>{@jacl2db_admin~acl2.type.user@}</option>
        <option value="group" {if $type == "group"}selected="selected"{/if}>{@jacl2db_admin~acl2.type.group@}</option>
        <option value="all" {if $type == "all"}selected="selected"{/if}>{@jacl2db_admin~acl2.type.all@}</option>
    </select><br/>
    <div id="hideField" style="display: {if $type == 'user'}inline;{else} none;{/if}">
    <label for="user-list-group">{@jacl2db_admin~acl2.filter.group@}</label>
    <select name="grpid" id="user-list-group">
    {foreach $groups as $group}
        <option value="{$group->id_aclgrp}" {if $group->id_aclgrp == $grpid}selected="selected"{/if}>{$group->name}</option>
    {/foreach}
     </select>
    - 
    </div>
    <label for="user-list-filter">{@jacl2db_admin~acl2.filter.word@}</label>
    <input type="text" name="filter" value="{$filter|eschtml}" id="user-list-filter" />
    <br/><input type="submit" value="{@jacl2db_admin~acl2.show.button@}" />
</fieldset>
</form>

{if $resultsCount == 0}
<p>{@jacl2db_admin~acl2.no.result.message@}</p>
{else}
<table class="records-list">
<thead>
    <tr>
        <th>{@jacl2db_admin~acl2.col.groups.name@}</th>
        <th>{@jacl2db_admin~acl2.col.type@}</th>
        <th>{@jacl2db_admin~acl2.col.groups@}</th>
        <th></th>
    </tr>
</thead>
<tbody>
{assign $line = true}
{foreach $results as $result}
    <tr class="{if $line}odd{else}even{/if}">
        
        <td><a href="{jurl 'jacl2db_admin~users:rights', array('user'=>$user->login)}">{@jacl2db_admin~acl2.rights.link@}</a></td>
        <td>{$result->login}</td>
        <td>{foreach $result->groups as $key => $group} 
            {if $key == $last}
                {$group->name}
            {else}
                {$group->name.', '}
            {/if}
        {/foreach}</td>
        <td>{$result->type}</td>
        <td><a href="{jurl 'jacl2db_admin~rights:rights', array('user'=>$result->login)}">{@jacl2db_admin~acl2.rights.link@}</a></td>
    </tr>
{assign $line = !$line}
{/foreach}
</tbody>
</table>
{/if}

{if $resultsCount > $listPageSize}
<div class="record-pages-list">{@jacl2db_admin~acl2.pages.links.label@} {pagelinks 'jacl2db_admin~rights:index', array('grpid'=>$grpid, 'filter'=>$filter),  $usersCount, $offset, $listPageSize, 'idx' }</div>
{/if}



