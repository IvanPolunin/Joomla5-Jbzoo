<?php
namespace JExtstore\Component\JMap\Administrator\Framework;
/**
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Framework autoloader based on camel case and prefix namespacing
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @since 2.0
 */
abstract class Loader {
	/**
	 * Container for already imported library paths.
	 *
	 * @var array
	 */
	protected static $classes = array ();
	
	/**
	 * Container for already imported library paths.
	 *
	 * @var array
	 */
	protected static $imported = array ();
	
	/**
	 * Container for registered library class prefixes and path lookups.
	 *
	 * @var array
	 */
	protected static $prefixes = array ();
	
	/**
	 * Container for namespace => path map.
	 *
	 * @var array
	 * @since 12.3
	 */
	protected static $namespaces = array ();
	
	/**
	 * Method to get the list of registered classes and their respective file paths for the autoloader.
	 *
	 * @return array The array of class => path values for the autoloader.
	 */
	public static function getClassList() {
		return self::$classes;
	}
	
	/**
	 * Load the file for a class.
	 *
	 * @param string $class
	 *        	The class to be loaded.
	 *        	
	 * @return boolean True on success
	 */
	public static function load($class) {
		// Sanitize class name.
		$class = strtolower ( $class );
		
		// If the class already exists do nothing.
		if (class_exists ( $class )) {
			return true;
		}
		
		// If the class is registered include the file.
		if (isset ( self::$classes [$class] )) {
			include_once self::$classes [$class];
			return true;
		}
		
		return false;
	}
	
	/**
	 * Directly register a class to the autoload list.
	 *
	 * @param string $class
	 *        	The class name to register.
	 * @param string $path
	 *        	Full path to the file that holds the class to register.
	 * @param boolean $force
	 *        	True to overwrite the autoload path value for the class if it already exists.
	 *        	
	 * @return void
	 */
	public static function register($class, $path, $force = true) {
		// Sanitize class name.
		$class = strtolower ( $class );
		
		// Only attempt to register the class if the name and file exist.
		if (! empty ( $class ) && is_file ( $path )) {
			// Register the class with the autoloader if not already registered or the force flag is set.
			if (empty ( self::$classes [$class] ) || $force) {
				self::$classes [$class] = $path;
			}
		}
	}
	
	/**
	 * Register a class prefix with lookup path.
	 * This will allow developers to register library
	 * packages with different class prefixes to the system autoloader. More than one lookup path
	 * may be registered for the same class prefix, but if this method is called with the reset flag
	 * set to true then any registered lookups for the given prefix will be overwritten with the current
	 * lookup path.
	 *
	 * @param string $prefix
	 *        	The class prefix to register.
	 * @param string $path
	 *        	Absolute file path to the library root where classes with the given prefix can be found.
	 * @param boolean $reset
	 *        	True to reset the prefix with only the given lookup path.
	 *        	
	 * @return void
	 */
	public static function registerPrefix($prefix, $path, $reset = false) {
		// Verify the library path exists.
		if (! file_exists ( $path )) {
			throw new \RuntimeException ( 'Library path ' . $path . ' cannot be found.', 500 );
		}
		
		// If the prefix is not yet registered or we have an explicit reset flag then set set the path.
		if (! isset ( self::$prefixes [$prefix] ) || $reset) {
			self::$prefixes [$prefix] = array (
					$path 
			);
		} 		// Otherwise we want to simply add the path to the prefix.
		else {
			self::$prefixes [$prefix] [] = $path;
		}
	}
	
