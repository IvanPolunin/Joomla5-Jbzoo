<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Gallery\Site\Trait;

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

trait CompatibilityTrait
{
    public static function checkCompatibility(int $id):void
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__bagallery_category')
            ->where('form_id = ' . $id)
            ->where('settings <>' . $db->quote(''));
        $db->setQuery($query);
        $categories = $db->loadObjectList();
        foreach ($categories as $category) {
            $settings = explode(';', $category->settings);
            $category->settings = '';
            $category->alias = $settings[8] ?? '';
            $category->published = $settings[2];
            $category->default = $settings[1];
            $category->category_id = $settings[4];
            $category->category_all = $settings[3] == '*' ? 1 : 0;
            $category->description = str_replace('-_-', ';', $settings[7]);
            $category->image = $settings[5];
            $db->updateObject('#__bagallery_category', $category, 'id');
        }
    }
}