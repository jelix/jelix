jQuery(function($){
  var d=$.datepicker.regional['en'];
  d.buttonText = 'Open the calendar';
  d.resetButtonText = 'Reset the date';
  $.datepicker.setDefaults($.datepicker.regional['en']);
});
