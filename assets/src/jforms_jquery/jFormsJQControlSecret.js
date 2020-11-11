/**
 * @author       Laurent Jouanneau
 * @contributor  Julien Issler
 * @copyright    2007-2020 Laurent Jouanneau
 * @copyright    2008-2015 Julien Issler
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


/**
 * control for secret input
 */
export default function jFormsJQControlSecret(name, label) {
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

jFormsJQControlSecret.prototype.check = function (val, jfrm) {
    if(this.minLength != -1 && val.length < this.minLength)
        return false;
    if(this.maxLength != -1 && val.length > this.maxLength)
        return false;
    if (this.regexp && !this.regexp.test(val))
        return false;
    return true;
};
