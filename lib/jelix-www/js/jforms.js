/**
* @package      jelix
* @subpackage   forms
* @author       Laurent Jouanneau
* @contributor  Julien Issler
* @copyright    2007-2008 Laurent Jouanneau
* @copyright    2008 Julien Issler
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/*
usage :

jForms.tForm = new jFormsForm('name');                         // create a form descriptor
jForms.tForm.setErrorDecorator(new jFormsErrorDecoratorAlert());    // declare an error handler

// declare a form control
jForms.tControl = new jFormsControl('name', 'a label', 'datatype');
jForms.tControl.required = true;
jForms.tControl.errInvalid='';
jForms.tControl.errRequired='';
jForms.tForm.addControl( gControl);
...

// declare the form now
jForms.declareForm(jForms.tForm);

//On a form tag, you should add this onsubmit attribute :
onsubmit="return jForms.verifyForm(this)"

*/

/**
 * form manager
 */
var jForms = {
    _forms: {},

    tForm: null,
    tControl: null,
    tControl2: null,

    declareForm : function(aForm){
        this._forms[aForm.name]=aForm;
        var elem = document.getElementById(aForm.name);

        if (elem.addEventListener) {
            elem.addEventListener("submit", function (ev) { if(!jForms.verifyForm(elem)) {ev.preventDefault(); ev.stopPropagation(); return false;} return true; }, false);
        } else if (elem.attachEvent) {
            elem.attachEvent("onsubmit", function (ev) { if(!jForms.verifyForm(elem)) { window.event.cancelBubble = true ; window.event.returnValue = false; return false;} return true;});
        }
    },

    getForm : function (name) {
        return this._forms[name];
    },

    verifyForm : function(frmElt){
        var f = this._forms[frmElt.attributes.getNamedItem("id").value]; // we cannot use getAttribute for id because a bug with IE
        var msg = '';
        var valid = true;
        f.errorDecorator.start();
        for(var i =0; i < f.controls.length; i++){
            var c = f.controls[i];
            var elt = frmElt.elements[c.name];
            if (!elt) continue; // sometimes, all controls are not generated...
            var val = this._getValue(elt);
            if(val == ''){
                if(c.required){
                    f.errorDecorator.addError(c, 1);
                    valid = false;
                }
            }else{
                var ok = false;
                switch(c.datatype){
                    case 'string' :
                        ok = true;
                        if(c.minLength != -1 && val.length < c.minLength)
                            ok = false;
                        if(c.maxLength != -1 && val.length > c.maxLength)
                            ok = false;
                        break;
                    case 'boolean' :
                        ok = (val == 'true' || val == 'false');
                        break;
                    case 'decimal' :
                        ok = ( -1 != val.search(/^\s*[\+\-]?\d+(\.\d+)?\s*$/));
                        break;
                    case 'integer' :
                        ok = ( -1 != val.search(/^\s*[\+\-]?\d+\s*$/));
                        break;
                    case 'hexadecimal' : 
                        ok = (val.search(/^0x[a-f0-9A-F]+$/) != -1);
                        break;
                    case 'datetime' : 
                        var t = val.match(/^(\d{4})\-(\d{2})\-(\d{2}) (\d{2}):(\d{2})(:(\d{2}))?$/);
                        if(t == null){ ok=false; break; };
                        var yy = parseInt(t[1],10);
                        var mm = parseInt(t[2],10) -1;
                        var dd = parseInt(t[3],10);
                        var th = parseInt(t[4],10);
                        var tm = parseInt(t[5],10);
                        var ts = 0;
                        if(t[7] != null)
                            ts = parseInt(t[7],10);
                        var dt = new Date(yy,mm,dd,th,tm,ts);
                        if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate() || th != dt.getHours() || tm != dt.getMinutes() || ts != dt.getSeconds())
                            ok = false;
                        else
                            ok = true;
                        break;
                    case 'date' :
                        var t = val.match(/^(\d{4})\-(\d{2})\-(\d{2})$/);
                        if(t == null){ ok=false; break; };
                        var yy = parseInt(t[1],10);
                        var mm = parseInt(t[2],10) -1;
                        var dd = parseInt(t[3],10);
                        var dt = new Date(yy,mm,dd,0,0,0);
                        if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate())
                            ok = false;
                        else
                            ok = true;
                        break;
                    case 'localetime' :
                    case 'time' :
                        var t = val.match(/^(\d{2}):(\d{2})(:(\d{2}))?$/);
                        if(t == null){ ok=false; break; };
                        var th = parseInt(t[1],10);
                        var tm = parseInt(t[2],10);
                        var ts = 0;
                        if(t[4] != null)
                            ts = parseInt(t[4],10);
                        var dt = new Date(2007,05,02,th,tm,ts);
                        if(th != dt.getHours() || tm != dt.getMinutes() || ts != dt.getSeconds())
                            ok = false;
                        else
                            ok = true;
                        break;
                    case 'localedatetime' :
                        var yy, mm, dd, th, tm, ts;
                        if(c.lang.indexOf('fr_') == 0) {
                            var t = val.match(/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})(:(\d{2}))?$/);
                            if(t == null){ ok=false; break; }
                            yy = parseInt(t[3],10);
                            mm = parseInt(t[2],10) -1;
                            dd = parseInt(t[1],10);
                            th = parseInt(t[4],10);
                            tm = parseInt(t[5],10);
                            ts = 0;
                            if(t[7] != null)
                                ts = parseInt(t[7],10);
                        }else{
                            //default is en_* format
                            var t = val.match(/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})(:(\d{2}))?$/);
                            if(t == null){ ok=false; break; }
                            yy = parseInt(t[3],10);
                            mm = parseInt(t[1],10) -1;
                            dd = parseInt(t[2],10);
                            th = parseInt(t[4],10);
                            tm = parseInt(t[5],10);
                            ts = 0;
                            if(t[7] != null)
                                ts = parseInt(t[7],10);
                        }
                        var dt = new Date(yy,mm,dd,th,tm,ts);
                        if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate() || th != dt.getHours() || tm != dt.getMinutes() || ts != dt.getSeconds())
                            ok = false;
                        else
                            ok = true;
                        break;
                    case 'localedate' :
                        var yy, mm, dd;
                        if(c.lang.indexOf('fr_') == 0) {
                            var t = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                            if(t == null){ ok=false; break; }
                            yy = parseInt(t[3],10);
                            mm = parseInt(t[2],10) -1;
                            dd = parseInt(t[1],10);
                        }else{
                            //default is en_* format
                            var t = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                            if(t == null){ ok=false; break; }
                            yy = parseInt(t[3],10);
                            mm = parseInt(t[1],10) -1;
                            dd = parseInt(t[2],10);
                        }
                        var dt = new Date(yy,mm,dd,0,0,0);
                        if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate())
                            ok = false;
                        else
                            ok = true;
                        break;
                    case 'url' :
                        ok = (val.search(/^[a-z]+:\/\/((((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))((\/)|$)/) != -1);
                        break;
                    case 'email' :
                        ok = (val.search(/^((\"[^\"f\n\r\t\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/) != -1);
                        break;
                    case 'ipv4' :
                         var t = val.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/);
                         if(t)
                            ok = (t[1] > 255 || t[2] > 255 || t[3] > 255 || t[4] > 255);
                         else
                            ok = false;
                        break;
                    case 'ipv6' :
                        ok = (val.search(/^([a-f0-9]{1,4})(:([a-f0-9]{1,4})){7}$/i) != -1);
                        break;
                }
                if(!ok){
                    f.errorDecorator.addError(c, 2);
                    valid = false;
                }
            }
            if(c.isConfirmField){
                var val2 = this._getValue(frmElt.elements[c.confirmFieldOf]);
                if(val != val2){
                    f.errorDecorator.addError(c, 2);
                    valid = false;
                }
            }
        }
        if(!valid)
            f.errorDecorator.end();
        return valid;
    },


    _getValue : function (elt){
        var value='';
        if(elt.nodeType) { // this is a node
            switch (elt.nodeName.toLowerCase()) {
                case "input":
                    switch (elt.getAttribute("type")) {
                    case "checkbox":
                    case "radio":
                        if (elt.checked)
                            value = 'true';
                        else
                            value = 'false';
                        break;
                    default:
                        value = elt.value;
                        break;
                    }
                    break;
                case "textarea":
                    value= elt.value;
                    break;
                case "select":
                    if (!elt.multiple) {
                        value =  elt.value;
                        break;
                    }
                    var options = elt.getElementsByTagName("option");
                    value = [];
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].selected) {
                            value.push(options[i].value);
                        }
                    }
                    break;
            }
        } else if(elt.item){
            // this is a NodeList of radio buttons
            value = [];
            for (var i = 0; i < elt.length; i++) {
                var radio = elt.item(i);
                if (radio.checked) {
                    value.push(radio.value);
                }
            }
        }
        return value;
    },

    showHelp : function(aFormName, aControlName){
        var frm = this._forms[aFormName];
        var ctrls = frm.controls;
        var ctrl = null;
        for(var i=0; i < ctrls.length; i++){
            if (ctrls[i].name == aControlName) {
                ctrl = ctrls[i];
                break;
            }
        }
        if (ctrl) {
            frm.helpDecorator.show(ctrl.help);
        }
    }
};

