<script type="text/javascript">
var urlAjaxForm = "{jurl 'testapp~sampleform:showajaxform'}"

{literal}
function loadForm() {
    jQuery.ajax(urlAjaxForm, {
        complete: function(jqXHR, textStatus) {
            $("#theform").html(jqXHR.responseText);
        },
        dataType: 'html',
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Error: "+textStatus);
        }
    });
}

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

<h1>A sample Ajax form</h1>
<p>By clicking on the button, you'll see a form generated and
managed by jforms, and displayed via an ajax request.</p>

<button onclick="loadForm()">Load the form</button>

<div id="theform">
    
</div>