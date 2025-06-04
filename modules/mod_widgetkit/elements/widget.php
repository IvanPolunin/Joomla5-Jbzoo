<?php

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

class JFormFieldWidget extends FormField
{
    protected $type = 'Widget';

    function getInput()
    {
        if (
            !($app = @include JPATH_ADMINISTRATOR . '/components/com_widgetkit/widgetkit-app.php')
        ) {
            return;
        }

        // Check if front end editing.
        if (!$app['admin'] && $app['config']->get('theme.support') !== 'uikit3') {
            return sprintf(
                '<a href="administrator/index.php?option=com_modules&task=module.edit&id=%d" class="btn btn-small">Edit in Backend.</a>',
                intval($_GET['id'])
            );
        }

        HTMLHelper::_('jquery.framework');

        $style = $app['url']->to('assets/css/joomla.css', [], true);
        $script = $app['url']->to('assets/js/joomla.picker.js', [], true);
        $iframe = Route::_('index.php?option=com_widgetkit&tmpl=component&p=/picker', false);
        $select = $app['translator']->trans('Select Widget');
        $current = $app['translator']->trans('Widget: %widget%');

        $document = Factory::getApplication()->getDocument();
        $document->addScript($script, ['version' => 'auto'], ['defer' => true]);
        $document->addScriptOptions('widgetkit', compact('iframe', 'select', 'current'));
        $document->addStylesheet($style);

        $value = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');
        $btnClasses = 'btn btn-small';

        if (version_compare(JVERSION, '4.0', '>')) {
            $btnClasses = 'btn btn-primary';
            HTMLHelper::_('bootstrap.modal');
        }

        return <<<EOT
    <button type="button" class="{$btnClasses} widgetkit-widget">
        <span>{$app['translator']->trans('Select Widget')}</span>
    </button>
    <input type="hidden" name="{$this->name}" value="{$value}">
EOT;
    }
}
