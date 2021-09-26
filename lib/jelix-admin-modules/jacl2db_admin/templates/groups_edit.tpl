{meta_html assets 'jacl2_admin'}

<h1>{@jacl2db_admin~acl2.groups.title@}</h1>

<form action="{formurl 'jacl2db_admin~groups:index'}" method="get" class="form-inline">
    <div>
        <label>{@jacl2db_admin~acl2.groups.search.label@}</label>
        <input name="group" id="search-bar" data-link="{jurl 'jacl2db_admin~groups:autocomplete'}"/>
        <button type="submit">{@jacl2db_admin~acl2.search.button@}</button>
    </div>
</form>
{if $searchMode}<h2>{@jacl2db_admin~acl2.groups.search.results@}</h2>
    {if $groups === null}
        <p>{@jacl2db_admin~acl2.groups.search.no.results@}</p>
    {/if}
{/if}

{if $groups !== null}
<table class="records-list">
<thead>
    <tr>
        <th>{@jacl2db_admin~acl2.col.groups@}</th>
        <th></th>
    </tr>
</thead>
<tbody>
{foreach $groups as $group}
    <tr class="{cycle array('odd', 'even')}">
        {if $group->name === 'anonymous'}
        <td> {@jacl2db_admin~acl2.anonymous.group.name@} </td>
        {else}
        <td>{$group->name}</td>
        {/if}
        <td><a href="{jurl 'jacl2db_admin~groups:view', array('group'=>$group->id_aclgrp)}">{@jacl2db_admin~acl2.groups.view.link@}</a></td>
    </tr>
{/foreach}
</tbody>
</table>
{/if}

{if $searchMode}
    <p><a href="{jurl 'jacl2db_admin~groups:index'}">{@jacl2db_admin~acl2.groups.back.to.list@}</a></p>
{else}
<form action="{formurl 'jacl2db_admin~groups:setdefault'}" method="post">
<fieldset><legend>{@jacl2db_admin~acl2.groups.new.users.title@}</legend>
{formurlparam 'jacl2db_admin~groups:setdefault'}
    {foreach $groups as $group}
        {if $group->id_aclgrp !== '__anonymous'}
        <label><input type="checkbox" name="groups[]" value="{$group->id_aclgrp}" {if $group->grouptype > 0}checked="checked"{/if}/> {$group->name}</label>
        {/if}
    {/foreach}
    <br />
    <input type="submit" value="{@jacl2db_admin~acl2.setdefault.button@}" />
</fieldset>
</form>
{/if}
<p>
{ifacl2 'acl.group.create'}
<a href="{jurl 'jacl2db_admin~groups:create'}">{@jacl2db_admin~acl2.create.group@}</a>
{/ifacl2}
</p>