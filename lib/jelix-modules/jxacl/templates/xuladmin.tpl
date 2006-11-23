<!--
* @package     jelix-modules
* @subpackage  jxacl
* @author      Laurent Jouanneau
* @contributor Nicolas Jeudy
* @copyright   2006 Laurent Jouanneau
* @copyright   2006 Nicolas Jeudy
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
-->

{meta_xul css 'chrome://global/skin/'}
{meta_xul css 'jelix/xul/jxulform.css'}
{meta_xul css 'jelix/design/xulpage.css'}
{meta_xul css 'jelix/xul/jxbl.css'}
{meta_xul ns array('jx'=>'jxbl')}

<script type="application/x-javascript"><![CDATA[

  {literal}
    var gCurrentRight = {};
    var gGroupList;

    function init(ev){
        gGroupList = document.getElementById('groupid');
        disableAll();
    }
    window.addEventListener("load", init, false);


    function disableAll(){
        document.getElementById('rights').setAttribute("datasources","");
        document.getElementById('users').setAttribute("datasources","");
        document.getElementById('rightsvalues').selectedIndex=0;
        document.getElementById('groupstatus').setAttribute('disabled','true');
        var pager = document.getElementById('userspager');
        pager.setAttribute('counturl','');
        pager.setAttribute('datasourceurl','');
        pager.loadCount();
    }

    function selectRightForm(idvalgrp, rightvalue){
        var deck=document.getElementById('rightsvalues');
        deck.selectedIndex=0;
        if(idvalgrp != '0'){
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

    function changeGroup( idgroup ){
        if( idgroup!= ''){
            {/literal}
            var righturl={urljsstring 'jxacl~admin_rightslist@rdf',array(),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
            var usersurl={urljsstring 'jxacl~admin_userslist@rdf',array('offset'=>'__OFFSET__','count'=>'__COUNT__'),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
            var counturl={urljsstring 'jxacl~admin_usersgcount@classic',array(),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
            {literal}
            document.getElementById('rights').setAttribute("datasources","");
            document.getElementById('rights').setAttribute("datasources",righturl);
            document.getElementById('groupstatus').removeAttribute('disabled');
            var pager = document.getElementById('userspager');
            pager.setAttribute('counturl',counturl);
            pager.setAttribute('datasourceurl',usersurl);
            pager.loadCount();
        }else{
            disableAll();
        }
        document.getElementById('groupname').setAttribute('value',gGroupList.selectedItem.label);
        selectRightForm("0",0);
        gCurrentRight = {};
    }

    function reloadRights(){
        document.getElementById('rights').setAttribute("datasources","");
        changeGroup(document.getElementById('groupid').selectedItem.value);
    }

    function removeUserFromGrp (tree) {
        if (confirm('Etes vous sûr de vouloir supprimer cet Utilisateur ?')) {
            var idx = tree.view.selection.currentIndex;
            myuser = tree.view.getCellText(idx, tree.columns.getNamedColumn ( "logins-col"));
            document.getElementById('deluser').setAttribute('value',myuser);
            document.getElementById('removeuserfromgrp').submit();
        }
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



function onAddUserGroup(form,idgroup){
	document.getElementById('user').value='';
	document.getElementById('user2').value='';
	{/literal}
	var usersurl={urljsstring 'jxacl~admin_userslist@rdf',array('offset'=>'0','count'=>'10'),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
	var counturl={urljsstring 'jxacl~admin_usersgcount@classic',array(),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
	{literal}
	var pager = document.getElementById('userspager');
   pager.setAttribute('counturl',counturl);
   pager.setAttribute('datasourceurl','');
	pager.setAttribute('datasourceurl',usersurl);
	pager.loadCount();
}

function onRemoveUserGroup(form,idgroup){
	document.getElementById('user').value='';
	{/literal}
	var usersurl={urljsstring 'jxacl~admin_userslist@rdf',array('offset'=>'0','count'=>'10'),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
	var counturl={urljsstring 'jxacl~admin_usersgcount@classic',array(),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
	{literal}
	var pager = document.getElementById('userspager');
   pager.setAttribute('counturl',counturl);
   pager.setAttribute('datasourceurl','');
	pager.setAttribute('datasourceurl',usersurl);
	pager.loadCount();
}
  {/literal}
]]></script>

<broadcasterset>
   <broadcaster id="groupname" label="--" value="--"/>
    <broadcaster id="groupstatus" disabled="true"/>
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
<jx:submission id="deleteform" action="{jurl '@jsonrpc'}" method="POST"
        format="json-rpc" rpcmethod="jxacl~admin_deletegrp"
        onsubmit="return confirm('Etes vous sûr de vouloir supprimer ce groupe ?')"
        onresult="onDeleteGroup(this)"
        onhttperror="alert('erreur http :' + event.errorCode)"
        oninvalidate="alert('erreur de saisie')"
        onrpcerror="alert(this.jsonResponse.error.toSource())"
        onerror="alert(this.httpreq.responseText);"
        />
<jx:submission id="renameform" action="{jurl '@jsonrpc'}" method="POST"
        format="json-rpc" rpcmethod="jxacl~admin_renamegrp"
        onsubmit=""
        onresult="onRenameGroup(this)"
        onhttperror="alert('erreur http :' + event.errorCode)"
        oninvalidate="alert('Saisissez correctement le nouveau nom')"
        onrpcerror="alert(this.jsonResponse.error.toSource())"
        onerror="alert(this.httpreq.responseText);"
        />
<jx:submission id="rightsform" action="{jurl '@jsonrpc'}" method="POST"
        format="json-rpc" rpcmethod="jxacl~admin_saveright"
        onsubmit="onRightsFormSubmit(this)"
        onresult="reloadRights()"
        onhttperror="alert('erreur http :' + event.errorCode)"
        oninvalidate="alert('erreur de saisie')"
        onrpcerror="alert('rpcerror:\n'+this.jsonResponse.error.toSource())"
        onerror="alert('error:\n'+this.httpreq.responseText);"
        />
<jx:submission id="addusertogrpform" 
        action="{jurl '@jsonrpc'}" 
        method="POST"
        format="json-rpc" 
        rpcmethod="jxacl~admin_addusertogrp"
        onsubmit=""
        onresult="onAddUserGroup(this,document.getElementById('groupid').selectedItem.value)"
        onhttperror="alert('erreur http :' + event.errorCode)"
        oninvalidate="alert('Saisissez correctement le nouveau nom')"
        onrpcerror="alert(this.jsonResponse.error.toSource())"
        onerror="alert(this.httpreq.responseText);"/>

<description class="title-page">Gestion des droits</description>
<hbox flex="1" align="stretch" >
    <vbox class="content-cols" style="width: 200px;">
        <jx:jbox title="Traitement des Groupes:">
            <label control="groupid" value="Séléctionner un groupe :"/>
            <menulist id="groupid" name="groupid" required="true"
                    form="renameform,deleteform,rightsform,addusertogrp,removeuserfromgrp,addusertogrpform"
                    oncommand="changeGroup(this.selectedItem.value)">
                <menupopup>
                    <menuitem label="--" value="" />
                    {foreach $groups as $grp}
                    <menuitem label="{$grp->name|escxml}" value="{$grp->id_aclgrp}"/>
                    {/foreach}
                </menupopup>
            </menulist>
    
            <groupbox submit="newgrpsubmit">
                <label control="newgroup" value="Ajouter un groupe :"/>
                <textbox id="newgroup" name="groupname" value="" required="true" form="newgrpform" />
                <jx:submit id="newgrpsubmit" form="newgrpform" label="Créer"/>
            </groupbox>

            <groupbox submit="deletesubmit">
                <caption label="Suppression du groupe"/>
                <label observes="groupname"/>
                <jx:submit id="deletesubmit" form="deleteform" label="Supprimer" observes="groupstatus"/>
            </groupbox>

            <groupbox submit="renamesubmit">
                <caption label="Renommage du groupe"/><label observes="groupname"/>
                <hbox align="center" pack="start"><label control="newname" value="Nouveau nom:"/>
                <textbox id="newname" name="newname" value="" required="true" form="renameform" observes="groupstatus" />
                <jx:submit id="renamesubmit" form="renameform" label="Renommer" observes="groupstatus"/></hbox>
            </groupbox>

        </jx:jbox>
        <jx:jbox title="Droits associés">
            <deck id="rightsvalues">
                <description></description>
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

        </jx:jbox>
    </vbox>


    <vbox class="content-cols" flex="1">
        <tabbox flex="1">
            <tabs>
                <tab label="Droits" />
                <tab label="Utilisateurs" />
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
                        <treechildren alternatingbackground="true">
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
            </tabpanel>
            <tabpanel orient="vertical">
                <popupset>
                    <popup id="addUserMenu">
                            <menuitem label="Ajouter un Utilisateur" onclick="document.getElementById('user').focus();"/>
                            <menuitem label="Supprimer cet Utilisateur" onclick="removeUserFromGrp(document.getElementById('users'));"/>
                    </popup>
                </popupset>

                <jx:templatepager id="userspager" target="users" increment="200"
                                  datasourceurl="" counturl="" />
                <tree id="users" flex="1" flags="dont-build-content" ref="urn:data:row" datasources="rdf:null"
                    onselect="" seltype="single" context="addUserMenu">
                    <treecols>
                        <treecol id="logins-col" label="Logins" primary="true" flex="1"
                                class="sortDirectionIndicator" sortActive="false"
                                sortDirection="ascending"
                                sort="rdf:http://jelix.org/ns/usersgroup#login"/>
                    </treecols>
                    <template>
                        <treechildren  alternatingbackground="true">
                            <treeitem uri="rdf:*">
                                <treerow>
                                    <treecell label="rdf:http://jelix.org/ns/usersgroup#login"/>
                                </treerow>
                            </treeitem>
                        </treechildren>
                    </template>
                </tree>
                <groupbox submit="addusersubmit">
                    <caption label="Ajouter un Utilisateur :"/>
                    <textbox id="user" name="user" value="" required="true" form="addusertogrpform" observes="groupstatus"/>
                    <jx:submit id="addusersubmit" form="addusertogrpform" label="Ajouter" observes="groupstatus"/>
	       </groupbox>

            </tabpanel>
        </tabpanels>
    </tabbox>
   </vbox>
</hbox>

<!--
<jx:modalboxes>

    <jx:modalbox id="jxaclModalBox2" pack="center" orient="vertical" align="center" hidden="true">
        <box class="notifyBox" orient="horizontal" align="stretch" pack="start" njFormBoxParent="jxaclNotifyBox1">
                    <jx:submission id="removeuserfromgrp" 
                                action="{jurl '@jsonrpc'}" 
                                method="POST"
                        format="json-rpc" 
                        rpcmethod="jxacl~admin_removeuserfromgrp"
                        onsubmit="return confirm('Etes vous sÃ»r de vouloir supprimer cet Utilisateur ?')"
                        onresult="onRemoveUserGroup(this,document.getElementById('groupid').selectedItem.value)"
                        onhttperror="alert('erreur http :' + event.errorCode)"
                        oninvalidate="alert('Utilisateur Incorrect')"
                        onrpcerror="alert(this.jsonResponse.error.toSource())"
                        onerror="alert(this.httpreq.responseText);"/>
                    <textbox id="deluser" name="deluser" value="" required="true" form="removeuserfromgrp"/>
        <jx:submit id="removeusersubmit" form="removeuserfromgrp" label="Supprimer" observes="groupstatus"/>
            </box>
    </box>

</jx:modalboxes>
-->