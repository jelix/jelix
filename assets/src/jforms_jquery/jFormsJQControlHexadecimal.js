/**
 * @author       Laurent Jouanneau
 * @copyright    2007-2020 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


/**
 * control with Hexadecimal
 */
export default function jFormsJQControlHexadecimal(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.readOnly = false;
}

jFormsJQControlHexadecimal.prototype.check = function (val, jfrm) {
    return (val.search(/^0x[a-f0-9A-F]+$/) != -1);
};
