<?php

namespace YOOtheme\Widgetkit\Content\instagram;

use YOOtheme\Framework\Application;
use YOOtheme\Framework\ApplicationAware;

class InstagramApp extends ApplicationAware
{
    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function fetch($params, $content)
    {
        // Cache settings
        $now = time();
        $expires = 6 * 3600; // cache time 6 hours
        $maxItems = $params['limit'] ?: '12';

        $posts = [];

        // Cache invalid?
        if (
            !isset($content['hash']) || // never cached
            $now - $content['hashed'] > $expires || // cached values too old
            md5(serialize($params)) != $content['hash'] // content settings have changed
        ) {
            try {
                $data = $this->getUserData($params['username'], $maxItems);

                $user = $data['user'];
                $items = $data['edges'];

                foreach ($items as $item) {
                    $post = [
                        'title' =>
                            $params['title'] === 'username'
                                ? $user['username']
                                : (empty($user['username'])
                                    ? $user['full_name']
                                    : "{$user['full_name']} ({$user['username']})"),
                        'content' =>
                            @$item['node']['edge_media_to_caption']['edges'][0]['node']['text'] ?:
                            '',
                        'date' => date('d-m-Y H:i:s O', $item['node']['taken_at_timestamp']),
                        'link' => "//instagram.com/p/{$item['node']['shortcode']}/?taken-by={$user['username']}",
                        'location' => null,
                        'media' => $item['node']['thumbnail_src'],
                        'options' => [
                            'media' => [
                                'width' => $item['node']['dimensions']['width'],
                                'height' => $item['node']['dimensions']['height'],
                            ],
                        ],
                    ];

                    // separate the hashtags
                    $post['content'] = preg_replace('/#/', ' #', $post['content']);
                    // make hashtags clickable
                    $post['content'] = preg_replace(
                        '/(?<=^|(?<=[^a-zA-Z0-9-_\.]))\#([\P{Z}]+)/',
                        '<a href="https://instagram.com/explore/tags/$1">#$1</a>',
                        $post['content']
                    );

                    // make user names clickable
                    $post['content'] = preg_replace(
                        '/(?<=^|(?<=[^a-zA-Z0-9-_\.]))\@([\P{Z}]+)/',
                        '<a href="https://instagram.com/$1">@$1</a>',
                        $post['content']
                    );

                    // convert emoticons to UTF-8 code
                    $post['content'] = mb_convert_encoding($post['content'], 'UTF-8');

                    //                    if($item['type'] == 'video'){
                    //                        $post['media'] = $item['videos']['standard_resolution']['url'];
                    //                        $post['options']['media'] = array(
                    //                            'poster' => $item['images']['standard_resolution']['url'],
                    //                            'width'  => $item['videos']['standard_resolution']['width'],
                    //                            'height' => $item['videos']['standard_resolution']['height']
                    //                        );
                    //                    }

                    if ($params['title'] == 'username') {
                        $post['title'] = $user['username'];
                    } elseif ($params['title'] == 'fullname') {
                        $post['title'] = $user['full_name'];
                    }

                    $posts[] = $post;
                }

                // write cache
                $content['prepared'] = json_encode($posts);
                $content['hash'] = md5(serialize($params));
                $content['hashed'] = $now;
                unset($content['error']);

                $this->app['content']->save($content->toArray());

                return $posts;
            } catch (\Exception $e) {
                // Fallback to cache and log of API error
                $content['error'] = $e->getMessage();
                $this->app['content']->save($content->toArray());
            }
        }

        // read from cache
        $posts = json_decode($content['prepared'], true);

        return $posts ?: [];
    }

    protected function getUserData($userName, $maxItems = 12)
    {
        if (!function_exists('curl_exec') && ini_get('open_basedir') !== '') {
            throw new \Exception('Curl not enabled.');
        }

        $url = "https://www.instagram.com/{$userName}/";

        $conn = curl_init();
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn, CURLOPT_AUTOREFERER, false);
        curl_setopt($conn, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($conn, CURLOPT_VERBOSE, 0);
        curl_setopt($conn, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt(
            $conn,
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36'
        );

        curl_setopt($conn, CURLOPT_URL, $url);

        $content = curl_exec($conn);

        if (curl_errno($conn)) {
            throw new \Exception('Curl: ' . curl_error($conn));
        }

        if (strpos($content, '-cx-PRIVATE-Page__main') !== false) {
            throw new \Exception('Account invalid');
        }

        if (strpos($content, 'edge_owner_to_timeline_media') === false) {
            throw new \Exception('No media found for this account');
        }

        if (
            !preg_match(
                '/<script.*?>\s*window\._sharedData\s*=\s*(.+?)\s*;\s*<\/script>/',
                $content,
                $matches
            )
        ) {
            throw new \Exception('Failed to extract JSON data');
        }

        $data = json_decode($matches[1], true);

        if (!isset($data['entry_data']['ProfilePage'])) {
            throw new \Exception('Instagram account requires authorization.');
        }

        $user = $data['entry_data']['ProfilePage'][0]['graphql']['user'];
        $edges = $user['edge_owner_to_timeline_media']['edges'];

        if ($maxItems > 12) {
            // Build GraphQL query
            $query = http_build_query([
                'query_hash' => '42323d64886122307be10013ad2dcc44',
                'variables' => json_encode([
                    'id' => $user['id'],
                    'first' => $maxItems,
                    'after' => null,
                ]),
            ]);

            $url = "https://www.instagram.com/graphql/query/?{$query}";

            curl_setopt($conn, CURLOPT_URL, $url);
            curl_setopt($conn, CURLOPT_REFERER, "https://www.instagram.com/{$userName}/");

            $content = curl_exec($conn);

            if (!curl_errno($conn)) {
                $response = json_decode($content, true);
                if (isset($response['data']['user']['edge_owner_to_timeline_media']['edges'])) {
                    $edges = $response['data']['user']['edge_owner_to_timeline_media']['edges'];
                }
            }
        }

        curl_close($conn);

        return $response = ['user' => $user, 'edges' => $edges];
    }

    /**
     * Hashes request parameters.
     *
     * @param $params
     * @return string
     */
    protected function hash($params)
    {
        $fields = [$params];

        return md5(serialize($fields));
    }
}
