<?php
/**
 * @package     jelix
 * @subpackage  core_response
 *
 * @author      René-Luc Dhont
 *
 * @copyright   2021 René-Luc Dhont
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Streamed response.
 *
 * Response use to send a streamed data to the client. It
 * uses a callback for its content.
 *
 * The callback should use the standard PHP functions like echo
 * to stream the response back to the client. The flush() function
 * can also be used if needed.
 *
 * @package  jelix
 * @subpackage core_response
 *
 * @see jResponse
 * @since 1.8a2
 */
final class jResponseStreamed extends jResponse
{
    /**
     * @var string
     */
    protected $_type = 'streamed';

    /**
     * The mimeType of the current streamed response.
     * It will be sent in the header "Content-Type".
     *
     * @var string
     */
    public $mimeType = 'application/octet-stream';

    /**
     * @var callable The callback use to send the content
     */
    protected $callback;

    /**
     * Send the content provided by the callback to the client.
     *
     * @throws jException
     *
     * @return bool true it it's ok
     */
    public function output()
    {
        $this->addHttpHeader('Content-Type', $this->mimeType, true);

        if (null === $this->callback) {
            throw new jException('The Response callback must not be null.');
        }

        if ($this->_outputOnlyHeaders) {
            $this->sendHttpHeaders();

            return true;
        }
        $this->sendHttpHeaders();

        ($this->callback)();

        return true;
    }

    /**
     * Sets the PHP callback associated with this Response.
     *
     * @param callable $callback The callback use to send the content
     *
     */
    public function setCallback(callable $callback)
    {
        if (!is_callable($callback)) {
            throw new jException('The Response callback must be callable.');
        }
        $this->callback = $callback;
    }

    /**
     * Sets the PHP callback associated with this Response with an
     * iterable. For each value a PHP_EOL is added after.
     *
     * @param iterable $iterator The result of a generator use to build the callback to send the content
     *
     */
    public function setGenerator(iterable $iterator)
    {
        if (!is_iterable($iterator)) {
            throw new jException('The iterator must be iterable.');
        }
        $this->callback = function () use ($iterator) {
            foreach ($iterator as $line) {
                echo $line.PHP_EOL;
            }
        };
    }

}
