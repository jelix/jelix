{meta_html assets 'jacl2_admin'}
<h1>{@jacl2db_admin~acl2.group.view.title@.' '.$group->name}</h1>

<table class="jforms-table">
<tbody>
    <tr>
        <th> {@jacl2db_admin~acl2.col.groups.name@} </th>
        <td> {$group->name} </td>
    </tr>
    <tr>
        <th> {@jacl2db_admin~acl2.table.th.rights@} </th>
        <td>
            {if count($rights)}
            <ul>
        {foreach $rights as $right}
            <li> {$right} </li>
        {/foreach}
        </ul>{else}
                <p>{@jacl2db_admin~acl2.no.rights@}.</p>
        {/if}
        <a href="{jurl 'jacl2db_admin~groups:rights', array('group' => $group->id_aclgrp)}">{@jacl2db_admin~acl2.groups.change.rights.link@}</a>
        </td>
    </tr>
    {if $users !== null}
    <tr>
        <th> {@jacl2db_admin~acl2.col.users@} </th>
        <td> <ul>
        {foreach $users as $user}
            <li> {$user} </li>
        {/foreach}
        </ul> </td>
    </tr>
    {/if}
</tbody>
</table>
