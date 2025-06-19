<?php
/**
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @author      Claudio Bernardes
 * @contributor Laurent Jouanneau, Julien Issler, Dominique Papin
 *
 * @copyright   2012 Claudio Bernardes
 * @copyright   2006-2017 Laurent Jouanneau, 2008-2011 Julien Issler, 2008 Dominique Papin
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * HTML form builder.
 *
 * @package     jelix
 * @subpackage  forms_widget_plugin
 *
 * @see http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 */
class datetime_htmlFormWidget extends \Jelix\Forms\HtmlWidget\WidgetBase
{
    public function outputMetaContent($resp)
    {
        $config = $this->ctrl->datepickerConfig ?
                    $this->ctrl->datepickerConfig :
                    jApp::config()->forms['datetimepicker'];
        $resp->addAssets('jforms_datetimepicker_'.$config);
    }

    protected function outputJs()
    {
        $ctrl = $this->ctrl;
        $jFormsJsVarName = $this->builder->getjFormsJsVarName();

        $js = 'c = new '.$jFormsJsVarName."ControlDatetime('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        $js .= "c.multiFields = true;\n";
        $minDate = $ctrl->datatype->getFacet('minValue');
        $maxDate = $ctrl->datatype->getFacet('maxValue');
        if ($minDate) {
            $js .= "c.minDate = '".$minDate->toString(jDateTime::DB_DFORMAT)."';\n";
        }
        if ($maxDate) {
            $js .= "c.maxDate = '".$maxDate->toString(jDateTime::DB_DFORMAT)."';\n";
        }

        $this->parentWidget->addJs($js);
        $this->commonJs();

        if ($ctrl instanceof jFormsControlDatetime
            || get_class($ctrl->datatype) == 'jDatatypeDateTime'
            || get_class($ctrl->datatype) == 'jDatatypeLocaleDateTime') {
            $config = $ctrl->datepickerConfig != '' ?
                        $ctrl->datepickerConfig :
                        jApp::config()->forms['datetimepicker'];
            if ($config) {
                $this->parentWidget->addJs('jelix_datetimepicker_'.$config."(c, jFormsJQ.config);\n");
            }
        }
    }

    public function outputControl()
    {
        $attr = $this->getControlAttributes();
        $value = $this->getValue();

        $attr['id'] = $this->builder->getName().'_'.$this->ctrl->ref.'_';
        $v = array('year' => '', 'month' => '', 'day' => '', 'hour' => '', 'minutes' => '', 'seconds' => '');
        if (preg_match('#^(\d{4})?-(\d{2})?-(\d{2})?(?: |T)(\d{2})?:(\d{2})?(:(\d{2})?)?(?:$|\\s|\\.)#', $value, $matches)) {
            if (isset($matches[1])) {
                $v['year'] = $matches[1];
            }
            if (isset($matches[2])) {
                $v['month'] = $matches[2];
            }
            if (isset($matches[3])) {
                $v['day'] = $matches[3];
            }
            if (isset($matches[4])) {
                $v['hour'] = $matches[4];
            }
            if (isset($matches[5])) {
                $v['minutes'] = $matches[5];
            }
            if (isset($matches[7])) {
                $v['seconds'] = $matches[7];
            }
        } elseif (preg_match('#^(\d{4})?-(\d{2})?-(\d{2})?($|\\s)#', $value, $matches)) {
            if (isset($matches[1])) {
                $v['year'] = $matches[1];
            }
            if (isset($matches[2])) {
                $v['month'] = $matches[2];
            }
            if (isset($matches[3])) {
                $v['day'] = $matches[3];
            }
            $v['hour'] = '00';
            $v['minutes'] = '00';
            $v['seconds'] = '00';
        }
        $f = jLocale::get('jelix~format.datetime');
        for ($i = 0; $i < strlen($f); ++$i) {
            if ($f[$i] == 'Y') {
                $this->_outputDateControlYear($this->ctrl, $attr, $v['year']);
            } elseif ($f[$i] == 'm') {
                $this->_outputDateControlMonth($this->ctrl, $attr, $v['month']);
            } elseif ($f[$i] == 'd') {
                $this->_outputDateControlDay($this->ctrl, $attr, $v['day']);
            } elseif ($f[$i] == 'H') {
                $this->_outputDateControlHour($this->ctrl, $attr, $v['hour']);
            } elseif ($f[$i] == 'i') {
                $this->_outputDateControlMinutes($this->ctrl, $attr, $v['minutes']);
            } elseif ($f[$i] == 's') {
                $this->_outputDateControlSeconds($this->ctrl, $attr, $v['seconds']);
            } else {
                echo ' ';
            }
        }
        echo "\n";
        $this->outputJs();
    }

