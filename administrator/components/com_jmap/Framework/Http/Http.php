<?php
namespace JExtstore\Component\JMap\Administrator\Framework;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use JExtstore\Component\JMap\Administrator\Framework\Http\Response;

/**
 * HTTP connector client object interface
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @since 1.0
 */
interface IHttp {
	/**
	 * Method to send the GET command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  Response
	 *
	 * @since 2.0
	 */
	public function get($url, ?array $headers = null);
	
	/**
	 * Method to send the POST command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 * @param	int 	$timeout
	 * @param	string 	$useragent
	 *
	 * @return  Response
	 *
	 * @since 1.0
	 */
	public function post($url, $data, ?array $headers = null, $timeout = null, $userAgent = null);
}

/**
 * HTTP client class.
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage http
 * @since 1.0
 */
class Http implements IHttp {
	/**
	 * Number of requests placed
	 * @var    Int 
	 * @access protected
	 */
	protected $numRequests;

	/**
	 * @var    \Joomla\Registry\Registry  Options for the HTTP client
	 * @access protected
	 */
	protected $options;

	/**
	 * @var    Http\Transport  The HTTP transport object to use in sending HTTP requests.
	 * @access protected
	 */
	protected $transport;

	/**
	 * Component params
	 * @var    Object&
	 * @access protected
	 */
	protected $cParams;
	
	/**
	 * Application object
	 * @var    Object&
	 * @access protected
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   Http\Transport  $transport  The HTTP transport object.
	 * @param   $cParams Object& Component configuration
	 *
	 * @since 1.0
	 */
	public function __construct(?Http\Transport $transport = null, &$cParams = null) {
		$this->numRequests = 0;
		$this->cParams = $cParams;
		$this->app = Factory::getApplication();

		$this->transport = isset($transport) ? $transport : new Http\Transport\Socket($this->options);
	}

	/**
	 * Method to send the OPTIONS command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  Response
	 *
	 * @since 1.0
	 */
	public function options($url, ?array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('OPTIONS', new Uri($url), null, $headers);
	}

	/**
	 * Method to send the HEAD command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  Response
	 *
	 * @since 1.0
	 */
	public function head($url, ?array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('HEAD', new Uri($url), null, $headers);
	}

	/**
	 * Method to send the GET command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  Response
	 *
	 * @since 1.0
	 */
	public function get($url, ?array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('GET', new Uri($url), null, $headers);
	}

	/**
	 * Method to send the POST command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  Response
	 *
	 * @since 1.0
	 */
	public function post($url, $data, ?array $headers = null, $timeout = null, $userAgent = null) {
		$this->numRequests++;
		return $this->transport->request('POST', new Uri($url), $data, $headers, $timeout, $userAgent);
	}

	/**
	 * Method to send the PUT command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  Response
	 *
	 * @since 1.0
	 */
	public function put($url, $data, ?array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('PUT', new Uri($url), $data, $headers);
	}

	/**
	 * Method to send the DELETE command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  Response
	 *
	 * @since 1.0
	 */
	public function delete($url, ?array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('DELETE', new Uri($url), null, $headers);
	}

	/**
	 * Method to send the TRACE command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  Response
	 *
	 * @since 1.0
	 */
	public function trace($url, ?array $headers = null) {
		$this->numRequests++;
		return $this->transport->request('TRACE', new Uri($url), null, $headers);
	}

	/**
	 * Check for remaining requests
	 * 
	 * @access public
	 * @return boolean
	 */
	public function isValidRequest() {
		// If unlimited requests, return always true
		if ($this->cParams->get('max_images_requests', 0) == 0) {
			return true;
		}

		// If limited check if remains count
		$limitRequests = $this->cParams->get('max_images_requests');
		if ($this->numRequests < $limitRequests) {
			return true;
		}

		return false;
	}
}
