{meta_html css  $j_jelixwww.'design/jacl2.css'}

<h1>{@jacl2db_admin~acl2.groups.rights.title@}</h1>

<form action="{formurl 'jacl2db_admin~groups:saverights'}" method="post">
<fieldset><legend>{@jacl2db_admin~acl2.rights.title@}</legend>
<div>{formurlparam 'jacl2db_admin~groups:saverights'}</div>
<table class="records-list jacl2-list">
<thead>
    <tr>
        <th></th>
        <th colspan="0">{@jacl2db_admin~acl2.table.th.groups@}</th>
    </tr>
    <tr>
        <th>{@jacl2db_admin~acl2.table.th.rights@}</th>
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
<div><input type="submit" value="{@jacl2db_admin~acl2.save.button@}" /></div>
</fieldset>
</form>

