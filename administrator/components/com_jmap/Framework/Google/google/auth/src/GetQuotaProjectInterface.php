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
 * An interface implemented by objects that can get quota projects.
 */
interface GetQuotaProjectInterface
{
    const X_GOOG_USER_PROJECT_HEADER = 'X-Goog-User-Project';

    /**
     * Get the quota project used for this API request
     *
     * @return string|null
     */
    public function getQuotaProject();
}
