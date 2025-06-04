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

/**
 * Describes a class which supports signing arbitrary strings.
 */
interface SignBlobInterface extends FetchAuthTokenInterface
{
    /**
     * Sign a string using the method which is best for a given credentials type.
     *
     * @param string $stringToSign The string to sign.
     * @param bool $forceOpenssl Require use of OpenSSL for local signing. Does
     *        not apply to signing done using external services. **Defaults to**
     *        `false`.
     * @return string The resulting signature. Value should be encoded.
     */
    public function signBlob($stringToSign, $forceOpenssl = false);

    /**
     * Returns the current Client Name.
     *
     * @param callable $httpHandler callback which delivers psr7 request, if
     *     one is required to obtain a client name.
     * @return string
     */
    public function getClientName(?callable $httpHandler = null);
}
