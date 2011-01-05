<?php
/**
 * @package    jelix
 * @subpackage core
 * @author     Brice Tence
 * @copyright  2010 Brice Tence
 *   Idea of this class was picked from the Minify project ( Minify 2.1.3, http://code.google.com/p/minify )
 * @link       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

define('MINIFY_MIN_DIR', LIB_PATH.'minify/min/');

// setup include path
set_include_path(MINIFY_MIN_DIR.'/lib' . PATH_SEPARATOR . get_include_path());

require_once "Minify/Controller/MinApp.php";
require_once 'Minify/Source.php';
require_once 'Minify.php';

/**
 * This object is responsible to concatenate and minify CSS or JS files.
 * There is a cache system so that previously minified files are served directly.
 * Otherwise, files ares concatenated, minified and stored in cache.
 * We also check if cache is up to date and needs to be refreshed.
 * @package  jelix
 * @subpackage core
 * @author     Brice Tence
 * @copyright  2010 Brice Tence
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html .
 */
class jMinifier {

    /**
     * @var Minify_Controller active controller for current request
     */
    protected static $_controller = null;

    /**
     * @var array options for current request
     */
    protected static $_options = null;

    /**
     * Cache file locking. Set to false of filesystem is NFS. On at least one 
     * NFS system flock-ing attempts stalled PHP for 30 seconds!
     */
    protected static $min_cacheFileLocking = true;

    /**
     * This is a static class, so private constructor
     */
    private function __construct(){
    }

    /**
     * @param    array   $fileList    Array of file URLs to include
     * @return array of file path to minify's www cached file. A further improvment could be to output several files (size limit for iPhone). That's why this is an array.
     */
    public static function minify( $fileList, $fileType ){
        global $gJConfig,$gJCoord;

        $cachePathCSS = 'cache/minify/css/';
        jFile::createDir(JELIX_APP_WWW_PATH.$cachePathCSS);
        $cachePathJS = 'cache/minify/js/';
        jFile::createDir(JELIX_APP_WWW_PATH.$cachePathJS);

        $minifiedFiles = array();

        $minAppMaxFiles = count($fileList);

        //compute a hash of source files to manage cache
        $sourcesHash = md5(implode(';', $fileList));

        $options = array();
        $options['MinApp']['maxFiles'] = $minAppMaxFiles;
        $cachePath = '';
        $cacheExt = '';
        switch ($fileType) {
        case 'js':
            $options['contentType'] = Minify::TYPE_JS;
            $cachePath = $cachePathJS;
            $cacheExt = 'js';
            break;
        case 'css':
            $options['contentType'] = Minify::TYPE_CSS;
            $cachePath = $cachePathCSS;
            $cacheExt = 'css';
            break;
        default:
            return;
        }

        $cacheFilepath = $cachePath . $sourcesHash . '.' . $cacheExt;


        $cacheFilepathFilemtime = null;
        if( is_file( JELIX_APP_WWW_PATH.$cacheFilepath ) ) {
            $cacheFilepathFilemtime = filemtime( JELIX_APP_WWW_PATH.$cacheFilepath );
        }

        //If we should not check filemtime of source files, let's see if we have the result in our cache
        //We assume minifyCheckCacheFiletime is "on" if not set
        if( isset($GLOBALS['gJConfig']->responseHtml) &&
            $GLOBALS['gJConfig']->responseHtml['minifyCheckCacheFiletime'] === false &&
            $cacheFilepathFilemtime !== null ) {
                $minifiedFiles[] = $cacheFilepath;
                return $minifiedFiles;
            }


        $sources = array();
        //add source files
        foreach ($fileList as $file) {
            $minifySource = new Minify_Source(array(
                'filepath' => realpath($_SERVER['DOCUMENT_ROOT'] . $file)
            ));
            $sources[] = $minifySource;
        }

        $controller = new Minify_Controller_MinApp();
        $controller->sources = $sources;
        $options = $controller->analyzeSources($options);
        $options = $controller->mixInDefaultOptions($options);

        self::$_options = $options;
        self::$_controller = $controller;

        if( $cacheFilepathFilemtime === null ||
            $cacheFilepathFilemtime < self::$_options['lastModifiedTime'] ) {
                //cache does not exist or is to old. Let's refresh it :

                //rewrite URL in CSS files
                if (self::$_options['contentType'] === Minify::TYPE_CSS && self::$_options['rewriteCssUris']) {
                    reset($controller->sources);
                    while (list($key, $source) = each(self::$_controller->sources)) {
                        if ($source->filepath 
                            && !isset($source->minifyOptions['currentDir'])
                            && !isset($source->minifyOptions['prependRelativePath'])
                        ) {
                            $source->minifyOptions['currentDir'] = dirname($source->filepath);
                        }
                    }
                }

                $cacheData = self::combineAndMinify();

                $flag = self::$min_cacheFileLocking
                    ? LOCK_EX
                    : null;

                if (is_file(JELIX_APP_WWW_PATH.$cacheFilepath)) {
                    @unlink(JELIX_APP_WWW_PATH.$cacheFilepath);
                }

                if (! @file_put_contents(JELIX_APP_WWW_PATH.$cacheFilepath, $cacheData, $flag)) {
                    return false;
                }
            }

        $minifiedFiles[] = $cacheFilepath;

        return $minifiedFiles;
    }



