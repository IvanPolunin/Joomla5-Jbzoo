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
 * Describes a Credentials object which supports fetching the project ID.
 */
interface ProjectIdProviderInterface
{
    /**
     * Get the project ID.
     *
     * @param callable $httpHandler Callback which delivers psr7 request
     * @return string|null
     */
    public function getProjectId(?callable $httpHandler = null);
}
