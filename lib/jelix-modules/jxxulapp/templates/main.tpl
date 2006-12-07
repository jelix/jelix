{meta_xul css 'chrome://global/skin/'}
<script type="application/x-javascript" src="xulapp/main.js" />
<script type="application/x-javascript"><![CDATA[
var gUrlQuit = '{jurl 'jxauth~login_out',array(),false}';
]]></script>

<commandset id="commandset-main">
    <command id="cmdx_quit" oncommand="CmdxQuit()" />
</commandset>

<keyset id="keyset-main"></keyset>

<menubar id="menubar-main">
  <menu label="Rubriques" id="menu-rubrique" accesskey="R">
    <menupopup id="menupopup-sections">
    </menupopup>
  </menu>

  <menu label="Outils" id="menu-outils" accesskey="O">
    <menupopup id="menupopup-outils">
    </menupopup>
  </menu>
  <menu label="Administration" id="menu-admin" accesskey="A">
    <menupopup id="menupopup-admin">
    </menupopup>
  </menu>
</menubar>

<toolbox id="toolbox-main">
    <toolbar id="toolbar-main">

        <toolbarspacer flex="1" id="toolbar-spacer"/>
        <toolbarbutton label="Quitter" command="cmdx_quit" accesskey="Q"/>
    </toolbar>

</toolbox>

<iframe flex="1" id="mainContent"/>

<statusbar> <textbox id="statusmessage" value="" flex="1"/>
</statusbar>