<?php
/**
 * Wikirenderer is a wiki text parser. It can transform a wiki text into xhtml or other formats
 * @package WikiRenderer
 * @author Laurent Jouanneau <jouanneau@netcourrier.com>
 * @copyright 2003-2007 Laurent Jouanneau
 * @link http://wikirenderer.berlios.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public 2.1
 * License as published by the Free Software Foundation.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */
define('WIKIRENDERER_PATH', dirname(__FILE__).'/');
define('WIKIRENDERER_VERSION', '3.0-php5');

/**
 * base class to generate output from inline wiki tag
 *
 * this objects are driven by the wiki inline parser
 * @package WikiRenderer
 * @see WikiInlineParser
 */
abstract class WikiTag {

    public $beginTag='';
    public $endTag='';
    public $isTextLineTag=false;
    public $separators=array();

    protected $attribute=array();
    protected $checkWikiWordIn=array();
    protected $contents=array('');
    protected $wikiContentArr = array('');
    protected $wikiContent='';
    protected $separatorCount=0;
    protected $separator=false;
    protected $checkWikiWordFunction=false;
    protected $config = null;

    /**
    * @param WikiRendererConfig $config
    */
    function __construct($config){
        $this->config = $config;
        $this->checkWikiWordFunction=$config->checkWikiWordFunction;
        if($config->checkWikiWordFunction === null) $this->checkWikiWordIn=array();
        if(count($this->separators)) $this->separator= $this->separators[0];
    }

    /**
    * called by the inline parser, when it found a new content
    * @param string $wikiContent   the original content in wiki syntax if $parsedContent is given, or a simple string if not
    * @param string $parsedContent the content already parsed (by an other wikitag object), when this wikitag contents other wikitags
    */
    public final function addContent($wikiContent, $parsedContent=false){
        if($parsedContent === false){
            $parsedContent =$this->_doEscape($wikiContent);
            if(count( $this->checkWikiWordIn)
                && isset($this->attribute[$this->separatorCount])
                && in_array($this->attribute[$this->separatorCount], $this->checkWikiWordIn)){
                $parsedContent=$this->_findWikiWord($parsedContent);
            }
        }
        $this->contents[$this->separatorCount] .= $parsedContent;
        $this->wikiContentArr[$this->separatorCount] .= $wikiContent;
    }

    /**
    * called by the inline parser, when it found a separator
    */
    public final function addseparator(){
        $this->wikiContent.= $this->wikiContentArr[$this->separatorCount];
        $this->separatorCount++;
        if($this->separatorCount> count($this->separators))
            $this->separator = end($this->separators);
        else
            $this->separator = $this->separators[$this->separatorCount-1];
        $this->wikiContent.= $this->separator;
        $this->contents[$this->separatorCount]='';
        $this->wikiContentArr[$this->separatorCount]='';
    }

    /**
    * return the separator used by this tag.
    *
    * The tag can support many separator
    * @return string the separator
    */
    public final function getCurrentSeparator(){
            return $this->separator;
    }

    /**
    * return the wiki content of the tag
    * @return string the content
    */
    public function getWikiContent(){
        return $this->beginTag.$this->wikiContent.$this->wikiContentArr[$this->separatorCount].$this->endTag;
    }

    /**
    * return the generated content of the tag
    * @return string the content
    */
    public function getContent(){ return $this->contents[0];}

    /**
    * return the generated content of the tag
    * @return string the content
    */
    public function getBogusContent(){
        $c=$this->beginTag;
        $m= count($this->contents)-1;
        $s= count($this->separators);
        foreach($this->contents as $k=>$v){
            $c.=$v;
            if($k< $m){
                if($k < $s)
                    $c.=$this->separators[$k];
                else
                    $c.=end($this->separators);
            }
        }

        return $c;
    }

    /**
    * escape a simple string.
    */
    protected function _doEscape($string){
        return $string;
    }

    protected function _findWikiWord($string){
        if($this->checkWikiWordFunction !== null && preg_match_all("/(?<=\b)[A-Z][a-z]+[A-Z0-9]\w*/", $string, $matches)){
            $fct=$this->checkWikiWordFunction;
            $match = array_unique($matches[0]); // il faut avoir une liste sans doublon, à cause du str_replace suivant...
            $string= str_replace($match, $fct($match), $string);
        }
        return $string;
    }

}

/**
 *
 */
