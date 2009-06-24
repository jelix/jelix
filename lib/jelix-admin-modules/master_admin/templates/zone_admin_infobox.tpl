<ul>
        <li id="info-user">
            <strong>{@master_admin~gui.header.user@}</strong>
            {ifacl2 'auth.user.view'}
                <span id="info-user-login"><a href="{jurl 'jauthdb_admin~user:index', array('j_user_login'=>$user->login)}">{$user->login}</a></span>
            {else}
                <span id="info-user-login">{$user->login}</span>
            {/ifacl2}
            | <a href="{jurl 'jauth~login:out'}" id="info-user-logout">{@master_admin~gui.header.disconnect@}</a>
        </li>
        {foreach $infoboxitems  as $item}
            <li {if $item->icon} style="background-image:url({$item->icon});"{/if}>
                {if $item->type == 'url'}<a href="{$item->content|eschtml}">{$item->label|eschtml}</a>
                {else}{$item->content}{/if}
            </li>
        {/foreach}
</ul>