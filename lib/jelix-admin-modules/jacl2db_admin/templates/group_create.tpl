{meta_html assets 'jacl2_admin'}

<h1>{@acl2.create.group@}</h1>
<form action="{formurl 'jacl2db_admin~groups:newgroup'}" method="post" id="create-group">
<table>
    <tr>
        <td><label for="grp_name">{@acl2.group.name.label@}</label></td>
        <td><input id="grp_name" name="name" required value="{$groupname}"/></td>
    </tr>
    <tr>
        <td><label for="grp_id">{@acl2.group.id.label@}</label></td>
        <td><input id="grp_id" name="id" required/>
        ({@acl2.group.id.help@})
        </td>
    </tr>
    <tr>
        <td><label for="rights-copy">{@acl2.group.copy.label@}</label></td>
        <td><select id="rights-copy" name="rights-copy">
            <option value=""></option>
            {foreach $groups as $group}
                <option value="{$group->id_aclgrp}">{$group->name}</option>
            {/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="2"><input type="submit" value="{@acl2.create.group@}"/></td>
    </tr>
</table>
</form>