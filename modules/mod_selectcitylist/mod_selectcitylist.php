<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

// Подключаем файл helper.php
require_once __DIR__ . '/helper.php';

// Получение объекта документа
$doc = Factory::getDocument();

// Добавление файла стилей
$doc->addStyleSheet(Uri::root() . 'modules/mod_selectcitylist/assets/css/selectcitylist.css');

// Добавление JavaScript файла
$doc->addScript(Uri::root() . 'modules/mod_selectcitylist/assets/js/selectcitylist.js');

// Получение значения настройки 'city_favorite'
$componentParams = ModSelectCityListHelper::getComponentParams();
$cityFavorite = $componentParams->get('city_favorite', '');
$cityMode = $componentParams->get('regim_city', '');

$arrayCities = ModSelectCityListHelper::getCities();

// Получение объекта ввода приложения.
$input = Factory::getApplication()->input;
// Получение значения cookie.
$citieCookieId = $input->cookie->get('city_id', 0, 'INT');

// Определение ссылки в зависимости от режима
switch ($cityMode) {
    case 'regim_poddomen':
        $popupCityName = '<a href="#" title="Выбрать город" id="link_my_selectcitylist" data-url="{city_url}"> {city} <span class="down">▼</span></a>';
        break;

    case 'regim_subdomen':
        $popupCityName = '<a href="#" title="Выбрать город" id="link_my_selectcitylist" data-url="{city_url}"> {city} <span class="down">▼</span></a>';
        break;
        
    case 'regim_reading_substitution':
        $popupCityName = '<a href="#" title="Выбрать город" id="link_my_selectcitylist" data-url="{city_url}"> {city} <span class="down">▼</span></a>';
        break;

    default:
        if ($citieCookieId > 0) {
            $arrCityObject = ModSelectCityListHelper::getArrCitiesId($citieCookieId);
        } else {
            $arrCityObject = ModSelectCityListHelper::getArrCitiesId($cityFavorite);
        }
        $popupCityName = '<a href="#" title="Выбрать город" id="link_my_selectcitylist" data-url="{city_url}"> ' . $arrCityObject->name_city . ' <span class="down">▼</span></a>';
        break;
}

// Получение выбранного пользователем шаблона из настроек
$layout = $params->get('layout', 'default');
$labelText = $params->get('label_text', 'Ваш город:'); // Получаем текст из настроек или значение по умолчанию
$labelPopup = $params->get('label_popup', 'Выберите Ваш город'); // Получаем текст из настроек или значение по умолчанию


// Загрузка соответствующего файла шаблона
include ModuleHelper::getLayoutPath('mod_selectcitylist', $layout);