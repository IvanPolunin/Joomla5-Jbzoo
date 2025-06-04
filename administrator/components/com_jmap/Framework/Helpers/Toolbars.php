<?php

namespace JExtstore\Component\JMap\Administrator\Framework\Helpers;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage helpers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ContainerAwareToolbarFactory;

/**
 * Utility class for the toolbar
 */
abstract class Toolbars {
	/**
	 * Get the toolbar object instance, optionally from a Toolbar object or from the Document object
	 *
	 * @return object The shared toolbar object
	 */
	private static function getToolbarInstance() {
		$document = Factory::getApplication()->getDocument();
		if(method_exists($document, 'getToolbar')) {
			$bar = $document->getToolbar( 'toolbar' );
		} else {
			// Fallback to legacy
			$bar = Toolbar::getInstance ( 'toolbar' );
		}
		
		return $bar;
	}
	
	/**
	 * Title cell.
	 * For the title and toolbar to be rendered correctly,
	 * this title function must be called before the starttable function and the toolbars icons
	 * this is due to the nature of how the css has been used to position the title in respect to the toolbar.
	 *
	 * @param string $title
	 *        	The title.
	 * @param string $icon
	 *        	The space-separated names of the image.
	 *        	
	 * @return void
	 */
	public static function title($title, $icon = 'generic.png') {
		$layout = new FileLayout ( 'joomla.toolbar.title' );
		$html = $layout->render ( [ 
				'title' => $title,
				'icon' => $icon
		] );

		$app = Factory::getApplication ();
		$app->JComponentTitle = $html;
		$title = strip_tags ( $title ) . ' - ' . $app->get ( 'sitename' );

		if ($app->isClient ( 'administrator' )) {
			$title .= ' - ' . Text::_ ( 'JADMINISTRATION' );
		}

		Factory::getDocument ()->setTitle ( $title );
	}

	/**
	 * Writes a custom option and task button for the button bar.
	 *
	 * @param string $task
	 *        	The task to perform (picked up by the switch($task) blocks).
	 * @param string $icon
	 *        	The image to display.
	 * @param string $iconOver
	 *        	No longer used
	 * @param string $alt
	 *        	The alt text for the icon image.
	 * @param bool $listSelect
	 *        	True if required to check that a standard list item is checked.
	 * @param string $formId
	 *        	The id of action form.
	 *        	
	 * @return void
	 */
	public static function custom($task = '', $icon = '', $iconOver = '', $text = '', $listSelect = true, $formId = null) {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );
		$button = $factory->createButton ( $bar, 'Standard' );

