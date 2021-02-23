/**
 * @author       Laurent Jouanneau
 * @copyright    2007-2020 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


/**
 * control with ipv6
 */
export default function jFormsJQControlIpv6(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.readOnly = false;
}

jFormsJQControlIpv6.prototype.check = function (val, jfrm) {
    return (val.search(/^([a-f0-9]{1,4})(:([a-f0-9]{1,4})){7}$/i) != -1);
};
