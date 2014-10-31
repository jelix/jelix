<?php
/**
 * minify controller for a jelix application
 *
 * @author Laurent Jouanneau
 */

class jelixMinify {

    static function getDocumentRoot() {
        if (isset($_SERVER['DOCUMENT_ROOT']))
            return $_SERVER['DOCUMENT_ROOT'];

        $config = parse_ini_file(jApp::mainConfigFile());

        $urlengine = $config['urlengine'];

        if($urlengine['scriptNameServerVariable'] == '') {
            $urlengine['scriptNameServerVariable'] = jConfigCompiler::findServerName('.php');
        }
        $urlScript = $_SERVER[$urlengine['scriptNameServerVariable']];
        $lastslash = strrpos ($urlScript, '/');

        $urlScriptPath = substr ($urlScript, 0, $lastslash ).'/';

        $basepath = $urlengine['basePath'];
        if ($basepath == '') {
            // for beginners or simple site, we "guess" the base path
            $basepath = $urlScriptPath;
        }
        elseif ($basepath != '/') {
            if($basepath[0] != '/') $basepath='/'.$basepath;
            if(substr($basepath,-1) != '/') $basepath.='/';

            if(strpos($urlScriptPath, $basepath) !== 0){
                throw new Exception('Jelix Error: basePath ('.$basepath.') in config file doesn\'t correspond to current base path. You should setup it to '.$urlengine['urlScriptPath']);
            }
        }

        if ($basepath == '/')
            return jApp::wwwPath();

        if(strpos(jApp::wwwPath(), $basepath) === false){
            return jApp::wwwPath();
        }

        return substr(jApp::wwwPath(), 0, - (strlen($basepath)));
    }
}

// ============ configuration of Minify

if (!isset($min_allowDebugFlag))
    $min_allowDebugFlag = false;

if (!isset($min_errorLogger))
    $min_errorLogger = false;

$min_enableBuilder = false;

$min_cachePath = jApp::tempPath('minify/');
if (!file_exists($min_cachePath))
    mkdir($min_cachePath, 0775);

if (!isset($min_documentRoot))
    $min_documentRoot = jelixMinify::getDocumentRoot();

if (!isset($min_cacheFileLocking))
    $min_cacheFileLocking = true;

if (!isset($min_serveOptions['bubbleCssImports']))
    $min_serveOptions['bubbleCssImports'] = false;

if (!isset($min_serveOptions['maxAge']))
    $min_serveOptions['maxAge'] = 1800;

$min_serveOptions['minApp']['groupsOnly'] = false;

if (!isset($min_serveOptions['minApp']['maxFiles']))
    $min_serveOptions['minApp']['maxFiles'] = 10;

if (!isset($min_symlinks))
    $min_symlinks = array();

if (!isset($min_uploaderHoursBehind))
    $min_uploaderHoursBehind = 0;

if (!isset($min_customConfigPaths)) {
    $min_customConfigPaths = array(
        'groups' => jApp::configPath('minifyGroupsConfig.php')
    );
}

ini_set('zlib.output_compression', '0');

// =========================== run Minify

require(__DIR__.'/../../vendor/mrclay/minify/min/index.php');
