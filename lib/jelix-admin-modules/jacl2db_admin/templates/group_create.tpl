{meta_html assets 'jacl2_admin'}

<h1>{@acl2.create.group@}</h1>
<form action="{formurl 'jacl2db_admin~groups:newgroup'}" method="post">
<label>{@acl2.group.name.label@}</label> <input name="name" required/><br/>
<label>{@acl2.group.id.label@}</label> <input name="id" required/><br/>
{foreach $subjects as $subject}
    {assign $label = $subject->label_key}
    <label>{@$label@}</label>
    <select name="{$subject->id_aclsbj}">
        <option value="">-</option>
        <option value="y">yes</option>
        <option value="n">no</option>
    </select><br/>
{/foreach}
<input type="submit" value="{@acl2.create.group@}"/>
</form>