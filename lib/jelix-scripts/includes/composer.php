<?php 

class jComposerInstaller {
	
	/*
	 * Execute composer commands if composer.phar and composer.json exists
	 */
	static function exec($args) {
		$composer_config = jApp::configPath().'composer.json';
		$composer_exec = jApp::appPath().'composer.phar';
		if (! file_exists($composer_config) || ! file_exists($composer_exec)) {
			return false;
		}
		
	    $ret = system("COMPOSER=$composer_config php $composer_exec $args");
	    self::reconfigure();
	    return $ret;
	}
	
	
	/*
	 * Return vendor_dir or False if errors
	 */
	static function get_vendor_dir() {
		$jsonfile = jApp::configPath() . 'composer.json';
		$json = file_get_contents($jsonfile);
		if ($json === false) {
			return false;
		}
		
		$composer = json_decode($json, true);
		if (! $composer) {
			return false;
		}
		
		if (isset($composer['config']['vendor-dir'])) {
			$vendor_dir = realpath(jApp::appPath() .'/'. $composer['config']['vendor-dir']);
		} else {
			$vendor_dir = realpath(jApp::appPath() .'/'.'vendor');
		}
		return $vendor_dir;
	}
	
	
	/*
	 * Execute composer install command
	 */
	static function install() {
		return self::exec('install');
	}
	
	/*
	 * Execute composer update command
	 */
	static function update() {
		return self::exec('update');
	}
	
	
	/*
	 * Reconfigure jelix config "modulepath" with current composer paths
	 */
	static function reconfigure() {
		$vendor_dir = realpath(self::get_vendor_dir());
		
		$jelixconfig = jApp::configPath().'/defaultconfig.ini.php';
		$ini = new jIniFileModifier($jelixconfig);
		$modulespath = $ini->getValue('modulesPath');
		
		// Parsing modulespath
		$updated_paths = array();
		foreach (explode(',', $modulespath) as $path) {
			$p = explode(':', $path);
			if (count($p) == 2 && $p[0] == 'app') {
				$modulepath = realpath(jApp::appPath().'/'.$p[1]);
			} else if (count($p) == 2 && $p[0] == 'lib') {
				$modulepath = realpath(LIB_PATH.'/'.$p[1]);
			} else {
				$modulepath = realpath($path);
			}
			
			if ($modulepath && strstr($modulepath, $vendor_dir) === false) {
				$updated_paths[] = $path;
			}
		}
		
		
		// Updating jelix composer modulespath
		if (strstr($vendor_dir, realpath(jApp::appPath())) !== false) {
			$vendor_modulespath = 'app:' . substr($vendor_dir, strlen(realpath(jApp::appPath())) + 1, strlen($vendor_dir));
		} elseif(strstr($vendor_dir, realpath(LIB_PATH)) !== false) {
			$vendor_modulespath = 'lib:' . substr($vendor_dir, strlen(realpath(LIB_PATH)) + 1, strlen($vendor_dir));
		} else {
			$vendor_modulespath = realpath($vendor_dir);
		}
		
		
		if ($vh = opendir($vendor_dir)) {
			while (false !== ($d = readdir($vh))) {
				$path = realpath($vendor_dir . '/' . $d);
				if (is_dir($path) && $d != '.' && $d != '..' && $d != 'composer') {
					$updated_paths[] = $vendor_modulespath . '/' . $d;
				}
			}
		}
		
		// Saving configuration
		$ini->setValue('modulesPath', implode(',', $updated_paths));
		$ini->save();
	}
}
