{meta_html assets 'jacl2_admin'}

<h1>{@jacl2db_admin~acl2.user.rightres.title@} {$user}</h1>


<form action="{formurl 'jacl2db_admin~users:saverightres',array('user'=>$user)}" method="post">
<fieldset><legend>{@jacl2db_admin~acl2.rightres.title@}</legend>
<div>{formurlparam 'jacl2db_admin~users:saverightres',array('user'=>$user)}</div>
{if count($rightsWithResources)}

<p><strong>{@jacl2db_admin~acl2.warning.deleting.rightres@}</strong></p>

<table class="records-list jacl2-list">
<thead>
    <tr>
        <th>{@jacl2db_admin~acl2.col.subjects@}</th>
        <th>{@jacl2db_admin~acl2.col.resources@}</th>
    </tr>
</thead>
<tfoot>
    <tr>
        <td><input type="submit" value="{@jacl2db_admin~acl2.delete.button@}" /></td>
        <td></td>
    </tr>
</tfoot>
<tbody>
{foreach $rightsWithResources as $subject=>$resources}
<tr class="{cycle array('odd','even')}">
    <th>
        <input type="checkbox" name="subjects[{$subject}]" id="{$subject|eschtml}" />
        <label for="{$subject|eschtml}">{$rightsLabels[$subject]|eschtml}</label>
    </th>
    <td>{assign $firstr=true}
        {foreach $resources as $r}{if !$firstr}, {else}{assign $firstr=false}{/if}
        <span class="aclres{$r->canceled}">{$r->id_aclres|eschtml}</span>{/foreach}</td>
</tr>
{/foreach}
</tbody>
</table>
{else}
<p>{@jacl2db_admin~acl2.no.rightres@}</p>
{/if}
</fieldset>
</form>

<p><a href="{jurl 'jacl2db_admin~users:rights',array('user'=>$user)}">{@jacl2db_admin~acl2.link.return.to.rights@}</a>.</p>

