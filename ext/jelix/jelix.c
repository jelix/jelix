/**
*  Jelix
*  a php extension for Jelix Framework
* @copyright Copyright (c) 2006-2008 Laurent Jouanneau
* @author : Laurent Jouanneau
* @link http://jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_jelix.h"
#include "jelix_interfaces.h"
#include "zend_ini_scanner.h"

ZEND_DECLARE_MODULE_GLOBALS(jelix)

/* True global resources - no need for thread safety here */
static int le_jelix;

/* {{{ jelix_functions[]
 *
 * Every user visible function must have an entry in jelix_functions[].
 */
zend_function_entry jelix_functions[] = {
	PHP_FE(jelix_version,	NULL)
	PHP_FE(jelix_read_ini,  NULL)
	PHP_FE(jelix_scan_module_sel,  NULL)
	PHP_FE(jelix_scan_action_sel,  NULL)
	PHP_FE(jelix_scan_old_action_sel,  NULL)
	PHP_FE(jelix_scan_class_sel,  NULL)
	PHP_FE(jelix_scan_locale_sel,  NULL)
	{NULL, NULL, NULL}	/* Must be the last line in jelix_functions[] */
};
/* }}} */

/* {{{ jelix_module_entry
 */
zend_module_entry jelix_module_entry = {
	STANDARD_MODULE_HEADER,
	"jelix",
	jelix_functions,
	PHP_MINIT(jelix),
	PHP_MSHUTDOWN(jelix),
	PHP_RINIT(jelix),		/* Replace with NULL if there's nothing to do at request start */
	PHP_RSHUTDOWN(jelix),	/* Replace with NULL if there's nothing to do at request end */
	PHP_MINFO(jelix),
	JELIX_VERSION,
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_JELIX
ZEND_GET_MODULE(jelix)
#endif

/* {{{ PHP_INI
 */
PHP_INI_BEGIN()
    /* this flag allow to disable some features of the jelix extension, so we can use
     * dev or opt edition of jelix
     */
    STD_PHP_INI_BOOLEAN("jelix.activated", "1", PHP_INI_SYSTEM, OnUpdateBool, activated, zend_jelix_globals, jelix_globals)
PHP_INI_END()

/* }}} */

/* {{{ php_jelix_init_globals
 */

static void php_jelix_init_globals(zend_jelix_globals *jelix_globals)
{
    jelix_globals->activated = 1;
}

/* }}} */

/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(jelix)
{
    REGISTER_INI_ENTRIES();
    if (JELIX_G(activated)) {
        PHP_MINIT(jelix_interfaces)(INIT_FUNC_ARGS_PASSTHRU);

        REGISTER_STRING_CONSTANT("JELIX_NAMESPACE_BASE", "http://jelix.org/ns/", CONST_CS | CONST_PERSISTENT);
    }
    return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(jelix)
{
    UNREGISTER_INI_ENTRIES();
    return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request start */
/* {{{ PHP_RINIT_FUNCTION
 */
PHP_RINIT_FUNCTION(jelix)
{
    return SUCCESS;
}
/* }}} */

/* Remove if there's nothing to do at request end */
/* {{{ PHP_RSHUTDOWN_FUNCTION
 */
PHP_RSHUTDOWN_FUNCTION(jelix)
{
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(jelix)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "jelix framework support", "enabled");
	php_info_print_table_end();

	DISPLAY_INI_ENTRIES();
}
/* }}} */


/* {{{ proto string confirm_jelix_compiled(string arg)
   Return a string to confirm that the module is compiled in */
PHP_FUNCTION(jelix_version)
{
	if(ZEND_NUM_ARGS() != 0)  ZEND_WRONG_PARAM_COUNT()
    
	RETURN_STRINGL(JELIX_VERSION, sizeof(JELIX_VERSION)-1, 1);
}
/* }}} */


#if PHP_API_VERSION < 20070928
static void jelix_ini_parser_cb(zval *arg1, zval *arg2, int callback_type, zval *obj)
{
	TSRMLS_FETCH();
#else
static void jelix_ini_parser_cb(zval *arg1, zval *arg2, zval *arg3, int callback_type, zval *obj)
{
#endif

/*
ZEND_INI_PARSER_ENTRY       foo = bar
ZEND_INI_PARSER_POP_ENTRY   foo[]=bar
ZEND_INI_PARSER_SECTION		[section]
*/

	if (callback_type == ZEND_INI_PARSER_SECTION) {

		zval *hash, **find_hash;

		if (zend_hash_find(Z_OBJPROP_P(obj), Z_STRVAL_P(arg1), Z_STRLEN_P(arg1)+1, (void **) &find_hash) == SUCCESS
			&& Z_TYPE_P(*find_hash) == IS_ARRAY) {

			JELIX_G(active_ini_file_section) = *find_hash;

		} else if (is_numeric_string(Z_STRVAL_P(arg1), Z_STRLEN_P(arg1), NULL, NULL, 0) != IS_LONG) {
			ALLOC_ZVAL(hash);
			INIT_PZVAL(hash);
			array_init(hash);
			zend_hash_update(Z_OBJPROP_P(obj), Z_STRVAL_P(arg1), Z_STRLEN_P(arg1)+1, &hash, sizeof(zval *), NULL);

			JELIX_G(active_ini_file_section) = hash;
		}


	} else if (arg2) {

		zval *element;
		ALLOC_ZVAL(element);
		*element = *arg2;
		zval_copy_ctor(element);
		INIT_PZVAL(element);


		if (JELIX_G(active_ini_file_section)) {
			// il faut ajouter la valeur en tant qu'element à un tableau
			zval * arr;
			arr = JELIX_G(active_ini_file_section);

			if (callback_type == ZEND_INI_PARSER_ENTRY) {
				if (is_numeric_string(Z_STRVAL_P(arg1), Z_STRLEN_P(arg1), NULL, NULL, 0) != IS_LONG) {
					zend_hash_update(Z_ARRVAL_P(arr), Z_STRVAL_P(arg1), Z_STRLEN_P(arg1)+1, &element, sizeof(zval *), NULL);
				} else {
					ulong key = (ulong) zend_atoi(Z_STRVAL_P(arg1), Z_STRLEN_P(arg1));
					zend_hash_index_update(Z_ARRVAL_P(arr), key, &element, sizeof(zval *), NULL);
				}
			} else if (	callback_type == ZEND_INI_PARSER_POP_ENTRY ) {
				zval *hash, **find_hash;

				if (is_numeric_string(Z_STRVAL_P(arg1), Z_STRLEN_P(arg1), NULL, NULL, 0) != IS_LONG) {
					if (zend_hash_find(Z_ARRVAL_P(arr), Z_STRVAL_P(arg1), Z_STRLEN_P(arg1)+1, (void **) &find_hash) == FAILURE) {
						ALLOC_ZVAL(hash);
						INIT_PZVAL(hash);
						array_init(hash);
						zend_hash_update(Z_ARRVAL_P(arr), Z_STRVAL_P(arg1), Z_STRLEN_P(arg1)+1, &hash, sizeof(zval *), NULL);
					} else {
						hash = *find_hash;
					}
				} else {
					ulong key = (ulong) zend_atoi(Z_STRVAL_P(arg1), Z_STRLEN_P(arg1));
					if (zend_hash_index_find(Z_ARRVAL_P(arr), key, (void **) &find_hash) == FAILURE) {
						ALLOC_ZVAL(hash);
						INIT_PZVAL(hash);
						array_init(hash);
						zend_hash_index_update(Z_ARRVAL_P(arr), key, &hash, sizeof(zval *), NULL);
					} else {
						hash = *find_hash;
					}
				}

				add_next_index_zval(hash, element);
			}

		} else if (is_numeric_string(Z_STRVAL_P(arg1), Z_STRLEN_P(arg1), NULL, NULL, 0) != IS_LONG) {
			// il faut ajouter la valeur en tant que propriété d'objet
			if (callback_type == ZEND_INI_PARSER_ENTRY) {

				add_property_zval(obj , Z_STRVAL_P(arg1), element);

			} else if (	callback_type == ZEND_INI_PARSER_POP_ENTRY ) {
				zval *hash, **find_hash;
				if (zend_hash_find(Z_OBJPROP_P(obj), Z_STRVAL_P(arg1), Z_STRLEN_P(arg1)+1, (void **) &find_hash) == SUCCESS
					&& Z_TYPE_P(*find_hash) == IS_ARRAY ) {
					hash = *find_hash;
				} else {
					ALLOC_ZVAL(hash);
					INIT_PZVAL(hash);
					array_init(hash);
					zend_hash_update(Z_OBJPROP_P(obj), Z_STRVAL_P(arg1), Z_STRLEN_P(arg1)+1, &hash, sizeof(zval *), NULL);
				}
				add_next_index_zval(hash, element);
			}
		}
	}
}


/* {{{ proto object jelix_read_ini(string filename [, object existingobject])
   Parse configuration file */
PHP_FUNCTION(jelix_read_ini)
{
	zval **filename, **confObjectArg, *confObject;
	zend_file_handle fh;

	switch (ZEND_NUM_ARGS()) {

		case 1:
			if (zend_get_parameters_ex(1, &filename) == FAILURE) {
				RETURN_FALSE;
			}
			object_init(return_value);
			confObject = return_value;
			break;

		case 2:
			if (zend_get_parameters_ex(2, &filename, &confObjectArg) == FAILURE) {
				RETURN_FALSE;
			}
			if(Z_TYPE_P(*confObjectArg) != IS_OBJECT){
				php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid second argument, not an object");
				object_init(return_value);
				confObject = return_value;
			}else{
				confObject = *confObjectArg;
			}
			break;

		default:
			ZEND_WRONG_PARAM_COUNT();
			break;
	}

	convert_to_string_ex(filename);

	memset(&fh, 0, sizeof(fh));
	fh.filename = Z_STRVAL_PP(filename);
	Z_TYPE(fh) = ZEND_HANDLE_FILENAME;
	JELIX_G(active_ini_file_section) = NULL;

#if PHP_API_VERSION < 20070928
    zend_parse_ini_file(&fh, 0, (zend_ini_parser_cb_t)jelix_ini_parser_cb, confObject);
#else
    zend_parse_ini_file(&fh, 0, ZEND_INI_SCANNER_NORMAL, (zend_ini_parser_cb_t)jelix_ini_parser_cb, (void *)confObject TSRMLS_CC);
#endif

}
/* }}} */

/* {{{ proto boolean jelix_scan_module_sel(string arg, object tofill)
   scan a string as a jelix selector, and fill object properties with founded values 
/^(([\w\.]+)~)?([\w\.]+)$/
*/
PHP_FUNCTION(jelix_scan_module_sel)
{
    zval **selectorStr, **objectArg;
    int length;
    char *sel, *cursor, *module, *resource;


    switch (ZEND_NUM_ARGS()) {
        case 2:
            if (zend_get_parameters_ex(2, &selectorStr, &objectArg) == FAILURE) {
                RETURN_FALSE;
            }
			break;

/*		case 3:
			if (zend_get_parameters_ex(3, &selectorStr, &objectArg, &typeArg) == FAILURE) {
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "cannot read arguments");
				RETURN_FALSE;
			}
	        convert_to_long_ex(typeArg);
            type = Z_LVAL_PP(typeArg);
            if(type < 1 || type > 4){
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "Third argument doesn't correspond to one of JELIX_SEL_* constant");
                RETURN_FALSE;
            }
            break;*/
		default:
			ZEND_WRONG_PARAM_COUNT();
			break;
	}

    if(Z_TYPE_P(*objectArg) != IS_OBJECT){
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid second argument, not an object");
        RETURN_FALSE;
	}

    int module_length=0;
    int resource_length=0;
    int cursor_count=0;

    convert_to_string_ex(selectorStr);
    length = Z_STRLEN_PP(selectorStr);
    sel = Z_STRVAL_PP(selectorStr);

    cursor_count=0;
    cursor = module = resource = sel;

    int error = 0;

    // parse the module part
    while(cursor_count < length){
        if(*cursor == '~'){
            break;
        }
        if(!( ( *cursor >= 'a' && *cursor <= 'z')
            || ( *cursor >= 'A' && *cursor <= 'Z')
            || ( *cursor >= '0' && *cursor <= '9')
            || *cursor == '_' || *cursor == '.')){
            RETURN_FALSE;
        }
        module_length ++;
        cursor_count ++;
        cursor++;
    }


    if(cursor_count >= length){
        // we don't find any '~' characters, so we have parsed the resource
        resource_length = module_length;
        module_length = 0;
    }else{
        // the string starts by a ~ : it's not really a problem, but we generate an error
        // to keep compatibily with php version of selectors.
        if(module_length == 0){
            RETURN_FALSE;
        }

        cursor_count++;
        cursor++;
        resource = cursor;
        resource_length = 0;
        while(cursor_count < length){
            if(!( ( *cursor >= 'a' && *cursor <= 'z')
                || ( *cursor >= 'A' && *cursor <= 'Z')
                || ( *cursor >= '0' && *cursor <= '9')
                || *cursor == '_' || *cursor == '.')){
                RETURN_FALSE;
            }
            resource_length ++;
            cursor_count ++;
            cursor++;
        }
    }

    if( resource_length == 0 ){
        RETURN_FALSE;
    }

    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "module", sizeof("module") - 1,	module, module_length TSRMLS_CC);
    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	resource, resource_length TSRMLS_CC);
	RETURN_TRUE;
}
/* }}} */


/* {{{ proto boolean jelix_scan_action_sel(string arg, object tofill *)
   scan a string as a jelix selector, and fill object properties with founded values 
/^(?:([a-zA-Z0-9_\.]+|\#)~)?([a-zA-Z0-9_]+|\#)?(?:@([a-zA-Z0-9_]+))?$/
*/
PHP_FUNCTION(jelix_scan_old_action_sel)
{
    zval **selectorStr, **objectArg, **defaultActionArg;
    int length;
    char * sel, *cursor, *module, *resource, *request;


    if(ZEND_NUM_ARGS() != 3) {
        ZEND_WRONG_PARAM_COUNT();
    }
    if (zend_get_parameters_ex(3, &selectorStr, &objectArg, &defaultActionArg) == FAILURE) {
        php_error_docref(NULL TSRMLS_CC, E_WARNING, "cannot read arguments");
        RETURN_FALSE;
    }

    if(Z_TYPE_P(*objectArg) != IS_OBJECT){
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid second argument, not an object");
        RETURN_FALSE;
	}
    if(Z_TYPE_P(*defaultActionArg) != IS_STRING){
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid third argument, not a string");
        RETURN_FALSE;
	}

    int module_length=0;
    int resource_length=0;
    int request_length=0;
    int cursor_count=0;

    convert_to_string_ex(selectorStr);
    length = Z_STRLEN_PP(selectorStr);
    sel = Z_STRVAL_PP(selectorStr);

    cursor_count=0;
    cursor = module = resource = sel;

    int error = 0;
    int sharpOk = 0;
    int hasRequest=0;
    int firstDashPos = -1;
    int hasDot = 0;

    // parse the module part
    while(cursor_count < length){
        if(*cursor == '~' || *cursor == '@'){
            break;
        }
        if(*cursor == '#'){
            if(sharpOk || module_length > 1){
                RETURN_FALSE;
            }
            sharpOk=1;
        }else if(*cursor == '.'){
            hasDot = 1;
        }else if(!( ( *cursor >= 'a' && *cursor <= 'z')
                || ( *cursor >= 'A' && *cursor <= 'Z')
                || ( *cursor >= '0' && *cursor <= '9')
                || *cursor == '_' ) || sharpOk){
                RETURN_FALSE;
        }
        if(*cursor == '_' && firstDashPos == -1){
            firstDashPos = module_length;
        }

        module_length ++;
        cursor_count ++;
        cursor++;
    }

    if(cursor_count >= length){
        // we don't find any '~' characters, so we have parsed the resource
        if(hasDot) RETURN_FALSE;

        resource_length = module_length;
        module_length = 0;
    }else if( *cursor == '@'){
        // we don't find any '~' characters, so we have parsed the resource
        if(hasDot) RETURN_FALSE;

        resource_length = module_length;
        module_length = 0;
        hasRequest = 1;
        // now we parse the @ section
        cursor_count ++;
        cursor++;

        request = cursor;
        request_length = 0;
        while(cursor_count < length){
            if(!( ( *cursor >= 'a' && *cursor <= 'z')
                || ( *cursor >= 'A' && *cursor <= 'Z')
                || ( *cursor >= '0' && *cursor <= '9')
                || *cursor == '_' || *cursor == '.')){
                RETURN_FALSE;
            }
            request_length ++;
            cursor_count ++;
            cursor++;
        }
    }else{
        // the string starts by a ~ : it's not really a problem, but we generate an error
        // to keep compatibily with php version of selectors.
        if(module_length == 0){
            RETURN_FALSE;
        }
        firstDashPos=-1;
        cursor_count++;
        cursor++;
        resource = cursor;
        resource_length = 0;
        sharpOk = 0;
        while(cursor_count < length){
            if(*cursor == '@'){
                break;
            }

            if(*cursor == '#'){
                if(sharpOk || resource_length > 1){
                    RETURN_FALSE;
                }
                sharpOk=1;
            }else if(!( ( *cursor >= 'a' && *cursor <= 'z')
                    || ( *cursor >= 'A' && *cursor <= 'Z')
                    || ( *cursor >= '0' && *cursor <= '9')
                    || *cursor == '_') || sharpOk){
                    RETURN_FALSE;
            }
            if(*cursor == '_' && firstDashPos == -1){
                firstDashPos = resource_length;
            }

            resource_length ++;
            cursor_count ++;
            cursor++;
        }

        if(*cursor == '@'){
            hasRequest = 1;
            cursor_count++;
            cursor++;
            request = cursor;
            request_length = 0;
            while(cursor_count < length){
                if(!( ( *cursor >= 'a' && *cursor <= 'z')
                    || ( *cursor >= 'A' && *cursor <= 'Z')
                    || ( *cursor >= '0' && *cursor <= '9')
                    || *cursor == '_' || *cursor == '.')){
                    RETURN_FALSE;
                }
                request_length ++;
                cursor_count ++;
                cursor++;
            }
        }
    }

    // request shouldn't empty if there is a @
    if(hasRequest && request_length == 0){
        RETURN_FALSE;
    }

    if(resource_length == 0){
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	"default:index", sizeof("default:index")-1 TSRMLS_CC);
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	"default", sizeof("default")-1 TSRMLS_CC);
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	"index", sizeof("index")-1 TSRMLS_CC);
    }else{
        if(resource_length == 1 && *resource == '#'){
           resource_length = Z_STRLEN_PP(defaultActionArg);
           resource = Z_STRVAL_PP(defaultActionArg);
           firstDashPos=-1;
           int i;
           for(i=0; i < resource_length;i++){
                if(resource[i] == ':'){
                    firstDashPos=i;
                    break;
                }
           }
           if (firstDashPos == -1) {
              for(i=0; i < resource_length;i++){
                  if(resource[i] == '_'){
                     firstDashPos=i;
                     break;
                  }
              }
           }
        }

        if(firstDashPos == -1){
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	"default", sizeof("default")-1 TSRMLS_CC);
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	resource, resource_length TSRMLS_CC);

            char *r;
            int ld = sizeof("default:") -1;
            int lr = ld + resource_length;
            r= emalloc(lr+1);
            if (r) {
                memcpy(r, "default:", ld);
                memcpy(r+ld, resource, resource_length);
                r[lr] = 0;
                zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	r, lr TSRMLS_CC);
                efree(r);
            }

        }else if(firstDashPos == 0){
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	"default", sizeof("default")-1 TSRMLS_CC);
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	resource+1, resource_length-1 TSRMLS_CC);

            char *r;
            int ld = sizeof("default:") -1;
            int lr = ld + resource_length-1;
            r= emalloc(lr+1);
            if (r) {
                memcpy(r, "default:", ld);
                memcpy(r+ld, resource+1, resource_length-1);
                r[lr] = 0;
                zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	r, lr TSRMLS_CC);
                efree(r);
            }

        }else if(firstDashPos == resource_length-1){
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	resource, resource_length-1 TSRMLS_CC);
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	"index", sizeof("index")-1 TSRMLS_CC);
            char *r;
            int ld = sizeof("index") -1;
            int lr = resource_length + ld;
            r= emalloc(lr+1);
            if (r) {
                memcpy(r, resource, resource_length-1);
                memcpy(r+resource_length-1, ":index", ld+1);
                r[lr] = 0;
                zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	r, lr TSRMLS_CC);
                efree(r);
            }

        }else{
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	resource, firstDashPos TSRMLS_CC);
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	resource+firstDashPos+1, resource_length-firstDashPos-1 TSRMLS_CC);
            char *r;
            r= emalloc(resource_length+1);
            if (r) {
                memcpy(r, resource, firstDashPos);
                memcpy(r+firstDashPos, ":", 1);
                memcpy(r+firstDashPos+1, resource+firstDashPos+1, resource_length-firstDashPos-1);
                r[resource_length] = 0;
                zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	r, resource_length TSRMLS_CC);
                efree(r);
            }
        }
    }

    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "module", sizeof("module") - 1,	module, module_length TSRMLS_CC);
    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "request", sizeof("request") - 1,	request, request_length TSRMLS_CC);
	RETURN_TRUE;
}
/* }}} */


