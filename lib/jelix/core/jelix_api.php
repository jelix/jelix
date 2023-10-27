<?php

/**
 * pure PHP version of API provided by the extension of Jelix for PHP.
 *
 * @package      jelix
 * @subpackage   core
 *
 * @author       Laurent Jouanneau
 * @copyright    2012 Laurent Jouanneau
 *
 * @see         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
if (function_exists('jelix_version')) {
    return;
}

/**
 * @param string $fileName the ini file to read
 * @param object|null $config the object that will contain properties readed from the ini file
 * @param array $ignoredSection list of ini section that must not be merged
 * @return object the updated config object
 *
 * @deprecated use \jFile::mergeIniFile() instead
 */
function jelix_read_ini($fileName, $config = null, $ignoredSection = array())
{
    return \jFile::mergeIniFile(
        $fileName,
        $config,
        $ignoredSection
    );
}

function jelix_scan_module_sel($selStr, $selObj)
{
    if (preg_match('/^(([a-zA-Z0-9_\\.]+)~)?([a-zA-Z0-9_\\.]+)$/', $selStr, $m)) {
        if ($m[1] != '' && $m[2] != '') {
            $selObj->module = $m[2];
        } else {
            $selObj->module = '';
        }
        $selObj->resource = $m[3];

        return true;
    }

    return false;
}

function jelix_scan_action_sel($selStr, $selObj, $actionName)
{
    if (preg_match('/^(?:([a-zA-Z0-9_\\.]+|\\#)~)?([a-zA-Z0-9_:]+|\\#)?(?:@([a-zA-Z0-9_]+))?$/', $selStr, $m)) {
        $m = array_pad($m, 4, '');
        $selObj->module = $m[1];
        if ($m[2] == '#') {
            $selObj->resource = $actionName;
        } else {
            $selObj->resource = $m[2];
        }
        $r = explode(':', $selObj->resource);
        if (count($r) == 1) {
            $selObj->controller = 'default';
            $selObj->method = $r[0] == '' ? 'index' : $r[0];
        } else {
            $selObj->controller = $r[0] == '' ? 'default' : $r[0];
            $selObj->method = $r[1] == '' ? 'index' : $r[1];
        }
        $selObj->resource = $selObj->controller.':'.$selObj->method;
        $selObj->request = $m[3];

        return true;
    }

    return false;
}

function jelix_scan_class_sel($selStr, $selObj)
{
    if (preg_match('/^(([a-zA-Z0-9_\\.]+)~)?([a-zA-Z0-9_\\.\\/]+)$/', $selStr, $m)) {
        $selObj->module = $m[2];
        $selObj->resource = $m[3];
        if (($p = strrpos($m[3], '/')) !== false) {
            $selObj->className = substr($m[3], $p + 1);
            $selObj->subpath = substr($m[3], 0, $p + 1);
        } else {
            $selObj->className = $m[3];
            $selObj->subpath = '';
        }

        return true;
    }

    return false;
}

function jelix_scan_locale_sel($selStr, $selObj)
{
    if (preg_match('/^(([a-zA-Z0-9_\\.]+)~)?([a-zA-Z0-9_]+)\\.([a-zA-Z0-9_\\-\\.]+)$/', $selStr, $m)) {
        if ($m[1] != '' && $m[2] != '') {
            $selObj->module = $m[2];
        } else {
            $selObj->module = '';
        }
        $selObj->resource = $m[3];
        $selObj->fileKey = $m[3];
        $selObj->messageKey = $m[4];

        return true;
    }

    return false;
}
