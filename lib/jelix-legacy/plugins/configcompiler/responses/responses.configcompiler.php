<?php
/**
 * @package      jelix
 * @subpackage   core_config_plugin
 *
 * @author       Laurent Jouanneau
 * @copyright    2012 Laurent Jouanneau
 *
 * @see         http://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class responsesConfigCompilerPlugin implements \Jelix\Core\Config\CompilerPluginInterface
{
    public function getPriority()
    {
        return 15;
    }

    public function atStart($config)
    {
        $this->_initResponsesPath($config, 'responses');
        $this->_initResponsesPath($config, '_coreResponses');
    }

    public function onModule($config, Jelix\Core\Infos\ModuleInfos $module)
    {
    }

    public function atEnd($config)
    {
    }

    /**
     * get all physical paths of responses file.
     *
     * @param object $config the configuration object
     * @param string $list  name of the list to read into the configuration
     */
    protected function _initResponsesPath($config, $list)
    {
        $copylist = $config->{$list}; // because we modify $list and then it will search for "foo.path" responses...
        foreach ($copylist as $type => $class) {
            if (strpos($class, 'app:') === 0) {
                $config->{$list}[$type] = $class = substr($class, 4);
                $config->{$list}[$type.'.path'] = $path = jApp::appPath('app/responses/'.$class.'.class.php');
                if (file_exists($path)) {
                    continue;
                }
            } elseif (preg_match('@^(?:module:)?([^~]+)~(.+)$@', $class, $m)) {
                $mod = $m[1];
                if (isset($config->_modulesPathList[$mod])) {
                    $class = $m[2];
                    $path = $config->_modulesPathList[$mod].'responses/'.$class.'.class.php';
                    $config->{$list}[$type] = $class;
                    $config->{$list}[$type.'.path'] = $path;
                    if (file_exists($path)) {
                        continue;
                    }
                } else {
                    $path = $class;
                }
            } elseif (strpos($class, '\\') !== false) {
                // class name with namespace is supposed to be autoloaded
                $config->{$list}[$type.'.path'] = '';

                continue;
            } elseif (file_exists($path = JELIX_LIB_CORE_PATH.'response/'.$class.'.class.php')) {
                $config->{$list}[$type.'.path'] = $path;

                continue;
            } elseif (file_exists($path = jApp::appPath('app/responses/'.$class.'.class.php'))) {
                $config->{$list}[$type.'.path'] = $path;

                continue;
            }

            throw new Exception('Error in main configuration on responses parameters -- the class file of the response type "'.$type.'" is not found ('.$path.')', 12);
        }
    }
}
