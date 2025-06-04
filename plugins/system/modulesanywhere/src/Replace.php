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

namespace RegularLabs\Plugin\System\ModulesAnywhere;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\LayoutHelper as JLayout;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\CMS\WebAsset\Exception\InvalidActionException;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\Input as RL_Input;
use RegularLabs\Library\ObjectHelper as RL_Object;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;
use RegularLabs\Plugin\System\AdvancedModules\Helper as AdvancedModulesHelper;
use RegularLabs\Plugin\System\ModulesAnywhere\Form\Field\ChromeStyleField;

class Replace
{
    static $message       = '';
    static $protect_end   = '<!-- END: MA_PROTECT -->';
    static $protect_start = '<!-- START: MA_PROTECT -->';

    public static function processModules(
        &$string,
        $area = 'article',
        $context = '',
        $article = null
    )
    {

        // Check if tags are in the text snippet used for the search component
        if (str_starts_with($context, 'com_search.'))
        {
            $limit = explode('.', $context, 2);
            $limit = (int) array_pop($limit);

            $string_check = substr($string, 0, $limit);

            if ( ! RL_String::contains($string_check, Params::getTags(true)))
            {
                return;
            }
        }


        if ( ! RL_String::contains($string, Params::getTags(true)))
        {
            return;
        }

        if ( ! RL_Document::isFeed())
        {
            JPluginHelper::importPlugin('content');
        }

        self::replace($string, $area);
    }

    public static function replaceTags(&$string, $area = 'article', $context = '')
    {
        if ( ! is_string($string) || $string == '')
        {
            return false;
        }

        $params = Params::get();

        if ( ! RL_String::contains($string, Params::getTags(true)))
        {
            return false;
        }

        // allow in component?
        if (RL_Protect::isRestrictedComponent($params->disabled_components ?? [], $area))
        {

            Protect::_($string);

            self::removeAll($string, $area);

            RL_Protect::unprotect($string);

            return true;
        }

        Protect::_($string);

        // COMPONENT
        if (RL_Document::isFeed())
        {
            $string = RL_RegEx::replace('(<item[^>]*>)', '\1<!-- START: MODA_COMPONENT -->', $string);
            $string = str_replace('</item>', '<!-- END: MODA_COMPONENT --></item>', $string);
        }

        if ( ! str_contains($string, '<!-- START: MODA_COMPONENT -->'))
        {
            Area::tag($string, 'component');
        }

        self::$message = '';

        $components = Area::get($string, 'component');

        foreach ($components as $component)
        {
            if ( ! str_contains($string, $component[0]))
            {
                continue;
            }

            self::processModules($component[1], 'components');
            $string = str_replace($component[0], $component[1], $string);
        }

        // EVERYWHERE
        self::processModules($string, 'other');

        RL_Protect::unprotect($string);

        return true;
    }

    private static function addFrontendEditing(&$module, &$html)
    {
    }

    private static function applyAssignments(&$module)
    {
        if (empty($module))
        {
            return;
        }

        self::setModulePublishState($module);

        if (empty($module->published))
        {
            $module = null;
        }
    }

    private static function convertLoadModuleSyntax($string)
    {
        [$type, $title, $style] = explode(',', $string . ',,');

        $id = self::getFirstModuleIdByType($type, $title);

        if ($style)
        {
            return $id;
        }

        return 'id="' . $id . '" style="' . trim($style) . '"';
    }

    private static function convertLoadPositionSyntax($string)
    {
        [$id, $style] = explode(',', $string . ',');

        if ($style)
        {
            return trim($id);
        }

        return 'id="' . trim($id) . '" style="' . trim($style) . '"';
    }

