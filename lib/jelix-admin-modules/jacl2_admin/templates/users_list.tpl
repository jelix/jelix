{meta_html css  $j_jelixwww.'design/jacl2.css'}


<h1>{@jacl2_admin~acl2.users.title@}</h1>


<form action="{formurl 'jacl2_admin~users:index'}" method="get">
<fieldset><legend>{@jacl2_admin~acl2.filter.title@}</legend>
{formurlparam 'jacl2_admin~users:index'}
    <select name="grpid">
    {foreach $groups as $group}
        <option value="{$group->id_aclgrp}" {if $group->id_aclgrp == $grpid}selected="selected"{/if}>{$group->name}</option>
    {/foreach}
     </select>
    <input type="submit" value="{@jacl2_admin~acl2.show.button@}" />
</fieldset>
</form>

{if $usersCount == 0}
<p>{@jacl2_admin~acl2.no.user.message@}</p>
{else}
<table class="rights">
<thead>
    <tr>
        <th>{@jacl2_admin~acl2.col.users@}</th>
        <th>{@jacl2_admin~acl2.col.groups@}</th>
        <th></th>
    </tr>
</thead>
<tbody>
{foreach $users as $user}
    <tr>
        <td>{$user->login}</td>
        <td>{foreach $user->groups as $group} {$group->name} {/foreach}</td>
        <td><a href="{jurl 'jacl2_admin~users:rights', array('user'=>$user->login)}">{@jacl2_admin~acl2.rights.link@}</a></td>
    </tr>
{/foreach}
</tbody>
</table>
{/if}

{if $usersCount > 0}
<div class="pages">{@jacl2_admin~acl2.pages.links.label@} {pagelinks 'jacl2_admin~users:index', array('grpid'=>$grpid),  $usersCount, $offset, $listPageSize, 'idx' }</div>
{/if}



