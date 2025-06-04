<?php 
namespace Joomla\Plugin\System\Selectcity\Extension;

defined('_JEXEC') or die;

use Joomla\Plugin\System\Selectcity\Helper\SelectcityHelper;
use Joomla\Plugin\System\Selectcity\Helper\CityMorpher;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Profiler\Profiler;


final class Selectcity extends CMSPlugin
{
    private $cityCookieId, $cityCookieArr, $citiesArr, $cityMode, $cityFavoriteId, $thisCityArr, $excludedUrls;
    private $cityUrl = 'city';
    protected $app;

    public function __construct($subject, $config = array(), CMSApplication $app = null)
    {
        Profiler::getInstance('Application')->mark('Selectcity plugin: Constructor start');
        
        $this->app = $app ?: Factory::getApplication();
        parent::__construct($subject, $config);
        $this->initializePluginProperties();
        
        Profiler::getInstance('Application')->mark('Selectcity plugin: Constructor end');
    }

    private function initializePluginProperties()
    {
        Profiler::getInstance('Application')->mark('Selectcity plugin: Initialize properties start');
        
        if ($this->app->isClient('site')) {
            
            
            $this->citiesArr = SelectcityHelper::getCities();
            $this->cityFavoriteId = SelectcityHelper::getComponentParams('city_favorite');
            $this->citiesArr[$this->cityFavoriteId]['city_allias'] = '';
            $this->cityMode = SelectcityHelper::getComponentParams('regim_city');
            $this->cityCookieId = $this->app->getInput()->cookie->get('city_id', '', 'string');
            $this->excludedUrls = $this->params->get('excluded_urls', []);
            
            $this->determineCity();
            
            
            if ($this->params->get('replace_robots_txt', 0)) {
                $input = $this->app->getInput();
                if ($input->getCmd('option') === 'com_selectcity' && $input->getCmd('task') === 'robots') {
                    $this->generateRobotsTxt();
                    $this->app->close();
                }
                if ($input->getCmd('option') === 'com_selectcity' && $input->getCmd('task') === 'sitemap') {
                    $this->generateSitemap();
                    $this->app->close();
                }
            }
            
        }
        
        Profiler::getInstance('Application')->mark('Selectcity plugin: Initialize properties end');
    }

    private function determineCity()
    {
        $foundCity = SelectcityHelper::findCityInArrayByUrl($this->citiesArr);
        
        switch ($this->cityMode) {
            case 'regim_poddomen':
                
                if (!empty($foundCity)) {
                    $this->thisCityArr = $foundCity; //если алиас города найден
                } else {
                    //если не найден
                    
                    $host = \Joomla\CMS\Uri\Uri::getInstance()->getHost();
                    $parts = explode('.', $host);
                    
                    // Вид основной домен -> domain.tld
                    if (count($parts) < 3) {
                        $this->thisCityArr = $this->citiesArr[$this->cityFavoriteId];
                    }
                    
                }
                
                break;
            case 'regim_subdomen':
                
                if (!empty($foundCity)) {
                    $this->thisCityArr = $foundCity;
                } else {
                    $this->thisCityArr = $this->citiesArr[$this->cityFavoriteId];
                }
                
                
                break;
            case 'regim_reading_substitution':
                
                if (!empty($foundCity)) {
                    $this->thisCityArr = $foundCity;
                } else {
                    $this->thisCityArr = $this->citiesArr[$this->cityFavoriteId];
                }
                
                break;
            default:
                $this->thisCityArr = !empty($this->cityCookieId) ? $this->citiesArr[$this->cityCookieId] : $this->citiesArr[$this->cityFavoriteId];
                break;
        }
        
        $this->cityUrl = $this->thisCityArr['city_allias'] ?? 'city';
    }
    
    
    public function getCurrentCityName()
    {
        return $this->thisCityArr['name_city'] ?? 'Город не найден';
    }

    public function getCurrentCityAlias()
    {
        return $this->thisCityArr['city_allias'] ?? 'Алиас не найден';
    }

    public function getCurrentCityCases()
    {
        $morpher = new CityMorpher();
        $cases = [];
        $cityName = $this->thisCityArr['name_city'] ?? '';

        for ($i = 1; $i <= 6; $i++) {
            $cases[$i] = $morpher->getCase($cityName, $i);
        }

        return $cases;
    }
    

