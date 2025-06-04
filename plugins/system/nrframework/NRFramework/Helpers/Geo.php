<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers;

defined('_JEXEC') or die;

class Geo
{
    /**
     * Detect and return the visitor's country.
     *
     * @return  string   The visitor's country code (GR)
     */
    public static function getVisitorCountryCode()
    {
    	$path = JPATH_PLUGINS . '/system/tgeoip/';

    	if (!is_dir($path))
    	{
    		return;
    	}

    	if (!class_exists('TGeoIP'))
    	{
        	@include_once $path . 'vendor/autoload.php';
        	@include_once $path . 'helper/tgeoip.php';
    	}

        $geo = new \TGeoIP();
        return $geo->getCountryCode();
    }
}