/* {{{ proto boolean jelix_scan_action_sel(string arg, object tofill *)
   scan a string as a jelix selector, and fill object properties with founded values 
/^(?:([a-zA-Z0-9_\.]+|\#)~)?([a-zA-Z0-9_:]+|\#)?(?:@([a-zA-Z0-9_]+))?$/
*/
PHP_FUNCTION(jelix_scan_action_sel)
{
    zval **selectorStr, **objectArg, **defaultActionArg;
    int length;
    char * sel, *cursor, *module, *resource, *request;


    if(ZEND_NUM_ARGS() != 3) {
        ZEND_WRONG_PARAM_COUNT();
    }
    if (zend_get_parameters_ex(3, &selectorStr, &objectArg, &defaultActionArg) == FAILURE) {
        php_error_docref(NULL TSRMLS_CC, E_WARNING, "cannot read arguments");
        RETURN_FALSE;
    }

    if(Z_TYPE_P(*objectArg) != IS_OBJECT){
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid second argument, not an object");
        RETURN_FALSE;
	}
    if(Z_TYPE_P(*defaultActionArg) != IS_STRING){
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid third argument, not a string");
        RETURN_FALSE;
	}

    int module_length=0;
    int resource_length=0;
    int request_length=0;
    int cursor_count=0;

    convert_to_string_ex(selectorStr);
    length = Z_STRLEN_PP(selectorStr);
    sel = Z_STRVAL_PP(selectorStr);

    cursor_count=0;
    cursor = module = resource = sel;

    int error = 0;
    int sharpOk = 0;
    int hasRequest=0;
    int firstDashPos = -1;
    int hasDot = 0;

    // parse the module part
    while(cursor_count < length){
        if(*cursor == '~' || *cursor == '@'){
            break;
        }
        if(*cursor == '#'){
            if(sharpOk || module_length > 1){
                RETURN_FALSE;
            }
            sharpOk=1;
        }else if(*cursor == '.'){
            hasDot = 1;
        }else if(!( ( *cursor >= 'a' && *cursor <= 'z')
                || ( *cursor >= 'A' && *cursor <= 'Z')
                || ( *cursor >= '0' && *cursor <= '9')
                || *cursor == '_' || *cursor == ':' ) || sharpOk){
                RETURN_FALSE;
        }
        if(*cursor == ':' && firstDashPos == -1){
            firstDashPos = module_length;
        }

        module_length ++;
        cursor_count ++;
        cursor++;
    }

    if(cursor_count >= length){
        // we don't find any '~' characters, so we have parsed the resource
        if(hasDot) RETURN_FALSE;

        resource_length = module_length;
        module_length = 0;
    }else if( *cursor == '@'){
        // we don't find any '~' characters, so we have parsed the resource
        if(hasDot) RETURN_FALSE;

        resource_length = module_length;
        module_length = 0;
        hasRequest = 1;
        // now we parse the @ section
        cursor_count ++;
        cursor++;

        request = cursor;
        request_length = 0;
        while(cursor_count < length){
            if(!( ( *cursor >= 'a' && *cursor <= 'z')
                || ( *cursor >= 'A' && *cursor <= 'Z')
                || ( *cursor >= '0' && *cursor <= '9')
                || *cursor == '_' || *cursor == ':' || *cursor == '.')){
                RETURN_FALSE;
            }
            request_length ++;
            cursor_count ++;
            cursor++;
        }
    }else{
        // the string starts by a ~ : it's not really a problem, but we generate an error
        // to keep compatibily with php version of selectors.
        if(module_length == 0){
            RETURN_FALSE;
        }
        firstDashPos=-1;
        cursor_count++;
        cursor++;
        resource = cursor;
        resource_length = 0;
        sharpOk = 0;
        while(cursor_count < length){
            if(*cursor == '@'){
                break;
            }

            if(*cursor == '#'){
                if(sharpOk || resource_length > 1){
                    RETURN_FALSE;
                }
                sharpOk=1;
            }else if(!( ( *cursor >= 'a' && *cursor <= 'z')
                    || ( *cursor >= 'A' && *cursor <= 'Z')
                    || ( *cursor >= '0' && *cursor <= '9')
                    || *cursor == '_' || *cursor == ':') || sharpOk){
                    RETURN_FALSE;
            }
            if(*cursor == ':' && firstDashPos == -1){
                firstDashPos = resource_length;
            }

            resource_length ++;
            cursor_count ++;
            cursor++;
        }

        if(*cursor == '@'){
            hasRequest = 1;
            cursor_count++;
            cursor++;
            request = cursor;
            request_length = 0;
            while(cursor_count < length){
                if(!( ( *cursor >= 'a' && *cursor <= 'z')
                    || ( *cursor >= 'A' && *cursor <= 'Z')
                    || ( *cursor >= '0' && *cursor <= '9')
                    || *cursor == '_' || *cursor == '.')){
                    RETURN_FALSE;
                }
                request_length ++;
                cursor_count ++;
                cursor++;
            }
        }
    }

    // request shouldn't empty if there is a @
    if(hasRequest && request_length == 0){
        RETURN_FALSE;
    }

    if(resource_length == 0){
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	"default:index", sizeof("default:index")-1 TSRMLS_CC);
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	"default", sizeof("default")-1 TSRMLS_CC);
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	"index", sizeof("index")-1 TSRMLS_CC);
    }else{
        if(resource_length == 1 && *resource == '#'){
           resource_length = Z_STRLEN_PP(defaultActionArg);
           resource = Z_STRVAL_PP(defaultActionArg);
           firstDashPos=-1;
           int i;
           for(i=0; i < resource_length;i++){
                if(resource[i] == ':'){
                    firstDashPos=i;
                    break;
                }
           }
        }

        if(firstDashPos == -1){
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	"default", sizeof("default")-1 TSRMLS_CC);
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	resource, resource_length TSRMLS_CC);

            char *r;
            int ld = sizeof("default:") -1;
            int lr = ld + resource_length;
            r= emalloc(lr+1);
            if (r) {
                memcpy(r, "default:", ld);
                memcpy(r+ld, resource, resource_length);
                r[lr] = 0;
                zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	r, lr TSRMLS_CC);
                efree(r);
            }

        }else if(firstDashPos == 0){
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	"default", sizeof("default")-1 TSRMLS_CC);
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	resource+1, resource_length-1 TSRMLS_CC);

            char *r;
            int ld = sizeof("default") -1;
            int lr = ld + resource_length;
            r= emalloc(lr+1);
            if (r) {
                memcpy(r, "default", ld);
                memcpy(r+ld, resource, resource_length);
                r[lr] = 0;
                zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	r, lr TSRMLS_CC);
                efree(r);
            }

        }else if(firstDashPos == resource_length-1){
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	resource, resource_length-1 TSRMLS_CC);
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	"index", sizeof("index")-1 TSRMLS_CC);
            char *r;
            int ld = sizeof("index") -1;
            int lr = resource_length + ld;
            r= emalloc(lr+1);
            if (r) {
                memcpy(r, resource, resource_length);
                memcpy(r+resource_length, "index", ld);
                r[lr] = 0;
                zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	r, lr TSRMLS_CC);
                efree(r);
            }

        }else{
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "controller", sizeof("controller") - 1,	resource, firstDashPos TSRMLS_CC);
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "method", sizeof("method") - 1,	resource+firstDashPos+1, resource_length-firstDashPos-1 TSRMLS_CC);
            zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	resource, resource_length TSRMLS_CC);
        }
    }

    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "module", sizeof("module") - 1,	module, module_length TSRMLS_CC);
    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "request", sizeof("request") - 1,	request, request_length TSRMLS_CC);
	RETURN_TRUE;
}
/* }}} */


