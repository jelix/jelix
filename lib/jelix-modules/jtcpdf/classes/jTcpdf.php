<?php
/**
* @package     jelix
* @subpackage  jtcpdf module
* @author      Julien Issler
* @contributor Laurent Jouanneau
* @copyright   2007-2009 Julien Issler, 2007-2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.0
*/

define('K_TCPDF_EXTERNAL_CONFIG',true);
define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
define('K_PATH_URL',
       jApp::coord()->request->getServerURI() .
       jApp::urlBasePath());
define('K_PATH_CACHE', jApp::tempPath());
define('K_PATH_IMAGES', jApp::appPath());
define('K_BLANK_IMAGE', K_PATH_MAIN.'images/_blank.png');
define('K_CELL_HEIGHT_RATIO', 1.25);
define('K_SMALL_RATIO', 2/3);

/**
 * sub-class of TCPDF, for better Jelix integration and easy save to disk feature.
 * @package    jelix
 * @subpackage utils
 * @since 1.0
 */
class jTcpdf extends TCPDF {

    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {

        if (is_string($unicode)) { // support of previous behavior
            $encoding = $unicode;
            $unicode = ($encoding == 'UTF-8' || $encoding == 'UTF-16');
        }

        parent::__construct($orientation, $unit, $format, $unicode , $encoding, $diskcache, $pdfa);

        $this->setHeaderFont(array('helvetica','',10));
        $this->setFooterFont(array('helvetica','',10));
        $this->setFont('helvetica','',10);
    }

    /**
     * Method to save the current document to a file on the disk
     * @param string $filename The target filename
     * @param string $path The target path where to store the file
     * @return boolean TRUE if success, else throws a jException
     */
    public function saveToDisk($filename,$path){

        if(!is_dir($path))
            throw new jException('jelix~errors.file.directory.notexists',array($path));

        if(!is_writable($path))
           throw new jException('jelix~errors.file.directory.notwritable',array($path));

        $file = realpath($path).'/'.$filename;
        if (file_put_contents($file, $this->Output('','S'))) {
            chmod($file, jApp::config()->chmodFile);
            return true;
        }

        throw new jException('jelix~errors.file.write.error',array($path.'/'.$filename,''));
    }

}