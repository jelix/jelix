<?php
/**
* @package     jelix
* @subpackage  jtcpdf module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jtcpdfModuleInstaller extends jInstallerModule {

    function install() {
        $master = $this->config->getMaster();
        // setup the tcpdf response if not already done
        if (!$master->getValue('tcpdf', 'responses')) {
            $master->setValue('tcpdf', "jtcpdf~jResponseTcpdf", "responses");
        }
        if (!$master->getValue('tcpdf', '_coreResponses')) {
            $master->setValue('tcpdf', "jtcpdf~jResponseTcpdf", "_coreResponses");
        }
    }
}