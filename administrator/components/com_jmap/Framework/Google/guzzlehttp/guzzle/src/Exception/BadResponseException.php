<?php
namespace GuzzleHttp\Exception;

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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Exception when an HTTP error occurs (4xx or 5xx error)
 */
class BadResponseException extends RequestException
{
    public function __construct(
        string $message,
        RequestInterface $request,
        ResponseInterface $response,
        \Throwable $previous = null,
        array $handlerContext = []
    ) {
        parent::__construct($message, $request, $response, $previous, $handlerContext);
    }

    /**
     * Current exception and the ones that extend it will always have a response.
     */
    public function hasResponse(): bool
    {
        return true;
    }

    /**
     * This function narrows the return type from the parent class and does not allow it to be nullable.
     */
    public function getResponse(): ResponseInterface
    {
        /** @var ResponseInterface */
        return parent::getResponse();
    }
}
