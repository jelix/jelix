{meta_html assets 'jacl2_admin'}
<h1>{@jacl2db_admin~acl2.group.view.title@.' '.$group->name}</h1>

<table class="jforms-table">
<tbody>
    <tr>
        <th> {@jacl2db_admin~acl2.col.groups.name@} </th>
        <td> {$group->name} </td>
    </tr>
    <tr>
        <th> {@jacl2db_admin~acl2.table.th.rights@} </th>
        <td>
            {if count($rights)}
            <ul>
        {foreach $rights as $right}
            <li> {$right} </li>
        {/foreach}
        </ul>{else}
                <p>{@jacl2db_admin~acl2.no.rights@}.</p>
        {/if}
        <a href="{jurl 'jacl2db_admin~groups:rights', array('group' => $group->id_aclgrp)}">{@jacl2db_admin~acl2.groups.change.rights.link@}</a>
        </td>
    </tr>
    {if $users !== null}
    <tr>
        <th> {@jacl2db_admin~acl2.col.users@} </th>
        <td> <ul>
        {foreach $users as $user}
            <li> {$user} </li>
        {/foreach}
        </ul> </td>
    </tr>
    {/if}
</tbody>
</table>

{ifacl2 'acl.group.modify'}
{if $group->id_aclgrp !== '__anonymous'}
<form id="rename-form" action="{formurl 'jacl2db_admin~groups:changename'}" method="post">
<fieldset><legend>{@jacl2db_admin~acl2.change.name.title@}</legend>
{formurlparam 'jacl2db_admin~groups:changename'}
    <label for="newname">{@jacl2db_admin~acl2.new.name.label@}</label> <input id="newname" name="newname" />
    <input type="hidden" name="group_id" value="{$group->id_aclgrp}"/>
    <input type="submit" value="{@jacl2db_admin~acl2.rename.button@}" />
</fieldset>
</form>
<br/>
{/if}
{/ifacl2}

{ifacl2 'acl.group.delete'}
{if $group->id_aclgrp !== '__anonymous'}
<form action="{formurl 'jacl2db_admin~groups:delgroup'}" method="post" onsubmit="return confirm('{@jacl2db_admin~acl2.delete.button.confirm.label@}');">
{formurlparam 'jacl2db_admin~groups:delgroup'}
<div>
    <input type="hidden" name="group" value="{$group->id_aclgrp}"/>
    <input type="submit" value="{@jacl2db_admin~acl2.delete.group@}"/>
</div>
</form>
{/if}
{/ifacl2}