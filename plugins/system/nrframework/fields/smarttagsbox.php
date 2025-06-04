<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use NRFramework\SmartTags;
use Joomla\CMS\Factory;

class JFormFieldSmartTagsBox extends FormField
{
    /**
     * Undocumented variable
     *
     * @var string
     */
    public $input_selector = '.show-smart-tags';

    /**
     *  Disable field label
     *
     *  @return  boolean
     */
    protected function getLabel()
    {
        return false;
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array   An array of options.
     */
    protected function getInput()
    {
        HTMLHelper::_('script', 'plg_system_nrframework/smarttagsbox.js', ['version' => 'auto', 'relative' => true]);
        HTMLHelper::_('stylesheet', 'plg_system_nrframework/smarttagsbox.css', ['version' => 'auto', 'relative' => true]);

        Text::script('NR_SMARTTAGS_NOTFOUND');
        Text::script('NR_SMARTTAGS_SHOW');

        Factory::getDocument()->addScriptOptions('SmartTagsBox', [
            'selector' => $this->input_selector,
            'tags'     => [
                'Joomla' => [
                    '{page.title}'     => Text::_('NR_TAG_PAGETITLE'),
                    '{url}'            => Text::_('NR_TAG_URL'),
                    '{url.path}'       => Text::_('NR_TAG_URLPATH'),
                    '{page.lang}'      => Text::_('NR_TAG_PAGELANG'),
                    '{page.langurl}'   => Text::_('NR_TAG_PAGELANGURL'),
                    '{page.desc}'      => TEXT::_('NR_TAG_PAGEDESC'),
                    '{site.name}'      => TEXT::_('NR_TAG_SITENAME'),
                    '{site.url}'       => Text::_('NR_TAG_SITEURL'),
                    '{site.email}'     => Text::_('NR_TAG_SITEEMAIL'),
                    '{user.id}'        => Text::_('NR_TAG_USERID'),
                    '{user.username}'  => Text::_('NR_USER_USERNAME'),
                    '{user.email}'     => Text::_('NR_TAG_USEREMAIL'),
                    '{user.name}'      => Text::_('NR_TAG_USERNAME'),
                    '{user.firstname}' => Text::_('NR_TAG_USERFIRSTNAME'),
                    '{user.lastname}'  => Text::_('NR_TAG_USERLASTNAME'),
                    '{user.groups}'    => Text::_('NR_TAG_USERGROUPS'),
                    '{user.registerdate}' => Text::_('NR_USER_REGISTRATION_DATE'),
                ],
                Text::_('NR_VISITOR') => [
                    '{client.device}'    => Text::_('NR_TAG_CLIENTDEVICE'),
                    '{ip}'               => Text::_('NR_TAG_IP'),
                    '{client.browser}'   => Text::_('NR_TAG_CLIENTBROWSER'),
                    '{client.os}'        => Text::_('NR_TAG_CLIENTOS'),
                    '{client.useragent}' => Text::_('NR_TAG_CLIENTUSERAGENT'),
                    '{client.id}'        => Text::_('NR_TAG_CLIENTID'),
                    '{geo.country}'      => Text::_('NR_TAG_GEOCOUNTRY'),
                    '{geo.countrycode}'  => Text::_('NR_TAG_GEOCOUNTRYCODE'),
                    '{geo.city}'         => Text::_('NR_TAG_GEOCITY'),
                    '{geo.location}'     => Text::_('NR_TAG_GEOLOCATION'),
                ],
                Text::_('NR_OTHER') => [
                    '{date}'  => Text::_('NR_DATE'),
                    '{time}'  => Text::_('NR_TIME'),
                    '{day}'   => Text::_('NR_TAG_DAY'),
                    '{month}' => Text::_('NR_TAG_MONTH'),
                    '{year}'  => Text::_('NR_TAG_YEAR'),
                    '{referrer}' => Text::_('NR_ASSIGN_REFERRER'),
                    '{randomid}' => Text::_('NR_TAG_RANDOMID'),
                    '{querystring.YOUR_KEY}' => Text::_('NR_QUERY_STRING'),
                    '{language.YOUR_KEY}' => Text::_('NR_LANGUAGE_STRING'),
                    '{post.YOUR_KEY}' => Text::_('NR_POST_DATA'),
                    '{cookie.YOUR_KEY}' => Text::_('NR_COOKIE')
                ]
            ]
        ]);

        // Render box layout
        $layout = new FileLayout('smarttagsbox', JPATH_PLUGINS . '/system/nrframework/layouts');
        return $layout->render();
    }
}