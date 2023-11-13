<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2012-2023 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Core;

/**
 * Static class providing some utilities to retrieve information about the server.
 */
class Server
{
    /**
     * tells if we are in a CLI (Command Line Interface) context or not.
     * If this is the case, fills some missing $_SERVER variables when cgi is used.
     *
     * @return bool true if we are in a CLI context
     */
    public static function isCLI()
    {
        if (PHP_SAPI != 'cli' && strpos(PHP_SAPI, 'cgi') === false) {
            return false;
        }

        if (PHP_SAPI != 'cli') {
            // only php-cgi used from the command line can be used, not the one called by apache
            if (isset($_SERVER['HTTP_HOST']) || isset($_SERVER['REDIRECT_URL']) || isset($_SERVER['SERVER_PORT'])) {
                return false;
            }
            header('Content-type: text/plain');
            if (!isset($_SERVER['argv'])) {
                $_SERVER['argv'] = array_keys($_GET);
                $_SERVER['argc'] = count($_GET);
            }
            if (!isset($_SERVER['SCRIPT_NAME'])) {
                $_SERVER['SCRIPT_NAME'] = $_SERVER['argv'][0];
            }
            if (!isset($_SERVER['DOCUMENT_ROOT'])) {
                $_SERVER['DOCUMENT_ROOT'] = '';
            }
        }

        return true;
    }

    /**
     * return the application domain name.
     *
     * @return string
     *
     * @since 1.6.30
     */
    public static function getDomainName()
    {
        // domainName should not be empty, as it is filled by jConfigCompiler
        // but let's check it anyway, jConfigCompiler cache may not be valid anymore
        if (App::config()->domainName != '') {
            return App::config()->domainName;
        }
        list($domain, $port) = self::getDomainPortFromServer();

        return $domain;
    }

    /**
     * return the server URI of the application (protocol + server name + port).
     *
     * @since 1.6.30
     *
     * @param null|mixed $forceHttps
     *
     * @return string the serveur uri
     */
    public static function getServerURI($forceHttps = null)
    {
        if (($forceHttps === null && self::isHttps()) || $forceHttps) {
            $uri = 'https://';
        } else {
            $uri = 'http://';
        }

        $uri .= self::getDomainName();
        $uri .= self::getPort($forceHttps);

        return $uri;
    }

    /**
     * return the server port of the application.
     *
     * @since 1.6.30
     *
     * @param null|mixed $forceHttps
     *
     * @return string the ":port" or empty string
     */
    public static function getPort($forceHttps = null)
    {
        $isHttps = self::isHttps();

        if ($forceHttps === null) {
            $https = $isHttps;
        } else {
            $https = $forceHttps;
        }

        $forcePort = ($https ? App::config()->forceHTTPSPort : App::config()->forceHTTPPort);
        if ($forcePort === true || $forcePort === '1') {
            return '';
        }
        if ($forcePort) { // a number
            $port = $forcePort;
        } elseif ($isHttps != $https) {
            // the asked protocol is different from the current protocol
            // we use the standard port for the asked protocol
            return '';
        } else {
            list($domain, $port) = self::getDomainPortFromServer();
        }

        if (($port === null) || ($port == '') || ($https && $port == '443') || (!$https && $port == '80')) {
            return '';
        }

        return ':'.$port;
    }

    /**
     * Indicate if the request is done or should be done with HTTPS,.
     *
     * It takes care about the Jelix configuration, else from the server
     * parameters.
     *
     * @return bool true if the request is done or should be done with HTTPS
     *
     * @todo support Forwarded and X-Forwarded-Proto headers
     *
     * @since 1.6.30
     */
    public static function isHttps()
    {
        if (App::config()->urlengine['forceProxyProtocol'] == 'https') {
            if (trim(App::config()->forceHTTPSPort) === '') {
                App::config()->forceHTTPSPort = true;
            }

            return true;
        }

        return self::isHttpsFromServer();
    }

    /**
     * return the protocol.
     *
     * @return string http:// or https://
     *
     * @since 1.6.30
     */
    public static function getProtocol()
    {
        return self::isHttps() ? 'https://' : 'http://';
    }

    protected static $domainPortCache;

    /**
     * Return the domain and the port from the server parameters.
     *
     * @param bool $cache
     *
     * @return array the domain and the port number
     *
     * @since 1.6.34
     */
    public static function getDomainPortFromServer($cache = true)
    {
        if ($cache && self::$domainPortCache !== null) {
            return self::$domainPortCache;
        }

        $domain = $port = '';
        if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']) {
            list($domain, $port) = explode(':', $_SERVER['HTTP_HOST'].':');
        } elseif (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME']) {
            list($domain, $port) = explode(':', $_SERVER['SERVER_NAME'].':');
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $domain = gethostname();
        } elseif (php_uname('n') !== false) {
            $domain = php_uname('n');
        }

        if ($port == '') {
            if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']) {
                $port = $_SERVER['SERVER_PORT'];
            } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') {
                $port = '443';
            } else {
                $port = '80';
            }
        }
        self::$domainPortCache = array($domain, $port);

        return self::$domainPortCache;
    }

    /**
     * Indicate if the request is done with HTTPS, as indicated by the server parameters.
     *
     * @since 1.6.34
     */
    public static function isHttpsFromServer()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off';
    }
}
