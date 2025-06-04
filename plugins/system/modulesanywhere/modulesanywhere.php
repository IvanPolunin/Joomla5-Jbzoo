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
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\ModulesAnywhere\Params;
use RegularLabs\Plugin\System\ModulesAnywhere\Protect;
use RegularLabs\Plugin\System\ModulesAnywhere\Replace;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/regularlabs.xml')
    || ! class_exists('RegularLabs\Library\Parameters')
    || ! class_exists('RegularLabs\Library\DownloadKey')
    || ! class_exists('RegularLabs\Library\SystemPlugin')
)
{
    JFactory::getApplication()->getLanguage()->load('plg_system_modulesanywhere', __DIR__);
    JFactory::getApplication()->enqueueMessage(
        JText::sprintf('MA_EXTENSION_CAN_NOT_FUNCTION', JText::_('MODULESANYWHERE'))
        . ' ' . JText::_('MA_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
        'error'
    );

    return;
}

if ( ! RL_Document::isJoomlaVersion(4, 'MODULESANYWHERE'))
{
    RL_Extension::disable('modulesanywhere', 'plugin');

    RL_Document::adminError(
        JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('MODULESANYWHERE'))
    );

    return;
}

if (true)
{
    class PlgSystemModulesAnywhere extends RL_SystemPlugin
    {
        public    $_lang_prefix = 'MA';
        protected $_jversion    = 4;

        /**
         * @param string &$string
         * @param string  $area
         * @param string  $context The context of the content being passed to the plugin.
         * @param mixed   $article An object with a "text" property
         * @param int     $page    Optional page number. Unused. Defaults to zero.
         *
         * @return  void
         */
        public function processArticle(
            &$string,
            $area = 'article',
            $context = '',
            $article = null,
            $page = 0
        )
        {
            Replace::processModules($string, $area, $context, $article);
        }

        /**
         * @param object $module
         * @param array  $params
         */
        protected function handleOnAfterRenderModule(&$module, &$params): void
        {
            if ( ! isset($module->content))
            {
                return;
            }

            Replace::replaceTags($module->content, 'module');
        }

        /**
         * @param string $buffer
         *
         * @return  bool
         */
        protected function changeDocumentBuffer(&$buffer)
        {
            return Replace::replaceTags($buffer, 'component');
        }

        /**
         * @param string $html
         *
         * @return  bool
         */
        protected function changeFinalHtmlOutput(&$html)
        {
            if (RL_Document::isFeed())
            {
                Replace::replaceTags($html);

                return true;
            }

            // only do stuff in body
            [$pre, $body, $post] = RL_Html::getBody($html);
            Replace::replaceTags($body, 'body');
            $html = $pre . $body . $post;

            return true;
        }

        /**
         * @param string $html
         */
        protected function cleanFinalHtmlOutput(&$html)
        {
            RL_Protect::removeAreaTags($html, 'MODA');

            $params = Params::get();

            Protect::unprotectTags($html);

            RL_Protect::removeFromHtmlTagContent($html, Params::getTags(true));
            RL_Protect::removeInlineComments($html, 'Modules Anywhere');

            if ( ! $params->place_comments)
            {
                RL_Protect::removeCommentTags($html, 'Modules Anywhere');
            }
        }
    }
}
