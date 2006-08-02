{if count($includes) > 0}
<h2>Includes:</h2>
<ul>
{section name=includes loop=$includes}
<li>{$includes[includes].include_name}({$includes[includes].include_value}) <span class="linenumber">[line {if $includes[includes].slink}{$includes[includes].slink}{else}{$includes[includes].line_number}{/if}]</span>

{include file="inc/docblock.tpl" sdesc=$includes[includes].sdesc desc=$includes[includes].desc tags=$includes[includes].tags}
</li>
{/section}
</ul>
{/if}