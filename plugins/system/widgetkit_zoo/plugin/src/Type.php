<?php

namespace YOOtheme\Widgetkit\Joomla\Zoo;

use YOOtheme\Widgetkit\Content\ContentInterface;
use YOOtheme\Widgetkit\Content\Type as BaseType;

class Type extends BaseType
{
    protected $zoo;

    public function __construct(array $values = array())
    {
        parent::__construct($values);
        $this->zoo = \App::getInstance('zoo');
    }

    public function getItems(ContentInterface $content)
    {
        $items  = parent::getItems($content);
        $params = $content->getData();
        $order  = array();

        // detect reversed order
        if (strpos($params['order'], 'reversed') !== false) {

            $order[] = str_replace('_reversed', '', $params['order']);
            $order[] = '_reversed';

        } else {
            $order[] = $params['order'];
        }

        // set order
        $params['order'] = $order;


        foreach ($this->zoo->module->getItems($this->zoo->data->create($params)) as $item) {

            $data    = array();
            $mapping = $content['mapping'][$item->getType()->id];

            $data['zooitem']    = $item;
            $data['title']      = $item->name;

            foreach ($mapping as $field => $value) {

                if(!$element = $item->getElement($value)) {
                    continue;
                }

                if ($element instanceof \ElementRepeatable) {
                    $element->seek(0);
                }

                $event = $this->app->trigger('joomla.zoo.render.'.$element->getElementType(), compact('element'));

                $data[$field] = isset($event['value']) ? $event['value'] : $element->get('value');

                if ($field == 'tags') {
                    $data[$field] = is_array($data[$field]) ? $data[$field] : array($data[$field]);
                }
            }

            // validate fields
            $data['author']     = isset($data['author']) && is_string($data['author']) ? $data['author'] : null;
            $data['categories'] = isset($data['categories']) && is_array($data['categories']) ? $data['categories'] : null;

            if (!empty($data['date']) && strtotime($data['date'])) {
                $date = $this->zoo->date->create($data['date']);
                $data['date'] = $date
                    ->add($this->zoo->date->create($data['date'], $this->zoo->date->getOffset())->diff($date))
                    ->format(DATE_ISO8601);
            } else {
                $data['date'] = null;
            }

            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = array_filter(array_keys($data['tags']), 'is_numeric') ? array_values($data['tags']): array_keys($data['tags']);
            }

            if (isset($data['content']) && is_array($data['content'])) {
                $data['content'] = implode(', ', $data['content']);
            }

            $items->add($data);
        }

        return $items;
    }

    public function getFormData()
    {
        $result = array();

        foreach ($this->zoo->application->getApplications() as $app) {

            $data = array('id' => $app->id, 'name' => $app->name, 'types' => array(), 'categories' => array());

            foreach ($app->getTypes() as $type) {

                $data['types'][$type->id] = array(
                    'id'       => $type->id,
                    'name'     => $type->getName(),
                    'elements' => array_values(array_map(function ($el) {
                        return array(
                            'id'        => $el->identifier,
                            'name'      => $el->config->name ?: $el->getMetaData('name'),
                            'type'      => $el->getElementType(),
                            'orderable' => $el->getMetaData('orderable') == 'true',
                            'core'      => $el->getMetaData('group') == 'Core',
                            'group'     => $el->getMetaData('group')
                        );
                    }, $type->getElements() + $type->getCoreElements()))
                );

                // add none mapping option
                $data['types'][$type->id]['elements'][] = array(
                    'id'    => 'none',
                    'name'  => $this->app['translator']->trans('None'),
                    'group' => ''
                );
            }

            $data['categories'][] = array('id' => '0', 'name' => html_entity_decode('&#8226; Frontpage', ENT_QUOTES, 'UTF-8'));
            foreach ($this->zoo->tree->buildList(0, $app->getCategoryTree(), array(), '-&nbsp;', '.&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;') as $category) {
                $data['categories'][] = array(
                    'id'   => $category->id,
                    'name' => html_entity_decode($category->treename, ENT_QUOTES, 'UTF-8')
                );
            }

            $result[$app->id] = $data;
        }

        return $result;
    }
}
