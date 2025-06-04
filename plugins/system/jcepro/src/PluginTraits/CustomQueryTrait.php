<?php
/**
 * @package     JCE
 * @subpackage  Editors.Jce
 *
 * @copyright   Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\JcePro\PluginTraits;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use WFApplication;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handles the onDisplay event for the JCE editor.
 *
 * @since  2.9.70
 */
trait CustomQueryTrait
{
    /**
     * Get the query variables from the request.
     *
     * @return array Associative array of query variables.
     */
    protected function getQueryVarsFromRequest()
    {
        $app = Factory::getApplication();
        $query = $app->input->getArray();

        $option = $app->input->getCmd('option', '');

        $vars = array();

        if ($option == 'com_jce') {
            $query = $app->input->get('profile_custom', array());
        }

        foreach ($query as $key => $value) {
            if ($key === 'option') {
                continue;
            }

            // convert namespaced catid key
            if ($key == 'wf_catid') {
                $key = 'catid';
            }

            $vars[$key] = $value;
        }

        return $vars;
    }

    private function checkValue($actual, $expected)
    {
        $negated = false;

        // check if this is a negated value
        if (substr($expected, 0, 1) === '!') {
            $negated = true;
            $expected = substr($expected, 1);
        }

        if ($negated) {
            // must not match the expected value
            if ($actual == $expected) {
                return false;
            }
        } else {
            // must match the expected value
            if ($actual != $expected) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the provided query array matches all custom conditions.
     *
     * @param array $custom The custom conditions array with keys and expected values.
     * @return bool Returns true if all conditions in the custom array are met by the query array, false otherwise.
     */
    private function checkCustomQueryVars($custom)
    {
        $query = $this->getQueryVarsFromRequest();

        foreach ($custom as $key => $expected) {
            
            // set the actual value to an empty string if the key is not found in the query
            $actual = isset($query[$key]) ? $query[$key] : '';

            if (!is_array($expected)) {
                return $this->checkValue($actual, $expected);
            } else {
                $match = true;

                foreach ($expected as $expectedValue) {
                    $match = $this->checkValue($actual, $expectedValue);
                }

                if (!$match) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Converts an array of name-value pairs into an associative array.
     *
     * This function takes an array of associative arrays with 'name' and 'value' keys
     * and re-maps them into a single associative array. Values are aggregated into
     * arrays under their respective names, ensuring unique values are kept and empty
     * values are ignored.
     *
     * @param array $values An array of associative arrays with 'name' and 'value' keys.
     * @return array The resulting associative array.
     */
    private function convertNameValuePairsToAssociativeArray($values)
    {
        $associativeArray = array();

        // Re-map name|value pairs to associative array
        foreach ($values as $value) {
            if (array_key_exists('name', $value) && array_key_exists('value', $value)) {
                // Empty values are ignored
                if ($value['value'] == '') {
                    continue;
                }

                // Initialize the array for this name if not already set
                if (!isset($associativeArray[$value['name']])) {
                    $associativeArray[$value['name']] = [];
                }

                // Append the value, ensuring unique values only
                if (!in_array($value['value'], $associativeArray[$value['name']])) {
                    $associativeArray[$value['name']][] = $value['value'];
                }
            }
        }

        return $associativeArray;
    }

    /**
     * Filters query keys extracted from the request by custom values stored in the profile.
     *
     * This function iterates over the provided input array, retrieves the corresponding
     * request values from the application's input, and filters them based on specific criteria.
     * If the query key 'catid' is encountered, it uses the 'wf_catid' request value instead.
     * Matching values are added to the output array.
     *
     * @param array $inputArray The input array of expected values.
     * @param array $outputArray The array to populate with matching values.
     */
    private function filterQueryVarsByCustomValues($inputArray, &$outputArray)
    {
        $app = Factory::getApplication();

        foreach ($inputArray as $key => $expectedValue) {
            // Retrieve the request value for the current key
            $requestValue = $app->input->get($key, null);

            // Special case for 'catid' key: use 'wf_catid' request value
            if ($key == 'catid') {
                $requestValue = $app->input->get('wf_catid', null);
            }

            // Skip if no value is found in the request
            if ($requestValue === null) {
                continue;
            }

            // Add the expected value to the output array
            $outputArray[$key] = $expectedValue;
        }
    }

    /**
     * Update the Media Field URL with custom query values.
     *
     * @param array  $data Media Field data array containing the URL.
     * @param object $profile Editor Profile object.
     * @return void
     */
    public function onWfMediaFieldGetOptions(&$data, $profile = null)
    {
        if (empty($data)) {
            return;
        }

        if (!is_object($profile)) {
            return;
        }

        $app = Factory::getApplication();

        $params = new Registry($profile->params);

        // get custom query variables from parameters if set
        $customParams = $params->get('setup.custom', array());

        // no custom query variables to process
        if (empty($customParams)) {
            return;
        }

        // process to array
        $custom = (new Registry($customParams))->toArray();

        // invalid custom query variables
        if (empty($custom)) {
            return;
        }

        $vars = $this->convertNameValuePairsToAssociativeArray($custom);

        if (empty($vars)) {
            return;
        }

        // assign custom query values
        $options = [
            'profile_custom' => $app->input->get('profile_custom', array(), 'ARRAY'),
        ];

        // process custom query values to remove invalid values
        $this->filterQueryVarsByCustomValues($vars, $options['profile_custom']);

        // update the URL with the custom query values
        $data['url'] .= '&' . http_build_query($options);
    }

    /**
     * Pass the custom query variables to the editor profile options.
     *
     * @param array $options Editor Profile options array.
     * @return void
     */
    public function onWfEditorProfileOptions(&$options)
    {
        $options['custom'] = $this->getQueryVarsFromRequest();
    }

    public function onWfBeforeEditorProfileItem(&$item)
    {
        $app = Factory::getApplication();

        // check custom query variables
        if (!empty($item->params)) {
            $params = new Registry($item->params);

            // get custom query variables from parameters if set
            $customParams = $params->get('setup.custom', array());

            // no custom query variables to process
            if (empty($customParams)) {
                return;
            }

            // process to array
            $custom = (new Registry($customParams))->toArray();

            // invalid custom query variables
            if (empty($custom)) {
                return;
            }

            $vars = $this->convertNameValuePairsToAssociativeArray($custom);

            if (empty($vars)) {
                return;
            }

            // no valid custom query variables
            if ($this->checkCustomQueryVars($vars) === false) {
                $item = false;
            }
        }
    }

    public function onBeforeWfEditorSettings(&$settings)
    {
        $values = $this->getQueryVarsFromRequest();

        if (empty($values)) {
            return;
        }

        $app = Factory::getApplication();

        // get an editor instance
        $wf = WFApplication::getInstance();

        // get custom query variables from parameters if set
        $custom = $wf->getParam('setup.custom', array());

        if (empty($custom)) {
            return;
        }

        $vars = $this->convertNameValuePairsToAssociativeArray($custom);

        // no custom query variables to process
        if (empty($vars)) {
            return;
        }

        $custom = array();

        $this->filterQueryVarsByCustomValues($vars, $custom);

        $settings['query'] = array_merge($settings['query'], array('profile_custom' => $vars));
    }
}
