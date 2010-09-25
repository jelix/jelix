
function jelix_wymeditor_default(textarea_id, form_id, skin, config) {

  jQuery(function() {
      jQuery("#"+textarea_id).wymeditor({
        updateSelector:    "#"+form_id,
        updateEvent:       'jFormsUpdateFields'
    });
  });

}