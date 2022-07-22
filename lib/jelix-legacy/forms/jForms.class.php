<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2009 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
require_once JELIX_LIB_PATH.'forms/jFormsBase.class.php';

require_once JELIX_LIB_PATH.'forms/jFormsSession.class.php';

/**
 * static class to manage and call a form.
 *
 * A form is identified by a selector, and each instance of a form have a unique id (formId).
 * This id can be the id of a record for example. If it is not given, the id is set to 0.
 *
 * @package     jelix
 * @subpackage  forms
 */
class jForms
{
    const ID_PARAM = '__forms_id__';

    const DEFAULT_ID = 0;

    const ERRDATA_INVALID = 1;
    const ERRDATA_REQUIRED = 2;
    const ERRDATA_INVALID_FILE_SIZE = 3;
    const ERRDATA_INVALID_FILE_TYPE = 4;
    const ERRDATA_FILE_UPLOAD_ERROR = 5;

    /**
     * pure static class, so no constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return jFormsSession
     */
    protected static function getSession()
    {
        // We store the jFormsSession in the session, just to know when the session is saved, and so to
        // have the opportunity to save the content of jFormsSession into a cache, instead of the session storage
        // See jFormsSession
        if (!isset($_SESSION['JFORMS_SESSION'])) {
            $_SESSION['JFORMS_SESSION'] = new jFormsSession();
        }

        return $_SESSION['JFORMS_SESSION'];
    }

    /**
     * Create a new form with empty data.
     *
     * Call it to create a new form, before to display it.
     * Data of the form are stored in the php session in a jFormsDataContainer object.
     * If a form with same id exists, data are erased.
     *
     * @param string $formSel the selector of the xml jform file
     * @param string $formId  the id of the new instance (an id of a record for example)
     *
     * @return jFormsBase the object representing the form
     */
    public static function create($formSel, $formId = null)
    {
        $session = self::getSession();
        list($container, $sel) = $session->getContainer($formSel, $formId, true);
        jIncluder::inc($sel);
        $c = $sel->getClass();

        return new $c($container->formSelector, $container, true);
    }

    /**
     * get an existing instance of a form.
     *
     * In your controller, call it before to re-display a form with existing data.
     *
     * @param string $formSel the selector of the xml jform file
     * @param string $formId  the id of the form (if you use multiple instance of a form)
     *
     * @return jFormsBase the object representing the form. Return null if there isn't an existing form
     */
    public static function get($formSel, $formId = null)
    {
        $session = self::getSession();
        list($container, $sel) = $session->getContainer($formSel, $formId, false);
        if (!$container) {
            return null;
        }
        jIncluder::inc($sel);
        $c = $sel->getClass();

        return new $c($container->formSelector, $container, false);
    }

    /**
     * get an existing instance of a form, and fill it with data provided by the request.
     *
     * use it in the action called to submit a webform.
     *
     * @param string $formSel the selector of the xml jform file
     * @param string $formId  the id of the form (if you use multiple instance of a form)
     *
     * @return jFormsBase the object representing the form. Return null if there isn't an existing form
     */
    public static function fill($formSel, $formId = null)
    {
        $form = self::get($formSel, $formId);
        if ($form) {
            $form->initFromRequest();
        }

        return $form;
    }

    /**
     * destroy a form in the session.
     *
     * use it after saving data of a form, and if you don't want to re-display the form.
     *
     * @param string $formSel the selector of the xml jform file
     * @param string $formId  the id of the form (if you use multiple instance of a form)
     */
    public static function destroy($formSel, $formId = null)
    {
        $session = self::getSession();
        $session->deleteContainer($formSel, $formId);
    }

    /**
     * destroy all form which are too old and unused.
     *
     * parameters are deprecated and unused
     *
     * @param mixed $formSel
     * @param mixed $life
     */
    public static function clean($formSel = '', $life = 86400)
    {
        $session = self::getSession();
        $session->garbage();
    }
}
