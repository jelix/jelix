<?php
/**
* @author     Laurent Jouanneau
* @author     Gerald Croes
* @contributor Julien Issler, Yannick Le Guédart, Dominique Papin
*
* @copyright  2001-2005 CopixTeam, 2005-2016 Laurent Jouanneau
* Some parts of this file are took from Copix Framework v2.3dev20050901, CopixI18N.class.php, http://www.copix.org.
* copyrighted by CopixTeam and released under GNU Lesser General Public Licence.
* initial authors : Gerald Croes, Laurent Jouanneau.
* enhancement by Laurent Jouanneau for Jelix.
* @copyright 2008 Julien Issler, 2008 Yannick Le Guédart, 2008 Dominique Papin
*
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * reads a properties file
 */
class jPropertiesFileReader
{
    protected $fileName;

    protected $properties = array();
    
    /**
     * constructor.
     *
     * @param string $file path of a properties file
     */
    public function __construct($fileName, $charset = 'UTF-8')
    {
        $this->fileName = $fileName;
        $this->charset = $charset;
    }

    public function get($key) {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        }
        return null;
    }

    public function getProperties() {
        return $this->properties;
    }

    /**
     * @return an array
     */
    public function parse()
    {
        $f = @fopen($this->fileName, 'r');
        if ($f === false) {
            throw new Exception('Cannot load the resource '.$this->fileName, 216);
        }

        $this->properties = array();

        $utf8Mod = ($this->charset == 'UTF-8') ? 'u' : '';
        $unbreakablespace = ($this->charset == 'UTF-8') ? utf8_encode(chr(160)) : chr(160);
        $escapedChars = array('\#', '\n', '\w', '\S', '\s');
        $unescape = array('#', "\n", ' ', $unbreakablespace, ' ');
        $multiline = false;
        $linenumber = 0;
        $key = '';
        while (!feof($f)) {
            if ($line = fgets($f)) {
                ++$linenumber;
                $line = rtrim($line);
                if ($multiline) {
                    // the current line is the part of the value of the previous readed property
                    if (preg_match("/^\s*(.*)\s*(\\\\?)$/U".$utf8Mod, $line, $match)) {
                        $multiline = ($match[2] == '\\');
                        if (strlen($match[1])) {
                            $sp = preg_split('/(?<!\\\\)\#/', $match[1], -1, PREG_SPLIT_NO_EMPTY);
                            $this->properties[$key] .= ' '.str_replace($escapedChars, $unescape, trim($sp[0]));
                        } else {
                            $this->properties[$key] .= ' ';
                        }
                    } else {
                        throw new Exception('Syntaxe error in file properties '.$this->fileName.' line '.$linenumber, 215);
                    }
                } elseif (preg_match("/^\s*(.+)\s*=\s*(.*)\s*(\\\\?)$/U".$utf8Mod, $line, $match)) {
                    // we got a key=value 
                    $key = $match[1];
                    $multiline = ($match[3] == '\\');
                    $sp = preg_split('/(?<!\\\\)\#/', $match[2], -1, PREG_SPLIT_NO_EMPTY);
                    if (count($sp)) {
                        $value = trim($sp[0]);
                    } else {
                        $value = '';
                    }
                    $this->properties[$key] = str_replace($escapedChars, $unescape, $value);

                } elseif (preg_match("/^\s*(\#.*)?$/", $line, $match)) {
                    // ok, just a comment
                } else {
                    throw new Exception('Syntaxe error in file properties '.$this->fileName.' line '.$linenumber, 216);
                }
            }
        }
        fclose($f);
    }
}
