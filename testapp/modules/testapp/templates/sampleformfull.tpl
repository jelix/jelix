<h1>Sample form</h1>
<p>Here is a form generated and managed entirely by jforms using the builder "{$builder}"</p>
{if $builder == 'html'}{assign $newbuilder="legacy.htmllight"}{else}{assign $newbuilder="html"}{/if}

<ul>
    <li>If you want to see the form using a custom template: <a href="{jurl 'testapp~sampleform:show', array('builder'=>$builder)}">click here</a>.</li>
    <li>If you want to see the look of the form with the builder "{$newbuilder}",
        <a href="{jurl 'testapp~sampleform:show', array('builder'=>$newbuilder)}">click here</a></li>
</ul>


{formfull $form,'sampleform:save', array(), $builder, array(
'plugins'=>array(
    'explanation' => 'textarea_as_input_html',
    'pwd' => 'password_html',
    'pwd2' => 'passwordeditor_html',
    'inputautocomplete' => 'autocomplete_html'
))}
