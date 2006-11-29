<h2>Tests unitaires</h2>
Version php : {$versionphp}<br/>
Version Jelix: {$versionjelix}<br/>

<p><a href="?">Retour à l'accueil</a></p>
<h3>Core</h3>
<ul>
    <li><a href="{jurl 'unittest~testselectoract'}">selecteurs d'action</a></li> <!--?module=unittest&amp;action=testselectoract-->
</ul>
<h3>jEvent</h3>
<ul>
    <li><a href="?module=unittest&amp;action=testevent">lancer deux évènements</a></li>
</ul>

<h3>jUrl</h3>
<ul>
    <li><a href="?module=unittest&amp;action=testurlcreate">Tester la création d'url</a></li>
    <li><a href="?module=unittest&amp;action=testurlparse">Tester l'analyse d'url</a></li>
</ul>
{if $isurlsig}
<p>test urls :
<a href="{jurl 'unittest~urlsig_url1',array('annee'=>'2006','mois'=>'10','id'=>'01')}">url1</a>
<a href="{jurl 'unittest~urlsig_url9',array('annee'=>'2006','mois'=>'10','id'=>'09')}">url9</a>
<a href="{jurl 'unittest~urlsig_url10',array('annee'=>'2006','mois'=>'10','id'=>'10')}">url10</a>
<a href="{jurl 'unittest~urlsig_url3',array('rubrique'=>'voiture','id_art'=>'54','article'=>'dodge viper')}">url3</a>
<a href="{jurl 'unittest~urlsig_url2',array('annee'=>'2005','mois'=>'7')}">url2</a>
<a href="{jurl 'unittest~urlsig_url4',array('first'=>'premier parametre','second'=>'toto le rigolo')}">url4</a>
</p>{/if}
<h3>jDao</h3>
<ul>
    <li><a href="?module=unittest&amp;action=dao_parser">Parser</a></li>
    <li><a href="?module=unittest&amp;action=dao_parser2">Parser (2)</a></li>
    <li><a href="?module=unittest&amp;action=dao_conditions">jDaoConditions</a></li>
</ul>

<h3>Utilitaires</h3>
<ul>
    <li><a href="?module=unittest&amp;action=testfilter">jFilter</a></li>
</ul>
