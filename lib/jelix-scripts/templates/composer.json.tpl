{
    "name": "%%appname%%",
    "type": "application",
    "description": "",
    "homepage": "%%default_website%%",
    "license": "",
    "authors": [
        {
            "name": "%%default_creator_name%%",
           
        }
    ],
    "require": {
        "php": ">=5.3.3",
        "jelix/composer-module-setup": "0.2"
    },
    "autoload": {
        "psr-0": { },
        "classmap": [ ],
        "files": [ "../lib/jelix/init.php"  ]
    },
    "minimum-stability": "stable",
    "extra" : {
        "jelix": {
            "modules-dir" : [
                "modules/",
                "../lib/jelix-modules",
                "../lib/jelix-admin-modules"
            ],
            "plugins-dir" : [
                "plugins/",
                "../lib/jelix-plugins"
            ]
        }
    }
}
