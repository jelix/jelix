/**
 * @author       Laurent Jouanneau
 * @contributor  Philippe Villiers
 * @copyright    2007-2020 Laurent Jouanneau
 * @copyright    2013 Philippe Villiers
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


/**
 * control with Integer
 */
export default function jFormsJQControlInteger(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.minValue = -1;
    this.maxValue = -1;
    this.errInvalid = '';
    this.errRequired = '';
    this.readOnly = false;
}

jFormsJQControlInteger.prototype.check = function (val, jfrm) {
    if (!(-1 !== val.search(/^\s*[+-]?\d+\s*$/))) return false;
    if (this.minValue !== -1 && parseInt(val) < this.minValue) return false;
    if (this.maxValue !== -1 && parseInt(val) > this.maxValue) return false;
    return true;
};
