<?php

namespace YOOtheme\Widgetkit;

class Update300beta02 implements Update
{
    public function run($app)
    {
        try {
            $provider = $app['content'];
            $contents = $provider->findAll();

            foreach ($contents as $content) {
                static::updateWidget($content);
                $provider->save(array_filter($content->toArray()));
            }
        } catch (\Exception $e) {
            throw new \Exception('Error executing update to 3.0.0-beta.0.2. (' . $e->getMessage() . ')', $e->getCode(), $e);
        }
    }

    public static function updateWidget($content)
    {
        $data = $content->getData();

        if (isset($data['_widget'])) {
            $widget = &$data['_widget'];

            if (isset($widget['data'])) {
                $settings = &$widget['data'];

                // Accordion, Grid, Grid Slider, Map, Slideshow Panel, Switcher, Switcher Panel
                if (isset($settings['media_breakpoint'])) {
                    switch ($settings['media_breakpoint']) {
                        case 'small':
                            $settings['media_breakpoint'] = 's';
                            break;
                        case 'medium':
                            $settings['media_breakpoint'] = 'm';
                            break;
                        case 'large':
                            $settings['media_breakpoint'] = 'l';
                            break;
                        case 'xlarge':
                            $settings['media_breakpoint'] = 'xl';
                            break;
                    }
                }

                // Grid Stack
                if (isset($settings['breakpoint'])) {
                    switch ($settings['breakpoint']) {
                        case 'small':
                            $settings['breakpoint'] = 's';
                            break;
                        case 'medium':
                            $settings['breakpoint'] = 'm';
                            break;
                        case 'large':
                            $settings['breakpoint'] = 'l';
                            break;
                        case 'xlarge':
                            $settings['breakpoint'] = 'xl';
                            break;
                    }
                }

                // Accordion, Grid, Grid Slider, Map, Slideshow Panel, Switcher, Switcher Panel
                if (isset($settings['media_width'])) {
                    switch ($settings['media_width']) {
                        case '3-10':
                            $settings['media_width'] = '1-3';
                            break;
                    }
                }

                // NOT Map, Popover
                // Grid Stack, Switcher, Parallax
                if (
                    in_array($widget['name'], ['grid-stack', 'switcher', 'parallax']) &&
                    isset($settings['width'])
                ) {
                    switch ($settings['width']) {
                        // Grid Stack, Switcher
                        case '3-10':
                            $settings['width'] = '1-3';
                            break;
                        // Grid Stack, Parallax
                        case '7-10':
                            $settings['width'] = '2-3';
                            break;
                        // Parallax
                        case '9-10':
                            $settings['width'] = '4-5';
                            break;
                    }
                }

                // Parallax
                if (isset($settings['width_small'])) {
                    switch ($settings['width_small']) {
                        case '7-10':
                            $settings['width_small'] = '2-3';
                            break;
                        case '9-10':
                            $settings['width_small'] = '4-5';
                            break;
                    }
                }
                if (isset($settings['width_medium'])) {
                    switch ($settings['width_medium']) {
                        case '7-10':
                            $settings['width_medium'] = '2-3';
                            break;
                        case '9-10':
                            $settings['width_medium'] = '4-5';
                            break;
                    }
                }
                if (isset($settings['width_large'])) {
                    switch ($settings['width_large']) {
                        case '7-10':
                            $settings['width_large'] = '2-3';
                            break;
                        case '9-10':
                            $settings['width_large'] = '4-5';
                            break;
                    }
                }

                // Accordion, Grid, Grid Stack, Map, Popover, Slideset, Switcher, Switcher Panel
                if (isset($settings['media_animation'])) {
                    switch ($settings['media_animation']) {
                        case 'scale':
                        case 'spin':
                            $settings['media_animation'] = 'scale-up';
                            break;
                        case 'fade':
                        case 'grayscale':
                            $settings['media_animation'] = 'none';
                            break;
                    }
                }

                // Gallery, Slider
                if (isset($settings['image_animation'])) {
                    switch ($settings['image_animation']) {
                        case 'scale':
                        case 'spin':
                            $settings['image_animation'] = 'scale-up';
                            break;
                        case 'fade':
                        case 'grayscale':
                            $settings['image_animation'] = 'none';
                            break;
                    }
                }

                // NOT Grid Stack, Switcher
                // Gallery, Grid, Grid Slider, Popover, Slideset, Slideshow Panel, Switcher Panel
                if (
                    in_array($widget['name'], [
                        'gallery',
                        'grid',
                        'grid-slider',
                        'popover',
                        'slideset',
                        'slideshow-panel',
                        'switcher-panel',
                    ]) &&
                    isset($settings['panel'])
                ) {
                    if ($settings['panel'] == 'box') {
                        $settings['panel'] = 'default';
                    }
                }

                // Gallery, Grid, Grid Slider, Grid Stack, Popover, Slideset, Slideshow Panel, Switcher, Switcher Panel
                if (isset($settings['title_size'])) {
                    if ($settings['title_size'] == 'panel') {
                        $settings['title_size'] = 'h3';
                    }
                    if ($settings['title_size'] == 'large') {
                        $settings['title_size'] = 'medium';
                    }
                }

                // Gallery
                if (isset($settings['lightbox_title_size'])) {
                    if ($settings['lightbox_title_size'] == 'panel') {
                        $settings['lightbox_title_size'] = 'h3';
                    }
                    if ($settings['lightbox_title_size'] == 'large') {
                        $settings['lightbox_title_size'] = 'medium';
                    }
                }

                if (isset($settings['lightbox'])) {
                    if ($settings['lightbox'] === true) {
                        $settings['lightbox'] = 'default';
                    }
                }

                // List
                if (isset($settings['list'])) {
                    switch ($settings['list']) {
                        case 'line':
                            $settings['list'] = 'divider';
                            break;
                        case 'space':
                            $settings['list'] = 'large';
                            break;
                    }
                }

                // NOT Gallery, Grid, Grid Slider, Switcher, Switcher Panel
                // Slideshow, Slideshow Panel
                if (
                    in_array($widget['name'], ['slideshow', 'slideshow-panel']) &&
                    isset($settings['animation'])
                ) {
                    switch ($settings['animation']) {
                        case 'scroll':
                            $settings['animation'] = 'slide';
                            break;
                        case 'swipe':
                            $settings['animation'] = 'pull';
                            break;
                        case 'slice-up':
                        case 'slice-down':
                        case 'slice-up-down':
                        case 'fold':
                        case 'puzzle':
                        case 'boxes':
                        case 'boxes-reverse':
                        case 'random-fx':
                            $settings['animation'] = 'fade';
                            break;
                    }
                }

                // Grid Slider
                if (isset($settings['slide_animation'])) {
                    switch ($settings['slide_animation']) {
                        case 'scroll':
                            $settings['slide_animation'] = 'slide';
                            break;
                        case 'swipe':
                            $settings['slide_animation'] = 'pull';
                            break;
                        case 'slice-up':
                        case 'slice-down':
                        case 'slice-up-down':
                        case 'fold':
                        case 'puzzle':
                        case 'boxes':
                        case 'boxes-reverse':
                        case 'random-fx':
                            $settings['slide_animation'] = 'fade';
                            break;
                    }
                }

                // NOT any other widgets
                // Switcher, Switcher Panel
                if (
                    in_array($widget['name'], ['switcher', 'switcher-panel']) &&
                    isset($settings['animation'])
                ) {
                    switch ($settings['animation']) {
                        case 'scale':
                            $settings['animation'] = 'scale-up';
                            break;
                        case 'slide-horizontal':
                            $settings['animation'] = 'slide-left, {wk}-animation-slide-right';
                            break;
                        case 'slide-vertical':
                            $settings['animation'] = 'slide-top, {wk}-animation-slide-bottom';
                            break;
                    }
                }

                // Slideshow, Slideshow Panel
                if (isset($settings['kenburns_animation'])) {
                    switch ($settings['kenburns_animation']) {
                        case 'animation-middle-center':
                            $settings['kenburns_animation'] = 'transform-origin-middle-center';
                            break;
                        case 'animation-bottom-center':
                            $settings['kenburns_animation'] = 'transform-origin-bottom-center';
                            break;
                        case 'animation-top-center':
                            $settings['kenburns_animation'] = 'transform-origin-top-center';
                            break;
                        case 'animation-middle-right':
                            $settings['kenburns_animation'] = 'transform-origin-middle-right';
                            break;
                        case 'animation-middle-left':
                            $settings['kenburns_animation'] = 'transform-origin-middle-left';
                            break;
                        case 'animation-bottom-right':
                            $settings['kenburns_animation'] = 'transform-origin-bottom-right';
                            break;
                        case 'animation-bottom-left':
                            $settings['kenburns_animation'] = 'transform-origin-bottom-left';
                            break;
                        case 'animation-top-right':
                            $settings['kenburns_animation'] = 'transform-origin-top-right';
                            break;
                        case 'animation-top-left':
                            $settings['kenburns_animation'] = 'transform-origin-top-left';
                            break;
                    }
                }

                // Gallery
                $convertIcons = function ($icon) {
                    switch ($icon) {
                        case 'info-circle':
                            return 'info';
                        case 'search-plus':
                            return 'search';
                        case 'angle-right':
                        case 'chevron-circle-right':
                            return 'chevron-right';
                        case 'angle-double-right':
                            return 'chevron-double-right';
                        case 'arrow-circle-right':
                        case 'arrow-circle-o-right':
                        case 'long-arrow-right':
                        case 'hand-o-right':
                            return 'arrow-right';
                        case 'caret-right':
                        case 'caret-square-o-right':
                            return 'triangle-right';
                        case 'plus-square':
                        case 'plus-square-o':
                            return 'plus-circle';
                        case 'share':
                        case 'share-square':
                        case 'share-square-o':
                            return 'forward';
                        case 'arrows-alt':
                        case 'lightbulb-o':
                        case 'eye':
                        case 'external-link':
                        case 'external-link-square':
                            return 'plus';
                        default:
                            return $icon;
                    }
                };
                if (isset($settings['link_icon'])) {
                    $settings['link_icon'] = $convertIcons($settings['link_icon']);
                }
                if (isset($settings['lightbox_icon'])) {
                    $settings['lightbox_icon'] = $convertIcons($settings['link_icon']);
                }

                // Gallery, Grid, Grid Slider
                if (
                    in_array($widget['name'], ['gallery', 'grid', 'grid-slider']) &&
                    isset($settings['grid'])
                ) {
                    if ($settings['grid'] == 'dynamic') {
                        $settings['grid'] = 'masonry';
                        $settings['gutter'] = 'small';
                        $settings['parallax'] = false;
                    } else {
                        $settings['filter'] = 'none';
                    }
                }

                // Slideshow, Slideshow Panel
                if (
                    in_array($widget['name'], ['slideshow', 'slideshow-panel']) &&
                    isset($settings['fullscreen'])
                ) {
                    if ($settings['fullscreen'] == true) {
                        $settings['height'] = 'viewport';
                    }
                    unset($settings['fullscreen']);
                }

                // Slideset
                if ($widget['name'] == 'slideset' && isset($settings['slidenav'])) {
                    if ($settings['slidenav'] == 'above') {
                        $settings['slidenav'] = 'default';
                    }
                    if ($settings['slidenav'] == 'bottom') {
                        $settings['slidenav'] = 'below';
                    }
                }

                // Type cast column settings
                foreach (['small', 'medium', 'large', 'xlarge'] as $breakpoint) {
                    if (
                        isset($settings["columns_{$breakpoint}"]) &&
                        is_numeric($settings["columns_{$breakpoint}"])
                    ) {
                        $settings["columns_{$breakpoint}"] =
                            (string) $settings["columns_{$breakpoint}"];
                    }
                }
            }
        }

        $content->setData($data);

        return $content;
    }
}

return new Update300beta02();
