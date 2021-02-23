/**
 * @author       Adrien Lagroy
 * @contributor  Laurent Jouanneau
 * @copyright    2020 Adrien Lagroy, 2020 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
import $ from 'jquery';

/**
 * control with time for jForms
 */
export default function jFormsJQControlTime2(name, label) {
    this.name = name;
    this.label = label;
    this.multiFields = false;
    this.required = false;
    this.errInvalid = '';
    this.errRequired = '';
    this.minTime = null;
    this.maxTime = null;
    this.readOnly = false;
}

jFormsJQControlTime2.prototype.check = function (val, jfrm) {
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
    else
        return true;
};
jFormsJQControlTime2.prototype.getValue = function(){
    if (!this.multiFields) {
        var val = $.trim($('#'+this.formName+'_'+this.name).val());
        return (val !=='' ? val : null);
    }

    var controlId = '#' + this.formName + '_' + this.name;
    var v = $(controlId+'_hour').val() + ':'
        + $(controlId+'_minutes').val();

    var secondsControl = $('#'+this.formName+'_'+this.name+'_seconds');
    if(secondsControl.attr('type') !== 'hidden'){
        v += ':'+secondsControl.val();
        if(v == '::')
            return null;
    }
    else if(v == ':')
        return null;
    return v;
};
jFormsJQControlTime2.prototype.deactivate = function(deactivate){
    var controlId = '#' + this.formName + '_' + this.name;
    if(deactivate){
        if (!this.multiFields)
            $(controlId).attr('disabled','disabled').addClass('jforms-disabled').trigger('jFormsActivateControl', false);
        else{
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
            $(controlId+'_hour').removeAttr('disabled').removeClass('jforms-disabled');
            $(controlId+'_minutes').removeAttr('disabled').removeClass('jforms-disabled');
            $(controlId+'_seconds').removeAttr('disabled').removeClass('jforms-disabled');
            $(controlId+'_hidden').trigger('jFormsActivateControl', true);
        }
    }
};
