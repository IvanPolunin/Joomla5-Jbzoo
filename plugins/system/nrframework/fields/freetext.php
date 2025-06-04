<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldNR_Freetext extends NRFormField
{
    /**
     * The field type.
     *
     * @var         string
     */
    public $type = 'freetext';

    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    protected function getInput()
    {   
        $file  = $this->get("file", false);
        $text  = $this->get("text", false);
        $label = $this->get("label", false);
        $description = $this->get("description", false);

        if (!$label)
        {
            $html[] = '</div><div class="freetext '.$this->get("class").'">';
        }

        if ($file)
        {
            $html[] = $this->renderContent($this->get("file"), $this->get("path"), $this);        
        }

        if ($text)
        {
            $html[] = $this->prepareText($text);

            if (!defined('nrJ4') && $description)
            {
                $html[] = '<p class="description">' . Text::_($description) . '</p>';
            }
        }

        return implode(" ", $html);
    }

    /**
     *  Render PHP file with data
     *
     *  @param   string  $file         The file name
     *  @param   string  $path         The pathname
     *  @param   mixed   $displayData  The data object passed to template file
     *
     *  @return  string                HTML rendered
     */
    private function renderContent($file, $path, $displayData = null) 
    {
        $layout = new FileLayout($file, JPATH_SITE . $path, array('debug' => 0));
        return $layout->render($displayData);
    }

}