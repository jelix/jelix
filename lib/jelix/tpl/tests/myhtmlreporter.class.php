<?php
/**
* @package     jelix
* @subpackage  junittests
* @author     Laurent Jouanneau
* @contributor
* @copyright  2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class myHtmlReporter extends HtmlReporter {

   function _getCss() {
      return ".fail { color: red; } pre { background-color: lightgray; }
.diff { background: white; border: 1px solid black; }
.diff .block { background: #ccc; padding-left: 1em; }
.diff .context { background: white; border: none; }
.diff .block tt { font-weight: normal;  font-family: monospace;  color: black;
        margin-left: 0;  border: none; }
.diff del, .diff ins {  font-weight: bold; text-decoration: none; }
.diff .original, .diff .deleted,
.diff .final, .diff .added {  background: white; }
.diff .original, .diff .deleted {  background: #fcc;  border: none; }
.diff .final, .diff .added {  background: #cfc; border: none; }
.diff del { background: #f99; }
.diff ins { background: #9f9; }
   ";
   }


   function paintDiff($stringA, $stringB){
        $diff = new Diff(explode("\n",$stringA),explode("\n",$stringB));
        if($diff->isEmpty()) {
            echo '<p>Erreur diff : bizarre, aucune différence d\'aprés la difflib...</p>';
        }else{
            $fmt = new HtmlUnifiedDiffFormatter();
            echo $fmt->format($diff);
        }
   }

}

