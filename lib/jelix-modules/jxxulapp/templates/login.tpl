{meta_xul css 'chrome://global/skin/'}
{meta_xul css 'jelix/xul/jxulform.css'}
{meta_xul ns array('jx'=>'jxbl')}
{meta_xul js 'jelix/js/json.js'}

<script type="application/x-javascript"><![CDATA[

function onLoginResult(form){ldelim}
    if(form.jsonResponse.result == 'OK')
        window.location.href='{jurl 'jxxulapp~default_index',array(),false}';
    else
        alert('login ou mot de passe érroné');

{literal}
}

function onTheLoad(){
    document.getElementById('login').focus();
}


window.addEventListener('load', onTheLoad ,false);
{/literal}
]]></script>

<jx:submission id="loginform" action="{jurl '@jsonrpc'}" method="POST"
        format="json-rpc" rpcmethod="jxauth~login_in"
        onsubmit=""
        oninvalidate="alert('Saisissez un login et mot de passe')"

        onresult="onLoginResult(this)"
        onrpcerror="alert('RPC error\ncode='+this.jsonResponse.error.code + '\nmessage='+this.jsonResponse.error.string)"
        onhttperror="alert('HTTP Error\nstatus=' + this.httpreq.status + '\nresponse='+ this.httpreq.responseText)"
        onerror="alert('internal error, code ='+event.errorCode+'\nresponse='+ this.httpreq.responseText)"
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
    <jx:submit id="ident" form="loginform" label="Identification"/>
</vbox>