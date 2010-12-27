<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @copyright  2006-2010 Laurent Jouanneau
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * this class is formatting an error message for a logger
 */
class jLogErrorMessage implements jILoggerMessage {
    protected $category;
    protected $message;
    protected $file;
    protected $line;
    protected $trace;
    protected $code;
    protected $format = '%date%\t%ip%\t[%code%]\t%msg%\t%file%\t%line%\n\t%url%\n%params%\n%trace%';

    public function __construct($category, $code, $message, $file, $line, $trace) {
        $this->category = $category;
        $this->message = $message;
        $this->code = $code;
        $this->file = $file;
        $this->line = $line;
        $this->trace = $trace;
    }

    public function getCode() {
        return $this->code;
    }

    public function setFormat($format) {
        $this->format = $format;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getFormatedMessage() {
        global $gJCoord, $gJConfig;

        $url = isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'Unknow requested URI';
        // url params including module and action
        if ($gJCoord->request) {
            $params = str_replace("\n", ' ', var_export($gJCoord->request->params, true));
            $remoteAddr = $gJCoord->request->getIP();
        }
        else {
            $params = isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:'';
            // When we are in cmdline we need to fix the remoteAddr
            $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        }

        $traceLog="";
        foreach($this->trace as $k=>$t){
            $traceLog.="\n\t$k\t".(isset($t['class'])?$t['class'].$t['type']:'').$t['function']."()\t";
            $traceLog.=(isset($t['file'])?$t['file']:'[php]').' : '.(isset($t['line'])?$t['line']:'');
        }

        $messageLog = strtr($this->format, array(
            '%date%' => date("Y-m-d H:i:s"),
            '%typeerror%'=>$this->category,
            '%code%' => $this->code,
            '%msg%'  => $this->message,
            '%ip%'   => $remoteAddr,
            '%url%'  => $url,
            '%params%'=>$params,
            '%file%' => $this->file,
            '%line%' => $this->line,
            '%trace%' => $traceLog,
            '\t' =>"\t",
            '\n' => "\n"
        ));

        return $messageLog;
    }
}