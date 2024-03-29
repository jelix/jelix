<?php
/**
 * @package     jelix
 * @subpackage  jacl2_plugin
 *
 * @author     Laurent Jouanneau
 * @copyright  2008 Laurent Jouanneau
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

        if (isset($params['jacl2.right'])) {
            $aclok = jAcl2::check($params['jacl2.right']);
        } elseif (isset($params['jacl2.rights.and'])) {
            $aclok = true;
            foreach ($params['jacl2.rights.and'] as $right) {
                if (!jAcl2::check($right)) {
                    $aclok = false;

                    break;
                }
            }
        } elseif (isset($params['jacl2.rights.or'])) {
            $aclok = false;
            foreach ($params['jacl2.rights.or'] as $right) {
                if (jAcl2::check($right)) {
                    $aclok = true;

                    break;
                }
            }
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

    public function beforeOutput()
    {
    }

    public function afterProcess()
    {
    }
}
