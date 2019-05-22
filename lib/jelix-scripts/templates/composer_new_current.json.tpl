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
        "php": ">=5.6",
        "jelix/composer-module-setup": "^0.5.0",
        "jelix/jelix-essential" : "@dev"
    },
    "minimum-stability": "stable"
}