/* {{{ proto boolean jelix_scan_class_sel(string arg, object tofill *)
   scan a string as a jelix selector, and fill object properties with founded values 
/^(([a-zA-Z0-9_\.]+)~)?([a-zA-Z0-9_\.\\/]+)$/
*/
PHP_FUNCTION(jelix_scan_class_sel)
{
    zval **selectorStr, **objectArg;
    switch (ZEND_NUM_ARGS()) {
        case 2:
            if (zend_get_parameters_ex(2, &selectorStr, &objectArg) == FAILURE) {
                RETURN_FALSE;
            }
			break;
		default:
			ZEND_WRONG_PARAM_COUNT();
			break;
	}

    if(Z_TYPE_P(*objectArg) != IS_OBJECT){
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid second argument, not an object");
        RETURN_FALSE;
	}

    int length;
    char *sel, *cursor, *module, *resource, *classname, *subpath;

    int module_length=0;
    int resource_length=0;
    int class_length=0;
    int subpath_length=0;
    int cursor_count=0;

    convert_to_string_ex(selectorStr);
    length = Z_STRLEN_PP(selectorStr);
    sel = Z_STRVAL_PP(selectorStr);

    cursor_count=0;
    cursor = module = resource = classname = subpath = sel;

    int error = 0;
    int hasSlash = 0;

    // parse the module part
    while(cursor_count < length){
        if(*cursor == '~'){
            break;
        }
        if (*cursor == '/') {
            subpath_length = module_length + 1;
            classname = cursor+1;
        } else  if(!( ( *cursor >= 'a' && *cursor <= 'z')
            || ( *cursor >= 'A' && *cursor <= 'Z')
            || ( *cursor >= '0' && *cursor <= '9')
            || *cursor == '_' || *cursor == '.')) {
            RETURN_FALSE;
        }
        module_length ++;
        cursor_count ++;
        cursor++;
    }


    if(cursor_count >= length){
        // we don't find any '~' characters, so we have parsed the resource
        resource_length = module_length;
        module_length = 0;
    }else{
        // the string starts by a ~ : it's not really a problem, but we generate an error
        // to keep compatibility with php version of selectors.
        if(module_length == 0 || subpath_length != 0){
            RETURN_FALSE;
        }

        cursor_count++;
        cursor++;
        classname = subpath = resource = cursor;
        resource_length = 0;
        while(cursor_count < length){
            if (*cursor == '/') {
                subpath_length = resource_length + 1;
                classname = cursor+1;
            } else if(!( ( *cursor >= 'a' && *cursor <= 'z')
                || ( *cursor >= 'A' && *cursor <= 'Z')
                || ( *cursor >= '0' && *cursor <= '9')
                || *cursor == '_' || *cursor == '.')){
                RETURN_FALSE;
            }
            resource_length ++;
            cursor_count ++;
            cursor++;
        }

    }

    if( resource_length == 0 ){
        RETURN_FALSE;
    }

    if (subpath_length == 0) {
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "className", sizeof("className") - 1,	resource, resource_length TSRMLS_CC);
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "subpath", sizeof("subpath") - 1,	subpath, 0 TSRMLS_CC);
    }
    else if (subpath_length == 1) {
        if (resource_length == 1) {
            RETURN_FALSE;
        }
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "className", sizeof("className") - 1,	classname, resource_length-1 TSRMLS_CC);
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "subpath", sizeof("subpath") - 1,	subpath, 0 TSRMLS_CC);
    }
    else {
        if (resource_length == subpath_length) {
            RETURN_FALSE;
        }
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "className", sizeof("className") - 1,	classname, resource_length - subpath_length TSRMLS_CC);
        zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "subpath", sizeof("subpath") - 1,	subpath, subpath_length TSRMLS_CC);
    }

    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "module", sizeof("module") - 1,	module, module_length TSRMLS_CC);
    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	resource, resource_length TSRMLS_CC);

	RETURN_TRUE;
}

