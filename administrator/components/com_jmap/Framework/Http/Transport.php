<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Http;
/**
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;

/**
 * HTTP transport class interface.
 *
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @since 1.0
 */
interface Transport {

	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param   string   $method     The HTTP method for sending the request.
	 * @param   Uri     $uri        The URI to the resource to request.
	 * @param   mixed    $data       Either an associative array or a string to be sent with the request.
	 * @param   array    $headers    An array of request headers to send with the request.
	 * @param   integer  $timeout    Read timeout in seconds.
	 * @param   string   $userAgent  The optional user agent string to send with the request.
	 *
	 * @return  Response
	 *
	 * @since   11.3
	 */
	public function request($method, Uri $uri, $data = null, ?array $headers = null, $timeout = 120, $userAgent = null);
}
