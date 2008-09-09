/**
*  Jelix
*  a php extension for Jelix Framework
* @copyright Copyright (c) 2006-2008 Laurent Jouanneau
* @author : Laurent Jouanneau
* @link http://jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "zend_exceptions.h"
#include "zend_interfaces.h"

#include "php_jelix.h"
#include "jelix_interfaces.h"

PHPAPI zend_class_entry * jelix_ce_jIAcl2Driver;
PHPAPI zend_class_entry * jelix_ce_jIAclDriver;
PHPAPI zend_class_entry * jelix_ce_jIAuthDriver;
PHPAPI zend_class_entry * jelix_ce_jIAuthDriverClass;
PHPAPI zend_class_entry * jelix_ce_jICoordPlugin;
PHPAPI zend_class_entry * jelix_ce_jIFilteredDatatype;
PHPAPI zend_class_entry * jelix_ce_jIFormsDatasource;
PHPAPI zend_class_entry * jelix_ce_jIMultiFileCompiler;
PHPAPI zend_class_entry * jelix_ce_jIRestController;
PHPAPI zend_class_entry * jelix_ce_jISelector;
PHPAPI zend_class_entry * jelix_ce_jISimpleCompiler;
PHPAPI zend_class_entry * jelix_ce_jIUrlEngine;


/* -------------------------------------
interface jIAcl2Driver{
    public function getRight($subject, $resource=null);
    public function clearCache();
}
*/

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAcl2Driver_getRight, 0, 0, 1)
    ZEND_ARG_INFO(0, subject)
    ZEND_ARG_INFO(0, resource)
ZEND_END_ARG_INFO();


zend_function_entry zend_funcs_jIAcl2Driver[] = {
    ZEND_ABSTRACT_ME(jIAcl2Driver, getRight, arginfo_jIAcl2Driver_getRight)
    ZEND_ABSTRACT_ME(jIAcl2Driver, clearCache, NULL)
    {NULL, NULL, NULL}
};


/* -------------------------------------
interface jIAclDriver{
    public function getRight($subject, $resource=null);
    public function clearCache();
}
*/

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAclDriver_getRight, 0, 0, 1)
    ZEND_ARG_INFO(0, subject)
    ZEND_ARG_INFO(0, resource)
ZEND_END_ARG_INFO();


zend_function_entry zend_funcs_jIAclDriver[] = {
    ZEND_ABSTRACT_ME(jIAclDriver, getRight, arginfo_jIAclDriver_getRight)
    ZEND_ABSTRACT_ME(jIAclDriver, clearCache, NULL)
    {NULL, NULL, NULL}
};

/* -------------------------------------

interface jIAuthDriver {
    function __construct($params);
    public function createUser($login, $password);
    public function saveNewUser($user);
    public function removeUser($login);
    public function updateUser($user);
    public function getUser($login);
    public function getUserList($pattern);
    public function changePassword($login, $newpassword);

    public function verifyPassword($login, $password);
}
*/

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAuthDriver_params, 0, 0, 1)
	ZEND_ARG_INFO(0, params)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAuthDriver_pattern, 0, 0, 1)
	ZEND_ARG_INFO(0, pattern)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAuthDriver_user, 0, 0, 1)
	ZEND_ARG_INFO(0, user)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAuthDriver_login, 0, 0, 1)
	ZEND_ARG_INFO(0, login)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAuthDriver_loginpwd, 0, 0, 2)
	ZEND_ARG_INFO(0, login)
	ZEND_ARG_INFO(0, password)
ZEND_END_ARG_INFO();

zend_function_entry zend_funcs_jIAuthDriver[] = {
	ZEND_ABSTRACT_ME(jIAuthDriver, __construct, arginfo_jIAuthDriver_params )
	ZEND_ABSTRACT_ME(jIAuthDriver, createUserObject, arginfo_jIAuthDriver_loginpwd)
	ZEND_ABSTRACT_ME(jIAuthDriver, saveNewUser, arginfo_jIAuthDriver_user)
	ZEND_ABSTRACT_ME(jIAuthDriver, removeUser, arginfo_jIAuthDriver_login)
	ZEND_ABSTRACT_ME(jIAuthDriver, updateUser, arginfo_jIAuthDriver_user)
	ZEND_ABSTRACT_ME(jIAuthDriver, getUser, arginfo_jIAuthDriver_login)
	ZEND_ABSTRACT_ME(jIAuthDriver, getUserList, arginfo_jIAuthDriver_pattern)
	ZEND_ABSTRACT_ME(jIAuthDriver, changePassword, arginfo_jIAuthDriver_loginpwd)
	ZEND_ABSTRACT_ME(jIAuthDriver, verifyPassword, arginfo_jIAuthDriver_loginpwd)
	{NULL, NULL, NULL}
};


