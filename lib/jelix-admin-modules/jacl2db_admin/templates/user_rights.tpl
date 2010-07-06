{meta_html css  $j_jelixwww.'design/jacl2.css'}

<h1>{@jacl2db_admin~acl2.user.rights.title@} {$user}</h1>


<form action="{formurl 'jacl2db_admin~users:saverights',array('user'=>$user)}" method="post">
<fieldset><legend>{@jacl2db_admin~acl2.rights.title@}</legend>

<div>{formurlparam 'jacl2db_admin~users:saverights',array('user'=>$user)}</div>
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
        <th>{$group->name} <a class="removegroup" href="{jurl 'jacl2db_admin~users:removegroup',array('user'=>$user,'grpid'=>$group->id_aclgrp)}" title="{@jacl2db_admin~acl2.remove.group.tooltip@}">-</a></th>
        {else}
        <th class="notingroup">{$group->name} <a class="addgroup" href="{jurl 'jacl2db_admin~users:addgroup',array('user'=>$user,'grpid'=>$group->id_aclgrp)}" title="{@jacl2db_admin~acl2.add.group.tooltip@}">+</a></th>
        {/if}
    {/foreach}
    </tr>
</thead>
<tfoot>
    <tr>
        <td></td>
        <td><input type="submit" value="{@jacl2db_admin~acl2.save.button@}" /></td>
        <td></td>
        {if $nbgrp}
        <td colspan="{$nbgrp}"></td>
        {/if}
        <td colspan="2"></td>
    </tr>
</tfoot>
<tbody>
{foreach $rights as $subject=>$right}
<tr class="{cycle array('odd','even')}">
    <th><label for="{$subject|eschtml}">{$subjects_localized[$subject]|eschtml}</label></th>
    {assign $hasr=false}
    {foreach $right as $group=>$r}
    {if $hisgroup && $group == $hisgroup->id_aclgrp}
    <td><input type="checkbox" name="rights[{$subject}]" id="{$subject|eschtml}" {if $r}{assign $hasr=true} checked="checked"{/if} />
    <input type="hidden" name="currentrights[{$subject}]" value="{$r}"/></td>
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
</fieldset>
</form>

