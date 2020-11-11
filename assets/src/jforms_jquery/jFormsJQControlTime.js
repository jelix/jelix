/**
 * @author       Laurent Jouanneau
 * @copyright    2007-2020 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


/**
 * control with time
 */
export default function jFormsJQControlTime(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
}

jFormsJQControlTime.prototype.check = function (val, jfrm) {
    var t = val.match(/^(\d{2}):(\d{2})(:(\d{2}))?$/);
    if(t == null) return false;
    var th = parseInt(t[1],10);
    var tm = parseInt(t[2],10);
    var ts = 0;
    if(t[4] != null)
        ts = parseInt(t[4],10);
    var dt = new Date(2007,5,2,th,tm,ts);
    if(th != dt.getHours() || tm != dt.getMinutes() || ts != dt.getSeconds())
        return false;
    else if((this.minTime !== null && val < this.minTime) || (this.maxTime !== null && val > this.maxTime))
        return false;
    else
        return true;
};
