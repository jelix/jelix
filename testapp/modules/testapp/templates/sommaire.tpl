<h2>Sommaire</h2>

<h3>Tests simples</h3>
<ul>
   <li><a href="{jurl 'main:hello'}">Hello world en html</a></li>
   <li><a href="{jurl 'main:hello', array('output'=>'text')}">Hello world en texte</a></li>
   <li><a href="{jurl 'main:hello2'}">Template Hello world surchargé</a></li>
   <li><a href="{jurl 'main:testdao'}">test dao</a></li>
</ul>

<h3>Tests Unitaires</h3>
<ul>
    <li><a href="{jurl 'junittests~default:index'}">Tests unitaires</a></li>
</ul>

<h3>Formulaires</h3>
<ul>
   <li><a href="{jurl 'sampleform:status'}">données session</a></li>
   <li><a href="{jurl 'samplecrud:index'}">Formulaire crud</a></li>
</ul>
<p>formulaire simple (singleton)</p>
<ul>
   <li><a href="{jurl 'sampleform:newform'}">Nouveau formulaire</a></li>
   <li><a href="{jurl 'sampleform:show'}">voir le formulaire</a> (<a href="{jurl 'sampleform:show', array('full'=>1)}">full</a>)</li>
   <li><a href="{jurl 'sampleform:ok'}">Resultats</a></li>
   <li><a href="{jurl 'sampleform:destroy'}">détruire le formulaire</a></li>
</ul>

<p>formulaire à instances multiples</p>
<ul>
   <li><a href="{jurl 'forms:newform'}">Nouveau formulaire</a></li>
   <li><a href="{jurl 'forms:listform'}">liste des instances</a></li>
</ul>



<h3>Tests syndication</h3>
<ul>
   <li><a href="{jurl 'syndication:rss'}">Rss 2.0</a></li>
   <li><a href="{jurl 'syndication:atom'}">Atom 1.0</a></li>
</ul>

<h3>Tests soap</h3>
<ul>
   <li><a href="{jurl 'clientSoap:soapExtension'}">client (soap extension)</a></li>
   <li><a href="{jurl 'jWSDL~WSDL:index'}">Web services documentation</a></li>
   <li><a href="{jurl 'jWSDL~WSDL:wsdl', array('service'=>'testapp~soap')}">WSDL</a></li>
</ul>
