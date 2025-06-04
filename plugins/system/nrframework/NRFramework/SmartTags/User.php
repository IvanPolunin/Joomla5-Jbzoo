<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

use NRFramework\Cache;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

/**
 * Use the {user} Smart Tags to retrieve information about the currently logged-in user. This Smart Tag can return the value of any property from the Joomla User object as long as you know the property's name. 
 */
class User extends SmartTag
{
    protected $user;

    /**
     * Class constructor
     *
     * @param [type] $factory
     * @param [type] $options
     */
    public function __construct($factory = null, $options = null)
    {
        parent::__construct($factory, $options);

        $this->user = $this->fetchUser();
    }
    
    /**
     * Fetch a property from the User object
     *
     * @param   string  $key   The name of the property to return
     *
     * @return  mixed   Null if property is not found, mixed if property is found
     */
    public function fetchValue($key)
    {
        if (!$this->user)
        {
            return;
        }
        
        // Just in case, deny access to the 'password' property
        if ($key == 'password')
        {
            return;
        }

        // Case custom fields: {user.field.age}
        if (strpos($key, 'field.') !== false && $this->options['isPro'])
        {
            $fieldParts = explode('.', $key);

            $fieldname = $fieldParts[1];

            // Case {user.field.age.rawvalue}
            $fieldProp = isset($fieldParts[2]) ? implode('.', array_slice($fieldParts, 2)) : 'value';

            if ($fields = $this->fetchUserFields())
            {
                return $fields->get($fieldname . '.' . $fieldProp);
            }

            return;
        }

        // Standard user info: {user.name}
        if (is_null($this->user) || $this->user->id == 0)
        {
            return;
        }

        $userRegistry = new Registry($this->user);

        return $userRegistry->get($key);
    }

    /**
     * Return an assosiative array with user custoom fields
     *
     * @return mixed    Array on success, null on failure
     */
    private function fetchUserFields()
    {
        $callback = function()
        { 
            \JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
    
            $prepareCustomFields = $this->parsedOptions->get('preparecustomfields', 'true') === 'true';

            if (!$fields = \FieldsHelper::getFields('com_users.user', $this->user, $prepareCustomFields))
            {
                return;
            }

            $fieldsAssoc = [];

            foreach ($fields as $field)
            {
                if ($field->type == 'subform')
                {
                    // Make subform field values accessible via a user-friendly shortcode {user.field.[SUBFORM_FIELD_NAME].rawvalue.[ROW_INDEX].[FIELD_NAME]}
                    // We could just decode the rawvalue property directly but it does make use of the field IDs instead of field names which is not that user-friendly.
                    $rows = [];

                    foreach ($field->subform_rows as $row)
                    {
                        $row_ = [];

                        foreach ($row as $fieldName => $fieldObj)
                        {
                            $row_[$fieldName] = $fieldObj->value;
                        }

                        $rows[] = $row_;
                    }

                    $field->rawvalue = $rows;
                }

                $fieldsAssoc[$field->name] = $field;
            }

            return new Registry($fieldsAssoc);
        };

        return Cache::memo('fetchUserFields' . $this->user->id, $callback);
    }

    /**
     * Return the user object
     *
     * @return Juser
     */
    private function fetchUser()
    {
        return $this->factory->getUser(isset($this->options['user']) ? $this->options['user'] : null);
    }

    /**
     * Returns the name of the user capitalized
     * 
     * @return  string
     */
    public function getName()
    {
        if (!$name = $this->fetchValue('name'))
        {
            return;
        }
        
        return ucwords(strtolower($name));
    }

    /**
     * Returns the user first name
     * 
     * @return  string
     */
    public function getFirstname()
    {
        if (!$name = $this->getName())
        {
            return;
        }
        
		// Set first name
        $nameParts = explode(' ', $name, 2);
        $firstname = trim($nameParts[0]);
        
        return $firstname;
    }

    /**
     * Returns the user last name
     * 
     * @return  string
     */
    public function getLastname()
    {
        if (!$name = $this->getName())
        {
            return;
        }
        
		// Set last name
    	$nameParts = explode(' ', $name, 2);
    	$lastname  = isset($nameParts[1]) ? trim($nameParts[1]) : $nameParts[0];
        
        return $lastname;
    }

    /**
     * Returns the user login
     * 
     * @deprecated Use {user.username}
     * 
     * @return  string
     */
    public function getLogin()
    {
        return $this->fetchValue('username');
    }

    /**
     * Returns the user register date
     * 
     * @return  string
     */
    public function getRegisterDate()
    {
        if (!$date = $this->fetchValue('registerDate'))
        {
            return;
        }

        return HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC5'));
    }
}