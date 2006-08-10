{capture name="tutle"}File Source for {$name}{/capture}
{include file="header.tpl" title=$smarty.capture.tutle}
<h1>Source for file {$name}</h1>
<p>Documentation is available at {$docs}</p>
<pre class="src-code">
{$source}
</pre>
{include file="footer.tpl"}