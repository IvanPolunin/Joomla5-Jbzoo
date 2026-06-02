<?php
/**
 * JBZoo Application
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Application
 * @license    GPL-2.0
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/JBZoo
 * @author     Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;

/**
 * Class JBFileHelper
 */
class JBFileHelper extends AppHelper
{
    /**
     * Read custom data from file
     * @param string $path
     * @param bool   $safeMode
     * @return null|string
     */
    public function read($path, $safeMode = false)
    {
        $path = Path::clean($path);

        if (File::exists($path)) {

            if ($safeMode) {
                $handle   = fopen($path, "rb");
                $contents = fread($handle, filesize($path));
                fclose($handle);
            } else {
                $contents = file_get_contents($path);
            }

            return $contents;
        }

        return null;
    }

    /**
     * @param $file
     * @param $data
     * @return bool
     */
    public function save($file, $data)
    {
        $dir = dirname($file);
        if (!Folder::exists($dir)) {
            Folder::create($dir);
        }

        return File::write($file, $data);
    }

    /**
     * Quickest way for getting first file line
     * @param string $file
     * @return string
     */
    public function firstLine($file)
    {
        $cacheRes  = fopen($file, 'r');
        $firstLine = fgets($cacheRes);
        fclose($cacheRes);

        return $firstLine;
    }
}
