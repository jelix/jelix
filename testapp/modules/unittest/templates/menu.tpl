<h2>Tests unitaires</h2>
Version php : {$versionphp}<br/>
Version Jelix: {$versionjelix}<br/>

<p><a href="?">Retour à l'accueil</a></p>
<h3>Core</h3>
{if $isurlsig}<ul>

<li>test urls :
<a href="{jurl 'unittest~urlsig_url1',array('annee'=>'2006','mois'=>'10','id'=>'01')}">url1</a>
<a href="{jurl 'unittest~urlsig_url9',array('annee'=>'2006','mois'=>'10','id'=>'09')}">url9</a>
<a href="{jurl 'unittest~urlsig_url10',array('annee'=>'2006','mois'=>'10','id'=>'10')}">url10</a>
<a href="{jurl 'unittest~urlsig_url3',array('rubrique'=>'voiture','id_art'=>'54','article'=>'dodge viper')}">url3</a>
<a href="{jurl 'unittest~urlsig_url2',array('annee'=>'2005','mois'=>'7')}">url2</a>
<a href="{jurl 'unittest~urlsig_url4',array('first'=>'premier parametre','second'=>'toto le rigolo')}">url4</a>
</li>
</ul>
{/if}

<h3>jDao</h3>
<ul>
    <li><a href="?module=unittest&amp;action=dao_parser">Parser</a></li>
    <li><a href="?module=unittest&amp;action=dao_parser2">Parser (2)</a></li>
    <li><a href="?module=unittest&amp;action=dao_conditions">jDaoConditions</a></li>
    <li><a href="?module=unittest&amp;action=dao_index">api</a></li>
</ul>
<h3>jAcl</h3>
<ul>
    <li><a href="?module=unittest&amp;action=acl_usergroup">jAclUserGroup</a></li>
    <li><a href="?module=unittest&amp;action=acl_manager">jAclManager</a></li>
    <li><a href="?module=unittest&amp;action=acl_index">jAcl</a></li>
</ul>
