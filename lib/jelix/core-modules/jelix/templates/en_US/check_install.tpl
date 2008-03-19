{meta_html css $j_jelixwww.'design/jelix.css'}

<div class="logo"></div>

<div class="nocss">
	<hr />
	<p>If you see this message, it's because you don't set correctly Jelix's web files (js and css). Two solutions :</p>
	<ul>
		<li>you can configure your virtualhost and create an alias myapp/www/jelix/ to lib/jelix-www/</li>
		<li>otherwise copy/past the lib/jelix-www/ directory in myapp/www/ and rename it 'jelix'</li>
	</ul>
	<p>If you want to use another name for the Jelix's web file directory, modify the jelixWWWPath parameter in myapp/var/config/defaultconfig.ini.</p>
	<p>For more informations, see <a href="http://jelix.org/articles/en/manual/installation" title="installation documentation">the Jelix's installation documentation</a>.</p>
	<hr />
</div>

<h2>Is your server configuration correct ?</h2>
{$check}

<h2>This is a temporary page</h2>
<p>This page is part of the Jelix default module. It will not appear anymore.</p>

<h2>What to do now ?</h2>
<ul>
	<li><a href="http://www.jelix.org" title="official Jelix's site">Official Jelix's site</li>
	<li><a href="http://jelix.org/articles/manuel" title="Jelix's documenation">Jelix's documenation</li>
</ul>