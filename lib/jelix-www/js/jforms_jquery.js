/**
* @package      jelix
* @subpackage   forms
* @author       Laurent Jouanneau
* @contributor  Julien Issler, Dominique Papin, Litchi
* @copyright    2007-2010 Laurent Jouanneau
* @copyright    2008-2010 Julien Issler, 2008 Dominique Papin
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/*
usage :

jFormsJQ.tForm = new jFormsJQForm('name', 'selector','internalid');                         // create a form descriptor
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorAlert());    // declare an error handler

// declare a form control
var c = new jFormsJQControl('name', 'a label', 'datatype');
c.required = true;
c.errInvalid='';
c.errRequired='';
jFormsJQ.tForm.addControl(c);
...

// declare the form now. A 'submit" event handler will be attached to the corresponding form element
jFormsJQ.declareForm(jFormsJQ.tForm);

*/

/**
 * form manager
 */
var jFormsJQ = {
    _forms: {},

    tForm: null,
    selectFillUrl : '',

    /**
     * @param jFormsJQForm aForm
     */
    declareForm : function(aForm){
        this._forms[aForm.name] = aForm;
        jQuery('#'+aForm.name).bind('submit',function (ev) {
            jQuery(ev.target).trigger('jFormsUpdateFields');
            return jFormsJQ.verifyForm(ev.target) });
    },

    getForm : function (name) {
        return this._forms[name];
    },

    /**
     *  @param DOMElement frmElt  the <form> element
     */
    verifyForm : function(frmElt) {
        this.tForm = this._forms[frmElt.attributes.getNamedItem("id").value]; // we cannot use getAttribute for id because a bug with IE
        var msg = '';
        var valid = true;
        this.tForm.errorDecorator.start(this.tForm);
        for(var i =0; i < this.tForm.controls.length; i++){
            if (!this.verifyControl(this.tForm.controls[i], this.tForm))
                valid = false;
        }
        if(!valid)
            this.tForm.errorDecorator.end();
        return valid;
    },

    /**
     * @param jFormsJQControl*  ctrl     a jform control
     * @param jFormsJQForm      frm      the jform object
     */
    verifyControl : function (ctrl, frm) {
        var val;
        if(typeof ctrl.getValue == 'function') {
            val = ctrl.getValue();
        }
        else {
            var elt = frm.element.elements[ctrl.name];
            if (!elt) return true; // sometimes, all controls are not generated...
            val = this.getValue(elt);
        }

        if (val === null || val === false) {
            if (ctrl.required) {
                frm.errorDecorator.addError(ctrl, 1);
                return false;
            }
        }
        else {
            if(!ctrl.check(val, frm)){
                frm.errorDecorator.addError(ctrl, 2);
                return false;
            }
        }
        return true;
    },

    /**
     * @param DOMElement elt
     */
    getValue : function (elt){
        if(elt.nodeType) { // this is a node
            switch (elt.nodeName.toLowerCase()) {
                case "input":
                    if(elt.getAttribute('type') == 'checkbox')
                        return elt.checked;
                case "textarea":
                    var val = jQuery.trim(elt.value);
                    return (val !== '' ? val:null);
                case "select":
                    if (!elt.multiple)
                        return (elt.value!==''?elt.value:null);
                    var values = [];
                    for (var i = 0; i < elt.options.length; i++) {
                        if (elt.options[i].selected)
                            values.push(elt.options[i].value);
                    }
                    if(values.length)
                        return values;
                    return null;
            }
        } else if(this.isCollection(elt)){
            // this is a NodeList of radio buttons or multiple checkboxes
            var values = [];
            for (var i = 0; i < elt.length; i++) {
                var item = elt[i];
                if (item.checked)
                    values.push(item.value);
            }
            if(values.length) {
                if (elt[0].getAttribute('type') == 'radio')
                    return values[0];
                return values;
            }
        }
        return null;
    },

    /**
     * @param DOMElement elt
     */
    hasClass: function (elt,clss) {
        return jQuery(elt).hasClass(clss);
    },
    addClass: function (elt,clss) {
        if (this.isCollection(elt)) {
            for(var j=0; j<elt.length;j++) {
                jQuery(elt[j]).addClass(clss);
            }
        } else {
            jQuery(elt).addClass(clss);
        }
    },
    removeClass: function (elt,clss) {
        if (this.isCollection(elt)) {
            for(var j=0; j<elt.length;j++) {
                jQuery(elt[j]).removeClass(clss);
            }
        } else {
            jQuery(elt).removeClass(clss);
        }
    },
    setAttribute: function(elt, name, value){
        if (this.isCollection(elt)) {
            for(var j=0; j<elt.length;j++) {
                elt[j].setAttribute(name, value);
            }
        } else {
            elt.setAttribute(name, value);
        }
    },
    removeAttribute: function(elt, name){
        if (this.isCollection(elt)) {
            for(var j=0; j<elt.length;j++) {
                elt[j].removeAttribute(name);
            }
        } else {
            elt.removeAttribute(name);
        }
    },
    /**
     * @param DOMElement elt
     */
    isCollection: function(elt) {
        if (typeof HTMLCollection != "undefined" && elt instanceof HTMLCollection) {
            return true;
        }
        if (typeof NodeList != "undefined" && elt instanceof NodeList) {
          return true;
        }
        if (elt instanceof Array)
            return true;
        if (elt.length != undefined && (elt.localName == undefined || elt.localName == 'SELECT' || elt.localName != 'select'))
            return true;
        return false;
    }
};

