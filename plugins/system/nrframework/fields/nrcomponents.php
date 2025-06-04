<?php
/**
 * @author          Tassos.gr <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class JFormFieldNRComponents extends ListField
{
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), $this->getInstalledComponents());
    }

	/**
	 *  Method to get field parameters
	 *
	 *  @param   string  $val      Field parameter
	 *  @param   string  $default  The default value
	 *
	 *  @return  string
	 */
	public function get($val, $default = '')
	{
		return (isset($this->element[$val]) && (string) $this->element[$val] != '') ? (string) $this->element[$val] : $default;
	}
    
    /**
     *  Creates a list of installed components
     *
     *  @return array
     */
    protected function getInstalledComponents()
    {
        $lang = Factory::getLanguage();
        $db   = Factory::getDbo();

        $components = $db->setQuery(
            $db->getQuery(true)
                ->select('name, element')
                ->from('#__extensions')
                ->where('type = ' . $db->quote('component'))
                ->where('name != ""')
                ->where('element != ""')
                ->where('enabled = 1')
                ->order('element, name')
        )->loadObjectList();

        $comps = array();

        foreach ($components as $component)
        {
            // Make sure we have a valid element
            if (empty($component->element))
            {
                continue;
            }

            // Skip backend-based only components
            if ($this->get('frontend', false))
            {
                $component_folder = JPATH_SITE . '/components/' . $component->element;

                if (!is_dir($component_folder))
                {
                    continue;
                }

                if (!is_dir($component_folder . '/views') && 
                    !is_dir($component_folder . '/View')  && 
                    !is_dir($component_folder . '/view'))
                {
                    continue;
                }
            }

            // Try loading component's system language file in order to display a user friendly component name
            // Runs only if the component's name is not translated already.
            if (strpos($component->name, ' ') === false)
            {   
                $filenames = [
                    $component->element . '.sys',
                    $component->element
                ];

                $paths = [
                    JPATH_ADMINISTRATOR,
                    JPATH_ADMINISTRATOR . '/components/' . $component->element,
                    JPATH_SITE,
                    JPATH_SITE . '/components/' . $component->element
                ];

                foreach ($filenames as $key => $filename)
                {
                    foreach ($paths as $key => $path)
                    {
                        $loaded = $lang->load($filename, $path, null) || $lang->load($filename, $path, $lang->getDefault());

                        if ($loaded)
                        {
                            break 2;
                        }
                    }
                }

                // Translate component's name
                $component->name = Text::_(strtoupper($component->name));
            }

            $comps[strtolower($component->element)] = $component->name;
        }

        asort($comps);

        $options = array();

        foreach ($comps as $key => $name)
        {
            $options[] = HTMLHelper::_('select.option', $key, $name);
        }

        return $options;
    }
}