
[entrypoint:index.php]
config="index/config.ini.php"

[entrypoint:rest.php]
config="rest/config.ini.php"
type=classic

[module:complex]
enabled=on

[module:simple]
enabled=on
installparam[foo] = bar

[module:package]
enabled=off
