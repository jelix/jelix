<?php

function jtpl_meta_common_testinc($tpl, $variable)
{
    if (isset($tpl->_meta[$variable])) {
        $tpl->_meta[$variable]++;
    }
    else
        $tpl->_meta[$variable] = 1;
}
