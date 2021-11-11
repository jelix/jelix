<h1>{@jelix~crud.title.list@}</h1>
{if isset($filterForm) && filterForm}
<h2>{@jelix~crud.title.filter@}</h2>
{form $filterForm, $filterAction}
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
    {if isset($controls[$propname])}
    <th>{$controls[$propname]->label|eschtml}</th>
    {else}
    <th>{$propname|eschtml}</th>
    {/if}
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

