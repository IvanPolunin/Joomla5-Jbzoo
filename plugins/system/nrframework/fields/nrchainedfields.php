<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Layout\FileLayout;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldNRChainedFields extends NRFormField
{
	/**
	 * All CSV choices.
	 * 
	 * @var  array
	 */
	protected $choices = [];

	/**
	 * The separator used in the CSV file.
	 * 
	 * @var  string
	 */
	protected $separator = ',';

	/**
	 * The data source contents.
	 * 
	 * @var  string
	 */
	protected $dataset = '';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$layout = new FileLayout('chainedfields', JPATH_PLUGINS . '/system/nrframework/layouts');

		$data_source = $this->get('data_source', 'custom');

		switch ($data_source)
		{
			case 'custom':
				$this->dataset = $this->get('data_source_custom', '');
				break;

			case 'csv_file':
				$csv_file = $this->get('data_source_csv', '');

				if (!file_exists($csv_file))
				{
					return;
				}
				
				$this->dataset = $csv_file;
				break;
		}

		$this->separator = $this->get('separator', ',');

		$data = [
			'csv' => $this->dataset,
			'data_source' => $data_source,
			'value' => $this->getValue(),
			'data' => $this->getData()
		];

		return $layout->render($data);
	}

	/**
	 * Returns the field value.
	 * 
	 * @return  mixed
	 */
	private function getValue()
	{
		if (!$this->value)
		{
			return null;
		}

		if (!$this->choices = \NRFramework\Helpers\ChainedFields::loadCSV($this->dataset, $this->get('data_source', 'custom'), $this->separator, $this->id . '_', $this->name))
		{
			return null;
		}

		$choices = $this->choices['inputs'];

		// Ensure the value is in correct format when used as plain field or in subform
		$this->value = array_values((array) $this->value);

		foreach ($this->choices['inputs'] as $key => $input)
		{
			$choices[$key]['choices'] = $this->getSelectedChoices($key, $this->value, $input);
		}

		return $choices;
	}

	/**
	 * Finds the choices of the select.
	 * 
	 * @param   string  $key
	 * @param   array   $value
	 * @param   array   $input
	 * 
	 * @return  mixed
	 */
	private function getSelectedChoices($key, $value, $input)
	{
		$choices = $this->getSelectChoices($value, $input['id']);

		if (!is_array($choices))
		{
			return null;
		}

		// set selected options based on value
		foreach ($choices as $_key => &$choice)
		{
			if (!isset($value[$key]) || $choice['value'] !== $value[$key])
			{
				continue;
			}

			$choice['isSelected'] = true;
		}

		return $choices;
	}

    /**
     * Handles the AJAX request.
	 * 
	 * Runs when select a value from a select field.
     *
     * @param   array    $options
     *
     * @return  array
     */
    public function onAjax($options)
    {
		$options = new Registry($options);
	
		if (!$select_id = $options->get('select_id'))
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_CANNOT_PROCESS_REQUEST')
			]);
			jexit();
		}

		if (!$value = $options->get('value'))
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_CANNOT_PROCESS_REQUEST')
			]);
			jexit();
		}

		if (!$value = json_decode($options->get('value'), true))
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_CANNOT_PROCESS_REQUEST')
			]);
			jexit();
		}

		if (!$data_source = $options->get('data_source'))
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_CANNOT_PROCESS_REQUEST')
			]);
			jexit();
		}

		if (!$csv = $options->get('csv'))
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_CANNOT_PROCESS_REQUEST')
			]);
			jexit();
		}

		$csv = json_decode($csv, true);

		// get field ID from select ID
		$id = preg_replace('/_[0-9]+$/', '', $select_id);

		if (!$id || !$select_id || !$value || !$csv)
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_CANNOT_PROCESS_REQUEST')
			]);
			jexit();
		}

		$this->id = $id;

		// get all CSV data
		if (!$this->choices = \NRFramework\Helpers\ChainedFields::loadCSV($csv, $data_source, $this->separator, $this->id . '_', $this->name))
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_CANNOT_PROCESS_REQUEST')
			]);
			jexit();
		}

		// find next select options
		$this->findNextOptions($id, $select_id, $value);
	}

	/**
	 * Finds the options of the next select.
	 * 
	 * @param   string  $id
	 * @param   string  $select_id
	 * @param   array   $value
	 * 
	 * @return  void
	 */
	private function findNextOptions($id, $select_id, $value)
	{
		// find next ID
		$next_select_id = $this->getNextSelectID($id, $select_id);

		// get next choices
		$choices = $next_select_id ? $this->getSelectChoices($value, $next_select_id) : [];

		echo json_encode([
			'error' => false,
			'response' => $choices
		]);
		jexit();
	}

	/**
	 * Returns the select choices.
	 * 
	 * @param   array    $field_value
	 * @param   string   $select_id
	 * @param   integer  $depth
	 * @param   array    $choices
	 * @param   array    $full_field_value
	 * 
	 * @return  array
	 */
	private function getSelectChoices($field_value = null, $select_id = null, $depth = null, $choices = null, $full_field_value = null)
	{
		$full_field_value = $full_field_value !== null ? $full_field_value : $field_value;
		$value            = array_shift($field_value);
		$index            = $select_id ? $this->getSelectID($select_id) : 1;
		$depth            = $depth ? $depth : 1;
		$choices          = $choices === null ? $this->choices['choices'] : (empty($choices) ? [] : $choices);
		$select_choices   = [];

		if ($depth == $index)
		{
			$select_choices = $choices;
		}
		else
		{
			foreach ($choices as $choice)
			{
				if ($choice['value'] !== $value)
				{
					continue;
				}

				$select_choices = $this->getSelectChoices($field_value, $select_id, $depth + 1, !empty($choice['choices']) ? $choice['choices'] : [], $full_field_value);
				break;
			}
		}

		if (empty($select_choices) && $this->getPreviousSelectValue($select_id, $full_field_value))
		{
			$select_choices = [
				[
					'text'       => 'No options',
					'value'      => '',
					'isSelected' => true
				]
			];
		}

		return $select_choices;
	}

	/**
	 * Returns the previous select value
	 * 
	 * @param   string  $select_id
	 * @param   string  $full_field_value
	 * 
	 * @return  string
	 */
	public function getPreviousSelectValue($select_id, $full_field_value)
	{
		$explode = explode('_', $select_id);

		$input_id = $explode[count($explode) - 1];
		$field_id = rtrim($select_id, '_' . $input_id);

		$prev_input_id = sprintf('%s.%s', $field_id, $input_id - 1);
		$prev_input_value = isset($full_field_value[$prev_input_id]) ? $full_field_value[$prev_input_id] : null;

		return $prev_input_value;
	}

	/**
	 * Finds the next select ID
	 * 
	 * @param   string  $base_id
	 * @param   string  $select_id
	 * 
	 * @return  mixed
	 */
	public function getNextSelectID($base_id, $select_id)
	{
		$id = $this->getSelectID($select_id);
		$next_id = $id + 1;

		if ($next_id % 10 == 0)
		{
			$next_id++;
		}

		$next_select_id = sprintf('%s_%d', $base_id, $next_id);

		foreach ($this->choices['inputs'] as $input)
		{
			if ($input['id'] != $next_select_id)
			{
				continue;
			}

			return $next_select_id;
		}

		return false;
	}

	/**
	 * Gets the ID (index) of the select by its ID.
	 * 
	 * @param   string  $select_id
	 * 
	 * @return  int
	 */
	public function getSelectID($select_id)
	{
		$explode = explode('_', $select_id);

		return (int) array_pop($explode);
	}

	/**
	 * Fetches all CSV data.
	 * 
	 * @return  array
	 */
	private function getData()
	{
		if (!$this->dataset)
		{
			return [];
		}

		if (!$choices = \NRFramework\Helpers\ChainedFields::loadCSV($this->dataset, $this->get('data_source', 'csv_file'), $this->separator, $this->id . '_', $this->name))
		{
			return [];
		}

		return $choices;
	}
}