<h1>{@jauthdb_admin~crud.title.view@} {$id}</h1>
{if count($otherInfo)}
<h2>{@jauthdb_admin~crud.view.primaryinfo@}</h2>
{/if}

{formdatafull $form}

<ul class="crud-links-list">    
    <li><a href="{jurl 'jauthdb_admin~default:preupdate', array('id'=>$id)}" class="crud-link">{@jauthdb_admin~crud.link.edit.record@}</a></li>
    <li><a href="{jurl 'jauthdb_admin~default:passwordform', array('id'=>$id)}" class="crud-link">{@jauthdb_admin~crud.link.change.password@}</a></li>
    {if $canDelete}<li><a href="{jurl 'jauthdb_admin~default:delete', array('id'=>$id)}" class="crud-link" onclick="return confirm('{@jauthdb_admin~crud.confirm.deletion@}')">{@jauthdb_admin~crud.link.delete.record@}</a></li>{/if}
</ul>

{if count($otherInfo)}
<h2>{@jauthdb_admin~crud.view.otherinfo@}</h2>

{foreach $otherInfo as $info}
 {$info}
{/foreach}

{/if}

<hr />
<p><a href="{jurl 'jauthdb_admin~default:index'}" class="crud-link">{@jauthdb_admin~crud.link.return.to.list@}</a></p>

