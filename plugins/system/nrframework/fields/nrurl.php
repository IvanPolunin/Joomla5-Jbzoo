<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldNRURL extends NRFormField
{
    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    function getInput()
    {   
        $url    = $this->get("url", "#");
        $target = $this->get("target", "_blank");
        $text   = $this->get("text");
        $class  = $this->get("class");
        $icon   = $this->get("icon", null);

        $url = str_replace("{{base}}", URI::base(), $url);
        $url = str_replace("{{root}}", URI::root(), $url);

        $html[] = '<a class="nrurl ' . $class . '" href="' . $url . '" target="' . $target . '">';

        if ($icon)
        {
            $html[] = '<span class="icon-'.$icon.'"></span>';
        }

        $html[] = $this->prepareText($text);
        $html[] = '</a>';

        // Add CSS to the page
        $run = false;
        if (!$run)
        {
            Factory::getDocument()->addStyleDeclaration('
                .nrurl.disabled {
                    pointer-events: none;
                }
            ');

            $run = true;
        }

        return implode(" ", $html);
    }
}