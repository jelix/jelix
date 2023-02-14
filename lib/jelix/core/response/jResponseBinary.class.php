<?php
/**
 * @package     jelix
 * @subpackage  core_response
 *
 * @author      Laurent Jouanneau
 * @contributor Nicolas Lassalle <nicolas@beroot.org> (ticket #188), Julien Issler
 * @contributor René-Luc Dhont
 *
 * @copyright   2005-2023 Laurent Jouanneau
 * @copyright   2007 Nicolas Lassalle
 * @copyright   2009-2016 Julien Issler
 * @copyright   2023 René-Luc Dhont
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Response use to send a binary file to the browser.
 *
 * It sends the content of a file (its path into $filename) or a custom content
 * (set into $content).
 * The downloaded content can be displayed directly into the browser (if it
 * can display it), or you can force the browser to save it into a file, on the
 * disk of the user. See $doDownload. You can indicate the name of the saved file
 * into $outputFileName.
 *
 * @package  jelix
 * @subpackage core_response
 */
class jResponseBinary extends jResponse
{
    /**
     * @var string
     */
    protected $_type = 'binary';

    /**
     * The path of the file you want to send. Keep empty if you provide the content
     * into $content. Or if $content is a callback, you can indicate the corresponding
     * filename here, to be able to delete it after the output, with $deleteFileAfterSending
     *
     * @var string
     */
    public $fileName = '';

    /**
     * name of the file under which the content will be sent to the user.
     *
     * @var string
     */
    public $outputFileName = '';

    /**
     * the content you want to send. Keep it to null if you indicate a filename into $fileName.
     *
     * @var string|callable|null
     */
    public $content;

    /**
     * Says if the "save as" dialog appear or not to the user.
     * if false, specify the mime type in $mimetype.
     *
     * @var bool
     */
    public $doDownload = true;

    /**
     * The mimeType of the current binary file.
     * It will be sent in the header "Content-Type".
     *
     * @var string
     */
    public $mimeType = 'application/octet-stream';

    /**
     * Delete file after the upload.
     *
     * Filename is indicated into $fileName
     */
    public $deleteFileAfterSending = false;

    /**
     * Sends the content or the file to the browser.
     *
     * @throws jException
     *
     * @return bool true if it's ok
     */
    public function output()
    {
        if ($this->_outputOnlyHeaders) {
            $this->sendHttpHeaders();

            return true;
        }

        if ($this->outputFileName === '' && $this->fileName !== '') {
            $f = explode('/', str_replace('\\', '/', $this->fileName));
            $this->outputFileName = $f[count($f) - 1];
        }

        $this->addHttpHeader('Content-Type', $this->mimeType, $this->doDownload);

        if ($this->doDownload) {
            $this->_downloadHeader();
        } else {
            $this->addHttpHeader('Content-Disposition', 'inline; filename="'.str_replace('"', '\"', $this->outputFileName).'"', false);
        }

        $hasFileToDelete = false;
        if ($this->fileName) {
            if (is_readable($this->fileName) && is_file($this->fileName)) {
                $this->_httpHeaders['Content-Length'] = filesize($this->fileName);
                if ($this->deleteFileAfterSending) {
                    $hasFileToDelete = true;
                }
                $f = $this->fileName;
                $this->content = function () use ($f) {
                    readfile($f);
                };
            }
            else {
                throw new jException('jelix~errors.repbin.unknown.file', $this->fileName);
            }
        }
        elseif (is_string($this->content)) {
            $this->_httpHeaders['Content-Length'] = strlen($this->content);
        }

        if ($this->content === null || is_bool($this->content)) {
            throw new \Exception("Missing content to output");
        }

        $this->sendHttpHeaders();

        if ($hasFileToDelete) {
            // ignore user abort, to be able to delete the file
            ignore_user_abort(true);
        }

        session_write_close();

        if (is_callable($this->content)) {
            ($this->content)();
        }
        else {
            echo $this->content;
        }

        flush();
        if ($hasFileToDelete) {
            unlink($this->fileName);
        }

        return true;
    }

    /**
     * set all headers to force download.
     */
    protected function _downloadHeader()
    {
        $this->addHttpHeader('Content-Disposition', 'attachment; filename="'.str_replace('"', '\"', $this->outputFileName).'"', false);
        $this->addHttpHeader('Content-Description', 'File Transfert', false);
        $this->addHttpHeader('Content-Transfer-Encoding', 'binary', false);
        $this->addHttpHeader('Pragma', 'public', false);
        $this->addHttpHeader('Cache-Control', 'maxage=3600', false);
        //$this->addHttpHeader('Cache-Control','no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);
        //$this->addHttpHeader('Expires','0', false);
    }


    /**
     * Sets the PHP callback associated with this Response.
     *
     * @param callable $callback The callback use to send the content
     *
     */
    public function setContentCallback(callable $callback)
    {
        $this->content = $callback;
    }

    /**
     * Sets the PHP callback associated with this Response with an
     * iterable.
     *
     * @param iterable $iterator The result of a generator use to build the callback to send the content
     *
     */
    public function setContentGenerator(iterable $iterator)
    {
        $this->content = function () use ($iterator) {
            foreach ($iterator as $line) {
                echo $line;
            }
        };
    }
}
