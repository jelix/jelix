{meta_xul css 'chrome://global/skin/'}
{meta_xul css '/jelix/xul/jxulform.css'}
{meta_xul css '/jelix/design/xulpage.css'}
{meta_xul css '/jelix/xul/jxbl.css'}
{meta_xul ns array('jxf'=>'jxulform', 'jx'=>'http://jelix.org/ns/xbl/1.0')}

<script type="application/x-javascript"><![CDATA[

  var tree;
  var dsUrl =  '{jurl 'users~default_userslist@rdf',array(),false}';

  {literal}
  function init()
  {
    tree = document.getElementById("userslist");
  }

  window.addEventListener("load", init, false);

  function setUser(){
    if(tree.currentIndex <0)
       return;
    var loginTb = document.getElementById('login');
    var emailTb = document.getElementById('email');


    var col = tree.columns.getNamedColumn('login-col');
    loginTb.value = tree.view.getCellText(tree.currentIndex, col);
    col = tree.columns.getNamedColumn('email-col');
    emailTb.value = tree.view.getCellText(tree.currentIndex, col);
    resetPwdForm();
    document.getElementById("userpanel").removeAttribute("collapsed");


  }

  function verifPwd(){
    if(document.getElementById("pwd").value == document.getElementById("pwd2").value)
        return true;
    alert("les deux mots de passes ne sont pas identiques\nRecommencez");
    return false;
  }

  function resetPwdForm(){
    document.getElementById("pwd").value ='';
    document.getElementById("pwd2").value = '';
  }

  {/literal}
]]></script>
<description class="title-page">Gestion des utilisateurs</description>

<vbox flex="1">
    <jx:remotetreecriterion uri="{jurl 'users~default_userslist@rdf'}" tree="userslist" id="criteres">
        <menulist name="letter">
            <menupopup>
                <menuitem label="" value=""/>
                <menuitem label="A" value="a"/>
                <menuitem label="B" value="b"/>
                <menuitem label="C" value="c"/>
                <menuitem label="D" value="d"/>
                <menuitem label="E" value="e"/>
                <menuitem label="F" value="f" />
                <menuitem label="G" value="g" />
                <menuitem label="H" value="h" />
                <menuitem label="I" value="i" />
                <menuitem label="J" value="j" />
                <menuitem label="K" value="k" />
                <menuitem label="L" value="l" />
                <menuitem label="M" value="m" />
                <menuitem label="N" value="n" />
                <menuitem label="O" value="o" />
                <menuitem label="P" value="p" />
                <menuitem label="Q" value="q" />
                <menuitem label="R" value="r" />
                <menuitem label="S" value="s" />
                <menuitem label="T" value="t" />
                <menuitem label="U" value="u" />
                <menuitem label="V" value="v" />
                <menuitem label="W" value="w" />
                <menuitem label="X" value="x" />
                <menuitem label="Y" value="y" />
                <menuitem label="Z" value="z" />
                <menuitem label="0" value="0" />
                <menuitem label="1" value="1" />
                <menuitem label="2" value="2" />
                <menuitem label="3" value="3" />
                <menuitem label="4" value="4" />
                <menuitem label="5" value="5" />
                <menuitem label="6" value="6" />
                <menuitem label="7" value="7" />
                <menuitem label="8" value="8" />
                <menuitem label="9" value="9" />
            </menupopup>
        </menulist>
    </jx:remotetreecriterion>
    <hbox flex="1">
        <tree id="userslist" flex="1" flags="dont-build-content" ref="urn:data:row" datasources="rdf:null"
            onselect="setUser()" seltype="single"
            >
            <treecols>
                <treecol id="login-col" label="Login" primary="true" flex="1"
                        class="sortDirectionIndicator" sortActive="false"
                        sortDirection="ascending"
                        sort="rdf:http://jelix.org/ns/users#login"/>
                <splitter class="tree-splitter"/>
                <treecol id="email-col" label="Email" flex="1"
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
        <vbox id="userpanel" collapsed="true">
            <groupbox>
            <caption label="Détails sur l'utilisateur"/>
                <vbox submit="userdata">

                <jxf:submission id="userform" action="jsonrpc.php5" method="POST"
                                format="json-rpc" rpcmethod="users~default_saveuser"
                                onsubmit=""
                                onresult="document.getElementById('criteres').show()"
                                onhttperror="alert('erreur http :' + event.errorCode)"
                                oninvalidate="alert('Saisissez correctement le login et l\'email')"
                                onrpcerror="alert(this.jsonResponse.error.toSource())"
                                onerror="alert(this.httpreq.responseText);"
                />
                <grid>
                    <columns>
                        <column/>
                        <column flex="1"/>
                    </columns>
                    <rows>
                        <row>
                            <label control="login" value="Login"  style="text-align:right;"/>
                            <textbox id="login" name="login" flex="1" form="userform pwdform" required="true" readonly="true" />
                        </row>
                        <row>
                            <label control="email" value="Email"  style="text-align:right;"/>
                            <textbox id="email" name="email" flex="1" form="userform" required="true"/>
                        </row>
                    </rows>
                </grid>
                <jxf:submit id="userdata" form="userform" label="Sauvegarder"/>
                </vbox>
            </groupbox>
            <groupbox>
                <caption label="Changement du mot de passe"/>
                <vbox submit="userpwd">
                <jxf:submission id="pwdform" action="jsonrpc.php5" method="POST"
                                format="json-rpc" rpcmethod="users~default_newpwd"
                                onsubmit="return verifPwd()"
                                onresult="alert('mot de passe modifié');resetPwdForm();"
                                onhttperror="alert('erreur http :' + event.errorCode)"
                                oninvalidate="alert('Saisissez correctement le mot de passe')"
                                onrpcerror="alert(this.jsonResponse.error.toSource())"
                                onerror="alert(this.httpreq.responseText);"
                                />
                <grid>
                    <columns>
                        <column/>
                        <column flex="1"/>
                    </columns>
                    <rows>
                        <row>
                            <label control="pwd1" value="Nouveau"  style="text-align:right;"/>
                            <textbox id="pwd" name="pwd" flex="1" type="password" form="pwdform" required="true"/>
                        </row>
                        <row>
                            <label control="pwd2" value="Répétez"  style="text-align:right;"/>
                            <textbox id="pwd2" flex="1" type="password" required="true"/>
                        </row>
                    </rows>
                </grid>
                <jxf:submit id="userpwd" form="pwdform" label="Changer"/>

                </vbox>
            </groupbox>

        </vbox>
    </hbox>
</vbox>
