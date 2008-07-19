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
    frmElt: null,

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
        this.tForm = this._forms[frmElt.attributes.getNamedItem("id").value]; // we cannot use getAttribute for id because a bug with IE
        this.frmElt = frmElt;
        var msg = '';
        var valid = true;
        this.tForm.errorDecorator.start();
        for(var i =0; i < this.tForm.controls.length; i++){
            var c = this.tForm.controls[i];
            var elt = frmElt.elements[c.name];
            if (!elt) continue; // sometimes, all controls are not generated...
            var val = this.getValue(elt);
            if(val == ''){
                if(c.required){
                    this.tForm.errorDecorator.addError(c, 1);
                    valid = false;
                }
            }else{
                if(!c.check(val, this)){
                    this.tForm.errorDecorator.addError(c, 2);
                    valid = false;
                }
            }
        }
        if(!valid)
            this.tForm.errorDecorator.end();
        return valid;
    },

    getValue : function (elt){
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
            if (ctrls[i].confirmField &&  ctrls[i].confirmField.name == aControlName) {
                ctrl = ctrls[i].confirmField;
                break;
            }
        }
        if (ctrl) {
            frm.helpDecorator.show(ctrl.help);
        }
    },

    hasClass: function (elt,clss) {
        return elt.className.match(new RegExp('(\\s|^)'+clss+'(\\s|$)'));
    },
    addClass: function (elt,clss) {
        if (!this.hasClass(elt,clss)) elt.className += " "+clss;
    },
    removeClass: function (elt,clss) {
        if (this.hasClass(elt,clss)) {
            elt.className = elt.className.replace(new RegExp('(\\s|^)'+clss+'(\\s|$)'),' ');
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
        ctrl.formName = this.name;
    },

    setErrorDecorator : function (decorator){
        this.errorDecorator = decorator;
    },

    setHelpDecorator : function (decorator){
        this.helpDecorator = decorator;
    },
    getControl : function(aControlName) {
        var ctrls = this.controls;
        for(var i=0; i < ctrls.length; i++){
            if (ctrls[i].name == aControlName) {
                return ctrls[i];
            }
        }
        return null;
    }
};


/**
 * control with string
 */
function jFormsControlString(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
    this.isConfirmField = false;
    this.confirmFieldOf = '';
    this.minLength = -1;
    this.maxLength = -1;
};
jFormsControlString.prototype.check = function (val, jfrm) {
    if(this.minLength != -1 && val.length < this.minLength)
        return false;
    if(this.maxLength != -1 && val.length > this.maxLength)
        return false;
    return true;
};

/**
 * control with optional confirmation string
 */
function jFormsControlSecret(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
    this.confirmField = null;
    this.minLength = -1;
    this.maxLength = -1;
};
jFormsControlSecret.prototype.check = function (val, jfrm) {
    if(this.minLength != -1 && val.length < this.minLength)
        return false;
    if(this.maxLength != -1 && val.length > this.maxLength)
        return false;

    if(this.confirmField){
        var val2 = jfrm.getValue(jfrm.frmElt.elements[this.confirmField.name]);
        if(val != val2){
            jfrm.tForm.errorDecorator.addError(this.confirmField, 2);
            return false;
        }
    }
    return true;
};
function jFormsControlSecretConfirm(name, label) {
    this.name = name;
    this.label = label;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
    this.minLength = -1;
    this.maxLength = -1;
};

/**
 * control with boolean
 */
