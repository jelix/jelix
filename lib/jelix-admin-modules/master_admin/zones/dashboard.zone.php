<?php
/**
* @package   jelix_admin_modules
* @subpackage master_admin
* @author    Laurent Jouanneau
* @copyright 2008 Laurent Jouanneau
* @link      http://jelix.org
* @licence  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
*/

class masterAdminDashboardWidget {
    public $title = '';
    public $content = '';
}

class dashboardZone extends jZone {
    protected $_tplname='zone_dashboard';

    protected function _prepareTpl(){
        $this->_tpl->assignIfNone('foo','bar');
        
        $this->_tpl->assign('widgets', jEvent::notify('masterAdminGetDashboardWidget')->getResponse());
        
        
    }
}
