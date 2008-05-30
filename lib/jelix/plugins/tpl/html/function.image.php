<?php
/**
 * @package    jelix
 * @subpackage jtpl_plugin
 * @author     Lepeltier kévin
 * @copyright  2007-2008 Lepeltier kévin
 * @link       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * image plugin :  write the url corresponding to the image
 * 
 * Add a link to the image,
 * The image is resized, and cached
 *
 * class :string
 * id :string
 * alt :string
 * width :uint
 * height :uint
 * maxwidth :uint only with maxheight
 * maxheight :uint only with maxwidth
 * zoom 1-100
 * omo :boolean
 * alignh [left|center|right|:int]
 * alignv [top|center|bottom|:int]
 * ext [png|jpg|gif]
 * quality 0-100 if ext = jpg
 * shadow :boolean
 * soffset :uint
 * sangle :uint
 * sblur :uint
 * sopacity :uint
 * scolor #000000 :string
 * background #000000 :string
 *
 * gif   -> image/gif
 * jpeg  -> image/jpeg
 * jpg   -> image/jpeg
 * jpe   -> image/jpeg
 * xpm   -> image/x-xpixmap
 * xbm   -> image/x-xbitmap
 * wbmp  -> image/vnd.wap.wbmp
 * png   -> image/png
 * other -> image/png
 *
 * @param jTpl $tpl template engine
 * @param string $src the url of image (myapp/www/):string.[gif|jpeg|jpg|jpe|xpm|xbm|wbmp|png]
 * @param array $params parameters for the url
 */
function jtpl_function_html_image($tpl, $src, $params=array()) {
    
    // Extension
    if( empty($params['ext']) ) {
        $path_parts = pathinfo($src);
        $ext = strtolower($path_parts['extension']);
    } else $ext = strtolower($params['ext']);
    
    // White background for IE
    if (   empty($params['background'])
        && strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE') !== false
        && strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 7') === false) {
        $params['background'] = '#ffffff';
    }
    
    // Name of the file cache
    $chaine = $src;
    foreach($params as $key => $value)
        if( !in_array($key, array('alt', 'class', 'id', 'style', 'longdesc', 'name', 'ismap', 'usemap', 'title', 'dir', 'lang', 'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove', 'onmouseout', 'onkeypress', 'onkeydown', 'onkeyup')))
            $chaine .= $key.$value;
    $cachename = md5($chaine).'.'.$ext;
    
    // Path
    $cache_path = JELIX_APP_WWW_PATH.'cache/images/'.$cachename;
    $origine_path = JELIX_APP_WWW_PATH.$src;
    
    global $gJConfig;
    $www = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST'].$gJConfig->urlengine['basePath'];
    $cache_www = $www.'cache/images/'.$cachename;
    $origine_www = $www.$src;
    
    // Cache and make changes if necessary
    if( is_file($origine_path) && !is_file($cache_path) ) {
        $att = array('width'=>'', 'height'=>'', 'maxwidth'=>'', 'maxheight'=>'', 'zoom'=>'', 'alignh'=>'', 'alignv'=>'', 'ext'=>'', 'quality'=>'', 'shadow'=>'');
        if( count(array_intersect_key($params, $att)) )
            jtpl_function_html_image_inCache($src, $cachename, $params);
    }
    
    // Attributes
    $att = array('alt'=>'', 'id'=>'', 'class'=>'', 'style'=>'', 'longdesc'=>'', 'name'=>'', 'ismap'=>'', 'usemap'=>'', 'title'=>'', 'dir'=>'', 'lang'=>'', 'onclick'=>'', 'ondblclick'=>'', 'onmousedown'=>'', 'onmouseup'=>'', 'onmouseover'=>'', 'onmousemove'=>'', 'onmouseout'=>'', 'onkeypress'=>'', 'onkeydown'=>'', 'onkeyup'=>'');
    $att = array_intersect_key($params, $att);
    
    // If the image does not undergo transformation
    if( !is_file($cache_path) ) {
        $att['src'] = $origine_www;
        $att['style'] = empty($att['style'])?'':$att['style'];
        if( !empty($params['width']) )             $att['style'] .= 'width: '.$params['width'].'px;';
        else if( !empty($params['maxwidth']) )     $att['style'] .= 'width: '.$params['maxwidth'].'px;';
        if( !empty($params['height']) )            $att['style'] .= 'height: '.$params['height'].'px;';
        else if( !empty($params['maxheight']) )    $att['style'] .= 'height: '.$params['maxheight'].'px;';
    } else
        $att['src'] = $cache_www;
    
    // Tag image
    echo '<img';
    foreach( $att as $key => $val )
        if( !empty($val) )
            echo ' '.$key.'="'.$val.'"';
    echo '/>';
    
    }

