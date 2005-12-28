<?php
    // +----------------------------------------------------------------------+
    // | PHP version 4                                                        |
    // +----------------------------------------------------------------------+
    // | Copyright (c) 2005 Michal Migurski                                   |
    // +----------------------------------------------------------------------+
    // | This source file is subject to version 3.0 of the PHP license,       |
    // | that is bundled with this package in the file LICENSE, and is        |
    // | available through the world-wide-web at the following url:           |
    // | http://www.php.net/license/3_0.txt.                                  |
    // | If you did not receive a copy of the PHP license and are unable to   |
    // | obtain it through the world-wide-web, please send a note to          |
    // | license@php.net so we can mail you a copy immediately.               |
    // +----------------------------------------------------------------------+
    // | Author: Michal Migurski, mike-json[at]teczno[dot]com                 |
    // | with contributions from:                                             |
    // |   Matt Knapp, mdknapp[at]gmail[dot]com                               |
    // |   Brett Stimmerman, brettstimmerman[at]gmail[dot]com                 |
    // +----------------------------------------------------------------------+
    //
    // $Id: JSON.php,v 1.15 2005/06/03 17:31:47 migurski Exp $
    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

    define('JSON_SLICE',   1);
    define('JSON_IN_STR',  2);
    define('JSON_IN_ARR',  4);
    define('JSON_IN_OBJ',  8);
    define('JSON_IN_CMT', 16);
    define('JSON_LOOSE_TYPE', 10);
    define('JSON_STRICT_TYPE', 11);
    
   /** JSON
    * Conversion to and from JSON format.
    * See http://json.org for details.
    *
    * note all strings should be in ASCII or UTF-8 format!
    */
    class JSON
    {
       /** function JSON
        * constructor
        *
        * @param    use     int     object behavior: when encoding or decoding,
        *                           be loose or strict about object/array usage
        *
        *                           possible values:
        *                              JSON_STRICT_TYPE - strict typing, default
        *                                                 "{...}" syntax creates objects in decode
        *                               JSON_LOOSE_TYPE - loose typing
        *                                                 "{...}" syntax creates associative arrays in decode
        */
        function JSON($use=JSON_STRICT_TYPE)
        {
            $this->use = $use;
        }

       /** function encode
        * encode an arbitrary variable into JSON format
        *
        * @param    var     mixed   any number, boolean, string, array, or object to be encoded.
        *                           see argument 1 to JSON() above for array-parsing behavior.
        *                           if var is a strng, note that encode() always expects it
        *                           to be in ASCII or UTF-8 format!
        *
        * @return   string  JSON string representation of input var
        */
        function encode($var)
        {
            switch(gettype($var)) {
                case 'boolean':
                    return $var ? 'true' : 'false';
                
                case 'NULL':
                    return 'null';
                
                case 'integer':
                    return sprintf('%d', $var);
                    
                case 'double':
                case 'float':
                    return sprintf('%f', $var);
                    
                case 'string': // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
                    $ascii = '';
                    $strlen_var = strlen($var);
    
                    for($c = 0; $c < $strlen_var; $c++) {
                        
                        $ord_var_c = ord($var{$c});
                
                        if($ord_var_c == 0x08) {
                            $ascii .= '\b';
                        
                        } elseif($ord_var_c == 0x09) {
                            $ascii .= '\t';
                        
                        } elseif($ord_var_c == 0x0A) {
                            $ascii .= '\n';
                        
                        } elseif($ord_var_c == 0x0C) {
                            $ascii .= '\f';
                        
                        } elseif($ord_var_c == 0x0D) {
                            $ascii .= '\r';
                        
                        } elseif(($ord_var_c == 0x22) || ($ord_var_c == 0x2F) || ($ord_var_c == 0x5C)) {
                            $ascii .= '\\'.$var{$c}; // double quote, slash, slosh
                        
                        } elseif(($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)) {
                            // characters U-00000000 - U-0000007F (same as ASCII)
                            $ascii .= $var{$c}; // most normal ASCII chars
                
                        } elseif(($ord_var_c & 0xE0) == 0xC0) {
                            // characters U-00000080 - U-000007FF, mask 110XXXXX, see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c, ord($var{$c+1})); $c+=1;
                            $ascii .= sprintf('\u%04s', bin2hex(mb_convert_encoding($char, 'UTF-16', 'UTF-8')));
    
                        } elseif(($ord_var_c & 0xF0) == 0xE0) {
                            // characters U-00000800 - U-0000FFFF, mask 1110XXXX, see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c, ord($var{$c+1}), ord($var{$c+2})); $c+=2;
                            $ascii .= sprintf('\u%04s', bin2hex(mb_convert_encoding($char, 'UTF-16', 'UTF-8')));
    
                        } elseif(($ord_var_c & 0xF8) == 0xF0) {
                            // characters U-00010000 - U-001FFFFF, mask 11110XXX, see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c, ord($var{$c+1}), ord($var{$c+2}), ord($var{$c+3})); $c+=3;
                            $ascii .= sprintf('\u%04s', bin2hex(mb_convert_encoding($char, 'UTF-16', 'UTF-8')));
    
                        } elseif(($ord_var_c & 0xFC) == 0xF8) {
                            // characters U-00200000 - U-03FFFFFF, mask 111110XX, see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c, ord($var{$c+1}), ord($var{$c+2}), ord($var{$c+3}), ord($var{$c+4})); $c+=4;
                            $ascii .= sprintf('\u%04s', bin2hex(mb_convert_encoding($char, 'UTF-16', 'UTF-8')));
    
                        } elseif(($ord_var_c & 0xFE) == 0xFC) {
                            // characters U-04000000 - U-7FFFFFFF, mask 1111110X, see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                            $char = pack('C*', $ord_var_c, ord($var{$c+1}), ord($var{$c+2}), ord($var{$c+3}), ord($var{$c+4}), ord($var{$c+5})); $c+=5;
                            $ascii .= sprintf('\u%04s', bin2hex(mb_convert_encoding($char, 'UTF-16', 'UTF-8')));
    
                        }
                    }
                    
                    return sprintf('"%s"', $ascii);
                    
                case 'array':
                    // As per JSON spec if any array key is not an integer we must treat the the whole array as an object. 
            		// We also try to catch a sparsely populated associative array with numeric keys here because some JS 
            		// engines will create an array with empty indexes up to max_index which can cause memory issues 
            		// and because the keys, which may be relevant, will be remapped otherwise.
            		//
                    // As per the ECMA and JSON specification an object may have any string as a property. Unfortunately due to a 
                    // hole in the ECMA specification if the key is a ECMA reserved word or starts with a digit the parameter is only
                    // accessible using ECMAScript's bracket notation.  
                    
                    // treat as a JSON object  
                    if(is_array($var) && (array_keys($var) !== range(0, sizeof($var) - 1)))
                        return sprintf('{%s}', join(',', array_map(array($this, 'name_value'), array_keys($var), array_values($var))));

                    // treat it like a regular array
                    return sprintf('[%s]', join(',', array_map(array($this, 'encode'), $var)));
                    
                case 'object':
                    $vars = get_object_vars($var);
                    return sprintf('{%s}', join(',', array_map(array($this, 'name_value'), array_keys($vars), array_values($vars))));                    

                default:
                    return '';
            }
        }
        
       /** function enc
        * alias for encode()
        */
        function enc($var)
        {
            return $this->encode($var);
        }
        
       /** function name_value
        * array-walking function for use in generating JSON-formatted name-value pairs
        *
        * @param    name    string  name of key to use
        * @param    value   mixed   reference to an array element to be encoded
        *
        * @return   string  JSON-formatted name-value pair, like '"name":value'
        */
        function name_value($name, $value)
        {
            return (sprintf("%s:%s", $this->encode(strval($name)), $this->encode($value)));
        }        

       /** function reduce_string
        * reduce a string by removing leading and trailing comments and whitespace
        *
        * @param    str     string      string value to strip of comments and whitespace
        *
        * @return   string  string value stripped of comments and whitespace
        */
        function reduce_string($str)
        {
            $str = preg_replace('#^\s*//(.+)$#m', '', $str); // eliminate single line comments in '// ...' form
            $str = preg_replace('#^\s*/\*(.+)\*/#Us', '', $str); // eliminate multi-line comments in '/* ... */' form, at start of string
            $str = preg_replace('#/\*(.+)\*/\s*$#Us', '', $str); // eliminate multi-line comments in '/* ... */' form, at end of string
            $str = trim($str); // eliminate extraneous space
            
            return $str;
        }

       /** function decode
        * decode a JSON string into appropriate variable
        *
        * @param    str     string  JSON-formatted string
        *
        * @return   mixed   number, boolean, string, array, or object
        *                   corresponding to given JSON input string.
        *                   see argument 1 to JSON() above for object-output behavior.
        *                   note that decode() always returns strings
        *                   in ASCII or UTF-8 format!
        */
        function decode($str)
        {
            $str = $this->reduce_string($str);
        
            switch(strtolower($str)) {
                case 'true':
                    return true;
    
                case 'false':
                    return false;
                
                case 'null':
                    return null;
                
                default:
                    if(is_numeric($str)) { // Lookie-loo, it's a number
                        // return (float)$str; // This would work on its own, but I'm trying to be good about returning integers where appropriate
                        return ((float)$str == (integer)$str)
                            ? (integer)$str
                            : (float)$str;
                        
                    } elseif(preg_match('/^".+"$/s', $str) || preg_match('/^\'.+\'$/s', $str)) { // STRINGS RETURNED IN UTF-8 FORMAT
                        $delim = substr($str, 0, 1);
                        $chrs = substr($str, 1, -1);
                        $utf8 = '';
                        $strlen_chrs = strlen($chrs);
                        
                        for($c = 0; $c < $strlen_chrs; $c++) {
                        
                            $substr_chrs_c_2 = substr($chrs, $c, 2);

                            if($substr_chrs_c_2 == '\b') {
                                $utf8 .= chr(0x08); $c+=1;
    
                            } elseif($substr_chrs_c_2 == '\t') {
                                $utf8 .= chr(0x09); $c+=1;
    
                            } elseif($substr_chrs_c_2 == '\n') {
                                $utf8 .= chr(0x0A); $c+=1;
    
                            } elseif($substr_chrs_c_2 == '\f') {
                                $utf8 .= chr(0x0C); $c+=1;
    
                            } elseif($substr_chrs_c_2 == '\r') {
                                $utf8 .= chr(0x0D); $c+=1;
    
                            } elseif(($delim == '"') && (($substr_chrs_c_2 == '\\"') || ($substr_chrs_c_2 == '\\\\') || ($substr_chrs_c_2 == '\\/'))) {
                                $utf8 .= $chrs{++$c};
    
                            } elseif(($delim == "'") && (($substr_chrs_c_2 == '\\\'') || ($substr_chrs_c_2 == '\\\\') || ($substr_chrs_c_2 == '\\/'))) {
                                $utf8 .= $chrs{++$c};
    
                            } elseif(preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6))) { // single, escaped unicode character
                                $utf16 = chr(hexdec(substr($chrs, ($c+2), 2))) . chr(hexdec(substr($chrs, ($c+4), 2)));
                                $utf8 .= mb_convert_encoding($utf16, 'UTF-8', 'UTF-16');
                                $c+=5;
    
                            } elseif((ord($chrs{$c}) >= 0x20) && (ord($chrs{$c}) <= 0x7F)) {
                                $utf8 .= $chrs{$c};
    
                            }
                        
                        }
                        
                        return $utf8;
                    
                    } elseif(preg_match('/^\[.*\]$/s', $str) || preg_match('/^{.*}$/s', $str)) { // array, or object notation
    
                        if($str{0} == '[') {
                            $stk = array(JSON_IN_ARR);
                            $arr = array();
                        } else {
                            if($this->use == JSON_LOOSE_TYPE) {
                                $stk = array(JSON_IN_OBJ);
                                $obj = array();
                            } else {
                                $stk = array(JSON_IN_OBJ);
                                $obj = new ObjectFromJSON();
                            }
                        }
                        
                        array_push($stk, array('what' => JSON_SLICE, 'where' => 0, 'delim' => false));
                        $chrs = substr($str, 1, -1);
                        $chrs = $this->reduce_string($chrs);
                        
                        if($chrs == '') {
                            if(reset($stk) == JSON_IN_ARR) {
                                return $arr;

                            } else {
                                return $obj;

                            }
                        }

                        //print("\nparsing {$chrs}\n");
                        
                        $strlen_chrs = strlen($chrs);
                        
                        for($c = 0; $c <= $strlen_chrs; $c++) {
                        
                            $top = end($stk);
                            $substr_chrs_c_2 = substr($chrs, $c, 2);
                        
                            if(($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == JSON_SLICE))) { // found a comma that is not inside a string, array, etc., OR we've reached the end of the character list
                                $slice = substr($chrs, $top['where'], ($c - $top['where']));
                                array_push($stk, array('what' => JSON_SLICE, 'where' => ($c + 1), 'delim' => false));
                                //print("Found split at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
    
                                if(reset($stk) == JSON_IN_ARR) { // we are in an array, so just push an element onto the stack
                                    array_push($arr, $this->decode($slice));
    
                                } elseif(reset($stk) == JSON_IN_OBJ) { // we are in an object, so figure out the property name and set an element in an associative array, for now
                                    if(preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) { // "name":value pair
                                        $key = $this->decode($parts[1]);
                                        $val = $this->decode($parts[2]);

                                        if($this->use == JSON_LOOSE_TYPE) {
                                            $obj[$key] = $val;
                                        } else {
                                            $obj->$key = $val;
                                        }
                                    } elseif(preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) { // name:value pair, where name is unquoted
                                        $key = $parts[1];
                                        $val = $this->decode($parts[2]);

                                        if($this->use == JSON_LOOSE_TYPE) {
                                            $obj[$key] = $val;
                                        } else {
                                            $obj->$key = $val;
                                        }
                                    }
    
                                }
    
                            } elseif((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != JSON_IN_STR)) { // found a quote, and we are not inside a string
                                array_push($stk, array('what' => JSON_IN_STR, 'where' => $c, 'delim' => $chrs{$c}));
                                //print("Found start of string at {$c}\n");
    
                            } elseif(($chrs{$c} == $top['delim']) && ($top['what'] == JSON_IN_STR) && (($chrs{$c - 1} != "\\") || ($chrs{$c - 1} == "\\" && $chrs{$c - 2} == "\\"))) { // found a quote, we're in a string, and it's not escaped
                                array_pop($stk);
                                //print("Found end of string at {$c}: ".substr($chrs, $top['where'], (1 + 1 + $c - $top['where']))."\n");
    
                            } elseif(($chrs{$c} == '[') && in_array($top['what'], array(JSON_SLICE, JSON_IN_ARR, JSON_IN_OBJ))) { // found a left-bracket, and we are in an array, object, or slice
                                array_push($stk, array('what' => JSON_IN_ARR, 'where' => $c, 'delim' => false));
                                //print("Found start of array at {$c}\n");
    
                            } elseif(($chrs{$c} == ']') && ($top['what'] == JSON_IN_ARR)) { // found a right-bracket, and we're in an array
                                array_pop($stk);
                                //print("Found end of array at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
    
                            } elseif(($chrs{$c} == '{') && in_array($top['what'], array(JSON_SLICE, JSON_IN_ARR, JSON_IN_OBJ))) { // found a left-brace, and we are in an array, object, or slice
                                array_push($stk, array('what' => JSON_IN_OBJ, 'where' => $c, 'delim' => false));
                                //print("Found start of object at {$c}\n");
    
                            } elseif(($chrs{$c} == '}') && ($top['what'] == JSON_IN_OBJ)) { // found a right-brace, and we're in an object
                                array_pop($stk);
                                //print("Found end of object at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
    
                            } elseif(($substr_chrs_c_2 == '/*') && in_array($top['what'], array(JSON_SLICE, JSON_IN_ARR, JSON_IN_OBJ))) { // found a comment start, and we are in an array, object, or slice
                                array_push($stk, array('what' => JSON_IN_CMT, 'where' => $c, 'delim' => false));
                                $c++;
                                //print("Found start of comment at {$c}\n");
    
                            } elseif(($substr_chrs_c_2 == '*/') && ($top['what'] == JSON_IN_CMT)) { // found a comment end, and we're in one now
                                array_pop($stk);
                                $c++;
                                
                                for($i = $top['where']; $i <= $c; $i++)
                                    $chrs = substr_replace($chrs, ' ', $i, 1);
                                
                                //print("Found end of comment at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");
    
                            }
                        
                        }
                        
                        if(reset($stk) == JSON_IN_ARR) {
                            return $arr;
    
                        } elseif(reset($stk) == JSON_IN_OBJ) {
                            return $obj;
    
                        }
                    
                    }
            }
        }
        
       /** function dec
        * alias for decode()
        */
        function dec($var)
        {
            return $this->decode($var);
        }
        
    }

   /** ObjectFromJSON
    * Generic object wrapper, used in object returns from decode()
    */
    class ObjectFromJSON { function ObjectFromJSON() {} }
    
?>