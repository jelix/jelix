<?php
/**
 * check a jelix installation.
 *
 * @author   Laurent Jouanneau
 * @contributor Bastien Jaillot
 * @contributor Olivier Demah, Brice Tence, Julien Issler
 *
 * @copyright 2007-2018 Laurent Jouanneau, 2008 Bastien Jaillot, 2009 Olivier Demah, 2010 Brice Tence, 2011 Julien Issler
 *
 * @see     http://www.jelix.org
 * @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @since 1.0b2
 */

namespace Jelix\Installer\Checker;

/**
 * base class for a jelix installation checker.
 *
 * @since 1.7
 */
class CheckerBase
{
    /**
     * the object responsible of the results output.
     *
     * @var \Jelix\Installer\Reporter\ReporterInterface
     */
    protected $reporter;

    /**
     * @var \Jelix\Installer\Checker\Messages
     */
    public $messages;

    protected $buildProperties = array();

    public $verbose = false;

    public $checkForInstallation = false;

    /**
     * @param mixed $lang
     */
    public function __construct(
        \Jelix\Installer\Reporter\ReporterInterface $reporter,
        $lang = 'en'
    ) {
        $this->reporter = $reporter;
        if (is_string($lang)) {
            $this->messages = new Messages($lang);
        } elseif ($lang instanceof Messages) {
            $this->messages = $lang;
        } else {
            throw new \Exception('Error checker: No message provider');
        }
    }

    protected $otherExtensions = array();

    public function addExtensionCheck($extension, $required)
    {
        $this->otherExtensions[$extension] = $required;
    }

    protected $otherPaths = array();

    /**
     * @since 1.2.5
     *
     * @param mixed $pathOrFileName
     */
    public function addWritablePathCheck($pathOrFileName)
    {
        if (is_array($pathOrFileName)) {
            $this->otherPaths = array_merge($this->otherPaths, $pathOrFileName);
        } else {
            $this->otherPaths[] = $pathOrFileName;
        }
    }

    protected $databases = array();
    protected $dbRequired = false;

    public function addDatabaseCheck($databases, $required)
    {
        $this->databases = $databases;
        $this->dbRequired = $required;
    }

    /**
     * run the ckecking.
     */
    public function run()
    {
        $this->reporter->start();

        try {
            $this->_otherCheck();
            $this->checkPhpExtensions();
            $this->checkPhpSettings();
        } catch (\Exception $e) {
            $this->error('cannot.continue', $e->getMessage());
        }
        $this->reporter->end();
    }

    protected function _otherCheck()
    {
    }

    protected function error($msg, $msgparams = array(), $extraMsg = '')
    {
        $this->reporter->message($this->messages->get($msg, $msgparams).$extraMsg, 'error');
    }

    protected function ok($msg, $msgparams = array())
    {
        $this->reporter->message($this->messages->get($msg, $msgparams), 'ok');
    }

    /**
     * generate a warning.
     *
     * @param string $msg       the key of the message to display
     * @param mixed  $msgparams
     */
    protected function warning($msg, $msgparams = array())
    {
        $this->reporter->message($this->messages->get($msg, $msgparams), 'warning');
    }

    protected function notice($msg, $msgparams = array())
    {
        $this->reporter->message($this->messages->get($msg, $msgparams), 'notice');
    }

