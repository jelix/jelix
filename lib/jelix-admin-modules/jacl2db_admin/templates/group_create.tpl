{meta_html assets 'jacl2_admin'}

<h1>{@acl2.create.group@}</h1>
<form action="{formurl 'jacl2db_admin~groups:newgroup'}" metdod="post">
<table>
<tr>
    <td><label>{@acl2.group.name.label@}</label></td><td><input name="name" required/></td>
</tr><tr>
<td><label>{@acl2.group.id.label@}</label></td><td><input name="id" required/></td>
</tr><tr>
<td><label>{@acl2.group.copy.label@}</label></td><td><select name="rights-copy">
<option value=""></option>
{foreach $groups as $group}
    <option value="{$group->id_aclgrp}">{$group->name}</option>
{/foreach}
</select></td>
</tr><tr>
<td colspan="2"><input type="submit" value="{@acl2.create.group@}"/></td>
</tr>
</table>
</form>