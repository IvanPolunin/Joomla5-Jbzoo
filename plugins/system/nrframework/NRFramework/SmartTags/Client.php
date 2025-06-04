<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

use NRFramework\WebClient;

class Client extends SmartTag
{
    /**
     * Returns the type of the device of the user. 
     * 
     * @return  string  Possible values: desktop, mobile, tablet
     */
    public function getDevice()
    {
        return WebClient::getDeviceType();
    }

    /**
     * Returns the operating system of the user. 
     * 
     * @return  string  Possible values: windows, windows phone, iphone, ipad, ipod, mac, blackberry, android, android tablet, linux
     */
    public function getOS()
    {
        return WebClient::getOS();
    }

    /**
     * Returns the name of the browser of the user.
     * 
     * @return  string  Possible values: ie, firefox, chrome, safari, opera, edge
     */
    public function getBrowser()
    {
        return WebClient::getBrowser()['name'];
    }
    
    /**
     * Returns the user agent string of the user.
     * 
     * @return  string
     */
    public function getUserAgent()
    {
        return WebClient::getClient()->userAgent;
    }

    /**
     * Returns the 8-character hexadecimal ID representing the visitor's unique ID as stored in the nrid cookie in the visitor’s browser.
     *
     * @return string  Example: 03bc431d0d605ce4
     */
    public function getID()
    {
        return \NRFramework\VisitorToken::getInstance()->get();
    }
}