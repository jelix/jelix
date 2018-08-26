<?php
/**
* @author   Laurent Jouanneau
* @copyright 2014-2018 Laurent Jouanneau
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Checker;

/**
 * Allow to access to some localized messages
 */
class Messages extends \Jelix\SimpleLocalization\Container {

    /**
     * @param $lang
     * @param string|array  list of path (or a single path) to files containing messages.
     *     if the path contains %LANG%, there should be a file for each lang. The content
     *     should be in an associative array key => translation. Else it contains translation
     *     for several lang, so the array should be array('lang code'=> array('key1'=>'message',...))
     */
    function __construct($lang='', $langFilePath=''){
        if ($langFilePath == '') {
            $langFilePath = __DIR__.'/installmessages.%LANG%.inc.php';
        }
        parent::__construct($langFilePath, $lang);
    }
}
