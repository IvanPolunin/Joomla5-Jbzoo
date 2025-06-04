<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;

class Geo extends SmartTag
{
	/**
     * This is a Pro-only feature
     *
     * @var boolean
     */
    public $proOnly = true;

    /**
     * The Geolocation object
     *
     * @var mixed   Object on success, Null when TGeoIP plugin can't be loaded.
     */
    private $geo;

    /**
     * Class constructor
     */
    public function __construct($factory = null, $options = null)
    {
        parent::__construct($factory = null, $options = null);
        $this->loadGeo();
    }

    /**
     * Return the visitor's detected multilingual Country Name
     *
     * @return mixed    String on success, null on failure
     */
    public function getCountry()
    {
        if ($this->geo && $code = $this->geo->getCountryCode())
        {
            return Text::_('NR_COUNTRY_' . $code);
        }
    }

    /**
     * Return the visitor's detected Country code
     *
     * @return mixed    String on success, null on failure
     */
    public function getCountryCode()
    {
        if ($this->geo)
        {
            return $this->geo->getCountryCode();
        }
    }

    /**
     * Return the visitor's detected City name
     *
     * @return mixed    String on success, null on failure
     */
    public function getCity()
    {
        if ($this->geo)
        {
            return $this->geo->getCity();
        }
    }

    /**
     *  Return the visitor's detected Regions
     *
     * @return mixed    String on success, null on failure
     */
    public function getRegion()
    {
        if (!$record = $this->geo)
        {
            return;
        }

        // Ensure we have regions
        if (!isset($record->subdivisions))
        {
            return;
        }

        $regions = [];

        // Skip if no regions found
        if (!$record->subdivisions)
        {
            return;
        }

        $langCode = $this->factory->getLanguage()->getTag();
        $langCode = explode('-', $langCode)[0];
        
        foreach ($record->subdivisions as $region)
        {
            $regions[] = isset($region->names[$langCode]) ? $region->names[$langCode] : $region->names['en'];
        }

        return implode(', ', $regions);
    }

    /**
     * Return the visitor's full geo location (Country, City, Regions)
     *
     * @return mixed    String on success, null on failure
     */
    public function getLocation()
    {
        $location_parts = array_filter([
            $this->getCountry(),
            $this->getCity(),
            $this->getRegion()
        ]);

        return implode(', ', $location_parts);
    }

    /**
     *  Load GeoIP Classes
     *
     *  @return  void
     */
    private function loadGeo($ip = null)
    {
        if (!class_exists('TGeoIP'))
        {
            $path = JPATH_PLUGINS . '/system/tgeoip';

            if (@file_exists($path . '/helper/tgeoip.php'))
            {
                if (@include_once($path . '/vendor/autoload.php'))
                {
                    @include_once $path . '/helper/tgeoip.php';
                }
            }

            // If for some reason the tgeoip plugin files do not exist, abort
            if (!class_exists('TGeoIP'))
            {
                return;
            }
        }

        $this->geo = new \TGeoIP($ip);
    }
}