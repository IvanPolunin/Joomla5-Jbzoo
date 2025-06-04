<?php
namespace Google\AuthHandler;
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

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Exception;

class AuthHandlerFactory
{
  /**
   * Builds out a default http handler for the installed version of guzzle.
   *
   * @return Guzzle5AuthHandler|Guzzle6AuthHandler|Guzzle7AuthHandler
   * @throws Exception
   */
  public static function build($cache = null, array $cacheConfig = [])
  {
    $guzzleVersion = null;
    if (defined('\GuzzleHttp\ClientInterface::MAJOR_VERSION')) {
      $guzzleVersion = ClientInterface::MAJOR_VERSION;
    } elseif (defined('\GuzzleHttp\ClientInterface::VERSION')) {
      $guzzleVersion = (int) substr(ClientInterface::VERSION, 0, 1);
    }

    switch ($guzzleVersion) {
      case 5:
        return new Guzzle5AuthHandler($cache, $cacheConfig);
      case 6:
        return new Guzzle6AuthHandler($cache, $cacheConfig);
      case 7:
        return new Guzzle7AuthHandler($cache, $cacheConfig);
      default:
        throw new Exception('Version not supported');
    }
  }
}
