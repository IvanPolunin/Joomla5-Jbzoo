<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
   Class: ElementSocialBookmarks
       The ElementSocialBookmarks element class
*/
class ElementSocialBookmarks extends Element implements iSubmittable {

	/*
		Function: hasValue
			Checks if the element's value is set.

	   Parameters:
			$params - render parameter

		Returns:
			Boolean - true, on success
	*/
	public function hasValue($params = array()) {
		return (bool) $this->get('value', $this->config->get('default'));
	}

	/*
		Function: render
			Override. Renders the element.

	   Parameters:
            $params - render parameter

		Returns:
			String - html
	*/
	public function render($params = array()) {

		if ($this->get('value', $this->config->get('default'))) {

			// init vars
			$bookmarks_config = $this->config->get('bookmarks');
			$bookmarks 		  = array();

			// get active bookmarks
			foreach (self::getBookmarks() as $bookmark => $data) {
				if ($this->config->get($bookmark)) {
					$bookmarks[$bookmark] = $data;
				}
			}

			// render layout
			if ($layout = $this->getLayout()) {
				return $this->renderLayout($layout, compact('bookmarks'));
			}
		}

		return null;
	}

	/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit() {
		return $this->app->html->_('select.booleanlist', $this->getControlName('value'), '', $this->get('value', $this->config->get('default')));
	}

	/*
		Function: renderSubmission
			Renders the element in submission.

	   Parameters:
            $params - AppData submission parameters

		Returns:
			String - html
	*/
	public function renderSubmission($params = array()) {
        return $this->edit();
	}

	/*
		Function: validateSubmission
			Validates the submitted element

	   Parameters:
            $value  - AppData value
            $params - AppData submission parameters

		Returns:
			Array - cleaned value
	*/
	public function validateSubmission($value, $params) {
		return array('value' => (bool) $value->get('value'));
	}

	/*
		Function: getBookmarks
			Get array of supported bookmarks.

		Returns:
			Array - Bookmarks
	*/
	public static function getBookmarks() {

		// Google
		$bookmarks['google']['link']  = "https://www.google.com/";
		$bookmarks['google']['click'] = "window.open('https://www.google.com/bookmarks/mark?op=add&amp;hl=en&amp;bkmk='+encodeURIComponent(location.href)+'&amp;title='+encodeURIComponent(document.title));return false;";

		// Facebook
		$bookmarks['facebook']['link']  = "https://www.facebook.com/";
		$bookmarks['facebook']['click'] = "window.open('https://www.facebook.com/sharer.php?u='+encodeURIComponent(location.href)+'&amp;t='+encodeURIComponent(document.title));return false;";

		// Reddit
		$bookmarks['reddit']['link']  = "https://reddit.com/";
		$bookmarks['reddit']['click'] = "window.open('https://reddit.com/submit?url='+encodeURIComponent(location.href)+'&amp;title='+encodeURIComponent(document.title));return false;";

		// Twitter
		$bookmarks['twitter']['link']  = "https://twitter.com/";
		$bookmarks['twitter']['click'] = "window.open('https://twitter.com/intent/tweet?status='+encodeURIComponent(document.title)+' '+encodeURIComponent(location.href));return false;";

		// Email
		$bookmarks['email']['link']  = "";
		$bookmarks['email']['click'] = "this.href='mailto:?subject='+document.title+'&amp;body='+encodeURIComponent(location.href);";

		return $bookmarks;
	}

}
