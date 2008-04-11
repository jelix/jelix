{meta_html css  $j_jelixwww.'design/jacl2.css'}

<h1>{@jacl2_admin~acl2.groups.title@}</h1>

<table class="rights">
<thead>
    <tr>
        <th></th>
    {foreach $groups as $group}
        <th>{$group->name}</th>
    {/foreach}
    </tr>
</thead>
<tbody>

{assign $line = true}
{foreach $rights as $subject=>$right}
<tr class="{if $line}odd{else}even{/if}">
    <th>{$subject}</th>
    {foreach $right as $group=>$r}
    <td>{if $r}X{/if}</td>
    {/foreach}
</tr>
{assign $line = !$line}
{/foreach}
</tbody>
</table>
