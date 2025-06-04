<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

class File
{
	public static function getFileSources($sources, $allowedExtensions = null)
    {
        if (!$sources)
        {
            return;
        }

        // Support comma separated values
        $sources = is_array($sources) ? $sources : explode(',', $sources);
        $result  = [];
		$ds 	 = DIRECTORY_SEPARATOR;

        foreach ($sources as $source)
        {
            if (!$pathinfo = pathinfo($source))
            {
                continue;
            }

            if (!isset($pathinfo['extension']))
            {
                continue;
            }
            
            if ($allowedExtensions && !in_array($pathinfo['extension'], $allowedExtensions))
            {
                continue;
            }

            // Add root path to local source
            if (strpos($source, 'http') === false)
            {
                $source = Uri::root() . ltrim($source, '/');
            }

            $result[] = [
                'ext'  => $pathinfo['extension'],
                'file' => $source
			];
        }

        return $result;
    }
}