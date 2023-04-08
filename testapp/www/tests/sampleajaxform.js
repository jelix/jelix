

function loadForm() {
    let urlAjaxForm = document.getElementById('script-form').dataset.urlShowForm;

    jQuery.ajax(urlAjaxForm, {
        complete: function(jqXHR, textStatus) {
            $("#theform").html(jqXHR.responseText);
            jFormsJQ.onFormReady('jforms_testapp_sample', function(form) {
                form.submitWithXHR(function(result) {
                    $("#theform").html(result.customData.htmlContent);
                });
            });
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