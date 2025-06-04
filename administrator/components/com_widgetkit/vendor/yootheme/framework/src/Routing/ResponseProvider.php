<?php

namespace YOOtheme\Framework\Routing;

class ResponseProvider
{
    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * Constructor.
     *
     * @param UrlGenerator $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Returns a response.
     *
     * @param  mixed $content
     * @param  int   $status
     * @param  array $headers
     * @return Response
     */
    public function create($content = '', $status = 200, $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    /**
     * Returns a response.
     *
     * @param  mixed $content
     * @param  int   $status
     * @param  array $headers
     * @return Response
     */
    public function raw($content = '', $status = 200, $headers = [])
    {
        return new RawResponse($content, $status, $headers);
    }

    /**
     * Returns a redirect response.
     *
     * @param  string $url
     * @param  array  $parameters
     * @param  int    $status
     * @param  array  $headers
     * @return RedirectResponse
     */
    public function redirect($url, $parameters = [], $status = 302, $headers = [])
    {
        return new RedirectResponse($this->url->to($url, $parameters), $status, $headers);
    }

    /**
     * Returns a JSON response.
     *
     * @param  string|array $data
     * @param  int          $status
     * @param  array        $headers
     * @return JsonResponse
     */
    public function json($data = [], $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }
}
