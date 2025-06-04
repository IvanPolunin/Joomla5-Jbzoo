<?php
namespace Joomla\Plugin\Task\Selectcity_sitemap\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\SubscriberInterface;

class SelectcitySitemap extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;

    protected const TASKS_MAP = [
        'plg_task_check_sitemap' => [
            'langConstPrefix' => 'PLG_TASK_CHECK_SITEMAP',
            'method' => 'checkSitemap',
            'form' => 'check_sitemap'
        ]
    ];

    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList' => 'advertiseRoutines',
            'onExecuteTask' => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    private function getCities(): array
    {
        // Получение объекта базы данных
        /** @var DatabaseDriver $db */
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

        return $restructuredCities;
    }

    private function checkSitemap(ExecuteTaskEvent $event): int
    {
        // Путь к файлу sitemap.xml
        $filePath = JPATH_ROOT . '/sitemap.xml';
        // Путь к целевой папке
        $targetFolder = JPATH_ROOT . '/media/plg_task_selectcity_sitemap';
    
        // Проверяем, существует ли файл
        if (file_exists($filePath)) {
            // Проверяем, существует ли папка, и создаем ее, если нет
            if (!Folder::exists($targetFolder)) {
                if (!Folder::create($targetFolder)) {
                    $this->logTask('Ошибка создания папки ' . $targetFolder, 'error');
                    return Status::KNOCKOUT;
                }
            }
    
            // Определение текущего домена и протокола
            $currentHost = $_SERVER['HTTP_HOST'];
            $currentHost = preg_replace("/\:\d+/is", "", $currentHost); // Удаление порта, если есть
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http";
    
            // Извлечение основного домена
            $parts = explode('.', $currentHost);
            if (count($parts) > 2) {
                // Убираем поддомен
                $mainDomain = implode('.', array_slice($parts, -2));
            } else {
                $mainDomain = $currentHost;
            }
    
            // Получаем список городов
            $cities = $this->getCities();
    
            // Копируем sitemap.xml с именами алиасов городов
            foreach ($cities as $city) {
                $alias = $city['city_allias'];
                $targetFile = $targetFolder . '/' . $alias . '_sitemap.xml';
    
                // Открываем исходный файл для чтения
                $handle = fopen($filePath, 'r');
                if ($handle === false) {
                    $this->logTask('Ошибка открытия файла sitemap.xml', 'error');
                    return Status::NO_TASK;
                }
    
                // Открываем целевой файл для записи
                $targetHandle = fopen($targetFile, 'w');
                if ($targetHandle === false) {
                    fclose($handle);
                    $this->logTask('Ошибка создания файла ' . $targetFile, 'error');
                    return Status::KNOCKOUT;
                }
    
                // Чтение и обработка файла построчно
                while (($line = fgets($handle)) !== false) {
                    // Замена домена и протокола в строке sitemap.xml для текущего города
                    $pattern = '/<loc>\s*(https?:\/\/)' . preg_quote($mainDomain, '/') . '(\/.*?)\s*<\/loc>/i';
                    $replacement = "<loc>{$protocol}://{$alias}.{$mainDomain}$2</loc>";
                    $updatedLine = preg_replace($pattern, $replacement, $line);
                    
                    // Запись обработанной строки в целевой файл
                    fwrite($targetHandle, $updatedLine);
                }
    
                // Закрываем оба файла
                fclose($handle);
                fclose($targetHandle);
    
                // Логируем успешное копирование для каждого города
                $this->logTask('Файл sitemap.xml успешно скопирован в ' . $targetFile, 'info');
            }
    
            return Status::OK;
        } else {
            // Логируем отсутствие файла
            $this->logTask('Файл sitemap.xml не найден', 'error');
            return Status::NO_TASK;
        }
    }
}