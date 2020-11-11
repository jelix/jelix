/**
 * @author       Laurent Jouanneau
 * @copyright    2020 Laurent Jouanneau
 * @link         https://jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

import jFormsJQControlBoolean from './jFormsJQControlBoolean.js';
import jFormsJQControlChoice from './jFormsJQControlChoice.js';
import jFormsJQControlColor from './jFormsJQControlColor.js';
import jFormsJQControlConfirm from './jFormsJQControlConfirm.js';
import jFormsJQControlDate from './jFormsJQControlDate.js';
import jFormsJQControlDatetime from './jFormsJQControlDatetime.js';
import jFormsJQControlDecimal from './jFormsJQControlDecimal.js';
import jFormsJQControlEmail from './jFormsJQControlEmail.js';
import jFormsJQControlGroup from './jFormsJQControlGroup.js';
import jFormsJQControlHexadecimal from './jFormsJQControlHexadecimal.js';
import jFormsJQControlHtml from './jFormsJQControlHtml.js';
import jFormsJQControlInteger from './jFormsJQControlInteger.js';
import jFormsJQControlIpv4 from './jFormsJQControlIpv4.js';
import jFormsJQControlIpv6 from './jFormsJQControlIpv6.js';
import jFormsJQControlLocaleDate from './jFormsJQControlLocaleDate.js';
import jFormsJQControlLocaleDatetime from './jFormsJQControlLocaleDatetime.js';
import jFormsJQControlSecret from './jFormsJQControlSecret.js';
import jFormsJQControlString from './jFormsJQControlString.js';
import jFormsJQControlTime2 from './jFormsJQControlTime2.js';
import jFormsJQControlTime from './jFormsJQControlTime.js';
import jFormsJQControlUrl from './jFormsJQControlUrl.js';
import jFormsJQErrorDecoratorAlert from './jFormsJQErrorDecoratorAlert.js';
import jFormsJQErrorDecoratorHtml from './jFormsJQErrorDecoratorHtml.js';
import jFormsJQForm from './jFormsJQForm.js';
import jFormsJQ from './jFormsJQ.js';


var __jforms_jquery = {
    jFormsJQ,
    jFormsJQForm,
    jFormsJQErrorDecoratorHtml,
    jFormsJQErrorDecoratorAlert,
    jFormsJQControlUrl,
    jFormsJQControlTime,
    jFormsJQControlTime2,
    jFormsJQControlString,
    jFormsJQControlSecret,
    jFormsJQControlLocaleDatetime,
    jFormsJQControlLocaleDate,
    jFormsJQControlIpv6,
    jFormsJQControlIpv4,
    jFormsJQControlInteger,
    jFormsJQControlHtml,
    jFormsJQControlHexadecimal,
    jFormsJQControlGroup,
    jFormsJQControlEmail,
    jFormsJQControlDecimal,
    jFormsJQControlDatetime,
    jFormsJQControlDate,
    jFormsJQControlConfirm,
    jFormsJQControlColor,
    jFormsJQControlChoice,
    jFormsJQControlBoolean
};

for (var o in __jforms_jquery) {
    window[o] = __jforms_jquery[o];
}

