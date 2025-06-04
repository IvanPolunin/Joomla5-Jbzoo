<?php 

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\SmartTags;

defined('_JEXEC') or die('Restricted access');

use NRFramework\Cache;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
/**
 *   SmartTags replaces placeholder variables in a string
 */
class SmartTags
{
	/**
	 * Factory Class
	 *
	 * @var object
	 */
	protected $factory;

	/**
	 * Path where each extension stores
	 * their Smart Tags.
	 * 
	 * @var  array
	 */
	protected $paths;

	/**
	 * Tags Array
	 *
	 * @var array
	 */
	protected $tags = [];

	/**
	 * All the options that we were given.
	 * This is stored in case we were given options
	 * other then the prefix/placeholder such as a user.
	 * This is useful for other plugins to manipulate the user, etc...
	 * 
	 * @var  array
	 */
	protected $options;

	/**
	 * The Smart Tags pattern used to find all available Smart Tags in a subject.
	 * 
	 * @var  string
	 */
	protected $pattern;

	/**
	 * The Smart Tag prefix
	 * 
	 * @var  string
	 */
	protected $prefix = '';

	/**
	 * The Smart Tag placeholder
	 * 
	 * @var  string
	 */
	private $placeholder = '{}';

	/**
	 * Indicates whether the calculated value will be converted to text using a layout or keep the original type as returned by the value method.
	 * This is supposed to be set to true when the result is supposed to be used later in the code or in a API call, just like we do in Convert Forms Webhooks.
	 *
	 * @var bool
	 */
	private $prepareValue = true;

	/**
	 * List of excluded files within the NRFramework\SmartTags namespace
	 * 
	 * @var  array
	 */
	protected $excluded_smart_tags_files = [
		'.',
		'..',
		'index.php',
		'SmartTag.php',
		'SmartTags.php'
	];

	/**
	 * List of areas in the content that should not be parsed for Smart Tags.
	 *
	 * @var array
	 */
	private $protectedAreas = [];

	/**
	 * Indicates whether the version of the extension that calls Smart Tags is Pro or Free.
	 *
	 * @var boolean
	 */
	private $isPro = true;
	
	/**
	 * Smart Tags Constructor
	 * 
	 * @param   array    $opts		An array of options(prefix, placeholder)
	 * @param   Factory  $factory   NRFramework Factory
	 */
	public function __construct($opts = [], $factory = null)
	{
		$this->options = $opts;
		
		// set options
		if (is_array($opts))
		{
			$this->prefix = isset($opts['prefix']) ? $opts['prefix'] : $this->prefix;
			$this->placeholder = isset($opts['placeholder']) ? $opts['placeholder'] : $this->placeholder;
			$this->prepareValue = isset($opts['prepareValue']) ? $opts['prepareValue'] : $this->prepareValue;
			$this->isPro = isset($opts['isPro']) ? $opts['isPro'] : true;
		}

		$this->pattern = $this->getPattern();

		// Set Factory
		if (!$factory)
		{
			$factory = new \NRFramework\Factory();
		}

		$this->factory = $factory;

		// register NRFramework Smart Tags
		$this->register('\NRFramework\SmartTags', dirname(__DIR__) . '/SmartTags');
	}

	/**
	 * Get a cache instance of the class
	 * 
	 * @param   array	$opts		An array of options(prefix, placeholder)
	 * @param   object	$factory   	The framework's factory class
	 * 
	 * @return  object
	 */
    static public function getInstance($opts = [], $factory = null)
    {
        static $instance = null;

		if ($instance === null)
		{
            $instance = new SmartTags($opts, $factory);
		}
		
        return $instance;
    }

	/**
	 * Registers a namespace, path and some data where Smart Tags are stored.
	 * 
	 * @param   string  $namespace
	 * @param   string  $path
	 * @param   array   $data
	 * 
	 * @return  void
	 */
	public function register($namespace, $path, $data = [])
	{
		if (!$namespace || !$path)
		{
			return;
		}

		if (isset($this->paths[$namespace]))
		{
			return;
		}

		$this->paths[$namespace] = [
			'path' => $path
		];

		if (isset($data))
		{
			$this->paths[$namespace]['data'] = $data;
		}
	}

