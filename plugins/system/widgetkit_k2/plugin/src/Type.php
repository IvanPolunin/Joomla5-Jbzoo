<?php

namespace YOOtheme\Widgetkit\Joomla\K2;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use YOOtheme\Widgetkit\Content\ContentInterface;
use YOOtheme\Widgetkit\Content\Type as BaseType;

class Type extends BaseType
{
    public function getItems(ContentInterface $content)
    {
        $items = parent::getItems($content);
        $params = new Registry(array(
            'itemCount' => $content['number'],
            'category_id' => $content['category'],
            'getChildren' => $content['subcategories'],
            'catfilter' => !empty($content['category']) && $content['category'][0] != 0,
            'itemIntroText' => true,
            'itemsOrdering' => $content['ordering'],
            'itemImage' => true,
            'itemTags' => true,
            'itemExtraFields' => true,
            'itemAuthor' => true,
            'itemCategory' => true
        ));

        // get cat/fields group relation
        $cats = $this->app['db']->fetchAllObjects("SELECT cat.id as id, gro.name as 'group' FROM @k2_categories as cat LEFT JOIN @k2_extra_fields_groups AS gro ON cat.extraFieldsGroup = gro.id WHERE cat.trash = 0 ORDER BY cat.parent, cat.ordering");

        $cats_group = array();
        foreach ($cats as $cat) {
            $cats_group[$cat->id] = $cat->group;
        }

        require_once(JPATH_ROOT.'/modules/mod_k2_content/helper.php');

        if (K2_JVERSION != '15') {
            $language = Factory::getApplication()->getLanguage();
            $language->load('mod_k2.j16', JPATH_ADMINISTRATOR, null, true);
        }

        $k2_items = \modK2ContentHelper::getItems($params) ?: array();

        foreach ($k2_items as $item) {

            $data    = array();
            $group   = isset($cats_group[$item->catid]) ? $cats_group[$item->catid] : 'default';
            $mapping = isset($content['mapping'][$group]) ? $content['mapping'][$group] : array();

            $data['title'] = $item->title;

            $data['tags'] = array();
            foreach ($item->tags as $tag) {
                $data['tags'][] = $tag->name;
            }

            foreach ($mapping as $field => $value) {

                if ((int)($value) != 0) {
                    $item->current_xfield = $value;
                    $value = 'xfields';
                }

                $event = $this->app->trigger('joomla.k2.render.'.$value, compact('item'));

                $data[$field] = isset($event['value']) ? $event['value'] : '';
            }

            // validate fields
            $data['date']       = isset($data['date']) && strtotime($data['date']) ? $data['date'] : null;
            $data['author']     = isset($data['author']) && is_string($data['author']) ? $data['author'] : null;
            $data['categories'] = isset($data['categories']) && is_array($data['categories']) ? $data['categories'] : null;

            if (isset($data['content']) && is_array($data['content'])) {
                $data['content'] = implode(', ', $data['content']);
            }

            $items->add($data);
        }

        return $items;
    }

    public function getFormData()
    {
        $result = array('fields' => array(), 'categories' => array());

        // none field
        $none = array(array('id' => 'none', 'name' => $this->app['translator']->trans('None'), 'type' => ''));

        // core fields
        $core_fields = array(
            array('id' => 'intro', 'name' => $this->app['translator']->trans('Article Intro'), 'type' => 'Core'),
            array('id' => 'full' , 'name' => $this->app['translator']->trans('Article Full'),  'type' => 'Core'),
            array('id' => 'link', 'name' => $this->app['translator']->trans('Article Link'), 'type' => 'Core'),
            array('id' => 'created', 'name' => $this->app['translator']->trans('Article Created date'), 'type' => 'Core'),
            array('id' => 'publish_up', 'name' => $this->app['translator']->trans('Article Publish Up date'), 'type' => 'Core'),
            array('id' => 'publish_down', 'name' => $this->app['translator']->trans('Article Publish Down date'), 'type' => 'Core'),
            array('id' => 'author', 'name' => $this->app['translator']->trans('Author'), 'type' => 'Core'),
            array('id' => 'image', 'name' => $this->app['translator']->trans('Image'), 'type' => 'Core'),
            array('id' => 'categories', 'name' => $this->app['translator']->trans('Categories'), 'type' => 'Core')
        );

        // custom fields
        $fields = $this->app['db']->fetchAllObjects("SELECT field.id as 'id', field.type as 'type', field.name as 'name', groups.name as 'group' FROM @k2_extra_fields as field LEFT JOIN @k2_extra_fields_groups AS groups ON field.group = groups.id WHERE published = 1");

        foreach ($fields as $field) {

            if (!isset($result['fields'][$field->group])) {
                $result['fields'][$field->group] = array();
            }

            $result['fields'][$field->group][] = array('id' => $field->id, 'name' => $field->name, 'type' => ucfirst($field->type));
        }

        // add core fields
        foreach ($result['fields'] as &$group) {
            $group = array_merge($group, $core_fields, $none);
        }

        // if no groups add default one
        if (empty($result['fields'])) {
            $result['fields'] = array('default' => array_merge($core_fields, $none));
        }

        // categories
        $children = array();
        $categories = $this->app['db']->fetchAllObjects('SELECT * FROM @k2_categories WHERE trash = 0 ORDER BY parent, ordering');

        foreach ($categories as $category) {

            $category->title = $category->name;
            $category->parent_id = $pt = $category->parent;

            $children[$pt] = isset($children[$pt]) ? $children[$pt] : array();
            $children[$pt][] = $category;
        }

        $list = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);

        $result['categories'][] = array('id' => 0, 'name' => $this->app['translator']->trans('All'));

        foreach ($list as $item) {
            $result['categories'][] = array(
                'id'   => $item->id,
                'name' => StringHelper::str_ireplace('&#160;&#160;', '.&nbsp;&nbsp;&nbsp;', $item->treename)
            );
        }

        return $result;
    }
}
