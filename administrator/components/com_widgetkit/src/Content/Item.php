<?php

namespace YOOtheme\Widgetkit\Content;

use YOOtheme\Framework\Application;

class Item implements ItemInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param Application $app
     * @param array       $data
     */
    public function __construct(Application $app, array $data)
    {
        $this->app = $app;
        $this->data = $data;
    }

    /**
     * Get a value or option by key.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (strpos($key, '.') === false) {
            return isset($this->data[$key]) ? $this->data[$key] : $default;
        }

        list($key, $option) = explode('.', $key, 2);

        if (
            isset($this->data[$key]) &&
            is_array($this->data[$key]) &&
            isset($this->data[$key][$option])
        ) {
            return $this->data[$key][$option];
        }

        if (
            isset($this->data['options'], $this->data['options'][$key]) &&
            is_array($this->data['options'][$key]) &&
            isset($this->data['options'][$key][$option])
        ) {
            return $this->data['options'][$key][$option];
        }

        return $default;
    }

    /**
     * Get escaped value or option by key.
     *
     * @param  string $key
     * @return mixed
     */
    public function escape($key)
    {
        if ($value = $this->get($key)) {
            $value = htmlspecialchars($value);

            // email cloaking fix
            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $value = str_replace('@', '&#064;', $value);
            }

            return $value;
        }
    }

    /**
     * Get image tag for a url.
     *
     * @param  string $key
     * @param  array  $attrs
     * @return bool
     */
    public function img($key, array $attrs = [])
    {
        if ($value = $this->get($key)) {
            $value = str_replace(' ', '%20', $value);

            if (
                ($image = $this->app['image']->create($value)) and
                ($srcSets = $image->getSrcSetUrls(['2x', '3x']))
            ) {
                $attrs['srcset'] = implode(',', $srcSets);
            }

            return sprintf('<img%s>', $this->attrs(['src' => $value], $attrs));
        }
    }

    /**
     * Get image thumbnail tag for a url.
     *
     * @param  string $key
     * @param  string $width
     * @param  string $height
     * @param  array  $attrs
     * @param  bool   $url
     * @return string
     */
    public function thumbnail($key, $width, $height, array $attrs = [], $url = false)
    {
        if ($value = $this->get($key)) {
            if ($image = $this->app['image']->create($value)) {
                $image->setOptions(['w' => $width, 'h' => $height]);

                if ($srcSets = $image->getSrcSets(['2x', '3x'])) {
                    array_walk($srcSets, function (&$img, $density) use ($width, $height) {
                        $img =
                            $img
                                ->setOptions([
                                    'w' => $width * $density[0],
                                    'h' => $height * $density[0],
                                ])
                                ->thumbnail() .
                            ' ' .
                            $density;
                    });

                    $attrs['srcset'] = implode(',', $srcSets);
                }

                $value = $image->cache();
            }

            return $url ? $value : sprintf('<img%s>', $this->attrs(['src' => $value], $attrs));
        }
    }

    /**
     * Get video tag for a url.
     *
     * @param  string $key
     * @param  array  $attrs
     * @return bool
     */
    public function video($key, array $attrs = [])
    {
        if ($value = $this->get($key)) {
            return sprintf('<video%s></video>', $this->attrs(['src' => $value], $attrs));
        }
    }

    /**
     * Get audio tag for a url.
     *
     * @param  string $key
     * @param  array  $attrs
     * @return bool
     */
    public function audio($key, array $attrs = [])
    {
        if ($value = $this->get($key)) {
            return sprintf('<audio%s></audio>', $this->attrs(['src' => $value], $attrs));
        }
    }

    /**
     * Get image, video or audio tag for a url.
     *
     * @param  string $key
     * @param  array  $attrs
     * @return bool
     */
    public function media($key, array $attrs = [])
    {
        switch ($this->type($key)) {
            case 'image':
                return $this->img($key, $attrs);

            case 'video':
                return $this->video($key, $attrs);

            case 'audio':
                return $this->audio($key, $attrs);

            case 'iframe':
                $src = $this->get($key);
                $scheme = parse_url($src);

                if (
                    preg_match('/(\/\/.*?youtube\.[a-z]+)\/watch\?v=([^&]+)&?(.*)/i', $src, $m) ||
                    preg_match('/(\/\/.*?youtu\.be)\/([^\?]+)(.*)/i', $src, $m)
                ) {
                    $src =
                        '//www.youtube.com/embed/' .
                        $m[2] .
                        (strpos($m[3], '?') === false ? '?' : '') .
                        $m[3];
                    $src .= '&wmode=transparent';
                } elseif (preg_match('/(\/\/.*?)vimeo\.[a-z]+\/(?:\w*\/)*(\d+)/i', $src, $m)) {
                    $src =
                        '//player.vimeo.com/video/' .
                        $m[2] .
                        (isset($scheme['query']) && $scheme['query'] ? '?' . $scheme['query'] : '');
                }

                return sprintf(
                    '<iframe%s></iframe>',
                    $this->attrs(['src' => $src, 'allowfullscreen'], $attrs)
                );
        }
    }

    /**
     * Get media type for a url.
     *
     * @param  string $key
     * @return string
     */
    public function type($key)
    {
        if (!($value = $this->get($key)) or !is_string($value)) {
            return;
        }

        $url = array_merge(['host' => '', 'path' => ''], parse_url($value));

        if (preg_match('/\.(avif|gif|a?png|jpe?g|svg|webp)$/i', $url['path'])) {
            return 'image';
        } elseif (preg_match('/\.(mp4|ogv|webm)$/i', $url['path'])) {
            return 'video';
        } elseif (preg_match('/\.(mp3|ogg|wav)$/i', $url['path'])) {
            return 'audio';
        } elseif (preg_match('/(vimeo\.com|youtu(be\.com|\.be))$/i', $url['host'])) {
            return 'iframe';
        }
    }

    /**
     * Get tag attributes form array.
     *
     * @param  array $attrs
     * @return string
     */
    public function attrs($attrs)
    {
        $html = [];
        $attrs = call_user_func_array('array_merge', func_get_args());

        foreach ($attrs as $key => $value) {
            if (is_numeric($key)) {
                $html[] = $value;
            } elseif ($value === true) {
                $html[] = $key;
            } elseif ($value !== '') {
                $html[] = sprintf(
                    '%s="%s"',
                    $key,
                    htmlspecialchars($value ?: '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false)
                );
            }
        }

        return $html ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Checks if a key exists.
     *
     * @param  string $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Gets a value by key.
     *
     * @param  string $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Sets a value.
     *
     * @param string $key
     * @param string $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Unset a value.
     *
     * @param string $key
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Returns an iterator for item keys.
     *
     * @return \ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator(array_keys($this->data));
    }
}
