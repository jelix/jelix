<h1>Test de formulaire</h1>
<p>Voici un formulaire de test</p>
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
            alert("Message from myErrorDecorator\nErreur de saisie:\n" + this.message);
        }
    }
}
{/literal}
</script>

{form $form,'sampleform:save', array(), 'myErrorDecorator'}
<fieldset>
   <legend>Votre identité</legend>
    {formcontrols}
    <p>{ctrl_label}: {ctrl_control}</p>
    {/formcontrols}
</fieldset>
<p>{formreset}{formsubmit}</p>
{/form}
