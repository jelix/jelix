/**
 * @author       Laurent Jouanneau
 * @contributor  Julien Issler
 * @copyright    2007-2020 Laurent Jouanneau
 * @copyright    2008-2015 Julien Issler
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

import jFormsJQ from './jFormsJQ.js';

/**
 * choice control
 */
export default function jFormsJQControlChoice(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.items = {};
    this.readOnly = false;
}

jFormsJQControlChoice.prototype = {
    addControl : function (ctrl, itemValue) {
        if (this.items[itemValue] === undefined) {
            this.items[itemValue] = [];
        }
        if (ctrl) { // a choice item can be empty
            this.items[itemValue].push(ctrl);
            ctrl.formName = this.formName;
        }
    },
    getChild : function (aControlName) {
        for (var it in this.items) {
            for (var i=0; i < this.items[it].length; i++) {
                var c = this.items[it][i];
                if (c.name == aControlName)
                    return c;
            }
        }
        return null;
    },
    check : function (val, jfrm) {
        if(this.items[val] == undefined)
            return false;

        var list = this.items[val];
        var valid = true;
        for(var i=0; i < list.length; i++) {
            var ctrlvalid = jFormsJQ.verifyControl(list[i], jfrm);
            if (!ctrlvalid)
                valid = false;
        }
        return valid;
    },
    activate : function (val) {
        var frmElt = document.getElementById(this.formName);
        for(var j in this.items) {
            var list = this.items[j];
            var htmlItem = document.getElementById(this.formName+'_'+this.name+'_'+j+'_item');
            if (htmlItem) {
                if (val == j) {
                    jFormsJQ.addClass(htmlItem, "jforms-selected");
                    jFormsJQ.removeClass(htmlItem, "jforms-notselected");
                }
                else {
                    jFormsJQ.removeClass(htmlItem, "jforms-selected");
                    jFormsJQ.addClass(htmlItem, "jforms-notselected");
                }
            }
            for(var i=0; i < list.length; i++) {
                var ctl = list[i];
                if(typeof ctl.deactivate == 'function'){
                    if (ctl.readOnly)
                        ctl.deactivate(true);
                    else
                        ctl.deactivate(val != j);
                    continue;
                }
                var elt = frmElt.elements[ctl.name];
                if (val == j && !ctl.readOnly) {
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
