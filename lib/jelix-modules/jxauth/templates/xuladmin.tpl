{meta_xul css 'chrome://global/skin/'}
{meta_xul css 'jelix/xul/jxulform.css'}
{meta_xul css 'jelix/xul/jxbl.css'}
{meta_xul csstheme 'xulpage.css'}
{meta_xul ns array('jx'=>'jxbl')}

<script type="application/x-javascript"><![CDATA[

    var gUserDataModified = false;
    var gUserIndex = -1;
    var gModeEdit = 0;
    var gTree;
    var gLoginTb;
    var gEmailTb;
    var dsUrl =  '{jurl 'jxauth~admin_userslist@rdf',array(),false}';
    var gFormErrorMsg = "{@jxauth~auth.form.error@}"
    
{literal}
    function init(){
        gTree = document.getElementById("userslist");
        gLoginTb = document.getElementById('login');
        gEmailTb = document.getElementById('email');
        unSelectUser();
    }

    window.addEventListener("load", init, false);

    function resetUserForm(){
        gEmailTb.value='';
        gLoginTb.value = '';
        document.getElementById("pwd").value ='';
        document.getElementById("pwd2").value = '';
        document.getElementById('userdata-cancel').disabled=true;
        document.getElementById('userdata').disabled=true;
    }

    function unSelectUser(){
        resetUserForm()
        gUserIndex = -1;
        document.getElementById("pwd").readonly = true;
        document.getElementById("pwd2").readonly = true;
        gLoginTb.readonly = true;
        gEmailTb.readonly = true;
    }

    function modificationUserForm(){
        gUserDataModified = true;
        document.getElementById('userdata-cancel').disabled=false;
        document.getElementById('userdata').disabled=false;
    }
    
    function loadUserForm(){
        var col = gTree.columns.getNamedColumn('login-col');
        gLoginTb.value = gTree.view.getCellText(gTree.currentIndex, col);
        col = gTree.columns.getNamedColumn('email-col');
        gEmailTb.value = gTree.view.getCellText(gTree.currentIndex, col);
        document.getElementById('userdata-cancel').disabled=true;
        document.getElementById('userdata').disabled=true;
        gUserDataModified = false;
        document.getElementById("pwd").readonly = false;
        document.getElementById("pwd").removeAttribute('required');
        document.getElementById("pwd2").readonly = false;
        //gLoginTb.readonly = true;
        gEmailTb.readonly = false;
    }
    
    function onUserFormSaved(){
        document.getElementById('criteres').show(); 
        gUserDataModified=false;
        document.getElementById("pwd").value ='';
        document.getElementById("pwd2").value = '';
        document.getElementById('userdata-cancel').disabled=true;
        document.getElementById('userdata').disabled=true;
    }

    function changeUser(){
        if(gTree.currentIndex == gUserIndex) return;
        if(gTree.currentIndex <0){
            unSelectUser();
            return;
        }
        
        if(gUserDataModified){
            if(!confirm('Vous avez modifié les données de '+gLoginTb.value+'\nVoulez-vous abandonner cette modification ?')){
                gTree.currentIndex = gUserIndex; //on remet l'ancien user
                return;
            }
            
        }
        loadUserForm();
        document.getElementById("pwd").value ='';
        document.getElementById("pwd2").value = '';
        document.getElementById("pwd").readonly = false;
        document.getElementById("pwd2").readonly = false;
        gUserIndex = gTree.currentIndex;
        
    }
    
    function verifPwd(){
        if(document.getElementById("pwd").value == document.getElementById("pwd2").value)
            return true;
        alert("les deux mots de passes ne sont pas identiques\nRecommencez");
        return false;
    }

{/literal}
]]></script>
<description class="title-page">{@jxauth~auth.page.title@}</description>

<jx:submission id="userform" action="{jurl '@jsonrpc'}" method="POST"
    format="json-rpc" rpcmethod="jxauth~admin_saveuser"
    onsubmit="return verifPwd()"
    onresult="onUserFormSaved()"
    onhttperror="alert('http error :' + event.errorCode)"
    oninvalidate="alert(gFormErrorMsg)"
    onrpcerror="alert(this.jsonResponse.error.toSource())"
    onerror="alert(this.httpreq.responseText);"
/>

<vbox flex="1">
    <jx:templatecriterion uri="{jurl 'jxauth~admin_userslist@rdf'}" target="userslist" id="criteres">
        <label control="letter" value="{@jxauth~auth.search.label@}"/>
        <textbox id="letter" name="letter" />
    </jx:templatecriterion>
    <hbox flex="1">
        <tree id="userslist" flex="1" flags="dont-build-content" ref="urn:data:row" datasources="rdf:null"
            onselect="changeUser()" seltype="single"
            >
            <treecols>
                <treecol id="login-col" label="{@jxauth~auth.userlist.logincol@}" primary="true" flex="1"
                        class="sortDirectionIndicator" sortActive="false"
                        sortDirection="ascending"
                        sort="rdf:http://jelix.org/ns/users#login"/>
                <splitter class="tree-splitter"/>
                <treecol id="email-col" label="{@jxauth~auth.userlist.emailcol@}" flex="1"
                        class="sortDirectionIndicator" sortActive="true"
                        sortDirection="ascending"
                        sort="rdf:http://jelix.org/ns/users#email"/>
            </treecols>
            <template>
                <treechildren>
                    <treeitem uri="rdf:*">
                        <treerow>
                            <treecell label="rdf:http://jelix.org/ns/users#login"/>
                            <treecell label="rdf:http://jelix.org/ns/users#email"/>
                        </treerow>
                    </treeitem>
                </treechildren>
            </template>
        </tree>
        <vbox id="userpanel" submit="userdata">
            <!--<hbox pack="right"><button id="createuser-btn" label="Nouvel Utilisateur" oncommand="prepareNewUser()" /></hbox>-->
            <groupbox>
                <caption label="{@jxauth~auth.userform.title@}"/>
                <grid>
                    <columns>
                        <column/>
                        <column flex="1"/>
                    </columns>
                    <rows id="userdata-rows" oninput="modificationUserForm()">
                        <row>
                            <label control="login" value="{@jxauth~auth.userform.login@}"  style="text-align:right;"/>
                            <textbox id="login" name="login" flex="1" form="userform pwdform" 
                                        required="true" readonly="true"/>
                        </row>
                        <row>
                            <label control="email" value="{@jxauth~auth.userform.email@}"  style="text-align:right;"/>
                            <textbox id="email" name="email" flex="1" form="userform" 
                                        required="true"/>
                        </row>
                    </rows>
                </grid>
            </groupbox>
            <groupbox>
                <caption label="{@jxauth~auth.userform.password@}"/>
                <grid>
                    <columns>
                        <column/>
                        <column flex="1"/>
                    </columns>
                    <rows  oninput="modificationUserForm()">
                        <row>
                            <label control="pwd1" value="{@jxauth~auth.userform.password.new@}"  style="text-align:right;"/>
                            <textbox id="pwd" name="pwd" flex="1" type="password" 
                                        form="userform" />
                        </row>
                        <row>
                            <label control="pwd2" value="{@jxauth~auth.userform.password.repeat@}"  style="text-align:right;"/>
                            <textbox id="pwd2" flex="1" type="password" />
                        </row>
                    </rows>
                </grid>
            </groupbox>
            <hbox><jx:submit id="userdata" form="userform" label="{@jxauth~auth.userform.save@}"/>
                    <button id="userdata-cancel" label="{@jxauth~auth.userform.cancel@}" oncommand="loadUserForm()" /></hbox>
        </vbox>
    </hbox>
</vbox>