    protected function checkPhpExtensions()
    {
        $ok = true;
        if (!version_compare($this->buildProperties['PHP_VERSION_TARGET'], phpversion(), '<=')) {
            $this->error('php.bad.version');
            $notice = $this->messages->get('php.version.required', $this->buildProperties['PHP_VERSION_TARGET']);
            $notice .= '. '.$this->messages->get('php.version.current', phpversion());
            $this->reporter->message($notice, 'notice');
            $ok = false;
        } elseif ($this->verbose) {
            $this->ok('php.ok.version', phpversion());
        }

        $extensions = array('dom', 'SPL', 'SimpleXML', 'pcre', 'session',
            'tokenizer', 'iconv', 'filter', 'json', );

        foreach ($extensions as $name) {
            if (!extension_loaded($name)) {
                $this->error('extension.required.not.installed', $name);
                $ok = false;
            } elseif ($this->verbose) {
                $this->ok('extension.required.installed', $name);
            }
        }

        if (count($this->databases)) {
            $driversInfos = \jDbParameters::getDriversInfosList();
            $okdb = false;

            array_combine($this->databases, array_fill(0, count($this->databases), false));

            $alreadyExtensionsChecked = array();
            $okdatabases = array();
            foreach ($this->databases as $name) {
                foreach ($driversInfos as $driverInfo) {
                    list($dbType, $nativeExt, $pdoExt, $jdbDriver, $pdoDriver) = $driverInfo;

                    if ($name == $dbType || $name == $nativeExt || $name == $pdoDriver) {
                        if (extension_loaded($nativeExt)) {
                            if (!isset($alreadyExtensionsChecked[$nativeExt])) {
                                if ($this->verbose) {
                                    $this->ok('extension.installed', $nativeExt);
                                }
                                $alreadyExtensionsChecked[$nativeExt] = true;
                                $okdb = true;
                                $okdatabases[$name] = true;
                            }
                        } else {
                            if (!isset($alreadyExtensionsChecked[$nativeExt])) {
                                if ($this->verbose) {
                                    $this->notice('extension.not.installed', $nativeExt);
                                }
                                $alreadyExtensionsChecked[$nativeExt] = false;
                            }
                        }
                        if (extension_loaded($pdoExt)) {
                            if (!isset($alreadyExtensionsChecked[$pdoExt])) {
                                if ($this->verbose) {
                                    $this->ok('extension.installed', $pdoExt);
                                }
                                $alreadyExtensionsChecked[$pdoExt] = true;
                                $okdb = true;
                                $okdatabases[$name] = true;
                            }
                        } else {
                            if (!isset($alreadyExtensionsChecked[$pdoExt])) {
                                if ($this->verbose) {
                                    $this->notice('extension.not.installed', $pdoExt);
                                }
                                $alreadyExtensionsChecked[$pdoExt] = false;
                            }
                        }
                    }
                }
            }
            if ($this->dbRequired) {
                if ($okdb) {
                    $this->ok('extension.database.ok', implode(',', array_keys($okdatabases)));
                } else {
                    $this->error('extension.database.missing');
                    $ok = false;
                }
            } else {
                if ($okdb) {
                    $this->ok('extension.database.ok2', implode(',', array_keys($okdatabases)));
                } else {
                    $this->notice('extension.database.missing2');
                }
            }
        }

        foreach ($this->otherExtensions as $name => $required) {
            $req = ($required ? 'required' : 'optional');
            if (!extension_loaded($name)) {
                if ($required) {
                    $this->error('extension.'.$req.'.not.installed', $name);
                    $ok = false;
                } else {
                    $this->notice('extension.'.$req.'.not.installed', $name);
                }
            } elseif ($this->verbose) {
                $this->ok('extension.'.$req.'.installed', $name);
            }
        }

        if ($ok) {
            $this->ok('extensions.required.ok');
        }

        return $ok;
    }

    protected function checkPhpSettings()
    {
        $ok = true;

        if (ini_get('magic_quotes_gpc') == 1) {
            $this->error('ini.magic_quotes_gpc');
            $ok = false;
        }

        if (ini_get('magic_quotes_runtime') == 1) {
            $this->error('ini.magic_quotes_runtime');
            $ok = false;
        }

        if (ini_get('session.auto_start') == 1) {
            $this->error('ini.session.auto_start');
            $ok = false;
        }

        if (ini_get('safe_mode') == 1) {
            $this->error('ini.safe_mode');
            $ok = false;
        }

        if (ini_get('register_globals') == 1) {
            $this->warning('ini.register_globals');
            $ok = false;
        }

        if (ini_get('asp_tags') == 1) {
            $this->notice('ini.asp_tags');
        }
        if ($ok) {
            $this->ok('ini.ok');
        }

        return $ok;
    }
}
