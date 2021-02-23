/**
 * @package      jelix
 * @subpackage   forms
 * @author       Laurent Jouanneau
 * @copyright    2007-2020 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


/**
 * control for color code
 */
export default function jFormsJQControlColor(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.readOnly = false;
}

jFormsJQControlColor.prototype.check = function (val, jfrm) {
    return (val.search(/^#[a-f0-9A-F]{6}$/) != -1);
};

