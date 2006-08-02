</div>

<div id="mainfooter">

    <div id="info">
        <div class="wfooterinfo">Documentation generated on {$date} by <a href="{$phpdocwebsite}">phpDocumentor {$phpdocversion}</a></div>
        <div class="wfooterbuttons"></div>
    </div>
    <div id="authinfo">
        <div class="wfooterinfo"></div>
        <div class="wfooterbuttons"></div>
    </div>
</div>

<div id="sidemenu">
    <h1><a href="http://jelix.org" title="retour sur la page d'accueil du site"><img src="{$subdir}media/logo_jelix_moyen.png" alt="Jelix" /></a></h1>
    
    <ul>
    {if count($ric) >= 1}
        {section name=ric loop=$ric}
        <li><a href="{$subdir}{$ric[ric].file}">{$ric[ric].name}</a></li>
        {/section}
    {/if}
    {if $hastodos}
        <li><a href="{$subdir}{$todolink}">Todo List</a></li>
    {/if}
    </ul>
    
    <h3>Packages:</h3>
    <ul>
        {section name=packagelist loop=$packageindex}
        <li><a href="{$subdir}{$packageindex[packagelist].link}">{$packageindex[packagelist].title}</a></li>
        {/section}
    </ul>

    {if $tutorials}
        <h3>Tutorials/Manuals:</h3>
        <ul>
        {if $tutorials.pkg}
        <li>Package-level:
        {section name=ext loop=$tutorials.pkg}
        {$tutorials.pkg[ext]}
        {/section}
        </li>
        {/if}
        
        {if $tutorials.cls}
        <li>Class-level:
        {section name=ext loop=$tutorials.cls}
        {$tutorials.cls[ext]}
        {/section}
        </li>
        {/if}
        {if $tutorials.proc}
        <li>Procedural-level:
        {section name=ext loop=$tutorials.proc}
        {$tutorials.proc[ext]}
        {/section}
        </li>
        {/if}
         </ul>
    {/if}
    
    {if !$noleftindex}{assign var="noleftindex" value=false}{/if}
    {if !$noleftindex}
        {if $compiledinterfaceindex}
        <h3>Interfaces:</h3>
        {eval var=$compiledinterfaceindex}{/if}
        {if $compiledclassindex}
        <h3>Classes:</h3>
        {eval var=$compiledclassindex}{/if}
            
        {if $compiledfileindex}
         <h3>Files:</h3>
                {eval var=$compiledfileindex}{/if}
        
    {/if}
</div>


<div id="footer">

</div>
</body>
</html>
