<?php
// Legacy Joomla class aliases/shims for JBZoo on Joomla 6+

$jbzooLib = defined('JPATH_LIBRARIES')
    ? JPATH_LIBRARIES
    : dirname(__DIR__, 3) . '/libraries';

/**
 * @param string $class
 * @param string $file
 * @return bool
 */
function jbzoo_require_class(string $class, string $file): bool
{
    if (!class_exists($class) && is_file($file)) {
        require_once $file;
    } 

    return class_exists($class);
}

// Core class aliases
if (!class_exists('JFactory')) {
    if (jbzoo_require_class('Joomla\\CMS\\Factory', $jbzooLib . '/src/Factory.php')) {
        class_alias('Joomla\\CMS\\Factory', 'JFactory');
    }
}
if (!class_exists('JFile')) {
    if (jbzoo_require_class('Joomla\\CMS\\Filesystem\\File', $jbzooLib . '/src/Filesystem/File.php')) {
        class_alias('Joomla\\CMS\\Filesystem\\File', 'JFile');
    }
}
if (!class_exists('JFolder')) {
    if (jbzoo_require_class('Joomla\\CMS\\Filesystem\\Folder', $jbzooLib . '/src/Filesystem/Folder.php')) {
        class_alias('Joomla\\CMS\\Filesystem\\Folder', 'JFolder');
    }
}
if (!class_exists('JPath')) {
    if (jbzoo_require_class('Joomla\\CMS\\Filesystem\\Path', $jbzooLib . '/src/Filesystem/Path.php')) {
        class_alias('Joomla\\CMS\\Filesystem\\Path', 'JPath');
    }
}
if (!class_exists('JUri')) {
    if (jbzoo_require_class('Joomla\\CMS\\Uri\\Uri', $jbzooLib . '/src/Uri/Uri.php')) {
        class_alias('Joomla\\CMS\\Uri\\Uri', 'JUri');
    }
}
if (!class_exists('JText')) {
    if (jbzoo_require_class('Joomla\\CMS\\Language\\Text', $jbzooLib . '/src/Language/Text.php')) {
        class_alias('Joomla\\CMS\\Language\\Text', 'JText');
    }
}
if (!class_exists('Text')) {
    if (class_exists('Joomla\\CMS\\Language\\Text')) {
        class_alias('Joomla\\CMS\\Language\\Text', 'Text');
    }
}
if (!class_exists('JString')) {
    $stringHelper = $jbzooLib . '/vendor/joomla/string/src/StringHelper.php';
    if (jbzoo_require_class('Joomla\\String\\StringHelper', $stringHelper)) {
        class_alias('Joomla\\String\\StringHelper', 'JString');
    }
}
if (!class_exists('JRegistry') && class_exists(\Joomla\Registry\Registry::class)) {
    class_alias(\Joomla\Registry\Registry::class, 'JRegistry');
}
if (!class_exists('JDate') && class_exists(\Joomla\CMS\Date\Date::class)) {
    class_alias(\Joomla\CMS\Date\Date::class, 'JDate');
}
if (!class_exists('JUser') && class_exists(\Joomla\CMS\User\User::class)) {
    class_alias(\Joomla\CMS\User\User::class, 'JUser');
}
if (!class_exists('JSession') && class_exists(\Joomla\CMS\Session\Session::class)) {
    class_alias(\Joomla\CMS\Session\Session::class, 'JSession');
}
if (!class_exists('JRoute') && class_exists(\Joomla\CMS\Router\Route::class)) {
    class_alias(\Joomla\CMS\Router\Route::class, 'JRoute');
}
if (!class_exists('JHtml') && class_exists(\Joomla\CMS\HTML\HTMLHelper::class)) {
    class_alias(\Joomla\CMS\HTML\HTMLHelper::class, 'JHtml');
}
if (!class_exists('JComponentHelper') && class_exists(\Joomla\CMS\Component\ComponentHelper::class)) {
    class_alias(\Joomla\CMS\Component\ComponentHelper::class, 'JComponentHelper');
}
if (!class_exists('JModuleHelper') && class_exists(\Joomla\CMS\Helper\ModuleHelper::class)) {
    class_alias(\Joomla\CMS\Helper\ModuleHelper::class, 'JModuleHelper');
}
if (!class_exists('JPluginHelper') && class_exists(\Joomla\CMS\Plugin\PluginHelper::class)) {
    class_alias(\Joomla\CMS\Plugin\PluginHelper::class, 'JPluginHelper');
}
if (!class_exists('JToolbar') && class_exists(\Joomla\CMS\Toolbar\Toolbar::class)) {
    class_alias(\Joomla\CMS\Toolbar\Toolbar::class, 'JToolbar');
}
if (!class_exists('JToolbarHelper') && class_exists(\Joomla\CMS\Toolbar\ToolbarHelper::class)) {
    class_alias(\Joomla\CMS\Toolbar\ToolbarHelper::class, 'JToolbarHelper');
}
if (!class_exists('JTable') && class_exists(\Joomla\CMS\Table\Table::class)) {
    class_alias(\Joomla\CMS\Table\Table::class, 'JTable');
}
if (!class_exists('JTableNested') && class_exists(\Joomla\CMS\Table\Nested::class)) {
    class_alias(\Joomla\CMS\Table\Nested::class, 'JTableNested');
}
if (!class_exists('JPagination') && class_exists(\Joomla\CMS\Pagination\Pagination::class)) {
    class_alias(\Joomla\CMS\Pagination\Pagination::class, 'JPagination');
}
if (!class_exists('JInput') && class_exists(\Joomla\CMS\Input\Input::class)) {
    class_alias(\Joomla\CMS\Input\Input::class, 'JInput');
}
if (!class_exists('JDocument') && class_exists(\Joomla\CMS\Document\Document::class)) {
    class_alias(\Joomla\CMS\Document\Document::class, 'JDocument');
}
if (!class_exists('JDatabase') && class_exists(\Joomla\Database\DatabaseDriver::class)) {
    class_alias(\Joomla\Database\DatabaseDriver::class, 'JDatabase');
}
if (!class_exists('JArrayHelper')) {
    if (class_exists(\Joomla\CMS\Utility\ArrayHelper::class)) {
        class_alias(\Joomla\CMS\Utility\ArrayHelper::class, 'JArrayHelper');
    } elseif (class_exists(\Joomla\Utilities\ArrayHelper::class)) {
        class_alias(\Joomla\Utilities\ArrayHelper::class, 'JArrayHelper');
    }
}
if (!class_exists('JAccess') && class_exists(\Joomla\CMS\Access\Access::class)) {
    class_alias(\Joomla\CMS\Access\Access::class, 'JAccess');
}
if (!class_exists('JFilterInput') && class_exists(\Joomla\CMS\Filter\InputFilter::class)) {
    class_alias(\Joomla\CMS\Filter\InputFilter::class, 'JFilterInput');
}
if (!class_exists('JFilterOutput') && class_exists(\Joomla\CMS\Filter\OutputFilter::class)) {
    class_alias(\Joomla\CMS\Filter\OutputFilter::class, 'JFilterOutput');
}
if (!class_exists('JLanguage') && class_exists(\Joomla\CMS\Language\Language::class)) {
    class_alias(\Joomla\CMS\Language\Language::class, 'JLanguage');
}
if (!class_exists('JMail') && class_exists(\Joomla\CMS\Mail\Mail::class)) {
    class_alias(\Joomla\CMS\Mail\Mail::class, 'JMail');
}
if (!class_exists('JLog') && class_exists(\Joomla\CMS\Log\Log::class)) {
    class_alias(\Joomla\CMS\Log\Log::class, 'JLog');
}
if (!class_exists('JInstaller') && class_exists(\Joomla\CMS\Installer\Installer::class)) {
    class_alias(\Joomla\CMS\Installer\Installer::class, 'JInstaller');
}
if (!class_exists('JArchive') && class_exists(\Joomla\CMS\Archive\Archive::class)) {
    class_alias(\Joomla\CMS\Archive\Archive::class, 'JArchive');
}
if (!class_exists('JCaptcha') && class_exists(\Joomla\CMS\Captcha\Captcha::class)) {
    class_alias(\Joomla\CMS\Captcha\Captcha::class, 'JCaptcha');
}
if (!class_exists('JRouter') && class_exists(\Joomla\CMS\Router\Router::class)) {
    class_alias(\Joomla\CMS\Router\Router::class, 'JRouter');
}
if (!class_exists('JComponentRouterBase') && class_exists(\Joomla\CMS\Component\Router\RouterBase::class)) {
    class_alias(\Joomla\CMS\Component\Router\RouterBase::class, 'JComponentRouterBase');
}
if (!class_exists('JComponentRouterView') && class_exists(\Joomla\CMS\Component\Router\RouterView::class)) {
    class_alias(\Joomla\CMS\Component\Router\RouterView::class, 'JComponentRouterView');
}
if (!class_exists('JComponentRouterRulesMenu') && class_exists(\Joomla\CMS\Component\Router\Rules\MenuRules::class)) {
    class_alias(\Joomla\CMS\Component\Router\Rules\MenuRules::class, 'JComponentRouterRulesMenu');
}
if (!class_exists('JComponentRouterRulesStandard') && class_exists(\Joomla\CMS\Component\Router\Rules\StandardRules::class)) {
    class_alias(\Joomla\CMS\Component\Router\Rules\StandardRules::class, 'JComponentRouterRulesStandard');
}
if (!class_exists('JComponentRouterRulesNomenu') && class_exists(\Joomla\CMS\Component\Router\Rules\NomenuRules::class)) {
    class_alias(\Joomla\CMS\Component\Router\Rules\NomenuRules::class, 'JComponentRouterRulesNomenu');
}
if (!class_exists('JViewLegacy') && class_exists(\Joomla\CMS\MVC\View\HtmlView::class)) {
    class_alias(\Joomla\CMS\MVC\View\HtmlView::class, 'JViewLegacy');
}
if (!class_exists('JControllerLegacy') && class_exists(\Joomla\CMS\MVC\Controller\BaseController::class)) {
    class_alias(\Joomla\CMS\MVC\Controller\BaseController::class, 'JControllerLegacy');
}
if (!class_exists('JForm') && class_exists(\Joomla\CMS\Form\Form::class)) {
    class_alias(\Joomla\CMS\Form\Form::class, 'JForm');
}
if (!class_exists('JFormField') && class_exists(\Joomla\CMS\Form\FormField::class)) {
    class_alias(\Joomla\CMS\Form\FormField::class, 'JFormField');
}
if (!class_exists('JFormHelper') && class_exists(\Joomla\CMS\Form\FormHelper::class)) {
    class_alias(\Joomla\CMS\Form\FormHelper::class, 'JFormHelper');
}
if (!class_exists('JHtmlString')) {
    class JHtmlString
    {
        public static function truncate($text, $length = 0, $noSplit = true, $allowHtml = true)
        {
            return \Joomla\CMS\HTML\HTMLHelper::_('string.truncate', $text, $length, $noSplit, $allowHtml);
        }
    }
}

