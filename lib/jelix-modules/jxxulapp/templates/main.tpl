{meta_xul css 'chrome://global/skin/'}
<script type="application/x-javascript" src="xulapp/main.js" />
<script type="application/x-javascript"><![CDATA[
var gUrlQuit = '{jurl 'jxauth~login_out',array(),false}';

{literal}
function XulAppOnLoad(ev){
  // pour le bug du load qui se propage au fenêtre parentes..
  if(ev.target != document)
    return;
}

document.addEventListener("load", XulAppOnLoad, false);

function CmdxQuit(){
    if(confirm("Étes vous sûr de vouloir quitter l'application ?"))
          window.location.href= gUrlQuit;
}
{/literal}
]]></script>

<commandset id="commandset-main">
    <command id="cmdx_quit" oncommand="CmdxQuit()" />
</commandset>

<keyset id="keyset-main">

</keyset>


<menubar id="menubar-main">
  <menu label="Rubriques" id="menu-rubrique">
    <menupopup id="menupopup-sections">
    </menupopup>

  </menu>

  <menu label="Outils" id="menu-outils">
    <menupopup id="menupopup-outils">
    </menupopup>
  </menu>
  <menu label="Administration" id="menu-admin">
    <menupopup id="menupopup-admin">
    </menupopup>
  </menu>
</menubar>

<toolbox id="toolbox-main">
    <toolbar id="toolbar-main">

        <toolbarspacer flex="1" id="toolbar-spacer"/>
        <toolbarbutton label="Quitter" command="cmdx_quit" />
    </toolbar>

</toolbox>

<iframe flex="1" id="content" />

<!--<tabbox flex="1" id="tabbox-main">
    <tabs closebutton="true" id="tabs-main">
        <tab label="Tableau de bord" />
    </tabs>
    <tabpanels flex="1" id="tabpanels-main">
        <tabpanel>
            <vbox>
                <html:h1>Tableau de bord</html:h1>
            </vbox>


        </tabpanel>
    </tabpanels>

</tabbox>
-->