    public function onAfterInitialise()
    { 
        Profiler::getInstance('Application')->mark('Selectcity plugin: onAfterInitialise start');
        
        if ($this->app->isClient('site') && $this->shouldExecute()) {
            
            if ($this->cityMode == 'regim_read') {
                return;
            }

            // Проверка на пустой список городов или отсутствие избранного города
            if ( empty(reset($this->citiesArr)['name_city']) || empty($this->cityFavoriteId) ) {
                $this->app->enqueueMessage(\Joomla\CMS\Language\Text::_('SELECTCITY: Не добавлены города или не выбран основной город в настройках компонента!'), 'warning');
                return;
            }
            
            if(empty($this->thisCityArr)){
                // Если город по алиасу не найден, выводим сообщение об ошибке и перенаправляем на страницу 404
                throw new \Joomla\CMS\Router\Exception\RouteNotFoundException(\Joomla\CMS\Language\Text::_('JERROR_LAYOUT_PAGE_NOT_FOUND'));
                return;
            }
            
            // Проверка исключенных URL и перенаправление на основной город, 
            // если текущий URL совпадает с одним из исключений и текущий город не избранный
            if(!empty($this->excludedUrls) && $this->thisCityArr['id'] != $this->cityFavoriteId){
                $currentUrl = Uri::getInstance()->toString(['path', 'query', 'fragment']);
                foreach ($this->excludedUrls as $excludedUrl) {
                    if (strpos($currentUrl, $excludedUrl->term) !== false) {
                        $this->processCityRedirection($this->citiesArrp[$this->cityFavoriteId]['city_allias']);
                        return;
                    }
                }
            }
            
            //если куки есть и это не текущий город, перенаправляем на город из куки
            if(!empty($this->cityCookieArr) && (serialize($this->thisCityArr) !== serialize($this->cityCookieArr))){
                $this->processCityRedirection($this->cityCookieArr['city_allias']);
            }
            
            if(!empty($this->cityUrl) && $this->cityMode == 'regim_subdomen'){
                $this->attachRouterRules();
            }
            
        }
        
        Profiler::getInstance('Application')->mark('Selectcity plugin: onAfterInitialise end');
    }

    private function shouldExecute()
    {
        $excludedFormats = ['json', 'com_ajax'];
        $excludedOptions = ['com_sppagebuilder'];
        $excludedLayouts = ['edit'];
        $excludedTask = ['edit', 'upload'];
        $excludedMethod = ['ajax', 'ajaxAddToCart'];

        $format = $this->app->getInput()->get('format', '');
        $option = $this->app->getInput()->get('option', '');
        $layout = $this->app->getInput()->get('layout', '');
        $task = $this->app->getInput()->get('task', '');
        $method = $this->app->getInput()->get('method', ''); // Получаем параметр method
        
        // Проверка на наличие параметра 'task'
        $isTaskPresent = !empty($task);

        if (in_array($format, $excludedFormats) || 
            in_array($option, $excludedOptions) ||
            in_array($layout, $excludedLayouts) ||
            in_array($task, $excludedTask) ||
            in_array($method, $excludedMethod) ||
            $isTaskPresent ){ 
            return false;
        }

        return true;
    }

    private function processCityRedirection(&$cityUrl)
    {
        $currentUri = Uri::getInstance();
        $path = $currentUri->getPath();
        
        $newPath = str_replace($this->cityUrl, '', $path);
        
        if(!empty($cityUrl)){
            $newPath = '/' . $cityUrl . $newPath;
        }
        
        
        $newPath = str_replace('//', '/', $newPath);
        
        $newUrl = $currentUri->toString(['scheme', 'host', 'port']) . $newPath;


        //если переменная $cityUrl пустая значит перенаправляем на основной город
        if(empty($cityUrl)){
            $newUrl = str_replace($this->cityUrl.'.', '', $newUrl);
            $newUrl = str_replace($this->cityUrl, '', $newUrl);
        }

        $this->app->redirect($newUrl);
        
    }

    private function attachRouterRules()
    {
        $router = $this->app->getRouter();
        
        $router->attachBuildRule([$this, 'buildRule'], Router::PROCESS_BEFORE);
        $router->attachParseRule([$this, 'parseRule'], Router::PROCESS_BEFORE);
        
        
    }

//menu
    public function buildRule(&$router, &$uri)
    {
        

        $path = $uri->getPath();

        // Удаляем "index.php" из пути, если он присутствует
        $path = str_replace('index.php', '', $path);
        
        if (strpos($path, $this->cityUrl) === false) {
            // Добавляем алиас города в начало пути, если его там нет
            $uri->setPath($this->cityUrl . '/' . ltrim($path, '/'));
        }
        
    }
    
//url
    public function parseRule(&$router, &$uri)
    {
        $path = $uri->getPath();

        if (strpos($path, $this->cityUrl) === 0) {
            $uri->setPath(substr($path, strlen($this->cityUrl) + 1));
        }
    }

    
    
