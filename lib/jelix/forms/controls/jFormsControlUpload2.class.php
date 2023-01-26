<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2006-2022 Laurent Jouanneau
 * @copyright   2009 Julien Issler
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlUpload2 extends jFormsControl
{
    public $type = 'upload';

    public $mimetype = array();

    public $maxsize = 0;

    public $accept = '';

    public $capture = '';

    public $fileInfo = array();

    protected $error;

    protected $modified = false;

    public function setForm($form)
    {
        parent::setForm($form);
        if (!isset($this->container->privateData[$this->ref]['newfile'])) {
            $this->container->privateData[$this->ref]['newfile'] = '';
        }
        if (!isset($this->container->privateData[$this->ref]['originalfile'])) {
            $this->container->privateData[$this->ref]['originalfile'] = '';
        }
    }

    /**
     * the filename sets into the form during its initialization
     *
     * @return string
     */
    public function getOriginalFile()
    {
        if (isset($this->container->privateData[$this->ref]['originalfile'])) {
            return $this->container->privateData[$this->ref]['originalfile'];
        }

        return '';
    }

    /**
     * The filename of the file uploaded during the form submission
     *
     * @return string
     */
    public function getNewFile()
    {
        if (isset($this->container->privateData[$this->ref]['newfile'])) {
            return $this->container->privateData[$this->ref]['newfile'];
        }

        return '';
    }

    protected function getTempFile($file)
    {
        jFile::createDir(jApp::tempPath('uploads/'));

        return jApp::tempPath('uploads/'.session_id().'-'.
            $this->form->getSelector().'-'.$this->form->id().'-'.
            $this->ref.'-'.$file);
    }

    protected function deleteNewFile()
    {
        if ($this->container->privateData[$this->ref]['newfile'] != '') {
            $file = $this->getTempFile($this->container->privateData[$this->ref]['newfile']);
            if (is_file($file)) {
                unlink($file);
            }
            $this->container->privateData[$this->ref]['newfile'] = '';
        }
    }

    public function setDataFromDao($value, $daoDatatype)
    {
        $this->deleteNewFile();
        $this->container->privateData[$this->ref]['originalfile'] = $value;
        $this->container->data[$this->ref] = $value;
    }

    /**
     * @param jRequest $request
     */
    public function setValueFromRequest($request)
    {
        $action = $request->getParam($this->ref.'_jf_action', '');
        $this->processUpload($action, isset($_FILES[$this->ref]) ? $_FILES[$this->ref] : null);
    }

    protected function processUpload($action, $fileInfo)
    {
        if ($this->isReadOnly()) {
            $action = 'keep';
        }

        switch ($action) {
            case 'keep':
                $this->deleteNewFile();
                $this->error = null;
                $this->container->data[$this->ref] = $this->container->privateData[$this->ref]['originalfile'];

                $this->modified = false;

                break;

            case 'keepnew':
                if ($this->container->privateData[$this->ref]['newfile'] != ''
                    && file_exists($this->getTempFile($this->container->privateData[$this->ref]['newfile']))
                ) {
                    $this->container->data[$this->ref] = $this->container->privateData[$this->ref]['newfile'];
                } else {
                    $this->container->data[$this->ref] = $this->container->privateData[$this->ref]['originalfile'];
                }

                $this->modified = true;

                break;

            case 'new':
                $fileName = $this->processNewFile($fileInfo);
                $this->modified = true;
                if ($fileName) {
                    if ($this->container->privateData[$this->ref]['newfile'] != $fileName) {
                        $this->deleteNewFile();
                    }
                    $this->container->privateData[$this->ref]['newfile'] = $fileName;
                    $this->container->data[$this->ref] = $fileName;
                } elseif ($this->container->privateData[$this->ref]['newfile'] != '') {
                    $this->container->data[$this->ref] = $this->container->privateData[$this->ref]['newfile'];
                } else {
                    $this->modified = false;
                    $this->container->privateData[$this->ref]['newfile'] = '';
                    $this->container->data[$this->ref] = $this->container->privateData[$this->ref]['originalfile'];
                }

                break;

            case 'del':
                $this->deleteNewFile();
                if (!$this->required) {
                    $this->modified = true;
                    $this->container->data[$this->ref] = '';
                } else {
                    $this->modified = false;
                    $this->error = jForms::ERRDATA_REQUIRED;
                }

                break;

            default:
        }
        $this->container->privateData[$this->ref]['action'] = $action;
    }

    protected function processNewFile($fileInfo)
    {
        $this->error = null;

        if ($fileInfo) {
            $this->fileInfo = $fileInfo;
        } else {
            $this->fileInfo = array('name' => '', 'type' => '', 'size' => 0,
                'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, );
        }

        if ($this->fileInfo['error'] == UPLOAD_ERR_NO_FILE) {
            if ($this->required) {
                $this->error = jForms::ERRDATA_REQUIRED;
            }

            return null;
        }
        if ($this->fileInfo['error'] == UPLOAD_ERR_NO_TMP_DIR
                || $this->fileInfo['error'] == UPLOAD_ERR_CANT_WRITE
            ) {
            $this->error = jForms::ERRDATA_FILE_UPLOAD_ERROR;
        }

        if ($this->fileInfo['error'] == UPLOAD_ERR_INI_SIZE
                || $this->fileInfo['error'] == UPLOAD_ERR_FORM_SIZE
                || ($this->maxsize && $this->fileInfo['size'] > $this->maxsize)
            ) {
            $this->error = jForms::ERRDATA_INVALID_FILE_SIZE;
        }

        if ($this->fileInfo['error'] == UPLOAD_ERR_PARTIAL
                || !$this->isUploadedFile($this->fileInfo['tmp_name'])
            ) {
            $this->error = jForms::ERRDATA_INVALID;
        }

        if (count($this->mimetype)) {
            $this->fileInfo['type'] = \Jelix\FileUtilities\File::getMimeType($this->fileInfo['tmp_name']);
            if ($this->fileInfo['type'] == 'application/octet-stream') {
                // let's try with the name
                $this->fileInfo['type'] = jFile::getMimeTypeFromFilename($this->fileInfo['name']);
            }

            if (!in_array($this->fileInfo['type'], $this->mimetype)) {
                $this->error = jForms::ERRDATA_INVALID_FILE_TYPE;
            }
        }

        if ($this->error === null) {
            $filePath = $this->getTempFile($this->fileInfo['name']);
            if ($this->moveUploadedFile($this->fileInfo['tmp_name'], $filePath)) {
                return $this->fileInfo['name'];
            }
            $this->error = jForms::ERRDATA_FILE_UPLOAD_ERROR;
        }

        return null;
    }

    /**
     * Change the name of the new file name. If there is already an uploaded file, it is deleted
     *
     * @param string $fileName
     * @return void
     */
    public function setNewFile($fileName)
    {
        if ($fileName) {
            if ($this->container->privateData[$this->ref]['newfile'] != $fileName) {
                $this->deleteNewFile();
            }
            $this->container->privateData[$this->ref]['newfile'] = $fileName;
            $this->container->data[$this->ref] = $fileName;
        } elseif ($this->container->privateData[$this->ref]['newfile'] != '') {
            $this->deleteNewFile();
            $this->container->data[$this->ref] = '';
        } else {
            $this->container->data[$this->ref] = '';
        }
    }

    public function check()
    {
        if ($this->error) {
            return $this->container->errors[$this->ref] = $this->error;
        }

        return null;
    }

    public function isModified()
    {
        if ($this->modified) {
            return true;
        }

        return parent::isModified();
    }

    /**
     * Return a filename under which the file can be saved without overwriting an existing file.
     *
     * The base name of the file is the name of the uploaded file, or the name given into the $alternateName
     * parameter.
     * The returning filename can be ended by a number if there is already a file with the original name.
     *
     * @param string $directoryPath  the directory where the file is supposed to be stored
     * @param string $alternateName
     * @return string the filename
     */
    public function getUniqueFileName($directoryPath, $alternateName = '')
    {
        if ($alternateName == '') {
            $alternateName = $this->container->privateData[$this->ref]['newfile'];
            if ($alternateName == '') {
                return '';
            }
        }
        $directoryPath = rtrim($directoryPath, '/').'/';
        $path = $directoryPath.$alternateName;
        $filename = basename($path);
        $dir = rtrim(dirname($path), '/');
        $idx = 0;
        $originalName = $filename;
        while (file_exists($dir.'/'.$filename)) {
            ++$idx;
            $splitValue = explode('.', $originalName);
            $splitValue[0] = $splitValue[0].$idx;
            $filename = implode('.', $splitValue);
        }

        return substr($dir.'/'.$filename, strlen($directoryPath));
    }

    /**
     * Save the uploaded file into the given directory, under the original filename, or under the name given
     * into $alternateName
     *
     * If there is already a file with that name, it will be overwritten. If you don't want this behavior, you
     * can call getUniqueFileName to have a unique name.
     *
     * @param string $directoryPath  the directory where the file is supposed to be stored
     * @param string $alternateName
     * @return bool
     */
    public function saveFile($directoryPath, $alternateName = '', $deletePreviousFile = true)
    {
        if (isset($this->container->errors[$this->ref])
            && $this->container->errors[$this->ref] != ''
        ) {
            return false;
        }

        if ($this->container->privateData[$this->ref]['newfile']) {
            if ($this->container->privateData[$this->ref]['originalfile']) {
                $originalFile = $directoryPath.$this->container->privateData[$this->ref]['originalfile'];
                if ($deletePreviousFile && file_exists($originalFile)) {
                    unlink($originalFile);
                }
            }
            if ($alternateName == '') {
                $alternateName = $this->container->privateData[$this->ref]['newfile'];
            }
            $newFileToCopy = $this->getTempFile($this->container->privateData[$this->ref]['newfile']);
            $dir = dirname($directoryPath.$alternateName);
            jFile::createDir($dir);
            rename($newFileToCopy, $directoryPath.$alternateName);
            $this->container->privateData[$this->ref]['originalfile'] = $alternateName;
            $this->container->data[$this->ref] = $alternateName;
            $this->container->privateData[$this->ref]['newfile'] = '';
        } elseif ($this->container->data[$this->ref] == ''
            && $this->container->privateData[$this->ref]['originalfile']
        ) {
            $originalFile = $directoryPath.$this->container->privateData[$this->ref]['originalfile'];
            if ($deletePreviousFile && file_exists($originalFile)) {
                unlink($originalFile);
            }
            $this->container->privateData[$this->ref]['originalfile'] = '';
        }

        return true;
    }

    /**
     * delete the current file indicated into the control
     * @param string $directoryPath  the directory where the file is supposed to be stored
     */
    public function deleteFile($directoryPath)
    {
        if ($this->container->data[$this->ref] != '') {
            $file = $directoryPath.$this->container->data[$this->ref];
            if (file_exists($file)) {
                unlink($file);
            }
            $this->container->data[$this->ref] = '';
        }
    }

    protected function isUploadedFile($file)
    {
        return is_uploaded_file($file);
    }

    protected function moveUploadedFile($file, $target)
    {
        return move_uploaded_file($file, $target);
    }

    public function getWidgetType()
    {
        return 'upload2';
    }
}
