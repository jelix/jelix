<h1>{$titre|upper}</h1>
<ul>
{foreach $countries as $country}
<li>{$country} ({$country|count_characters})</li>
{/foreach}
</ul>
