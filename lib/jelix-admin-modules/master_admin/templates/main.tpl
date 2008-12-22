<div id="header">
    <div id="top">{if $adminTitle}{$adminTitle|eschtml}{else}{@master_admin~gui.header.title@}{/if}</div>

    <div id="info-box">
        <div id="info-user">{@master_admin~gui.header.user@} <span id="info-user-login">{$user->login}</span>
        | <a href="{jurl 'jauth~login:out'}" id="info-user-logout">{@master_admin~gui.header.disconnect@}</a>
        </div>
    </div>
</div>
<div id="main">
    <div id="menu">
        {$MENU}
    </div>

    <div id="content">
    {if $MAIN}{$MAIN}{else}<p>{@master_admin~gui.nocontent@}</p>{/if}
    </div>

</div>

<div id="footer">
   <a href="http://jelix.org"><img src="{$j_jelixwww}/design/images/jelix_powered.png" alt="Powered by Jelix" title="Powered by Jelix"/></a>
</div>