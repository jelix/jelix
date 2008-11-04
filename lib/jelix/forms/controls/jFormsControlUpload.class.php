<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2006-2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlUpload extends jFormsControl {
    public $type='upload';
    public $mimetype=array();
    public $maxsize=0;

    public $fileInfo = array();

    function check(){
        if(isset($_FILES[$this->ref]))
            $this->fileInfo = $_FILES[$this->ref];
        else
            $this->fileInfo = array('name'=>'','type'=>'','size'=>0,'tmp_name'=>'', 'error'=>UPLOAD_ERR_NO_FILE);

        if($this->fileInfo['error'] == UPLOAD_ERR_NO_FILE) {
            if($this->required)
                return $this->container->errors[$this->ref] = jForms::ERRDATA_REQUIRED;
        }else{
            if($this->fileInfo['error'] != UPLOAD_ERR_OK || !is_uploaded_file($this->fileInfo['tmp_name']))
                return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;

            if($this->maxsize && $this->fileInfo['size'] > $this->maxsize)
                return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;

            if(count($this->mimetype)){
                if($this->fileInfo['type']==''){
                    $this->fileInfo['type'] = mime_content_type($this->fileInfo['tmp_name']);
                }
                if(!in_array($this->fileInfo['type'], $this->mimetype))
                    return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
            }
        }
        return null;
    }

    function setValueFromRequest($request) {
        if(isset($_FILES[$this->ref])){
            $this->setData($_FILES[$this->ref]['name']);
        }else{
            $this->setData('');
        }
    }
}
