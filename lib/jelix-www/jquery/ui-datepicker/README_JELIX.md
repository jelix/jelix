In Jelix we need only the datepicker from jQueryUI.

So JQuery UI bundled into Jelix has been built from http://jqueryui.com/download/
with only this selected features:

- all core files
- no interactions features
- only datepicker widget
- no effects
- base theme

We took also all i18n files from directly the git repository https://github.com/jquery/jquery-ui/tree/1.12.1/ui/i18n.

If you want to update files for a newer jQuery UI version to contribute into 
the Jelix repository, please keep this selection.

Else, if you want a newer jQueryUI as a user, or if you need more features of 
jQueryUI, please upload your own jQuery files to your app, not in this directory, 
and change the webassets configuration of jQueryUI (see jquery_ui.* and 
jforms_datepicker_default.* options in the configuration)



