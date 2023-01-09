<h1>{@jelix~crud.title.list@}</h1>
{if isset($filterForm) && $filterForm}
<h2>{@jelix~crud.title.filter@}</h2>
{form $filterForm, $filterAction, [], 'html', [ 'method' => 'GET']}
<table class="jforms-table">
{formcontrols $filterFields}
    <tr>
        <th scope="row">{ctrl_label}</th>
        <td>{ctrl_control}</td>
    </tr>
{/formcontrols}
</table>
<div>{formreset}{formsubmit}</div>
{/form}
<h2>{jlocale 'jelix~crud.title.results', $recordCount}</h2>
{/if}

<table class="records-list">
<thead>
<tr>
    {foreach $properties as $propname}
    <th>
        {if $showPropertiesOrderLinks && array_key_exists($propname, $propertiesForListOrder)}
        <a href="{jurl '#~#', array($offsetParameterName=>$page, 'listorder'=>$propname)}"
           class="view-order{if isset($sessionForListOrder[$propname])} {if $sessionForListOrder[$propname] == 'asc'} order-asc{elseif $sessionForListOrder[$propname] == 'desc'} order-desc{/if}{/if}">
            {/if}
            {if isset($controls[$propname]) && $controls[$propname]->label}{$controls[$propname]->label|eschtml}{else}{$propname|eschtml}{/if}
            {if $showPropertiesOrderLinks && array_key_exists($propname, $propertiesForListOrder)}</a>{/if}
    </th>
    {/foreach}
    <th>&nbsp;</th>
</tr>
</thead>
<tbody>
{foreach $list as $record}
<tr class="{cycle array('odd','even')}">
    {foreach $properties as $propname}
    <td>{$record->$propname|eschtml}</td>
    {/foreach}
    <td>
        <a href="{jurl $viewAction,array('id'=>$record->$primarykey)}">{@jelix~crud.link.view.record@}</a>
    </td>
</tr>
{/foreach}
</tbody>
</table>
{if $recordCount > $listPageSize}
<p class="record-pages-list">{@jelix~crud.title.pages@} : {pagelinks $listAction, array(),  $recordCount, $page, $listPageSize, $offsetParameterName }</p>
{/if}
<p><a href="{jurl $createAction}" class="crud-link">{@jelix~crud.link.create.record@}</a>.</p>

