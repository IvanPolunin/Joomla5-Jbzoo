<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Uri\Uri;

class ModSelectCityListHelper
{
    private static $db;

    private static function getDB()
    {
        if (!self::$db) {
            self::$db = Factory::getDbo();
        }
        return self::$db;
    }

    public static function getCities()
    {
        $db = self::getDB();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__selectcity'))
            ->where($db->quoteName('state') . ' <> 0') // Фильтрация городов, у которых state не равно 0
            ->order('name_city ASC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getGroupedCities()
    {
        $groupedCities = [];
        foreach (self::getCities() as $city) {
            $firstLetter = mb_strtoupper(mb_substr($city->name_city, 0, 1, 'UTF-8'), 'UTF-8');
            $groupedCities[$firstLetter][] = $city;
        }

        return $groupedCities;
    }

    public static function getArrCitiesId($cityId)
    {
        $arrayCities = self::getCities();
        foreach ($arrayCities as $cityObject) {
            if ($cityObject->id == $cityId) {
                return $cityObject;
            }
        }
        return null;
    }

    public static function getContentAjax()
    {
        $groupedCities = self::getGroupedCities();
        $componentParams = self::getComponentParams();

        $favoriteCityId = $componentParams->get('city_favorite', '');

        self::clearCityAliasById($groupedCities, $favoriteCityId);

        if (!empty($groupedCities)) {
            $html = '<div class="city-groups">';
            foreach ($groupedCities as $letter => $cities) {
                $html .= "<div class='city-group'>";
                $html .= "<h2>{$letter}</h2>";
                $html .= '<ul>';
                foreach ($cities as $city) {
                    $city_link = self::getCityLink($city, $componentParams);
                    $html .= "<li> <a href='{$city_link}' class='city-one' data-id='{$city->id}' data-alias='{$city->city_allias}'>" . htmlspecialchars($city->name_city) . "</a></li>";
                }
                $html .= "</ul>";
                $html .= "</div>";
            }
            $html .= '</div>';
        } else {
            $html = "<p>No cities found.</p>";
        }

        return $html;
    }

    public static function getComponentParams()
    {
        return ComponentHelper::getParams('com_selectcity');
    }

    private static function getCityLink($city, $componentParams)
    {
        $cityMode = $componentParams->get('regim_city', ''); // Режим работы
        $fullUrl = Factory::getApplication()->input->getString('fullUrl', ''); // Полный URL
        $path = Factory::getApplication()->input->getString('path', ''); // Путь страницы
    
        // Получаем текущий протокол (http или https)
        $scheme = Uri::getInstance()->getScheme();
    
        // Если режим 'regim_reading_substitution', загружаем города
        if ($cityMode == 'regim_reading_substitution') {
            $cities = self::getCities();
        }
    
        switch ($cityMode) {
            case 'regim_poddomen':
                // Формируем URL для поддоменов
                return $scheme . '://' . htmlspecialchars($city->city_allias) . '.' . ltrim($fullUrl, '.');
    
            case 'regim_subdomen':
                // Формируем URL для субдоменов
                $city_link = '/' . htmlspecialchars($city->city_allias) . $path;
                return str_replace("//", "/", $city_link);
    
            case 'regim_reading_substitution':
                
                $favoriteCityId = $componentParams->get('city_favorite', '');
                
                // Если $path пустой, извлекаем путь из $fullUrl
                if (empty($path) && !empty($fullUrl)) {
                    if (strpos($fullUrl, '/') !== false) {
                        $path = substr($fullUrl, strpos($fullUrl, '/')); // Извлекаем путь
                    } else {
                        $path = '/'; // Если путь не найден, устанавливаем "/"
                    }
                }
    
                $currentUrl = $path; // Используем только путь
    
                // Проверяем и заменяем алиас города
                foreach ($cities as $existingCity) {
                    // Проверяем алиас города в текущем URL
                    if (strpos($currentUrl, '/' . $existingCity->city_allias . '/') !== false ||
                        strpos($currentUrl, '/' . $existingCity->city_allias) === (strlen($currentUrl) - strlen('/' . $existingCity->city_allias))) {
                        
                        // Замена алиаса города с учетом наличия завершающего слэша
                        $currentUrl = preg_replace(
                            '/\/' . preg_quote($existingCity->city_allias, '/') . '(\/|$)/',
                            '/' . htmlspecialchars($city->city_allias) . '$1',
                            $currentUrl
                        );
                        break; // Прерываем цикл, т.к. город уже заменен
                    }
                }
                
                
    
                // Если алиас города не найден и это не избранный город, добавляем алиас текущего города
                if ((empty($currentUrl) || strpos($currentUrl, '/' . htmlspecialchars($city->city_allias) . '/') === false) && ($city->id != $favoriteCityId)) {
                    $currentUrl = '/' . htmlspecialchars($city->city_allias) . $path;
                }
    
                $city_link = str_replace("//", "/", $currentUrl);
               
                // Проверяем доступность маршрута и убираем части пути до существующего
                while (!self::isRouteAccessible($city_link)) {
                    // Убираем последнюю часть пути
                    $city_link = rtrim($city_link, '/'); // Убираем слэш в конце
                    $lastSlash = strrpos($city_link, '/'); // Находим последнее вхождение "/"
                    
                    if ($lastSlash === false || $lastSlash === 0) {
                        // Если ничего не осталось, возвращаем алиас города
                        $city_link = '/' . htmlspecialchars($city->city_allias);
                        break;
                    }
    
                    $city_link = substr($city_link, 0, $lastSlash); // Обрезаем путь до последнего "/"
                }
    
                return $city_link;
    
            default:
                // Если режим не указан, возвращаем полный URL
                return $scheme . '://' . $fullUrl;
        }
    }
    
    private static function isUrlAccessible($url) //проверка доступности url
    {
        // Получаем объект HTTP-клиента Joomla
        $httpClient = \Joomla\CMS\Http\HttpFactory::getHttp();
    
        try {
            // Выполняем запрос HEAD к указанному URL
            $response = $httpClient->head($url);
    
            // Проверяем статус-код ответа
            return $response->code === 200;
        } catch (\Exception $e) {
            // Если произошла ошибка, URL считается недоступным
            return false;
        }
    }
    
    private static function isRouteAccessible($urlPath)
    {
        try {
            // Используем Router для обработки пути
            $uri = \Joomla\CMS\Uri\Uri::getInstance();
            $uri->setPath($urlPath);
    
            $router = \Joomla\CMS\Factory::getApplication()->getRouter();
            $route = $router->parse($uri);
    
            // Если парсинг прошел, маршрут существует
            return !empty($route);
        } catch (\Exception $e) {
            // Если маршрут недоступен или произошла ошибка
            return false;
        }
    }
    

    private static function clearCityAliasById(&$cities, $targetId)
    {
        foreach ($cities as &$letterGroup) {
            foreach ($letterGroup as &$cityObject) {
                if ($cityObject->id == $targetId) {
                    $cityObject->city_allias = '';
                }
            }
        }
    }
}