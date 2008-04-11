{meta_html css  $j_jelixwww.'design/jacl2.css'}

<h1>{@jacl2_admin~acl2.user.rights.title@} {$user}</h1>


<form action="{formurl 'jacl2_admin~users:saverights',array('user'=>$user)}" method="post">
<fieldset><legend>{@jacl2_admin~acl2.rights.title@}</legend>

<div>{formurlparam 'jacl2_admin~users:saverights',array('user'=>$user)}</div>
<table class="rights">
<thead>
    <tr>
        <th rowspan="2"></th>
        <th rowspan="2">{@jacl2_admin~acl2.col.personnal.rights.1@}<br />{@jacl2_admin~acl2.col.personnal.rights.2@}</th>
        {if $nbgrp}
        <th colspan="{$nbgrp}">{@jacl2_admin~acl2.col.groups@}</th>
        {/if}
        <th class="colblank" rowspan="2"></th>
        <th rowspan="2">{@jacl2_admin~acl2.col.resulting.1@}<br />{@jacl2_admin~acl2.col.resulting.2@}</th>
    </tr>
    <tr>
    {foreach $groups as $group}
        {if isset($groupsuser[$group->id_aclgrp])}
        <th>{$group->name} <a class="removegroup" href="{jurl 'jacl2_admin~users:removegroup',array('user'=>$user,'grpid'=>$group->id_aclgrp)}" title="{@jacl2_admin~acl2.remove.group.tooltip@}">-</a></th>
        {else}
        <th class="notingroup">{$group->name} <a class="addgroup" href="{jurl 'jacl2_admin~users:addgroup',array('user'=>$user,'grpid'=>$group->id_aclgrp)}" title="{@jacl2_admin~acl2.add.group.tooltip@}">+</a></th>
        {/if}
    {/foreach}
    </tr>
</thead>
<tfoot>
    <tr>
        <td></td>
        <td><input type="submit" value="{@jacl2_admin~acl2.save.button@}" /></td>
        {if $nbgrp}
        <td colspan="{$nbgrp}"></td>
        {/if}
        <td colspan="2"></td>
    </tr>
</tfoot>
<tbody>

{assign $line = true}
{foreach $rights as $subject=>$right}
<tr class="{if $line}odd{else}even{/if}">
    <th>{$subject}</th>
    {assign $hasr=false}
    {foreach $right as $group=>$r}
    {if $group == $hisgroup->id_aclgrp}
    <td><input type="checkbox" name="rights[{$subject}]" {if $r}{assign $hasr=true}checked="checked"{/if} /></td>
    {else}
    <td {if !isset($groupsuser[$group])}class="notingroup"{elseif $r}{assign $hasr=true}{/if}>{if $r}X{/if}</td>
    {/if}
    {/foreach}
    <td class="colblank"></td>
    <td>{if $hasr}X{/if}</td>
</tr>
{assign $line = !$line}
{/foreach}
</tbody>
</table>

</fieldset>
</form>

