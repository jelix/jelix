# Changes into Jelix 2.0

- minimum PHP version is 7.3.0
- upgrade PHPUnit to 8.5.0

- Many Jelix classes are now under a namespace, but some classes with old names
  still exist to ease the transition, although it is recommanded to use new name
  as these old classes are deprecated.

- `jApp::coord()` is replaced by `\Jelix\Core\App::router()`

- Jelix config is able to read namespaces declaration as in composer.json

- project.xml is replaced by jelix-app.json
- module.xml is replaced by composer.json
- Installer internal API have been changed

- module.xml: 'creator' and 'contributor' elements changed to 'author'
- module.xml: 'minversion' and 'maxversion' are changed to 'version'
    Same syntax in this new attribute as in composer.json

- Composer package: module name are now normalized. The module name is now the
  package name with the `/` replaced by `_`. Except if the module name is
  indicated into the composer file in `composer.extra.jelix.moduleName`.

- Remove support of infoIDSuffix from jelix-scripts.ini files

## internal

- `$GLOBALS['JELIX_EVENTS']` does not exists anymore

## removed classes and methods

- `jJsonRpc`