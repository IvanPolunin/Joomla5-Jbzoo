<?php

namespace YOOtheme\Widgetkit;

use YOOtheme\Framework\Application as BaseApplication;
use YOOtheme\Framework\Event\EventSubscriberInterface;
use YOOtheme\Widgetkit\Content\ContentProvider;
use YOOtheme\Widgetkit\Image\ImageProvider;
use YOOtheme\Widgetkit\Helper\Shortcode;

class Application extends BaseApplication implements EventSubscriberInterface
{
    const REGEX_URL = '/
                        (?P<attr>href|src|poster)=             # match the attribute
                        ([\"\'])                               # start with a single or double quote
                        (?!\/|\#|[a-zA-Z0-9\-\.]+\:)           # make sure it is a relative path
                        (?P<url>[^\"\'>]+)                     # match the actual src value
                        \2                                     # match the previous quote
                       /xiU';

    /**
     * Constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this['content'] = new ContentProvider($this);
        $this['image'] = new ImageProvider($this);
        $this['shortcode'] = new Shortcode();
        $this['types'] = new Collection();
        $this['widgets'] = new Collection();

        $this->extend('locator', function ($locator, $app) {
            return $locator->addPath('', $app['path']);
        });

        $this->extend('translator', function ($translator, $app) {
            return $translator->addResource('languages/' . $app['locale'] . '.json');
        });

        $this->on('boot', function ($event, $app) {
            $app['plugins']->addPath($app['path'] . '/plugins/*/*/plugin.php');
            $app['locator']->addPath('widgetkit', dirname(__DIR__));
            $app['locator']->addPath('~', $app['request']->getBasePath());

            foreach ($app['templates'] as $path) {
                $app['locator']->addPath('plugins', $path);
                $app['plugins']->addPath($path . '/*/*/plugin.php');
            }
        });

        $this['events']->subscribe($this);
    }

    public function init()
    {
        // controller
        $this['controllers']->add('YOOtheme\Widgetkit\Controller\ContentController');
        $this['controllers']->add('YOOtheme\Widgetkit\Controller\ImageController');
        $this['controllers']->add('YOOtheme\Widgetkit\Controller\CacheController');

        // uikit scripts
        $this['scripts']->register('uikit', 'assets/lib/uikit/dist/js/uikit.min.js');
        $this['scripts']->register('uikit-icons', 'assets/lib/uikit/dist/js/uikit-icons.min.js');

        // site event
        if (!$this['admin']) {
            $this->trigger('init.site', [$this]);
        }
    }

    public function initSite()
    {
        // styles
        $this->on('view', function ($event, $app) {
            $support = $app['config']->get('theme.support');

            // uikit
            if ($support === 'noconflict') {
                $app['locator']->addPath(
                    'assets/lib/uikit',
                    dirname(__DIR__) . '/assets/lib/wkuikit'
                );
                $app['styles']->add('widgetkit-site', 'assets/css/site.wk.css');
            } elseif ($support === 'scoped') {
                $app['styles']->add('widgetkit-site', 'assets/css/site.css');
            }

            if ($support === 'scoped' || $support === 'noconflict') {
                $this['scripts']->add('uikit');
                $this['scripts']->add('uikit-icons');
            }
        });
    }

    public function initAdmin()
    {
        // angular
        $this['angular']->set('name', 'widgetkit');
        $this['angular']->set('adminBase', $this['url']->to('widgetkit'));
        $this['angular']->set('assetsBase', $this['url']->to('assets'));
        $this['angular']->set('settingsPage', $this['config']->get('settings-page'));
        $this['angular']->set('types', $this['types']->toArray());
        $this['angular']->set('widgets', $this['widgets']->toArray());
        $this['angular']->set('images', [
            'audio' => $this['url']->to('assets/images/preview-audio.svg'),
            'video' => $this['url']->to('assets/images/preview-video.svg'),
            'iframe' => $this['url']->to('assets/images/preview-iframe.svg'),
            'placeholder' => $this['url']->to('assets/images/preview-placeholder.svg'),
        ]);
        $this['angular']->addTemplate('picker', 'views/picker.php', true);

        // if not triggered from e.g. frontend module edit
        if ($this['admin']) {
            $this['scripts']->add('uikit');
            $this['scripts']->add('uikit-icons');
        }

        // widgetkit
        $this['styles']->add('widgetkit-admin', 'assets/css/admin.css');
        $this['styles']->add('uieditor-codemirr-css', 'assets/lib/codemirror/codemirror.css');
        $this['styles']->add('uieditor-hint', 'assets/lib/codemirror/hint.css');
        $this['scripts']->add(
            'widgetkit-admin',
            'assets/js/admin.js',
            array_merge($this['admin'] ? ['uikit', 'uikit-icons'] : [], [
                'application-translator',
                'angular',
                'angular-resource',
                'angular-touch',
            ])
        );

        // check if PHP GD is available
        $this['angular']->set('GD', extension_loaded('gd') && function_exists('gd_info'));

        // google maps key
        $this['angular']->set('googlemapseapikey', $this['config']->get('googlemapseapikey'));
    }

    public function convertUrls($content)
    {
        $url = $this['url'];

        return preg_replace_callback(
            self::REGEX_URL,
            function ($matches) use ($url) {
                if (strpos($matches['url'], 'index.php') !== 0) {
                    $matches['url'] = $url->to($matches['url']);
                }

                return sprintf('%s="%s"', $matches['attr'], $matches['url']);
            },
            $content
        );
    }

    public function renderWidget(array $attrs)
    {
        if (
            !isset($attrs['id']) ||
            !($content = $this['content']->get($attrs['id'])) ||
            !($data = $content['_widget']) ||
            !isset($data['name'], $data['data']) ||
            !($widget = $this['widgets']->get($data['name']))
        ) {
            return '';
        }

        $data = array_map(function ($value) {
            return in_array($value, ['true', 'false'], true) ? $value == 'true' : $value;
        }, $data['data']);

        $content = $this->convertUrls($widget->render($content, $data));

        $support = $this['config']->get('theme.support');

        if ($support !== 'uikit3') {
            $content = "<div class=\"{wk}-scope\">{$content}</div>";
        }

        return str_replace('{wk}', $support === 'noconflict' ? 'wk' : 'uk', $content);
    }

    public function install()
    {
        $sql = "CREATE TABLE IF NOT EXISTS @widgetkit (
            id int(10) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(255) NOT NULL,
            data longtext NOT NULL,
            PRIMARY KEY  id (id)
        ) DEFAULT CHARSET=utf8;";

        if ($this['db']->executeQuery($sql) === false) {
            throw new \RuntimeException('Unable to create Widgetkit database.');
        }
    }

    public function uninstall()
    {
        $sql = 'DROP TABLE IF EXISTS @widgetkit';

        if ($this['db']->executeQuery($sql) === false) {
            throw new \RuntimeException('Unable to delete Widgetkit database.');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'init' => ['init', -5],
            'init.site' => 'initSite',
            'init.admin' => 'initAdmin',
            'view.render' => 'viewRender',
        ];
    }
}
