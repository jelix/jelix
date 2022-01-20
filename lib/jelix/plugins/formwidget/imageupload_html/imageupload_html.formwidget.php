<?php
/**
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @author      Laurent Jouanneau
 * @copyright   2020-2022 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
require_once __DIR__.'/../upload2_html/upload2_html.formwidget.php';

/**
 * Widget to display an image selector/editor.
 *
 * @package     jelix
 * @subpackage  forms_widget_plugin
 */
class imageupload_htmlFormWidget extends upload2_htmlFormWidget
{
    protected $dialogWidth = 1024;
    protected $dialogHeight = 768;

    protected $newImgMaxWidth = 0;
    protected $newImgMaxHeight = 0;

    /**
     * @param array $attr
     */
    protected function filterImageAttributes(&$attr)
    {
        foreach (array('dialogWidth', 'dialogHeight', 'newImgMaxWidth', 'newImgMaxHeight') as $parameter) {
            if (isset($attr[$parameter])) {
                $this->{$parameter} = $attr[$parameter];
                unset($attr[$parameter]);
            }
        }
    }

    public function setDefaultAttributes($attr)
    {
        if ($this->ctrl->maxWidth) {
            $this->newImgMaxWidth = $this->ctrl->maxWidth;
        }

        if ($this->ctrl->maxHeight) {
            $this->newImgMaxHeight = $this->ctrl->maxHeight;
        }

        $this->filterImageAttributes($attr);
        parent::setDefaultAttributes($attr);
    }

    public function setAttributes($attr)
    {
        $this->filterImageAttributes($attr);
        parent::setAttributes($attr);
    }

    public function outputMetaContent($resp)
    {
        $resp->addAssets('jforms_imageupload');
    }

    protected function displayInputImage($attr, $idChoiceItem = '', $existingFile = '')
    {
        $this->displayStartInputFile($attr['id'].'_image_form');

        if ($existingFile) {
            $this->displayModifyButton('#'.$idChoiceItem.'keep_item img', basename($this->ctrl->getOriginalFile()));
        }

        $this->displaySelectButton();

        if ($this->newImgMaxWidth) {
            $attr['data-img-max-width'] = $this->newImgMaxWidth;
        }
        if ($this->newImgMaxHeight) {
            $attr['data-img-max-height'] = $this->newImgMaxHeight;
        }
        echo '<input';
        $this->_outputAttr($attr);
        echo "/>\n";

        $style = 'display:none;';
        if ($this->imgMaxHeight) {
            $style .= 'max-height:'.$this->imgMaxHeight.'px;';
        }
        if ($this->imgMaxWidth) {
            $style .= 'max-width:'.$this->imgMaxWidth.'px;';
        } else {
            $style .= 'max-width:100%;';
        }

        $this->displayImagePreview($style);

        $this->displayDialogEditor();

        $this->displayEndInputFile();

        $this->parentWidget->addJs("jFormsInitImageControl('".$attr['id']."_image_form');\n");
    }

    protected function displayStartInputFile($blockId)
    {
        echo '<div id="'.$blockId.'">';
    }

    protected function displayEndInputFile()
    {
        echo '</div>';
    }

    protected function displaySelectButton()
    {
        echo '<button class="jforms-image-select-btn" type="button">'.jLocale::get('jelix~jforms.upload.picture.choice.new.file').'</button>'."\n";
    }

    protected function displayModifyButton($imageSelector, $currentFileName)
    {
        echo '<button class="jforms-image-modify-btn" type="button" 
            data-current-image="'.$imageSelector.'" 
            data-current-file-name="'.htmlspecialchars($currentFileName).'">'.
            jLocale::get('jelix~jforms.upload.picture.choice.modify').
            '</button>'."\n";
    }

    protected function displayImagePreview($style)
    {
        echo '<br/><img class="jforms-image-preview" style="'.$style.'"/>';
    }

    protected function displayDialogEditor()
    {
        echo '<div class="jforms-image-dialog" style="display: none"
        data-dialog-width="'.$this->dialogWidth.'" data-dialog-height="'.$this->dialogHeight.'"
        data-dialog-title="'.jLocale::get('jelix~jforms.upload.picture.dialog.title').'" 
        data-dialog-ok-label="'.jLocale::get('jelix~ui.buttons.ok').'"
        data-dialog-cancel-label="'.jLocale::get('jelix~ui.buttons.cancel').'">
    <div class="jforms-image-dialog-toolbar">
        <button class="rotateleft" type="button">'.jLocale::get('jelix~jforms.upload.picture.edit.rotateleft').'</button>
        <button class="rotateright" type="button">'.jLocale::get('jelix~jforms.upload.picture.edit.rotateRight').'</button>
        <button class="cropreset" type="button">'.jLocale::get('jelix~jforms.upload.picture.edit.reset').'</button>
    </div>
    <div class="jforms-image-dialog-img-container" style="border:2px solid black;">
        <canvas class="jforms-image-dialog-editor" style="max-width:100%"></canvas>
    </div>
</div>';
    }

