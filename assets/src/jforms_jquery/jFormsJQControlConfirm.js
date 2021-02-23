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
 * confirm control
 */
export default function jFormsJQControlConfirm(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this._masterControl = name.replace(/_confirm$/,'');
}

jFormsJQControlConfirm.prototype.check = function(val, jfrm) {
    if(jFormsJQ.getValue(jfrm.element.elements[this._masterControl]) !== val)
        return false;
    return true;
};
