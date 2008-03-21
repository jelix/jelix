{meta_html css $j_jelixwww.'design/jelix.css'}

<div id="page">
    <div class="logo"></div>

    <div class="nocss">
	    <hr />
	    <p>Si vous voyez ce message, c'est que vous n'avez pas rendu accessible les fichiers web (js et css) de Jelix. Deux solutions :</p>
	    <ul>
		    <li>vous pouvez configurer votre virtualhost et créer un alias monapp/www/jelix/ pointant vers lib/jelix-www/</li>
		    <li>sinon copiez/collez le dossier lib/jelix-www/ dans le dossier www/ de votre application et renommez le en 'jelix'</li>
	    </ul>
	    <p>Si vous voulez utiliser un autre nom que jelix pour ce dossier, modifier le paramêtre jelixWWWPath dans monapp/var/config/defaultconfig.ini.</p>
	    <p>Pour plus d'informations, consultez <a href="http://jelix.org/articles/manuel/installation/application#configuration-du-serveur" title="documentation officielle">la documentation sur l'installation de Jelix</a>.</p>
	    <hr />
    </div>

    <div class="monbloc">
        <h2>Votre configuration est-elle correcte ?</h2>
        <div class="blockcontent">
            {$check}
        </div>
    </div>

    <div class="monbloc">
        <h2>Ceci est une page temporaire</h2>
        <div class="blockcontent">
            <p>Cette page n'apparaît que parce que vous venez de créer votre application. Elle fait partie du module jelix non présent dans votre application.</p>
        </div>
    </div>

    <div class="monbloc">
        <h2>Que faire maintenant ?</h2>
        <div class="blockcontent">
            <ul>
	            <li><a href="http://www.jelix.org" title="official Jelix's site">Site officiel de Jelix</li>
	            <li><a href="http://jelix.org/articles/manuel" title="Jelix's documenation">Documentation de Jelix</li>
            </ul>
        </div>
    </div>
    
    <div id="jelixpowered"></div>
</div>
