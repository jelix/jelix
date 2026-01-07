{form $form,'sampleform:saveajax', array(), 'html', array(
    'plugins'=>array(
        'explanation' => 'textarea_as_input_html',
        'pwd' => 'password_html',
        'pwd2' => 'passwordeditor_html',
        'inputautocomplete' => 'autocomplete_html'
    ),
    'xhrSubmit' => [
        'onSuccess' => 'sampleFormOnSuccess(result);'
    ]
)}
    {formcontrols}
    <div>{ctrl_label '', '%s: '} {ctrl_control}</div>
    {/formcontrols}
<p>{formreset} {formsubmit}</p>
{/form}
