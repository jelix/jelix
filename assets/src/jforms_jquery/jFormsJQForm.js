/**
 * @author       Laurent Jouanneau
 * @contributor  Julien Issler, Vincent viaud, Steven Jehannet
 * @copyright    2007-2020 Laurent Jouanneau
 * @copyright    2008-2015 Julien Issler, 2011 Steven Jehannet, 2010 Vincent viaud
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

import jFormsJQErrorDecoratorHtml from './jFormsJQErrorDecoratorHtml.js';
import jFormsJQ from './jFormsJQ.js';
import $ from 'jquery';

function jFormsJQControl(name, label) {
    /** @var {string} name the ref value of the control */
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.minLength = -1;
    this.maxLength = -1;
    this.regexp = null;
    this.readOnly = false;
}

jFormsJQControl.prototype.check = function (val, jfrm) {
    return true;
};


/**
 * represents a form
 */
export default function jFormsJQForm(name, selector, id){
    // the jelix selector corresponding to the jforms object
    this.selector = selector;

    // the jforms id (id given to jForms::get())
    this.formId = id;

    // the value of the id attribute: jforms_<module>_<name>
    this.name = name;

    this.controls = [];
    this.errorDecorator =  new jFormsJQErrorDecoratorHtml();
    this.element = $('#'+name).get(0);

    // list of dependencies. Values are list of controls to update when a
    // control (key is its name) is modified
    this.allDependencies = {};
    this.updateInProgress = false;
    this.controlsToUpdate = [];
    this.preSubmitHandlers = [];
    this.postSubmitHandlers = [];
}


jFormsJQForm.prototype={
    /**
     * @param {jFormsJQControl} ctrl
     */
    addControl : function(ctrl){
        this.controls.push(ctrl);
        ctrl.formName = this.name;
    },

    setErrorDecorator : function (decorator){
        this.errorDecorator = decorator;
    },

    /**
     * @param {String} aControlName the ref value of the control
     * @return {jFormsJQControl}
     */
    getControl : function(aControlName) {
        var ctrls = this.controls;
        for(var i=0; i < ctrls.length; i++){
            if (ctrls[i].name == aControlName) {
                return ctrls[i];
            }
            else if (ctrls[i].getChild){
                var child = ctrls[i].getChild(aControlName);
                if (child)
                    return child;
            }
        }
        return null;
    },

    /**
     * declare a list as a dynamic list: its possible values change when an
     * other control is modified.
     * @param {String} controlName the ref value of the control corresponding to the html list to update
     */
    declareDynamicFill : function (controlName) {
        var ctrl = this.getControl(controlName);
        // dependencies property contains name of controls that provide values
        // used as criterion to retrieve list of values for controlName
        if (!ctrl.dependencies)
            return;

        var me = this;
        // the control has some dependencies : we put a listener
        // on these dependencies, so when these dependencies
        // change, we retrieve the new content of the control
        for(var i=0; i< ctrl.dependencies.length; i++) {
            var depName = ctrl.dependencies[i];
            var dep = this.element.elements[depName];
            if (this.allDependencies[depName] === undefined) {
                this.allDependencies[depName] = [controlName];
                $(dep).change(function() {
                    me.updateLinkedElements(depName);
                });
            }
            else {
                this.allDependencies[depName].push(controlName);
            }
        }
    },

    /**
     * update the given list that depends on another control.
     *
     * Useful if you know that this list has changed at the backend side.
     *
     * @param {String} controlName the ref value of the list to update
     */
    updateDynamicList : function(controlName) {
        var ctrl = this.getControl(controlName);
        if (!ctrl.dependencies) {
            return;
        }
        this.controlsToUpdate.push(controlName);
        this.dynamicFillAjax();
    },

    /**
     * update the content of all elements which depends on the value of the given
     * control
     * @param {String} controlName the ref value of the control
     */
    updateLinkedElements : function (controlName) {
        if (this.updateInProgress) // we don't want to call same ajax request...
            return;
        this.updateInProgress = true;
        this.buildOrderedControlsList(controlName);
        // we now have the list of controls to update, in the reverse order
        // let's start the update
        this.dynamicFillAjax();
    },

    /**
     *
     * @param {String} controlName the ref value of the control
     */
    buildOrderedControlsList : function(controlName) {
        // we should build a graph, to update elements in the right order
        this.controlsToUpdate = [];
        var alreadyCheckedControls = [];
        var checkedCircularDependency = [];
        var me = this;
        var buildListDependencies = function (controlName) {
            if (checkedCircularDependency[controlName] === true)
                throw "Circular reference !";
            checkedCircularDependency[controlName] = true;

            var list = me.allDependencies[controlName];
            if (list !== undefined) {
                for (var j=0; j< list.length; j++) {
                    if (alreadyCheckedControls[list[j]] !== true) {
                        buildListDependencies(list[j]);
                    }
                }
            }
            checkedCircularDependency[controlName] = false;
            alreadyCheckedControls[controlName] = true;
            me.controlsToUpdate.push(controlName);
        };

        var list = this.allDependencies[controlName];
        if (list !== undefined) {
            for (var i=0; i< list.length; i++) {
                checkedCircularDependency = [];
                if (alreadyCheckedControls[list[i]] !== true) {
                    buildListDependencies(list[i]);
                }
            }
        }
    },

    /**
     * It sends the values of dependencies of a control,
     * and then we retrieve the new values of this control
     */
    dynamicFillAjax : function () {
        var ctrlname = this.controlsToUpdate.pop();
        if (!ctrlname) {
            this.updateInProgress = false;
            this.controlsToUpdate = [];
            return;
        }
        var ctrl = this.getControl(ctrlname);
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
            '__ref' : ctrl.name.replace('[]','')
        };

        for(var i=0; i< ctrl.dependencies.length; i++) {
            var n = ctrl.dependencies[i];
            param[n] = jFormsJQ.getValue(this.element.elements[n]);
        }

        var elt = this.element.elements[ctrl.name];
        var eltValue = elt.value;
        var me = this;

        $.post(jFormsJQ.selectFillUrl, param,
            function(data){
                if(elt.nodeType && elt.nodeName.toLowerCase() == 'select') {
                    var select = $(elt).eq(0);
                    var emptyitem = select.children('option[value=""]').detach();
                    select.empty();
                    if(emptyitem)
                        select.append(emptyitem);
                    $.each(data, function(i, item){
                        if (emptyitem && item.value == '') {
                            // do not add empty item if it already exists.
                            return;
                        }
                        if(typeof item.items == 'object'){
                            select.append('<optgroup label="'+item.label+'"/>');
                            var optgroup = select.children('optgroup[label="'+item.label+'"]').eq(0);
                            $.each(item.items, function(i,item){
                                 optgroup.append('<option value="'+item.value+'"'+(item.value == eltValue ? ' selected="selected"' : '')+'>'+item.label+'</option>');
                            });
                        }
                        else
                            select.append('<option value="'+item.value+'"'+(item.value == eltValue ? ' selected="selected"' : '')+'>'+item.label+'</option>');
                    });
                }
                if (me.controlsToUpdate.length) {
                    me.dynamicFillAjax();
                }
                else {
                    me.updateInProgress = false;
                }
            }, "json");
    },

    addSubmitHandler : function (handler, beforeCheck) {
        if (beforeCheck) {
            this.preSubmitHandlers.push(handler);
        }
        else
            this.postSubmitHandlers.push(handler);
    }
};
