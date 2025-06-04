<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class JFormFieldNRText extends TextField
{
    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    public function getInput()
    {   
        // This line added to help us support the K2 Items and Joomla! Articles dropdown listbox array values
        $this->value = is_array($this->value) ? implode(',', $this->value) : $this->value;

        // Adds an extra info label next to input
        $addon  = (string) $this->element['addon'];
        $parent = parent::getInput();

        if (!empty($addon))
        {
            $html[] = '
                <div class="input-append input-group">
                    ' . $parent . '
                    <span class="add-on input-group-append">
                        <span class="input-group-text" style="font-size:inherit;">' . Text::_($addon) . '</span>
                    </span>
                </div>';
        } else
        {
            $html[] = parent::getInput();
        }

        // Adds a link next to input
        $url        = $this->element['url'];
        $text       = $this->element['urltext'];
        $target     = $this->element['urltarget'] ? $this->element['urltarget'] : "_blank";
        $class      = $this->element['urlclass'] ? $this->element['urlclass'] : "";
        $attributes = "";

        // Popup mode
        if ($this->element["urlpopup"])
        {
            $class .= " nrPopup";
            $attributes = 'data-width="600" data-height="600"';
            $this->addPopupScript();
        }

        if ($url && $text)
        {
            $style = !defined('nrJ4') ? ' style="margin-left:10px;"' : '';
            $html[] = '<a ' . $attributes . ' class="' . $class . '"' . $style . ' href="' . $url . '" target="' . $target . '">' . Text::_($text) . '</a>';
        }

        return implode('', $html);
    }

    private function addPopupScript()
    {
        static $run;

        if ($run)
        {
            return;
        }

        $run = true;

        Factory::getDocument()->addScriptDeclaration('
            jQuery(function($) {
                $(".nrPopup").click(function() {
                    url    = $(this).attr("href");
                    width  = $(this).data("width");
                    height = $(this).data("height");

                    window.open(""+url+"", "nrPopup", "width=" + width + ", height=" + height + "");

                    return false;              
                })
            })
        ');
    }
}