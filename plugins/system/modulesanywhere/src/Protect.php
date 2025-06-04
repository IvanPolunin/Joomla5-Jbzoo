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

use RegularLabs\Library\Protect as RL_Protect;

class Protect
{
    static $name = 'Modules Anywhere';

    public static function _(&$string)
    {
        RL_Protect::protectHtmlCommentTags($string);
        RL_Protect::protectFields($string, Params::getTags(true));
        RL_Protect::protectSourcerer($string);
    }

    /**
     * Get the html comment tag containing a message
     *
     * @param string $message
     *
     * @return string
     */
    public static function getMessageCommentTag($message)
    {
        return RL_Protect::getMessageCommentTag(self::$name, $message);
    }

    public static function protectTags(&$string)
    {
        RL_Protect::protectTags($string, Params::getTags(true));
    }

    public static function unprotectTags(&$string)
    {
        RL_Protect::unprotectTags($string, Params::getTags(true));
    }

    /**
     * Wrap the comment in comment tags
     *
     * @param string $comment
     *
     * @return string
     */
    public static function wrapInCommentTags($comment)
    {
        return RL_Protect::wrapInCommentTags(self::$name, $comment);
    }
}
