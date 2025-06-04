<?php

defined('_JEXEC') or die;

$aliases = [];

// class aliases for Joomla < 3.8
if (version_compare(JVERSION, '3.8', '<')) {
    $aliases['JApplicationHelper'] = 'Joomla\CMS\Application\ApplicationHelper';
    $aliases['JComponentHelper'] = 'Joomla\CMS\Component\ComponentHelper';
    $aliases['JEditor'] = 'Joomla\CMS\Editor\Editor';
    $aliases['JFormField'] = 'Joomla\CMS\Form\FormField';
    $aliases['JHelperTags'] = 'Joomla\CMS\Helper\TagsHelper';
    $aliases['JHtml'] = 'Joomla\CMS\HTML\HTMLHelper';
    $aliases['JObject'] = 'Joomla\CMS\Object\CMSObject';
    $aliases['JPlugin'] = 'Joomla\CMS\Plugin\CMSPlugin';
    $aliases['JSession'] = 'Joomla\CMS\Session\Session';
}

// register class aliases
foreach ($aliases as $original => $alias) {
    JLoader::registerAlias($alias, $original);
}
