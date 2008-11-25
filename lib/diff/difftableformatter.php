<?php
/**
 *  Wikipedia Table style diff formatter.
 * @author Foxmask (for Jelix)
 * @copyright 2008 Foxmask
 */
require_once(dirname(__FILE__).'/difflib.php');

class HtmlTableDiffFormatter extends DiffFormatter
{
  function __construct($version1,$version2,$type) {
    $this->type     = $type;
    $this->version1 = $version1;
    $this->version2 = $version2;
    $this->leading_context_lines = 2;
    $this->trailing_context_lines = 2;
  }

  function _pre($text){
    $text = htmlspecialchars($text);
    $text = str_replace('  ',' &nbsp;',$text);
    return $text;
  }

  function _block_header( $xbeg, $xlen, $ybeg, $ylen ) {    
    global $lang;
    if ($this->type == 'sidebyside') {
        $r = '<table class="sidebyside">'."\n".
        '<colgroup class="l"><col class="lineno" /><col class="content" /></colgroup>'."\n".
        '<colgroup class="r"><col class="lineno" /><col class="content" /></colgroup>'."\n";
        if($this->version1 != '' || $this->version2 != '')        
            $r .= '<thead><tr><th colspan="2"> Version '.$this->version1.":</th>\n" .
            '<th colspan="2"> Version '.$this->version2.":</th></tr></thead>\n";
    }
    else {
        $r = '<table class="inline">'."\n".
        '<colgroup><col class="lineno" /><col class="lineno" /><col class="content" /></colgroup>'."\n";
        if($this->version1 != '' || $this->version2 != '')        
            $r .= '<thead><tr><th>'.$this->version1.":</th>\n" .
            '<th>'.$this->version2.":</th><th>&nbsp;</th></tr></thead>\n";    
    }
    return $r;
  }

  function _start_block( $header ) {
    print( $header );
  }

  function _end_block() {
    echo '</table>';
  }

  function _lines( $lines, $prefix=' ', $color="white" ) {
  }

  function addedLine( $line,$class ) {
    $line = str_replace('  ','&nbsp; ',$line);
    if ($this->type == 'sidebyside') 
        return '<td></td><td class="'.$class.'">' .$line.'</td>';
    else 
        return '<th>&nbsp;</th><th>&nbsp;</th><td class="'.$class.'">' .$line.'</td>';
  }

  function deletedLine( $line, $class ) {
    $line = str_replace('  ','&nbsp; ',$line);
    if ($this->type == 'sidebyside') 
        return '<td>-</td><td class="'.$class.'">' .$line.'</td>';
    else
        return '<th>&nbsp;</th><th>&nbsp;</th><td class="'.$class.'">' .$line.'</td>';   
  }

  function emptyLine() {
    //$line = str_replace('  ','&nbsp; ',$line);
    if ($this->type == 'sidebyside') 
        return '<td colspan="2">&nbsp;</td>';
    else 
        return '<th>&nbsp;</th><th>&nbsp;</th><td>&nbsp;</td>';
  }

  function contextLine( $line , $class) {
    $line = str_replace('  ','&nbsp; ',$line);
    if ($this->type == 'sidebyside') 
        return '<td> </td><td class="'.$class.'">'.$line.'</td>';
    else 
        return '<th>&nbsp;</th><th>&nbsp;</th><td class="'.$class.'">'.$line.'</td>';
  }

  function _added($lines) {
    foreach ($lines as $line) {
      if ($this->type == 'sidebyside') 
        print( '<tbody class="add"><tr>' . $this->emptyLine() .
            $this->addedLine( $line,'r' ) . "</tr></tbody>\n" );
      else 
        print( '<tbody class="add"><tr>'. $this->addedLine( $line,'r' ) . "</tr></tbody>\n" );      
    }
  }

  function _deleted($lines) {
    foreach ($lines as $line) {
        if ($this->type == 'sidebyside')  
            print( '<tbody class="rem"><tr>' . $this->deletedLine( $line,'l' ) . $this->emptyLine() . "</tr></tbody>\n" );
        else 
            print( '<tbody class="rem"><tr>'. $this->deletedLine( $line,'l' ) ."</tr></tbody>\n" );
        
    }
  }

  function _context( $lines ) {
    foreach ($lines as $line) {
        if ($this->type == 'sidebyside') 
            print( '<tbody><tr>' . $this->contextLine( $line, "l" ) .
                    $this->contextLine( $line,"r" ) . "</tr></tbody>\n" );
        else                    
            print( '<tbody><tr>' . $this->contextLine( $line, "l" ) ."</tr></tbody>\n" );

    }
  }

  function _changed( $orig, $closing ) {
    $diff = new WordLevelDiff( $orig, $closing );
    $del = $diff->orig();
    $add = $diff->_final();

    while ( $line = array_shift( $del ) ) {
        $aline = array_shift( $add );
        if ($this->type == 'sidebyside') 
            print( '<tbody class="mod"><tr>' . $this->deletedLine( $line ,'l') . $this->addedLine( $aline ,'r') . "</tr></tbody>\n" );
        else
            print( '<tbody class="mod"><tr>' . $this->deletedLine( $line ,'l') . "</tr></tbody>\n" );
    }
    $this->_added( $add ); // If any leftovers
  }
}