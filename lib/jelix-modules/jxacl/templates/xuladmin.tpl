{meta_xul css 'chrome://global/skin/'}
{meta_xul css '/jelix/xul/jxulform.css'}
{meta_xul css '/jelix/design/xulpage.css'}
{meta_xul css '/jelix/xul/jxbl.css'}
{meta_xul ns array('jx'=>'jxbl')}

<script type="application/x-javascript"><![CDATA[

  {literal}
    var gCurrentRight = {};
    var gGroupList;

    function init(ev){
        disableAll();
        gGroupList = document.getElementById('groupid');
    }

    window.addEventListener("load", init, false);

    function changeGroup( idgroup ){
        if( idgroup!= ''){
            {/literal}
            var righturl={urljsstring 'jxacl~admin_rightslist@rdf',array(),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
            var usersurl={urljsstring 'jxacl~admin_userslist@rdf',array('offset'=>'__OFFSET__','count'=>'__COUNT__'),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
            var counturl={urljsstring 'jxacl~admin_usersgcount@classic',array(),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
            {literal}
            document.getElementById('rights').setAttribute("datasources","");
            document.getElementById('rights').setAttribute("datasources",righturl);
            document.getElementById('rightsedit').collapsed=false;
            document.getElementById('groupstatus').removeAttribute('disabled');
            var pager = document.getElementById('userspager');
            pager.setAttribute('counturl',counturl);
            pager.setAttribute('datasourceurl',usersurl);
            pager.loadCount();
        }else{
            disableAll();
        }
    }

    function reloadRights(){
        document.getElementById('rights').setAttribute("datasources","");
        changeGroup(document.getElementById('groupid').selectedItem.value);
    }

    function disableAll(){
        document.getElementById('rights').setAttribute("datasources","");
        document.getElementById('rightsedit').collapsed=true;
        document.getElementById('users').setAttribute("datasources","");
        document.getElementById("rightsforms").selectedIndex=0;
        document.getElementById('groupstatus').setAttribute('disabled','true');
        var pager = document.getElementById('userspager');
        pager.setAttribute('counturl','');
        pager.setAttribute('datasourceurl','');
        pager.loadCount();
    }


    function onSubjectSelect(tree){
        var idx = tree.view.selection.currentIndex;
        if(idx == -1){
            selectRightForm("0",0);
            gCurrentRight = {};
        }else{

            gCurrentRight.rightvalue= tree.view.getCellText(idx, tree.columns.getNamedColumn ( "value-col"));
            gCurrentRight.id_aclvalgrp =  tree.view.getCellText(idx, tree.columns.getNamedColumn ( "id_aclvalgrp-col"));
            gCurrentRight.id_aclsbj =  tree.view.getCellText(idx, tree.columns.getNamedColumn ( "id_aclsbj-col"));
            gCurrentRight.id_aclres =  tree.view.getCellText(idx, tree.columns.getNamedColumn ( "res-col"));

            selectRightForm(gCurrentRight.id_aclvalgrp, gCurrentRight.rightvalue);
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

    function onRightsFormSubmit(form){
        var deck=document.getElementById("rightsforms");
        if(deck.selectedIndex!=0){
            var chks = deck.selectedPanel.getElementsByTagName("checkbox");
            var chkvalue, value=0;
            for(var j=0; j < chks.length; j++){
                if(chks[j].checked){
                    value = value | parseInt(chks[j].getAttribute('rightvalue'));
                }
            }
            form.formDatas.rightvalue=value;
            form.formDatas.subject= gCurrentRight.id_aclsbj;
            form.formDatas.ressource= gCurrentRight.id_aclres;
            return true;
        }else{
            return false;
        }

    }


    function onCreateNewGroup(form){
        gGroupList.selectedItem=gGroupList.appendItem(form.formDatas.groupname, form.jsonResponse.result.id);
        changeGroup(form.jsonResponse.result.id);
        document.getElementById('newgroup').value='';
    }

    function onDeleteGroup(form){
        var i=gGroupList.selectedIndex;
        gGroupList.selectedIndex=0;
        gGroupList.removeItemAt(i);
        disableAll();
    }

    function onRenameGroup(form){
        gGroupList.selectedItem.label=form.formDatas.newname;
        gGroupList.setAttribute('label',form.formDatas.newname);
    }



  {/literal}
]]></script>

<broadcasterset>
    <broadcaster id="groupstatus" />
</broadcasterset>


<jx:submission id="newgrpform" action="{jurl '@jsonrpc'}" method="POST"
               format="json-rpc" rpcmethod="jxacl~admin_newgrp"
               onsubmit=""
               onresult="onCreateNewGroup(this)"
               onhttperror="alert('erreur http :' + event.errorCode)"
               oninvalidate="alert('Saisissez correctement le nom du nouveau groupe')"
               onrpcerror="alert(this.jsonResponse.error.toSource())"
               onerror="alert(this.httpreq.responseText);"
               />

<description class="title-page">Gestion des droits</description>
<hbox align="baseline">
    <label control="groupid" value="Groupe :"/>
    <menulist id="groupid" name="groupid" form="renameform,deleteform,rightsform" required="true"
              oncommand="changeGroup(this.selectedItem.value)">
        <menupopup>
            <menuitem label="--" value="" />
            {foreach $groups as $grp}
            <menuitem label="{$grp->name|escxml}" value="{$grp->id_aclgrp}"/>
            {/foreach}
        </menupopup>
    </menulist>
    <spacer flex="1"/>

    <label control="newgroup" value="Nouveau groupe :"/>
    <textbox id="newgroup" name="groupname" value="" required="true" form="newgrpform" />
    <jx:submit id="newgrpsubmit" form="newgrpform" label="Créer"/>
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
                        <treecol id="id_aclvalgrp-col" label="" flex="0" ignoreincolumnpicker="true" hidden="true" />
                        <treecol id="id_aclsbj-col" label="" flex="0" ignoreincolumnpicker="true" hidden="true" />
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
                                    <treecell label="rdf:http://jelix.org/ns/rights#id_aclsbj"/>
                                </treerow>
                            </treeitem>
                        </treechildren>
                    </template>
                </tree>
                <vbox id="rightsedit"> <!--  collapsed="true" -->

                    <jx:submission id="rightsform" action="{jurl '@jsonrpc'}" method="POST"
                                   format="json-rpc" rpcmethod="jxacl~admin_saveright"
                                   onsubmit="onRightsFormSubmit(this)"
                                   onresult="reloadRights()"
                                   onhttperror="alert('erreur http :' + event.errorCode)"
                                   oninvalidate="alert('erreur de saisie')"
                                   onrpcerror="alert('rpcerror:\n'+this.jsonResponse.error.toSource())"
                                   onerror="alert('error:\n'+this.httpreq.responseText);"
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
            <tabpanel orient="vertical">
                <jx:templatepager id="userspager" target="users" increment="200"
                                  datasourceurl="" counturl="" />
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
                    <jx:submission id="renameform" action="{jurl '@jsonrpc'}" method="POST"
                                   format="json-rpc" rpcmethod="jxacl~admin_renamegrp"
                                    onsubmit=""
                                    onresult="onRenameGroup(this)"
                                    onhttperror="alert('erreur http :' + event.errorCode)"
                                    oninvalidate="alert('Saisissez correctement le nouveau nom')"
                                    onrpcerror="alert(this.jsonResponse.error.toSource())"
                                    onerror="alert(this.httpreq.responseText);"
                                    />
                    <label control="newname" value="Nouveau nom"/>
                    <textbox id="newname" name="newname" value="" required="true" form="renameform" observes="groupstatus" />
                    <jx:submit id="renamesubmit" form="renameform" label="Renommer" observes="groupstatus"/>
                </groupbox>
                <groupbox submit="deletesubmit">
                    <caption label="Suppression du groupe"/>
                    <jx:submission id="deleteform" action="{jurl '@jsonrpc'}" method="POST"
                                   format="json-rpc" rpcmethod="jxacl~admin_deletegrp"
                                    onsubmit="return confirm('Etes vous sûr de vouloir supprimer ce groupe ?')"
                                    onresult="onDeleteGroup(this)"
                                    onhttperror="alert('erreur http :' + event.errorCode)"
                                    oninvalidate="alert('erreur de saisie')"
                                    onrpcerror="alert(this.jsonResponse.error.toSource())"
                                    onerror="alert(this.httpreq.responseText);"
                                    />
                    <jx:submit id="deletesubmit" form="deleteform" label="Supprimer" observes="groupstatus"/>
                </groupbox>

            </tabpanel>
        </tabpanels>
    </tabbox>

</hbox>