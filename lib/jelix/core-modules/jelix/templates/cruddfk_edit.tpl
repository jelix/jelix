{if $dpk === null}

<h1>{@jelix~crud.title.create@}</h1>
{formfull $form, $submitAction, array($spkName=>$spk)}

{else}

<h1>{@jelix~crud.title.update@}</h1>
{formfull $form, $submitAction, array($spkName=>$spk,$dpkName=>$dpk)}

{/if}



<p><a href="{jurl $listAction, array($spkName=>$spk)}" class="crud-link">{@jelix~crud.link.return.to.list@}</a>.</p>