/* -------------------------------------
interface jIAuthDriverClass {
    public function insert($user);
    public function deleteByLogin($login);
    public function update($user);
    public function getByLogin($login);
    public function createUserObject();
    public function findAll();
    public function findByLoginPattern($pattern);
    public function updatePassword($login, $cryptedpassword);
    public function getByLoginPassword($login, $cryptedpassword);
}
*/

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAuthDriverClass_pattern, 0, 0, 1)
    ZEND_ARG_INFO(0, pattern)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAuthDriverClass_user, 0, 0, 1)
    ZEND_ARG_INFO(0, user)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAuthDriverClass_login, 0, 0, 1)
    ZEND_ARG_INFO(0, login)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIAuthDriverClass_loginpwd, 0, 0, 2)
    ZEND_ARG_INFO(0, login)
    ZEND_ARG_INFO(0, password)
ZEND_END_ARG_INFO();

zend_function_entry zend_funcs_jIAuthDriverClass[] = {
    ZEND_ABSTRACT_ME(jIAuthDriverClass, insert,        arginfo_jIAuthDriverClass_user)
    ZEND_ABSTRACT_ME(jIAuthDriverClass, deleteByLogin, arginfo_jIAuthDriverClass_login)
    ZEND_ABSTRACT_ME(jIAuthDriverClass, update,        arginfo_jIAuthDriverClass_user)
    ZEND_ABSTRACT_ME(jIAuthDriverClass, getByLogin,    arginfo_jIAuthDriverClass_login)
    ZEND_ABSTRACT_ME(jIAuthDriverClass, findAll,       NULL)
    ZEND_ABSTRACT_ME(jIAuthDriverClass, findByLoginPattern,   arginfo_jIAuthDriverClass_pattern)
    ZEND_ABSTRACT_ME(jIAuthDriverClass, updatePassword, arginfo_jIAuthDriverClass_loginpwd)
    ZEND_ABSTRACT_ME(jIAuthDriverClass, getByLoginPassword, arginfo_jIAuthDriverClass_loginpwd)
    {NULL, NULL, NULL}
};


/* -------------------------------------
interface jICoordPlugin{
    public function __construct($config);
    public function beforeAction($param);
    public function beforeOutput();
    public function afterProcess();
}
*/

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jICoordPlugin_beforeAction, 0, 0, 1)
    ZEND_ARG_INFO(0, params)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jICoordPlugin_config, 0, 0, 1)
    ZEND_ARG_INFO(0, config)
ZEND_END_ARG_INFO();

zend_function_entry zend_funcs_jICoordPlugin[] = {
    ZEND_ABSTRACT_ME(jICoordPlugin, __construct, arginfo_jICoordPlugin_config )
    ZEND_ABSTRACT_ME(jICoordPlugin, beforeAction, arginfo_jICoordPlugin_beforeAction)
    ZEND_ABSTRACT_ME(jICoordPlugin, beforeOutput, NULL)
    ZEND_ABSTRACT_ME(jICoordPlugin, afterProcess, NULL)
    {NULL, NULL, NULL}
};


/* -------------------------------------
interface jIFilteredDatatype {
    public function getFilteredValue();
}
*/
zend_function_entry zend_funcs_jIFilteredDatatype[] = {
    ZEND_ABSTRACT_ME(jIFilteredDatatype, getFilteredValue, NULL)
    {NULL, NULL, NULL}
};


/* -------------------------------------
interface jIFormsDatasource {
    public function getData($form);
    public function getLabel($key);
}
*/

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIFormsDatasource_params, 0, 0, 1)
    ZEND_ARG_INFO(0, params)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIFormsDatasource_key, 0, 0, 1)
    ZEND_ARG_INFO(0, keys)
ZEND_END_ARG_INFO();

zend_function_entry zend_funcs_jIFormsDatasource[] = {
    ZEND_ABSTRACT_ME(jIFormsDatasource, getData, arginfo_jIFormsDatasource_params )
    ZEND_ABSTRACT_ME(jIFormsDatasource, getLabel, arginfo_jIFormsDatasource_key)
    {NULL, NULL, NULL}
};


/* -------------------------------------
interface jIMultiFileCompiler {
    public function compileItem($sourceFile, $module);
    public function endCompile($cachefile);
}

*/
static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIMultiFileCompiler_compileItem, 0, 0, 2)
    ZEND_ARG_INFO(0, sourceFile)
    ZEND_ARG_INFO(0, module)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIMultiFileCompiler_endCompile, 0, 0, 1)
    ZEND_ARG_INFO(0, cachefile)
ZEND_END_ARG_INFO();


