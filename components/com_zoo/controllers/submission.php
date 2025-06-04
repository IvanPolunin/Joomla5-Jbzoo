<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/*
	Class: SubmissionController
		Site submission controller class
*/
class SubmissionController extends AppController {

	/*
       Class constants
    */
	const SESSION_PREFIX   = 'ZOO_';
    const PAGINATION_LIMIT = 20;
	const TIME_BETWEEN_PUBLIC_SUBMISSIONS = 300;
	const EDIT_DATE_FORMAT = '%Y-%m-%d %H:%M:%S';

    /*
       Variable: submission
         Current submission.
    */
    public $application;
	public $submission;
    public $type;
    public $item_id;
    public $item;
    public $renderer;
    public $layout;
    public $layout_path;
	public $session_form_key;
    public $itemedit;

 	/*
		Function: Constructor

		Parameters:
			$default - Array

		Returns:
			DefaultController
	*/
	public function __construct($default = array()) {
		parent::__construct($default);

		// get user
		$this->user = $this->zoo->user->get();

		// get item id
        $this->item_id = $this->zoo->request->getInt('item_id');

		// get pathway
		$this->pathway = $this->zoo->system->application->getPathway();

        // in edit view?
        $this->itemedit = 'itemedit' === $this->zoo->request->getString('redirect', '');

        // get submission info from Request
        if (!$submission_id = $this->zoo->request->getInt('submission_id')) {

            // else get submission info from menu item
            if ($menu = $this->zoo->menu->getActive()) {

                $this->menu_params   = $this->zoo->parameter->create($menu->getParams());
                $submission_id = $this->menu_params->get('submission');
            }
        }

        if ($this->itemedit) {

            // set application
            $this->application = $this->zoo->table->item->get($this->item_id)->getApplication();

            // set submissiom
            $this->submission = $this->application->getItemEditSubmission();

            // set template
            $this->template   = $this->application->getTemplate();

            // set session form key
            $this->session_form_key = self::SESSION_PREFIX . 'SUBMISSION_FORM_' . $this->submission->id;

        } elseif ($this->submission  = $this->zoo->table->submission->get((int) $submission_id)) {

            // set application
            $this->application = $this->submission->getApplication();

            // set template
            $this->template    = $this->application->getTemplate();

			// set session form key
			$this->session_form_key = self::SESSION_PREFIX . 'SUBMISSION_FORM_' . $this->submission->id;

        }

		// load administration language files
		$this->zoo->system->language->load('', JPATH_ADMINISTRATOR, null, true);
		$this->zoo->system->language->load('com_zoo', JPATH_ADMINISTRATOR, null, true);

	}

