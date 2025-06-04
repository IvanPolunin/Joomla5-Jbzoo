<?php

namespace YOOtheme\Widgetkit\Image;

use claviska\SimpleImage;
use YOOtheme\Framework\Application;
use YOOtheme\Framework\ApplicationAware;

class Image extends ApplicationAware
{
    /**
     * @var string[]
     */
    protected $defaults = ['w' => '', 'h' => '', 'strategy' => ''];

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string[]
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @param Application $app
     * @param string      $file
     */
    public function __construct(Application $app, $file)
    {
        $this->app = $app;
        $this->file = $file;
    }

    /**
     * @return string[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param  string[] $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = array_intersect_key(
            array_replace($this->defaults, $options),
            $this->defaults
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getPathName()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this['url']->to($this->file);
    }

    /**
     * @param  string|string[] $densities
     * @return Image[]
     */
    public function getSrcSets($densities = '2x')
    {
        $result = [];
        foreach ((array) $densities as $density) {
            $result[$density] = $this['image']->create(
                preg_replace('/(.*?)(\.[^\.]+)?$/i', "$1-$density$2", $this->file, 1)
            );
        }
        return array_filter($result);
    }

    /**
     * @param  string|string[] $densities
     * @return string[]
     */
    public function getSrcSetUrls($densities = '2x')
    {
        $srcSets = $this->getSrcSets($densities);
        array_walk($srcSets, function (&$img, $density) {
            $img = $img->getUrl() . ' ' . $density;
        });
        return $srcSets;
    }

    /**
     * @return string
     */
    public function thumbnail()
    {
        $this->options['strategy'] = 'thumbnail';
        return $this->cache();
    }

    /**
     * @return string
     */
    public function bestFit()
    {
        $this->options['strategy'] = 'best_fit';
        return $this->cache();
    }

    /**
     * @return string
     */
    public function resize()
    {
        $this->options['strategy'] = 'resize';
        return $this->cache();
    }

    /**
     * @param  bool $save
     * @return string
     */
    public function cache($save = false)
    {
        if (!file_exists($cache = $this->getCacheName())) {
            if (!$save) {
                $file = ltrim(substr($this->file, strlen($this['request']->getBasePath())), '/');
                return $this['url']->route(
                    'image',
                    array_merge($this->options, ['file' => $file, 'hash' => $this->getHash()])
                );
            }

            $this->create()->toFile($cache);
        }

        return $this['url']->to($cache);
    }

    /**
     * @param string $format
     * @param string $quality
     */
    public function output($format = null, $quality = null)
    {
        $this->create()->toScreen($format, $quality);
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return md5($this->file . ';' . implode(';', $this->options) . ';' . $this['secret']);
    }

    /**
     * @return string
     */
    protected function getCacheName()
    {
        return sprintf(
            '%s/%s-%s.%s',
            $this['path.cache'],
            pathinfo($this->file, PATHINFO_FILENAME),
            md5(filemtime($this->file) . filesize($this->file) . implode(';', $this->options)),
            pathinfo($this->file, PATHINFO_EXTENSION)
        );
    }

    /**
     * @return SimpleImage
     */
    protected function create()
    {
        $image = new SimpleImage($this->file);

        switch ($this->options['strategy']) {
            case 'thumbnail':
                $width = $this->options['w'] ?: $image->getWidth();
                $height = $this->options['h'] ?: $image->getHeight();
                $image->resize($width, $height);
                break;
            case 'best_fit':
                $width = $this->options['w'] ?: $image->getWidth();
                $height = $this->options['h'] ?: $image->getHeight();
                if (is_numeric($width) && is_numeric($height)) {
                    $image->bestFit($width, $height);
                }
                break;
            default:
                $width = $this->options['w'];
                $height = $this->options['h'];
                if (is_numeric($width) && is_numeric($height)) {
                    $image->thumbnail($width, $height);
                } elseif (is_numeric($width)) {
                    $image->fitToWidth($width);
                } elseif (is_numeric($height)) {
                    $image->fitToHeight($height);
                } else {
                    $width = $image->getWidth();
                    $height = $image->getHeight();
                    $image->thumbnail($width, $height);
                }
        }

        return $image;
    }
}
