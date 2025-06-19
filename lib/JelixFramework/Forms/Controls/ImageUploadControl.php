<?php
/**
 *
 * @author      Laurent Jouanneau
 * @copyright   2020-2024 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;
use Jelix\Forms\Forms;

/**
 */
class ImageUploadControl extends Upload2Control
{
    /** @var int max width for images */
    public $maxWidth = 0;

    /** @var int max width for images */
    public $maxHeight = 0;

    protected function processNewFile($fileInfo)
    {
        $this->error = null;

        // for main browsers that don't support canvas.toBlob method (before 2019), we need to store
        // the modified image (base64 encoded) and its properties into a JSON object.
        // This object is send into a '*_jforms_edited_image' parameter.
        $inputRef = $this->ref.'_jforms_edited_image';

        if (!array_key_exists($inputRef, $_POST)) {
            // the image is sent as usual
            return parent::processNewFile($fileInfo);
        }

        // the image is sent as base64 encoded string into a json object

        $this->fileInfo = @json_decode($_POST[$inputRef], true);

        if (!$this->fileInfo) {
            $this->fileInfo = array('name' => '', 'type' => '', 'size' => 0,
                'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, );
            if ($this->required) {
                $this->error = Forms::ERRDATA_REQUIRED;
            }

            return null;
        }

        $content = '';
        if (isset($this->fileInfo['content'])) {
            $content = $this->fileInfo['content'];
            unset($this->fileInfo['content']);
        }

        if ($content != '') {
            $content = @base64_decode($content, true);
            if ($content === false) {
                $this->error = Forms::ERRDATA_INVALID;

                return null;
            }
        } else {
            if ($this->required) {
                $this->error = Forms::ERRDATA_REQUIRED;
            }

            return null;
        }

        $filePath = $this->getTempFile($this->fileInfo['name']);
        $size = file_put_contents($filePath, $content);

        if ($size === false) {
            $this->error = Forms::ERRDATA_FILE_UPLOAD_ERROR;

            return null;
        }

        if ($this->maxsize && $size > $this->maxsize) {
            $this->error = Forms::ERRDATA_INVALID_FILE_SIZE;
            unlink($filePath);

            return null;
        }

        if (count($this->mimetype)) {
            $this->fileInfo['type'] = \Jelix\FileUtilities\File::getMimeType($filePath);
            if ($this->fileInfo['type'] == 'application/octet-stream') {
                // let's try with the name
                $this->fileInfo['type'] = \jFile::getMimeTypeFromFilename($this->fileInfo['name']);
            }

            if (!in_array($this->fileInfo['type'], $this->mimetype)) {
                $this->error = Forms::ERRDATA_INVALID_FILE_TYPE;
                unlink($filePath);

                return null;
            }
        }

        return $this->fileInfo['name'];
    }

    public function getWidgetType()
    {
        return 'imageupload';
    }
}
