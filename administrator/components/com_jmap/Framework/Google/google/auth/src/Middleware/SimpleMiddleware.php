<?php
namespace Google\Auth\Middleware;
/**
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

/**
 * SimpleMiddleware is a Guzzle Middleware that implements Google's Simple API
 * access.
 *
 * Requests are accessed using the Simple API access developer key.
 */
class SimpleMiddleware
{
    /**
     * @var array
     */
    private $config;

    /**
     * Create a new Simple plugin.
     *
     * The configuration array expects one option
     * - key: required, otherwise InvalidArgumentException is thrown
     *
     * @param array $config Configuration array
     */
    public function __construct(array $config)
    {
        if (!isset($config['key'])) {
            throw new \InvalidArgumentException('requires a key to have been set');
        }

        $this->config = array_merge(['key' => null], $config);
    }

    /**
     * Updates the request query with the developer key if auth is set to simple.
     *
     *   use Google\Auth\Middleware\SimpleMiddleware;
     *   use GuzzleHttp\Client;
     *   use GuzzleHttp\HandlerStack;
     *
     *   $my_key = 'is not the same as yours';
     *   $middleware = new SimpleMiddleware(['key' => $my_key]);
     *   $stack = HandlerStack::create();
     *   $stack->push($middleware);
     *
     *   $client = new Client([
     *       'handler' => $stack,
     *       'base_uri' => 'https://www.googleapis.com/discovery/v1/',
     *       'auth' => 'simple'
     *   ]);
     *
     *   $res = $client->get('drive/v2/rest');
     *
     * @param callable $handler
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            // Requests using "auth"="scoped" will be authorized.
            if (!isset($options['auth']) || $options['auth'] !== 'simple') {
                return $handler($request, $options);
            }

            $query = Psr7\parse_query($request->getUri()->getQuery());
            $params = array_merge($query, $this->config);
            $uri = $request->getUri()->withQuery(Psr7\build_query($params));
            $request = $request->withUri($uri);

            return $handler($request, $options);
        };
    }
}
