<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Selectcity
 * @author     Ivan Polynin <ipolynin@gmail.com>
 * @copyright  2024 Ivan Polynin
 * @license    GNU General Public License версии 2 или более поздней; Смотрите LICENSE.txt
 */

namespace Selectcity\Component\Selectcity\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use Selectcity\Component\Selectcity\Administrator\Helper\SelectcityHelper;

/**
 * Methods supporting a list of Selectcitys records.
 *
 * @since  1.0.0
 */
class SelectcitysModel extends ListModel
{
	/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'city_allias', 'a.city_allias',
				'name_city', 'a.name_city',
				'name_country', 'a.name_country',
				'name_region', 'a.name_region',
			);
		}

		parent::__construct($config);
	}


	

	

	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('name_city', 'ASC');

		$context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		// Split context into component and optional section
		if (!empty($context))
		{
			$parts = FieldsHelper::extract($context);

			if ($parts)
			{
				$this->setState('filter.component', $parts[0]);
				$this->setState('filter.section', $parts[1]);
			}
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string A store id.
	 *
	 * @since   1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		
		return parent::getStoreId($id);
		
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   1.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__selectcity` AS a');
		
		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');
		

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif (empty($published))
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.city_allias LIKE ' . $search . '  OR  a.name_city LIKE ' . $search . ' )');
			}
		}
		
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'name_city');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	 /*public function getItems() {
		$items = parent::getItems();
		

		return $items;
	}*/
	
    public function getItems()
    {
        // Получаем экземпляр базы данных
        $db = Factory::getDbo();
    
        // Получаем объект запроса
        $query = $db->getQuery(true);
    
        // Ваш SQL-запрос с фильтрами
        $query->select('a.*')
              ->from($db->quoteName('#__selectcity', 'a'));
    
        // Применяем фильтры, если они заданы
        $filter_name_country = $this->getState('filter.name_country');
        if (!empty($filter_name_country)) {
            $query->where('a.' . $db->quoteName('name_country') . ' = ' . $db->quote($filter_name_country));
        }
    
        $filter_name_region = $this->getState('filter.name_region');
        if (!empty($filter_name_region)) {
            $query->where('a.' . $db->quoteName('name_region') . ' = ' . $db->quote($filter_name_region));
        }
        
        // Получаем поисковый запрос из состояния модели
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            // Добавляем условия поиска в запрос
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('a.name_city LIKE ' . $search);
        }
        
        // Получаем фильтр состояния
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.state = ' . (int) $state);
        } elseif ($state === '') {
            $query->where('(a.state IN (0, 1))'); // Можете изменить на нужные вам состояния
        }
    
        // Клонируем запрос для подсчета общего числа элементов
        $countQuery = clone $query;
        $countQuery->clear('select')->select('COUNT(*)');
    
        // Устанавливаем запрос на подсчет общего числа элементов и выполняем его
        $db->setQuery($countQuery);
        $total = (int) $db->loadResult();
    
        // Обновляем состояние модели общим числом элементов для корректной пагинации
        $this->setState('list.total', $total);
    
        // Получаем параметры сортировки и направления из состояния модели
        $orderCol = $this->state->get('list.ordering', 'a.name_city');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
    
        // Учитываем лимит и начальную точку (смещение) для запроса элементов
        $limit = $this->getState('list.limit');
        $start = $this->getState('list.start');
        $query->setLimit($limit, $start);
    
        // Выполняем запрос и возвращаем элементы
        $db->setQuery($query);
        $items = $db->loadObjectList();
    
        return $items;
    }
    
    public function getTotal()
    {
        // Получаем экземпляр базы данных
        $db = Factory::getDbo();
        // Получаем объект запроса
        $query = $db->getQuery(true);
    
        // Подготавливаем запрос для подсчета общего числа элементов
        $query->select('COUNT(*)')
              ->from($db->quoteName('#__selectcity', 'a'));

        // Применяем фильтры, если они заданы
        $filter_name_country = $this->getState('filter.name_country');
        if (!empty($filter_name_country)) {
            $query->where('a.' . $db->quoteName('name_country') . ' = ' . $db->quote($filter_name_country));
        }
    
        $filter_name_region = $this->getState('filter.name_region');
        if (!empty($filter_name_region)) {
            $query->where('a.' . $db->quoteName('name_region') . ' = ' . $db->quote($filter_name_region));
        }
        
        // Получаем поисковый запрос из состояния модели
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            // Добавляем условия поиска в запрос
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('a.name_city LIKE ' . $search);
        }
        
        // Получаем фильтр состояния
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.state = ' . (int) $state);
        } elseif ($state === '') {
            $query->where('(a.state IN (0, 1))'); // Можете изменить на нужные вам состояния
        }
        
    
        // Устанавливаем запрос и выполняем его
        $db->setQuery($query);
        return (int) $db->loadResult();
    }
    
    
    public function exportCSV()
    {
        // Получение данных для экспорта
        $dataArray = $this->getItems();
        
        // Имя файла CSV
        $csvFile = 'output.csv';
        
        // Открываем файл для записи
        $handle = fopen($csvFile, 'w');
        
        // Получаем ключи первого элемента массива и удаляем ненужные
        $headers = array_keys((array)$dataArray[0]);
        $excludeFields = ['id', 'state', 'ordering', 'checked_out', 'checked_out_time', 'created_by', 'modified_by'];
        $headers = array_diff($headers, $excludeFields);
    
        // Записываем отфильтрованные заголовки CSV
        fputcsv($handle, $headers);
        
        // Записываем данные, исключая ненужные поля
        foreach ($dataArray as $row) {
            $row = (array)$row;
            $filteredRow = array_diff_key($row, array_flip($excludeFields));
            fputcsv($handle, $filteredRow);
        }
        
        // Закрываем файл
        fclose($handle);
        
        // Отправляем HTTP-заголовки для скачивания файла
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $csvFile . '"');
        header('Content-Length: ' . filesize($csvFile));
        
        // Отправляем содержимое файла
        readfile($csvFile);
        
        // Удаляем временный файл (если нужно)
        unlink($csvFile);
        
        Factory::getApplication()->close();
    }
    
    public function importCSV($csvFile)
    {
        // Очистка таблицы перед загрузкой новых данных
        $this->truncateTable();
    
        $file = fopen($csvFile, 'r');
    
        // Пропускаем заголовок CSV
        $header = fgetcsv($file);
        $addGenerateAlias = false;
        
        if (!in_array('city_allias', $header)) {
            $header[] = 'city_allias'; // Добавляем поле 'city_allias' в заголовки
            $addGenerateAlias = true;
        }

    
        while (($row = fgetcsv($file)) !== false) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            
            if($addGenerateAlias == true){
                // Создаем алиас из name_city
                $cityAliasIndex = array_search('name_city', $header);
                if ($cityAliasIndex !== false) {
                    $cityAlias = \JFilterOutput::stringURLSafe($row[$cityAliasIndex]);
                    $row[] = $cityAlias;
                }
            }
            
            // Вставляем данные в базу данных
            $query->insert($db->quoteName('#__selectcity'))
                ->columns($db->quoteName($header))
                ->values(implode(',', array_map(array($db, 'quote'), $row)));
    
            $db->setQuery($query);
    
            try {
                $db->execute();
            } catch (\Exception $e) {
                // Логируем ошибку
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        fclose($file);
    }

    private function truncateTable()
    {
        // Очистка таблицы
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Получаем имена таблицы с учетом префикса
        $tableName = $db->quoteName('#__selectcity');

        // Создаем SQL-запрос для удаления всех записей из таблицы
        $query->delete($tableName);

        // Выполняем запрос
        $db->setQuery($query);
        $db->execute();
    }
    
    
}
