<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Methodes issues originellement de la classe  CopixDbWidget du framework Copix 2.3dev20050901. http://www.copix.org
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

class jDBWidget {
    /**
    * a jDbConnection object
    */
    private $_conn;

    /**
    * Constructor
    */
    function __construct ($connection){
        $this->_conn = $connection;
    }

    /**
    * Effectue une requête, renvoi une ligne de resultat sous forme d'objet et libere les ressources.
    * @param   string   $query   requète SQL
    * @return  object  objet contenant les champs  sous forme de propriétés, de la ligne sélectionnée
    */
    public function  fetchFirst($query){
        $rs     =  $this->_conn->query ($query);
        $result =  $rs->fetch ();
        return $result;
    }

    /**
    * Effectue une requête, et met à jour les propriétes de l'objet passé en paramètre
    * @param   string   $query   requète SQL
    * @param   object ou string  object à remplir ou nom de la classe de l'objet à remplir
    * @return  object  objet initialisé rempli
    */
    public function fetchFirstInto ($query, $object){
        $rs     =  $this->_conn->query   ($query);
        $result =  $rs->fetchInto ($object);
        return $result;
    }

    /**
    * Récupère tout les enregistrements d'un select dans un tableau (d'objets)
    * @param   string   $query   requète SQL
    * @return  array    tableau d'objets
    */
    public function fetchAll($query, $limitOffset=null, $limitCount=null){
        if($limitOffset===null || $limitCount===null){
            $rs =  $this->_conn->query ($query);
        }else{
            $rs =  $this->_conn->limitQuery ($query, $limitOffset, $limitCount);
        }
        return $rs->fetchAll ();
    }

    /**
    * Récupère tout les enregistrements d'un select dans un tableau (d'objets)
    * @param   string   $query   requète SQL
    * @param   object ou string  $className object à remplir ou nom de la classe de l'objet à remplir
    * @return  array    tableau d'objets
    */
    public function fetchAllInto($query, $className, $limitOffset=null, $limitCount=null){
        if($limitOffset===null || $limitCount===null){
            $rs =  $this->_conn->query ($query);
        }else{
            $rs =  $this->_conn->limitQuery ($query, $limitOffset, $limitCount);
        }
        $result = array();
        if ($rs){
            while($res = $rs->fetchInto ($className)){
                $result[] =  $res;
            }
        }
        return $result;
    }
}
?>