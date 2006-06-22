{meta_xul css 'chrome://global/skin/'}
{meta_xul css '/jelix/xul/jxulform.css'}
{meta_xul css '/jelix/design/xulpage.css'}
{meta_xul css '/jelix/xul/jxbl.css'}
{meta_xul ns array('jx'=>'jxbl')}

<script type="application/x-javascript"><![CDATA[
   var dsUrl =  '{jurl 'acl~admin_rightslist@rdf',array(),false}';


  {literal}
    function changeGroup( select ){
        var val = select.selectedItem.value;
        if( val!= ''){
        var url=dsUrl+"&grpid="+val;
            alert(url);
            document.getElementById('rights').setAttribute("datasources",url);

        }else{
        alert(val);
            disableAll();
        }
    }

    function disableAll(){
        document.getElementById('rights').setAttribute("datasources","");
        document.getElementById('rightsedit').collapsed=true;
        document.getElementById('users').setAttribute("datasources","");
        document.getElementById('renamesubmit').disabled=true;
        document.getElementById('deletesubmit').disabled=true;
        document.getElementById('newname').disabled=true;
    }


  {/literal}
]]></script>


<commandset id="xuladmin-cmd-set">
    <command id="cmdx_grp_new" />
</commandset>



<description class="title-page">Gestion des droits</description>
<hbox>

    <menulist id="grouplist" name="idgroup" form="renameform" oncommand="changeGroup(this)">
        <menupopup>
            <menuitem label="--" value="" />
            {foreach $groups as $grp}
            <menuitem label="{$grp->name|escxml}" value="{$grp->id_aclgrp}"/>
            {/foreach}
        </menupopup>
    </menulist>
    <spacer flex="1"/>
    <button label="Nouveau groupe" command="cmdx_grp_new" />

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
            <tab label="Propriétés" />
        </tabs>
        <tabpanels flex="1">
            <tabpanel>
                <tree id="rights" flex="1" flags="dont-build-content" ref="urn:data:row"
                      datasources="rdf:null"  onselect="" seltype="single"
                    >
                    <treecols>
                        <treecol id="subject-col" label="Sujets" primary="true" flex="2"
                                class="sortDirectionIndicator" sortActive="false"
                                sortDirection="ascending"
                                sort="rdf:http://jelix.org/ns/rights#label"/>
                        <splitter class="tree-splitter"/>
                        <treecol id="res-col" label="Ressources" flex="1"
                                 class="sortDirectionIndicator" sortActive="true"
                                 sortDirection="ascending"
                                 sort="rdf:http://jelix.org/ns/rights#id_aclres"/>
                        <splitter class="tree-splitter"/>
                        <treecol id="values-col" label="Droits" flex="3"
                                class="sortDirectionIndicator" sortActive="true"
                                sortDirection="ascending"
                                sort="rdf:http://jelix.org/ns/rights#value_label"/>
                    </treecols>
                    <template>
                        <treechildren>
                            <treeitem uri="rdf:*">
                                <treerow>
                                    <treecell label="rdf:http://jelix.org/ns/rights#label"/>
                                    <treecell label="rdf:http://jelix.org/ns/rights#id_aclres"/>
                                    <treecell label="rdf:http://jelix.org/ns/rights#value_label"/>
                                </treerow>
                            </treeitem>
                        </treechildren>
                    </template>
                </tree>
                <vbox id="rightsedit"> <!--  collapsed="true" -->
                    <groupbox submit="rightdata">
                        <caption label="Édition des droits"/>

                        <jx:submission id="rightsform" action="jsonrpc.php5" method="POST"
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
                        <jx:submit id="rightdata" form="rightsform" label="Sauvegarder"/>
                    </groupbox>
                    <groupbox>
                        <caption label="Édition des droits"/>
                        <vbox submit="rightdata2">

                            <jx:submission id="rightsform2" action="jsonrpc.php5" method="POST"
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
                            <jx:submit id="rightdata2" form="rightsform2" label="Sauvegarder"/>
                        </vbox>
                    </groupbox>

                </vbox>


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
            <tabpanel orient="horizontal" align="start">

                <groupbox submit="renamesubmit">
                    <caption label="Renommage"/>
                    <jx:submission id="renameform" action="jsonrpc.php5" method="POST"
                                    format="json-rpc" rpcmethod="acl~"
                                    onsubmit=""
                                    onresult=""
                                    onhttperror="alert('erreur http :' + event.errorCode)"
                                    oninvalidate="alert('Saisissez correctement le login et l\'email')"
                                    onrpcerror="alert(this.jsonResponse.error.toSource())"
                                    onerror="alert(this.httpreq.responseText);"
                                    />
                    <label control="newname" value="Nouveau nom"/>
                    <textbox id="newname" name="newname" value="" required="true" form="renameform" />
                    <jx:submit id="renamesubmit" form="renameform" label="Renommer"/>
                </groupbox>
                <groupbox submit="deletesubmit">
                    <caption label="Suppression du groupe"/>
                    <jx:submission id="deleteform" action="jsonrpc.php5" method="POST"
                                    format="json-rpc" rpcmethod="acl~"
                                    onsubmit="return confirm('Etes vous sûr de vouloir supprimer ce groupe ?')"
                                    onresult=""
                                    onhttperror="alert('erreur http :' + event.errorCode)"
                                    oninvalidate="alert('Saisissez correctement le login et l\'email')"
                                    onrpcerror="alert(this.jsonResponse.error.toSource())"
                                    onerror="alert(this.httpreq.responseText);"
                                    />
                    <jx:submit id="deletesubmit" form="deleteform" label="Supprimer"/>
                </groupbox>

            </tabpanel>
        </tabpanels>
    </tabbox>

</hbox>