/**
 * represents a form
 */
function jFormsJQForm(name, selector, id){
    this.selector = selector;
    this.formId = id;
    this.name = name;
    this.controls = [];
    this.errorDecorator =  new jFormsJQErrorDecoratorHtml();
    this.element = jQuery('#'+name).get(0);
};

jFormsJQForm.prototype={
    /**
     * @param jFormsJQControl ctrl
     */
    addControl : function(ctrl){
        this.controls.push(ctrl);
        ctrl.formName = this.name;
    },

    setErrorDecorator : function (decorator){
        this.errorDecorator = decorator;
    },

    /**
     * @return jFormsJQControl
     */
    getControl : function(aControlName) {
        var ctrls = this.controls;
        for(var i=0; i < ctrls.length; i++){
            if (ctrls[i].name == aControlName) {
                return ctrls[i];
            }
        }
        return null;
    },

    declareDynamicFill : function (controlName) {
        var elt = this.element.elements[controlName];
        var ctrl = this.getControl(controlName);
        if (!ctrl.dependencies)
            return;
        var me = this;
        for(var i=0; i< ctrl.dependencies.length; i++) {
            var dep = this.element.elements[ctrl.dependencies[i]];
            jQuery(dep).change(function() {
                me.dynamicFillAjax(ctrl);
            });
        }
    },

    dynamicFillAjax : function (ctrl) {
        var token = this.element.elements['__JFORMS_TOKEN__'];
        if (typeof token == "undefined" ) {
            token = '';
        }
        else
            token = token.value;

        var param = {
            '__form': this.selector,
            '__formid' : this.formId,
            '__JFORMS_TOKEN__' : token,
            '__ref' : ctrl.name
        }

        for(var i=0; i< ctrl.dependencies.length; i++) {
            var n = ctrl.dependencies[i];
            param[n] = jFormsJQ.getValue(this.element.elements[n]);
        }

        var elt = this.element.elements[ctrl.name];

        jQuery.post(jFormsJQ.selectFillUrl, param,
            function(data){
              if (typeof data.data == "undefined")
                return;
                if(elt.nodeType && elt.nodeName.toLowerCase() == 'select') {
                    var emptyitem = jQuery('option[value=""]', elt);
                    jQuery(elt).empty();
                    var opt;
                    if (0 < emptyitem.length) {
                        opt = document.createElement('option');
                        opt.value = emptyitem.val();
                        opt.appendChild(document.createTextNode(emptyitem.text()));
                        elt.appendChild(opt);
                    }
                    var val, name;
                    for (val in data.data) {
                        opt = document.createElement('option');
                        opt.setAttribute('value', val);
                        opt.appendChild(document.createTextNode(data.data[val]));
                        elt.appendChild(opt);
                    }
                }
            }, "json");
    }
};

/**
 * control with string
 */
function jFormsJQControlString(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.minLength = -1;
    this.maxLength = -1;
};
jFormsJQControlString.prototype.check = function (val, jfrm) {
    if(this.minLength != -1 && val.length < this.minLength)
        return false;
    if(this.maxLength != -1 && val.length > this.maxLength)
        return false;
    return true;
};

/**
 * control with HTML content generated by a wysiwyg editor
 */
