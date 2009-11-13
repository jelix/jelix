{meta_html css  $j_jelixwww.'design/jacl2.css'}

<h1>{@jacl2db_admin~acl2.groups.rights.title@}</h1>

<form action="{formurl 'jacl2db_admin~groups:saverights'}" method="post">
<fieldset><legend>{@jacl2db_admin~acl2.rights.title@}</legend>
<div>{formurlparam 'jacl2db_admin~groups:saverights'}</div>
<table class="records-list jacl2-list">
<thead>
    <tr>
        <th rowspan="2"></th>
        <th colspan="0">{@jacl2db_admin~acl2.table.th.groups@}</th>
    </tr>
    <tr>
        
    {foreach $groups as $group}
        <th colspan="2">{$group->name}</th>
    {/foreach}
    </tr>
    <tr>
        <th>{@jacl2db_admin~acl2.table.th.rights@}</th>
    {foreach $groups as $group}
        <th>global</th>
        <th>on res</th>
    {/foreach}
    </tr>
</thead>
<tfoot>
    <tr>
        <td></td>
    {foreach $groups as $group}
        <th></th>
        <th><a href="{jurl 'jacl2db_admin~groups:rightres',array('group'=>$group->id_aclgrp)}">see</a></th>
    {/foreach}
    </tr>
</tfoot>
<tbody>
{foreach $rights as $subject=>$right}
<tr class="{cycle array('odd','even')}">
    <th>{$subjects_localized[$subject]|escxml}</th>
    {foreach $right as $group=>$r}
    <td><input type="checkbox" name="rights[{$group}][{$subject}]" {if $r}checked="checked"{/if} /></td>
    <td>{if isset($rightsWithResources[$subject][$group]) && $rightsWithResources[$subject][$group]}yes{/if}</td>
    {/foreach}
</tr>
{/foreach}
</tbody>
</table>
<div><input type="submit" value="{@jacl2db_admin~acl2.save.button@}" /></div>
</fieldset>
</form>

