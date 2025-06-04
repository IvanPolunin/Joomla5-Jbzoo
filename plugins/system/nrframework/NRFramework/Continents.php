<?php
/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 *  Helper class to work with continent names/codes
 */
class Continents
{
    /**
     *  Return a continent code from it's name
     *
     *  @param  string $cont
     *  @return string|void
     */
    public static function getCode($cont)
    {
        $cont = \ucwords(strtolower($cont));
        foreach (self::getContinentsList() as $key => $value)
        {
            if (strpos($value, $cont) !== false)
            {
                return $key;
            }
        }
        return null;
    }

    /**
     * Returns a list of continents
     * 
     * @return  array
     */
    public static function getContinentsList()
    {
        return [
            'AF' => Text::_('NR_CONTINENT_AF'),
            'AS' => Text::_('NR_CONTINENT_AS'),
            'EU' => Text::_('NR_CONTINENT_EU'),
            'NA' => Text::_('NR_CONTINENT_NA'),
            'SA' => Text::_('NR_CONTINENT_SA'),
            'OC' => Text::_('NR_CONTINENT_OC'),
            'AN' => Text::_('NR_CONTINENT_AN'),
        ];
    }
}