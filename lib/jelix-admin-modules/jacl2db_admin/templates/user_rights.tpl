{meta_html assets 'jacl2_admin'}

<h1>{@jacl2db_admin~acl2.user.rights.title@} {$user}</h1>


<form id="rights-edit" action="{formurl 'jacl2db_admin~users:saverights',array('user'=>$user)}" method="post">
<fieldset><legend>{@jacl2db_admin~acl2.rights.title@}</legend>

<div>{formurlparam 'jacl2db_admin~users:saverights',array('user'=>$user)}</div>
<table class="records-list jacl2-list-user">
<thead>
    <tr>
        <th rowspan="2"></th>
        <th class="colreduced" rowspan="2">{@jacl2db_admin~acl2.col.personnal.rights@}</th>
        <th class="colreduced" rowspan="2">{@jacl2db_admin~acl2.col.personnal.rights.res@}</th>
        {if $nbgrp}
        <th id="group-head" colspan="{$nbgrp}">{@jacl2db_admin~acl2.col.groups@}</th>
        {/if}
        <th class="colhide" rowspan="2">
        <div id="div-group-selector">
        <select id="groupSelector">
        {foreach $groups as $group}
            {if !isset($groupsuser[$group->id_aclgrp])}
                <option value="{$group->id_aclgrp}">{$group->name}</option>
            {/if}
        {/foreach}
        </select>
        <button type="button" onclick="showColumn();">{@jacl2db_admin~acl2.button.display@}</button></th>
        </div>
        <th class="colreduced" rowspan="2">{@jacl2db_admin~acl2.col.resulting@}</th>
    </tr>
    <tr>
    {foreach $groups as $group}
        {if isset($groupsuser[$group->id_aclgrp])}
        <th>{$group->name} <a class="removegroup" href="{jurl 'jacl2db_admin~users:removegroup',array('user'=>$user,'grpid'=>$group->id_aclgrp)}" title="{@jacl2db_admin~acl2.remove.group.tooltip@}">-</a></th>
        {else}
        <th class="notingroup {$group->id_aclgrp}" style="display:none;">{$group->name} <a class="addgroup" href="{jurl 'jacl2db_admin~users:addgroup',array('user'=>$user,'grpid'=>$group->id_aclgrp)}" title="{@jacl2db_admin~acl2.add.group.tooltip@}">+</a></th>
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
        <td id="foot-col" colspan="{$nbgrp}"></td>
        {/if}
        <td class="colhide"></td>
        <td></td>
    </tr>
</tfoot>
<tbody>
{assign $currentsbjgroup = '---'}
{foreach $rights as $subject=>$right}

{if $rightsProperties[$subject]['grp'] && $currentsbjgroup != $rightsProperties[$subject]['grp']}
<tr class="{cycle array('odd','even')}">
    <th class="subjects-groups" colspan="{=$nbgrp*2+4}"><h3>{$rightsGroupsLabels[$rightsProperties[$subject]['grp']]}</h3></th>
</tr>{assign $currentsbjgroup = $rightsProperties[$subject]['grp']}
{/if}
<tr class="{cycle array('odd','even')}">
    <th><label for="{$subject|eschtml}">{$rightsProperties[$subject]['label']|eschtml}</label></th>
    {assign $resultr=''}
    {foreach $right as $group=>$r}
        {if $hisgroup && $group == $hisgroup->id_aclgrp}
            {if $r=='y' && $resultr==''}{assign $resultr='y'}{/if}
            {if $r=='n'}{assign $resultr='n'}{/if}
    <td>
        <select name="rights[{$subject}]" id="{$subject|eschtml}" >
        <option value=""  {if $r == ''}selected="selected"{/if}>-</option>
        <option value="y" {if $r == 'y'}selected="selected"{/if}>{@acl2.group.rights.value.yes@}</option>
        <option value="n" {if $r == 'n'}selected="selected"{/if}>{@acl2.group.rights.value.no@}</option>
        </select>
        <input type="hidden" name="currentrights[{$subject}]" value="{$r}"/>
    </td>
    <td>    {if $rightsWithResources[$subject]}yes{/if}</td>
        {else}
    <td {if !isset($groupsuser[$group])}class="notingroup {$group}" style="display:none;">
            {if $r =='y'}<img src="{$j_jelixwww}/design/icons/accept_disabled.png" alt="yes" />
            {elseif $r=='n'}<img src="{$j_jelixwww}/design/icons/cancel_disabled.png" alt="no" />{/if}
        {else}>
            {if $r =='y'}<img src="{$j_jelixwww}/design/icons/accept.png" alt="yes" />{if $resultr==''}{assign $resultr='y'}{/if}
            {elseif $r=='n'}<img src="{$j_jelixwww}/design/icons/cancel.png" alt="no" />{assign $resultr='n'}{/if}
        {/if}
    </td>
    {/if}
    {/foreach}
    <td class="colblank"></td>
    <td>
        {if $resultr =='y'}<img src="{$j_jelixwww}/design/icons/accept.png" alt="yes" />
        {else}<img src="{$j_jelixwww}/design/icons/cancel.png" alt="no" />{/if}
    </td>
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
{if $hasRightsOnResources}
<p>{@jacl2db_admin~acl2.has.rights.on.resources@}. <a href="{jurl 'jacl2db_admin~users:rightres',array('user'=>$user)}">{@jacl2db_admin~acl2.see.rights.on.resources@}</a>.</p>
{/if}
</fieldset>
</form>

