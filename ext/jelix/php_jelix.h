/**
*  Jelix
*  a php extension for Jelix Framework
* @copyright Copyright (c) 2006-2007 Laurent Jouanneau
* @author : Laurent Jouanneau
* @link http://jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#ifndef PHP_JELIX_H
#define PHP_JELIX_H

#define JELIX_VERSION "0.1"
#define JELIX_SELECTOR_MODULE 1
#define JELIX_SELECTOR_ACTION 2
#define JELIX_SELECTOR_LOCALE 3
#define JELIX_SELECTOR_SIMPLEFILE 4

extern zend_module_entry jelix_module_entry;
#define phpext_jelix_ptr &jelix_module_entry

#ifdef PHP_WIN32
#define PHP_JELIX_API __declspec(dllexport)
#else
#define PHP_JELIX_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

PHP_MINIT_FUNCTION(jelix);
PHP_MSHUTDOWN_FUNCTION(jelix);
PHP_RINIT_FUNCTION(jelix);
PHP_RSHUTDOWN_FUNCTION(jelix);
PHP_MINFO_FUNCTION(jelix);

PHP_FUNCTION(jelix_version);
PHP_FUNCTION(jelix_read_ini);
PHP_FUNCTION(jelix_scan_selector);


ZEND_BEGIN_MODULE_GLOBALS(jelix)
    zval * active_ini_file_section;
ZEND_END_MODULE_GLOBALS(jelix)

/* In every utility function you add that needs to use variables 
   in php_jelix_globals, call TSRMLS_FETCH(); after declaring other 
   variables used by that function, or better yet, pass in TSRMLS_CC
   after the last function argument and declare your utility function
   with TSRMLS_DC after the last declared argument.  Always refer to
   the globals in your function as JELIX_G(variable).  You are 
   encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/

#ifdef ZTS
#define JELIX_G(v) TSRMG(jelix_globals_id, zend_jelix_globals *, v)
#else
#define JELIX_G(v) (jelix_globals.v)
#endif

//ZEND_EXTERN_MODULE_GLOBALS(jelix)


#endif	/* PHP_JELIX_H */
