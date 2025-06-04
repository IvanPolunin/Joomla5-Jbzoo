<?php 
namespace Joomla\Plugin\System\Selectcity\Helper;

defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

use Joomla\Plugin\System\Selectcity\Helper\CityMorpher;

class SelectcityData
{
    protected static $cityInfo = null;

    // Инициализация данных города
    protected static function initialize() {
        if (self::$cityInfo !== null) {
            return;
        }

        $app = Factory::getApplication();
        $input = $app->input;

        // Попытка загрузить данные из текущего URL или из кук
        $cityAlias = self::getCityAliasFromUrl();
        if ($cityAlias) {
            self::$cityInfo = self::findCityByAlias($cityAlias);
        }

        if (empty(self::$cityInfo)) {
            $cityId = $input->cookie->get('city_id', '', 'int');
            if ($cityId) {
                self::$cityInfo = self::loadCityData($cityId);
            }
        }

        // Если город так и не был определен, загрузим данные избранного города
        if (empty(self::$cityInfo)) {
            $favoriteCityId = SelectcityHelper::getComponentParams('city_favorite');
            if ($favoriteCityId) {
                self::$cityInfo = self::loadCityData($favoriteCityId);
            }
        }
    }

    // Получение алиаса города из URL
    protected static function getCityAliasFromUrl() {
        $uri = Uri::getInstance();
        $host = $uri->getHost();
        $path = trim($uri->getPath(), '/');
        $parts = explode('.', $host);
        $pathParts = explode('/', $path);

        // Предположим, что алиас города может быть поддоменом или первой частью пути
        if (count($parts) > 2) {
            return array_shift($parts); // Поддомен
        } elseif (!empty($pathParts)) {
            return array_shift($pathParts); // Первая часть пути
        }

        return null;
    }

    // Загрузка данных города по алиасу
    protected static function findCityByAlias($alias) {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__selectcity'))
            ->where($db->quoteName('city_allias') . ' = ' . $db->quote($alias)); // Исправлено название столбца на city_allias
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    // Загрузка данных города по ID
    protected static function loadCityData($cityId) {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__selectcity'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($cityId));
        $db->setQuery($query);
        return $db->loadAssoc();
    }
    
    public static function getCityAlias() {
        self::initialize();  // Убедимся, что данные города инициализированы
        return self::$cityInfo['city_allias'] ?? null;  // Возвращаем алиас или null, если данные не загружены
    }

    // Получение всех склонений текущего города
    public static function getCurrentCityCases() {
        self::initialize();  // Инициализация данных
    
        if (empty(self::$cityInfo)) {
            return null;  // Если информация о городе не найдена, возвращаем null
        }
    
        $cityName = self::$cityInfo['name_city'] ?? '';  // Получаем название города
    
        $morpher = new CityMorpher();
        $cases = [];
        for ($i = 1; $i <= 6; $i++) {
            $cases[] = $morpher->getCase($cityName, $i);
        }
    
        return [
            'nominative' => $cases[0] ?? $cityName,  // Именительный падеж, или оригинальное название, если не склоняется
            'genitive' => $cases[1] ?? '',           // Родительный падеж
            'dative' => $cases[2] ?? '',             // Дательный падеж
            'accusative' => $cases[3] ?? '',         // Винительный падеж
            'instrumental' => $cases[4] ?? '',       // Творительный падеж
            'prepositional' => $cases[5] ?? ''       // Предложный падеж
        ];
    }
}