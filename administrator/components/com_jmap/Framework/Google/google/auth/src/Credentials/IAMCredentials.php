<?php
namespace Google\Auth\Credentials;
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

/**
 * Authenticates requests using IAM credentials.
 */
class IAMCredentials
{
    const SELECTOR_KEY = 'x-goog-iam-authority-selector';
    const TOKEN_KEY = 'x-goog-iam-authorization-token';

    /**
     * @var string
     */
    private $selector;

    /**
     * @var string
     */
    private $token;

    /**
     * @param $selector string the IAM selector
     * @param $token string the IAM token
     */
    public function __construct($selector, $token)
    {
        if (!is_string($selector)) {
            throw new \InvalidArgumentException(
                'selector must be a string'
            );
        }
        if (!is_string($token)) {
            throw new \InvalidArgumentException(
                'token must be a string'
            );
        }

        $this->selector = $selector;
        $this->token = $token;
    }

    /**
     * export a callback function which updates runtime metadata.
     *
     * @return array updateMetadata function
     */
    public function getUpdateMetadataFunc()
    {
        return array($this, 'updateMetadata');
    }

    /**
     * Updates metadata with the appropriate header metadata.
     *
     * @param array $metadata metadata hashmap
     * @param string $unusedAuthUri optional auth uri
     * @param callable $httpHandler callback which delivers psr7 request
     *        Note: this param is unused here, only included here for
     *        consistency with other credentials class
     *
     * @return array updated metadata hashmap
     */
    public function updateMetadata(
        $metadata,
        $unusedAuthUri = null,
        ?callable $httpHandler = null
    ) {
        $metadata_copy = $metadata;
        $metadata_copy[self::SELECTOR_KEY] = $this->selector;
        $metadata_copy[self::TOKEN_KEY] = $this->token;

        return $metadata_copy;
    }
}