	/**
	 * Remove all tags starting with the given prefix.
	 *
	 * @param  string $prefix	The prefix
	 * 
	 * @return void
	 */
	public function removeTagsByPrefix($prefix)
	{
		foreach ($this->tags as $key => $value)
		{
			if (substr($key, 0, strlen($prefix)) !== $prefix)
			{
				continue;
			}

			unset($this->tags[$key]);
		}
		
		return $this;
	}

	/**
	 * Adds Custom Tags to the list
	 *
	 * @param  mixed   $tags    Tags list (Array or Object)
	 * @param  string  $prefix  A string to prefix all keys
	 */
	public function add($tags, $prefix = null)
	{
		if (!$tags || !is_array($tags))
		{
			return;
		}

		// Start of Convert Forms View Submissions Compatibility Issue
		// This block is added to handle the backwards compatibility issue occured in the front-end submissions view 
		// in Convert Forms which adds submissions smart tags with curly brackets {}.
		// @deprecated - Scheduled to be removed at the end of 2021
		foreach ($tags as $key => $value)
		{
			if (strpos($key, '{') === false)
			{
				continue;
			}

			$newKey = ltrim($key, '{');
			$newKey = rtrim($newKey, '}');
			$tags[$newKey] = $value;
		}
		// End of Convert Forms View Submissions Compatibility Issue

		// Add Prefix to keys
		if ($prefix)
		{
			foreach ($tags as $key => $value)
			{
		        $newKey = strtolower($prefix . $key);
		        $tags[$newKey] = $value;
				unset($tags[$key]);
			}
		}

		$this->tags = array_merge($this->tags, $tags);
		
		return $this;
	}

	/**
	 *  Returns placeholder in 2 pieces
	 *
	 *  @return  array
	 */
	protected function getPlaceholder()
	{
		return str_split($this->placeholder, strlen($this->placeholder) / 2);
	}
	
	/**
	 *  Replace tags in object recursively
	 *
	 *  @param   mixed  $obj  The data object to search for Smart Tags
	 *
	 *  @return  mixed
	 */
	public function replace($subject)
	{
		if (is_null($subject))
		{
			return $subject;
		}

		if (is_scalar($subject))
		{
			while ($matches = $this->findSmartTags($subject))
			{
				// This indicates whether the subject comprises solely a single shortcode or a mixture of shortcode and plain text.
				$mixContent = !(count($matches) == 1 && $matches[0] == $subject);

				if (!$tmpSubject = $this->replaceSmartTagsInContent($subject, $matches, $mixContent))
				{
					break;
				}

				$subject = $tmpSubject;
			}

			// Restore protected areas
			if (!empty($this->protectedAreas))
			{
				foreach ($this->protectedAreas as $protectedArea)
				{
					$subject = str_ireplace($protectedArea[0], $protectedArea[1], $subject);
				}			
			}
		} 
		else 
		{
			foreach ($subject as $key => $subject_item)
			{
				$value = $this->replace($subject_item);

				if ($subject instanceof Registry)
				{
					$subject->set($key, $value);
					continue;
				}

				if (is_object($subject))
				{
					$subject->$key = $value;
					continue;
				}

				if (is_array($subject))
				{
					$subject[$key] = $value;
				}
			}
		}

		return $subject;
	}

	/**
	 * Finds and replaces found Smart Tags in given content
	 * 
	 * @param   string  $content
	 * 
	 * @return  void
	 */
	private function findSmartTags(&$content)
	{
		if (!is_scalar($content))
		{
			return;
		}

		// Skip protected areas
		$reg = '/<!-- SmartTags Skip Start -->(.*?)<!-- SmartTags Skip End -->/s';
		preg_match_all($reg, $content, $protectedAreas);

		if ($protectedAreas[0])
		{
			foreach ($protectedAreas[0] as $protectedAreaIndex => $protectedArea)
			{
				$hash = md5($protectedArea);

				$protectedAreaWithoutComments = $protectedAreas[1][$protectedAreaIndex];

				$this->protectedAreas[] = [$hash, $protectedAreaWithoutComments];

				$content = str_replace($protectedArea, $hash, $content);
			}
		}

		// if no smart tags exist in content, abort
		if (!$this->textHasShortcode($content))
		{
			return;
		}

		// find all Smart Tags
		preg_match_all($this->pattern, $content, $matches);

		// find all Smart Tags and keep the unique only
		return array_unique($matches[0]);
	}

