{meta_html css  $j_jelixwww.'design/jacl2.css'}

<h1>{@jacl2_admin~acl2.groups.title@}</h1>

<form action="{formurl 'jacl2_admin~groups:saverights'}" method="post">
<fieldset><legend>{@jacl2_admin~acl2.rights.title@}</legend>
<div>{formurlparam 'jacl2_admin~groups:saverights'}</div>
<table class="rights">
<thead>
    <tr>
        <th></th>
    {foreach $groups as $group}
        <th>{$group->name}</th>
    {/foreach}
    </tr>
</thead>
<tbody>
{assign $line = true}
{foreach $rights as $subject=>$right}
<tr class="{if $line}odd{else}even{/if}">
    <th>{$subject}</th>
    {foreach $right as $group=>$r}
    <td><input type="checkbox" name="rights[{$group}][{$subject}]" {if $r}checked="checked"{/if} /></td>
    {/foreach}
</tr>
{assign $line = !$line}
{/foreach}
</tbody>
</table>
<div><input type="submit" value="{@jacl2_admin~acl2.save.button@}" /></div>
</fieldset>
</form>

{ifacl2 'acl.group.create'}
<form action="{formurl 'jacl2_admin~groups:newgroup'}" method="post">
<fieldset><legend>{@jacl2_admin~acl2.create.group@}</legend>
{formurlparam 'jacl2_admin~groups:newgroup'}
<label for="newgroup">{@jacl2_admin~acl2.group.name.label@}</label> <input id="newgroup" name="newgroup" />
<input type="submit" value="{@jacl2_admin~acl2.save.button@}" />
</fieldset>
</form>
{/ifacl2}

{ifacl2 'acl.group.modify'}
<form action="{formurl 'jacl2_admin~groups:changename'}" method="post">
<fieldset><legend>{@jacl2_admin~acl2.change.name.title@}</legend>
{formurlparam 'jacl2_admin~groups:changename'}
    <select name="group_id">
    {foreach $groups as $group}
        {if  $group->id_aclgrp != 0}<option value="{$group->id_aclgrp}">{$group->name}</option>{/if}
    {/foreach}
     </select>

    <label for="newname">{@jacl2_admin~acl2.new.name.label@}</label> <input id="newname" name="newname" />
    <input type="submit" value="{@jacl2_admin~acl2.rename.button@}" />
</fieldset>
</form>
{/ifacl2}

{ifacl2 'acl.group.delete'}
<form action="{formurl 'jacl2_admin~groups:delgroup'}" method="post">
<fieldset><legend>{@jacl2_admin~acl2.delete.group@}</legend>
{formurlparam 'jacl2_admin~groups:delgroup'}
    <select name="group_id">
    {foreach $groups as $group}
        {if  $group->id_aclgrp != 0}<option value="{$group->id_aclgrp}">{$group->name}</option>{/if}
    {/foreach}
     </select>

    <input type="submit" value="{@jacl2_admin~acl2.delete.button@}" />
</fieldset>
</form>
{/ifacl2}