/**
 * represents a form
 */
function jFormsForm(name){
    this.name = name;
    this.controls = [];
    this.errorDecorator =  new jFormsErrorDecoratorAlert();
    this.helpDecorator =  new jFormsHelpDecoratorAlert();
};

jFormsForm.prototype={
    addControl : function(ctrl){
        this.controls.push(ctrl);
    },

    setErrorDecorator : function (decorator){
        this.errorDecorator = decorator;
    },

    setHelpDecorator : function (decorator){
        this.helpDecorator = decorator;
    }
};

/*
 * informations about a control
 */
function jFormsControl(name, label, datatype) {
    this.name = name;
    this.label = label;
    this.datatype = datatype;
    this.required = false;
    this.readonly = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
    this.isConfirmField = false;
    this.confirmFieldOf = '';
    this.minLength = -1;
    this.maxLength = -1;
};


/**
 * Decorator to display errors in an alert dialog box
 */
function jFormsErrorDecoratorAlert(){
    this.message = '';
};

jFormsErrorDecoratorAlert.prototype = {
    start : function(){
        this.message = '';
    },
    addError : function(control, messageType){
        if(messageType == 1){
            this.message  +="* "+control.errRequired + "\n";
        }else if(messageType == 2){
            this.message  +="* "+control.errInvalid + "\n";
        }else{
            this.message  += "* Error on '"+control.label+"' field\n";
        }
    },
    end : function(){
        if(this.message != ''){
            alert(this.message);
        }
    }
};


/**
 * Decorator to display help messages in an alert dialog box
 */
function jFormsHelpDecoratorAlert() {

};
jFormsHelpDecoratorAlert.prototype = {
    show : function( message){
        alert(message);
    }
};

