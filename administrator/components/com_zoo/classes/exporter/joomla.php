<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\TagsHelper;

/**
 * Exporter for Joomla 2.5/3.0 articles and categories
 *
 * @package Component.Classes.Exporters
 */
class AppExporterJoomla extends AppExporter {

	/**
	 * Class Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->_name = 'Joomla';
	}

	/**
	 * If this exporter can be used
	 *
	 * @return boolean If this exporter can be used
	 *
	 * @since 2.0
	 */
	public function isEnabled() {
		return $this->app->joomla->version->isCompatible('3.0');
	}

	/**
	 * Perform the actual export of categories and articles
	 *
	 * @return string The exported data in JSON format
	 *
	 * @since 2.0
	 */
	public function export() {

		$categories = $this->app->database->queryObjectList('SELECT * FROM #__categories WHERE published != -2 ORDER BY lft ASC', 'id');

		$category_aliases = array();
		$ordered_categories = array();
		foreach ($categories as $category) {
			$ordered_categories[$category->parent_id][] = $category->id;
		}

	    foreach ($categories as $category) {

			if ($category->alias != 'root' && $category->extension != 'com_content') {
				continue;
			}

			if ($category->alias == 'root') {
				$category->title  = 'Root';
				$category->alias = '_root';
			}
			$i = 2;
			$new_alias = $category->alias;
			while (in_array($new_alias, $category_aliases)) {
				$new_alias = $category->alias . '-' . $i++;
			}
			$category_aliases[] = $category->alias = $new_alias;

			// store category parent
			if (isset($categories[$category->parent_id])) {
				$category->parent = $categories[$category->parent_id]->alias;
			}

			if (isset($ordered_categories[$category->parent_id]) && is_array($ordered_categories[$category->parent_id])) {
				$category->ordering = array_search($category->id, $ordered_categories[$category->parent_id]);
			}

			$params = $this->app->parameter->create($category->params);

	    	$data = array();
			foreach ($this->category_attributes as $attribute) {
				if (isset($category->$attribute)) {
					$data[$attribute] = $category->$attribute;
				}
			}

            $data['content']['Image'] = $params->get('image');
            $data['content']['Image Alt Text'] = $params->get('image_alt');

            if (class_exists(FieldsHelper::class)) {
                foreach (FieldsHelper::getFields('com_content.categories', $category) as $field) {
                    if ($field->state == 1 && in_array($field->type, array('media', 'text', 'textarea', 'editor', 'url', 'color', 'integer'))) {
                        $data['content'][$field->label] = $field->rawvalue;
                    }
                }
            }

			$this->_addCategory($category->title, $category->alias, $data);

			$query = "SELECT * FROM #__content WHERE catid =" . $category->id;
			$articles = $this->app->database->queryObjectList($query);

			foreach ($articles as $article) {
				if ($article->state != -2) {
					$this->_addJoomlaItem($article, $category->alias, Text::_('Joomla article'));
				}
			}
	    }

		$query = "SELECT * FROM #__content WHERE catid = 0";
		$articles = $this->app->database->queryObjectList($query);

		foreach ($articles as $article) {
			if ($article->state != -2) {
				$this->_addJoomlaItem($article, 0, Text::_('Joomla article'));
			}
		}

		return parent::export();

	}

	/**
	 * Add an item to the export list
	 *
	 * @param object $article The article to export
	 * @param string $parent Where the article should be inserted into
	 * @param string $group The group in which it should be put into
	 *
	 * @return AppExporterJoomla $this For chaining support
	 *
	 * @since 2.0
	 */
	protected function _addJoomlaItem($article, $parent, $group = 'default') {

		if ($article->state > 1) {
			$article->state = 0;
		}

		$data = array();
		foreach ($this->item_attributes as $attribute) {
			if (isset($article->$attribute)) {
				$data[$attribute] = $article->$attribute;
			}
		}

		$metadata = $this->app->parameter->create($article->metadata);
		$data['metadata'] = array('description' => $article->metadesc, 'keywords' => $article->metakey, 'robots' => $metadata->get('robots'), 'author' => $metadata->get('author'));

		$data['author'] = ($user = $this->app->user->get($article->created_by)) ? $user->username : $this->app->user->get()->username;

		if ($article->featured) {
			$data['categories'][] = '_root';
		}
		$data['categories'][] = $parent;
        $data['config']['primary_category'] = $parent;

        $tags = (new TagsHelper())->getItemTags('com_content.article', $article->id);
        $data['tags'] = array_map(function ($tag) { return $tag->title; }, $tags);

		$data['elements'][0]['type'] = 'textarea';
		$data['elements'][0]['name'] = 'Article';
		$data['elements'][0]['data'] = array(array('value' => $article->introtext), array('value' => $article->fulltext));

		$images = @json_decode($article->images, true) ?: array();

		$data['elements'][1]['type'] = 'image';
		$data['elements'][1]['name'] = 'Intro Image';
		$data['elements'][1]['data'] = array('file' => @$images['image_intro'], 'title' => @$images['image_intro_caption']);

		$data['elements'][2]['type'] = 'image';
		$data['elements'][2]['name'] = 'Fulltext Image';
		$data['elements'][2]['data'] = array('file' => @$images['image_fulltext'], 'title' => @$images['image_fulltext_caption']);

		$urls = @json_decode($article->urls, true) ?: array();

		$data['elements'][3]['type'] = 'link';
		$data['elements'][3]['name'] = 'Link A';
		$data['elements'][3]['data'] = array(array('value' => @$urls['urla'] ?: '', 'text' => @$urls['urlatext']));

		$data['elements'][4]['type'] = 'link';
		$data['elements'][4]['name'] = 'Link B';
		$data['elements'][4]['data'] = array(array('value' => @$urls['urlb'] ?: '', 'text' => @$urls['urlbtext']));

		$data['elements'][5]['type'] = 'link';
		$data['elements'][5]['name'] = 'Link C';
		$data['elements'][5]['data'] = array(array('value' => @$urls['urlc'] ?: '', 'text' => @$urls['urlctext']));

        if (class_exists(FieldsHelper::class)) {
            foreach (FieldsHelper::getFields('com_content.article', $article) as $field) {

                if ($field->state != 1 || in_array($field->type, array('usergrouplist', 'user'))) {
                    continue;
                }

                $data['elements'][$field->name]['name'] = "{$field->title} ({$field->name})";
                $data['elements'][$field->name] += is_callable($resolve = array($this, "_resolveField{$field->type}"))
                    ? $resolve($field, $data['elements'])
                    : $this->_resolveField($field, $field->rawvalue);
            }
        }

		parent::_addItem($article->title, $article->alias, $group, $data);
	}

