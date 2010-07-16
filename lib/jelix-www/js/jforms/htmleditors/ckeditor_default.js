function jelix_ckeditor_ckdefault(textarea_id, form_id, skin, lang){
    var conf = {
            toolbar:
            [
                ['Cut','Copy','Paste','PasteText','PasteFromWord','-','SpellChecker'],
                ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                ['Maximize', 'ShowBlocks'],
                '/',
                ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                ['Link','Unlink','Anchor'],
                ['Image','Table','HorizontalRule', 'SpecialChar'],
            ]
    };
    if (skin !='default')
        conf['skin'] = skin;
    conf["language"] = lang.substr(0,2).toLowerCase();

    CKEDITOR.replace(textarea_id, conf);
}