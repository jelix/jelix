<?php
/**
* @package    jelix
* @subpackage template plugins
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


function jtpl_modifier_escxml($string)
{
    return htmlspecialchars($string);
}

?>