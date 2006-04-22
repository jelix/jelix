<?php
/**
* @package    jelix
* @subpackage auth
* @version    $Id:$
* @author     Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue d'une branche experimentale du
* framework Copix 2.3dev. http://www.copix.org (jAuth)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteur initial : Laurent Jouanneau
* Adaptée pour Jelix par Laurent Jouanneau
*/

// pas de méthode pour cet objet, car le user peut ne pas etre
// une instance de jUser, tout dépend du driver..
class jAuthUser {
    var $login = '';
    var $level = 0;
    var $email ='';
}

?>
