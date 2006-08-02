{section name=vars loop=$vars}
{if $vars[vars].static}
{if $show == 'summary'}
	static var {$vars[vars].var_name}, {$vars[vars].sdesc}<br>
{else}
	<a name="{$vars[vars].var_dest}"></a>
	<h4>static {$vars[vars].var_name}{if $vars[vars].has_default} = <span class="value">{$vars[vars].var_default|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</span>{/if}</h4>
		<p class="linenumber">[line {if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}]</p>
                {include file="inc/docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc tags=$vars[vars].tags}
		<p><b>Type:</b> {$vars[vars].var_type}</p>
		<p><b>Overrides:</b> {$vars[vars].var_overrides}</p>
	<p class="top">[ <a href="#top">Top</a> ]</p>
{/if}
{/if}
{/section}
{section name=vars loop=$vars}
{if !$vars[vars].static}
{if $show == 'summary'}
	var {$vars[vars].var_name}, {$vars[vars].sdesc}<br>
{else}
	<a name="{$vars[vars].var_dest}"></a>
	<h4>{$vars[vars].var_name}{if $vars[vars].has_default} = <span class="value">{$vars[vars].var_default|replace:"\n":"<br>\n"|replace:" ":"&nbsp;"|replace:"\t":"&nbsp;&nbsp;&nbsp;"}</span>{/if}</h4>
		<p class="linenumber">[line {if $vars[vars].slink}{$vars[vars].slink}{else}{$vars[vars].line_number}{/if}]</p>
                {include file="inc/docblock.tpl" sdesc=$vars[vars].sdesc desc=$vars[vars].desc tags=$vars[vars].tags}
		<p><b>Type:</b> {$vars[vars].var_type}</p>
		<p><b>Overrides:</b> {$vars[vars].var_overrides}</p>
	<p class="top">[ <a href="#top">Top</a> ]</p>
{/if}
{/if}
{/section}