    public function onAfterRender()
    {
        Profiler::getInstance('Application')->mark('Selectcity plugin: onAfterRender start');
    
        if ($this->app->isClient('site')) {
            $body = $this->app->getBody();
            Profiler::getInstance('Application')->mark('Selectcity plugin: get body');
    
            // Замена шорткода {city} на название города
            if (isset($this->thisCityArr['name_city'])) {
                $cityName = $this->thisCityArr['name_city'];
                $body = str_replace('{city}', $cityName, $body);
                Profiler::getInstance('Application')->mark('Selectcity plugin: replace {city}');
            }
    
            $currentUri = \Joomla\CMS\Uri\Uri::getInstance();
            $path = $currentUri->toString(['path']);
            $baseDomain = $currentUri->getHost();
    
            if (strpos($path, 'edit') === false && strpos($path, 'sppagebuilder') === false && strpos($path, 'index.php') === false) {
                if ($this->params->get('auto_shortcode_replace', 1)) {
                    $body = SelectcityHelper::shortcodeReplace($body, $this->thisCityArr);
                    Profiler::getInstance('Application')->mark('Selectcity plugin: shortcodeReplace');
                }
    
                if ($this->params->get('auto_city_alias', 0)) {
                    if ($this->cityMode == 'regim_subdomen' && $this->thisCityArr['id'] != $this->cityFavoriteId) {
                        $alias = $this->thisCityArr['city_allias'];
                        $currentUrl = Uri::getInstance()->toString(['scheme', 'host', 'port', 'path']); // Текущий URL без параметров
            
                        // Добавляем алиас города к каждой ссылке внутри тегов <a>
                        $body = preg_replace_callback('/<a\s+([^>]*?)href="([^"]*)"/i', function($matches) use ($alias, $baseDomain, $currentUrl) {
                            $attributes = $matches[1];
                            $url = $matches[2];
                            
                            // Не изменять ссылки, содержащие :
                            if (strpos($url, ':') !== false) {
                                return $matches[0]; 
                            }
            
                            // Проверяем, ведет ли URL на файл (содержит ли точку в последней части пути)
                            $urlPath = parse_url($url, PHP_URL_PATH);
                            if (preg_match('/\.[a-z0-9]+$/i', $urlPath)) {
                                return $matches[0]; // Не изменять ссылки, ведущие на файлы
                            }
            
                            // Проверяем, является ли URL ссылкой вида #
                            if ($url === '#' || $url === '') {
                                return $matches[0]; // Не изменять ссылку, ведущую на текущую страницу или являющуюся #
                            }
            
                            if (strpos($url, 'http') === 0) { // Абсолютные URL
                                $urlHost = parse_url($url, PHP_URL_HOST);
                                if ($urlHost === $baseDomain) { // Сравниваем домены
                                    if (!preg_match('/\/' . preg_quote($alias, '/') . '\//', $url)) { // Проверяем, содержит ли URL уже алиас
                                        return '<a ' . $attributes . 'href="' . preg_replace('/https?:\/\/[^\/]+/', '$0/' . $alias, $url) . '"';
                                    }
                                }
                            } elseif ($url === '/') { // Относительные URL, ведущие на главную страницу
                                return '<a ' . $attributes . 'href="/' . $alias . '"';
                            } elseif (strpos($url, '/') === 0) { // Относительные URL, начинающиеся с '/'
                                if (!preg_match('/\/' . preg_quote($alias, '/') . '\//', $url)) { // Проверяем, содержит ли URL уже алиас
                                    return '<a ' . $attributes . 'href="/' . $alias . $url . '"';
                                }
                            } else { // Относительные URL, не начинающиеся с '/'
                                if (!preg_match('/^' . preg_quote($alias, '/') . '\//', $url)) { // Проверяем, содержит ли URL уже алиас
                                    return '<a ' . $attributes . 'href="/' . $alias . '/' . ltrim($url, '/') . '"';
                                }
                            }
                            return $matches[0]; // Не изменять другие URL
                        }, $body);
                        Profiler::getInstance('Application')->mark('Selectcity plugin: preg_replace_callback');
                    }
                }
                
                // Режим "reading_substitution"
                if ($this->cityMode == 'regim_reading_substitution') {
                    $alias = $this->thisCityArr['city_allias']; // Текущий алиас города
                    $cities = SelectcityHelper::getCities(); // Список городов
                    $currentDomain = \Joomla\CMS\Uri\Uri::getInstance()->getHost(); // Текущий домен
                    $isFavoriteCity = $this->thisCityArr['id'] == $this->cityFavoriteId; // Проверка на избранный город
                
                    // Обрабатываем все ссылки в теле страницы
                    $body = preg_replace_callback('/<a\s+([^>]*?)href="([^"]*)"/i', function ($matches) use ($alias, $cities, $currentDomain, $isFavoriteCity) {
                        $attributes = $matches[1]; // Атрибуты тега <a>
                        $url = $matches[2]; // URL ссылки
                
                        // Исключаем ссылки на телефон и почту
                        if (strpos($url, 'tel:') === 0 || strpos($url, 'mailto:') === 0) {
                            return $matches[0]; // Возвращаем ссылку без изменений
                        }
                
                        // Проверяем, является ли ссылка абсолютной и принадлежит ли текущему домену
                        if (strpos($url, 'http') === 0) {
                            $urlDomain = parse_url($url, PHP_URL_HOST); // Извлекаем домен из URL
                            if ($urlDomain !== $currentDomain) {
                                return $matches[0]; // Внешнюю ссылку пропускаем
                            }
                
                            // Убираем домен из URL для дальнейшей обработки
                            $urlPath = parse_url($url, PHP_URL_PATH);
                        } else {
                            $urlPath = $url; // Относительный URL
                        }
                
                        $hasCityAlias = false;
                
                        // Проверяем наличие алиаса города в пути
                        foreach ($cities as $city) {
                            $cityAlias = $city['city_allias'];
                            if (strpos($urlPath, '/' . $cityAlias . '/') !== false || preg_match('/^\/' . preg_quote($cityAlias, '/') . '$/', $urlPath)) {
                                // Если алиас найден, заменяем его на текущий алиас
                                $urlPath = preg_replace('/\/' . preg_quote($cityAlias, '/') . '(\/|$)/', $isFavoriteCity ? '/' : '/' . $alias . '$1', $urlPath);
                                $hasCityAlias = true;
                                break;
                            }
                        }
                
                        // Если алиас города отсутствует, добавляем текущий алиас
                        if (!$hasCityAlias) {
                            if ($isFavoriteCity) {
                                // Для избранного города алиас не добавляем
                                $urlPath = $urlPath;
                            } else {
                                if (strpos($urlPath, '/') === 0) {
                                    $urlPath = '/' . $alias . $urlPath;
                                } else {
                                    $urlPath = '/' . $alias . '/' . ltrim($urlPath, '/');
                                }
                            }
                        }
                
                        // Если ссылка была абсолютной, добавляем обратно домен
                        if (strpos($url, 'http') === 0) {
                            $url = parse_url($url, PHP_URL_SCHEME) . '://' . $currentDomain . $urlPath;
                        } else {
                            $url = $urlPath; // Для относительных ссылок используем только путь
                        }
                
                        // Возвращаем обновленную ссылку
                        return '<a ' . $attributes . 'href="' . $url . '"';
                    }, $body);
                
                    Profiler::getInstance('Application')->mark('Selectcity plugin: regim_reading_substitution applied');
                }

                
                
                // Добавляем алиас города к классам, если параметр unique_code активирован
                if ($this->params->get('unique_code', 0)) {
                    $alias = $this->thisCityArr['city_allias'];
                    $classesToUpdate = [
                        'row ',
                        'container-inner',
                        'col-lg-12',
                        'col-md-3',
                        'col-md-9',
                        'mb-3',
                        'col-lg-6',
                        'col-lg-4',
                        'col-lg-9',
                        'col-md-6',
                        'col-md-4',
                        'col-md-2',
                        'col-lg-3',
                        'sppb-addon-content',
                        'sppb-section',
                        'sppb-btn  ',
                        'sppb-addon-header',
                        'sp-module-content'
                    ];
    
                    foreach ($classesToUpdate as $class) {
                        $body = preg_replace_callback('/class="([^"]*?)\b' . preg_quote($class, '/') . '\b([^"]*?)"/', function($matches) use ($alias, $class) {
                            return 'class="' . $matches[1] . $class . ' ' . $alias . ' ' . $matches[2] . '"';
                        }, $body);
                    }
                    Profiler::getInstance('Application')->mark('Selectcity plugin: unique_code applied');
                }
    
                $this->app->setBody($body);
                Profiler::getInstance('Application')->mark('Selectcity plugin: set body');
            }
        }
    
        Profiler::getInstance('Application')->mark('Selectcity plugin: onAfterRender end');
    }
    
    
    private function generateRobotsTxt() {
        // Построение базового URL
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $host = preg_replace("/\:\d+/is", "", $host);  // Удаление порта из хоста, если он присутствует
    
        // Путь к файлу robots.txt в корне директории Joomla
        $robotsFilePath = JPATH_ROOT . '/robots.txt';
        if (file_exists($robotsFilePath)) {
            $contents = file_get_contents($robotsFilePath);
            // Замена строк
            $contents = preg_replace('/Host:.*$/im', "Host: {$scheme}://{$host}", $contents);
            $contents = preg_replace('/Sitemap:.*$/im', "Sitemap: {$scheme}://{$host}/sitemap.xml", $contents);
    
            // Установка заголовков и вывод контента
            header('Content-Type: text/plain');
            echo $contents;
        } else {
            echo "User-agent: *\nDisallow: /administrator/\nHost: {$scheme}://{$host}\nSitemap: {$scheme}://{$host}/sitemap.xml";
        }
        $this->app->close();  // Закрытие приложения для предотвращения дальнейшей обработки
    }
    
