{meta_xul css 'chrome://global/skin/'}
{meta_xul css '/jelix/xul/jxulform.css'}
{meta_xul css '/jelix/design/xulpage.css'}
{meta_xul css '/jelix/xul/jxbl.css'}
{meta_xul ns array('jxf'=>'jxulform', 'jx'=>'http://jelix.org/ns/xbl/1.0')}

<script type="application/x-javascript"><![CDATA[
  {literal}

  {/literal}
]]></script>
<description class="title-page">Gestion des droits</description>
<hbox>
    <menulist>
        <menupopup>
            <menuitem label="groupe 1" />
            <menuitem label="groupe 2" />
            <menuitem label="groupe 3" />
        </menupopup>
    </menulist>
    <button label="Renommer" />
    <button label="Supprimer" />
    <button label="Nouveau groupe" />
    <spacer flex="1" />

</hbox>

<!--
<vbox flex="1">
  <hbox flex="1">

  </hbox>

  <hbox>

  </hbox>
</vbox>-->
<hbox flex="1">
    <tabbox flex="1">
        <tabs>
            <tab label="Droits" />
            <tab label="Utilisateurs" />
        </tabs>
        <tabpanels flex="1">
            <tabpanel>
                <tree id="rights" flex="1" flags="dont-build-content" ref="urn:data:row" datasources="rdf:null"
                    onselect="" seltype="single"
                    >
                    <treecols>
                        <treecol id="subject-col" label="Sujets" primary="true" flex="1"
                                class="sortDirectionIndicator" sortActive="false"
                                sortDirection="ascending"
                                sort="rdf:http://jelix.org/ns/rights#subject"/>
                        <splitter class="tree-splitter"/>
                        <treecol id="res-col" label="Ressources" flex="1"
                                 class="sortDirectionIndicator" sortActive="true"
                                 sortDirection="ascending"
                                 sort="rdf:http://jelix.org/ns/rights#res"/>
                        <splitter class="tree-splitter"/>
                        <treecol id="values-col" label="Droits" flex="1"
                                class="sortDirectionIndicator" sortActive="true"
                                sortDirection="ascending"
                                sort="rdf:http://jelix.org/ns/rights#values"/>
                    </treecols>
                    <template>
                        <treechildren>
                            <treeitem uri="rdf:*">
                                <treerow>
                                    <treecell label="rdf:http://jelix.org/ns/rights#subject"/>
                                    <treecell label="rdf:http://jelix.org/ns/rights#res"/>
                                    <treecell label="rdf:http://jelix.org/ns/rights#values"/>
                                </treerow>
                            </treeitem>
                        </treechildren>
                    </template>
                </tree>
            </tabpanel>
            <tabpanel>
                <tree id="users" flex="1" flags="dont-build-content" ref="urn:data:row" datasources="rdf:null"
                    onselect="" seltype="single"
                    >
                    <treecols>
                        <treecol id="logins-col" label="Logins" primary="true" flex="1"
                                class="sortDirectionIndicator" sortActive="false"
                                sortDirection="ascending"
                                sort="rdf:http://jelix.org/ns/usersgroup#login"/>
                    </treecols>
                    <template>
                        <treechildren>
                            <treeitem uri="rdf:*">
                                <treerow>
                                    <treecell label="rdf:http://jelix.org/ns/usersgroup#login"/>
                                </treerow>
                            </treeitem>
                        </treechildren>
                    </template>
                </tree>

            </tabpanel>
        </tabpanels>
    </tabbox>
    <vbox id="rightsedit"> <!--  collapsed="true" -->
        <groupbox>
            <caption label="Édition des droits"/>
            <vbox submit="rightdata">

                <jxf:submission id="rightsform" action="jsonrpc.php5" method="POST"
                                format="json-rpc" rpcmethod="acl~"
                                onsubmit=""
                                onresult=""
                                onhttperror="alert('erreur http :' + event.errorCode)"
                                oninvalidate="alert('Saisissez correctement le login et l\'email')"
                                onrpcerror="alert(this.jsonResponse.error.toSource())"
                                onerror="alert(this.httpreq.responseText);"
                                />
                <checkbox label="foo" />
                <checkbox label="bar" />
                <checkbox label="baz" />
                <jxf:submit id="rightdata" form="rightsform" label="Sauvegarder"/>
            </vbox>
        </groupbox>
        <groupbox>
            <caption label="Édition des droits"/>
            <vbox submit="rightdata2">

                <jxf:submission id="rightsform2" action="jsonrpc.php5" method="POST"
                                format="json-rpc" rpcmethod="acl~"
                                onsubmit=""
                                onresult=""
                                onhttperror="alert('erreur http :' + event.errorCode)"
                                oninvalidate="alert('Saisissez correctement le login et l\'email')"
                                onrpcerror="alert(this.jsonResponse.error.toSource())"
                                onerror="alert(this.httpreq.responseText);"
                                />
                <radiogroup>
                <radio label="foo" />
                <radio label="bar" />
                <radio label="baz" />
            </radiogroup>
                <jxf:submit id="rightdata2" form="rightsform2" label="Sauvegarder"/>
            </vbox>
        </groupbox>

    </vbox>
</hbox>