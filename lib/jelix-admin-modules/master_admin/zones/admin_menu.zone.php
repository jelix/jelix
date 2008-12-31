<?php
/**
* @package   jelix_admin_modules
* @subpackage master_admin
* @author    Laurent Jouanneau
* @copyright 2008 Laurent Jouanneau
* @link      http://jelix.org
* @licence  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
*/

class masterAdminMenuItem {
    public $id = '';
    public $parentId = '';
    public $label = '';
    public $link = '';
    public $order = 0;
    public $icon = '';
    
    public $childItems = array();
    
    public function __construct($id, $label, $link, $order=0, $parentId='') {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->label = $label;
        $this->link = $link;
        $this->order = $order;
    }
    
    public function copyFrom($item) {
        $this->label = $item->label;
        $this->link = $item->link;
        $this->order = $item->order;
        $this->icon = $item->icon;
        $this->childItems = array_merge($item->childItems, $this->childItems);
    }
}

function masterAdminItemSort($itemA, $itemB)
{
    return ($itemA->order - $itemB->order);
}


class admin_menuZone extends jZone {
    protected $_tplname='zone_admin_menu';

    protected function _prepareTpl(){
        $menu = array();
        $menu['toplinks'] = new masterAdminMenuItem('toplinks', '', '');
        $menu['toplinks']->childItems[] = new masterAdminMenuItem('dashboard', jLocale::get('gui.menu.item.dashboard'), jUrl::get('default:index'));
        $menu['system'] = new masterAdminMenuItem('system', jLocale::get('gui.menu.item.system'), '', 100);

        $items = jEvent::notify('masteradminGetMenuContent')->getResponse();

        foreach ($items as $item) {
            if($item->parentId) {
                if(!isset($menu[$item->parentId])) {
                    $menu[$item->parentId] = new masterAdminMenuItem($item->parentId, '', '');
                }
                $menu[$item->parentId]->childItems[] = $item;
            }
            else {
                if(isset($menu[$item->id])) {
                    $menu[$item->id]->copyFrom($item);
                }
                else {
                    $menu[$item->id] = $item;
                }
            }
        }

        usort($menu, "masterAdminItemSort");
        foreach($menu as $topitem) {
            usort($topitem->childItems, "masterAdminItemSort");
        }
        $this->_tpl->assign('menuitems', $menu);
        $this->_tpl->assign('selectedMenuItem', $this->param('selectedMenuItem',''));
    }
}
