{meta_html assets 'jacl2_admin'}

<h1>{@jacl2db_admin~acl2.groups.rights.title@}</h1>

<form id="rights-edit" action="{formurl 'jacl2db_admin~groups:saverights'}" method="post">
<fieldset><legend>{@jacl2db_admin~acl2.rights.title@}</legend>
<div>{formurlparam 'jacl2db_admin~groups:saverights'}</div>
<table class="records-list jacl2-list">
<thead>
    <tr>
        <th>{@jacl2db_admin~acl2.table.th.rights@}</th>
        <th colspan="{$nbgrp}">{@jacl2db_admin~acl2.table.th.groups@}</th>
    </tr>
    <tr>
    <th></th>
    {foreach $groups as $group}
        <th>{$group->name}</th>
    {/foreach}
    </tr>
</thead>
<tbody>
{assign $currentsbjgroup = '---'}
{foreach $rights as $subject=>$right}
{if $subjects[$subject]['grp'] && $currentsbjgroup != $subjects[$subject]['grp']}
<tr class="{cycle array('odd','even')}">
    <th colspan="{=$nbgrp*2+1}"><h3>{$rightsGroupsLabels[$subjects[$subject]['grp']]}</h3></th>
</tr>{assign $currentsbjgroup = $subjects[$subject]['grp']}
{/if}
<tr class="{cycle array('odd','even')}">
    <th title="{$subject}">{$subjects[$subject]['label']|eschtml}</th>
    {foreach $right as $group=>$r}
    {if $group == $groupId}
    <td><select name="rights[{$group}][{$subject}]" id="{$subject|eschtml}">
        <option value=""  {if $r == ''}selected="selected"{/if}>-</option>
        <option value="y" {if $r == 'y'}selected="selected"{/if}>{@acl2.group.rights.value.yes@}</option>
        <option value="n" {if $r == 'n'}selected="selected"{/if}>{@acl2.group.rights.value.no@}</option>
        </select>
    </td>
    {elseif $r == ''}
    <td>-</td>
    {else}
    <td>
    {if $r =='y'}
        <input name="rights[{$group}][{$subject}]" value="y" style="display: none;"/>
        <img src="{$j_jelixwww}/design/icons/accept.png" alt="yes" />
    {elseif $r=='n'}
        <input name="rights[{$group}][{$subject}]" value="n" style="display: none;"/>
        <img src="{$j_jelixwww}/design/icons/cancel.png" alt="no" />{/if}
    </td>
    {/if}
    {/foreach}
</tr>
{/foreach}
</tbody>
</table>
<div class="legend">
    <ul>
        <li>{@jacl2db_admin~acl2.group.help.rights.inherit@}</li>
        <li><img src="{$j_jelixwww}/design/icons/accept.png" alt="yes" />{@jacl2db_admin~acl2.group.help.rights.yes@}</li>
        <li><img src="{$j_jelixwww}/design/icons/cancel.png" alt="no" />{@jacl2db_admin~acl2.group.help.rights.no@}</li>
    </ul>
</div>
<input name="group" value="{$groupId}" style="display:none;"/>
<div><input type="submit" value="{@jacl2db_admin~acl2.save.button@}" /></div>
</fieldset>
</form>

