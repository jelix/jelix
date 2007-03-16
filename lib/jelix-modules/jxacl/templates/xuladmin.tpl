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
{meta_xul css 'jelix/xul/jxbl.css'}
{meta_xul csstheme 'jxacl.css'}
{meta_xul ns array('jx'=>'jxbl')}
{meta_xul csstheme 'xulpage.css'}

<script type="application/x-javascript"><![CDATA[
{literal}
    var gGroupList;

    function init(ev){
        gGroupList = document.getElementById('groupid');
        disableAll();
    }
    window.addEventListener("load", init, false);


    function disableAll(){
        document.getElementById('rights').setAttribute("datasources","");
        document.getElementById('users').setAttribute("datasources","");
        document.getElementById('groupstatus').setAttribute('disabled','true');
        var pager = document.getElementById('userspager');
        pager.setAttribute('counturl','');
        pager.setAttribute('datasourceurl','');
        pager.loadCount();
        document.getElementById('newname').setAttribute('value','');
    }

    function changeGroup( idgroup ){
        if( idgroup!= ''){
{/literal}
            var righturl={urljsstring 'jxacl~admin_rightslist@rdf',array(),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
{literal}
            document.getElementById('rights').setAttribute("datasources","");
            document.getElementById('rights').setAttribute("datasources",righturl);
            document.getElementById('groupstatus').removeAttribute('disabled');
            document.getElementById('newname').setAttribute('value','');
            refreshUserList(idgroup);
        }else{
            disableAll();
        }
        document.getElementById('groupname').setAttribute('value',gGroupList.selectedItem.label);
    }

    function reloadRights(){
        document.getElementById('rights').setAttribute("datasources","");
        changeGroup(document.getElementById('groupid').selectedItem.value);
    }

    function removeUserFromGrp () {
        if (confirm('Etes vous sûr de vouloir supprimer cet Utilisateur ?')) {
            var tree = document.getElementById('users');
            var idx = tree.view.selection.currentIndex;
            var myuser = tree.view.getCellText(idx, tree.columns.getNamedColumn ( "logins-col"));
            document.getElementById('userdel').setAttribute('value', myuser);
            var frm = document.getElementById('removeuserfromgrp');
            frm.submit();
        }
    }


    function onSubjectSelect(tree){
    }

    var gModifiedRights = [];
    
    function onRightChange(ev, tree){
    
        var row = {}, col = {}, childElt = {};
        
        tree.treeBoxObject.getCellAt(ev.clientX, ev.clientY, row,  col, childElt);
    
        row = row.value;
        col = col.value;
        childElt = childElt.value;
        if(col == null || row == null || childElt == null) return;

        if(col.type == Components.interfaces.nsITreeColumn.TYPE_CHECKBOX) {
            if(tree.view.getLevel(row) == 1){
                var resCol = tree.view.getCellText(row,tree.treeBoxObject.columns.getNamedColumn ("res-col"));
                var valueCol = tree.view.getCellText(row,tree.treeBoxObject.columns.getNamedColumn ("value-col"));
                var idAclValGrpCol = tree.view.getCellText(row,tree.treeBoxObject.columns.getNamedColumn ("id_aclvalgrp-col"));
                var idAclSbjCol = tree.view.getCellText(row,tree.treeBoxObject.columns.getNamedColumn ("id_aclsbj-col"));
                var etat = tree.view.getCellValue(row, col);
                for(var i=0; i < gModifiedRights.length; i++){
                    var e = gModifiedRights[i];
                    if(e.res == resCol
                    && e.right == valueCol
                    && e.subject == idAclSbjCol){
                            gModifiedRights.splice(i,1);
                            return;
                    }
                }
                gModifiedRights.push({ res: resCol, right: valueCol, subject: idAclSbjCol, newvalue: etat });
            }
        }
    }

    function onRightsFormSubmit(form){
        form.formDatas.rightvalues=gModifiedRights;
        return true;
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
        changeGroup('');
    }

    function onRenameGroup(form){
        gGroupList.selectedItem.label=form.formDatas.newname;
        gGroupList.setAttribute('label',form.formDatas.newname);
    }

    function onAddUserGroup(){
        document.getElementById('user').value='';
        refreshUserList(gGroupList.selectedItem.value);
    }
    
    function onRemoveUserGroup(){
        refreshUserList(gGroupList.selectedItem.value);
    }

    function refreshUserList(idgroup){
{/literal}
        var counturl={urljsstring 'jxacl~admin_usersgcount@classic',array(),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
        var usersurl={urljsstring 'jxacl~admin_userslist@rdf',array('offset'=>'__OFFSET__','count'=>'__COUNT__'),array('grpid'=>'idgroup','__rnd'=>'Math.random()')};
{literal}
        var pager = document.getElementById('userspager');
        pager.setAttribute('counturl',counturl);
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
        onresult="gModifiedRights = []"
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
        onresult="onAddUserGroup()"
        onhttperror="alert('erreur http :' + event.errorCode)"
        oninvalidate="alert('Saisissez correctement le nouveau nom')"
        onrpcerror="alert(this.jsonResponse.error.toSource())"
        onerror="alert(this.httpreq.responseText);"/>

<jx:submission id="removeuserfromgrp" 
        action="{jurl '@jsonrpc'}" 
        method="POST"
        format="json-rpc" 
        rpcmethod="jxacl~admin_removeuserfromgrp"
        onsubmit=""
        onresult="onRemoveUserGroup()"
        onhttperror="alert('erreur http :' + event.errorCode)"
        oninvalidate="alert('Utilisateur Incorrect')"
        onrpcerror="alert(this.jsonResponse.error.toSource())"
        onerror="alert(this.httpreq.responseText);"/>


<description class="title-page">Gestion des droits</description>
<hbox flex="1" align="stretch" >
    <vbox class="content-cols" style="width: 200px;">
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
    </vbox>

    <vbox class="content-cols" flex="1">
        <tabbox flex="1">
            <tabs>
                <tab label="Droits" />
                <tab label="Utilisateurs" />
            </tabs>
        <tabpanels flex="1">
            <tabpanel orient="vertical">
                <tree id="rights" ref="urn:data:row" flex="1" width="500" datasources="rdf:null"
                    onclick="onRightChange(event,this)" seltype="single" editable="true">
                    <treecols>
                        <treecol id="rights-col" label="Droits" primary="true" flex="2"
                                class="sortDirectionIndicator" sortActive="false"
                                sortDirection="ascending"
                                sort="rdf:http://jelix.org/ns/rights#label"/>

                        <splitter class="tree-splitter"/>
                        
                        <treecol id="enable-col" label="Actif" width="50"
                                 type="checkbox" editable="true" />
                        
                        <splitter class="tree-splitter"/>
                        
                        <treecol id="res-col" label="Ressources" flex="1"
                                 class="sortDirectionIndicator" sortActive="true" sortDirection="ascending"
                                 sort="rdf:http://jelix.org/ns/rights#id_aclres"/>
                        
                        <treecol id="value-col" label="" flex="0" ignoreincolumnpicker="true" hidden="true" />
                        
                        <treecol id="id_aclvalgrp-col" label="" flex="0" ignoreincolumnpicker="true" hidden="true" />
                        
                        <treecol id="id_aclsbj-col" label="" flex="0" ignoreincolumnpicker="true" hidden="true" />
                        
                    </treecols>
                    <template>
                        <rule iscontainer="true">
                            <treechildren>
                                <treeitem uri="rdf:*">
                                    <treerow properties="subject">
                                        <treecell label="rdf:http://jelix.org/ns/rights#label"/>
                                    </treerow>
                                </treeitem>
                            </treechildren>
                        </rule>
                        <rule iscontainer="false">
                            <treechildren>
                                <treeitem uri="rdf:*">
                                    <treerow properties="right">
                                        <treecell label="rdf:http://jelix.org/ns/rights#label"/>
                                        <treecell value="rdf:http://jelix.org/ns/rights#enabled"/>
                                        <treecell label="rdf:http://jelix.org/ns/rights#id_aclres"/>
                                        <treecell label="rdf:http://jelix.org/ns/rights#value"/>
                                        <treecell label="rdf:http://jelix.org/ns/rights#id_aclvalgrp"/>
                                        <treecell label="rdf:http://jelix.org/ns/rights#id_aclsbj"/>
                                    </treerow>
                                </treeitem>
                            </treechildren>
                        </rule>
                    </template>
                </tree>
                <hbox><jx:submit form="rightsform" label="Enregistrer" observes="groupstatus"/></hbox>
            </tabpanel>
            <tabpanel orient="vertical">
                <popupset>
                    <popup id="addUserMenu">
                        <menuitem label="Ajouter un Utilisateur" oncommand="document.getElementById('user').focus();"/>
                        <menuitem label="Supprimer cet Utilisateur" oncommand="removeUserFromGrp();"/>
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
                    <hbox>
                        <textbox id="user" name="user" value="" required="true" form="addusertogrpform" observes="groupstatus"/>
                        <jx:submit id="addusersubmit" form="addusertogrpform" label="Ajouter" observes="groupstatus"/>
                    </hbox>
	            </groupbox>
                <html:input type="hidden" id="userdel" name="userdel"  required="true" form="removeuserfromgrp" />
            </tabpanel>
        </tabpanels>
    </tabbox>
   </vbox>
</hbox>