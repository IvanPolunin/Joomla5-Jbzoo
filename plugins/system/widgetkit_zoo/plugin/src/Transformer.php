<?php

namespace YOOtheme\Widgetkit\Joomla\Zoo;

use YOOtheme\Framework\Event\EventSubscriberInterface;

class Transformer implements EventSubscriberInterface
{
    protected $cache;
    protected $zoo;

    public function __construct()
    {
        $this->zoo = \App::getInstance('zoo');
    }

    public function __destruct()
    {
        if ($this->cache && $this->cache->check()) {
            $this->cache->save();
        }
    }

    public function renderFile($event, $element)
    {
        $event['value'] = $element->get('file');
    }

    public function renderMedia($event, $element)
    {
        $event['value'] = $element->get('url');
    }

    public function renderOption($event, $element)
    {
        $event['value'] = $element->get('option');
    }

    public function renderCountry($event, $element)
    {
        $event['value'] = $element->get('country');
    }

    public function renderItemlink($event, $element)
    {
        $event['value'] = html_entity_decode($this->zoo->route->item($element->getItem()));
    }

    public function renderItemhits($event, $element)
    {
        $event['value'] = $element->getItem()->hits;
    }

    public function renderItemname($event, $element)
    {
        $event['value'] = $element->getItem()->name;
    }

    public function renderItemtag($event, $element)
    {
        $event['value'] = $element->getItem()->getTags();
    }

    public function renderItempublish_up($event, $element)
    {
        $event['value'] = $element->getItem()->publish_up;
    }

    public function renderItempublish_down($event, $element)
    {
        $event['value'] = $element->getItem()->publish_down;
    }

    public function renderItemcreated($event, $element)
    {
        $event['value'] = $element->getItem()->created;
    }

    public function renderItemmodified($event, $element)
    {
        $event['value'] = $element->getItem()->modified;
    }

    public function renderGooglemaps($event, $element)
    {
        if ($element->hasValue()) {

            try {

                $coordinates = $this->zoo->googlemaps->locate($element->get('location'), $this->getCache(), $element->config->get('key'));

            } catch (\GooglemapsHelperException $e) {
                $this->zoo->system->application->enqueueMessage($e, 'notice');
                return;
            }

            $event['value'] = $coordinates;

        }
    }

    public function renderItemauthor($event, $element)
    {
        $author = $element->getItem()->created_by_alias;
        $user   = $this->zoo->user->get($element->getItem()->created_by);

        $event['value'] = empty($author) && $user ? $user->name : $author;
    }

    public function renderItemcategory($event, $element)
    {
        $categories = array();
        foreach ($element->getItem()->getRelatedCategories(true) as $cat) {
            $categories[$cat->name] = $this->zoo->route->category($cat);
        }

        $event['value'] = $categories;
    }

    public function renderRelatedcategories($event, $element)
    {
        $categories = array();
        foreach ($this->zoo->table->category->getById($element->get('category', array()), true) as $cat) {
            $categories[$cat->name] = $this->zoo->route->category($cat);
        }

        $event['value'] = $categories;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'joomla.zoo.render.itemlink'          => 'renderItemlink',
            'joomla.zoo.render.itemhits'          => 'renderItemhits',
            'joomla.zoo.render.itemname'          => 'renderItemname',
            'joomla.zoo.render.itemtag'           => 'renderItemtag',
            'joomla.zoo.render.image'             => 'renderFile',
            'joomla.zoo.render.download'          => 'renderFile',
            'joomla.zoo.render.checkbox'          => 'renderOption',
            'joomla.zoo.render.radio'             => 'renderOption',
            'joomla.zoo.render.select'            => 'renderOption',
            'joomla.zoo.render.media'             => 'renderMedia',
            'joomla.zoo.render.googlemaps'        => 'renderGooglemaps',
            'joomla.zoo.render.country'           => 'renderCountry',
            'joomla.zoo.render.itempublish_up'    => 'renderItempublish_up',
            'joomla.zoo.render.itempublish_down'  => 'renderItempublish_down',
            'joomla.zoo.render.itemcreated'       => 'renderItemcreated',
            'joomla.zoo.render.itemmodified'      => 'renderItemmodified',
            'joomla.zoo.render.itemauthor'        => 'renderItemauthor',
            'joomla.zoo.render.itemcategory'      => 'renderItemcategory',
            'joomla.zoo.render.relatedcategories' => 'renderRelatedcategories'
        );
    }

    public function getCache()
    {
        if (null === $this->cache) {
            $this->cache = $this->zoo->cache->create($this->zoo->path->path('cache:').'/geocode_cache');
        }

        return $this->cache;
    }
}
