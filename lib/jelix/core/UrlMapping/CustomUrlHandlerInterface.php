<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2005-2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

/**
 * interface for custom url handler.
 */
interface CustomUrlHandlerInterface
{
    /**
     * create the jUrlAction corresponding to the given jUrl. Return false if it doesn't correspond.
     *
     * @param \jUrl $url
     *
     * @return false|\jUrlAction
     */
    public function parse(\jUrl $url);

    /**
     * fill the given jurl object depending the jUrlAction object.
     *
     * @param \jUrlAction $urlact
     * @param \jUrl       $url
     */
    public function create(\jUrlAction $urlact, \jUrl $url);
}
