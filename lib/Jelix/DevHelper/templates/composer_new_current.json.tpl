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
        "php": ">=7.4",
        "jelix/composer-module-setup": "^1.0.5",
        "jelix/jelix-essential" : "@dev"
    },
    "minimum-stability": "stable"
}
