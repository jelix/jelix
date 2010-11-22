/**
* @package      jelix
* @subpackage   forms
* @author       Julien Issler
* @contributor
* @copyright    2008-2010 Julien Issler, 2008 Dominique Papin
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

function jelix_datepicker_default(control, config){
    if(typeof this._controls !== 'undefined'){
        this._controls.push(control);
        return;
    }

    this._controls = [];
    this._controls.push(control);

    this._config = config;

    this._jsFiles = [];
    if(typeof jQuery.ui == 'undefined')
        this._jsFiles.push(this._config.jqueryPath+'ui/jquery-ui-core-widg-mous-posi.custom.min.js');
    if(typeof jQuery.datepicker == 'undefined'){
        jQuery.include(this._config.jqueryPath+'themes/base/jquery.ui.all.css');
        this._jsFiles.push(this._config.jqueryPath+'ui/jquery.ui.datepicker.min.js');
        var lang = this._config.locale.substr(0,2).toLowerCase();
        if(lang != 'en')
            this._jsFiles.push(this._config.jqueryPath+'ui/i18n/jquery.ui.datepicker-'+lang+'.js');
        this._jsFiles.push(this._config.jelixWWWPath+'js/jforms/datepickers/default/ui.'+lang+'.js');
    }
    this._start = function(){
        var config = this._config;
        jQuery.each(
            this._controls,
            function(){
                var control = this;

                var disabled = false;

                if(control.multiFields){
                    var eltId = '#'+control.formName+'_'+control.name;
                    var eltYear = jQuery(eltId+'_year').after('<input type="hidden" disabled="disabled" id="'+control.formName+'_'+control.name+'_hidden" />');
                    var eltMonth = jQuery(eltId+'_month');
                    var eltDay = jQuery(eltId+'_day');
                    var elt = jQuery(eltId+'_hidden');
                    disabled = eltYear.attr('disabled');
                }
                else{
                    var elt = jQuery('#'+control.formName+'_'+control.name);
                    disabled = elt.attr('disabled');
                }

                var params = {
                    changeMonth: true,
                    changeYear: true,
                    showButtonPanel: true,
                    showOn: "button",
                    buttonImageOnly: true,
                    buttonImage: config.jelixWWWPath+'design/jforms/calendar.gif',
                    onSelect : function(date){
                        if(!control.multiFields)
                            return;
                        eltYear.val('');
                        eltMonth.val('');
                        eltDay.val('');
                        date = date.split('-');
                        eltYear.val(date[0]);
                        eltMonth.val(date[1]);
                        eltDay.val(date[2]);
                    }
                };

                var currentYear = parseInt(new Date().getFullYear(),10);
                var yearRange = [parseInt(currentYear-10,10), parseInt(currentYear+10,10)];
                if(control.minDate){
                    var t = control.minDate.match(/^(\d{4})\-(\d{2})\-(\d{2}).*$/);
                    if(t !== null){
                        yearRange[0] = parseInt(t[1],10);
                        params.minDate = new Date(parseInt(t[1],10), parseInt(t[2],10)-1, parseInt(t[3],10));
                    }
                }
                if(control.maxDate){
                    var t = control.maxDate.match(/^(\d{4})\-(\d{2})\-(\d{2}).*$/);
                    if(t !== null){
                        yearRange[1] = parseInt(t[1],10);
                        params.maxDate = new Date(parseInt(t[1],10), parseInt(t[2],10)-1, parseInt(t[3],10));
                    }
                }
                params.yearRange = yearRange.join(':');

                if(control.multiFields)
                    params.beforeShow = function(){
                        elt.val(eltYear.val()+'-'+eltMonth.val()+'-'+eltDay.val());
                    };

                elt.datepicker(params);

                if(!control.lang)
                    elt.datepicker('change',{dateFormat:'yy-mm-dd'});

                jQuery("#ui-datepicker-div").css("z-index",999999);
                var triggerIcon = elt.parent().children('img.ui-datepicker-trigger').eq(0);

                if(!control.required){
                    triggerIcon.after(' <img class="ui-datepicker-reset" src="'+config.jelixWWWPath+'design/jforms/cross.png" alt="'+elt.datepicker('option','resetButtonText')+'"  title="'+elt.datepicker('option','resetButtonText')+'" />');
                    var cleanTriggerIcon = elt.parent().children('img').eq(1);
                    cleanTriggerIcon.click(function(e){
                        if(elt.datepicker('isDisabled'))
                            return;
                        if(control.multiFields){
                            eltYear.val('');
                            eltMonth.val('');
                            eltDay.val('');
                        }
                        elt.val('');
                    });
                }

                triggerIcon.css({'vertical-align':'text-bottom', 'margin-left':'3px'});

                elt.bind('jFormsActivateControl', function(e, val){
                    if(val){
                        jQuery(this).datepicker('enable');
                        if(!control.required)
                            cleanTriggerIcon.css('opacity','1');
                    }
                    else{
                        jQuery(this).datepicker('disable');
                        if(!control.required)
                            cleanTriggerIcon.css('opacity','0.5');
                    }
                });

                elt.trigger('jFormsActivateControl', !disabled);
                elt.blur();
            }
        );
    };
    var me = this;
    jQuery(document).ready(function(){
        if(me._jsFiles.length){
            jQuery.include(me._jsFiles, function(){ me._start(); });
        }
        else
            me._start();
    });
}