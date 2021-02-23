/**
 * @author       Laurent Jouanneau
 * @contributor  Julien Issler
 * @copyright    2007-2020 Laurent Jouanneau
 * @copyright    2008-2015 Julien Issler
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
import $ from 'jquery';

/**
 * control with Date
 */
export default function jFormsJQControlDate(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.multiFields = false;
    this.minDate = null;
    this.maxDate = null;
    this.readOnly = false;
}

jFormsJQControlDate.prototype.check = function (val, jfrm) {
    let t = val.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if(t == null) return false;
    let yy = parseInt(t[1],10);
    let mm = parseInt(t[2],10) -1;
    let dd = parseInt(t[3],10);
    let dt = new Date(yy,mm,dd,0,0,0);
    if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate())
        return false;
    else if((this.minDate !== null && val < this.minDate) || (this.maxDate !== null && val > this.maxDate))
        return false;
    return true;
};
jFormsJQControlDate.prototype.getValue = function(){
    if (!this.multiFields) {
        let val = $.trim($('#'+this.formName+'_'+this.name).val());
        return (val!==''?val:null);
    }

    let controlId = '#'+this.formName+'_'+this.name;
    let v = $(controlId+'_year').val() + '-'
        + $(controlId+'_month').val() + '-'
        + $(controlId+'_day').val();
    if (v === '--')
        return null;
    return v;
};
jFormsJQControlDate.prototype.deactivate = function(deactivate){
    let controlId = '#'+this.formName+'_'+this.name;
    if(deactivate){
        if (!this.multiFields)
            $(controlId).attr('disabled','disabled').addClass('jforms-disabled').trigger('jFormsActivateControl', false);
        else{
            $(controlId+'_year').attr('disabled','disabled').addClass('jforms-disabled');
            $(controlId+'_month').attr('disabled','disabled').addClass('jforms-disabled');
            $(controlId+'_day').attr('disabled','disabled').addClass('jforms-disabled');
            $(controlId+'_hidden').trigger('jFormsActivateControl', false);
        }
    }
    else{
        if (!this.multiFields)
            $(controlId).removeAttr('disabled').removeClass('jforms-disabled').trigger('jFormsActivateControl', true);
        else{
            $(controlId+'_year').removeAttr('disabled').removeClass('jforms-disabled');
            $(controlId+'_month').removeAttr('disabled').removeClass('jforms-disabled');
            $(controlId+'_day').removeAttr('disabled').removeClass('jforms-disabled');
            $(controlId+'_hidden').trigger('jFormsActivateControl', true);
        }
    }
};