	/**
	 * Undocumented function
	 *
	 * @param	string	$content
	 * @param	array	$foundSmartTags
	 * @param	bool	$mixContent			Indicates whether the subject comprises solely a single shortcode or a mixture of shortcode and plain text.

	 * @return	void
	 */
	private function replaceSmartTagsInContent(&$content, $foundSmartTags, $mixContent)
	{
		$tag_value_pairs = [];

		// find values for each Smart Tag
		foreach ($foundSmartTags as $tag)
		{
			// prepare the smart tag that is going to be processed
			if (!$shortCodeObject = $this->parseShortcode($tag))
			{
				continue;
			}

			$smartTagName = $shortCodeObject['name'];
			$smartTagClassName = $shortCodeObject['group'];

			// Check if the tag is already processed by a previous operation or its value provided in the payload.
			if (isset($this->tags[$smartTagName]))
			{
				$tag_value_pairs[$tag] = $this->tags[$smartTagName];
				continue;
			}

			// OK, we don't know the value yet. Let's see if there's a method available we can call to get a value. 
			$smartTagNamespace = $shortCodeObject['namespace'];
			
			// get the Smart Tag class
			if (!$smartTag = $this->getSmartTagClassByName($smartTagNamespace, $smartTagClassName, $shortCodeObject['options']))
			{
				/**
				 * No method found to call. If the current Smart Tag was added via add(), remove it, otherwise, leave it as is.
				 * 
				 * This is due to without this check, a Smart Tag may be given i.e. {convertforms 1} which would be removed and thus Convert Forms
				 * wouldn't be able to replace it. We must only remove Smart Tags that were added by add().
				 */
				if (count($this->tags))
				{
					foreach ($this->tags as $key => $value)
					{
						if (strpos($key, $shortCodeObject['group']) !== 0)
						{
							continue;
						}

						$tag_value_pairs[$tag] = '';
						break;
					}
				}

				continue;
			}

			// Set data for Smart Tag if they exist in the path data. 
			if (isset($this->paths[$smartTagNamespace]['data']))
			{
				$smartTag->setData($this->paths[$smartTagNamespace]['data']);
			}

			// Make sure the Smart Tag can do replacements.
			if (!$smartTag->canRun())
			{
				continue;
			}

			// Get the Smart Tag value
			$value = $this->getSmartTagValue($smartTag, $shortCodeObject);

			// parse the value to ensure we can save it
			$layout = $shortCodeObject['options'] ? $shortCodeObject['options']->get('layout', '') : null;

			$this->prepareSmartTagValue($value, $layout);

			// Allow modifiers to manipulate the final value.
			if ($shortCodeObject['options'])
			{
				$modifiers = $shortCodeObject['options']->toArray();

				foreach ($modifiers as $modifierKey => $modifierValue)
				{
					$modifierMethod = 'modifier' . $modifierKey;

					if (!method_exists($this, $modifierMethod))
					{
						continue;
					}

					$this->$modifierMethod($modifierValue, $value);
				}
			}
			
			// cache value
			$this->tags[$smartTagName] = $value;
			
			// replace all instances of Smart Tag with its value
			$tag_value_pairs[$tag] = $value;
		}
		
		if (!$tag_value_pairs)
		{
			return;
		}

		// Replace Smart Tags found in the subject
		foreach ($tag_value_pairs as $tag => $value)
		{
			// Convert empty objects to empty strings if necessary.
			$value = empty($value) && ($this->prepareValue || $mixContent) ? '' : $value;

			// In the case of scalar (int, float, string, bool) properties, make the necessary string replacements. 
			if (is_scalar($value))
			{
				$content = str_ireplace($tag, (string) $value, $content);
				continue;
			}
			
			// Otherwise, do not touch the type of the variable.
			$content = $value;
		}

		return $content;
	}

