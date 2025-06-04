<?php
/**
 * @package         Modules Anywhere
 * @version         8.1.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2024 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Language\Text as JText;

class PlgSystemModulesAnywhereInstallerScript
{
    public function postflight($install_type, $adapter)
    {
        if ( ! in_array($install_type, ['install', 'update']))
        {
            return true;
        }

        self::deleteJoomla3Files();
        self::disableCoreEditorPlugin();

        return true;
    }

    public function uninstall($adapter)
    {
        self::enableCoreEditorPlugin();
    }

    private static function delete($files = [])
    {
        foreach ($files as $file)
        {
            if (is_dir($file))
            {
                JFolder::delete($file);
            }

            if (is_file($file))
            {
                JFile::delete($file);
            }
        }
    }

    private static function deleteJoomla3Files()
    {
        self::delete(
            [
                JPATH_SITE . '/plugins/system/modulesanywhere/vendor',
            ]
        );
    }

    private static function disableCoreEditorPlugin()
    {
        $db = JFactory::getDbo();

        $query = self::getCoreEditorPluginQuery()
            ->set($db->quoteName('enabled') . ' = 0')
            ->where($db->quoteName('enabled') . ' = 1');
        $db->setQuery($query);
        $db->execute();

        if ( ! $db->getAffectedRows())
        {
            return;
        }

        JFactory::getApplication()->enqueueMessage(JText::_('Joomla\'s own "Module" editor button has been disabled'), 'warning');
    }

    private static function enableCoreEditorPlugin()
    {
        $db = JFactory::getDbo();

        $query = self::getCoreEditorPluginQuery()
            ->set($db->quoteName('enabled') . ' = 1')
            ->where($db->quoteName('enabled') . ' = 0');
        $db->setQuery($query);
        $db->execute();

        if ( ! $db->getAffectedRows())
        {
            return;
        }

        JFactory::getApplication()->enqueueMessage(JText::_('Joomla\'s own "Module" editor button has been re-enabled'), 'warning');
    }

    private static function getCoreEditorPluginQuery()
    {
        $db = JFactory::getDbo();

        return $db->getQuery(true)
            ->update('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('module'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('editors-xtd'));
    }
}
