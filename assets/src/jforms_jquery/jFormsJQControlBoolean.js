/**
 * @author       Laurent Jouanneau
 * @contributor  Julien Issler
 * @copyright    2007-2020 Laurent Jouanneau
 * @copyright    2008-2015 Julien Issler
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */



/**
 * control with boolean
 */
export default function jFormsJQControlBoolean(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.readOnly = false;
}

jFormsJQControlBoolean.prototype.check = function (val, jfrm) {
    return (val == true || val == false);
};