    public function mysubmissions() {

        try {

            $this->_checkConfig();

			if (!$this->zoo->user->canAccess($this->user, 1)) {
				throw new SubmissionAccessException('Insufficient User Rights.');
			}

			// get request vars
			$order = $this->zoo->request->getCmd('order', $this->zoo->system->application->getParams()->get('order', 0));

            $limit = SubmissionController::PAGINATION_LIMIT;
            $state_prefix      = $this->option.'_'.$this->application->id.'.submission.'.$this->submission->id;
            $this->filter_type = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'.filter_type', 'filter_type', '', 'string');
			$search			   = $this->zoo->string->strtolower($this->zoo->system->application->getUserStateFromRequest($state_prefix.'.search', 'search', '', 'string'));
            $this->page        = $this->zoo->request->getInt('page', 1);

            $limitstart = (max(array($this->page, 1)) - 1) * $limit;

            $this->types = $this->submission->getSubmittableTypes();

            // set renderer
            $this->renderer = $this->zoo->renderer->create('item')->addPath(array($this->zoo->path->path('component.site:'), $this->template->getPath()));

            // type filter
            if (empty($this->filter_type)) {
                $type = $this->types;
            } else {
                $type = $this->filter_type;
            }

            $this->items      = $this->zoo->table->item->getByUser($this->application->id, $this->user->id, $type, $search, $order, $limitstart, $limit);
            $this->pagination = $this->zoo->pagination->create($this->zoo->table->item->getItemCountByUser($this->application->id, $this->user->id, $type, $search), $this->page, $limit, 'page', 'app');

            // type select
			if (count($this->types) > 1) {
				$options = array($this->zoo->html->_('select.option', '', '- '.Text::_('Select Type').' -'));
				foreach ($this->types as $id => $type) {
					$options[] = $this->zoo->html->_('select.option', $id, $type->name);
				}
				$this->lists['select_type'] = $this->zoo->html->_('select.genericlist', $options, 'filter_type', 'class="inputbox auto-submit"', 'value', 'text', $this->filter_type);
			}

			// add search
			$this->lists['search'] = $search;

			// Can a new item be added?
			$this->show_add = $this->_checkMaxSubmissions();

            // display view
            $this->getView('submission')->addTemplatePath($this->template->getPath())->setLayout('mysubmissions')->display();

        } catch (SubmissionAccessException $e) {
			// redirect to login for guest users
			if ($this->user->id == 0) {

				$return = urlencode(base64_encode(Uri::getInstance()->toString()));
				$link = Route::_(sprintf('index.php?option=com_users&view=login&return=%s', $return), false);

				$this->setRedirect($link, Text::_('Unable to access submissions'), 'error');
				$this->redirect();

			} else {

				$this->zoo->error->raiseWarning(0, (string) Text::_($e));

			}

        } catch (SubmissionControllerException $e) {

            // raise warning on exception
            $this->zoo->error->raiseWarning(0, (string) Text::_($e));

        }
    }

    public function submission() {

        try {

            $this->_init();

			// If it's a new item and the user has reached the max number of submissions, trigger error
			if (!$this->item->id && !$this->_checkMaxSubmissions()) {
				return $this->zoo->error->raiseNotice(0, 'You have reached your maximum number of submissions');
			}

			// bind data from sessions post data
			$this->errors = 0;
			if ($post = unserialize($this->zoo->system->application->getUserState($this->session_form_key, ''))) {

				// remove post data from session
				$this->zoo->system->application->setUserState($this->session_form_key, null);

				// bind data
				$this->errors = $this->_bind($post);

			}

            $this->cancelUrl = false;
            if ($this->redirectTo) {

				// build cancel url
				$this->cancelUrl = $this->_getRedirectLink();

				// build pathway
				$this->pathway->addItem($this->item->id ? Text::_('Edit Submission') : Text::_('Add Submission'));
            }

			// build captcha
			$this->captcha = false;
            if ($plugin = $this->submission->getParams()->get('captcha', false) and (!$this->submission->getParams()->get('captcha_guest_only', 0) || !$this->zoo->user->get()->id)) {
                $this->captcha  = Captcha::getInstance($plugin);
            }

            // display view
            $this->getView('submission')->addTemplatePath($this->template->getPath())->setLayout('submission')->display();

        } catch (SubmissionControllerException $e) {

            // raise warning on exception
            $this->zoo->error->raiseWarning(0, (string) Text::_($e));

        }

    }

    public function save() {

        // check for request forgeries
        $this->zoo->session->checkToken() or jexit('Invalid Token');

        // init vars
        $post	  = $this->zoo->request->get('post:', 'array');
		$msg	  = null;

        try {

            $this->_init();

			// set name on new item
			if (!$edit = (bool) $this->item->id) {
				$this->item->name = Text::_('Submitted Item');
			}

			// If it's a new item and the user has reached the max number of submissions, trigger error
			if (!$this->item->id && !$this->_checkMaxSubmissions()) {
				throw new AppControllerException('You have reached your maximum number of submissions');
			}

            // get element data from post
            if (isset($post['elements'])) {

                // filter element data
                if (!$this->submission->isInTrustedMode() && !$this->zoo->user->isJoomlaAdmin($this->user)) {
                    $this->zoo->request->setVar('elements', $this->zoo->submission->filterData($post['elements']));
                    $post = $this->zoo->request->get('post:', 'array');
                }

                // merge elements into post
                $post = array_merge($post, $post['elements']);
            }

			// merge userfiles element data with post data
			foreach ($_FILES as $key => $userfile) {
				if (strpos($key, 'elements_') === 0) {
					$post[str_replace('elements_', '', $key)]['userfile'] = $userfile;
				} else if ($key === 'elements' && !empty($userfile['name'])) {
				    foreach((array) $userfile['name'] as $identifier => $indexes) {
                        foreach ($indexes as $index => $values) {
                            if (is_array($values)) {
                                foreach ($values as $key => $name) {
                                    $post[$identifier][$index]["userfile_{$key}"] = array(
                                        'name'     => $name,
                                        'type'     => $userfile['type'][$identifier][$index][$key],
                                        'tmp_name' => $userfile['tmp_name'][$identifier][$index][$key],
                                        'error'    => $userfile['error'][$identifier][$index][$key],
                                        'size'     => $userfile['size'][$identifier][$index][$key],
                                    );
                                }
                            } else {
                                $post[$identifier][$index]['userfile'] = array(
                                    'name'     => $values,
                                    'type'     => $userfile['type'][$identifier][$index],
                                    'tmp_name' => $userfile['tmp_name'][$identifier][$index],
                                    'error'    => $userfile['error'][$identifier][$index],
                                    'size'     => $userfile['size'][$identifier][$index],
                                );
                            }
                        }
                    }
                }
			}

			$item_name = $this->item->name;

			$error = $this->_bind($post);

			// Check captcha
			if ($plugin = $this->submission->getParams()->get('captcha', false) and (!$this->submission->getParams()->get('captcha_guest_only', 0) or !$this->zoo->user->get()->id)) {

				$captcha = Captcha::getInstance($plugin);

            	if (!$captcha->checkAnswer(@$post['captcha'])) {
            		$error = $captcha->getError();
					if (!($error instanceof Exception)) {
						$error = new Exception($error);
					}
					// raise warning on exception
					$this->zoo->error->raiseWarning(0, Text::_('ZOO_CHECK_CAPTCHA') . ' - ' . Text::_($error));
            	}
			}

			// save item if it is valid
            if (!$error) {

                // set alias
				if (!$edit || $item_name != $this->item->name) {
					$this->item->alias = $this->zoo->alias->item->getUniqueAlias($this->item->id, $this->zoo->string->sluggify($this->item->name));
				}

				// unpublish item in none trusted state
				if (!$this->submission->isInTrustedMode() && !in_array('_itemstate', array_keys($this->elements_config))) {
					$this->item->state = 0;
				}

                // set modified
                $this->item->modified	 = $this->zoo->date->create()->toSQL();
                $this->item->modified_by = $this->user->get('id');

				// enforce time limit on submissions
				if (!$edit && !$this->submission->isInTrustedMode()) {
					$timestamp = time();
					if ($timestamp < $this->zoo->system->session->get('ZOO_LAST_SUBMISSION_TIMESTAMP') + SubmissionController::TIME_BETWEEN_PUBLIC_SUBMISSIONS) {
						$this->zoo->system->application->setUserState($this->session_form_key, serialize($post));
						throw new SubmissionControllerException('You are submitting too fast, please try again in a few moments.');
					}
					$this->zoo->system->session->set('ZOO_LAST_SUBMISSION_TIMESTAMP', $timestamp);
				}

				// deprecated as of version 2.5.7 call to doUpload, use before save event instead
				foreach ($this->elements_config as $element) {
					if (($element = $this->item->getElement($element['element'])) && $element instanceof iSubmissionUpload) {
						$element->doUpload();
					}
				}

				// Add primary category if no primary category is set (i.e: no itemcategory element present)
				$primary_category = $this->item->getPrimaryCategory();
				if (!$edit && empty($primary_category) && $category = $this->submission->getForm($this->type->id)->get('category')) {
					$this->item->getParams()->set('config.primary_category', $category);
				}

				// trigger before save event
				$this->zoo->event->dispatcher->notify($this->zoo->event->create($this->submission, 'submission:beforesave', array('item' => $this->item, 'new' => !$edit)));

                // save item
                $this->zoo->table->item->save($this->item);

                // save to default category
				if (!$edit && ($category = $this->submission->getForm($this->type->id)->get('category'))) {
					$this->zoo->category->saveCategoryItemRelations($this->item, array($category));
				}

                // set redirect message
				$msg = Text::_($edit ? 'Item saved' : ($this->submission->isInTrustedMode() ? 'Thanks for your submission.' : 'Thanks for your submission. It will be reviewed before being posted on the site.'));

				// trigger saved event
				$this->zoo->event->dispatcher->notify($this->zoo->event->create($this->submission, 'submission:saved', array('item' => $this->item, 'new' => !$edit, 'msg' => &$msg)));

            } else {

				// add post data to session if form is not valid
				$this->zoo->system->application->setUserState($this->session_form_key, serialize($post));

            }

        } catch (SubmissionControllerException $e) {

			$error = true;

            // raise warning on exception
            $this->zoo->error->raiseWarning(0, (string) Text::_($e));

        } catch (AppException $e) {

			$error = true;

            // raise warning on exception
            $this->zoo->error->raiseWarning(0, Text::_('There was an error saving your submission, please try again later.'));

            // add exception details, for super administrators only
            if ($this->user->superadmin) {
                $this->zoo->error->raiseWarning(0, (string) $e);
            }

        }

		// If an error is found, redirect to the edit form itself
		$link = $error ? $this->zoo->route->submission($this->submission, $this->type->id, null, $this->item_id, $this->redirectTo) : $this->_getRedirectLink();

        $this->setRedirect(Route::_($link, false), $msg);
    }

    public function remove() {

        // init vars
        $msg = null;

        try {

            $this->_checkConfig();

            if (!$this->submission->isInTrustedMode()) {
                throw new AppControllerException('The submission is not in Trusted Mode.');
            }

			// get item table and delete item
			$table = $this->zoo->table->item;

            $item = $table->get($this->item_id);

            // is current user the item owner and does the user have sufficient user rights
            if ($item->id && (!$item->canAccess($this->user) || $item->created_by != $this->user->id)) {
                throw new AppControllerException('You are not allowed to make changes to this item.');
            }

            $table->delete($item);

			// set redirect message
			$msg = Text::_('Submission Deleted');

			// trigger deleted event
			$this->zoo->event->dispatcher->notify($this->zoo->event->create($item, 'submission:deleted'));

		} catch (AppException $e) {

            // raise warning on exception
            $this->zoo->error->raiseWarning(0, Text::_('There was an error deleting your submission, please try again later.'));

            // add exception details, for super administrators only
            if ($this->user->superadmin) {
                $this->zoo->error->raiseWarning(0, (string) Text::_($e));
            }

		}

        $this->setRedirect(Route::_($this->zoo->route->mysubmissions($this->submission), false), $msg);

    }

	public function loadtags() {

		// get request vars
		$tag = $this->zoo->request->getString('tag', '');
		$app_id = $this->zoo->request->getInt('app_id', 0);

		echo $this->zoo->tag->loadTags($app_id, $tag);
	}

    protected function _checkConfig() {

        if (!$this->application || !$this->submission) {
            throw new SubmissionControllerException('Submissions are not configured correctly.');
        }

        if (!$this->submission->getState()) {
            throw new SubmissionControllerException('Submissions are disabled.');
        }

        // Check ACL on item edit
        if (!($this->itemedit && $this->zoo->table->item->get($this->item_id)->canEdit()) && !(!$this->itemedit && $this->submission->canAccess($this->user))) {
            throw new SubmissionAccessException('Insufficient User Rights.');
        }
    }

	protected function _checkMaxSubmissions() {

		$max_submissions = $this->submission->getParams()->get('max_submissions', '0');

		// Infinite: all ok
		if ($max_submissions == '0') {
			return true;
		}

		$current_submissions = $this->zoo->table->item->getItemCountByUser($this->application->id, $this->user->id);

		return $current_submissions < $max_submissions;

	}

    protected function _init() {

        //init vars
        $type_id          = $this->zoo->request->getCmd('type_id');
        $hash             = $this->zoo->request->getCmd('submission_hash');
        $this->redirectTo = urldecode($this->zoo->request->getString('redirect', ''));

        // check config
        $this->_checkConfig();

        // get submission info from request
        if ($type_id) {

            if ($hash != $this->zoo->submission->getSubmissionHash($this->submission->id, $type_id, $this->item_id, $this->itemedit)) {
                throw new SubmissionControllerException('Hashes did not match.');
            }

        // else get submission info from active menu
        } elseif ($this->menu_params) {
            $type_id = $this->menu_params->get('type');

            // remove item_id (menu item may not have an item_id)
            $this->item_id = null;
        }

        // set type
        $this->type  = $this->submission->getType($type_id);

        // check type
        if (!$this->type) {
            throw new SubmissionControllerException('Submissions are not configured correctly.');
        }

        // set hash
        $this->hash = $hash ? $hash : $this->zoo->submission->getSubmissionHash($this->submission->id, $this->type->id, $this->item_id, $this->itemedit);

        // set layout
        $this->layout = $this->submission->getForm($this->type->id)->get('layout', '');

        // check layout
        if (empty($this->layout)) {
            throw new SubmissionControllerException('Submission is not configured correctly.');
        }

		// set renderer
		$this->renderer = $this->zoo->renderer->create('submission')->addPath(array($this->zoo->path->path('component.site:'), $this->template->getPath()));

        // set layout path
        $this->layout_path = 'item.';
        if ($this->renderer->pathExists('item/'.$this->type->id)) {
                $this->layout_path .= $this->type->id.'.';
        }
        $this->layout_path .= $this->layout;

        // get item
		if (!$this->item_id || !($this->item = $this->zoo->table->item->get($this->item_id))) {

			$now = $this->zoo->date->create()->toSQL();

            $this->item = $this->zoo->object->create('Item');
            $this->item->application_id   = $this->application->id;
            $this->item->type			  = $this->type->id;
			$this->item->publish_up		  = $now;
			$this->item->publish_down	  = $this->zoo->database->getNullDate();
			$this->item->access			  = $this->zoo->joomla->getDefaultAccess();
			$this->item->created		  = $now;
			$this->item->created_by		  = $this->user->get('id');
			$this->item->created_by_alias = '';
			$this->item->state			  = 0;
			$this->item->searchable		  = true;
			$this->item->getParams()
				->set('config.enable_comments', true)
				->set('config.primary_category', 0);
        }

        // get positions
        $positions = $this->renderer->getConfig('item')->get($this->application->getGroup().'.'.$this->type->id.'.'.$this->layout, array());

        // get elements from positions
        $this->elements_config = array();
        foreach ($positions as $position) {
            foreach ($position as $params) {
                if ($el = $this->type->getElement($params['element']) and $el->canAccess()) {
                    $params['_position'] = $position;
                    if (false !== $this->zoo->event->dispatcher->notify($this->zoo->event->create($this->item, 'submission:elementinit', array('params' => $params, 'element' => $el)))->getReturnValue()) {
                       $this->elements_config[$params['element']] = $params;
                    }
                }
            }
        }

    }

	protected function _bind($post = array()) {
        $this->zoo->loader->register('AppValidatorException', 'classes:validator.php');

        $errors = 0;
		foreach ($this->elements_config as $element_data) {

			try {

				if (($element = $this->item->getElement($element_data['element']))) {

					// get params
					$params = $this->zoo->data->create(array_merge(array('trusted_mode' => $this->submission->isInTrustedMode()), $element_data));

					$element->bindData($element->validateSubmission($this->zoo->data->create(@$post[$element->identifier]), $params));

                    // trigger after element validation
                    $this->zoo->event->dispatcher->notify($this->zoo->event->create($this->submission, 'submission:elementvalidate', array('element' => $element, 'params' => $params, 'post' => $post)));

				}

			} catch (AppValidatorException $e) {

				if (isset($element)) {
					$element->error = $e;
					$element->bindData(@$post[$element->identifier]);
				}

				$errors++;
			}

		}
		return $errors;
	}

	protected function _getRedirectLink() {

		// Check redirect link
		switch ($this->redirectTo) {
			case null:

				// Check menu item for redirect
				if ($menu = $this->zoo->menu->getActive()) {
					if ($menu_item_id = $this->zoo->parameter->create($menu->getParams())->get('redirect', false)) {
						return Route::_('index.php?Itemid=' . $menu_item_id, false);
					}
				}
				return $this->zoo->route->submission($this->submission, $this->type->id, null, $this->item_id, $this->redirectTo);

			case 'mysubmissions':
				// redirect to list of submissions
				return $this->zoo->route->mysubmissions($this->submission);
			case 'itemedit':
				// Redirect to the item
				if ($this->item->isPublished()) {
					return $this->zoo->route->item($this->item);
				} else {
					return $this->zoo->route->submission($this->submission, $this->type->id, null, $this->item_id);
				}
			default:
				// Custom redirect
				return base64_decode($this->redirectTo);
		}
	}

}

/*
	Class: SubmissionControllerException
*/
class SubmissionControllerException extends AppException {}
class SubmissionAccessException extends SubmissionControllerException {}
