{
    "name": "%%appname%%",
    "type": "application",
    "description": "",
    "homepage": "%%default_website%%",
    "license": "",
    "authors": [
        {
            "name": "%%default_creator_name%%"
           
        }
    ],
    "require": {
        "php": ">=7.2",
        "jelix/composer-module-setup": "^0.5.0"
    },
    "autoload": {
        "files": [ "%%rp_jelix%%init.php"  ]
    },
    "minimum-stability": "stable",
    "extra" : {
        "jelix": {
            "modules-dir" : [
                "modules/",
                "%%rp_lib%%jelix-modules",
                "%%rp_lib%%jelix-admin-modules"
            ],
            "plugins-dir" : [
                "plugins/",
                "%%rp_lib%%jelix-plugins"
            ]
        }
    }
}
