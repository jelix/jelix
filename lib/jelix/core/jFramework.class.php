<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2015 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jFramework
{
    protected static $_version = null;

    public static function version()
    {
        if (self::$_version === null) {
            self::$_version = trim(str_replace(
                array('SERIAL', "\n"),
                array('0', ''),
                file_get_contents(__DIR__.'/../VERSION')
            ));
        }

        return self::$_version;
    }

    public static function versionMax()
    {
        $v = self::version();

        return preg_replace('/^[0-9]+\.[0-9]+\.([a-z0-9\-\.]+)$/i', '.*', $v);
    }
}