function jFormsControlBoolean(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlBoolean.prototype.check = function (val, jfrm) {
    return (val == 'true' || val == 'false');
};

/**
 * control with Decimal
 */
function jFormsControlDecimal(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlDecimal.prototype.check = function (val, jfrm) {
    return ( -1 != val.search(/^\s*[\+\-]?\d+(\.\d+)?\s*$/));
};

/**
 * control with Integer
 */
function jFormsControlInteger(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlInteger.prototype.check = function (val, jfrm) {
    return ( -1 != val.search(/^\s*[\+\-]?\d+\s*$/));
};

/**
 * control with Hexadecimal
 */
function jFormsControlHexadecimal(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlHexadecimal.prototype.check = function (val, jfrm) {
  return (val.search(/^0x[a-f0-9A-F]+$/) != -1);
};

/**
 * control with Datetime
 */
function jFormsControlDatetime(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlDatetime.prototype.check = function (val, jfrm) {
    var t = val.match(/^(\d{4})\-(\d{2})\-(\d{2}) (\d{2}):(\d{2})(:(\d{2}))?$/);
    if(t == null) return false;
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
        return false;
    else
        return true;
};

/**
 * control with Date
 */
function jFormsControlDate(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlDate.prototype.check = function (val, jfrm) {
    var t = val.match(/^(\d{4})\-(\d{2})\-(\d{2})$/);
    if(t == null) return false;
    var yy = parseInt(t[1],10);
    var mm = parseInt(t[2],10) -1;
    var dd = parseInt(t[3],10);
    var dt = new Date(yy,mm,dd,0,0,0);
    if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate())
        return false;
    else
        return true;
};

/**
 * control with time
 */
function jFormsControlTime(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlTime.prototype.check = function (val, jfrm) {
    var t = val.match(/^(\d{2}):(\d{2})(:(\d{2}))?$/);
    if(t == null) return false;
    var th = parseInt(t[1],10);
    var tm = parseInt(t[2],10);
    var ts = 0;
    if(t[4] != null)
        ts = parseInt(t[4],10);
    var dt = new Date(2007,05,02,th,tm,ts);
    if(th != dt.getHours() || tm != dt.getMinutes() || ts != dt.getSeconds())
        return false;
    else
        return true;
};

/**
 * control with LocaleDateTime
 */
function jFormsControlLocaledatetime(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
    this.lang='';
};
jFormsControlLocaledatetime.prototype.check = function (val, jfrm) {
    var yy, mm, dd, th, tm, ts;
    if(this.lang.indexOf('fr_') == 0) {
        var t = val.match(/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})(:(\d{2}))?$/);
        if(t == null) return false;
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
        if(t == null) return false;
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
        return false;
    else
        return true;
};

/**
 * control with localedate
 */
function jFormsControlLocaledate(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
    this.lang='';
};
jFormsControlLocaledate.prototype.check = function (val, jfrm) {
    var yy, mm, dd;
    if(this.lang.indexOf('fr_') == 0) {
        var t = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if(t == null) return false;
        yy = parseInt(t[3],10);
        mm = parseInt(t[2],10) -1;
        dd = parseInt(t[1],10);
    }else{
        //default is en_* format
        var t = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if(t == null) return false;
        yy = parseInt(t[3],10);
        mm = parseInt(t[1],10) -1;
        dd = parseInt(t[2],10);
    }
    var dt = new Date(yy,mm,dd,0,0,0);
    if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate())
        return false;
    else
        return true;
};

/**
 * control with Url
 */
function jFormsControlUrl(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlUrl.prototype.check = function (val, jfrm) {
    return (val.search(/^[a-z]+:\/\/((((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))((\/)|$)/) != -1);
};

/**
 * control with email
 */
function jFormsControlEmail(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlEmail.prototype.check = function (val, jfrm) {
    return (val.search(/^((\"[^\"f\n\r\t\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/) != -1);
};


/**
 * control with ipv4
 */
function jFormsControlIpv4(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlIpv4.prototype.check = function (val, jfrm) {
    var t = val.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/);
    if(t)
        return (t[1] > 255 || t[2] > 255 || t[3] > 255 || t[4] > 255);
    return false;
};

/**
 * control with ipv6
 */
function jFormsControlIpv6(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
};
jFormsControlIpv6.prototype.check = function (val, jfrm) {
    return (val.search(/^([a-f0-9]{1,4})(:([a-f0-9]{1,4})){7}$/i) != -1);
};

/**
 * choice control
 */
function jFormsControlChoice(name, label) {
    this.name = name;
    this.label = label;
    this.required = true;
    this.errInvalid = '';
    this.errRequired = '';
    this.help='';
    this.items = {};
};
jFormsControlChoice.prototype = {
    addControl : function (ctrl, itemValue) {
        if(this.items[itemValue] == undefined)
            this.items[itemValue] = [];
        this.items[itemValue].push(ctrl);
    },
    check : function (val, jfrm) {
        if(this.items[val] == undefined)
            return false;

        var list = this.items[val];
        for(var i=0; i < list.length; i++) {
            var val2 = jfrm.getValue(jfrm.frmElt.elements[list[i].name]);

            if (val2 == '') {
                if (list[i].required) {
                    jfrm.tForm.errorDecorator.addError(list[i], 1);
                }
            } else if (!list[i].check(val2, jfrm)) {
                jfrm.tForm.errorDecorator.addError(list[i], 2);
            }
        }
        return true;
    },
    activate : function (val) {
        var frmElt = document.getElementById(this.formName);
        for(var j in this.items) {
            var list = this.items[j];
            for(var i=0; i < list.length; i++) {
                var elt = frmElt.elements[list[i].name];
                if (val == j) {
                    elt.removeAttribute("readonly");
                    jForms.removeClass(elt, "jforms-readonly");
                } else {
                    elt.setAttribute("readonly", "readonly");
                    jForms.addClass(elt, "jforms-readonly");
                }
            }
        }
    }
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