function jFormsJQControlHtml(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.minLength = -1;
    this.maxLength = -1;
};
jFormsJQControlHtml.prototype.check = function (val, jfrm) {
    if(this.minLength != -1 && val.length < this.minLength)
        return false;
    if(this.maxLength != -1 && val.length > this.maxLength)
        return false;
    return true;
};
jFormsJQControlHtml.prototype.getValue = function () {
    var frm = jFormsJQ.getForm(this.formName);
    var elt = frm.element.elements[this.name];
    if (!elt) return null;
    val = jFormsJQ.getValue(elt);
    if (val == null)
        return null;
    val = val.replace(/<(img|object|video|svg|embed)[^>]*>/gi, 'TAG'); //tags which are contents
    val = val.replace(/<\/?[\S][^>]*>/gi, '');
    val = val.replace(/&[a-z]+;/gi, '');
    val = jQuery.trim(val);
    if (val=='')
        return null;
    return val;
}


/**
 * control for secret input
 */
function jFormsJQControlSecret(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.minLength = -1;
    this.maxLength = -1;
};
jFormsJQControlSecret.prototype.check = function (val, jfrm) {
    if(this.minLength != -1 && val.length < this.minLength)
        return false;
    if(this.maxLength != -1 && val.length > this.maxLength)
        return false;
    return true;
};

/**
 * confirm control
 */
function jFormsJQControlConfirm(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this._masterControl = name.replace(/_confirm$/,'');
};
jFormsJQControlConfirm.prototype.check = function(val, jfrm) {
    if(jFormsJQ.getValue(jfrm.element.elements[this._masterControl]) !== val)
        return false;
    return true;
};

/**
 * control with boolean
 */
function jFormsJQControlBoolean(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
};
jFormsJQControlBoolean.prototype.check = function (val, jfrm) {
    return (val == true || val == false);
};

/**
 * control with Decimal
 */
function jFormsJQControlDecimal(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
};
jFormsJQControlDecimal.prototype.check = function (val, jfrm) {
    return ( -1 != val.search(/^\s*[\+\-]?\d+(\.\d+)?\s*$/));
};

/**
 * control with Integer
 */
function jFormsJQControlInteger(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
};
jFormsJQControlInteger.prototype.check = function (val, jfrm) {
    return ( -1 != val.search(/^\s*[\+\-]?\d+\s*$/));
};

/**
 * control with Hexadecimal
 */
function jFormsJQControlHexadecimal(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
};
jFormsJQControlHexadecimal.prototype.check = function (val, jfrm) {
  return (val.search(/^0x[a-f0-9A-F]+$/) != -1);
};

/**
 * control with Datetime
 */
function jFormsJQControlDatetime(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.minDate = null;
    this.maxDate = null;
    this.multiFields = false;
};
jFormsJQControlDatetime.prototype.check = function (val, jfrm) {
    var t = val.match(/^(\d{4})\-(\d{2})\-(\d{2}) (\d{2}):(\d{2})(:(\d{2}))?$/);
    if(t == null) return false;
    var yy = parseInt(t[1],10);
    var mm = parseInt(t[2],10) -1;
    var dd = parseInt(t[3],10);
    var th = parseInt(t[4],10);
    var tm = parseInt(t[5],10);
    var ts = 0;
    if(t[7] != null && t[7] != "")
        ts = parseInt(t[7],10);
    var dt = new Date(yy,mm,dd,th,tm,ts);
    if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate() || th != dt.getHours() || tm != dt.getMinutes() || ts != dt.getSeconds())
        return false;
    else if((this.minDate !== null && val < this.minDate) || (this.maxDate !== null && val > this.maxDate))
        return false;
    return true;
};
jFormsJQControlDatetime.prototype.getValue = function(){
    if (!this.multiFields) {
        var val = jQuery.trim(jQuery('#'+this.formName+'_'+this.name).val());
        return (val!==''?val:null);
    }

    var controlId = '#'+this.formName+'_'+this.name;
    var v = jQuery(controlId+'_year').val() + '-'
        + jQuery(controlId+'_month').val() + '-'
        + jQuery(controlId+'_day').val() + ' '
        + jQuery(controlId+'_hour').val() + ':'
        + jQuery(controlId+'_minutes').val();

    var secondsControl = jQuery('#'+this.formName+'_'+this.name+'_seconds');
    if(secondsControl.attr('type') !== 'hidden'){
        v += ':'+secondsControl.val();
        if(v == '-- ::')
            return null;
    }
    else if(v == '-- :')
        return null;
    return v;
};
jFormsJQControlDatetime.prototype.deactivate = function(deactivate){
    var controlId = '#'+this.formName+'_'+this.name;
    if(deactivate){
        if (!this.multiFields)
            jQuery(controlId).attr('disabled','disabled').addClass('jforms-disabled').trigger('jFormsActivateControl', false);
        else{
            jQuery(controlId+'_year').attr('disabled','disabled').addClass('jforms-disabled');
            jQuery(controlId+'_month').attr('disabled','disabled').addClass('jforms-disabled');
            jQuery(controlId+'_day').attr('disabled','disabled').addClass('jforms-disabled');
            jQuery(controlId+'_hour').attr('disabled','disabled').addClass('jforms-disabled');
            jQuery(controlId+'_minutes').attr('disabled','disabled').addClass('jforms-disabled');
            jQuery(controlId+'_seconds').attr('disabled','disabled').addClass('jforms-disabled');
            jQuery(controlId+'_hidden').trigger('jFormsActivateControl', false);
        }
    }
    else{
        if (!this.multiFields)
            jQuery(controlId).removeAttr('disabled').removeClass('jforms-disabled').trigger('jFormsActivateControl', true);
        else{
            jQuery(controlId+'_year').removeAttr('disabled').removeClass('jforms-disabled');
            jQuery(controlId+'_month').removeAttr('disabled').removeClass('jforms-disabled');
            jQuery(controlId+'_day').removeAttr('disabled').removeClass('jforms-disabled');
            jQuery(controlId+'_hour').removeAttr('disabled').removeClass('jforms-disabled');
            jQuery(controlId+'_minutes').removeAttr('disabled').removeClass('jforms-disabled');
            jQuery(controlId+'_seconds').removeAttr('disabled').removeClass('jforms-disabled');
            jQuery(controlId+'_hidden').trigger('jFormsActivateControl', true);
        }
    }
};

