<h2>Tests unitaires</h2>

<p><a href="?">Retour à l'accueil</a></p>
{if $isurlsig}<ul>

<li>test urls :
<a href="{jurl 'jelix_tests~urlsig_url1',array('annee'=>'2006','mois'=>'10','id'=>'01')}">url1</a>
<a href="{jurl 'jelix_tests~urlsig_url9',array('annee'=>'2006','mois'=>'10','id'=>'09')}">url9</a>
<a href="{jurl 'jelix_tests~urlsig_url10',array('annee'=>'2006','mois'=>'10','id'=>'10')}">url10</a>
<a href="{jurl 'jelix_tests~urlsig_url3',array('rubrique'=>'voiture','id_art'=>'54','article'=>'dodge viper')}">url3</a>
<a href="{jurl 'jelix_tests~urlsig_url2',array('annee'=>'2005','mois'=>'7')}">url2</a>
<a href="{jurl 'jelix_tests~urlsig_url4',array('first'=>'premier parametre','second'=>'toto le rigolo')}">url4</a>
</li>
</ul>
{/if}
