<?php
/**
* @package    jelix
* @subpackage template plugins
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

function jtpl_function_jurl($tpl, $selector, $params=array())
{
     echo jUrl::get($selector, $params, true);
}

?>