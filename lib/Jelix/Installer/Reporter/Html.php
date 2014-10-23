<?php
/**
* @author      Laurent Jouanneau
* @copyright   2007-2014 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Reporter {
// enclose namespace here because this file is inserted into checker.php in some builds


/**
 * an HTML reporter
 */
class Html implements \Jelix\Installer\ReporterInterface {

    /**
     * @var \Jelix\SimpleLocalization\Container
     */
    protected $messageProvider;

    function __construct(\Jelix\SimpleLocalization\Container $messageProvider) {
        $this->messageProvider = $messageProvider;
    }

    function start(){
        echo '<ul class="checkresults">';
    }

    function message($message, $type=''){
        echo '<li class="'.$type.'">'.htmlspecialchars($message).'</li>';
    }
    
    function end($results){
        echo '</ul>';

        $nbError = $results['error'];
        $nbWarning = $results['warning'];
        $nbNotice = $results['notice'];

        echo '<div class="results">';
        if ($nbError) {
            echo ' '.$nbError. $this->messageProvider->get( ($nbError > 1?'number.errors':'number.error'));
        }
        if ($nbWarning) {
            echo ' '.$nbWarning. $this->messageProvider->get(($nbWarning > 1?'number.warnings':'number.warning'));
        }
        if ($nbNotice) {
            echo ' '.$nbNotice. $this->messageProvider->get(($nbNotice > 1?'number.notices':'number.notice'));
        }

        if($nbError){
            echo '<p>'.$this->messageProvider->get(($nbError > 1?'conclusion.errors':'conclusion.error')).'</p>';
        }else if($nbWarning){
            echo '<p>'.$this->messageProvider->get(($nbWarning > 1?'conclusion.warnings':'conclusion.warning')).'</p>';
        }else if($nbNotice){
            echo '<p>'.$this->messageProvider->get(($nbNotice > 1?'conclusion.notices':'conclusion.notice')).'</p>';
        }else{
            echo '<p>'.$this->messageProvider->get('conclusion.ok').'</p>';
        }
        echo "</div>";
    }
}

}// end of namespace