class WikiTextLine extends WikiTag {
    public $isTextLineTag=true;
}


/**
 *
 */
class WikiHtmlTextLine extends WikiTag {
    public $isTextLineTag=true;
    protected $attribute=array('$$');
    protected $checkWikiWordIn=array('$$');

    protected function _doEscape($string){
        return htmlspecialchars($string);
    }
}


/**
 * a base class for wiki inline tag, to generate XHTML element.
 * @package WikiRenderer
 */
abstract class WikiTagXhtml extends WikiTag {
   protected $name;
   protected $attribute=array('$$');
   protected $checkWikiWordIn=array('$$');

   public function getContent(){
        $attr='';
        $cntattr=count($this->attribute);
        $count=($this->separatorCount >= $cntattr?$cntattr-1:$this->separatorCount);
        $content='';

        for($i=0;$i<=$count;$i++){
            if($this->attribute[$i] != '$$')
                $attr.=' '.$this->attribute[$i].'="'.htmlspecialchars($this->wikiContentArr[$i]).'"';
            else
                $content = $this->contents[$i];
        }
        return '<'.$this->name.$attr.'>'.$content.'</'.$this->name.'>';
   }

   protected function _doEscape($string){
       return htmlspecialchars($string);
   }
}


/**
 * The parser used to find all inline tag in a single line of text
 * @package WikiRenderer
 * @abstract
 */
class WikiInlineParser {

    public $error=false;

    protected $listTag=array();
    protected $simpletags=array();

    protected $resultline='';
    protected $str=array();
    protected $splitPattern='';
    protected $_separator;
    protected $config;
    /**
    * constructeur
    * @param   array    $inlinetags liste des tags permis
    * @param   string   caractère séparateur des différents composants d'un tag wiki
    */
    function __construct($config ){
        $separators = array();
        $this->escapeChar = '\\';
        $this->config = $config;

        foreach($config->inlinetags as $class){
            $t = new $class($config);
            $this->listTag[$t->beginTag]=$t;

            $this->splitPattern.=preg_quote($t->beginTag).')|(';
            if($t->beginTag!= $t->endTag)
                $this->splitPattern.=preg_quote($t->endTag).')|(';
            $separators = array_merge($separators, $t->separators);
        }
        foreach($config->simpletags as $tag=>$html){
            $this->splitPattern.=preg_quote($tag).')|(';
        }
        $separators= array_unique($separators);
        foreach($separators as $sep){
            $this->splitPattern.=preg_quote($sep).')|(';
        }

        $this->splitPattern = '/('.$this->splitPattern.preg_quote($this->escapeChar ).')/';
        $this->simpletags= $config->simpletags;
    }

    /**
    * fonction principale du parser.
    * @param   string   $line avec des eventuels tag wiki
    * @return  string   chaine $line avec les tags wiki transformé en HTML
    */
    public function parse($line){
        $this->error=false;

        $this->str = preg_split($this->splitPattern,$line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $this->end = count($this->str);
        $l = $this->config->textLineContainer;
        $firsttag = new $l($this->config);

        if($this->end > 1){
            $pos=-1;
            $this->_parse($firsttag, $pos);
            return $firsttag->getContent();
        }else{
            $firsttag->addContent($line);
            return  $firsttag->getContent();
        }
    }


    /**
    * coeur du parseur. Appelé récursivement
    * @return integer new position
    */
    protected function _parse($tag, $posstart){

      $checkNextTag=true;
      $brutContent = '';
      // on parcours la chaine,  morceau aprés morceau
      for($i=$posstart+1; $i < $this->end; $i++){
            $t=&$this->str[$i];
            $brutContent.=$t;
            // a t-on un antislash ?
            if($t === $this->escapeChar){
               if($checkNextTag){
                  $t=''; // oui -> on ignore le tag (on continue)
                  $checkNextTag=false;
               }else{
                  // si on est là, c'est que précédement c'etait un anti slash
                  $tag->addContent($this->escapeChar); //,false);
                  $checkNextTag=true;
               }

            // est-ce un séparateur ?
            }elseif($t === $tag->getCurrentSeparator()){
                $tag->addSeparator();

            }elseif($checkNextTag){
                // a-t-on une balise de fin du tag ?
                if($tag->endTag == $t && !$tag->isTextLineTag){
                    return $i;
                // a-t-on une balise de debut de tag quelconque ?
                }elseif( isset($this->listTag[$t]) ){
                    $newtag = clone $this->listTag[$t];
                    $i=$this->_parse($newtag,$i);
                    if($i !== false){
                        $tag->addContent($newtag->getWikiContent(), $newtag->getContent());
                    }else{
                        $i=$this->end;
                        $tag->addContent($newtag->getWikiContent(), $newtag->getBogusContent());
                    }

                // a-t-on un tag simple ?
                }elseif( isset($this->simpletags[$t])){
                    $tag->addContent($t, $this->simpletags[$t]);
                }else{
                    $tag->addContent($t);
                }
            }else{
                if(isset($this->listTag[$t]) || isset($this->simpletags[$t]) || $tag->endTag == $t)
                    $tag->addContent($t);
                else
                    $tag->addContent($this->escapeChar.$t);
                $checkNextTag=true;
            }
      }
      if(!$tag->isTextLineTag ){
         //--- on n'a pas trouvé le tag de fin
         // on met en erreur
         $this->error=true;
         return false;
      }else
        return $this->end;
   }

}



/**
 * classe de base pour la transformation des élements de type bloc
 * @abstract
 */
abstract class WikiRendererBloc {

