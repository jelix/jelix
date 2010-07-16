function jelix_ckeditor_ckfull(textarea_id, form_id, skin, lang){
    var conf = {};
    if (skin !='default')
        conf['skin'] = skin;
    conf["language"] = lang.substr(0,2).toLowerCase();
    CKEDITOR.replace(textarea_id, conf);
}