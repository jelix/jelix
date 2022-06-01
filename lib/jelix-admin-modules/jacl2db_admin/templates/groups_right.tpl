{meta_html assets 'jacl2_admin'}

<h1>{@jacl2db_admin~acl2.groups.rights.title@}</h1>

<table class="records-list jacl2-list jacl2-list-sticky-heads" id="rights-list">
<thead>
    <tr>
        <th rowspan="2">{@jacl2db_admin~acl2.table.th.rights@}</th>
        <th colspan="{=$nbgrp}">{@jacl2db_admin~acl2.table.th.groups@}</th>
    </tr>
    <tr>
    {foreach $groups as $group}
        <th>{$group->name}</th>
    {/foreach}
    </tr>
</thead>
    <tfoot>
    <tr>
        <td>{@jacl2db_admin~acl2.groups.change.rights.link@}</td>
        {foreach $groups as $group}
            <th><a href="{jurl 'jacl2db_admin~groups:rights',array('group'=>$group->id_aclgrp)}">{@jelix~ui.buttons.update@}</a></th>
        {/foreach}
    </tr>
    </tfoot>
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
    {if $r == ''}
    <td><span class="right-no">{@jacl2db_admin~acl2.group.rights.no@}</span></td>
    {else}
    <td>
    {if $r =='y'}
        <img src="{$j_jelixwww}/design/icons/accept.png" alt="{@jacl2db_admin~acl2.group.rights.yes@}" title="{@jacl2db_admin~acl2.group.rights.yes@}" />
    {elseif $r=='n'}
        <img src="{$j_jelixwww}/design/icons/cancel.png" alt="{@jacl2db_admin~acl2.group.rights.forbidden@}"  title="{@jacl2db_admin~acl2.group.rights.forbidden@}"/>{/if}
    </td>
    {/if}
    {/foreach}
</tr>
{/foreach}
</tbody>
</table>

<div class="legend">
    <ul>
        <li><img src="{$j_jelixwww}/design/icons/accept.png" alt="yes" /> <span class="right-yes">{@jacl2db_admin~acl2.group.rights.yes@}</span> : {@jacl2db_admin~acl2.group.help.rights.yes@}</li>
        <li><span class="right-no">{@jacl2db_admin~acl2.group.rights.no@}</span>: {@jacl2db_admin~acl2.group.help.rights.no@}</li>
        <li><img src="{$j_jelixwww}/design/icons/cancel.png" alt="no" /> <span class="right-forbidden">{@jacl2db_admin~acl2.group.rights.forbidden@}</span>: {@jacl2db_admin~acl2.group.help.rights.forbidden@}</li>
    </ul>
</div>

