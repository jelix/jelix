<div id="auth_login_zone">
{if $failed}
<p>{@jauth~auth.failedToLogin@}</p>
{/if}

{if ! $isLogged}

<form action="{jurl 'jauth~login:in'}" method="post" id="loginForm">
      <fieldset>
      <table>
       <tr>
           <th><label for="login">{@jauth~auth.login@}</label></th>
        <td><input type="text" name="login" id="login" size="9" value="{$login}" /></td>
       </tr>
       <tr>
           <th><label for="password">{@jauth~auth.password@}</label></th>
        <td><input type="password" name="password" id="password" size="9" /></td>
       </tr>
       {if $showRememberMe}
       <tr>
           <th><label for="rememberMe">{@jauth~auth.rememberMe@}</label></th>
        <td><input type="checkbox" name="rememberMe" id="rememberMe" value="1" /></td>
       </tr>
       {/if}
       </table>
       <input type="submit" value="{@jauth~auth.buttons.login@}"/>
       </fieldset>
   </form>
{else}
    <p>{$user->surname} {$user->name}</p>
{/if}
</div>
