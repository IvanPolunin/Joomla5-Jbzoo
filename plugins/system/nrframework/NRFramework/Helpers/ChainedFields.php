<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Helpers;

defined('_JEXEC') or die;

class ChainedFields
{
	/**
	 * Loads a combined array of the inputs and choices of the CSV file.
	 * 
	 * @param   string  $path
	 * @param   string  $data_source
	 * @param   string  $separator
	 * @param   string  $id_prefix
	 * @param   string  $name_prefix
	 * 
	 * @return  array
	 */
	public static function loadCSV($input, $data_source = 'custom', $separator = ',', $id_prefix = '', $name_prefix = '')
	{
		if (!$separator)
		{
			return [];
		}

		if ($data_source === 'csv_file')
		{
			if (!file_exists($input))
			{
				return [];
			}
	
			if (!$input = file_get_contents($input))
			{
				return [];
			}
		}

		if (!$data = self::getData($input, $separator, $id_prefix, $name_prefix))
		{
			return [];
		}

		return $data;
	}

	/**
	 * Iterates over the given data and returns the inputs and choices.
	 * 
	 * @param   string  $data
	 * @param   string  $separator
	 * @param   string  $id_prefix
	 * @param   string  $name_prefix
	 * 
	 * @return  array
	 */
	public static function getData($data = '', $separator = ',', $id_prefix = '', $name_prefix = '')
	{
		if (!$data || !is_string($data))
		{
			return;
		}
		
		if (!$rows = explode(PHP_EOL, $data))
		{
			return;
		}

		$choices = [];
		$inputs = [];

		foreach ($rows as $row)
		{
			$row = explode($separator, $row);

			$row = array_filter($row, 'strlen');

			// if an empty row was found, skip it
			if (empty($row))
			{
				continue;
			}

			if (empty($inputs))
			{
				$i = 1;
				
				foreach ($row as $index => $item)
				{
					if ($i % 10 == 0)
					{
						$i++;
					}

					$inputs[] = [
						'id'    => $id_prefix . $i,
						'name'  => $name_prefix . '[' . $i . ']',
						'label' => trim($item),
					];

					$i++;
				}

				continue;
			}

			$parent = null;

			foreach($row as $item)
			{
				$item = trim($item);
				
				if ($parent === null)
				{
					$parent = &$choices;
				}

				if (!isset($parent[$item]))
				{
					$item = trim($item);

					$parent[$item] = [
						'text'       => $item,
						'value'      => $item,
						'isSelected' => false,
						'choices'    => []
					];
				}

				$parent = &$parent[$item]['choices'];
			}
		}

		self::array_values_recursive($choices);

		if (!isset($inputs) || !isset($choices))
		{
			return;
		}

		return compact('inputs', 'choices');
	}

	/**
	 * Transforms an array to using as key an index value instead of a alphanumeric.
	 * 
	 * @param   array   $choices
	 * @param   string  $property
	 * 
	 * @return  array
	 */
	public static function array_values_recursive(&$choices, $property = 'choices')
	{
		$choices = array_values($choices);

		for($i = 0; $i <= count($choices); $i++)
		{
			if(empty($choices[$i][$property]))
			{
				continue;
			}

			$choices[$i][$property] = self::array_values_recursive($choices[$i][$property], $property);
        }

		return $choices;
	}
}