<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

require_once JPATH_PLUGINS . '/system/nrframework/helpers/fieldlist.php';

class JFormFieldNR_Geo extends NRFormFieldList
{
	private $list;

	protected function getInput()
	{
		if ($this->get('detect_visitor_country', false) && empty($this->value) && $countryCode = \NRFramework\Helpers\Geo::getVisitorCountryCode())
		{
			$this->value = $countryCode;
		}

		return parent::getInput();
	}

	protected function getOptions()
	{
		switch ($this->get('geo'))
		{
			case 'continents':
				$this->list = \NRFramework\Continents::getContinentsList();
				$selectLabel = 'NR_SELECT_CONTINENT';
				break;
            default:
				$this->list = \NRFramework\Countries::getCountriesList();
				$selectLabel = 'NR_SELECT_COUNTRY';
				break;
		}

		if ($this->get('use_label_as_value', false))
		{
			$this->list = array_combine($this->list, $this->list);
		}

		$options = array();

		if ($this->get("showselect", 'true') === 'true')
		{
			$options[] = HTMLHelper::_('select.option', "", "- " . Text::_($selectLabel) . " -");
		}

		foreach ($this->list as $key => $value)
		{
			$options[] = HTMLHelper::_('select.option', $key, $value);
		}

		return array_merge(parent::getOptions(), $options);
	}
}