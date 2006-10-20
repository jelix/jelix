{foreach $items as $item}
<item>
    {foreach $item as $elem_name => $elem_value}
        {if is_array($elem_value)}
            {foreach $elem_value as $value}
                <{$elem_name}>{$value|escxml}</{$elem_name}>
            {/foreach}
        {else}
            <{$elem_name}>{$elem_value|escxml}</{$elem_name}>
        {/if}
    {/foreach}
</item>
{/foreach}