// Lightweight shims for removed legacy classes
if (!class_exists('JRequest')) {
    class JRequest
    {
        protected static function input(string $hash = 'method')
        {
            $app = Factory::getApplication();
            $input = $app->input;
            switch (strtolower($hash)) {
                case 'get':
                    return $input->get;
                case 'post':
                    return $input->post;
                case 'cookie':
                    return $input->cookie;
                case 'files':
                    return $input->files;
                case 'request':
                case 'method':
                default:
                    return $input;
            }
        }

        public static function getVar($name, $default = null, $hash = 'method', $type = 'none', $mask = 0)
        {
            return self::input($hash)->get($name, $default);
        }

        public static function getInt($name, $default = 0, $hash = 'method')
        {
            return (int) self::input($hash)->get($name, $default);
        }

        public static function getCmd($name, $default = '', $hash = 'method')
        {
            return self::input($hash)->getCmd($name, $default);
        }

        public static function getString($name, $default = '', $hash = 'method')
        {
            return (string) self::input($hash)->get($name, $default);
        }
    }
}

if (!class_exists('JError')) {
    class JError
    {
        public static function raiseWarning($code, $msg)
        {
            trigger_error($msg, E_USER_WARNING);
            return false;
        }

        public static function raiseNotice($code, $msg)
        {
            trigger_error($msg, E_USER_NOTICE);
            return false;
        }

        public static function raiseError($code, $msg)
        {
            throw new \RuntimeException($msg, (int) $code);
        }

        public static function isError($value)
        {
            return $value instanceof \Exception;
        }
    }
}

if (!class_exists('JSubMenuHelper') && class_exists(\Joomla\CMS\HTML\Helpers\Sidebar::class)) {
    class JSubMenuHelper
    {
        public static function addEntry($text, $link = '', $active = false)
        {
            return \Joomla\CMS\HTML\Helpers\Sidebar::addEntry($text, $link, $active);
        }
    }
}

if (!class_exists('JEventDispatcher') && class_exists(\Joomla\Event\Dispatcher::class)) {
    class JEventDispatcher extends \Joomla\Event\Dispatcher
    {
        public static function getInstance()
        {
            static $instance;
            if (!$instance) {
                $instance = new self();
            }
            return $instance;
        }
    }
}
