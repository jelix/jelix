/**
 * @package      jelix
 * @subpackage   forms
 * @author       Laurent Jouanneau
 * @contributor  Julien Issler
 * @copyright    2007-2020 Laurent Jouanneau
 * @copyright
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


/**
 * control with Url
 */
export default function jFormsJQControlUrl(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.readOnly = false;
}

jFormsJQControlUrl.prototype.check = function (val, jfrm) {
    return (val.search(/^[a-z]+:\/\/((((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9-])+\.)+[A-Za-z-]+))((\/)|$)/) != -1);
};