/* {{{ proto boolean jelix_scan_locale_sel(string arg, object tofill *)
   scan a string as a jelix selector, and fill object properties with founded values 
/^(([a-zA-Z0-9_\.]+)~)?([a-zA-Z0-9_]+)\.([a-zA-Z0-9_\.]+)$/
*/
PHP_FUNCTION(jelix_scan_locale_sel)
{
    zval **selectorStr, **objectArg;
    switch (ZEND_NUM_ARGS()) {
        case 2:
            if (zend_get_parameters_ex(2, &selectorStr, &objectArg) == FAILURE) {
                RETURN_FALSE;
            }
			break;
		default:
			ZEND_WRONG_PARAM_COUNT();
			break;
	}

    if(Z_TYPE_P(*objectArg) != IS_OBJECT){
		php_error_docref(NULL TSRMLS_CC, E_WARNING, "Invalid second argument, not an object");
        RETURN_FALSE;
	}

    int length;
    char *sel, *cursor, *module, *resource, *filekey, *localekey;

    int module_length=0;
    int resource_length=0;
    int filekey_length=0;
    int localkey_length=0;
    int cursor_count=0;

    convert_to_string_ex(selectorStr);
    length = Z_STRLEN_PP(selectorStr);
    sel = Z_STRVAL_PP(selectorStr);

    cursor_count=0;
    cursor = module = resource = filekey = localekey = sel;

    int error = 0;
    int hasPoint = 0;

    // parse the module part
    while(cursor_count < length){
        if(*cursor == '~'){
            break;
        }
        if (*cursor == '.' && !hasPoint) {
            filekey_length = module_length;
            localekey = cursor+1;
            hasPoint = 1;
        } else  if(!( ( *cursor >= 'a' && *cursor <= 'z')
            || ( *cursor >= 'A' && *cursor <= 'Z')
            || ( *cursor >= '0' && *cursor <= '9')
            || *cursor == '_' || *cursor == '.')) {
            RETURN_FALSE;
        }
        module_length ++;
        cursor_count ++;
        cursor++;
    }

    if(cursor_count >= length){
        // we don't find any '~' characters, so we have parsed the resource
        resource_length = module_length;
        module_length = 0;
    }else{
        // the string starts by a ~ : it's not really a problem, but we generate an error
        // to keep compatibility with php version of selectors.
        if(module_length == 0){
            RETURN_FALSE;
        }
        hasPoint = 0;
        cursor_count++;
        cursor++;
        filekey = localekey = resource = cursor;
        resource_length = 0;
        filekey_length = 0;
        while(cursor_count < length){
            if (*cursor == '.' && !hasPoint) {
                filekey_length = resource_length;
                localekey = cursor+1;
                hasPoint = 1;
            } else if(!( ( *cursor >= 'a' && *cursor <= 'z')
                || ( *cursor >= 'A' && *cursor <= 'Z')
                || ( *cursor >= '0' && *cursor <= '9')
                || *cursor == '_' || *cursor == '-' || *cursor == '.')){
                RETURN_FALSE;
            }
            resource_length ++;
            cursor_count ++;
            cursor++;
        }
    }

    if (resource_length == 0 || filekey_length == 0 || filekey_length == 1 
        || filekey_length == resource_length || filekey_length == resource_length -1) {
        RETURN_FALSE;
    }

    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "fileKey", sizeof("fileKey") - 1,	filekey, filekey_length TSRMLS_CC);
    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "messageKey", sizeof("messageKey") - 1,	localekey, resource_length - filekey_length -1 TSRMLS_CC);
    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "module", sizeof("module") - 1,	module, module_length TSRMLS_CC);
    /*zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	resource, resource_length TSRMLS_CC);*/
    zend_update_property_stringl(Z_OBJCE_P(*objectArg), *objectArg, "resource", sizeof("resource") - 1,	filekey, filekey_length TSRMLS_CC);

	RETURN_TRUE;
}
/* }}} */
