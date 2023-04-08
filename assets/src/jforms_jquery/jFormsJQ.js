/**
 * @author       Laurent Jouanneau
 * @contributor  Julien Issler, Dominique Papin
 * @copyright    2007-2023 Laurent Jouanneau
 * @copyright    2008-2015 Julien Issler, 2008 Dominique Papin
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
import $ from 'jquery';

/**
 * form manager
 */
const jFormsJQ = {

    /**
     * list of jFormsJQForm. property name are id value of forms (jforms_<module>_<name>)
     */
    _forms: {},

    tForm: null,
    selectFillUrl : '',

    config : {},

    _onReadyCallback: {},

    _submitListener : function(ev) {
        let frm = jFormsJQ.getForm(ev.target.attributes.getNamedItem("id").value);

        $(ev.target).trigger('jFormsUpdateFields');

        let submitOk = true;
        try {
            for (let i=0; i< frm.preSubmitHandlers.length; i++) {
                if (!frm.preSubmitHandlers[i](ev))
                    submitOk = false;
            }

            if (!jFormsJQ.verifyForm(ev.target))
                submitOk = false;

            for (let j=0; j< frm.postSubmitHandlers.length; j++) {
                if (!frm.postSubmitHandlers[j](ev))
                    submitOk = false;
            }
        }
        catch(e) {
            return false;
        }

        if (frm.isSubmitWithXhr && submitOk) {
            let fData = new FormData(frm.element);
            let url = frm.element.getAttribute('action');
            let httpMethod = frm.element.getAttribute('method');

            $.ajax(url, {
                data: fData,
                processData: false,
                contentType: false,
                method: httpMethod,
                dataType: 'json'
            })
            .done(function(data, status, xhr) {
                if (data.success) {
                    if (frm.xhrValidFormCallback) {
                        frm.xhrValidFormCallback(data)
                    }
                    else if (data.locationUrl) {
                        window.location.href = data.locationUrl;
                    }
                }
                else {
                    if (frm.xhrFormInErrorCallback) {
                        frm.xhrFormInErrorCallback(data)
                    }
                    else if (data.locationUrl) {
                        window.location.href = data.locationUrl;
                    }
                    else {
                        frm.setErrors(data.errors);
                    }
                }
            })
            .fail(function(xhr, status, error) {
                if (xhr.responseJSON && 'errorMessage' in xhr.responseJSON) {
                    error = xhr.responseJSON.errorMessage;
                }
                frm.showSubmitError(error);
            });
            //ev.preventDefault();
            return false;
        }
        return submitOk;
    },

    /**
     * @param {jFormsJQForm} aForm
     */
    declareForm : function(aForm){
        this._forms[aForm.name] = aForm;
        $('#'+aForm.name).bind('submit', jFormsJQ._submitListener);
        const event = new Event("jformsready");
        document.getElementById(aForm.name).dispatchEvent(event);
        if (aForm.name in this._onReadyCallback) {
            this._onReadyCallback[aForm.name](aForm);
        }
    },

    /**
     * Set a listener that is called when the form corresponding to the given name,
     * is ready.
     *
     * The listener may be called immediately if the form is already ready.
     * The listener should be a function that accepts a jFormsJQForm as parameter.
     * For example, the listener can register
     *
     * @param {String} formName  should be a name like `jforms_<module>_<name>`
     * @param {Function} callback
     */
    onFormReady: function (formName, callback) {
        this._onReadyCallback[formName] = callback;
        if (formName in this._forms) {
            callback(this._forms[formName]);
        }
    },

    /**
     *
     * @param name  should be a name like `jforms_<module>_<name>`
     * @returns {jFormsJQForm}
     */
    getForm : function (name) {
        return this._forms[name];
    },

    /**
     *  @param {Element} frmElt  the <form> HTML element
     */
    verifyForm : function(frmElt) {
        this.tForm = this._forms[frmElt.attributes.getNamedItem("id").value]; // we cannot use getAttribute for id because a bug with IE
        let valid = true;
        this.tForm.errorDecorator.start(this.tForm);
        for(let i =0; i < this.tForm.controls.length; i++){
            if (!this.verifyControl(this.tForm.controls[i], this.tForm))
                valid = false;
        }
        if(!valid)
            this.tForm.errorDecorator.end();
        return valid;
    },

    /**
     * @param {jFormsJQControl}  ctrl     a jform control
     * @param {jFormsJQForm}      frm      the jform object
     */
    verifyControl : function (ctrl, frm) {
        let val;
        if(typeof ctrl.getValue == 'function') {
            val = ctrl.getValue();
        }
        else {
            let elt = frm.element.elements[ctrl.name];
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
                if (!("getChild" in ctrl)) {
                    // don't output error for groups/choice, errors on child have already been set
                    frm.errorDecorator.addError(ctrl, 2);
                }
                return false;
            }
        }
        return true;
    },

    /**
     * @param {Element} elt
     */
    getValue : function (elt){
        if(elt.nodeType) { // this is a node
            let val, values = [];
            switch (elt.nodeName.toLowerCase()) {
                case "input":
                    if (elt.getAttribute('type') === 'checkbox') {
                        return elt.checked;
                    }
                    /* falls through */
                case "textarea":
                    val = $.trim(elt.value);
                    return (val !== '' ? val:null);
                case "select":
                    if (!elt.multiple) {
                        return (elt.value!==''?elt.value:null);
                    }
                    for (let i = 0; i < elt.options.length; i++) {
                        if (elt.options[i].selected) {
                            values.push(elt.options[i].value);
                        }
                    }
                    if (values.length) {
                        return values;
                    }
                    return null;
            }
        } else if(this.isCollection(elt)){
            // this is a NodeList of radio buttons or multiple checkboxes
            let values = [];
            for (let i = 0; i < elt.length; i++) {
                let item = elt[i];
                if (item.checked)
                    values.push(item.value);
            }
            if(values.length) {
                if (elt[0].getAttribute('type') === 'radio')
                    return values[0];
                return values;
            }
        }
        return null;
    },

    /**
     * @param {Element} elt
     * @param {String} clss  CSS class
     */
    hasClass: function (elt,clss) {
        return $(elt).hasClass(clss);
    },
    addClass: function (elt,clss) {
        if (this.isCollection(elt)) {
            for(let j=0; j<elt.length;j++) {
                $(elt[j]).addClass(clss);
            }
        } else {
            $(elt).addClass(clss);
        }
    },
    removeClass: function (elt,clss) {
        if (this.isCollection(elt)) {
            for(let j=0; j<elt.length;j++) {
                $(elt[j]).removeClass(clss);
            }
        } else {
            $(elt).removeClass(clss);
        }
    },
    setAttribute: function(elt, name, value){
        if (this.isCollection(elt)) {
            for(let j=0; j<elt.length;j++) {
                elt[j].setAttribute(name, value);
            }
        } else {
            elt.setAttribute(name, value);
        }
    },
    removeAttribute: function(elt, name){
        if (this.isCollection(elt)) {
            for(let j=0; j<elt.length;j++) {
                elt[j].removeAttribute(name);
            }
        } else {
            elt.removeAttribute(name);
        }
    },
    /**
     * @param {Element} elt
     */
    isCollection: function(elt) {
        if (typeof HTMLCollection != "undefined" && elt instanceof HTMLCollection) {
            return true;
        }
        if (typeof NodeList != "undefined" && elt instanceof NodeList) {
            return true;
        }
        if (elt instanceof Array) {
            return true;
        }
        if (elt.length !== undefined && (elt.localName === undefined || elt.localName.toLowerCase() === 'select')) {
            return true;
        }
        return false;
    }
};

export default jFormsJQ;
