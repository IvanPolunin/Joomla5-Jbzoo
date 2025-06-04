<?php 
namespace Joomla\Plugin\System\Selectcity\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

use Joomla\Plugin\System\Selectcity\Helper\CityMorpher;

class SelectcityHelper
{
    
    public static function getComponentParams($name_params)
    {
        $componentParams = ComponentHelper::getParams('com_selectcity');
        
        return $componentParams->get($name_params, 0);
    }
    
    public static function getCities() {
        // Получение объекта базы данных
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
    
        // Формирование запроса к базе данных для получения всех полей
        $query->select('*')
              ->from($db->quoteName('#__selectcity'))
              ->where($db->quoteName('state') . ' <> 0') // Фильтрация городов, у которых state не равно 0
              ->order('name_city ASC'); // Сортировка по возрастанию
    
        // Установка и выполнение запроса
        $db->setQuery($query);
        $cities = $db->loadAssocList();
    
        $restructuredCities = array();
        foreach ($cities as $city) {
            $restructuredCities[$city['id']] = $city;
        }
    
        return json_decode(json_encode($restructuredCities), true);
    }
    
    public static function shortcodeReplace($body, $cityCookieArr) {
        
        $app = Factory::getApplication();
        
        $currentCityName = $cityCookieArr['name_city'];
        
        // Проверяем, содержит ли $body шорткоды вида {city_Город}
        if (preg_match('/\{cityName_[^\}]+\}/', $body)) {
            $body = preg_replace_callback(
                '/\{cityName_(.*?)\}(.*?)\{\/cityName_\1\}/s',
                function ($matches) use ($currentCityName) {
                    return ($matches[1] === $currentCityName) ? $matches[2] : '';
                },
                $body
            );
        }
    
        // Проверяем, содержит ли $body шорткоды вида {!city_Город}
        if (preg_match('/\{\!cityName_[^\}]+\}/', $body)) {
            $body = preg_replace_callback(
                '/\{\!cityName_(.*?)\}(.*?)\{\/\!cityName_\1\}/s',
                function ($matches) use ($currentCityName) {
                    return ($matches[1] === $currentCityName) ? '' : $matches[2];
                },
                $body
            );
        }
        
                
        // Замена {city_url}
        $cityAlias = $cityCookieArr['city_allias'] ?? '';
        $body = str_replace('{city_url}', $cityAlias, $body);
        
        // Замена {sitename}
        $siteName = $app->get('sitename');
        $body = str_replace('{sitename}', $siteName, $body);
        
        // Замена {domain}
        $domain = \Joomla\CMS\Uri\Uri::root();
        $body = str_replace('{domain}', $domain, $body);
    
        
        // Заменяем шорткод {city} на название города
        $body = str_replace('{city}', $cityCookieArr['name_city'], $body);
        
        
        // Замена шорткодов для города в различных падежах
        $morpher = new CityMorpher();
        for ($i = 1; $i <= 6; $i++) {
            $body = str_replace(
                '{city_' . $i . '}',
                $morpher->getCase($currentCityName, $i),
                $body
            );
        }
        
        
        return $body;
        
    }
    
    
    public static function getCityFromUrl() {
        $parsedUrl = parse_url(Uri::getInstance()->getPath()); // Анализируем URL
        $path = $parsedUrl['path']; // Получаем путь из URL
    
        $pathParts = explode('/', trim($path, '/')); // Разбиваем путь на части
    
        return $pathParts[0]; // Возвращаем первую часть пути, которая идентифицирует город
    }
    
   public static function getCityAliasFromHost()
    {
        $host = \Joomla\CMS\Uri\Uri::getInstance()->getHost();
        $parts = explode('.', $host);
        
        // Предполагаем, что структура хоста: [алиас_города].domain.tld
        if (count($parts) >= 3) {
            return $parts[0]; // алиас города
        }
        
        return null; // алиас не найден
    }
    
    public static function findCityInArrayByUrl($citiesArr) {
        
        $cityMode = self::getComponentParams('regim_city');
        $cityAliasFromUrl = null; // Объявляем переменную по умолчанию
        
        if($cityMode == 'regim_subdomen'){
            $cityAliasFromUrl = self::getCityFromUrl();
        }
        
        if($cityMode == 'regim_reading_substitution'){
            $cityAliasFromUrl = self::getCityFromUrl();
        }
        
        if($cityMode == 'regim_poddomen'){
            $cityAliasFromUrl = self::getCityAliasFromHost();
        }
        
        foreach ($citiesArr as $city) {
            if ($city['city_allias'] === $cityAliasFromUrl) {
                return $city;
            }
        }

        return null; // Возвращает null, если город не найден
    }
    
    public static function clearCityAliasById(&$cities, $targetId) {
        foreach ($cities as &$letterGroup) {
            foreach ($letterGroup as &$cityObject) {
                if ($cityObject->id == $targetId) {
                    $cityObject->city_allias = ''; // Очищаем city_allias
                    // Нет необходимости возвращать true
                }
            }
        }
        
    }
    
}

