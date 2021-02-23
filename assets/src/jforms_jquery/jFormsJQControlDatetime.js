/**
 * @author       Laurent Jouanneau
 * @contributor  Julien Issler, Nigoki
 * @copyright    2007-2020 Laurent Jouanneau
 * @copyright    2008-2015 Julien Issler, 2009 Nigoki
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

import $ from 'jquery';
/**
 * control with Datetime
 */
export default function jFormsJQControlDatetime(name, label) {
    this.name = name;
    this.label = label;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.minDate = null;
    this.maxDate = null;
    this.multiFields = false;
    this.readOnly = false;
}

jFormsJQControlDatetime.prototype.check = function (val, jfrm) {
    let t = val.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})(:(\d{2}))?$/);
    if(t == null) return false;
    let yy = parseInt(t[1],10);
    let mm = parseInt(t[2],10) -1;
    let dd = parseInt(t[3],10);
    let th = parseInt(t[4],10);
    let tm = parseInt(t[5],10);
    let ts = 0;
    if(t[7] != null && t[7] != "") {
        ts = parseInt(t[7],10);
    }
    let dt = new Date(yy,mm,dd,th,tm,ts);
    if(yy != dt.getFullYear() || mm != dt.getMonth() || dd != dt.getDate() || th != dt.getHours() || tm != dt.getMinutes() || ts != dt.getSeconds())
        return false;
    else if((this.minDate !== null && val < this.minDate) || (this.maxDate !== null && val > this.maxDate))
        return false;
    return true;
};
jFormsJQControlDatetime.prototype.getValue = function(){
    if (!this.multiFields) {
        let val = $.trim($('#'+this.formName+'_'+this.name).val());
        return (val!==''?val:null);
    }

    let controlId = '#'+this.formName+'_'+this.name;
    let v = $(controlId+'_year').val() + '-'
        + $(controlId+'_month').val() + '-'
        + $(controlId+'_day').val() + ' '
        + $(controlId+'_hour').val() + ':'
        + $(controlId+'_minutes').val();

    let secondsControl = $('#'+this.formName+'_'+this.name+'_seconds');
    if (secondsControl.attr('type') !== 'hidden') {
        v += ':'+secondsControl.val();
        if (v === '-- ::') {
            return null;
        }
    }
    else if (v === '-- :')
        return null;
    return v;
};
jFormsJQControlDatetime.prototype.deactivate = function(deactivate){
    let controlId = '#'+this.formName+'_'+this.name;
    if(deactivate){
        if (!this.multiFields)
            $(controlId).attr('disabled','disabled').addClass('jforms-disabled').trigger('jFormsActivateControl', false);
        else{
            $(controlId+'_year').attr('disabled','disabled').addClass('jforms-disabled');
            $(controlId+'_month').attr('disabled','disabled').addClass('jforms-disabled');
            $(controlId+'_day').attr('disabled','disabled').addClass('jforms-disabled');
            $(controlId+'_hour').attr('disabled','disabled').addClass('jforms-disabled');
            $(controlId+'_minutes').attr('disabled','disabled').addClass('jforms-disabled');
            $(controlId+'_seconds').attr('disabled','disabled').addClass('jforms-disabled');
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
            $(controlId+'_hour').removeAttr('disabled').removeClass('jforms-disabled');
            $(controlId+'_minutes').removeAttr('disabled').removeClass('jforms-disabled');
            $(controlId+'_seconds').removeAttr('disabled').removeClass('jforms-disabled');
            $(controlId+'_hidden').trigger('jFormsActivateControl', true);
        }
    }
};