    /**
     * Combines sources and minifies the result.
     *
     * @return string
     */
    protected static function combineAndMinify()
    {
        $type = self::$_options['contentType']; // ease readability

        // when combining scripts, make sure all statements separated and
        // trailing single line comment is terminated
        $implodeSeparator = ($type === Minify::TYPE_JS)
            ? "\n;"
            : '';
        // allow the user to pass a particular array of options to each
        // minifier (designated by type). source objects may still override
        // these
        $defaultOptions = isset(self::$_options['minifierOptions'][$type])
            ? self::$_options['minifierOptions'][$type]
            : array();
        // if minifier not set, default is no minification. source objects
        // may still override this
        $defaultMinifier = isset(self::$_options['minifiers'][$type])
            ? self::$_options['minifiers'][$type]
            : false;

        if (Minify_Source::haveNoMinifyPrefs(self::$_controller->sources)) {
            // all source have same options/minifier, better performance
            // to combine, then minify once
            foreach (self::$_controller->sources as $source) {
                $pieces[] = $source->getContent();
            }
            $content = implode($implodeSeparator, $pieces);
            if ($defaultMinifier) {
                self::$_controller->loadMinifier($defaultMinifier);
                $content = call_user_func($defaultMinifier, $content, $defaultOptions);    
            }
        } else {
            // minify each source with its own options and minifier, then combine
            foreach (self::$_controller->sources as $source) {
                // allow the source to override our minifier and options
                $minifier = (null !== $source->minifier)
                    ? $source->minifier
                    : $defaultMinifier;
                $options = (null !== $source->minifyOptions)
                    ? array_merge($defaultOptions, $source->minifyOptions)
                    : $defaultOptions;
                if ($minifier) {
                    self::$_controller->loadMinifier($minifier);
                    // get source content and minify it
                    $pieces[] = call_user_func($minifier, $source->getContent(), $options);     
                } else {
                    $pieces[] = $source->getContent();     
                }
            }
            $content = implode($implodeSeparator, $pieces);
        }

        if ($type === Minify::TYPE_CSS && false !== strpos($content, '@import')) {
            $content = self::_handleCssImports($content);
        }

        // do any post-processing (esp. for editing build URIs)
        if (self::$_options['postprocessorRequire']) {
            require_once self::$_options['postprocessorRequire'];
        }
        if (self::$_options['postprocessor']) {
            $content = call_user_func(self::$_options['postprocessor'], $content, $type);
        }
        return $content;
    }

    /**
     * Bubble CSS @imports to the top or prepend a warning if an
     * @import is detected not at the top.
     */
    protected static function _handleCssImports($css)
    {
        if (self::$_options['bubbleCssImports']) {
            // bubble CSS imports
            preg_match_all('/@import.*?;/', $css, $imports);
            $css = implode('', $imports[0]) . preg_replace('/@import.*?;/', '', $css);
        } else if ('' !== Minify::$importWarning) {
            // remove comments so we don't mistake { in a comment as a block
            $noCommentCss = preg_replace('@/\\*[\\s\\S]*?\\*/@', '', $css);
            $lastImportPos = strrpos($noCommentCss, '@import');
            $firstBlockPos = strpos($noCommentCss, '{');
            if (false !== $lastImportPos
                && false !== $firstBlockPos
                && $firstBlockPos < $lastImportPos
            ) {
                // { appears before @import : prepend warning
                $css = Minify::$importWarning . $css;
            }
        }
        return $css;
    }

}
