<?php

namespace YOOtheme\Widgetkit\Joomla\K2;

use Joomla\CMS\Uri\Uri;
use YOOtheme\Framework\Event\EventSubscriberInterface;

class Transformer implements EventSubscriberInterface
{
    public function renderImage($event, $item)
    {
        $event['value'] = isset($item->image) ? Uri::base(true) . '/media/k2/items/src/' . md5('Image'.$item->id) . '.jpg' : '';
    }

    public function renderIntro($event, $item)
    {
        $event['value'] = $item->introtext;
    }

    public function renderFull($event, $item)
    {
        $event['value'] = $item->introtext.$item->fulltext;
    }

    public function renderExtrafields($event, $item)
    {
        $field = array_values(array_filter((array)$item->extra_fields, function($field) use ($item) {
            return $field->id == $item->current_xfield;
        }));

        if (!empty($field)) {
            $event['value'] = $field[0]->value;
        }
    }

    public function renderCreated($event, $item)
    {
        $event['value'] = $item->created;
    }

    public function renderPublish_up($event, $item)
    {
        $event['value'] = $item->publish_up;
    }

    public function renderPublish_down($event, $item)
    {
        $event['value'] = $item->publish_down;
    }

    public function renderAuthor($event, $item)
    {
        $event['value'] = $item->author;
    }

    public function renderCategories($event, $item)
    {
        $event['value'] = array($item->categoryname => $item->categoryLink);
    }

    public function renderLink($event, $item)
    {
        $event['value'] = html_entity_decode($item->link);
    }

    public static function getSubscribedEvents()
    {
        return array(
            'joomla.k2.render.image'        => 'renderImage',
            'joomla.k2.render.intro'        => 'renderIntro',
            'joomla.k2.render.full'         => 'renderFull',
            'joomla.k2.render.xfields'      => 'renderExtrafields',
            'joomla.k2.render.created'      => 'renderCreated',
            'joomla.k2.render.publish_up'   => 'renderPublish_up',
            'joomla.k2.render.publish_down' => 'renderPublish_down',
            'joomla.k2.render.author'       => 'renderAuthor',
            'joomla.k2.render.categories'   => 'renderCategories',
            'joomla.k2.render.link'         => 'renderLink'
        );
    }
}