zend_function_entry zend_funcs_jIMultiFileCompiler[] = {
    ZEND_ABSTRACT_ME(jIMultiFileCompiler, compileItem, arginfo_jIMultiFileCompiler_compileItem)
    ZEND_ABSTRACT_ME(jIMultiFileCompiler, endCompile, arginfo_jIMultiFileCompiler_endCompile)
    {NULL, NULL, NULL}
};


/* -------------------------------------
interface jIRestController{
    public function get();
    public function post();
    public function put();
    public function delete();
}
*/

zend_function_entry zend_funcs_jIRestController[] = {
    ZEND_ABSTRACT_ME(jIRestController, get , NULL)
    ZEND_ABSTRACT_ME(jIRestController, post , NULL)
    ZEND_ABSTRACT_ME(jIRestController, put, NULL)
    ZEND_ABSTRACT_ME(jIRestController, delete, NULL)
    {NULL, NULL, NULL}
};


/* -------------------------------------
interface jISelector {
    public function getPath ();
    public function getCompiledFilePath ();
    public function getCompiler();
    public function useMultiSourceCompiler();
    public function toString($full=false);
}

*/
static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jISelector_toString, 0, 0, 0)
	ZEND_ARG_INFO(0, full)
ZEND_END_ARG_INFO();


zend_function_entry zend_funcs_jISelector[] = {
	ZEND_ABSTRACT_ME(jISelector, getPath, NULL)
	ZEND_ABSTRACT_ME(jISelector, getCompiledFilePath, NULL)
	ZEND_ABSTRACT_ME(jISelector, getCompiler, NULL)
	ZEND_ABSTRACT_ME(jISelector, useMultiSourceCompiler, NULL)
	ZEND_ABSTRACT_ME(jISelector, toString, arginfo_jISelector_toString)
	{NULL, NULL, NULL}
};


/* -------------------------------------

interface jISimpleCompiler {
    public function compile($aSelector);
}

*/
static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jISimpleCompiler_compile, 0, 0, 1)
    ZEND_ARG_INFO(0, aSelector)
ZEND_END_ARG_INFO();


zend_function_entry zend_funcs_jISimpleCompiler[] = {
    ZEND_ABSTRACT_ME(jISimpleCompiler, compile, arginfo_jISimpleCompiler_compile)
    {NULL, NULL, NULL}
};

/* -------------------------------------
interface jIUrlEngine {
  public function parse($scriptNamePath, $pathinfo, $params );
  public function parseFromRequest($request, $params );
  public function create($urlact);
}

*/
static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIUrlEngine_parse, 0, 0, 3)
    ZEND_ARG_INFO(0, scriptNamePath)
    ZEND_ARG_INFO(0, pathinfo)
    ZEND_ARG_INFO(0, params)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIUrlEngine_parseFromRequest, 0, 0, 2)
    ZEND_ARG_INFO(0, request)
    ZEND_ARG_INFO(0, params)
ZEND_END_ARG_INFO();

static
ZEND_BEGIN_ARG_INFO_EX(arginfo_jIUrlEngine_create, 0, 0, 1)
    ZEND_ARG_INFO(0, urlact)
ZEND_END_ARG_INFO();


zend_function_entry zend_funcs_jIUrlEngine[] = {
    ZEND_ABSTRACT_ME(jIUrlEngine, parse, arginfo_jIUrlEngine_parse)
    ZEND_ABSTRACT_ME(jIUrlEngine, parseFromRequest, arginfo_jIUrlEngine_parseFromRequest)
    ZEND_ABSTRACT_ME(jIUrlEngine, create, arginfo_jIUrlEngine_create)
    {NULL, NULL, NULL}
};



#define JELIX_DECLARE_INTERFACE(classname) \
    INIT_CLASS_ENTRY(_ce, #classname, zend_funcs_##classname) \
	jelix_ce_##classname = zend_register_internal_interface(&_ce TSRMLS_CC);



PHP_MINIT_FUNCTION(jelix_interfaces)
{

	zend_class_entry _ce;

    JELIX_DECLARE_INTERFACE(jIAcl2Driver)
    JELIX_DECLARE_INTERFACE(jIAclDriver)
    JELIX_DECLARE_INTERFACE(jIAuthDriver)
    JELIX_DECLARE_INTERFACE(jIAuthDriverClass)
	JELIX_DECLARE_INTERFACE(jICoordPlugin)
    JELIX_DECLARE_INTERFACE(jIFilteredDatatype)
    JELIX_DECLARE_INTERFACE(jIFormsDatasource)
    JELIX_DECLARE_INTERFACE(jIMultiFileCompiler)
	JELIX_DECLARE_INTERFACE(jIRestController)
	JELIX_DECLARE_INTERFACE(jISelector)
    JELIX_DECLARE_INTERFACE(jISimpleCompiler)
    JELIX_DECLARE_INTERFACE(jIUrlEngine)
}
