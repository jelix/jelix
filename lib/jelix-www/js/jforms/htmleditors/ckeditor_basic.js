function jelix_ckeditor_ckbasic(textarea_id, form_id, skin, lang){
    var conf = {toolbar:'Basic',
                removePlugins : 'elementspath',
                scayt_autoStartup : false
                };
    if (skin !='default')
        conf['skin'] = skin;
    conf["language"] = lang.substr(0,2).toLowerCase();
    
    CKEDITOR.replace(textarea_id, conf);
}