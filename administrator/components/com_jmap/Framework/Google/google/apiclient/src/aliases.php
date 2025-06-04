<?php
// namespace administrator\components\com_jmap\framework\google;
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

if (!file_exists(JPATH_ROOT . '/libraries/cjlib/vendor/google/apiclient/src/Client.php') && class_exists('Google_Client')) {
    // Prevent error with preloading in PHP 7.4
    // @see https://github.com/googleapis/google-api-php-client/issues/1976
    return;
}

$classMap = [
    'Google\\Client' => 'Google_Client',
    'Google\\Service' => 'Google_Service',
    'Google\\AccessToken\\Revoke' => 'Google_AccessToken_Revoke',
    'Google\\AccessToken\\Verify' => 'Google_AccessToken_Verify',
    'Google\\Model' => 'Google_Model',
    'Google\\Utils\\UriTemplate' => 'Google_Utils_UriTemplate',
    'Google\\AuthHandler\\Guzzle6AuthHandler' => 'Google_AuthHandler_Guzzle6AuthHandler',
    'Google\\AuthHandler\\Guzzle7AuthHandler' => 'Google_AuthHandler_Guzzle7AuthHandler',
    'Google\\AuthHandler\\Guzzle5AuthHandler' => 'Google_AuthHandler_Guzzle5AuthHandler',
    'Google\\AuthHandler\\AuthHandlerFactory' => 'Google_AuthHandler_AuthHandlerFactory',
    'Google\\Http\\REST' => 'Google_Http_REST',
    'Google\\Task\\Retryable' => 'Google_Task_Retryable',
    'Google\\Task\\Exception' => 'Google_Task_Exception',
    'Google\\Task\\Runner' => 'Google_Task_Runner',
    'Google\\Collection' => 'Google_Collection',
    'Google\\Service\\Exception' => 'Google_Service_Exception',
    'Google\\Service\\Resource' => 'Google_Service_Resource',
    'Google\\Exception' => 'Google_Exception',
];

foreach ($classMap as $class => $alias) {
    class_alias($class, $alias);
}

/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
class Google_Task_Composer extends \Google\Task\Composer
{
}
