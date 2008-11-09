<?php
/**
* @package   %%appname%%
* @subpackage %%module%%
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @licence   %%default_licence_url%% %%default_licence%%
*/

class %%name%%Ctrl extends jControllerCmdLine {

    /**
    * Options to the command line
    *  'method_name' => array('-option_name' => true/false)
    * true means that a value should be provided for the option on the command line
    */
    protected $allowed_options = array(
            '%%method%%' => array());

    /**
     * Parameters for the command line
     * 'method_name' => array('parameter_name' => true/false)
     * false means that the parameter is optionnal. All parameters which follow an optional parameter
     * is optional
     */
    protected $allowed_parameters = array(
            '%%method%%' => array());
    /**
    *
    */
    function %%method%%() {
        $rep = $this->getResponse(); // cmdline response by default

        return $rep;
    }
}