	/**
	 * Method to autoload classes that are namespaced to the PSR-0 standard.
	 *
	 * @param string $class
	 *        	The fully qualified class name to autoload.
	 *
	 * @return boolean True on success, false otherwise.
	 *
	 * @since 13.1
	 */
	public static function loadByPsr4($class) {
		// Remove the root backslash if present.
		if ($class [0] == '\\') {
			$class = substr ( $class, 1 );
		}
		
		// Find the location of the last NS separator.
		$pos = strrpos ( $class, '\\' );
		
		// If one is found, we're dealing with a NS'd class.
		if ($pos !== false) {
			$classPath = str_replace ( '\\', DIRECTORY_SEPARATOR, substr ( $class, 0, $pos ) ) . DIRECTORY_SEPARATOR;
			$className = substr ( $class, $pos + 1 );
		} 		// If not, no need to parse path.
		else {
			$classPath = null;
			$className = $class;
		}
		
		// Addon
		$classPath .= str_replace ( '_', DIRECTORY_SEPARATOR, $className ) . '.php';
		
		// Loop through registered namespaces until we find a match.
		foreach ( self::$namespaces as $ns => $paths ) {
			if (strpos ( $class, $ns ) === 0) {
				$ns = str_replace ( '\\', DIRECTORY_SEPARATOR, $ns);
				// Loop through paths registered to this namespace until we find a match.
				foreach ( $paths as $path ) {
					$classFilePath = $path . DIRECTORY_SEPARATOR . $classPath;
					$classFilePath = str_replace($ns . DIRECTORY_SEPARATOR, '', $classFilePath);
					 
					// We check for class_exists to handle case-sensitive file systems
					if (file_exists ( $classFilePath ) && ! class_exists ( $class, false )) {
						return ( bool ) include_once $classFilePath;
					}
					
					// Try to load by same class name - folder name
					$alternativeClassPath = str_replace ( '\\', DIRECTORY_SEPARATOR, $class ) . DIRECTORY_SEPARATOR;
					$alternativeClassPath .=  str_replace ( '_', DIRECTORY_SEPARATOR, $className ) . '.php';
					$alternativeClassFilePath = $path . DIRECTORY_SEPARATOR . $alternativeClassPath;
					$alternativeClassFilePath = str_replace($ns . DIRECTORY_SEPARATOR, '', $alternativeClassFilePath);
					
					// We check for class_exists to handle case-sensitive file systems
					if (file_exists ( $alternativeClassFilePath ) && ! class_exists ( $class, false )) {
						return ( bool ) include_once $alternativeClassFilePath;
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Register a namespace to the autoloader.
	 * When loaded, namespace paths are searched in a "last in, first out" order.
	 *
	 * @param string $namespace
	 *        	A case sensitive Namespace to register.
	 * @param string $path
	 *        	A case sensitive absolute file path to the library root where classes of the given namespace can be found.
	 * @param boolean $reset
	 *        	True to reset the namespace with only the given lookup path.
	 * @param boolean $prepend
	 *        	If true, push the path to the beginning of the namespace lookup paths array.
	 *
	 * @return void
	 *
	 * @throws RuntimeException
	 *
	 * @since 12.3
	 */
	public static function registerNamespacePsr4($namespace, $path, $reset = false, $prepend = false) {
		// Verify the library path exists.
		if (! file_exists ( $path )) {
			$path = (str_replace ( JPATH_ROOT, '', $path ) == $path) ? basename ( $path ) : str_replace ( JPATH_ROOT, '', $path );
				
			throw new \RuntimeException ( 'Library path ' . $path . ' cannot be found.', 500 );
		}
	
		// If the namespace is not yet registered or we have an explicit reset flag then set the path.
		if (! isset ( self::$namespaces [$namespace] ) || $reset) {
			self::$namespaces [$namespace] = array (
					$path
			);
		}
	
		// Otherwise we want to simply add the path to the namespace.
		else {
			if ($prepend) {
				array_unshift ( self::$namespaces [$namespace], $path );
			} else {
				self::$namespaces [$namespace] [] = $path;
			}
		}
	}
	
	/**
	 * Method to setup the autoloaders for the Joomla Platform.
	 * Since the SPL autoloaders are
	 * called in a queue we will add our explicit, class-registration based loader first, then
	 * fall back on the autoloader based on conventions. This will allow people to register a
	 * class in a specific location and override platform libraries as was previously possible.
	 *
	 * @return void
	 */
	public static function setup() {
		// Register the autoloader functions.
		spl_autoload_register ( array (
				'\JExtstore\Component\JMap\Administrator\Framework\Loader',
				'load' 
		) );
		spl_autoload_register ( array (
				'\JExtstore\Component\JMap\Administrator\Framework\Loader',
				'_autoload' 
		) );
		
		spl_autoload_register ( array (
				'\JExtstore\Component\JMap\Administrator\Framework\Loader',
				'loadByPsr4' 
		) );
	}
	
	/**
	 * Autoload a class based on name.
	 *
	 * @param string $class
	 *        	The class to be loaded.
	 *        	
	 * @return void
	 */
	private static function _autoload($class) {
		foreach ( self::$prefixes as $prefix => $lookup ) {
			if (strpos ( $class, $prefix ) === 0) {
				return self::_load ( substr ( $class, strlen ( $prefix ) ), $lookup );
			}
		}
	}
	
	/**
	 * Load a class based on name and lookup array.
	 *
	 * @param string $class
	 *        	The class to be loaded (wihtout prefix).
	 * @param array $lookup
	 *        	The array of base paths to use for finding the class file.
	 *        	
	 * @return void
	 */
	private static function _load($class, $lookup) {
		// Split the class name into parts separated by camelCase.
		$parts = preg_split ( '/(?<=[a-z0-9])(?=[A-Z])/x', $class );
		
		// If there is only one part we want to duplicate that part for generating the path.
		$parts = (count ( $parts ) === 1) ? array (
				$parts [0],
				$parts [0] 
		) : $parts;
		
		foreach ( $lookup as $base ) {
			// Generate the path based on the class name parts.
			$path = $base . '/' . implode ( '/', array_map ( 'strtolower', $parts ) ) . '.php';
			
			// Load the file if it exists.
			if (file_exists ( $path )) {
				return include $path;
			} else {
				// Try if an exact folder/name match
				// Generate the path based on the class name parts.
				$parts[] = $parts[count($parts) - 1];
				$path = $base . '/' . implode ( '/', array_map ( 'strtolower', $parts ) ) . '.php';
				if (file_exists ( $path )) {
					return include $path;
				}
			}
		}
	}
}