<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

class Page extends SmartTag
{
    /**
     * It returns the title of the page. If the page is behind a Menu Item, its Browser Page Title will be returned if not empty; otherwise, it falls back to the title of the Menu Item. If you want to display the title of a Joomla Article, use the {article.title} Smart Tag instead.
     * 
     * @return  string
     */
    public function getTitle()
    {
        return $this->doc->getTitle();
    }

    /**
     * It returns the page’s meta description. If the page is a Joomla Article and has a meta description set, it will be returned. Otherwise, it falls back to the menu item’s page meta description. 
     * 
     * @return  string
     */
    public function getDesc()
    {
        return $this->doc->getMetaData('description');
    }

    /**
     * Returns the page keywords
     * 
     * @return  string
     * 
     * @deprecated Joomla 4 stopped offering the Meta Keywords option in the Menu Item. Use {article.keywords} instead. Reference: https://github.com/joomla/joomla-cms/issues/36639
     */
    public function getKeywords()
    {
        return $this->doc->getMetaData('keywords');
    }

    /**
     * It returns the language code of the page. For example, if the page’s language is English or Greek, expect "en-gb" and "el-GR" as the returned value, respectively.
     * 
     * @return  string
     */
    public function getLang()
    {
        return $this->doc->getLanguage();
    }

    /**
     * It returns the first part of the language code of the page. For example, if the page’s language is English or Greek, expect "en" and "el" as the returned value, respectively.
     * 
     * @return  string
     */
    public function getLangURL()
    {
        return explode('-',  $this->doc->getLanguage())[0];
    }

    /**
     * Returns the value of the generator meta tag.
     * 
     * @return  string
     */
    public function getGenerator()
    {
        return $this->doc->getGenerator();
    }

    /**
     * Returns the menu item’s Browser Page Title option even if it is empty.
     * 
     * @return  string
     */
    public function getBrowserTitle()
    {
		if (!$menu = $this->app->getMenu()->getActive())
		{
            return '';
        }
        
        return $menu->getParams()->get('page_title');
    }
}