    private function generateSitemap() {
        // Определение текущего домена и протокола
        $currentHost = $_SERVER['HTTP_HOST'];
        $currentHost = preg_replace("/\:\d+/is", "", $currentHost); // Удаление порта, если есть
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http";
    
        // Извлечение поддомена
        $parts = explode('.', $currentHost);
        if (count($parts) > 2) {
            $alias = array_shift($parts); // Получаем поддомен (алиас)
            $mainDomain = implode('.', $parts);
        } else {
            $alias = '';
            $mainDomain = $currentHost;
        }
    
        // Путь к файлу sitemap для текущего поддомена (алиаса)
        $aliasSitemapPath = JPATH_ROOT . "/media/plg_task_selectcity_sitemap/{$alias}_sitemap.xml";
    
        // Проверяем, существует ли файл для текущего поддомена
        if (!empty($alias) && file_exists($aliasSitemapPath)) {
            $sitemapContent = file_get_contents($aliasSitemapPath);
            if ($sitemapContent !== false) {
                // Отправка заголовка для XML
                header('Content-Type: text/xml');
                echo $sitemapContent; // Вывод содержимого alias_sitemap.xml
                return;
            }
        }
    
        // Если файл для поддомена не найден, выполняем стандартную логику
        $sitemapPath = JPATH_ROOT . '/sitemap.xml'; // Путь к файлу sitemap.xml
        if (file_exists($sitemapPath)) {
            $sitemapContent = file_get_contents($sitemapPath);
            if ($sitemapContent === false) {
                // Обработка ошибки чтения файла
                echo "Ошибка при чтении файла sitemap.xml.";
                return;
            }
    
            // Замена домена и протокола в содержимом sitemap.xml
            $pattern = '/<loc>\s*(https?:\/\/)[^\/]+(\/.*?)\s*<\/loc>/i';
            $replacement = "<loc>{$protocol}://{$currentHost}$2</loc>";
            $updatedContent = preg_replace($pattern, $replacement, $sitemapContent);
            
            // если текущий URL совпадает с одним из исключений и текущий город не избранный
            if(!empty($this->excludedUrls) && $this->thisCityArr['id'] != $this->cityFavoriteId){
                foreach ($this->excludedUrls as $excludedUrl) {
                    $term = preg_quote($excludedUrl->term, '/');
                    $urlPattern = "/<url>(?:(?!<\/url>).)*?<loc>[^<]*{$term}[^<]*<\/loc>.*?<\/url>/is";
                    $updatedContent = preg_replace($urlPattern, '', $updatedContent);
                }
            }
            
            // Отправка заголовка для XML
            header('Content-Type: text/xml');
            echo $updatedContent; // Вывод обновлённого содержимого
        } else {
            echo "Файл sitemap.xml не найден.";
        }
    }


}



    
