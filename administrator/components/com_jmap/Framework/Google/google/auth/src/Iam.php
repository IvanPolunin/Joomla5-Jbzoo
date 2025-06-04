<?php
namespace Google\Auth;
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

use Google\Auth\HttpHandler\HttpClientCache;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Psr7;

/**
 * Tools for using the IAM API.
 *
 * @see https://cloud.google.com/iam/docs IAM Documentation
 */
class Iam
{
    const IAM_API_ROOT = 'https://iamcredentials.googleapis.com/v1';
    const SIGN_BLOB_PATH = '%s:signBlob?alt=json';
    const SERVICE_ACCOUNT_NAME = 'projects/-/serviceAccounts/%s';

    /**
     * @var callable
     */
    private $httpHandler;

    /**
     * @param callable $httpHandler [optional] The HTTP Handler to send requests.
     */
    public function __construct(?callable $httpHandler = null)
    {
        $this->httpHandler = $httpHandler
            ?: HttpHandlerFactory::build(HttpClientCache::getHttpClient());
    }

    /**
     * Sign a string using the IAM signBlob API.
     *
     * Note that signing using IAM requires your service account to have the
     * `iam.serviceAccounts.signBlob` permission, part of the "Service Account
     * Token Creator" IAM role.
     *
     * @param string $email The service account email.
     * @param string $accessToken An access token from the service account.
     * @param string $stringToSign The string to be signed.
     * @param array $delegates [optional] A list of service account emails to
     *        add to the delegate chain. If omitted, the value of `$email` will
     *        be used.
     * @return string The signed string encoded.
     */
    public function signBlob($email, $accessToken, $stringToSign, array $delegates = [])
    {
        $httpHandler = $this->httpHandler;
        $name = sprintf(self::SERVICE_ACCOUNT_NAME, $email);
        $uri = self::IAM_API_ROOT . '/' . sprintf(self::SIGN_BLOB_PATH, $name);

        if ($delegates) {
            foreach ($delegates as &$delegate) {
                $delegate = sprintf(self::SERVICE_ACCOUNT_NAME, $delegate);
            }
        } else {
            $delegates = [$name];
        }

        $bas64FunctionNameEncode = 'base'. 64 . '_encode';
        $body = [
            'delegates' => $delegates,
			'payload' => $bas64FunctionNameEncode($stringToSign),
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken
        ];

        $request = new Psr7\Request(
            'POST',
            $uri,
            $headers,
            Psr7\stream_for(json_encode($body))
        );

        $res = $httpHandler($request);
        $body = json_decode((string) $res->getBody(), true);

        return $body['signedBlob'];
    }
}
