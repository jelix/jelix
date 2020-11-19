<h2>Contents</h2>

<h3>Simple tests</h3>
<ul>
   <li><a href="{jurl 'main:hello'}">Hello world in html</a></li>
   <li><a href="{jurl 'main:hello', array('output'=>'text')}">Hello world in text</a></li>
   <li><a href="{jurl 'main:hello2'}">Overloaded "Hello world" template</a></li>
   <li><a href="{jurl 'main:hello3'}">"Hello world" template of a theme</a></li>
   <li><a href="{jurl 'main:testdao'}">Dao test</a></li>
   <li><a href="{jurl 'main:generateerror'}">error on a page</a></li>
   <li><a href="{jurl 'main:generatewarning'}">warning on a page</a></li>
   <li><a href="{jurl 'main:testminify'}">page using minify</a></li>
   <li><a href="{jurl 'main:sitemap'}">sitemap</a></li>
   <li><a href="{jurl 'main:installchecker'}">Install checker</a></li>
</ul>

<h3>Unit tests</h3>
<ul>
   <li> JS unit tests :
      <ul>
        <li><a href="{jurl 'jelix_tests~jstests:jforms'}">jforms</a></li>
        <li><a href="{jurl 'jelix_tests~jstests:jsonrpc'}">jsonrpc</a></li>
        <li><a href="{jurl 'jelix_tests~jstests:testinclude'}">include</a></li>
      </ul>
   </li>
</ul>

<h3>Forms</h3>
<ul>
   <li><a href="{jurl 'samplecrud:index'}">Crud form</a></li>
</ul>
<p>Simple form (singleton)</p>
<ul>
   <li><a href="{jurl 'sampleform:newform'}">New form</a></li>
   <li><a href="{jurl 'sampleform:newajaxform'}">New ajax form</a></li>
   <li><a href="{jurl 'sampleform:show'}">See the form</a> (<a href="{jurl 'sampleform:show', array('full'=>1)}">full</a>)</li>
   <li><a href="{jurl 'sampleform:ok'}">Results</a></li>
   <li><a href="{jurl 'sampleform:destroy'}">Destroy the form</a></li>
</ul>

<p>Multiple instance of a form</p>
<ul>
   <li><a href="{jurl 'forms:newform'}">New form</a></li>
   <li><a href="{jurl 'forms:listform'}">Instance list</a></li>
</ul>



<h3>Syndication examples</h3>
<ul>
   <li><a href="{jurl 'syndication:rss'}">Rss 2.0</a></li>
   <li><a href="{jurl 'syndication:atom'}">Atom 1.0</a></li>
</ul>

<h3>Soap tests</h3>
<ul>
   <li><a href="{jurl 'clientSoap:soapExtension'}">client (soap extension)</a></li>
   <li><a href="{jurl 'jsoap~WSDL:index'}">Web services documentation</a></li>
   <li><a href="{jurl 'jsoap~WSDL:wsdl', array('service'=>'testapp~soap')}">WSDL</a></li>
</ul>

<h3>Others</h3>
<ul>
   <li><a href="{$j_basepath}admin/index.php">Admin app</a></li>
</ul>