/**
 * control with Date
 */
function jFormsJQControlDate(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.multiFields = false;
    this.minDate = null;
    this.maxDate = null;
};
jFormsJQControlDate.prototype.check = function (val, jfrm) {
    var t = val.match(/^(\d{4})\-(\d{2})\-(\d{2})$/);
    if(t == null) return false;
    var yy = parseInt(t[1],10);
    var mm = parseInt(t[2],10) -1;
    var dd = parseInt(t[3],10);
    var dt = new Date(yy,mm,dd,0,0,0);
    if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate())
        return false;
    else if((this.minDate !== null && val < this.minDate) || (this.maxDate !== null && val > this.maxDate))
        return false;
    return true;
};
jFormsJQControlDate.prototype.getValue = function(){
    if (!this.multiFields) {
        var val = jQuery.trim(jQuery('#'+this.formName+'_'+this.name).val());
        return (val!==''?val:null);
    }

    var controlId = '#'+this.formName+'_'+this.name;
    var v = jQuery(controlId+'_year').val() + '-'
        + jQuery(controlId+'_month').val() + '-'
        + jQuery(controlId+'_day').val();
    if(v == '--')
        return null;
    return v;
};
jFormsJQControlDate.prototype.deactivate = function(deactivate){
    var controlId = '#'+this.formName+'_'+this.name;
    if(deactivate){
        if (!this.multiFields)
            jQuery(controlId).attr('disabled','disabled').addClass('jforms-disabled').trigger('jFormsActivateControl', false);
        else{
            jQuery(controlId+'_year').attr('disabled','disabled').addClass('jforms-disabled');
            jQuery(controlId+'_month').attr('disabled','disabled').addClass('jforms-disabled');
            jQuery(controlId+'_day').attr('disabled','disabled').addClass('jforms-disabled');
            jQuery(controlId+'_hidden').trigger('jFormsActivateControl', false);
        }
    }
    else{
        if (!this.multiFields)
            jQuery(controlId).removeAttr('disabled').removeClass('jforms-disabled').trigger('jFormsActivateControl', true);
        else{
            jQuery(controlId+'_year').removeAttr('disabled').removeClass('jforms-disabled');
            jQuery(controlId+'_month').removeAttr('disabled').removeClass('jforms-disabled');
            jQuery(controlId+'_day').removeAttr('disabled').removeClass('jforms-disabled');
            jQuery(controlId+'_hidden').trigger('jFormsActivateControl', true);
        }
    }
};

/**
 * control with time
 */
