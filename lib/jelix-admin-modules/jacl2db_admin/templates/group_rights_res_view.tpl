{meta_html assets 'jacl2_admin'}

<h1>{@jacl2db_admin~acl2.group.rightres.title@} {$groupname}</h1>

<table class="records-list jacl2-list">
<thead>
    <tr>
        <th>{@jacl2db_admin~acl2.col.subjects@}</th>
        <th>{@jacl2db_admin~acl2.col.resources@}</th>
    </tr>
</thead>
<tbody>
{foreach $rightsWithResources as $subject=>$resources}
<tr class="{cycle array('odd','even')}">
    <th>{$rightsLabels[$subject]|eschtml}</th>
    <td>{assign $firstr=true}
        {foreach $resources as $r}{if !$firstr}, {else}{assign $firstr=false}{/if}
        <span class="aclres{$r->canceled}">{$r->id_aclres|eschtml}</span>{/foreach}</td>
</tr>
{/foreach}
</tbody>
</table>

<p><a href="{jurl 'jacl2db_admin~groups:rights', array('group'=>$groupid)}">{@jacl2db_admin~acl2.link.return.to.rights@}</a>.</p>

