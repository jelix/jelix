<?php

/**
 * @author    Florian Lonqueu-Brochard
 * @contributor Laurent Jouanneau
 *
 * @copyright 2012 Florian Lonqueu-Brochard, 2015 Laurent Jouanneau
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
class jPrefItemGroup
{
    /**
     * @var string the identifiant of the group
     */
    public $id;

    /**
     * the locale selector for its label, shown into a form (jpref_admin).
     */
    public $locale;

    /**
     * the order of the group into a list of group.
     */
    public $order;

    /**
     * @var jPrefItem[]
     */
    public $prefs = array();

    /**
     * Initialise the group with a node from an ini file.
     *
     * @param mixed $node_key
     * @param mixed $node
     */
    public function setFromIniNode($node_key, $node)
    {
        $this->id = substr($node_key, 6);

        if (!empty($node['locale'])) {
            $this->locale = $node['locale'];
        }

        if (!empty($node['order'])) {
            $this->order = $node['order'];
        }
    }

    /**
     * Compare 2 group.
     *
     * @param jPrefItemGroup $a the first group
     * @param jPrefItemGroup $b the second group
     */
    public static function compareGroup(jPrefItemGroup $a, jPrefItemGroup $b)
    {
        if (empty($a->order) || empty($b->order)) {
            if (empty($a->order) && empty($b->order)) {
                return 0;
            }
            if (empty($a->order)) {
                return 1;
            }

            return -1;
        }
        if ($a->order > $b->order) {
            return 1;
        }
        if ($a->order < $b->order) {
            return -1;
        }

        return 0;
    }
}
