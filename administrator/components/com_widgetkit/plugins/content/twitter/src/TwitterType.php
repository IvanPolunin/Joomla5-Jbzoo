<?php

namespace YOOtheme\Widgetkit\Content\Twitter;

use YOOtheme\Framework\Application;
use YOOtheme\Framework\Routing\ControllerInterface;
use YOOtheme\Widgetkit\Content\Type;

class TwitterType extends Type implements ControllerInterface
{
    /**
     * @param Application $app
     */
    public function main(Application $app)
    {
        parent::main($app);

        $app->on(
            'init',
            function ($event, $app) {
                $app['twitter'] = function () use ($app) {
                    $credentials = $app['plugins']->get('content/twitter')->config['credentials'];

                    return new TwitterOAuth($app, $credentials);
                };
            },
            -5
        );

        $this['controllers']->add($this);
    }

    /**
     * Redirects to twitter authorisation endpoint.
     *
     * @return response
     */
    public function redirectAction()
    {
        $data = $this['twitter']->getAuthorisationUri();

        $this['session']->set('twitter_token', $data['token']);

        return $this['response']->redirect($data['redirect_uri']);
    }

    /**
     * Resolve PIN to token action.
     *
     * @param $pin
     * @return response
     */
    public function pinResolveAction($pin)
    {
        $token = $this['session']->get('twitter_token', []);

        try {
            $response = $this['twitter']->resolveAuthPin($pin, $token);
            $token = [
                'token' => $response->oauth_token,
                'secret' => $response->oauth_token_secret,
            ];

            $this['option']->set('twitter_token', $token);

            return $this['response']->json(['success' => true]);
        } catch (\Exception $e) {
            return $this['response']->json(
                ['success' => false, 'message' => $e->getMessage()],
                400
            );
        }
    }

    /**
     * Deletes a token from the database.
     *
     * @return response
     */
    public function tokenDeleteAction()
    {
        unset($this['option']['twitter_token']);

        return $this['response']->json(['success' => true]);
    }

    public static function getRoutes()
    {
        return [
            ['twitter_auth', 'redirectAction', 'GET', ['access' => 'manage_widgetkit']],
            ['twitter_auth', 'pinResolveAction', 'POST', ['access' => 'manage_widgetkit']],
            ['twitter_auth', 'tokenDeleteAction', 'DELETE', ['access' => 'manage_widgetkit']],
        ];
    }
}