    /**
    * @var string  code identifiant le type de bloc
    */
   public $type='';

   /**
    * @var string  chaine qui sera insérée à l'ouverture du bloc
    */
   protected $_openTag='';

   /**
    * @var string  chaine qui sera insérée à la fermeture du bloc
    */
   protected $_closeTag='';
   /**
    * @var boolean    indique si le bloc doit être immediatement fermé aprés détection
    * @access private
    */
   protected $_closeNow=false;

   /**
    * @var WikiRenderer      référence à la classe principale
    */
   protected $engine=null;

   /**
    * @var   array      liste des élements trouvés par l'expression régulière regexp
    */
   protected $_detectMatch=null;

   /**
    * @var string      expression régulière permettant de reconnaitre le bloc
    */
   protected $regexp='';

   /**
    * @param WikiRenderer    $wr   l'objet moteur wiki
    */
   function __construct($wr){
      $this->engine = $wr;
   }

   /**
    * renvoi une chaine correspondant à l'ouverture du bloc
    * @return string
    */
   public function open(){
      return $this->_openTag;
   }

   /**
    * renvoi une chaine correspondant à la fermeture du bloc
    * @return string
    */
   public function close(){
      return $this->_closeTag;
   }

   /**
    * indique si le bloc doit etre immédiatement fermé
    * @return string
    */
   public function closeNow(){
      return $this->_closeNow;
   }

   /**
    * test si la chaine correspond au debut ou au contenu d'un bloc
    * @param string   $string
    * @return boolean   true: appartient au bloc
    */
   public function detect($string){
      return preg_match($this->regexp, $string, $this->_detectMatch);
   }

   /**
    * renvoi la ligne, traitée pour le bloc. A surcharger éventuellement.
    * @return string
    * @abstract
    */
   public function getRenderedLine(){
      return $this->_renderInlineTag($this->_detectMatch[1]);
   }

   /**
    * traite le rendu des signes de type inline (qui se trouvent necessairement dans des blocs
    * @param   string  $string une chaine contenant une ou plusieurs balises wiki
    * @return  string  la chaine transformée en XHTML
    * @see WikiRendererInline
    */
   protected function _renderInlineTag($string){
      return $this->engine->inlineParser->parse($string);
   }
}


/**
 * classe de base pour la configuration
 */
abstract class WikiRendererConfig {

   /**
    * @var array   liste des tags inline
   */
   public $inlinetags= array();

   public $textLineContainer = 'WikiTextLine';

   /**
   * liste des balises de type bloc reconnus par WikiRenderer.
   */
   public $bloctags = array();


   public $simpletags = array();

   public $checkWikiWordFunction = null;

   /**
    * methode invoquée avant le parsing
    * Peut être utilisée selon les besoins des rêgles
    */
   public function onStart($texte){
        return $texte;
    }

   /**
    * methode invoquée aprés le parsing
    * Peut être utilisée selon les besoins des rêgles
    */
    public function onParse($finalTexte){
        return $finalTexte;
    }

}

/**
 * Moteur de rendu. Classe principale à instancier pour transformer un texte wiki en texte XHTML.
 * utilisation :
 *      $ctr = new WikiRenderer();
 *      $monTexteXHTML = $ctr->render($montexte);
 */
class WikiRenderer {

