<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2015-2025 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Services\Database;


class DbProfilePlugin extends \Jelix\Database\ProfilePlugin\DbProfilePlugin
{
    protected $accessOptions = array(
        'filePathParser' => '\\Jelix\\Services\\Database\\DbProfilePlugin::parseSqlitePath'
    );


    /**
     * @param $path
     *
     * @return string
     * @throws \Exception
     */
    public static function parseSqlitePath($path)
    {
        if (preg_match('/^(app|lib|var|temp|www)\:/', $path)) {
            $path = \jFile::parseJelixPath($path);
        } elseif ($path[0] == '/' || // *nix path
            preg_match('!^[a-z]\\:(\\\\|/)[a-z]!i', $path) // windows path
        ) {
            if (!file_exists($path) && !file_exists(dirname($path))) {
                throw new \Exception('sqlite3 connector: unknown database path scheme');
            }
        } else {
            $path = \jApp::varPath('db/sqlite3/'.$path);
        }
        return $path;
    }
}