    public function outputControl()
    {
        $attr = $this->getControlAttributes();

        /*if($this->ctrl->maxsize){
            echo '<input type="hidden" name="MAX_FILE_SIZE" value="',$this->ctrl->maxsize,'"','/>';
        }*/
        $attr['type'] = 'file';
        $attr['value'] = '';
        if (property_exists($this->ctrl, 'accept') && $this->ctrl->accept != '') {
            $attr['accept'] = $this->ctrl->accept;
        } else {
            $attr['accept'] = 'image/png, image/jpeg';
        }
        if (property_exists($this->ctrl, 'capture') && $this->ctrl->capture) {
            if (is_bool($this->ctrl->capture)) {
                if ($this->ctrl->capture) {
                    $attr['capture'] = 'true';
                }
            } else {
                $attr['capture'] = $this->ctrl->capture;
            }
        }
        $attr['class'] .= ' jforms-image-input';
        $attr['style'] = 'display:none';

        $required = $this->ctrl->required;

        $container = $this->builder->getForm()->getContainer()->privateData[$this->ctrl->ref];
        $originalFile = $container['originalfile'];
        $newFile = $container['newfile'];
        $choices = array();

        $action = 'new';

        if ($originalFile) {
            $choices['keep'] = $originalFile;
            $action = 'keep';
        } else {
            if (!$required) {
                $choices['keep'] = '';
                $action = 'keep';
            }
        }

        if ($newFile) {
            $choices['keepnew'] = $newFile;
            $action = 'keepnew';
        }

        $choices['new'] = true;

        if (!$this->ctrl->isReadOnly()) {
            if (!$required && $originalFile) {
                $choices['del'] = true;
            }
        }
        $jformsVarName = $this->builder->getjFormsJsVarName();

        if (count($choices) <= 1) {
            echo '<input type="hidden" name="'.$this->ctrl->ref.'_jf_action" value="new" />';

            $inputProp = array(
                'label' => $this->ctrl->label,
                'ref' => $this->ctrl->ref,
                'readOnly' => $this->ctrl->isReadOnly(),
                'required' => $this->ctrl->required,
                'alertRequired' => ($this->ctrl->alertRequired ?: \jLocale::get('jelix~formserr.js.err.required', $this->ctrl->label)),
                'alertInvalid' => ($this->ctrl->alertInvalid ?: \jLocale::get('jelix~formserr.js.err.invalid', $this->ctrl->label)),
            );

            $attr['data-jforms-input-props'] = json_encode($inputProp);

            $this->displayInputImage($attr);
            $this->parentWidget->addJs('jFormsInitChoiceControlSingleItem("#'.$attr['id'].'", '.$jformsVarName.');');

            return;
        }

        $idItem = $this->builder->getName().'_'.$this->ctrl->ref.'_jf_action_';
        $idChoice = $this->builder->getName().'_'.$this->ctrl->ref;
        $choiceProp = array(
            'jformsName' => $this->builder->getName(),
            'radioName' => $this->ctrl->ref.'_jf_action',
            'itemIdPrefix' => $idItem,
            'radioIdPrefix' => $idChoice,
            'readOnly' => $this->ctrl->isReadOnly(),
            'label' => $this->ctrl->label,
            'ref' => $this->ctrl->ref,
            'required' => $this->ctrl->required,
            'currentAction' => $action,
            'alertRequired' => ($this->ctrl->alertRequired ?: \jLocale::get('jelix~formserr.js.err.required', $this->ctrl->label)),
            'alertInvalid' => ($this->ctrl->alertInvalid ?: \jLocale::get('jelix~formserr.js.err.invalid', $this->ctrl->label)),
        );

        $this->displayStartChoice(
            $attr['id'].'_choice_list',
            'data-jforms-choice-props="'.htmlspecialchars(json_encode($choiceProp)).'"'
        );

        $attrRadio = ' type="radio" name="'.$this->ctrl->ref.'_jf_action"';

        if ($this->ctrl->isReadOnly()) {
            $attrRadio .= ' readonly';
        }

        if (isset($choices['keep'])) {
            $this->displayStartChoiceItem(
                $idItem.'keep_item',
                $idChoice.'_jf_action_keep',
                $attrRadio.' value="keep"',
                ($action == 'keep'),
                (
                    $choices['keep'] === '' ?
                    jLocale::get('jelix~jforms.upload.picture.choice.keep.empty')
                    : jLocale::get('jelix~jforms.upload.choice.keep')
                )
            );
            if ($choices['keep'] !== '') {
                $this->_outputControlValue($choices['keep'], 'original');
            }
            $this->displayEndChoiceItem();
        }

        if (isset($choices['keepnew'])) {
            $this->displayStartChoiceItem(
                $idItem.'keepnew_item',
                $idChoice.'_jf_action_keepnew',
                $attrRadio.' value="keepnew"',
                ($action == 'keepnew'),
                jLocale::get('jelix~jforms.upload.picture.choice.keepnew')
            );
            $this->_outputControlValue($choices['keepnew'], 'new');
            $this->displayEndChoiceItem();
        }

        $this->displayStartChoiceItem(
            $idItem.'new_item',
            $idChoice.'_jf_action_new',
            $attrRadio.' value="new"',
            ($action == 'new'),
            jLocale::get('jelix~jforms.upload.picture.choice.new')
        );
        $this->displayInputImage($attr, $idItem, $choices['keep']);
        $this->displayEndChoiceItem();
        $this->parentWidget->addJs('jFormsInitChoiceControl("#'.$attr['id'].'_choice_list", '.$jformsVarName.', function(actionId) { jFormsImageSelectorBtnEnable("#'.$attr['id'].'_choice_list", actionId == "new");});');

        if (isset($choices['del'])) {
            $this->displayStartChoiceItem(
                $idItem.'del_item',
                $idChoice.'_jf_action_del',
                $attrRadio.' value="del"',
                ($action == 'del'),
                jLocale::get('jelix~jforms.upload.choice.del')
            );
            $this->displayEndChoiceItem();
        }

        $this->displayEndChoice();
    }
}
