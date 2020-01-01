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
    "repositories": [
        {
            "type": "path",
            "url": "%%rp_lib%%"
        }
    ],
    "require": {
        "php": ">=7.2",
        "jelix/composer-module-setup": "^0.5.0",
        "jelix/jelix-essential" : "@dev"
    },
    "minimum-stability": "stable"
}