		$button->name ( $icon )->text ( $text )->task ( $task )->listCheck ( $listSelect );
		$bar->appendButton ( $button );
	}

	/**
	 * Writes the common 'new' icon for the button bar.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 * @param boolean $check
	 *        	True if required to check that a standard list item is checked.
	 *        	
	 * @return void
	 */
	public static function addNew($task = 'add', $text = 'JTOOLBAR_NEW', $check = false) {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );
		$button = $factory->createButton ( $bar, 'Standard' );

		$button->name ( 'new' )->text ( $text )->task ( $task )->listCheck ( $check );
		$bar->appendButton ( $button );
	}

	/**
	 * Writes a common 'publish' button.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 * @param boolean $check
	 *        	True if required to check that a standard list item is checked.
	 *        	
	 * @return void
	 */
	public static function publish($task = 'publish', $text = 'JTOOLBAR_PUBLISH', $check = false) {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );
		$button = $factory->createButton ( $bar, 'Standard' );

		$button->name ( 'publish' )->text ( $text )->task ( $task )->listCheck ( $check );
		$bar->appendButton ( $button );
	}

	/**
	 * Writes a common 'publish' button for a list of records.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 *        	
	 * @return void
	 */
	public static function publishList($task = 'publish', $text = 'JTOOLBAR_PUBLISH') {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );
		$button = $factory->createButton ( $bar, 'Standard' );

		$button->name ( 'publish' )->text ( $text )->task ( $task )->listCheck ( true );
		$bar->appendButton ( $button );
	}

	/**
	 * Writes a common 'unpublish' button.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 * @param boolean $check
	 *        	True if required to check that a standard list item is checked.
	 *        	
	 * @return void
	 */
	public static function unpublish($task = 'unpublish', $text = 'JTOOLBAR_UNPUBLISH', $check = false) {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );
		$button = $factory->createButton ( $bar, 'Standard' );

		$button->name ( 'unpublish' )->text ( $text )->task ( $task )->listCheck ( $check );
		$bar->appendButton ( $button );
	}

	/**
	 * Writes a common 'unpublish' button for a list of records.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 *        	
	 * @return void
	 */
	public static function unpublishList($task = 'unpublish', $text = 'JTOOLBAR_UNPUBLISH') {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );
		$button = $factory->createButton ( $bar, 'Standard' );

		$button->name ( 'unpublish' )->text ( $text )->task ( $task )->listCheck ( true );
		$bar->appendButton ( $button );
	}

	/**
	 * Writes a common 'edit' button for a list of records.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 *        	
	 * @return void
	 */
	public static function editList($task = 'edit', $text = 'JTOOLBAR_EDIT') {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );
		$button = $factory->createButton ( $bar, 'Standard' );

		$button->name ( 'edit' )->text ( $text )->task ( $task )->listCheck ( true );
		$bar->appendButton ( $button );
	}

	/**
	 * Writes a common 'delete' button for a list of records.
	 *
	 * @param string $msg
	 *        	Postscript for the 'are you sure' message.
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 *        	
	 * @return void
	 */
	public static function deleteList($msg = '', $task = 'remove', $text = 'JTOOLBAR_DELETE') {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );

		// Add a delete button.
		if ($msg) {
			$button = $factory->createButton ( $bar, 'Confirm' );
			$button->name ( 'delete' )->text ( $text )->listCheck ( true )->message ( $msg )->task ( $task );
			$bar->appendButton ( $button );
		} else {
			$button = $factory->createButton ( $bar, 'Standard' );
			$button->name ( 'delete' )->text ( $text )->listCheck ( true )->task ( $task );
			$bar->appendButton ( $button );
		}
	}

	/**
	 * Writes a save button for a given option.
	 * Apply operation leads to a save action only (does not leave edit mode).
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 *        	
	 * @return void
	 */
	public static function apply($task = 'apply', $text = 'JTOOLBAR_APPLY') {
		$bar = self::getToolbarInstance();

		// Add an apply button
		$bar->apply ( $task, $text );
	}

	/**
	 * Writes a save button for a given option.
	 * Save operation leads to a save and then close action.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 *        	
	 * @return void
	 */
	public static function save($task = 'save', $text = 'JTOOLBAR_SAVE') {
		$bar = self::getToolbarInstance();

		// Add a save button.
		$bar->save ( $task, $text );
	}

	/**
	 * Writes a save and create new button for a given option.
	 * Save and create operation leads to a save and then add action.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 *        	
	 * @return void
	 */
	public static function save2new($task = 'save2new', $text = 'JTOOLBAR_SAVE_AND_NEW') {
		$bar = self::getToolbarInstance();

		// Add a save and create new button.
		$bar->save2new ( $task, $text );
	}

	/**
	 * Writes a save as copy button for a given option.
	 * Save as copy operation leads to a save after clearing the key,
	 * then returns user to edit mode with new key.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 *        	
	 * @return void
	 */
	public static function save2copy($task = 'save2copy', $text = 'JTOOLBAR_SAVE_AS_COPY') {
		$bar = self::getToolbarInstance();

		// Add a save and create new button.
		$bar->save2copy ( $task, $text );
	}

	/**
	 * Writes a checkin button for a given option.
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 * @param boolean $check
	 *        	True if required to check that a standard list item is checked.
	 *        	
	 * @return void
	 */
	public static function checkin($task = 'checkin', $text = 'JTOOLBAR_CHECKIN', $check = true) {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );
		$button = $factory->createButton ( $bar, 'Standard' );

		$button->name ( 'checkin' )->text ( $text )->task ( $task )->listCheck ( true );
		$bar->appendButton ( $button );
	}

	/**
	 * Writes a cancel button and invokes a cancel operation (eg a checkin).
	 *
	 * @param string $task
	 *        	An override for the task.
	 * @param string $alt
	 *        	An override for the alt text.
	 *        	
	 * @return void
	 */
	public static function cancel($task = 'cancel', $text = 'JTOOLBAR_CANCEL') {
		$bar = self::getToolbarInstance();

		$factory = new ContainerAwareToolbarFactory ();
		$factory->setContainer ( Factory::getContainer () );
		$button = $factory->createButton ( $bar, 'Standard' );

		$button->name ( 'cancel' )->text ( $text )->task ( $task )->listCheck ( false );
		$bar->appendButton ( $button );
	}
}