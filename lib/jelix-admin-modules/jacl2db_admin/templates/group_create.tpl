{meta_html assets 'jacl2_admin'}

<h1>{@acl2.create.group@}</h1>
<form action="{formurl 'jacl2db_admin~groups:newgroup'}" method="post">
<label>{@acl2.group.name.label@}</label> <input name="name" required/><br/>
<label>{@acl2.group.id.label@}</label> <input name="id" required/><br/>
<label>{@acl2.group.copy.label@}</label> <select name="rights-copy">
<option value=""></option>
{foreach $groups as $group}
    <option value="{$group->id_aclgrp}">{$group->name}</option>
{/foreach}
</select>
<input type="submit" value="{@acl2.create.group@}"/>
</form>