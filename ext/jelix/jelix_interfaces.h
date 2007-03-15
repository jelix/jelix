/**
*  Jelix
*  a php extension for Jelix Framework
* @copyright Copyright (c) 2006-2007 Laurent Jouanneau
* @author : Laurent Jouanneau
* @link http://jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#ifndef JELIX_INTERFACES_H
#define JELIX_INTERFACES_H

#include "php.h"
#include "php_jelix.h"


extern PHPAPI zend_class_entry * jelix_ce_jIPlugin;
extern PHPAPI zend_class_entry * jelix_ce_jIAuthDriver;
extern PHPAPI zend_class_entry * jelix_ce_jIUrlEngine;
extern PHPAPI zend_class_entry * jelix_ce_jIRestController;
extern PHPAPI zend_class_entry * jelix_ce_jISimpleCompiler;
extern PHPAPI zend_class_entry * jelix_ce_jIMultiFileCompiler;
extern PHPAPI zend_class_entry * jelix_ce_jISelector;

PHP_MINIT_FUNCTION(jelix_interfaces);
#endif
