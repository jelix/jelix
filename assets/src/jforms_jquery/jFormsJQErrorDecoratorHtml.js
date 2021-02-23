/**
 * @author       Laurent Jouanneau
 * @copyright    2007-2020 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

import $ from 'jquery';

export default function jFormsJQErrorDecoratorHtml(){
    this.message = '';
}


jFormsJQErrorDecoratorHtml.prototype = {
    start : function(form){
        this.message = '';
        this.form = form;
        $("#"+form.name+" .jforms-error").removeClass('jforms-error');
        $('#'+this.form.name+'_errors').empty().hide();
    },
    addError : function(control, messageType){
        var elt = this.form.element.elements[control.name];
        if (elt && elt.nodeType) {
            $(elt).addClass('jforms-error');
        }
        var name = control.name.replace(/\[\]/, '');
        $("#"+this.form.name+"_"+name+"_label").addClass('jforms-error');

        if(messageType == 1){
            this.message  += '<li class="error"> '+control.errRequired + "</li>";
        }else if(messageType == 2){
            this.message  += '<li class="error"> ' +control.errInvalid + "</li>";
        }else{
            this.message  += '<li class="error"> Error on \''+control.label+"' </li>";
        }
    },
    end : function(){
        var errid = this.form.name+'_errors';
        var ul = document.getElementById(errid);
        if(this.message != ''){
            if (!ul) {
                ul = document.createElement('ul');
                ul.setAttribute('class', 'jforms-error-list');
                ul.setAttribute('id', errid);
                $(this.form.element).first().before(ul);
            }
            var jul = $(ul);
            location.hash = "#"+errid;
            jul.hide().html(this.message).fadeIn();
        }
        else if (ul) {
            $(ul).hide();
        }
    }
};
