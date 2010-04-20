
{foreach $profiles as $profile}
<h3>{@title.profile@}: {$profile}</h3>
{if count($errors[$profile])}
<ul class="error">
  {foreach $errors[$profile] as $err}<li>{$err|eschtml}</li>{/foreach}
</ul>
{/if}
<script type="text/javascript">
{literal}
function driverChanged(select, profile) {
  if (select.options[select.selectedIndex].value == 'sqlite') {
    document.getElementById('host-'+profile).style.display = 'none';
    document.getElementById('user-'+profile).style.display = 'none';
    document.getElementById('password-'+profile).style.display = 'none';
    document.getElementById('passwordconfirm-'+profile).style.display = 'none';
    document.getElementById('force_encoding-'+profile).style.display = 'none';
  }
  else {
    document.getElementById('host-'+profile).style.display = 'table-row';
    document.getElementById('user-'+profile).style.display = 'table-row';
    document.getElementById('password-'+profile).style.display = 'table-row';
    document.getElementById('passwordconfirm-'+profile).style.display = 'table-row';
    document.getElementById('force_encoding-'+profile).style.display = 'table-row';
  }
}
{/literal}
</script>
<table>
  <tr>
    <th><label for="driver[{$profile}]">{@label.driver@}</label></th>
    <td><select id="driver[{$profile}]" name="driver[{$profile}]"
    onchange="driverChanged(this, '{$profile}')">
    {foreach $drivers as $drv}
      <option value="{$drv}" {if $driver[$profile] == $drv}selected="selected"{/if}>{$drv}</option>
    {/foreach}
    </select></td>
  </tr>
  <tr>
    <th><label for="usepdo[{$profile}]">{@label.use.pdo@}</th>
    <td><input type="checkbox" id="usepdo[{$profile}]" name="usepdo[{$profile}]"
          {if $usepdo[$profile]}checked="checked"{/if}/></td>
  </tr>
  <tr id="host-{$profile}" {if $driver[$profile] =='sqlite'}style="display:none"{/if}>
    <th><label for="host[{$profile}]">{@label.host@}</label></th>
    <td><input id="host[{$profile}]" name="host[{$profile}]" value="{$host[$profile]|eschtml}" size=""/></td>
  </tr>
  <tr>
    <th><label for="database[{$profile}]">{@label.database@}</label></th>
    <td><input id="database[{$profile}]" name="database[{$profile}]" value="{$database[$profile]|eschtml}" size=""/></td>
  </tr>
  <tr id="user-{$profile}" {if $driver[$profile] =='sqlite'}style="display:none"{/if}>
    <th><label for="user[{$profile}]">{@label.user@}</label></th>
    <td><input id="user[{$profile}]" name="user[{$profile}]" value="{$user[$profile]|eschtml}" size=""/></td>
  </tr>
  <tr id="password-{$profile}" {if $driver[$profile] =='sqlite'}style="display:none"{/if}>
    <th><label for="password[{$profile}]">{@label.password@}</label></th>
    <td><input type="password" id="password[{$profile}]" name="password[{$profile}]" value="{$password[$profile]|eschtml}" size=""/></td>
  </tr>
  <tr id="passwordconfirm-{$profile}" {if $driver[$profile] =='sqlite'}style="display:none"{/if}>
    <th><label for="passwordconfirm[{$profile}]">{@label.password.confirm@}</label></th>
    <td><input type="password" id="passwordconfirm[{$profile}]" name="passwordconfirm[{$profile}]" value="{$passwordconfirm[$profile]|eschtml}" size=""/></td>
  </tr>
  <tr>
    <th><label for="persistent[{$profile}]">{@label.persistent@}</th>
    <td><input type="checkbox" id="persistent[{$profile}]" name="persistent[{$profile}]"
               {if $persistent[$profile]}checked="checked"{/if}/></td>
  </tr>
  <tr id="force_encoding-{$profile}" {if $driver[$profile] =='sqlite'}style="display:none"{/if}>
    <th><label for="force_encoding[{$profile}]">{@label.force_encoding@}</th>
    <td><input type="checkbox" id="force_encoding[{$profile}]" name="force_encoding[{$profile}]"
               {if $force_encoding[$profile]}checked="checked"{/if}/> {@help.force_encoding@}</td>
  </tr>
  <tr>
    <th><label for="prefix[{$profile}]">{@label.prefix@}</label></th>
    <td><input id="prefix[{$profile}]" name="prefix[{$profile}]" value="{$prefix[$profile]|eschtml}" size=""/></td>
  </tr>
</table>
{/foreach}
