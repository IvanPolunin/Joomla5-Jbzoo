<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

if (!class_exists('App')) {

	// init vars
	$path = dirname(__FILE__);

    // load classes
    $aliases = [];
    $classes = [
        'App' => $path.'/classes/app.php',
        'AppController' => $path.'/classes/controller.php',
        'AppHelper' => $path.'/classes/helper.php',
        'AppView' => $path.'/classes/view.php',
        'ComponentHelper' => $path.'/helpers/component.php',
        'PathHelper' => $path.'/helpers/path.php',
        'UserAppHelper' => $path.'/helpers/user.php',
    ];

    // class aliases for Joomla < 4.0
    if (version_compare(JVERSION, '4.0', '<')) {
        $aliases['FieldsHelper'] = 'Joomla\Component\Fields\Administrator\Helper\FieldsHelper';
        $aliases['FinderIndexer'] = 'Joomla\Component\Finder\Administrator\Indexer\Indexer';
        $aliases['FinderIndexerAdapter'] = 'Joomla\Component\Finder\Administrator\Indexer\Adapter';
        $aliases['FinderIndexerHelper'] = 'Joomla\Component\Finder\Administrator\Indexer\Helper';
        $aliases['FinderIndexerResult'] = 'Joomla\Component\Finder\Administrator\Indexer\Result';
        $aliases['JArchive'] = 'Joomla\Archive\Archive';
        $aliases['JArrayHelper'] = 'Joomla\Utilities\ArrayHelper';
        $aliases['JDatabase'] = 'Joomla\Database\DatabaseDriver';
        $aliases['JString'] = 'Joomla\String\StringHelper';
        $aliases['JToolbarHelper'] = 'Joomla\CMS\Toolbar\ToolbarHelper';
        $classes['FinderIndexer'] = JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/indexer.php';
        $classes['FinderIndexerAdapter'] = JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';
        $classes['FinderIndexerHelper'] = JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/helper.php';
        $classes['FinderIndexerResult'] = JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/result.php';
    }

    // class aliases for Joomla < 3.9
    if (version_compare(JVERSION, '3.9', '<')) {
        $aliases['JFile'] = 'Joomla\CMS\Filesystem\File';
        $aliases['JFolder'] = 'Joomla\CMS\Filesystem\Folder';
        $aliases['JPath'] = 'Joomla\CMS\Filesystem\Path';
    }

    // class aliases for Joomla < 3.8
    if (version_compare(JVERSION, '3.8', '<')) {
        $aliases['JAccess'] = 'Joomla\CMS\Access\Access';
        $aliases['JApplicationHelper'] = 'Joomla\CMS\Application\ApplicationHelper';
        $aliases['JCaptcha'] = 'Joomla\CMS\Captcha\Captcha';
        $aliases['JComponentHelper'] = 'Joomla\CMS\Component\ComponentHelper';
        $aliases['JControllerLegacy'] = 'Joomla\CMS\MVC\Controller\BaseController';
        $aliases['JDate'] = 'Joomla\CMS\Date\Date';
        $aliases['JDocument'] = 'Joomla\CMS\Document\HtmlDocument';
        $aliases['JFactory'] = 'Joomla\CMS\Factory';
        $aliases['JFeedItem'] = '\Joomla\CMS\Document\Feed\FeedItem';
        $aliases['JFilterInput'] = 'Joomla\CMS\Filter\InputFilter';
        $aliases['JFilterOutput'] = 'Joomla\CMS\Filter\OutputFilter';
        $aliases['JForm'] = 'Joomla\CMS\Form\Form';
        $aliases['JFormField'] = 'Joomla\CMS\Form\FormField';
        $aliases['JHelperTags'] = 'Joomla\CMS\Helper\TagsHelper';
        $aliases['JHtml'] = 'Joomla\CMS\HTML\HTMLHelper';
        $aliases['JInstaller'] = 'Joomla\CMS\Installer\Installer';
        $aliases['JMenu'] = 'Joomla\CMS\Menu\AbstractMenu';
        $aliases['JModuleHelper'] = 'Joomla\CMS\Helper\ModuleHelper';
        $aliases['JPlugin'] = 'Joomla\CMS\Plugin\CMSPlugin';
        $aliases['JPluginHelper'] = 'Joomla\CMS\Plugin\PluginHelper';
        $aliases['JRoute'] = 'Joomla\CMS\Router\Route';
        $aliases['JRouter'] = 'Joomla\CMS\Router\Router';
        $aliases['JSession'] = 'Joomla\CMS\Session\Session';
        $aliases['JTable'] = 'Joomla\CMS\Table\Table';
        $aliases['JTableNested'] = 'Joomla\CMS\Table\Nested';
        $aliases['JText'] = 'Joomla\CMS\Language\Text';
        $aliases['JUri'] = 'Joomla\CMS\Uri\Uri';
        $aliases['JPagination'] = 'Joomla\CMS\Pagination\Pagination';
        $aliases['JVersion'] = 'Joomla\CMS\Version';
        $aliases['JToolbar'] = 'Joomla\CMS\Toolbar\Toolbar';
        $aliases['JViewLegacy'] = 'Joomla\CMS\MVC\View\HtmlView';
    }

    // register classes
    foreach ($classes as $class => $path) {
        JLoader::register($class, $path);
    }

    // register class aliases
    foreach ($aliases as $original => $alias) {
        JLoader::registerAlias($alias, $original);
    }

}
