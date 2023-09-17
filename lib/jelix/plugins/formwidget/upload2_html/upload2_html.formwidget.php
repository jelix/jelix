<?php
/**
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @author      Claudio Bernardes
 * @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
 *
 * @copyright   2012 Claudio Bernardes
 * @copyright   2006-2020 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * Widget to display the selection of a file to upload.
 *
 * For images upload, some attributes can be set to indicate
 * the url of the image. The url can be forged from a selector or from a base URI.
 * From a selector : action, parameters, and the parameter name that will
 * contain the filename, should be given in attributes  uriAction, uriActionParameters, uriActionFileParameter
 * From a base URI : a baseURI attribute should be given, with the URL on which
 * the filename will be append.
 */
class upload2_htmlFormWidget extends \jelix\forms\HtmlWidget\WidgetBase implements \jelix\forms\HtmlWidget\ParentWidgetInterface
{
    /**
     * @var string the jUrl action selector used to get the URL of the image
     */
    protected $uriAction = '';

    /**
     * @var array parameters for the jUrl object used to get the URL of the image
     */
    protected $uriActionParameters = array();

    /**
     * parameter name containing the control value, for the jUrl object used
     * to get the URL of the image.
     *
     * The parameter can already exist into $uriActionParameters and could contain
     * a `%s` pattern that will be replaced by the value. Else the existing parameter
     * value will be replaced by the new value.
     *
     * @var string parameter name containing the control value
     */
    protected $uriActionFileParameter = '';

    /**
     * @var string base URI of the image
     */
    protected $baseURI = '';

    protected $imgMaxWidth = 0;

    protected $imgMaxHeight = 0;

    /**
     * @var string indicate how to show new image
     * - 'filename' to show only its filename
     * - 'dataURI' to load the new image with a data URI created from the temporary file. Warning: the data URI can be huge for big images.
     *    Use this mode only for little images.
     * - 'URL' to load the new image like the original image. It means that the application has save the image
     *    as a way that it is accessible from the web, directly in a directory (baseURI), or from a action (see uriAction*).
     */
    protected $showModeForNewImage = 'filename';

    /**
     * @param array $attr
     */
    protected function filterUploadAttributes(&$attr)
    {
        foreach (array('uriAction', 'uriActionParameters', 'uriActionFileParameter',
            'baseURI', 'imgMaxWidth', 'imgMaxHeight', 'showModeForNewImage') as $parameter) {
            if (isset($attr[$parameter])) {
                $this->{$parameter} = $attr[$parameter];
                unset($attr[$parameter]);
            }
        }
    }

    //------ ParentBuilderInterface

    public function addJs($js)
    {
        $this->parentWidget->addJs($js);
    }

    public function addFinalJs($js)
    {
        $this->parentWidget->addFinalJs($js);
    }

    public function controlJsChild()
    {
        return true;
    }

    // -------- WidgetInterface

    public function setDefaultAttributes($attr)
    {
        $this->filterUploadAttributes($attr);
        parent::setDefaultAttributes($attr);
    }

    public function setAttributes($attr)
    {
        $this->filterUploadAttributes($attr);
        parent::setAttributes($attr);
    }

    protected function jsChoiceInternal()
    {
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $this->parentWidget->addJs('c = new '.$jFormsJsVarName."ControlChoice('".$this->ctrl->ref."_jf_action', ".$this->escJsStr($this->ctrl->label).");\n");
        if ($this->ctrl->isReadOnly()) {
            $this->parentWidget->addJs("c.readOnly = true;\n");
        }
        $this->parentWidget->addJs("c.required = true;\n");
        $this->parentWidget->addJs($jFormsJsVarName.".tForm.addControl(c);\n");
        $this->parentWidget->addJs("(function(up){let c;\n");
    }

