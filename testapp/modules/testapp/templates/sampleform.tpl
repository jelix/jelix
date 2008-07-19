<h1>A sample form</h1>
<p>Here is a form generated and managed by jforms, with a personnalized display.</p>
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

{form $form,'sampleform:save', array(), 'html', array('errorDecorator'=>'myErrorDecorator')}
    {formcontrols}
    <div>{ctrl_label}: {ctrl_control}</div>
    {/formcontrols}
<p>{formreset} {formsubmit}</p>
{/form}
