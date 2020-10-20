<h1>{@jacl2db_admin~acl2.groups.view.title@.' '.$group->name}</h1>

<table>
<tbody>
    <tr>
        <td> {@jacl2db_admin~acl2.col.groups.name@} </td>
        <td> {$group->name} <td>
    </tr>
    <tr>
        <td> {@jacl2db_admin~acl2.table.th.rights@} </td>
        <td> <ul>
        {foreach $rights as $right}
            <li> {$right} </li>
        {/foreach}
        </ul> </td>
    </tr>
    <tr>
        <td> {@jacl2db_admin~acl2.col.users@} </td>
        <td> <ul>
        {foreach $users as $user}
            <li> {$user} </li>
        {/foreach}
        </ul> </td>
    </tr>
</tbody>
</table>


{ifacl2 'acl.group.delete'}
<a href="{jurl 'jacl2db_admin~groups:delgroup', array('group' => $group->id_aclgrp)}">{@jacl2db_admin~acl2.delete.group@}</a>
{/ifacl2}
<br/>

{ifacl2 'acl.group.modify'}

<a onclick="document.getElementById('rename-form').style.display = 'block';">{@jacl2db_admin~acl2.change.name.title@}</a>

<form id="rename-form" style="display:none;" action="{formurl 'jacl2db_admin~groups:changename'}" method="post">
    <label for="newname">{@jacl2db_admin~acl2.new.name.label@}</label> <input id="newname" name="newname" />
    <input name="group_id" value="{$group->id_aclgrp}" style="display:none;"/>
    <input type="submit" value="{@jacl2db_admin~acl2.rename.button@}" />
</fieldset>
</form>
<br/>
<a href="{jurl 'jacl2db_admin~groups:rights', array('group' => $group->id_aclgrp)}">{@jacl2db_admin~acl2.groups.change.rights.link@}</a>

{/ifacl2}