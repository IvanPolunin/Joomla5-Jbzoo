<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

class URL extends SmartTag
{
    /**
     * Returns the complete URL of the page, including the query string. Example: https://www.site.com/blog/?category=123
     * 
     * @return  string
     */
    public function getURL()
    {
        return $this->factory->getURI()->toString();
    }

    /**
     * It returns the complete URL of the page, including the query string, but encoded. For instance, if the current URL is https://www.site.com/blog/?category=123 the Smart Tag will return https%3A%2F%2Fwww.site.com%2Fblog%2F%3Fcategory%3D123. This is useful when you want to pass the URL as a parameter in another URL.
     * 
     * @return  string
     */
    public function getEncoded()
    {
        return urlencode($this->factory->getURI()->toString());
    }

    /**
     * Returns the URL of the page without the query string. Example: https://www.site.com/blog/
     * 
     * @return  string
     */
    public function getPath()
    {
        $url = $this->factory->getURI();
        return $url::current();
    }
}