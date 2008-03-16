<?php
/**
* @package
* @subpackage %%module%%
* @author
* @copyright
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

class %%name%%Zone extends jZone {
    protected $_tplname='%%template%%';

    
    protected function _prepareTpl(){
        $this->_tpl->assign('foo','bar');
    }
}
?>
