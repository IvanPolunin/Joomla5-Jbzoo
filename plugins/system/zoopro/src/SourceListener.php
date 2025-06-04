<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

namespace YOOtheme\Builder\Joomla\Zoo;

use Joomla\CMS\Factory;
use YOOtheme\Builder\BuilderConfig;
use YOOtheme\Builder\Source;
use YOOtheme\Config;
use YOOtheme\Metadata;
use YOOtheme\Url;
use YOOtheme\Zoo;
use function YOOtheme\app;

class SourceListener
{
    /**
     * @param Source $source
     */
    public static function initSource($source)
    {
        $source->objectType('ZooDate', Type\DateType::config());
        $source->objectType('ZooDownload', Type\DownloadType::config());
        $source->objectType('ZooEmail', Type\EmailType::config());
        $source->objectType('ZooGallery', Type\GalleryType::config());
        $source->objectType('ZooGoogleMaps', Type\GoogleMapsType::config());
        $source->objectType('ZooImage', Type\ImageType::config());
        $source->objectType('ZooLink', Type\LinkType::config());
        $source->objectType('ZooRating', Type\RatingType::config());

        foreach (ApplicationHelper::getApplications() as $applications) {
            $source->queryType(Type\AppQueryType::config($source, $applications[0], $applications));
        }
    }

    public static function initCustomizer(Zoo $zoo, Config $config,  Metadata $metadata)
    {
        if (version_compare($config('theme.version'), '4', '<=')) {
            $config->add('customizer.zoo', static::getZooData());
            $config->add('customizer.templates', static::getTemplates());
        }

        $metadata->set('script:customizer.zoo', ['src' => Url::to('plugins/system/zoopro/zoopro.js'), 'version' => $zoo->zoo->version(),  'defer' => true]);
    }

    /**
     * @param BuilderConfig $config
     */
    public static function loadBuilderConfig($config) {
        $config->merge([
            'zoo' => static::getZooData(),
            'templates' => static::getTemplates()
        ]);
    }

    protected static function getZooData()
    {
        $data = [];
        $zoo = app(Zoo::class);
        foreach (ApplicationHelper::getApplications() as $applications) {
            foreach ($applications as $application) {
                $categories = [];
                foreach ($zoo->tree->buildList(0, $application->getCategoryTree(true, Factory::getUser()), [], '', '- ') as $category) {
                    $categories[] = ['value' => $category->id, 'text' => $category->treename];
                }

                $tags = [];
                foreach ($zoo->table->tag->getAll($application->id, '', '', '', 0, 0, true) as $tag) {
                    $tags[] = ['value' => $tag->name, 'text' => $tag->name];
                }

                $data[$application->id] = compact('categories', 'tags');
            }
        }
        return $data;
    }

    protected static function getTemplates() {
        $templates = [];

        foreach (ApplicationHelper::getApplications() as $applications) {
            foreach ($applications as $application) {

                $catField = [
                    'label' => 'Limit by Category',
                    'description' => 'The template is only assigned to items from the selected categories. Items from child categories are not included. Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> key to select multiple categories.',
                    'type' => 'select',
                    'default' => [],
                    'options' => [
                        ['evaluate' => "((config.zoo || {})[{$application->id}] || {})['categories']"],
                        ['evaluate' => "((api.builder.zoo || {})[{$application->id}] || {})['categories']"],
                    ],
                    'attrs' => [
                        'multiple' => true,
                        'class' => 'uk-height-small uk-resize-vertical',
                    ],
                ];

                $tagField = [
                    'label' => 'Limit by Tag',
                    'description' => 'The template is only assigned to items with the selected tags. Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> key to select multiple tags.',
                    'type' => 'select',
                    'default' => [],
                    'options' => [
                        ['evaluate' => "((config.zoo || {})[{$application->id}] || {})['tags']"],
                        ['evaluate' => "((api.builder.zoo || {})[{$application->id}] || {})['tags']"],
                    ],
                    'attrs' => [
                        'multiple' => true,
                        'class' => 'uk-height-small uk-resize-vertical',
                    ],
                ];

                $pagesField = [
                    'label' => 'Limit by Page Number',
                    'description' => 'The template is only assigned to the selected pages.',
                    'type' => 'select',
                    'options' => [
                        'All pages' => '',
                        'First page' => 'first',
                        'All except first page' => 'except_first',
                    ],
                ];

                $templates += [
                    "com_zoo.{$application->id}.frontpage" => [
                        'label' => "{$application->name} Frontpage",
                        'group' => 'ZOO Frontpage',
                        'fieldset' => [
                            'default' => [
                                'fields' => [
                                    'pages' => $pagesField,
                                ],
                            ],
                        ],
                    ],
                    "com_zoo.{$application->id}.category" => [
                        'label' => "{$application->name} Category",
                        'group' => 'ZOO Category',
                        'fieldset' => [
                            'default' => [
                                'fields' => [
                                    'catid' => $catField,
                                    'pages' => $pagesField,
                                ],
                            ],
                        ],
                    ],
                    "com_zoo.{$application->id}.tag" => [
                        'label' => "{$application->name} Tag",
                        'group' => 'ZOO Tag',
                        'fieldset' => [
                            'default' => [
                                'fields' => [
                                    'tag' => $tagField,
                                    'pages' => $pagesField,
                                ],
                            ],
                        ],
                    ],
                ];

                foreach ($application->getTypes() as $type) {
                    $templates["com_zoo.{$application->id}.{$type->id}"] = [
                        'label' => "{$application->name} {$type->name}",
                        'group' => 'ZOO Item',
                        'fieldset' => [
                            'default' => [
                                'fields' => [
                                    'catid' => $catField,
                                    'tag' => $tagField,
                                ],
                            ],
                        ],
                    ];
                }
            }
        }

        return $templates;
    }
}