function jtpl_function_html_image_inCache($src, $cachename, $array) {
    
    $mimes = array('gif'=>'image/gif', 'png'=>'image/png', 
                   'jpeg'=>'image/jpeg', 'jpg'=>'image/jpeg', 'jpe'=>'image/jpeg', 
                   'xpm'=>'image/x-xpixmap', 'xbm'=>'image/x-xbitmap', 'wbmp'=>'image/vnd.wap.wbmp');
    
    global $gJConfig;
    $origine_www = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST'].$gJConfig->urlengine['basePath'].$src;
    
    $path_parts = pathinfo($origine_www);
    $ext = $mimes[strtolower($path_parts['extension'])];
    $quality = (!empty($array['quality']))?  $array['quality'] : 100;
    
    // Creating an image
    switch ( $ext ) {
        case 'image/gif'             : $image = imagecreatefromgif($origine_www); break;
        case 'image/jpeg'            : $image = imagecreatefromjpeg($origine_www); break;
        case 'image/png'             : $image = imagecreatefrompng($origine_www); break;
        case 'image/vnd.wap.wbmp'    : $image = imagecreatefromwbmp($origine_www); break;
        case 'image/image/x-xbitmap' : $image = imagecreatefromxbm($origine_www); break;
        case 'image/x-xpixmap'       : $image = imagecreatefromxpm($origine_www); break;
        default                      : return ;
    }
    
    if(!empty($array['maxwidth']) && !empty($array['maxheight'])) {
        
        $rapy = imagesy($image)/$array['maxwidth'];
        $rapx = imagesx($image)/$array['maxheight'];
        
        if( $rapy > $rapx ) {
            $array['height'] = $array['maxheight'];
            $array['width'] = imagesx($image)/$rapy;
        } else {
            $array['width'] = $array['maxwidth'];
            $array['height'] = imagesy($image)/$rapx;
        }
    }
    
    if (!empty($array['width']) || !empty($array['height'])) {
    
        $ancienimage = $image;
        $resampleheight = imagesy($ancienimage);
        $resamplewidth = imagesx($ancienimage);
        $posx = 0;
        $posy = 0;
        
        if(empty($array['width'])) {
            $finalheight = $array['height'];
            $finalwidth = $finalheight*imagesx($ancienimage)/imagesy($ancienimage);
        } else if (empty($array['height'])) {
            $finalwidth = $array['width'];
            $finalheight = $finalwidth*imagesy($ancienimage)/imagesx($ancienimage);
        } else {
            $finalwidth = $array['width'];
            $finalheight = $array['height'];
            if(!empty($array['omo']) && $array['omo'] == 'true') {
                if($array['width'] >= $array['height']) {
                    $resampleheight = ( $resamplewidth*$array['height'] )/$array['width'];
                } else {
                    $resamplewidth = ( $resampleheight*$array['width'] )/$array['height'];
                }
            }
        }
        
        if(!empty($array['zoom'])) {
            $resampleheight /= 100/$array['zoom'];
            $resamplewidth /= 100/$array['zoom'];
        }
        
        $posx = imagesx($ancienimage)/2 -$resamplewidth/2;
        $posy = imagesy($ancienimage)/2 -$resampleheight/2;
        
        if(!empty($array['alignh'])) {
            if($array['alignh'] == 'left')            $posx = 0;
            else if($array['alignh'] == 'right')    $posx = -($resamplewidth - imagesx($ancienimage));
            else if($array['alignh'] != 'center')    $posx = -$array['alignh'];
        }
        
        if(!empty($array['alignv'])) {
            if($array['alignv'] == 'top')            $posy = 0;
            else if($array['alignv'] == 'bottom')    $posy = -($resampleheight - imagesy($ancienimage));
            else if($array['alignv'] != 'center')    $posy = -$array['alignv'];
        }
        
        $image = imagecreatetruecolor($finalwidth, $finalheight);
        imagesavealpha($image, true);
        $tp = imagecolorallocatealpha($image,0,0,0,127);
        imagefill($image,0,0,$tp);
        
        imagecopyresampled($image, $ancienimage, 0, 0, $posx, $posy, imagesx($image), imagesy($image), $resamplewidth, $resampleheight);
    }
    
    // The shadow cast adds to the dimension of the image chooses
    if( !empty($array['shadow']) )
       $image = jtpl_function_html_image_ombre ($image, $array);
    
    // Background
    if( !empty($array['background']) ) {
        $array['background'] = str_replace('#', '', $array['background']);
        $rgb = array(0,0,0);
        for ($x=0;$x<3;$x++) $rgb[$x] = hexdec(substr($array['background'],(2*$x),2));
        $fond = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill( $fond, 0, 0, imagecolorallocate( $fond, $rgb[0], $rgb[1], $rgb[2]) );
        imagecopy( $fond, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        $image = $fond;
    }
    
    
    $ext = empty($array['ext'])?$ext:$mimes[$array['ext']];
    $cache_path = JELIX_APP_WWW_PATH.'cache/images/';
    jFile::createDir($cache_path);
    
    
    // Register
    switch ( $ext ) {
        case 'image/gif'  : imagegif($image, $cache_path.$cachename); break;
        case 'image/jpeg' : imagejpeg($image, $cache_path.$cachename, $quality); break;
        default           : imagepng($image, $cache_path.$cachename);
    }
    
    // Destruction
    @imagedestroy($image);
}

function jtpl_function_html_image_ombre ( $image, $array) {
    
    // Default
    $leng = isset($array['soffset'])?$array['soffset']:10;
    $angle = isset($array['sangle'])?$array['sangle']:135;
    $flou = isset($array['sblur'])?$array['sblur']:10;
    $opac = isset($array['sopacity'])?$array['sopacity']:20;
    $color = isset($array['scolor'])?$array['scolor']:'#000000';
    
    // Color of the shadow
    $color = str_replace('#', '', $color);
    $rgb = array(0,0,0);
    if (strlen($color) == 6)
        for ($x=0;$x<3;$x++)
            $rgb[$x] = hexdec(substr($color,(2*$x),2));
    else if (strlen($color) == 3)
        for ($x=0;$x<3;$x++)
            $rgb[$x] = hexdec(substr($color,(2*$x),1));
    
    // Gaussian blur parameter
    $coeffs = array (array ( 1),
                     array ( 1, 1), 
                     array ( 1, 2, 1),
                     array ( 1, 3, 3, 1),
                     array ( 1, 4, 6, 4, 1),
                     array ( 1, 5, 10, 10, 5, 1),
                     array ( 1, 6, 15, 20, 15, 6, 1),
                     array ( 1, 7, 21, 35, 35, 21, 7, 1),
                     array ( 1, 8, 28, 56, 70, 56, 28, 8, 1),
                     array ( 1, 9, 36, 84, 126, 126, 84, 36, 9, 1),
                     array ( 1, 10, 45, 120, 210, 252, 210, 120, 45, 10, 1),
                     array ( 1, 11, 55, 165, 330, 462, 462, 330, 165, 55, 11, 1));
    $sum = pow (2, $flou);
    $demi = $flou/2;
    
    
    // Horizontal blur and blur margin
    $temp1 = imagecreatetruecolor(imagesx($image)+$flou, imagesy($image)+$flou);
    imagesavealpha($temp1, true);
    $tp = imagecolorallocatealpha($temp1,0,0,0,127);
    imagefill($temp1,0,0,$tp);
    
    for ( $i=0 ; $i < imagesx($temp1) ; $i++ )
    for ( $j=0 ; $j < imagesy($temp1) ; $j++ ) {
        $ig = $i-$demi; $jg = $j-$demi; $suma = 0;
        for ( $k=0 ; $k <= $flou ; $k++ ) {
            $ik = $ig-$demi+$k;
            if( $jg<0 || $jg>imagesy($temp1)-$flou-1 ) $alpha = 127;
            else if( $ik<0 || $ik>imagesx($temp1)-$flou-1 ) $alpha = 127;
            else $alpha = (imagecolorat($image, $ik, $jg) & 0x7F000000) >> 24;
            $suma += $alpha*$coeffs[$flou][$k];
        }
        $c = imagecolorallocatealpha($temp1, 0, 0, 0, $suma/$sum );
        imagesetpixel($temp1,$i,$j,$c);
    }
    
    // Vertical blur, a shift of the angle, opacity and color
    
    $x = cos(deg2rad($angle))*$leng;
    $y = sin(deg2rad($angle))*$leng;
    
    $temp2 = imagecreatetruecolor(imagesx($temp1)+abs($x), imagesy($temp1)+abs($y));
    imagesavealpha($temp2, true);
    $tp = imagecolorallocatealpha($temp2,0,0,0,127);
    imagefill($temp2,0,0,$tp);
    
    $x1 = $x<0?0:$x;
    $y1 = $y<0?0:$y;
    
    for ( $i=0 ; $i < imagesx($temp1) ; $i++ )
    for ( $j=0 ; $j < imagesy($temp1) ; $j++ ) {
        $suma = 0;
        for ( $k=0 ; $k <= $flou ; $k++ ) {
            $jk = $j-$demi+$k;
            if( $jk<0 || $jk>imagesy($temp1)-1 ) $alpha = 127;
            else $alpha = (imagecolorat($temp1, $i, $jk) & 0x7F000000) >> 24;
            $suma += $alpha*$coeffs[$flou][$k];
        }
        $alpha = 127-((127-($suma/$sum))/(100/$opac));
        $c = imagecolorallocatealpha($temp2, $rgb[0], $rgb[1], $rgb[2], $alpha < 0 ? 0 : $alpha > 127 ? 127 : $alpha );
        imagesetpixel($temp2,$i+$x1,$j+$y1,$c);
    }
    imagedestroy($temp1);
    
    // Merge of the image and are shade
    $x = $x>0?0:$x;
    $y = $y>0?0:$y;
    imagecopy( $temp2, $image, $demi-$x, $demi-$y, 0, 0, imagesx($image), imagesy($image));
    
    return $temp2;

}
?>
