<?php

defined('_JEXEC') or die();

$classes = [];
$aliases = [];

// class aliases for Joomla < 4.0
if (version_compare(JVERSION, '4.0', '<')) {
    $aliases['JHtmlContent'] = 'Joomla\CMS\HTML\Helpers\Content';

    // site only classes
    if (JPATH_BASE == JPATH_SITE) {
        $aliases['ContentHelperRoute'] = 'Joomla\Component\Content\Site\Helper\RouteHelper';
        $aliases['ContentModelArticles'] = 'Joomla\Component\Content\Site\Model\ArticlesModel';
        $classes['ContentHelperRoute'] = JPATH_SITE . '/components/com_content/helpers/route.php';
        $classes['ContentModelArticles'] =
            JPATH_SITE . '/components/com_content/models/articles.php';
    }
}

// class aliases for Joomla < 3.9
if (version_compare(JVERSION, '3.9', '<')) {
    $aliases['JFolder'] = 'Joomla\CMS\Filesystem\Folder';
}

// class aliases for Joomla < 3.8
if (version_compare(JVERSION, '3.8', '<')) {
    $aliases['JComponentHelper'] = 'Joomla\CMS\Component\ComponentHelper';
    $aliases['JDate'] = 'Joomla\CMS\Date\Date';
    $aliases['JDocument'] = 'Joomla\CMS\Document\Document';
    $aliases['JFactory'] = 'Joomla\CMS\Factory';
    $aliases['JLanguageMultilang'] = 'Joomla\CMS\Language\Multilanguage';
    $aliases['JRoute'] = 'Joomla\CMS\Router\Route';
    $aliases['JText'] = 'Joomla\CMS\Language\Text';
    $aliases['JUri'] = 'Joomla\CMS\Uri\Uri';
}

// register classes
foreach ($classes as $class => $path) {
    JLoader::register($class, $path);
}

// register class aliases
foreach ($aliases as $original => $alias) {
    JLoader::registerAlias($alias, $original);
}