	/**
	 * Prepares the Smart Tag value prior to saving it
	 * 
	 * @param   string   $value
	 * 
	 * @return  void
	 */
	protected function prepareSmartTagValue(&$value, $layout = '')
	{
		if (!$value)
		{
			return;
		}

		// string, integer, float
		if (is_scalar($value))
		{
			if ($layout)
			{
				$value = str_replace('%value%', $value, $layout);
			}

			return;
		}

		// Convert objects to array
		$value = (array) $value;

		if ($layout)
		{
			foreach ($value as &$item)
			{
				$this->prepareSmartTagValue($item, $layout);
			}
		}
		
		// Determine if we must convert the result into string
		if ($this->prepareValue)
		{
			$implodeChar = $layout ? '' : ',';

			$value = implode($implodeChar, $value);
		}
	}

	/**
	 * Parse shortcode and return an array of the shortcode information like, classname, method name e.t.c.
	 * 
	 * The expected shortcode syntax is as follow: {GROUP[.NAME]}
	 * 
	 * The GROUP part is required and must be pointing to \NRFramework\SmartTags\GROUP file which must declare a class with the name GROUP.
	 * Eg: The shortcode {customer} will try to find a class with the name Customer in the \NRFramework\SmartTags\Customer namespace.
	 * 
	 * The NAME part represents the name of the method in the called class. 
	 * For example, the shortcode {customer.name} will call the getName() method in the \NRFramework\SmartTags\Customer class.
	 * 
	 * If the NAME part is ommitted or is invalid, Smart Tags fallbacks to a method with the same name as the class.
	 * For example, the shortcode {customer} will call the getCustomer() method in the \NRFramework\SmartTags\Customer class.
	 *
	 * @param  string $text
	 *
	 * @return array
	 */
	private function parseShortcode($text)
	{
		if (empty($text))
		{
			return;
		}

		// Remove placeholders and prefix from the shortcode. {device} becomes device
		$placeholder = $this->getPlaceholder();
		$text = ltrim($text, $placeholder[0] . $this->prefix);
		$text = trim(rtrim($text, $placeholder[1]));

		$shortcodeTag = $text;
		$shortcodeOptions = null;

		// Split shortcode into 2 parts. First part should be the Smart Tag itself and the 2nd part should be the parameters.
		$firstOptionPos = strpos($text, '--');

		if ($firstOptionPos !== false)
		{
			$shortcodeOptions = substr($text, $firstOptionPos - strlen($text));
			$shortcodeTag = substr($text, 0, $firstOptionPos - 1);
		}

		// We expect a shortcode in 2 parts separated by a dot. 
		// The 1st part is the Smart Tags Group (Class Name) and the 2nd part is the Name of the actual Smart Tag (Method name, optional). 
		$textParts = explode('.', $shortcodeTag, 2);

		$group = $textParts[0];
		$key = isset($textParts[1]) ? $textParts[1] : $textParts[0];

		// Find shortcode options --option=value
		if (!is_null($shortcodeOptions))
		{
			$shortcodeOptions = $this->parseOptions($shortcodeOptions);
		}

		return [
			'name' => $text, // Rename to shortcode
			'group' => $group,
			'key' => $key,
			'method_name' => 'get' . $key,
			'namespace' => $this->getSmartTagNamespace($group),
			'options' => $shortcodeOptions
		];
	}

	/**
	 * Parase shortcode options
	 *
	 * @param  string $text	The original short code
	 * 
	 * @return mixed Null when no options are found, Registry object otherwise.
	 */
	public function parseOptions($text)
	{	
		// A quick test to determine whether to proceed or not.
		if (strpos($text, '--') === false)
		{
			return;
		}

		$regex = '--(.*?)[\W]';

        preg_match_all('/' . $regex . '/is', $text, $params);

		$options = [];

		// @Todo use Regex to parse both option name and value.
		for ($i = 0; $i < count($params[1]); $i++)
		{ 
			$paramName = $params[0][$i];

			$thisParamPosition = mb_strpos($text, $params[0][$i]);
			$nextParamPosition = isset($params[0][$i + 1]) ? mb_strpos($text, $params[0][$i + 1]) - strlen($text) : null;

			$paramValue = \mb_substr($text, $thisParamPosition + strlen($paramName), $nextParamPosition);

			$options[strtolower($params[1][$i])] = trim($paramValue);
		}

		return new Registry($options);
	}

