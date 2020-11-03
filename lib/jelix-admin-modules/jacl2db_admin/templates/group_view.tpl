{meta_html assets 'jacl2_admin'}
<h1>{@jacl2db_admin~acl2.groups.view.title@.' '.$group->name}</h1>

<table class="jforms-table">
<tbody>
    <tr>
        <th> {@jacl2db_admin~acl2.col.groups.name@} </th>
        <td> {$group->name} <td>
    </tr>
    <tr>
        <th> {@jacl2db_admin~acl2.table.th.rights@} </th>
        <td> <ul>
        {foreach $rights as $right}
            <li> {$right} </li>
        {/foreach}
        </ul>
        <a href="{jurl 'jacl2db_admin~groups:rights', array('group' => $group->id_aclgrp)}">{@jacl2db_admin~acl2.groups.change.rights.link@}</a> </td>
    </tr>
    <tr>
        <th> {@jacl2db_admin~acl2.col.users@} </th>
        <td> <ul>
        {foreach $users as $user}
            <li> {$user} </li>
        {/foreach}
        </ul> </td>
    </tr>
</tbody>
</table>

{ifacl2 'acl.group.modify'}

<form id="rename-form" action="{formurl 'jacl2db_admin~groups:changename'}" method="post">
<fieldset><legend>{@jacl2db_admin~acl2.change.name.title@}</legend>
{formurlparam 'jacl2db_admin~groups:changename'}
    <label for="newname">{@jacl2db_admin~acl2.new.name.label@}</label> <input id="newname" name="newname" />
    <input name="group_id" value="{$group->id_aclgrp}" style="display:none;"/>
    <input type="submit" value="{@jacl2db_admin~acl2.rename.button@}" />
</fieldset>
</form>
{/ifacl2}

{ifacl2 'acl.group.delete'}
<form action="{formurl 'jacl2db_admin~groups:delgroup'}" method="post" onsubmit="return confirm('{@jacl2db_admin~acl2.delete.button.confirm.label@}');">
{formurlparam 'jacl2db_admin~groups:delgroup'}
<div>
    <input type="text" name="group" value="{$group->id_aclgrp}" style="display: none;"/>
    <input type="submit" value="{@jacl2db_admin~acl2.delete.group@}"/>
</div>
</form>
{/ifacl2}