<?php
/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author     Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright  2010-2024 Laurent Jouanneau
 * @copyright  2015 Julien Issler
 * @licence    http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

use Jelix\Forms\Forms;

/**
 * @package    jelix-modules
 * @subpackage jelix-module
 */
class jformsCtrl extends jController
{
    /**
     * web service for XHR request when a control should be filled with a list
     * of values, depending on the value of another control.
     */
    public function getListData()
    {
        if (!$this->request->isPostMethod() || !$this->request->isAjax()) {
            $rep = $this->getResponse('text', true);
            $rep->setHttpStatus('405', 'Method Not Allowed');

            return $rep;
        }

        $rep = $this->getResponse('json', true);

        try {
            $form = Forms::get($this->param('__form'), $this->param('__formid'));
            if (!$form) {
                throw new Exception('Unknown form');
            }
        } catch (Exception $e) {
            $rep = $this->getResponse('text', true);
            $rep->setHttpStatus('422', 'Unprocessable entity');
            $rep->content = 'invalid form selector';

            return $rep;
        }

        // check CSRF
        if ($form->securityLevel == \Jelix\Forms\FormInstance::SECURITY_CSRF) {
            if (!$form->isValidToken($this->param('__JFORMS_TOKEN__'))) {
                $rep = $this->getResponse('text', true);
                $rep->setHttpStatus('422', 'Unprocessable entity');
                $rep->content = 'invalid token';
                jLog::logEx(new jException('jelix~formserr.invalid.token'), 'error');

                return $rep;
            }
        }

        // event so the form can be prepared correctly for forms made dynamically
        jEvent::notify('jformsPrepareToFillDynamicList', array('form' => $form, 'controlRef' => $this->param('__ref')));

        // retrieve the control to fill
        $control = $form->getControl($this->param('__ref'));
        if (!$control || (!($control instanceof \Jelix\Forms\Controls\AbstractDatasourceControl) && !($control instanceof jFormsControlDatasource))) {
            $rep = $this->getResponse('text', true);
            $rep->setHttpStatus('422', 'Unprocessable entity');
            $rep->content = 'bad control';

            return $rep;
        }

        if (!($control->datasource instanceof \Jelix\Forms\Datasource\DynamicDatasource
            || $control->datasource instanceof jIFormsDynamicDatasource)
        ) {
            $rep = $this->getResponse('text', true);
            $rep->setHttpStatus('422', 'Unprocessable entity');
            $rep->content = 'not supported datasource type';

            return $rep;
        }

        $dependentControls = $control->datasource->getCriteriaControls();
        if (!$dependentControls) {
            $rep = $this->getResponse('text', true);
            $rep->setHttpStatus('422', 'Unprocessable entity');
            $rep->content = 'no dependent controls';

            return $rep;
        }

        foreach ($dependentControls as $ctname) {
            $form->setData($ctname, $this->param($ctname));
        }

        $rep->data = array();
        if ($control->datasource->hasGroupedData()) {
            foreach ($control->datasource->getData($form) as $k => $items) {
                $data = array();
                foreach ($items as $k2 => $v) {
                    $data[] = array('value' => $k2, 'label' => $v);
                }
                $rep->data[] = array('items' => $data, 'label' => $k);
            }
        } else {
            foreach ($control->datasource->getData($form) as $k => $v) {
                $rep->data[] = array('value' => $k, 'label' => $v);
            }
        }

        return $rep;
    }

    /**
     * generates the javascript that verifies the form.
     *
     * @throws Exception
     *
     * @return jResponse
     */
    public function js()
    {
        $rep = $this->getResponse('text', true);
        $frmSel = $this->param('__form');
        $frmId = $this->param('__fid');
        if ($frmSel == '') {
            throw new \Exception('missing form selector parameters');
        }
        $form = Forms::get($frmSel, $frmId);
        if (!$form) {
            throw new jHttp404NotFoundException();
        }

        $rep->content = $form->getContainer()->privateData['__jforms_js'];
        $rep->mimeType = 'application/javascript';

        return $rep;
    }
}
