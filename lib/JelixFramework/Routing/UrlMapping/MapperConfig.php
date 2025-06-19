<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

class MapperConfig
{
    protected $enableParser = true;

    protected $entryPointName = 'index';

    protected $basePath = '/';

    protected $notFoundAct = 'jelix~error:notfound';

    /**
     * for an app on a simple http server behind an https proxy, we shouldn't
     * check HTTPS.
     */
    protected $checkHttpsOnParsing = true;

    /**
     * tell if adding .php to the entrypoint name in the url is requiered.
     */
    protected $extensionNeeded = true;

    /**
     * file that contains the url mapping into app/system.
     *
     * @var string
     */
    protected $mapFile = '';

    /**
     * file that contains the url mapping, into var/config.
     *
     * @var string
     */
    protected $localMapFile = '';

    public function __construct(array $options = array())
    {
        $availableOptions = array('enableParser', 'basePath',
            'checkHttpsOnParsing',
            'notFoundAct', );
        foreach ($availableOptions as $opt) {
            if (isset($options[$opt])) {
                $this->{$opt} = $options[$opt];
            }
        }

        if (isset($options['urlScriptIdenc'])) {
            $this->entryPointName = $options['urlScriptIdenc'];
        }
        if (isset($options['multiview'])) {
            $this->extensionNeeded = !$options['multiview'];
        }
        if (isset($options['significantFile'])) {
            $this->mapFile = $options['significantFile'];
        }

        if (isset($options['localSignificantFile'])) {
            $this->localMapFile = $options['localSignificantFile'];
        }
    }

    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }

        return null;
    }
}
