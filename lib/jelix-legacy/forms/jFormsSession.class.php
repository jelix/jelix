<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @copyright   2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * This class acts as a cache proxy during a request processing, for jForms
 * containers. It allows to get and store jForms containers data into an
 * external storage using jCache.
 *
 * jFormsSession instances can be stored temporarly in session, in order to have
 * the opportunity, at the end of the request processing, so when the session is
 * saved, to store automatically jForms containers data that have been readed,
 * created or modified.
 *
 * A profile for jCache, named 'jforms', may be declared in profiles.ini.php,
 * so you can choose where jforms data are stored. A virtual profile is used
 * if no one is defined, and use files for the cache.
 */
class jFormsSession
{
    const DEFAULT_ID = 0;

    public function __construct()
    {
        $this->loadProfile();
    }

    protected function loadProfile()
    {
        // be sure we have a profile for jCache.
        try {
            jProfiles::get('jcache', 'jforms', true);
        } catch (Exception $e) {
            // no profile, let's create a default profile
            $cacheDir = jApp::tempPath('jforms');
            jFile::createDir($cacheDir);
            $params = array(
                'enabled' => 1,
                'driver' => 'file',
                'ttl' => 3600 * 48,
                'automatic_cleaning_factor' => 3,
                'cache_dir' => $cacheDir,
                'directory_level' => 3,
            );
            jProfiles::createVirtualProfile('jcache', 'jforms', $params);
        }
    }

    public function __wakeup()
    {
        $this->loadProfile();
    }

    public function __sleep()
    {
        $this->save();

        return array();
    }

    public function save()
    {
        foreach ($this->containers as $key => $container) {
            jCache::set($key, serialize($container), null, 'jforms');
        }
    }

    protected $containers = array();

    /**
     * calculate the cache key corresponding to a form instance.
     *
     * @param string $formSel the selector of the form
     * @param mixed  $formId  the id of the instance
     *
     * @return array first element is a selector object,
     *               second is the normalized form id
     *               third is the key as string
     */
    public function getCacheKey($formSel, $formId)
    {
        $sel = new jSelectorForm($formSel);
        // normalize the form id
        if ($formId === null || $formId === '') {
            $formId = self::DEFAULT_ID;
        }

        $fid = is_array($formId) ? serialize($formId) : $formId;

        return array($sel, $formId, $sel->module.':'.$sel->resource.':'.session_id().':'.sha1($fid));
    }

    public function getContainer($formSel, $formId, $createIfNeeded)
    {
        list($selector, $formId, $key) = $this->getCacheKey($formSel, $formId);

        if (isset($this->containers[$key])) {
            if ($createIfNeeded && is_numeric($formId) && $formId == self::DEFAULT_ID) {
                ++$this->containers[$key]->refcount;
            }

            return array($this->containers[$key], $selector);
        }

        $container = jCache::get($key, 'jforms');
        if ($container === false) {
            if ($createIfNeeded) {
                $container = new jFormsDataContainer($selector->toString(), $formId);
            } else {
                return array(null, $selector);
            }
        } else {
            $container = unserialize($container);
        }

        if ($createIfNeeded && is_numeric($formId) && $formId == self::DEFAULT_ID) {
            ++$container->refcount;
        }

        $this->containers[$key] = $container;

        return array($container, $selector);
    }

    public function deleteContainer($formSel, $formId)
    {
        list($selector, $formId, $key) = $this->getCacheKey($formSel, $formId);
        if (isset($this->containers[$key])) {
            $container = $this->containers[$key];
        } else {
            $container = jCache::get($key, 'jforms');
            if (!$container) {
                return;
            }
            $container = unserialize($container);
            $this->containers[$key] = $container;
        }
        if (is_numeric($formId) && $formId == self::DEFAULT_ID) {
            if ((--$container->refcount) > 0) {
                $container->clear();

                return;
            }
        }
        jCache::delete($key, 'jforms');
        unset($this->containers[$key]);
    }

    public function garbage()
    {
        jCache::garbage('jforms');
        foreach ($this->containers as $key => $container) {
            if (!jCache::get($key, 'jforms')) {
                unset($this->containers[$key]);
            }
        }
    }

    public function flushAll()
    {
        jCache::flush('jforms');
        $this->containers = array();
    }
}
