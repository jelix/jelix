
function jelix_wymeditor_default(textarea_id, form_id, skin, lang) {

  jQuery(function() {
      jQuery("#"+textarea_id).wymeditor({
        updateSelector:    "#"+form_id,
        updateEvent:       'jFormsUpdateFields'
    });
  });

}