	/**
	 * Returns the Smart Tags Value
	 * 
	 * @param   SmartTag  $smartTag
	 * @param   array     $shortCodeObject  The parsed shortcode object
	 * 
	 * @return  mixed
	 */
	protected function getSmartTagValue($smartTag, $shortCodeObject)
	{
		// Smart Tags method name
		$smartTagMethod = $shortCodeObject['method_name'];

		// make sure method exists in the Smart Tag class
		if (method_exists($smartTag, $smartTagMethod))
		{
			return $smartTag->{$smartTagMethod}();
		}

		/**
		 * Check if the Smart Tag contains a method
		 * to fetch the Smart Tag we are trying to replace.
		 */
		if (method_exists($smartTag, 'fetchValue'))
		{
			return $smartTag->fetchValue($shortCodeObject['key']);
		}
	}

	/**
	 * Returns the Smart Tag Class given the name of the Smart Tag
	 * 
	 * @param   string   $smartTagNamespace
	 * @param   string   $smartTagClassName
	 * 
	 * @return  mixed
	 */
	private function getSmartTagClassByName($smartTagNamespace, $smartTagClassName, $shortcodeOptions = null)
	{
		// get namespace classes
		$namespace_classes = $this->getNamespaceClasses($smartTagNamespace);
		
		if (!isset($namespace_classes[strtolower($smartTagClassName)]))
		{
			return false;
		}

		$smartTagClass = $smartTagNamespace . '\\' . $namespace_classes[strtolower($smartTagClassName)];

		$options = $this->options;
		$options['options'] = $shortcodeOptions;
		$options['isPro'] = $this->isPro;
		
		$class = new $smartTagClass($this->factory, $options);

		if (!$this->isPro && $class->proOnly)
		{
			return;
		}

		return $class;
	}

	/**
	 * Retrieves the cached namespace clases or finds them in the given path
	 * 
	 * @param   string  $namespace
	 * @param   string  $path
	 * 
	 * @return  array
	 */
	private function getNamespaceClasses($namespace, $path = null)
	{
		$cache = $this->factory->getCache();
		$hash  = md5('nrf_smarttags_' . $namespace);

		// if namespace classes are cached, retrieve them
		if ($cache->has($hash))
		{
			return $cache->get($hash);
		}
		
		// if no cached namespace classes exist, ensure we were given a valid path
		if (!$path && !is_string($path))
		{
			return [];	
		}

		// find namespace classes
		$namespace_classes = Folder::files($path, '.', false, false, $this->excluded_smart_tags_files);

		// stores the final strtolower(class name) => actual class file name data
		$classes_data = [];

		// retrieve the strtolower(class name) => class file name array
		foreach ($namespace_classes as $className)
		{
			$base_class_name = str_replace('.php', '', $className);

			$classes_data[strtolower($base_class_name)] = $base_class_name;
		}
		
		// cache it
		return $cache->set($hash, $classes_data);
	}

	/**
	 * Find the namespace of the class in the path list
	 * 
	 * @param   string  $class_name
	 * 
	 * @return  mixed
	 */
	private function getSmartTagNamespace($class_name)
	{
		if (!$class_name && !is_string($class_name))
		{
			return false;
		}

		foreach ($this->paths as $namespace => $path_data)
		{
			// get namespace classes
			$namespace_classes = $this->getNamespaceClasses($namespace, $path_data['path']);

			if (!isset($namespace_classes[strtolower($class_name)]))
			{
				continue;
			}
			
			return $namespace;
		}
		
		return false;
	}

	/**
	 * Return the regular expression pattern that will be used for searches
	 *
	 * @return string
	 */
	private function getPattern()
	{
		$placeholder = $this->getPlaceholder();
		$prefix = $this->prefix ? preg_quote($this->prefix) . '.' : '';
		
		return '#(\\' . $placeholder[0] . $prefix . '([a-zA-Z]\\' . $placeholder[0] . '??[^\\' . $placeholder[0] . ']*?\\' . $placeholder[1] . '))#';
	}

