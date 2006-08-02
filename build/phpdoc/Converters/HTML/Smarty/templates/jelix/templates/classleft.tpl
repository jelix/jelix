<ul>
{foreach key=subpackage item=files from=$classleftindex}
    <li>{if $subpackage != ""}<b>{$subpackage}</b>{else}miscellaneous{/if}
	<ul>{section name=files loop=$files}
		<li>{if $files[files].link != ''}<a href="{$files[files].link}">{/if}
		{$files[files].title}
		{if $files[files].link != ''}</a>{/if}</li>
	{/section}
    </ul></li>
{/foreach}
</ul>
