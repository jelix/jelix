<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2015-2025 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Services\Database;
use Jelix\Database\Connection;


class DbProfilePlugin extends \Jelix\Database\ProfilePlugin\DbProfilePlugin
{
    protected $accessOptions = array(
        'filePathParser' => '\\Jelix\\Services\\Database\\DbProfilePlugin::parseSqlitePath'
    );

    public function getInstanceForPool($name, $profile)
    {
        $connector = Connection::createWithNormalizedParameters($profile);
        if (isset($profile['debug']) && $profile['debug']) {
            $logger = new DbLogger();
            $queryLogger = new SQLLogMessage($logger);
            $connector->setQueryLogger($queryLogger);
        }

        return $connector;
    }

    /**
     * @param $path
     *
     * @return string
     * @throws \Exception
     * @see DbProfilePlugin
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
            $path = \Jelix\Core\App::varPath('db/sqlite3/'.$path);
        }
        return $path;
    }

}
