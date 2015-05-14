{
    "name":"%%appname%",
    "version": "0.1pre",
    "createdate":"%%createdate%%",
    "date": "%%createdate%%",
    "label": "%%appname%%",
    "description": "",
    "homepage":"%%default_website%%",
    "license":"%%default_license%%",
    "licenseURL": "%%default_license_url%%"
    "copyright": "%%default_copyright%%",
    "authors":[
        { "name":"%%default_creator_name%%", "email":"%%default_creator_email%%" }
    ],
    "directories": {
        "config":"%%rp_conf%%",
        "var":"%%rp_var%%",
        "www":"%%rp_www%%",
        "log":"%%rp_log%%",
        "temp":"%%rp_temp%%"
    },
    "entrypoints" : [
        { "file": "index.php", "config":"index/config.ini.php", "type": "classic"}
    ]
}