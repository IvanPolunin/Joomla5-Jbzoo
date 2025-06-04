<?php

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

class plgContentWidgetkit extends CMSPlugin
{
    public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
    {
        if (JPATH_BASE != JPATH_SITE) {
            return;
        }

        if (!$app = @include(JPATH_ADMINISTRATOR . '/components/com_widgetkit/widgetkit-app.php')) {
            return;
        }

        $article->text = $app['shortcode']->parse('widgetkit', $article->text ?: '', function($attrs) use ($app) {
            return $app->renderWidget($attrs);
        });

        return '';
    }
}
