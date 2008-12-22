{foreach $menuitems as $bloc}
{if count($bloc->childItems)}
<div class="menu-bloc" id="menu-bloc-{$bloc->id}">
    {if $bloc->label}<h3>{$bloc->label|eschtml}</h3>{/if}
    <ul>{foreach $bloc->childItems as $item}
        <li{if $item->id == $selectedMenuItem} class="selected"{/if}{if $item->icon} style="background-image:url({$item->icon});"{/if}><a href="{$item->link|eschtml}">{$item->label|eschtml}</a></li>
    {/foreach}</ul>
</div>
{/if}
{/foreach}