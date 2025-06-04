<?php
defined('_JEXEC') or die;

class plgSystemSelectcityInstallerScript
{
    public function postflight($type, $parent)
    {
        if ($type === 'install' || $type === 'update')
        {
            // Получаем объект базы данных
            $db = JFactory::getDbo();
            // Имя вашего плагина
            $plugin_name = 'selectcity';
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('element') . ' = ' . $db->quote($plugin_name))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
            $db->setQuery($query);
            $db->execute();
        }
    }
}