    protected function _resolveField($field, $value)
    {
        if ($field->fieldparams->exists('multiple') && $field->fieldparams['multiple']) {
            $value = array_map(function ($value) {
                return is_scalar($value) ? compact('value') : $value;
            }, (array) $value);
        } else {
            $value = array('value' => $value);
        }

        return array(
            'type' => 'text|email',
            'data' => array($value)
        );
    }

    protected function _resolveFieldTextarea($field)
    {
        return array('type' => 'textarea') + $this->_resolveField($field, $field->rawvalue);
    }

    protected function _resolveFieldEditor($field)
    {
        return array('type' => 'textarea') + $this->_resolveField($field, $field->rawvalue);
    }

    protected function _resolveFieldList($field)
    {
        return array('type' => 'select|checkbox|radio', 'data' => array('option' => (array) $field->rawvalue));
    }

    protected function _resolveFieldCheckboxes($field)
    {
        return array('type' => 'select|checkbox|radio', 'data' => array('option' => (array) $field->rawvalue));
    }

    protected function _resolveFieldRadio($field)
    {
        return array('type' => 'select|checkbox|radio', 'data' => array('option' => (array) $field->rawvalue));
    }

    protected function _resolveFieldCalendar($field)
    {
        return array('type' => 'date') + $this->_resolveField($field, $field->rawvalue);
    }

    protected function _resolveFieldMedia($field)
    {
        return array(
            'type' => 'image|media|download',
            'data' => array('file' => $field->rawvalue, 'title' => basename($field->rawvalue))
        );
    }

    protected function _resolveFieldUrl($field, &$elements)
    {
        $elements["{$field->name}_media"] = array(
            'name' => "{$field->title} ({$field->name})",
            'type' => 'media',
            'data' => array('url' => $field->rawvalue)
        );

        return array(
            'type' => 'link',
            'data' => array(array('text' => $field->rawvalue, 'value' => $field->rawvalue))
        );
    }

    protected function _resolveFieldImagelist($field)
    {
        $root = ComponentHelper::getParams('com_media')->get('file_path', 'images') . "/{$field->fieldparams->get('directory')}";
        $values = array_filter((array) $field->rawvalue, function ($value) {
            return $value && $value != -1;
        });

        $value = array_pop($values);

        return array(
            'type' => 'image',
            'data' => array('file' => "{$root}/{$value}", 'title' => basename($value))
        );
    }

    protected function _resolveFieldRepeatable($field, &$elements)
    {
        $data = json_decode($field->rawvalue, true);
        $data = $data ? array_values($data) : [];

        $fields = (array) $field->fieldparams->get('fields');

        if (strpos($field->note, 'zootype') === 0) {

            $element = [];
            $mapping = [];

            foreach (explode(';', $field->note) as $str) {

                @list($key, $value) = explode(':', $str, 2);
                $key = trim($key);
                $value = trim($value);

                if ($key === 'zootype') {
                    $element['type'] = $value;
                }

                if ($key === 'zoofield') {
                    list($subfield, $name) = explode('=', $value);
                    $mapping[trim($subfield)] = trim($name);
                }

            }

            $element['data'] = array_reduce($data, function ($arr, $value) use ($field, $mapping) {

                $data = [];
                foreach ($mapping as $subfield => $name) {
                    $data[$name] = $value[$subfield];
                }

                return array_merge($arr, array($data));
            }, array());

            return $element;
        }

        if (count($fields) === 1) {

            $subfield = current($fields);

            if (in_array($subfield->fieldtype, ['editor', 'text', 'textarea'])) {
                $elements["{$field->name}_{$subfield->fieldname}"] =  array(
                    'name' => "{$field->title} ({$field->name})",
                    'type' => $subfield->fieldtype === 'editor' ? 'textarea' : $subfield->fieldtype,
                    'data' => array_reduce($data, function ($arr, $value) use ($subfield) {
                        return array_merge($arr, array(array('value'=> $value[$subfield->fieldname])));
                    }, array())
                );
            }
        }

        return array(
            'type' => 'group',
            'data' => $data
        );
    }

    protected function _resolveFieldArticles($field)
    {
        $ids = array_filter((array) $field->rawvalue);

        $aliases = $ids ? $this->app->database->queryResultArray(
            'SELECT alias FROM #__content WHERE id IN (' . implode(',', $ids) . ')'
        ) : [];

        return array(
            'type' => 'relateditems',
            'data' => array('item' => $aliases)
        );
    }

}
