<?php
/**
 * @package     jelix
 * @subpackage  core_response
 *
 * @author      Laurent Jouanneau
 * @contributor Nicolas Lassalle <nicolas@beroot.org> (ticket #188), Julien Issler
 * @contributor René-Luc Dhont
 *
 * @copyright   2005-2026 Laurent Jouanneau
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


    public $processPartialContent = false;

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

        $reponseForHeadRequest = $this->getRequest()->getHttpMethod() == 'HEAD';

        if ($this->outputFileName === '' && $this->fileName !== '') {
            $f = explode('/', str_replace('\\', '/', $this->fileName));
            $this->outputFileName = $f[count($f) - 1];
        }

        $this->addHttpHeader('Content-Type', $this->mimeType, $this->doDownload);

        $hasFileToDelete = false;
        if ($this->fileName) {
            if (is_readable($this->fileName) && is_file($this->fileName)) {
                $fileSize = filesize($this->fileName);
                $this->_httpHeaders['Content-Length'] = $fileSize;
                if ($this->deleteFileAfterSending) {
                    $hasFileToDelete = true;
                }
                $fileName = $this->fileName;

                if ($this->processPartialContent) {
                    if ($reponseForHeadRequest) {
                        $this->addHttpHeader('Accept-Ranges', 'bytes');
                    }
                    else {
                        $rangeHeader = $this->getRequestHeader('Range');
                        if ($rangeHeader !== false) {
                            $this->prepareRanges($rangeHeader, $fileName, $fileSize);
                        }
                        else {
                            $this->processPartialContent = false;
                            $this->content = function () use ($fileName) {
                                readfile($fileName);
                            };
                        }
                    }
                }
                else {
                    $this->content = function () use ($fileName) {
                        readfile($fileName);
                    };
                }
            }
            else {
                throw new jException('jelix~errors.repbin.unknown.file', $this->fileName);
            }
        }
        elseif (is_string($this->content)) {
            $this->_httpHeaders['Content-Length'] = strlen($this->content);
        }

        if (!$this->processPartialContent) {
            if ($this->doDownload) {
                $this->_downloadHeader();
            } else {
                $this->addHttpHeader('Content-Disposition', 'inline; filename="' . str_replace('"', '\"', $this->outputFileName) . '"', false);
            }
        }


        if (!$reponseForHeadRequest && ($this->content === null || is_bool($this->content))) {
            throw new \Exception("Missing content to output");
        }

        $this->sendHttpHeaders();

        if ($reponseForHeadRequest) {
            if ($hasFileToDelete) {
                unlink($this->fileName);
            }
            return true;
        }

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


    protected function readRanges($rangeHeader, $fileSize)
    {
        $ranges = array();
        if (preg_match('/^bytes=(.+)$/', $rangeHeader, $bytesMatches)) {
            $rangeList = preg_split('/\s*,\s*/', $bytesMatches[1]);
            foreach($rangeList as $rangeHeader) {
                if (!preg_match('/^(\d*)?-(\d*)?$/', $rangeHeader, $matches)) {
                    return false;
                }
                $startByte = ($matches[1] === ''? -1 : intval($matches[1]));
                $endByte = ($matches[2] === ''? -1 : intval($matches[2]));
                if ($endByte > $fileSize || $startByte === $endByte || ($startByte === -1 && $endByte === -1)) {
                    return false;
                }

                if ($startByte == -1) {
                    // $endByte is the number of bytes to read from the end of the file.
                    $startByte = $fileSize - $endByte;
                    $endByte = $fileSize - 1;
                }
                else if ($endByte == -1) {
                    $endByte = $fileSize - 1;
                }

                if ($startByte > $endByte) {
                    return false;
                }

                $ranges[] = array($startByte, $endByte);
            }
        }

        if (count($ranges) === 0) {
            return false;
        }
        if (count($ranges) === 1) {
            return $ranges;
        }

        // check that ranges don't overlap
        usort($ranges, function($a, $b) {
            return ($a[1] < $b[1] ? -1 : ($a[1] < $b[1] ? -1 : 0));
        });

        foreach($ranges as $k => $range) {
            if (isset($ranges[$k + 1])) {
                if ($range[1] >= $ranges[$k + 1][0]) {
                    return false;
                }
            }
        }
        return $ranges;
    }

    protected function prepareRanges($rangeHeader, $fileName, $fileSize)
    {
        $ranges = $this->readRanges($rangeHeader, $fileSize);
        if ($ranges === false) {
            $this->setHttpStatus('416', 'Requested Range Not Satisfiable');
            $this->addHttpHeader('Content-Range', 'bytes */'.$fileSize);
            $this->mimeType = 'text/plain';
            $this->content = '416 - Requested Range Not Satisfiable';
            $this->processPartialContent = false;
        }
        else if (count($ranges) == 1 ) {
            $startByte = $ranges[0][0];
            $endByte = $ranges[0][1];
            $this->setHttpStatus('206', 'Partial Content');
            $this->addHttpHeader('Content-Range', 'bytes '.$startByte.'-'.$endByte.'/'.$fileSize);
            $this->addHttpHeader('Content-Length', $fileSize);
            $this->content = function () use ($fileName, $startByte, $endByte) {
                $fh = fopen($fileName, 'rb');
                $output = fopen('php://output', 'wb');
                fseek($fh, $startByte);
                stream_copy_to_stream($fh, $output , $endByte - $startByte + 1);
                fclose($fh);
                fclose($output);
            };
        }
        else {

            $boundary =  md5(microtime());

            // prepare chunks
            $contentSize = 0;
            foreach ($ranges as $k => $range) {
                $startByte = $range[0];
                $endByte = $range[1];
                $chunkSize = $endByte - $startByte + 1;

                $boundaryString = sprintf("\r\n--%s\r\nContent-type: %s\r\nContent-Range: bytes %d-%d/%d\r\n\r\n", $boundary, $this->mimeType, $startByte, $endByte, $fileSize);
                $ranges[$k][2] = $boundaryString;
                $ranges[$k][3] = $chunkSize;

                $contentSize += $chunkSize + strlen($boundaryString);
            }

            $contentEnd = sprintf("\r\n--%s--\r\n", $boundary);
            $contentSize += strlen($contentEnd);

            $this->setHttpStatus('206', 'Partial Content');
            $this->addHttpHeader('Content-Type', 'multipart/byteranges; boundary='.$boundary);
            $this->addHttpHeader('Content-Length', $contentSize);

            $this->content = function () use ($fileName, $ranges, $contentEnd) {
                $fh = fopen($fileName, 'rb');
                $output = fopen('php://output', 'wb');

                foreach ($ranges as $range) {
                    list($startByte, $endByte, $boundaryString, $chunkSize) = $range;

                    fwrite($output, $boundaryString);
                    fseek($fh, $startByte);
                    stream_copy_to_stream($fh, $output , $chunkSize);
                }

                fwrite($output, $contentEnd);
                fclose($fh);
                fclose($output);
            };
        }
    }
}
