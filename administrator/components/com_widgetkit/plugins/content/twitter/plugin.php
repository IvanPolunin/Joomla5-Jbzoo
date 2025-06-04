<?php

return [
    'name' => 'content/twitter',

    'main' => 'YOOtheme\\Widgetkit\\Content\\Twitter\\TwitterType',

    'autoload' => [
        'YOOtheme\\Widgetkit\\Content\\Twitter\\' => 'src',
    ],

    'config' => [
        'name' => 'twitter',
        'label' => 'Twitter',
        'icon' => 'plugins/content/twitter/content.svg',
        'item' => ['title', 'content', 'media', 'link'],
        'data' => [
            'include_rts' => true,
            'include_replies' => false,
            'only_media' => false,
            'source' => 'user',
            'blacklist' => '',
            'limit' => 5,
            'title' => 'name',
        ],
        'credentials' => [
            'consumer_key' => 'jtSjLhhoh5hFu86tRauqWfyv4',
            'consumer_secret' => 'uKiiyEm3fzcIK6rhL7A208ALaNx94QyBxgDq53SFAUdWc0zRYu',
        ],
    ],

    'items' => function ($items, $content, $app) {
        $token = $app['option']->get('twitter_token', []);

        // determine api method and parameters
        $params = ['limit' => (int) $content['limit'], 'include_rts' => $content['include_rts']];

        if ($content['source'] == 'user') {
            $method = 'statuses/user_timeline';
            $params['screen_name'] = trim($content['search'], '@');
            $params['exclude_replies'] = !$content['include_replies'];
        } else {
            $method = 'search/tweets';
            $params['q'] = $content['search'];
        }

        // fetch tweets
        try {
            $tweets = $app['twitter']->fetch($method, $token, $params, $content);

            foreach ($tweets as $tweet) {
                $items->add($tweet);
            }
        } catch (Exception $e) {
            $items->add([
                'title' => 'Twitter Error',
                'content' => 'Fetching tweets failed with message: ' . $e->getMessage(),
            ]);
        }
    },

    'events' => [
        'init.admin' => function ($event, $app) {
            $app['scripts']->add(
                'widgetkit-twitter-controller',
                'plugins/content/twitter/assets/controller.js'
            );
            $app['angular']->addTemplate('twitter.edit', 'plugins/content/twitter/views/edit.php');
        },
    ],
];
