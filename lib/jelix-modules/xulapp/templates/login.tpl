{meta_xul css 'chrome://global/skin/'}
{meta_xul css '/jelix/xul/jxulform.css'}
{meta_xul ns array('jxf'=>'jxulform')}

<script type="application/x-javascript" src="/xulapp/login.js" />

<jxf:submission id="loginform" action="jsonrpc.php5" method="POST"
        format="json-rpc"
        onsubmit=""
        rpcmethod="auth~login_in"
        onresult="onResult(this)"
        onhttperror="alert('erreur http :' + event.errorCode)"
        oninvalidate="alert('Saisissez un login et mot de passe')"
        onrpcerror="alert(this.jsonResponse.error.toSource())"
        onerror="alert(this.httpreq.responseText);"
        />


<vbox flex="1" pack="center" align="center" submit="ident">
    <grid>
        <columns>
            <column/>
            <column flex="1"/>
        </columns>

        <rows>
            <row>
                <label control="login" value="Login"  style="text-align:right;"/>
                <textbox id="login" name="login" flex="1" form="loginform" required="true"/>
            </row>
            <row>
                <label control="passwd" value="Mot de passe"  style="text-align:right;"/>
                <textbox id="passwd" name="password"  form="loginform" type="password"  flex="1"  required="true"/>
            </row>
        </rows>
    </grid>
    <jxf:submit id="ident" form="loginform" label="Identification"/>
</vbox>