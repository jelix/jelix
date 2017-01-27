/**
* @package      jelix
* @subpackage   forms
* @author       Laurent Jouanneau
* @copyright    2017 Laurent Jouanneau
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

function jelix_datetimepicker_default(aControl, config){
    // we don't have yet a real datetime picker, so show only a
    // datepicker
    jelix_datepicker_default(aControl, config);
}
