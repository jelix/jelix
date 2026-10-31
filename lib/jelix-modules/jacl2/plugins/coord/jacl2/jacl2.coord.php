<?php
/**
 * @package     jelix
 * @subpackage  jacl2_plugin
 *
 * @author     Laurent Jouanneau
 * @copyright  2008-2026 Laurent Jouanneau
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 *
 * @since 1.1
 */

/**
 * @package     jelix
 * @subpackage  jacl2_plugin
 *
 * @since 1.1
 */
class jAcl2CoordPlugin implements jICoordPlugin
{
    public $config;

    public function __construct($conf)
    {
        $this->config = $conf;
    }

    /**
     * @param array $params plugin parameters for the current action
     */
    public function beforeAction($params)
    {
        $selector = null;
        $aclok = true;

        // We want to check rights indicated into the '*', and those indicated for the controller method.
        // because the developer may want to indicate rights to check for all methods in addition of specific rights for
        // the method, to avoid to repeat rights in parameters. So, since Jelix 1.8.29, we don't use the
        // list of parameters resulting from the merged parameters list of '*' and of the method.
        if (isset($params['__all'])) {
            $aclok = $this->checkAcl($params['__all']);
        }

        if ($aclok && isset($params['__method'])) {
            $aclok = $this->checkAcl($params['__method']);
        }

        if (!$aclok) {
            if (jApp::coord()->request->isAjax() || $this->config['on_error'] == 1
                || !jApp::coord()->request->isAllowedResponse('jResponseRedirect')) {
                throw new jException($this->config['error_message']);
            }
            $selector = new jSelectorAct($this->config['on_error_action']);
        }

        return $selector;
    }

    protected function checkAcl(&$params)
    {
        $aclok = true;

        if (isset($params['jacl2.right'])) {
            $aclok = jAcl2::check($params['jacl2.right']);
        }

        if ($aclok && isset($params['jacl2.rights.and'])) {
            foreach ($params['jacl2.rights.and'] as $right) {
                if (!jAcl2::check($right)) {
                    $aclok = false;

                    break;
                }
            }
        }

        if ($aclok && isset($params['jacl2.rights.or'])) {
            $aclok = false;
            foreach ($params['jacl2.rights.or'] as $right) {
                if (jAcl2::check($right)) {
                    $aclok = true;

                    break;
                }
            }
        }

        return $aclok;
    }

    public function beforeOutput()
    {
    }

    public function afterProcess()
    {
    }
}
