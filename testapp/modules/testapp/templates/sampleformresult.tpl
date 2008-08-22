<h1>Form test</h1>
<h2>Content of the form (using formcontrols plugin)</h2>
<dl>
    {formcontrols $form}
    <dt>{ctrl_label}</dt>
    <dd>{ctrl_value}</dd>
    {/formcontrols}
</dl>
<h2>Content of the form (using formdatafull plugin)</h2>
{formdatafull $form}
