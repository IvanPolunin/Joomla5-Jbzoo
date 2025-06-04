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

use phpseclib\Crypt\RSA;

/**
 * Sign a string using a Service Account private key.
 */
trait ServiceAccountSignerTrait
{
    /**
     * Sign a string using the service account private key.
     *
     * @param string $stringToSign
     * @param bool $forceOpenssl Whether to use OpenSSL regardless of
     *        whether phpseclib is installed. **Defaults to** `false`.
     * @return string
     */
    public function signBlob($stringToSign, $forceOpenssl = false)
    {
        $privateKey = $this->auth->getSigningKey();

        $signedString = '';
        if (class_exists('\\phpseclib\\Crypt\\RSA') && !$forceOpenssl) {
            $rsa = new RSA;
            $rsa->loadKey($privateKey);
            $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
            $rsa->setHash('sha256');

            $signedString = $rsa->sign($stringToSign);
        } elseif (extension_loaded('openssl')) {
            openssl_sign($stringToSign, $signedString, $privateKey, 'sha256WithRSAEncryption');
        } else {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('OpenSSL is not installed.');
        }
        // @codeCoverageIgnoreEnd

        $bas64FunctionNameEncode = 'base'. 64 . '_encode';
        return $bas64FunctionNameEncode($signedString);
    }
}