    protected function outputJs()
    {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $this->parentWidget->addJs('c = new '.$jFormsJsVarName."ControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n");
        $this->commonJs();
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

        if (count($choices) <= 1) {
            $this->outputJs();
            echo '<input type="hidden" name="'.$this->ctrl->ref.'_jf_action" value="new" />';
            $this->displayInputFile($attr);

            return;
        }

        $this->displayStartChoice($attr['id'].'_choice_list', '');

        $idItem = $this->builder->getName().'_'.$this->ctrl->ref.'_jf_action_';
        $idChoice = $this->builder->getName().'_'.$this->ctrl->ref;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();
        $attrRadio = ' type="radio" name="'.$this->ctrl->ref.'_jf_action"'.
            ' onclick="'.$jFormsJsVarName.'.getForm(\''.$this->builder->getName().
            '\').getControl(\''.$this->ctrl->ref.'_jf_action\').activate(\'';
        $attrRadioSuffix = '\')"';

        if ($this->ctrl->isReadOnly()) {
            $attrRadio .= ' readonly';
        }
        $this->jsChoiceInternal();

        if (isset($choices['keep'])) {
            $this->displayStartChoiceItem(
                $idItem.'keep_item',
                $idChoice.'_jf_action_keep',
                $attrRadio.'keep'.$attrRadioSuffix.' value="keep"',
                ($action == 'keep'),
                (
                    $choices['keep'] === '' ?
                    jLocale::get('jelix~jforms.upload.choice.keep.empty')
                    : jLocale::get('jelix~jforms.upload.choice.keep')
                )
            );

            if ($choices['keep'] !== '') {
                $this->_outputControlValue($choices['keep'], 'original');
            }
            $this->displayEndChoiceItem();
            $this->parentWidget->addJs("up.items['keep']=[];\n");
        }

        if (isset($choices['keepnew'])) {
            $this->displayStartChoiceItem(
                $idItem.'keepnew_item',
                $idChoice.'_jf_action_keepnew',
                $attrRadio.'keepnew'.$attrRadioSuffix.' value="keepnew"',
                ($action == 'keepnew'),
                jLocale::get('jelix~jforms.upload.choice.keepnew')
            );

            $this->_outputControlValue($choices['keepnew'], 'new');
            $this->displayEndChoiceItem();
            $this->parentWidget->addJs("up.items['keepnew']=[];\n");
        }

        $this->displayStartChoiceItem(
            $idItem.'new_item',
            $idChoice.'_jf_action_new',
            $attrRadio.'new'.$attrRadioSuffix.' value="new"',
            ($action == 'new'),
            jLocale::get('jelix~jforms.upload.choice.new')
        );
        $this->displayInputFile($attr);
        $this->displayEndChoiceItem();

        $this->parentWidget->addJs('c = new '.$jFormsJsVarName."ControlString('".$this->ctrl->ref."', ".$this->escJsStr($this->ctrl->label).");\n");
        $this->parentWidget->addJs($this->commonGetJsConstraints());
        $this->parentWidget->addJs("up.addControl(c, 'new');\n");

        if (isset($choices['del'])) {
            $this->displayStartChoiceItem(
                $idItem.'del_item',
                $idChoice.'_jf_action_del',
                $attrRadio.'del'.$attrRadioSuffix.' value="del"',
                ($action == 'del'),
                jLocale::get('jelix~jforms.upload.choice.del')
            );
            $this->displayEndChoiceItem();
            $this->parentWidget->addJs("up.items['del']=[];\n");
        }

        $this->parentWidget->addJs("up.activate('".$action."');})(c);\n");

        $this->displayEndChoice();
    }

    protected function displayStartChoice($blockId, $attrs)
    {
        echo '<ul class="jforms-choice" id="'.$blockId.'" '.$attrs.'>', "\n";
    }

    protected function displayStartChoiceItem($idItem, $idRadio, $attrRadio, $checked, $label)
    {
        echo '<li id="'.$idItem.'">',
            '<label> <input '.$attrRadio.' id="'.$idRadio.'"  '.($checked ? 'checked' : '').'/> ';
        echo $label.'</label> ';
    }

    protected function displayInputFile($attr)
    {
        echo '<input';
        $this->_outputAttr($attr);
        echo '/>';
    }

    protected function displayEndChoiceItem()
    {
        echo "</li>\n";
    }

    protected function displayEndChoice()
    {
        echo "</ul>\n";
    }

    public function outputControlValue()
    {
        $value = $this->getValue();
        $this->_outputControlValue($value);
    }

    protected function getImageURI($fileName)
    {
        if ($this->baseURI) {
            $url = htmlspecialchars($this->baseURI.$fileName);
        } else if ($this->uriAction) {
            $params = $this->uriActionParameters;
            if ($this->uriActionFileParameter) {
                // replace %s by the value into the uri action parameter
                $pname = $this->uriActionFileParameter;
                if (isset($params[$pname]) && strpos($params[$pname], '%s') !== false) {
                    $params[$pname] = str_replace('%s', $fileName, $params[$pname]);
                } else {
                    $params[$pname] = $fileName;
                }
            }
            $url = jUrl::get($this->uriAction, $params, jUrl::XMLSTRING);
        }
        else {
            $url = '';
        }
        return $url;
    }

    protected function _outputControlValue($fileName, $suffixId = '')
    {
        $value = $this->ctrl->getDisplayValue($fileName);
        $attr = $this->getValueAttributes();
        if ($suffixId) {
            $attr['id'] .= $suffixId;
        }

        $mimeType = jFile::getMimeTypeFromFilename($value);
        $url = '';
        if (strpos($mimeType, 'image/') === 0) {
            if ($suffixId != 'new') {
                $url = $this->getImageURI($value);
            }
            else if ($this->showModeForNewImage != 'filename') {
                if ($this->showModeForNewImage == 'dataURI') {
                    $file = $this->ctrl->getTempFile($value);
                    if (file_exists($file)) {
                        $url = 'data:'.$mimeType.';base64,'.base64_encode(file_get_contents($file));
                    }
                }
                else {
                    $url = $this->getImageURI($value);
                }
            }
        }

        if ($url) {
            $style = '';
            if ($this->imgMaxHeight) {
                $style .= 'max-height:'.$this->imgMaxHeight.'px;';
            }
            if ($this->imgMaxWidth) {
                $style .= 'max-width:'.$this->imgMaxWidth.'px;';
            }
            $this->displayValueAsImage($attr, $url, $value, $style);
        } else {
            $this->displayValueAsFilename($attr, $value, $mimeType);
        }
    }

    protected function displayValueAsImage($attr, $url, $filename, $style)
    {
        echo '<span ';
        $this->_outputAttr($attr);
        echo '>';
        echo '<a href="'.$url.'"><img src="'.$url.'" alt="'.$filename.'"'.($style ? ' style="'.$style.'"' : '').' /></a>';
        echo '</span>';
    }

    protected function displayValueASFilename($attr, $filename, $mimeType)
    {
        echo '<span ';
        $this->_outputAttr($attr);
        echo '>';
        echo htmlspecialchars($filename);
        echo '</span>';
    }
}
