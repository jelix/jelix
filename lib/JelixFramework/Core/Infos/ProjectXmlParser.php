<?php
/**
 * @author    Vincent Viaud
 * @contributor Laurent Jouanneau
 *
 * @copyright 2010 Vincent Viaud, 2012 FoxMaSk, 2014-2018 Laurent Jouanneau
 *
 * @see      http://havefnubb.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

/**
 * Class to parse the project.xml file of an application.
 */
class ProjectXmlParser extends XmlParserAbstract
{
    protected function createInfos()
    {
        return new AppInfos($this->path, true);
    }
}
