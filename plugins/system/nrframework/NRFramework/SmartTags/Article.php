<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

use NRFramework\Conditions\Conditions\Component\ContentBase;
use NRFramework\Cache;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die('Restricted access');

/**
 * Use the {article} Smart Tags to retrieve information about a Joomla article. This Smart Tag can return the value of any property from the Joomla Article object as long as you know the name of the property. It can access details from the current browsing article and any article by providing the article's ID using the –id property.
 */
class Article extends SmartTag
{
    /**
     * The data object of the article loaded
     *
     * @var mixed
     */
    protected $article;

    /**
     * Class constructor
     *
     * @param [type] $factory
     * @param [type] $options
     */
    public function __construct($factory = null, $options = null)
    {
        parent::__construct($factory, $options);

        $contentAssignment = new ContentBase();

        $article_id = $this->parsedOptions->get('id', null);

        if (is_null($article_id) && !$contentAssignment->isSinglePage())
        {
            return;
        }
        
        $this->article = $contentAssignment->getItem($article_id);
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
        if (!$this->article)
        {
            return;
        }

        // Case SEF URL: {article.link}
        if ($key == 'link')
        {
            if (!defined('nrJ4') && !class_exists('ContentHelperRoute'))
            {
                \JLoader::register('ContentHelperRoute', JPATH_ROOT . '/components/com_content/helpers/route.php');
            }

            $routerHelper = defined('nrJ4') ? '\Joomla\Component\Content\Site\Helper\RouteHelper' : '\ContentHelperRoute';
            return Route::_($routerHelper::getArticleRoute($this->article->id, $this->article->catid, $this->article->language));
        }

        // Support {article.user.USER_PROPERTY} - It would be great if somehow could integrate the {user} Smart Tag here.
        if (substr($key, 0, 5) == 'user.')
        {
            $this->article->user = $this->factory->getUser($this->article->created_by);
        }

        // Case custom fields: {article.field.age}
        if (strpos($key, 'field.') !== false && $this->options['isPro'])
        {
            $fieldParts = explode('.', $key);

            $fieldname = $fieldParts[1];

            // Case {article.field.age.rawvalue}
            $fieldProp = isset($fieldParts[2]) ? implode('.', array_slice($fieldParts, 2)) : 'value';

            if ($fields = $this->fetchCustomFields())
            {
                return $fields->get($fieldname . '.' . $fieldProp);
            }

            return;
        }

        $articleRegistry = new Registry($this->article);

        return $articleRegistry->get($key);
    }

    /**
     * Return an assosiative array with user custoom fields
     *
     * @return mixed    Array on success, null on failure
     */
    private function fetchCustomFields()
    {
        if (!$this->article)
        {
            return;
        }

        $callback = function()
        {
            \JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

            $prepareCustomFields = $this->parsedOptions->get('preparecustomfields', 'true') === 'true';
    
            if (!$fields = \FieldsHelper::getFields('com_content.article', $this->article, $prepareCustomFields))
            {
                return;
            }

            $fieldsAssoc = [];

            foreach ($fields as $field)
            {
                if ($field->type == 'subform')
                {
                    // Make subform field values accessible via a user-friendly shortcode {article.field.[SUBFORM_FIELD_NAME].rawvalue.[ROW_INDEX].[FIELD_NAME]}
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

        return Cache::memo('fetchCustomFields' . $this->article->id, $callback);
    }
}