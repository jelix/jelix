/**
 * @author       Laurent Jouanneau
 * @copyright    2007-2023 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */



/**
 * Decorator to display errors in an alert dialog box
 */
export default function jFormsJQErrorDecoratorAlert(){
    this.message = '';
}


jFormsJQErrorDecoratorAlert.prototype = {
    start : function(form){
        this.message = '';
    },
    addError : function(control, messageType){
        if(messageType == 1){
            this.message  +="* "+control.errRequired + "\n";
        }else if(messageType == 2){
            this.message  +="* "+control.errInvalid + "\n";
        }else{
            this.message  += "* Error on '"+control.label+"' field\n";
        }
    },
    end : function(){
        if(this.message != ''){
            alert(this.message);
        }
    },
    clean: function() {

    }
};