    private static function getFirstModuleIdByType($type, $title = '')
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__modules')
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('module') . ' = ' . $db->quote(trim($type)));

        if ($title)
        {
            $query->where($db->quoteName('title') . ' = ' . $db->quote(trim($title)));
        }

        $db->setQuery($query);

        return $db->loadResult();
    }

    private static function getModuleChromeStyle(&$settings, &$overrides)
    {
        $style = isset($overrides['style'])
            ? $overrides['style']
            : (($settings->style ?? '') ?: Params::get()->style);

        if ( ! isset($overrides['style'])
            && in_array($style, ['', 0, 'none', 'System-none'])
            && isset($overrides['showtitle'])
            && $overrides['showtitle']
        )
        {
            return 'System-html5';
        }

        unset($overrides['style']);

        if (empty($style))
        {
            return $style;
        }

        $style = str_replace(':', '-', $style);
        $pos   = strrpos($style, '-');

        if ($pos > 0)
        {
            return $style;
        }

        $styles = ChromeStyleField::getTemplateStyles();

        foreach ($styles as $template => $template_styles)
        {
            if ( ! in_array($style, $template_styles))
            {
                continue;
            }

            return ucfirst($template) . '-' . $style;
        }

        return $style;
    }

    private static function getModuleFromDatabase($id, $ignores = [])
    {
        $params = Params::get();

        $ignore_access      = $ignores['ignore_access'] ?? $params->ignore_access;
        $ignore_state       = $ignores['ignore_state'] ?? $params->ignore_state;
        $ignore_assignments = $ignores['ignore_assignments'] ?? $params->ignore_assignments;

        if (RL_RegEx::match('^[0-9]+[\:\#]', $id))
        {
            $id = (int) $id;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('m.*')
            ->from('#__modules AS m')
            ->where('m.client_id = 0')
            ->where(is_numeric($id)
                ? 'm.id = ' . (int) $id
                : 'm.title = ' . $db->quote(RL_String::html_entity_decoder($id))
            );

        if ( ! $ignore_access)
        {
            $user   = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
            $levels = $user->getAuthorisedViewLevels();
            $query->where('m.access IN (' . implode(',', $levels) . ')');
        }

        if ( ! $ignore_state)
        {
            $query->where('m.published = 1')
                ->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id')
                ->where('e.enabled = 1');
        }

        if ( ! $ignore_assignments)
        {
            $date     = JFactory::getDate();
            $now      = $date->toSql();
            $nullDate = $db->getNullDate();
            $query->where('(m.publish_up IS NULL OR m.publish_up = ' . $db->quote($nullDate) . ' OR m.publish_up <= ' . $db->quote($now) . ')')
                ->where('(m.publish_down IS NULL OR m.publish_down = ' . $db->quote($nullDate) . ' OR m.publish_down >= ' . $db->quote($now) . ')');

            if (RL_Document::isClient('site') && JFactory::getApplication()->getLanguageFilter())
            {
                $query->where('m.language IN (' . $db->quote(JFactory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
            }
        }

        $query->order('m.ordering');
        $db->setQuery($query);

        return $db->loadObject();
    }

    private static function getPropertiesArray($object)
    {
        // get an array of lowercase property names with original case as value
        $properties = [];

        foreach ($object as $key => $value)
        {
            $properties[strtolower($key)] = $key;
        }

        return $properties;
    }

    private static function getSettings(&$module, $overrides = [])
    {
        $settings = (object) [];

        if ( ! empty($module->params))
        {
            $settings = json_decode($module->params ?: '{}');
        }

        if ( ! empty($overrides))
        {
            self::setSettingsFromOverrides($overrides, $settings, $module);
        }

        return $settings;
    }

    private static function getTagValues($data)
    {
        $string = RL_String::html_entity_decoder($data['id']);

        $known_boolean_keys = [
            'ignore_access', 'ignore_state', 'ignore_assignments', 'ignore_caching',
            'showtitle',
        ];

        // Get the values from the tag
        $set = RL_PluginTag::getAttributesFromString($string, 'id', $known_boolean_keys);

        $key_aliases = [
            'id'      => ['ids', 'module', 'position', 'title', 'alias'],
            'style'   => ['module_style', 'html_style', 'chrome'],
            'fixhtml' => ['fix_html', 'html_fix', 'htmlfix'],
        ];

        $set = RL_Object::replaceKeys($set, $key_aliases);

        return $set;
    }

    private static function processMatch(&$string, &$data, $area = 'article')
    {
        $params = Params::get();

        if ( ! empty(self::$message))
        {
            $html = '';

            if ($params->place_comments)
            {
                $html = Protect::getMessageCommentTag(self::$message);
            }

            $string = str_replace($data[0], $html, $string);

            return true;
        }

        $data['type'] = ! empty($data['type_core']) ? trim($data['type_core']) : trim($data['type']);
        $type         = $data['type'];

        if ( ! empty($data['type_core']))
        {
            switch ($type)
            {
                // Convert core loadmodule tag
                case 'loadmodule':
                    $data['id'] = self::convertLoadModuleSyntax($data['id_core']);
                    $type       = $params->tag_module;
                    break;

                // Convert core loadmoduleid tag
                case 'loadmoduleid':
                    $data['id'] = $data['id_core'];
                    $type       = $params->tag_module;
                    break;

                // Convert core loadposition tag
                case 'loadposition':
                    $data['id'] = self::convertLoadPositionSyntax($data['id_core']);
                    $type       = $params->tag_pos;
                    break;

                default:
                    break;
            }

            unset($data['id_core']);
            unset($data['type_core']);
        }

        $tag = self::getTagValues($data);

        $id = trim($tag->id);

        $ignores   = [];
        $overrides = [];

        foreach ($tag as $key => $val)
        {
            switch ($key)
            {
                case 'id':
                case 'fixhtml':
                    break;

                case 'ignore_access':
                case 'ignore_state':
                case 'ignore_assignments':
                case 'ignore_caching':
                    $ignores[$key] = $val;
                    break;

                case 'style':
                case 'showtitle':
                    $overrides[$key] = $val;
                    break;

                default:
                    break;
            }
        }

        if ($type == $params->tag_module)
        {
            // module
            $html = self::processModule($id, $ignores, $overrides, $area);

            if ($html == 'MA_IGNORE')
            {
                return false;
            }
        }
        else
        {
            // module position
            $html = self::processPosition($id, $overrides['style'] ?? $params->style);
        }

        [$pre, $post] = RL_Html::cleanSurroundingTags(
            [$data['pre'], $data['post']],
            ['p', 'span']
        );

        $html = $pre . $html . $post;

        if (self::shouldFixHtml($tag, $pre, $post))
        {
            $html = RL_Html::fix($html);
        }

        if ($params->place_comments)
        {
            $html = Protect::wrapInCommentTags($html);
        }

        $string = str_replace($data[0], $html, $string);
        unset($data);

        return $id;
    }

    private static function processModule($id, $ignores = [], $overrides = [], $area = 'article')
    {
        $params = Params::get();

        $ignore_assignments = $ignores['ignore_assignments'] ?? $params->ignore_assignments;
        $ignore_caching     = $ignores['ignore_caching'] ?? $params->ignore_caching;

        $module = self::getModuleFromDatabase($id, $ignores);

        if ( ! $ignore_assignments)
        {
            self::applyAssignments($module);
        }

        if (empty($module))
        {
            if ($params->place_comments)
            {
                return Protect::getMessageCommentTag(JText::_('MA_OUTPUT_REMOVED_NOT_PUBLISHED'));
            }

            return '';
        }

        //determine if this is a custom module
        $module->user = (str_starts_with($module->module, 'mod_')) ? 0 : 1;

        $settings        = self::getSettings($module, $overrides);
        $settings->style = self::getModuleChromeStyle($settings, $overrides);

        $user   = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        $levels = $user->getAuthorisedViewLevels();

        if (isset($module->access) && ! in_array($module->access, $levels))
        {
            if ($params->place_comments)
            {
                return Protect::getMessageCommentTag(JText::_('MA_OUTPUT_REMOVED_ACCESS'));
            }

            return '';
        }

        $module->params = json_encode($settings);
        $render_params  = ['style' => $settings->style, 'name' => ''];

        $document = clone JFactory::getDocument();
        $renderer = $document->setType('html')->loadRenderer('module');

        try
        {
            try
            {
                $html = $renderer->render($module, $render_params);
            }
            catch (InvalidActionException $e)
            {
                $settings->cache = false;
                $module->params  = json_encode($settings);
                $html            = $renderer->render($module, $render_params);
            }
        }
        catch (InvalidActionException $e)
        {
            self::$message = JText::_('MA_OUTPUT_REMOVED_ASSETMANAGER_LOCKED');
            $html = '';
        }


        // don't return html on article level when caching is set
        if (
            $area == 'article'
            && ! $ignore_caching
            && (
                (isset($settings->cache) && ! $settings->cache)
                || (isset($settings->owncache) && ! $settings->owncache) // for stupid modules like RAXO that mess about with default params
            )
        )
        {
            return 'MA_IGNORE';
        }

        return $html;
    }

    private static function processPosition($position, $chrome = 'none')
    {
        $params = Params::get();

        $document = clone JFactory::getDocument();
        $renderer = $document->setType('html')->loadRenderer('module');

        $html = [];

        foreach (JModuleHelper::getModules($position) as $module)
        {
            $module_html = $renderer->render($module, ['style' => $chrome]);


            $html[] = $module_html;
        }

        return implode('', $html);
    }

    private static function removeAll(&$string, $area = 'article')
    {
        self::$message = JText::_('MA_OUTPUT_REMOVED_NOT_ENABLED');
        self::processModules($string, $area);
    }

    private static function replace(&$full_string, $area = 'article')
    {
        [$start_tags, $end_tags] = Params::getTags();

        [$pre_string, $string, $post_string] = RL_Html::getContentContainingSearches(
            $full_string,
            $start_tags,
            $end_tags
        );

        if ($string == '' || ! RL_String::contains($string, Params::getTags(true)))
        {
            return;
        }

        $regex = Params::getRegex();

        if ( ! RL_RegEx::match($regex, $string))
        {
            return;
        }

        $matches   = [];
        $break     = 0;
        $max_loops = 5;

        while (
            $break++ < $max_loops
            && RL_String::contains($string, Params::getTags(true))
            && RL_RegEx::matchAll($regex, $string, $matches)
        )
        {
            self::replaceMatches($string, $matches, $area);
            $break++;
        }

        $full_string = $pre_string . $string . $post_string;
    }

    private static function replaceMatches(&$string, $matches, $area = 'article')
    {
        $protects = [];

        foreach ($matches as $match)
        {
            if ( ! str_contains($string, $match[0]))
            {
                continue;
            }

            if (self::processMatch($string, $match, $area))
            {
                continue;
            }

            $protected  = self::$protect_start . base64_encode($match[0]) . self::$protect_end;
            $string     = str_replace($match[0], $protected, $string);
            $protects[] = [$match[0], $protected];
        }

        foreach ($protects as $protect)
        {
            if ( ! str_contains($string, $protect[1]))
            {
                continue;
            }

            $string = str_replace($protect[1], $protect[0], $string);
        }
    }

    private static function setModulePublishState(&$module)
    {
        if (empty($module->id))
        {
            return;
        }

        $module->published = true;

        // for Advanced Module Manager
        if (class_exists('RegularLabs\Plugin\System\AdvancedModules\Helper'))
        {
            $module->use_amm_cache = false;
            $modules               = [$module->id => $module];
            AdvancedModulesHelper::prepareModuleList($modules);
            $module = array_shift($modules);

            return;
        }

        // for core Joomla
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('mm.moduleid')
            ->from('#__modules_menu AS mm')
            ->where('mm.moduleid = ' . (int) $module->id)
            ->where('(mm.menuid = ' . RL_Input::getInt('Itemid') . ' OR mm.menuid <= 0)');
        $db->setQuery($query);
        $result = $db->loadResult();

        $module->published = ! empty($result);
    }

    private static function setSettingsFromOverrides($overrides, &$settings, &$module)
    {
        $module_props   = self::getPropertiesArray($module);
        $settings_props = self::getPropertiesArray($settings);

        // override module parameters
        foreach ($overrides as $key => $value)
        {
            // Key is found in main module attributes
            if (isset($module_props[$key]))
            {
                $module->{$module_props[$key]} = $value;
                continue;
            }

            // Key is found in extra params (Advanced Module Manager)
            if (class_exists('RegularLabs\Plugin\System\AdvancedModules\Helper'))
            {
                AdvancedModulesHelper::setExtraParams($modules);

                if (isset($module->extra->{$key}))
                {
                    $module->extra->{$key} = $value;
                    continue;
                }
            }

            // Else just add to the $settings object

            // Value is a json formatted array
            if ( ! empty($value)
                && is_string($value)
                && $value[0] == '['
                && $value[strlen($value) - 1] == ']'
            )
            {
                $value            = json_decode('{"val":' . $value . '}');
                $settings->{$key} = $value->val;
                continue;
            }

            // Value is not found in the module params
            if ( ! isset($settings_props[$key])
                || ! isset($settings->{$settings_props[$key]})
            )
            {
                $settings->{$key} = $value;
                continue;
            }

            // Value should be an array
            if (is_array($settings->{$settings_props[$key]}))
            {
                $settings->{$settings_props[$key]} = explode(',', $value);
                continue;
            }

            $settings->{$settings_props[$key]} = $value;
        }
    }

    private static function shouldFixHtml($tag, $pre, $post)
    {
        $page_type = RL_Document::get()->getType();

        if ($page_type == 'raw')
        {
            return false;
        }

        if (isset($tag->fixhtml))
        {
            return $tag->fixhtml;
        }

        $params = Params::get();

        if ( ! $params->fix_html)
        {
            return false;
        }

        $pre  = trim($pre);
        $post = trim($post);

        if (empty($pre) && empty($post))
        {
            return false;
        }

        // Ignore if pre/post is a surrounding div
        [$pre, $post] = RL_Html::cleanSurroundingTags(
            [$pre, $post],
            ['div']
        );

        if (empty($pre) && empty($post))
        {
            return false;
        }

        return true;
    }
}
