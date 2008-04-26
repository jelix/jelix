<?php
/**
* @package    jelix
* @subpackage forms
* @author     Laurent Jouanneau
* @contributor 
* @copyright   2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
interface jIFormsBuilderCompiler {

    public function __construct($mainCompiler);

    public function startCompile();

    public function generateControl($controltype, $control);

    public function endCompile();
}

