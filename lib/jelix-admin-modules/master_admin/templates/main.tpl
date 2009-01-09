<div id="header">
    <div id="top">{if $adminTitle}{$adminTitle|eschtml}{else}{@master_admin~gui.header.title@}{/if}</div>

    <div id="info-box">
        <div id="info-user">{@master_admin~gui.header.user@}
        {ifacl2 'auth.user.view'}
        <span id="info-user-login"><a href="{jurl 'jauthdb_admin~user:index', array('id'=>$user->login)}">{$user->login}</a></span>
        {else}
        <span id="info-user-login">{$user->login}</span>
        {/ifacl2}
        | <a href="{jurl 'jauth~login:out'}" id="info-user-logout">{@master_admin~gui.header.disconnect@}</a>
        </div>
    </div>
</div>
<div id="main">
    <div id="menu">
        {$MENU}
    </div>

    <div id="content">
        <div id="admin-message">{jmessage}</div>
        {if $MAIN}{$MAIN}{else}<p>{@master_admin~gui.main.nocontent@}</p>{/if}
    </div>

</div>

<div id="footer">
   <a href="http://jelix.org"><img src="{$j_jelixwww}/design/images/jelix_powered.png" alt="Powered by Jelix" title="Powered by Jelix"/></a>
</div>