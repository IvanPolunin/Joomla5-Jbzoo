<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo;

use function YOOtheme\app;
use YOOtheme\Zoo;

class ApplicationHelper
{
    public static function getApplications()
    {
        static $groups;

        if ($groups) {
            return $groups;
        }

        /**
         * @var $app Zoo
         */
        $app = app(Zoo::class);

        $groups = [];
        foreach ($app->application->getApplications() as $application) {

            // App is not installed
            if (!$application->getMetaXMLFile()) {
                continue;
            }

            $groups[$application->application_group][] = $application;
        }

        return $groups;
    }

    public static function getApplication($id)
    {
        foreach (static::getApplications() as $group) {
            foreach ($group as $application) {
                if ($id === $application->id) {
                    return $application;
                }
            }
        }
    }
}