    protected function _outputDateControlDay($ctrl, $attr, $value)
    {
        $attr['name'] = $ctrl->ref.'[day]';
        $attr['id'] .= 'day';
        if (jApp::config()->forms['controls.datetime.input'] == 'textboxes') {
            $attr['value'] = $value;
            echo '<input type="text" size="2" maxlength="2" placeholder="'.htmlspecialchars(jLocale::get('jelix~jforms.date.day.label')).'"';
            $this->_outputAttr($attr);
            echo '/>';
        } else {
            echo '<select';
            $this->_outputAttr($attr);
            echo '><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.date.day.label')).'</option>';
            for ($i = 1; $i < 32; ++$i) {
                $k = ($i < 10) ? '0'.$i : $i;
                echo '<option value="'.$k.'"'.($k == $value ? ' selected="selected"' : '').'>'.$k.'</option>';
            }
            echo '</select>';
        }
    }

    protected function _outputDateControlMonth($ctrl, $attr, $value)
    {
        $attr['name'] = $ctrl->ref.'[month]';
        $attr['id'] .= 'month';
        if (jApp::config()->forms['controls.datetime.input'] == 'textboxes') {
            $attr['value'] = $value;
            echo '<input type="text" size="2" maxlength="2" placeholder="'.htmlspecialchars(jLocale::get('jelix~jforms.date.month.label')).'"';
            $this->_outputAttr($attr);
            echo '/>';
        } else {
            $monthLabels = jApp::config()->forms['controls.datetime.months.labels'];
            echo '<select';
            $this->_outputAttr($attr);
            echo '><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.date.month.label')).'</option>';
            for ($i = 1; $i < 13; ++$i) {
                $k = ($i < 10) ? '0'.$i : $i;
                if ($monthLabels == 'names') {
                    $l = htmlspecialchars(jLocale::get('jelix~date_time.month.'.$k.'.label'));
                } elseif ($monthLabels == 'shortnames') {
                    $l = htmlspecialchars(jLocale::get('jelix~date_time.month.'.$k.'.shortlabel'));
                } else {
                    $l = $k;
                }
                echo '<option value="'.$k.'"'.($k == $value ? ' selected="selected"' : '').'>'.$l.'</option>';
            }
            echo '</select>';
        }
    }

    protected function _outputDateControlYear($ctrl, $attr, $value)
    {
        $attr['name'] = $ctrl->ref.'[year]';
        $attr['id'] .= 'year';
        if (jApp::config()->forms['controls.datetime.input'] == 'textboxes') {
            $attr['value'] = $value;
            echo '<input type="text" size="4" maxlength="4" placeholder="'.htmlspecialchars(jLocale::get('jelix~jforms.date.year.label')).'"';
            $this->_outputAttr($attr);
            echo '/>';
        } else {
            $minDate = $ctrl->datatype->getFacet('minValue');
            $maxDate = $ctrl->datatype->getFacet('maxValue');
            if ($minDate && $maxDate) {
                echo '<select';
                $this->_outputAttr($attr);
                echo '><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.date.year.label')).'</option>';
                for ($i = $minDate->year; $i <= $maxDate->year; ++$i) {
                    echo '<option value="'.$i.'"'.($i == $value ? ' selected="selected"' : '').'>'.$i.'</option>';
                }
                echo '</select>';
            } else {
                $attr['value'] = $value;
                echo '<input type="text" size="4" maxlength="4"';
                $this->_outputAttr($attr);
                echo '/>';
            }
        }
    }

    protected function _outputDateControlHour($ctrl, $attr, $value)
    {
        $attr['name'] = $ctrl->ref.'[hour]';
        $attr['id'] .= 'hour';
        if (jApp::config()->forms['controls.datetime.input'] == 'textboxes') {
            $attr['value'] = $value;
            echo '<input type="text" size="2" maxlength="2" placeholder="'.htmlspecialchars(jLocale::get('jelix~jforms.time.hour.label')).'"';
            $this->_outputAttr($attr);
            echo $this->_endt;
        } else {
            echo '<select';
            $this->_outputAttr($attr);
            echo '><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.time.hour.label')).'</option>';
            for ($i = 0; $i < 24; ++$i) {
                $k = ($i < 10) ? '0'.$i : $i;
                echo '<option value="'.$k.'"'.((string) $k === $value ? ' selected="selected"' : '').'>'.$k.'</option>';
            }
            echo '</select>';
        }
    }

    protected function _outputDateControlMinutes($ctrl, $attr, $value)
    {
        $attr['name'] = $ctrl->ref.'[minutes]';
        $attr['id'] .= 'minutes';
        if (jApp::config()->forms['controls.datetime.input'] == 'textboxes') {
            $attr['value'] = $value;
            echo '<input type="text" size="2" maxlength="2" placeholder="'.htmlspecialchars(jLocale::get('jelix~jforms.time.minutes.label')).'"';
            $this->_outputAttr($attr);
            echo $this->_endt;
        } else {
            echo '<select';
            $this->_outputAttr($attr);
            echo '><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.time.minutes.label')).'</option>';
            for ($i = 0; $i < 60; ++$i) {
                $k = ($i < 10) ? '0'.$i : $i;
                echo '<option value="'.$k.'"'.((string) $k === $value ? ' selected="selected"' : '').'>'.$k.'</option>';
            }
            echo '</select>';
        }
    }

    protected function _outputDateControlSeconds($ctrl, $attr, $value)
    {
        $attr['name'] = $ctrl->ref.'[seconds]';
        $attr['id'] .= 'seconds';
        if (!$ctrl->enableSeconds) {
            echo '<input type="hidden" id="'.$attr['id'].'" name="'.$attr['name'].'" value="'.$value.'"/>';
        } elseif (jApp::config()->forms['controls.datetime.input'] == 'textboxes') {
            $attr['value'] = $value;
            echo '<input type="text" placeholder="'.htmlspecialchars(jLocale::get('jelix~jforms.time.seconds.label')).'"';
            $this->_outputAttr($attr);
            echo $this->_endt;
        } else {
            echo '<select';
            $this->_outputAttr($attr);
            echo '><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.time.seconds.label')).'</option>';
            for ($i = 0; $i < 60; ++$i) {
                $k = ($i < 10) ? '0'.$i : $i;
                echo '<option value="'.$k.'"'.((string) $k === $value ? ' selected="selected"' : '').'>'.$k.'</option>';
            }
            echo '</select>';
        }
    }
}
