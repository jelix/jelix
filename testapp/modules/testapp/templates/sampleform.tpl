<h1>A sample form</h1>
<p>Here is a form generated and managed by jforms, with a personnalized display,  using the builder "{$builder}".</p>
{if $builder == 'html'}{assign $newbuilder="legacy.htmllight"}{else}{assign $newbuilder="html"}{/if}
<ul>
    <li>If you want to see the form generated automatically by the builder: <a href="{jurl 'testapp~sampleform:show', array('builder'=>$builder, 'full'=>1)}">click here</a>.</li>
    <li>If you want to see the look of the form with the builder "{$newbuilder}",
        <a href="{jurl 'testapp~sampleform:show', array('builder'=>$newbuilder)}">click here</a></li>
</ul>

<script type="text/javascript">
{literal}
function myErrorDecorator(){
    this.message = '';
}

myErrorDecorator.prototype = {
    start : function(){
        this.message = '';
    },
    addError : function(control, messageType){
        if(messageType == 1){
            this.message  += "* " +control.errRequired + "\n";
        }else if(messageType == 2){
            this.message  += "* " +control.errInvalid + "\n";
        }
    },
    end : function(){
        if(this.message != ''){
            alert("Message from myErrorDecorator\nError:\n" + this.message);
        }
    }
}
{/literal}
</script>
{*, array('errorDecorator'=>'myErrorDecorator')*}
{form $form,'sampleform:save', array(), $builder, array('plugins'=>array('explanation' => 'textarea_as_input_html'))}
    {formcontrols}
    <div>{ctrl_label '', '%s: '} {ctrl_control}</div>
    {/formcontrols}
<p>{formreset} {formsubmit}</p>
{/form}
