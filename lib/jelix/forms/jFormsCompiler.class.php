<?php
/**
* @package    jelix
* @subpackage jforms
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jFormsCompiler implements jISimpleCompiler {

    public function compile($selector){
        global $gJCoord;
        $sel = clone $selector;

        $sourceFile = $selector->getPath();
        $cachefile = $selector->getCompiledFilePath();

        // compilation du fichier xml
        $xml = simplexml_load_file ( $sourceFile);
        if(!$xml){
           return false;
        }

        $foundAction=false;
        foreach($xml->request as $req){
            if(isset($req['type'])){
                $requesttype=$req['type'];
            }else{
                trigger_error(jLocale::get('jelix~errors.ac.xml.request.type.attr.missing',array($sourceFile)), E_USER_ERROR);
                jContext::pop();
                return false;
            }

        }





        return true;
    }
}



interface jIFormGenerator {

   // on indique un objet form
   // il renvoi dans un tableau le code généré correspondant
   /*
   startform : code généré pour le debut du formulaire (balise <form> en html) Peut contenir %ATTR%
   head : code généré à ajouter dans l'en-tête de page
   controls : tableau assoc de chaque contrôle généré. Peuvent contenir %ATTR%
   endform : code généré pour la fin du formulaire


   %ATTR% : remplacés par les attributs supplémentaires indiqués par l'utilisateur dans le template
   */
   function buildForm($formObject);

}


?>