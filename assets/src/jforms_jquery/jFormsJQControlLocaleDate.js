/**
 * @author       Laurent Jouanneau
 * @copyright    2007-2020 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


/**
 * control with localedate
 */
export default function jFormsJQControlLocaleDate(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.lang='';
    this.readOnly = false;
}

jFormsJQControlLocaleDate.prototype.check = function (val, jfrm) {
    let yy, mm, dd, t;
    if (this.lang.indexOf('fr_') === 0) {
        t = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (t == null) return false;
        yy = parseInt(t[3],10);
        mm = parseInt(t[2],10) -1;
        dd = parseInt(t[1],10);
    }else{
        //default is en_* format
        t = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (t == null) return false;
        yy = parseInt(t[3],10);
        mm = parseInt(t[1],10) -1;
        dd = parseInt(t[2],10);
    }
    let dt = new Date(yy,mm,dd,0,0,0);
    if (yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate())
        return false;
    else
        return true;
};
