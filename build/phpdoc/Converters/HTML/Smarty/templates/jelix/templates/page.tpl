{include file="inc/header.tpl" eltype="Procedural file" class_name=$name hasel=true contents=$pagecontents}
<p>Source Location: {$source_location}</p>
{if $tutorial}
<span class="maintutorial">Main Tutorial: {$tutorial}</span>
{/if}
<h1>Classes:</h1>
<dl>
{section name=classes loop=$classes}
<dt>{$classes[classes].link}</dt>
	<dd>{$classes[classes].sdesc}</dd>
{/section}
</dl>

<h1>Page Details:</h1>
{include file="inc/docblock.tpl" type="page"}
<hr />
    {include file="inc/include.tpl"}
<hr />
    {include file="inc/global.tpl"}
<hr />
    {include file="inc/define.tpl"}
<hr />
    {include file="inc/function.tpl"}

    {include file="inc/footer.tpl"}