function jFormsJQControlTime(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
};
jFormsJQControlTime.prototype.check = function (val, jfrm) {
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
function jFormsJQControlLocaleDatetime(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.lang='';
};
jFormsJQControlLocaleDatetime.prototype.check = function (val, jfrm) {
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
function jFormsJQControlLocaleDate(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.lang='';
};
jFormsJQControlLocaleDate.prototype.check = function (val, jfrm) {
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
function jFormsJQControlUrl(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
};
jFormsJQControlUrl.prototype.check = function (val, jfrm) {
    return (val.search(/^[a-z]+:\/\/((((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))((\/)|$)/) != -1);
};

/**
 * control with email
 */
function jFormsJQControlEmail(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
};
jFormsJQControlEmail.prototype.check = function (val, jfrm) {
    return (val.search(/^((\"[^\"f\n\r\t\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/) != -1);
};


/**
 * control with ipv4
 */
function jFormsJQControlIpv4(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
};
jFormsJQControlIpv4.prototype.check = function (val, jfrm) {
    var t = val.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/);
    if(t)
        return (t[1] < 256 && t[2] < 256 && t[3] < 256 && t[4] < 256);
    return false;
};

/**
 * control with ipv6
 */
function jFormsJQControlIpv6(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
};
jFormsJQControlIpv6.prototype.check = function (val, jfrm) {
    return (val.search(/^([a-f0-9]{1,4})(:([a-f0-9]{1,4})){7}$/i) != -1);
};

/**
 * choice control
 */
function jFormsJQControlChoice(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.items = {};
};
jFormsJQControlChoice.prototype = {
    addControl : function (ctrl, itemValue) {
        if(this.items[itemValue] == undefined)
            this.items[itemValue] = [];
        this.items[itemValue].push(ctrl);
        ctrl.formName = this.formName;
    },
    check : function (val, jfrm) {
        if(this.items[val] == undefined)
            return false;

        var list = this.items[val];
        var valid = true;
        for(var i=0; i < list.length; i++) {
            var val2 = jFormsJQ.getValue(jfrm.element.elements[list[i].name]);

            if (val2 === null || val2 === false) {
                if (list[i].required) {
                    jfrm.errorDecorator.addError(list[i], 1);
                    valid = false;
                }
            } else if (!list[i].check(val2, jfrm)) {
                jfrm.errorDecorator.addError(list[i], 2);
                valid = false;
            }
        }
        return valid;
    },
    activate : function (val) {
        var frmElt = document.getElementById(this.formName);
        for(var j in this.items) {
            var list = this.items[j];
            for(var i=0; i < list.length; i++) {
                if(typeof list[i].deactivate == 'function'){
                    list[i].deactivate(val != j);
                    continue;
                }
                var elt = frmElt.elements[list[i].name];
                if (val == j) {
                    jFormsJQ.removeAttribute(elt, "disabled");
                    jFormsJQ.removeClass(elt, "jforms-disabled");
                } else {
                    jFormsJQ.setAttribute(elt, "disabled", "disabled");
                    jFormsJQ.addClass(elt, "jforms-disabled");
                }
            }
        }
    }
};

/**
 * Decorator to display errors in an alert dialog box
 */
function jFormsJQErrorDecoratorAlert(){
    this.message = '';
};

jFormsJQErrorDecoratorAlert.prototype = {
    start : function(form){
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

function jFormsJQErrorDecoratorHtml(){
    this.message = '';
};

jFormsJQErrorDecoratorHtml.prototype = {
    start : function(form){
        this.message = '';
        this.form = form;
        jQuery("#"+form.name+" .jforms-error").removeClass('jforms-error');
    },
    addError : function(control, messageType){
        var elt = this.form.element.elements[control.name];
        if (elt && elt.nodeType) {
            jQuery(elt).addClass('jforms-error');
        }
        var name = control.name.replace(/\[\]/, '');
        jQuery("#"+this.form.name+"_"+name+"_label").addClass('jforms-error');

        if(messageType == 1){
            this.message  += '<li class="error"> '+control.errRequired + "</li>";
        }else if(messageType == 2){
            this.message  += '<li class="error"> ' +control.errInvalid + "</li>";
        }else{
            this.message  += '<li class="error"> Error on \''+control.label+"' </li>";
        }
    },
    end : function(){
        var errid = this.form.name+'_errors';
        var ul = document.getElementById(errid);
        if(this.message != ''){
            if (!ul) {
                ul = document.createElement('ul');
                ul.setAttribute('class', 'jforms-error-list');
                ul.setAttribute('id', errid);
                jQuery(this.form.element).first().before(ul);
            }
            var jul = jQuery(ul);
            location.href="#"+errid;
            jul.hide().html(this.message).fadeIn();
        }
        else if (ul) {
            jQuery(ul).hide();
        }
    }
};