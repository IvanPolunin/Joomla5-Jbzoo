<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Pinger;
/**
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage pinger
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
use JExtstore\Component\JMap\Administrator\Framework\Pinger\Xmlrpc\Message as PingerXmlrpcMessage;
use JExtstore\Component\JMap\Administrator\Framework\Pinger\Xmlrpc\Client as PingerXmlrpcClient;
use JExtstore\Component\JMap\Administrator\Framework\Pinger\Xmlrpc\Value as PingerXmlrpcValue;

/**
 * Weblog pinger class to services
 *
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage pinger
 * @since 3.2
 */
include_once 'Xmlrpc.php';
class Weblog {
	// Ping-o-Matic XML-RPC settings
	private $ping_o_matic_server = "rpc.pingomatic.com";
	private $ping_o_matic_port = 80;
	private $ping_o_matic_path = "/RPC2";
	private $ping_o_matic_method = "weblogUpdates.ping";
	// Google XML-RPC settings
	private $google_server = "ping.fc2.com";
	private $google_port = 80;
	private $google_path = "/";
	private $google_method = "weblogUpdates.ping";
	// Weblogs.Com XML-RPC settings
	private $weblogs_com_server = "rpc.twingly.com";
	private $weblogs_com_port = 80;
	private $weblogs_com_path = "/RPC2";
	private $weblogs_com_method = "weblogUpdates.ping";
	private $weblogs_com_extended_method = "weblogUpdates.extendedPing";
	// Blo.gs XML-RPC settings
	private $blo_gs_server = "ping.blo.gs";
	private $blo_gs_port = 80;
	private $blo_gs_path = "/";
	private $blo_gs_method = "weblogUpdates.ping";
	private $software_version = "1.6";

	/* Multi-purpose ping for any XML-RPC server that supports the Weblogs.Com interface. */
	private function ping($xml_rpc_server, $xml_rpc_port, $xml_rpc_path, $xml_rpc_method, $weblog_name, $weblog_url, $changes_url, $cat_or_rss, $extended = false) {
		// build the parameters
		$name_param = new PingerXmlrpcValue ( $weblog_name, 'string' );
		$url_param = new PingerXmlrpcValue ( $weblog_url, 'string' );
		$changes_param = new PingerXmlrpcValue ( $changes_url, 'string' );
		$cat_or_rss_param = new PingerXmlrpcValue ( $cat_or_rss, 'string' );
		$method_name = "weblogUpdates.ping";
		if ($extended)
			$method_name = "weblogUpdates.extendedPing";
	
		if ($cat_or_rss != "") {
			$params = array (
					$name_param,
					$url_param,
					$changes_param,
					$cat_or_rss_param
			);
			$call_text = "$method_name(\"$weblog_name\", \"$weblog_url\", \"$changes_url\", \"$cat_or_rss\")";
		} else {
			if ($changes_url != "") {
				$params = array (
						$name_param,
						$url_param,
						$changes_param
				);
				$call_text = "$method_name(\"$weblog_name\", \"$weblog_url\", \"$changes_url\")";
			} else {
				$params = array (
						$name_param,
						$url_param
				);
				$call_text = "$method_name(\"$weblog_name\", \"$weblog_url\")";
			}
		}
	
		// create the message
		$message = new PingerXmlrpcMessage ( $xml_rpc_method, $params );
		$client = new PingerXmlrpcClient ( $xml_rpc_path, $xml_rpc_server, $xml_rpc_port );
		$response = $client->send ( $message, 5 );
		// log the message
		if (!$response) {
			$error_text = "Error: " . $xml_rpc_server . ": " . $client->errno . " " . $client->errstring;
			//throw new \Exception($error_text);
			return false;
		}
		if ($response->faultCode () != 0) {
			//throw new \Exception("Error: " . $xml_rpc_server . ": " . $response->faultCode () . " " . $response->faultString ());
			return false;
		}
		$response_value = $response->value ();
		$fl_error = $response_value->structmem ( 'flerror' );
		$message = $response_value->structmem ( 'message' );

		return true;
	}
	
	/*
	 * Ping Weblogs.Com to indicate that a weblog has been updated. Returns true on success and false on failure.
	 */
	public function ping_weblogs_com($weblog_name, $weblog_url, $changes_url = "", $category = "") {
		return $this->ping ( $this->weblogs_com_server, $this->weblogs_com_port, $this->weblogs_com_path, $this->weblogs_com_method, $weblog_name, $weblog_url, $changes_url, $category );
	}
	
	/*
	 * Ping Blo.gs to indicate that a weblog has been updated. Returns true on success and false on failure.
	 */
	public function ping_blo_gs($weblog_name, $weblog_url, $changes_url = "", $category = "") {
		return $this->ping ( $this->blo_gs_server, $this->blo_gs_port, $this->blo_gs_path, $this->blo_gs_method, $weblog_name, $weblog_url, $changes_url, $category );
	}

	/*
	 * Ping Pingomatic to indicate that a weblog has been updated. Returns true on success and false on failure.
	 */
	public function ping_ping_o_matic($weblog_name, $weblog_url, $changes_url = "", $category = "") {
		return $this->ping ( $this->ping_o_matic_server, $this->ping_o_matic_port, $this->ping_o_matic_path, $this->ping_o_matic_method, $weblog_name, $weblog_url, $changes_url, $category );
	}

	/*
	 * Ping Google to indicate that a weblog has been updated. Returns true on success and false on failure.
	 */
	public function ping_google($weblog_name, $weblog_url, $changes_url = "", $category = "") {
		return $this->ping ( $this->google_server, $this->google_port, $this->google_path, $this->google_method, $weblog_name, $weblog_url, $changes_url, $category );
	}
}