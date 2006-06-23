{meta_xul css 'chrome://global/skin/'}
{meta_xul css '/jelix/xul/jxulform.css'}
{meta_xul css '/jelix/design/xulpage.css'}
{meta_xul css '/jelix/xul/jxbl.css'}
{meta_xul ns array('jx'=>'jxbl')}

<script type="application/x-javascript"><![CDATA[

  {literal}
    function changeGroup( select ){
        var val = select.selectedItem.value;
        if( val!= ''){
            {/literal}
            var url={urljsstring 'acl~admin_rightslist@rdf',array(),array('grpid'=>'val')};
            {literal}
            document.getElementById('rights').setAttribute("datasources",url);

        }else{
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
        document.getElementById("rightsforms").selectedIndex=0;
    }


    function onSubjectSelect(tree){
        var idx = tree.view.selection.currentIndex;
        if(idx == -1){
            selectRightForm("0",0);
        }else{
            var colvalue = tree.columns.getNamedColumn ( "value-col");
            var colvalgrp = tree.columns.getNamedColumn ( "valgrp-col");

            var rightvalue= tree.view.getCellText(idx, colvalue);
            var valgrp =  tree.view.getCellText(idx, colvalgrp);
            selectRightForm(valgrp, rightvalue);
        }
    }


    function selectRightForm(idvalgrp, rightvalue){
        var deck=document.getElementById("rightsforms");
        if(idvalgrp == "0"){
            deck.selectedIndex=0;

        }else{
            deck.selectedIndex=0;
            var grpbox = deck.getElementsByTagName("groupbox");
            for(var i =0; i < grpbox.length; i++){
               if(grpbox[i].getAttribute("valgrp") == idvalgrp){
                    deck.selectedIndex=i+1;
                    break;
               }
            }
            if(deck.selectedIndex!=0){
                var chks = grpbox[i].getElementsByTagName("checkbox");
                var chkvalue, value=parseInt(rightvalue);
                for(var j=0; j < chks.length; j++){
                    chkvalue=parseInt(chks[j].getAttribute('rightvalue'));
                    if((chkvalue & value) == chkvalue)
                        chks[j].checked=true;
                    else
                        chks[j].checked=false;
                }
            }
        }
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
                      datasources="rdf:null"  onselect="onSubjectSelect(this)" seltype="single"
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
                        <treecol id="value-col" label="" flex="0" ignoreincolumnpicker="true" hidden="true" />
                        <treecol id="valgrp-col" label="" flex="0" ignoreincolumnpicker="true" hidden="true" />
                    </treecols>
                    <template>
                        <treechildren>
                            <treeitem uri="rdf:*">
                                <treerow>
                                    <treecell label="rdf:http://jelix.org/ns/rights#label"/>
                                    <treecell label="rdf:http://jelix.org/ns/rights#id_aclres"/>
                                    <treecell label="rdf:http://jelix.org/ns/rights#value_label"/>
                                    <treecell label="rdf:http://jelix.org/ns/rights#value"/>
                                    <treecell label="rdf:http://jelix.org/ns/rights#id_aclvalgrp"/>
                                </treerow>
                            </treeitem>
                        </treechildren>
                    </template>
                </tree>
                <vbox id="rightsedit"> <!--  collapsed="true" -->

                    <jx:submission id="rightsform" action="jsonrpc.php5" method="POST"
                                   format="json-rpc" rpcmethod="acl~"
                                   onsubmit=""
                                   onresult=""
                                   onhttperror="alert('erreur http :' + event.errorCode)"
                                   oninvalidate="alert('erreur de saisie')"
                                   onrpcerror="alert(this.jsonResponse.error.toSource())"
                                   onerror="alert(this.httpreq.responseText);"
                                   />
                    <deck id="rightsforms">
                     <description>modifier un droit</description>
                    {assign $valgrp=0}
                    {foreach $valuegroups as $i=>$vg}
                        {if $valgrp != $vg->id_aclvalgrp}
                           {if $valgrp !=0}
                                <jx:submit id="rightdata{$valgrp}" form="rightsform" label="Sauvegarder"/>
                                </groupbox>
                           {/if}
                           <groupbox submit="rightdata{$vg->id_aclvalgrp}" valgrp="{$vg->id_aclvalgrp}">
                             {assign $label=$vg->group_label_key}
                             <caption label="{@$label@}"/>
                           {assign $valgrp=$vg->id_aclvalgrp}
                        {/if}

                        {assign $label=$vg->label_key}
                        <checkbox label="{@$label@}" rightvalue="{$vg->value}" />
                    {/foreach}
                    {if $valgrp !=0}
                         <jx:submit id="rightdata{$valgrp}" form="rightsform" label="Sauvegarder"/>
                    </groupbox>
                    {/if}


                    </deck>
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