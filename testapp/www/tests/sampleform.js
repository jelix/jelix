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

jQuery(document).ready(function(){

    // test onFormReady, that add a submit handler on the form
    jFormsJQ.onFormReady('jforms_testapp_sample', function(form){
        form.addSubmitHandler(function(evSubmit){
            return window.confirm('Confirm to submit the form?');
        })
    })
});