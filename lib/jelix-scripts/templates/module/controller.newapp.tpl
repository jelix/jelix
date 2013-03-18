<?php
/**
* @package   %%appname%%
* @subpackage %%module%%
* @author    %%default_creator_name%%
* @copyright %%default_copyright%%
* @link      %%default_website%%
* @license   %%default_license_url%% %%default_license%%
*/

class %%name%%Ctrl extends jController {
    /**
    *
    */
    function %%method%%() {
        $rep = $this->getResponse('html');

        // this is a call for the 'welcome' zone after creating a new application
        // remove this line !
        $prj = new jProjectxml();
        $prjInfo = $prj->getInfo();
        $rep->title = $prjInfo->name . ' - '. jLocale::get('jelix~jelix.newapp.h1');
        $rep->body->assignZone('MAIN', 'jelix~check_install');

        return $rep;
    }
}
