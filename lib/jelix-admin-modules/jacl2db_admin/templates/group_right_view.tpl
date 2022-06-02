{meta_html assets 'jacl2_admin'}

<h1>{@jacl2db_admin~acl2.group.rights.label@} {$group->name}</h1>

<table class="records-list jacl2-list" id="rights-list">
<thead>
    <tr>
        <th >{@jacl2db_admin~acl2.table.th.rights@}</th>
        <th>{$group->name}</th>
    </tr>
</thead>
    <tfoot>
    <tr>
        <td>{@jacl2db_admin~acl2.group.rightres.title@}</td>
        <th><a href="{jurl 'jacl2db_admin~groups:rightres',array('group'=>$group->id_aclgrp)}">{@jacl2db_admin~acl2.special.rights@}</a></th>
    </tr>
    </tfoot>
<tbody>
{assign $currentsbjgroup = '---'}
{foreach $rights as $subject=>$right}
{if $rightsProperties[$subject]['grp'] && $currentsbjgroup != $rightsProperties[$subject]['grp']}
<tr class="{cycle array('odd','even')}">
    <th colspan="2"><h3>{$rightsGroupsLabels[$rightsProperties[$subject]['grp']]}</h3></th>
</tr>{assign $currentsbjgroup = $rightsProperties[$subject]['grp']}
{/if}
<tr class="{cycle array('odd','even')}">
    <th title="{$subject}">{$rightsProperties[$subject]['label']|eschtml}</th>
    <td>
        {if $rights[$subject] == ''}<span class="right-no">{@jacl2db_admin~acl2.group.rights.no@}</span>{/if}
        {if $rights[$subject] == 'y'}<img src="{$j_jelixwww}/design/icons/accept.png" alt="{@jacl2db_admin~acl2.group.rights.yes@}" />{/if}
        {if $rights[$subject] == 'n'}<img src="{$j_jelixwww}/design/icons/cancel.png" alt="{@jacl2db_admin~acl2.group.rights.forbidden@}" />{/if}
    </td>
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
<div>
    <br/>
    <a href="{jurl 'jacl2db_admin~groups:allrights'}">{@jacl2db_admin~acl2.groups.back.to.rights.list@}</a>
    <br/>
    <a href="{jurl 'jacl2db_admin~groups:index'}">{@jacl2db_admin~acl2.groups.back.to.list@}</a>
</div>