	/**
	 * Super fast way to determine whether given text includes shortcodes
	 *
	 * @param  string $text
	 *
	 * @return boolean
	 */
	private function textHasShortcode($text)
	{
		return StringHelper::strpos($text, $this->getPlaceholder()[0] . $this->prefix) !== false;
	}

	/**
	 *  Returns list of all tags found in given paths
	 * 
	 *  Currently used in the Convert Forms Front-end Submissions Menu Type and in the EngageBox SmartTags modal.
	 * 
	 *  @deprecated  since 4.5.6
	 * 
	 *  @return		 array
	 */
	public function get()
	{
		$placeholder = $this->getPlaceholder();

		// get all tags that have already been added to the list
		$smart_tags_data = $this->tags;

		// loop all registered paths
		foreach ($this->paths as $namespace => $path_data)
		{
			if (!isset($path_data['path']))
			{
				continue;
			}

			if (!is_dir($path_data['path']))
			{
				continue;
			}

			// find all smart tags
			$files = Folder::files($path_data['path'], '.', false, false, $this->excluded_smart_tags_files);

			// search all files
			foreach ($files as $className)
			{
				$baseClassName = str_replace('.php', '', $className);
				$className = $namespace . '\\' . $baseClassName;

				if (!class_exists($className))
				{
					continue;
				}
				
				// reflection class of smart tag
				$reflectionSmartTag = new \ReflectionClass($className);

				// search all methods
				foreach($reflectionSmartTag->getMethods() as $method)
				{
					// Only parse Smart Tags of current class and not from its parent
					if ($method->class != ltrim($className, '\\'))
					{
						continue;
					}
					
					// get smart tag name from each getSmartTag method
					if (strpos($method->name, 'get') !== 0)
					{
						continue;
					}

					$funcNameSplit = explode('get', $method->name);

					$suffix = '';
					if (strtolower($funcNameSplit[1]) != strtolower($reflectionSmartTag->getShortName()))
					{
						$suffix = '.' . $funcNameSplit[1];
					}
					
					$smartTagPrefix = $placeholder[0] . strtolower($reflectionSmartTag->getShortName() . $suffix) . $placeholder[1];

					$smart_tags_data[$smartTagPrefix] = '';
				}
			}
		}
		
		return $smart_tags_data;
	}

	/**
	 * Prepares subject with Joomla Content Plugins
	 * 
	 * Syntax: {shortcode --prepareContent=true}
	 *
	 * @param	Mixed	$modifierValue	The value of the modifier provided by the user
	 * @param	Mixed	$subject		The subject where we replace the Smart Tag
	 * 
	 * @return	void
	 */
	private function modifierPrepareContent($modifierValue, &$subject)
	{
		if ($modifierValue)
		{
			$subject = HTMLHelper::_('content.prepare', $subject);
		}
	}

	/**
	 * Converts a number into a short version, eg: 1000 -> 1k
	 * Based on: https://gist.github.com/RadGH/84edff0cc81e6326029c
	 *
	 * Syntax: {shortcode --shortNumber=true}
	 *
	 * @param	Mixed	$modifierValue	The value of the modifier provided by the user
	 * @param	Mixed	$subject		The subject where we replace the Smart Tag
	 * 
	 * @return	void
	 */
	public function modifierShortNumber($modifierValue, &$subject)
	{
		if ($modifierValue)
		{
			$subject = \NRFramework\Helpers\Number::toShortFormat($subject);
		}
	}

	/**
	 * Convert special characters to HTML entities
	 * 
	 * Syntax: {shortcode --tmlSpecialChars=true}
	 *
	 * @param	Mixed	$modifierValue	The value of the modifier provided by the user
	 * @param	Mixed	$subject		The subject where we replace the Smart Tag
	 * 
	 * @return void
	 */
	public function modifierHtmlSpecialChars($modifierValue, &$subject)
	{
		$subject = htmlspecialchars($subject);
	}
}