   /**
    * @var   string   contient la version HTML du texte analysé
    */
   protected $_newtext;

   /**
    * @var WikiRendererBloc element bloc ouvert en cours
    */
   protected $_currentBloc=null;

   /**
    * @var array       liste des differents types de blocs disponibles
    */
   protected $_blocList= array();

   /**
    * @var WikiInlineParser   analyseur pour les tags wiki inline
    */
   public $inlineParser=null;

   /**
    * liste des lignes où il y a une erreur wiki
    */
   public $errors=array();


   protected $config=null;
   /**
    * instancie les différents objets pour le rendu des elements inline et bloc.
    */
   function __construct( $config=null){

      if(is_string($config)){
          $f = WIKIRENDERER_PATH.'rules/'.basename($config).'.php';
          if(file_exists($f)){
              require_once($f);
              $this->config= new $config();
          }else
             throw new Exception('Wikirenderer : bad config name');
      }elseif(is_object($config)){
         $this->config=$config;
      }else{
         require_once(WIKIRENDERER_PATH . 'rules/wr3_to_xhtml.php');
         $this->config= new wr3_to_xhtml();
      }

      $this->inlineParser = new WikiInlineParser($this->config);

      foreach($this->config->bloctags as $name){
         $this->_blocList[]= new $name($this);
      }
   }

   /**
    * Methode principale qui transforme les tags wiki en tag XHTML
    * @param   string  $texte le texte à convertir
     * @return  string  le texte converti en XHTML
    */
   public function render($texte){
      $texte = $this->config->onStart($texte);

      $lignes=preg_split("/\015\012|\015|\012/",$texte); // on remplace les \r (mac), les \n (unix) et les \r\n (windows) par un autre caractère pour découper proprement

      $this->_newtext=array();
      $this->errors=array();
      $this->_currentBloc = null;

      // parcours de l'ensemble des lignes du texte
      foreach($lignes as $num=>$ligne){
         if($this->_currentBloc){
            // un bloc est déjà ouvert
            if($this->_currentBloc->detect($ligne)){
                $s =$this->_currentBloc->getRenderedLine();
                if($s !== false)
                    $this->_newtext[]=$s;
            }else{
                $this->_newtext[count($this->_newtext)-1].=$this->_currentBloc->close();
                $found=false;
                foreach($this->_blocList as $bloc){
                    if($bloc->type != $this->_currentBloc->type && $bloc->detect($ligne)){
                        $found=true;
                        // on ouvre le nouveau

                        if($bloc->closeNow()){
                            // si on doit fermer le nouveau maintenant, on le ferme
                            $this->_newtext[]=$bloc->open().$bloc->getRenderedLine().$bloc->close();
                            $this->_currentBloc = null;
                        }else{
                            $this->_currentBloc = clone $bloc; // attention, il faut une copie !
                            $this->_newtext[]=$this->_currentBloc->open().$this->_currentBloc->getRenderedLine();
                        }
                        break;
                    }
                }
                if(!$found){
                   $this->_newtext[]= $this->inlineParser->parse($ligne);
                   $this->_currentBloc = null;
                }
            }

         }else{
            $found=false;
            // pas de bloc ouvert, on test avec tout les blocs.
            foreach($this->_blocList as $bloc){
                if($bloc->detect($ligne)){
                    $found=true;
                    if($bloc->closeNow()){
                        $this->_newtext[]=$bloc->open().$bloc->getRenderedLine().$bloc->close();
                    }else{
                        $this->_currentBloc = clone $bloc; // attention, il faut une copie !
                        $this->_newtext[]=$this->_currentBloc->open().$this->_currentBloc->getRenderedLine();
                    }
                    break;
                }
            }
            if(!$found){
                $this->_newtext[]= $this->inlineParser->parse($ligne);
            }
         }
         if($this->inlineParser->error){
            $this->errors[$num+1]=$ligne;
         }
      }
      if($this->_currentBloc){
          $this->_newtext[count($this->_newtext)-1].=$this->_currentBloc->close();
      }

      return $this->config->onParse(implode("\n",$this->_newtext));
   }

    /**
     * renvoi la version de wikirenderer
     * @access public
     * @return string   version
     */
    public function getVersion(){
       return WIKIRENDERER_VERSION;
    }

    public function getConfig(){
        return $this->config;
    }

}

?>