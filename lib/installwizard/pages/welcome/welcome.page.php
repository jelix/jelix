<?php

/**
* page for Installation wizard
*
* @package     InstallWizard
* @subpackage  pages
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class welcomeWizPage extends installWizardPage {
    
    /**
     * action to display the page
     * @param \Jelix\Castor\Castor $tpl the template container
     */
    function show (\Jelix\Castor\Castor $tpl) {
        return true;
    }
    
    /**
     * action to process the page after the submit
     */
    function process() {
        return 0;
    }

}