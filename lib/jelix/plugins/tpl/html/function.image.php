<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @author     Lepeltier kévin
* @copyright  2008 Lepeltier kévin
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Ajoute un lien vers l'image,
 * l'image est redimensionné, et mis en cache
 *
 * class :string
 * id :string
 * alt :string
 * width :uint
 * height :uint
 * zoom 1-100
 * omo :boolean
 * alignh [left|center|right|:int]
 * alignv [top|center|bottom|:int]
 * ext [png|jpg|gif]
 * quality 0-100 if ext = jpg
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
 */

/**
 * image plugin :  write the url corresponding to the image
 *
 * @param jTpl $tpl template engine
 * @param string $src the url of image (data/fichiers/):string.[gif|jpeg|jpg|jpe|xpm|xbm|wbmp|png]
 * @param array $params parameters for the url
 */
function jtpl_function_html_image($tpl, $src, $params=array()) {

    // extension choisit
    if( empty($params['ext']) ) {
        $path_parts = pathinfo($src);
        $ext = $path_parts['extension'];
    } else $ext = $params['ext'];

    // nom unique du fichier en cache
    $chaine = $src;
    foreach($params as $key => $value)
        if( !in_array($key, array('alt', 'class', 'id')))
            $chaine .= $value;
    $cachename = md5($chaine).'.'.$ext;

    // path
    $cache_path = JELIX_APP_WWW_PATH.'cache/images/'.$cachename;
    $origine_path = JELIX_APP_WWW_PATH.'data/fichiers/'.$src;

    global $gJConfig;
    $www = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST'].$gJConfig->urlengine['basePath'];
    $cache_www = $www.'cache/images/'.$cachename;
    $origine_www = $www.'data/fichiers/'.$src;

    // mettre en cache et faire les transformations si nésséssaire.
    if( is_file($origine_path) && !is_file($cache_path) ) {
        $att = array('width'=>'', 'height'=>'', 'omo'=>'', 'zoom'=>'', 'alignh'=>'', 'alignv'=>'', 'ext'=>'', 'quality'=>'');
        if( count(array_intersect_key($params, $att)) )
            jtpl_function_html_image_inCache($src, $cachename, $params);
    }

    // les attributs
    $att = array('alt'=>'', 'id'=>'', 'class'=>'');
    $att = array_intersect_key($params, $att);

    // si l'image ne subit pas de transformation
    if( !is_file($cache_path) ) {
        $att['src'] = $origine_www;
        $att['style'] = '';
        if( !empty($params['width']) ) 	$att['style'] .= 'width: '.$params['width'].'px;';
        if( !empty($params['height']) ) $att['style'] .= 'height: '.$params['height'].'px;';
    } else
        $att['src'] = $cache_www;

    // la balise image
    echo '<img';
    foreach( $att as $key => $val )
        if( !empty($val) )
            echo ' ',$key,'="',$val,'"';
    echo '/>';

}

function jtpl_function_html_image_inCache($src, $cachename, $array) {

    $mimes = array('gif'=>'image/gif', 'png'=>'image/png', 
                   'jpeg'=>'image/jpeg', 'jpg'=>'image/jpeg', 'jpe'=>'image/jpeg', 
                   'xpm'=>'image/x-xpixmap', 'xbm'=>'image/x-xbitmap', 'wbmp'=>'image/vnd.wap.wbmp');

    global $gJConfig;
    $origine_www = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST'].$gJConfig->urlengine['basePath'].'data/fichiers/'.$src;

    $path_parts = pathinfo($origine_www);
    $ext = $mimes[$path_parts['extension']];
    $quality = (!empty($array['quality']))?  $array['quality'] : 100;

    // création de l'image
    switch ( $ext ) {
        case 'image/gif'             : $image = imagecreatefromgif($origine_www); break;
        case 'image/jpeg'            : $image = imagecreatefromjpeg($origine_www); break;
        case 'image/png'             : $image = imagecreatefrompng($origine_www); break;
        case 'image/vnd.wap.wbmp'    : $image = imagecreatefromwbmp($origine_www); break;
        case 'image/image/x-xbitmap' : $image = imagecreatefromxbm($origine_www); break;
        case 'image/x-xpixmap'       : $image = imagecreatefromxpm($origine_www); break;
        default                      : return ;
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
            if($array['alignh'] == 'left')			$posx = 0;
            else if($array['alignh'] == 'right')	$posx = -($resamplewidth - imagesx($ancienimage));
            else if($array['alignh'] != 'center')	$posx = -$array['alignh'];
        }

        if(!empty($array['alignv'])) {
           if($array['alignv'] == 'top')			$posy = 0;
            else if($array['alignv'] == 'bottom')	$posy = -($resampleheight - imagesy($ancienimage));
            else if($array['alignv'] != 'center')	$posy = -$array['alignv'];
        }

        $image = imagecreatetruecolor($finalwidth, $finalheight);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $tp = imagecolorallocate($image,0,0,0);
        imagefill($image,0,0,$tp);
        imagecolortransparent($image,$tp);
        imagecopyresampled($image, $ancienimage, 0, 0, $posx, $posy, imagesx($image), imagesy($image), $resamplewidth, $resampleheight);
    }

    $ext = empty($array['ext'])?$ext:$mimes[$array['ext']];
    $cache_path = JELIX_APP_WWW_PATH.'cache/images/';

    //enregistrer
    switch ( $ext ) {
        case 'image/gif'  : imagegif($image, $cache_path.$cachename); break;
        case 'image/jpeg' : imagejpeg($image, $cache_path.$cachename, $quality); break;
        default           : imagepng($image, $cache_path.$cachename);
    }

    // destruction
    @imagedestroy($image);
}

?>