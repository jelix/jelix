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
        $this->config->setValue('tcpdf', "jtcpdf~jResponseTcpdf", "responses");
        $this->config->setValue('tcpdf', "jtcpdf~jResponseTcpdf", "_coreResponses");
    }
}