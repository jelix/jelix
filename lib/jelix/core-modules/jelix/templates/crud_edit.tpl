{if $id === null}

<h1>Create a new record</h1>
{formfull $form, $submitAction}

{else}

<h1>Edit a record</h1>
{formfull $form, $submitAction, array('id'=>$id)}

{/if}



<p><a href="{jurl $listAction}">Return to the list</a>.</p>