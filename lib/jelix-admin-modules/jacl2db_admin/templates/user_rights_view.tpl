{meta_html css  $j_jelixwww.'design/jacl2.css'}

<h1>{@jacl2db_admin~acl2.user.rights.title@} {$user}</h1>

<table class="records-list jacl2-list">
<thead>
    <tr>
        <th rowspan="2"></th>
        <th class="colreduced" rowspan="2">{@jacl2db_admin~acl2.col.personnal.rights@}</th>
        <th class="colreduced" rowspan="2">{@jacl2db_admin~acl2.col.personnal.rights.res@}</th>
        {if $nbgrp}
        <th colspan="{$nbgrp}">{@jacl2db_admin~acl2.col.groups@}</th>
        {/if}
        <th class="colblank" rowspan="2"></th>
        <th class="colreduced" rowspan="2">{@jacl2db_admin~acl2.col.resulting@}</th>
    </tr>
    <tr>
    {foreach $groups as $group}
        {if isset($groupsuser[$group->id_aclgrp])}
        <th>{$group->name}</th>
        {else}
        <th class="notingroup">{$group->name}</th>
        {/if}
    {/foreach}
    </tr>
</thead>
<tbody>
{foreach $rights as $subject=>$right}
<tr class="{cycle array('odd','even')}">
    <th>{$subjects_localized[$subject]|eschtml}</th>
    {assign $hasr=false}
    {foreach $right as $group=>$r}
    {if $group == $hisgroup->id_aclgrp}
    <td>{if $r}{assign $hasr=true}X{/if}</td>
    <td>{if $rightsWithResources[$subject]}yes{/if}</td>
    {else}
    <td {if !isset($groupsuser[$group])}class="notingroup"{elseif $r}{assign $hasr=true}{/if}>{if $r}X{/if}</td>
    {/if}
    {/foreach}
    <td class="colblank"></td>
    <td>{if $hasr}X{/if}</td>
</tr>
{/foreach}
</tbody>
</table>
{if $hasRightsOnResources}
<p>{@jacl2db_admin~acl2.has.rights.on.resources@}. <a href="{jurl 'jacl2db_admin~users:rightres',array('user'=>$user)}">{@jacl2db_admin~acl2.see.rights.on.resources@}</a>.</p>
{/if}
