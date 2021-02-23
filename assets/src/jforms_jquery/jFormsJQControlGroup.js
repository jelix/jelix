/**
 * @author       Laurent Jouanneau
 * @copyright    2007-2020 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

import jFormsJQ from './jFormsJQ.js';
/**
 * group control
 */
export default function jFormsJQControlGroup(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.readOnly = false;
    this.children = [];
    this.hasCheckbox = false;
}

jFormsJQControlGroup.prototype = {
    addControl : function (ctrl, itemValue) {
        this.children.push(ctrl);
        ctrl.formName = this.formName;
    },
    getChild : function (aControlName) {
        for (var i=0; i < this.children.length; i++) {
            var c = this.children[i];
            if (c.name == aControlName)
                return c;
        }
        return null;
    },
    check : function (val, jfrm) {
        if (this.hasCheckbox) {
            var chk = document.getElementById(this.formName+'_'+this.name+'_checkbox');
            if (!chk.checked) {
                return true;
            }
        }
        var valid = true;
        for(var i=0; i < this.children.length; i++) {
            var ctrlvalid = jFormsJQ.verifyControl(this.children[i], jfrm);
            if (!ctrlvalid) {
                valid = false;
            }
        }
        return valid;
    },
    activate: function(yes) {
        var checkboxItem = document.getElementById(this.formName+'_'+this.name+'_checkbox');
        if (checkboxItem) {
            if (yes) {
                checkboxItem.setAttribute('checked', 'true');
            }
            else {
                checkboxItem.removeAttribute('checked');
            }
            this.showActivate();
        }
    },
    showActivate : function () {
        var checkboxItem = document.getElementById(this.formName+'_'+this.name+'_checkbox');
        if (!this.hasCheckbox || !checkboxItem) {
            return;
        }
        var fieldset = document.getElementById(this.formName+'_'+this.name);
        var frmElt = document.getElementById(this.formName);

        var toactivate = checkboxItem.checked;
        if (toactivate) {
            jFormsJQ.removeClass(fieldset, "jforms-notselected");
        }
        else {
            jFormsJQ.addClass(fieldset, "jforms-notselected");
        }
        for(var i=0; i < this.children.length; i++) {
            var ctl = this.children[i];
            if(typeof ctl.deactivate == 'function'){
                if (ctl.readOnly) {
                    ctl.deactivate(true);
                } else {
                    ctl.deactivate(!toactivate);
                }
                continue;
            }
            var elt = frmElt.elements[ctl.name];
            if (toactivate && !ctl.readOnly) {
                jFormsJQ.removeAttribute(elt, "disabled");
                jFormsJQ.removeClass(elt, "jforms-disabled");
            } else {
                jFormsJQ.setAttribute(elt, "disabled", "disabled");
                jFormsJQ.addClass(elt, "jforms-disabled");
            }
        }
    }
};

