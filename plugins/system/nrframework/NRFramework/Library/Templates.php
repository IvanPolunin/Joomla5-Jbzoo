<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Layout\LayoutHelper;

class Templates
{
	/**
	 * Library
	 * 
	 * @var   Library
	 */
	protected $library = [];

	/**
	 * The user download key.
	 * 
	 * @var  String
	 */
	private $download_key = null;

    public function __construct($library = [])
    {
        $this->library = $library;
		$this->download_key = $this->library->getLibrarySetting('license_key');
    }
    
	/**
	 * Checks whether we have the template locally and retrives its layout.
	 * If no local template is found, then retrieves it from remote and returns its layout.
	 * 
	 * @return  string
	 */
	public function tf_library_ajax_get_templates()
	{
		return $this->getTemplates($this->getList());
	}

	/**
	 * Checks whether the given license is valid or not and updates the "license_key_status" property
	 * which defines whether the pro templates should contain an error letting the user know that their
	 * license is invalid.
	 * 
	 * @return  void
	 */
	private function checkAndUpdateLicenseStatus()
	{
		$license_status = \NRFramework\Helpers\License::getRemoteLicenseData($this->download_key);
		$this->library->setLibrarySetting('license_key_status', !isset($license_status['error']) ? 'valid' : 'invalid');
	}

	/**
	 * Returns all available templates
	 * 
	 * @param   array  $templates
	 * 
	 * @return  array
	 */
	private function getTemplates($templates = [])
	{
		if (isset($templates->error) && $templates->error)
		{
			return $templates;
		}

		$this->checkAndUpdateLicenseStatus();
		
		$layout_payload = [
			'main_category_label' => $this->library->getLibrarySetting('main_category_label'),
			'project_name' => $this->library->getLibrarySetting('project_name'),
			'project_license_type' => $this->library->getLibrarySetting('project_license_type'),
			'project_version' => $this->library->getLibrarySetting('project_version'),
			'product_license_settings_url' => $this->library->getLibrarySetting('product_license_settings_url'),
			'template_use_url' => $this->library->getLibrarySetting('template_use_url'),
			'license_key' => $this->download_key,
			'license_key_status' => $this->library->getLibrarySetting('license_key_status'),
			'templates' => isset($templates->templates) ? $templates->templates : [],
			'favorites' => $this->library->favorites->getFavorites()
		];

		$filters_payload = [
			'filters' => $this->getTemplatesFilters(isset($templates->filters) ? $templates->filters : [])
		];

		$layouts_path = JPATH_PLUGINS . '/system/nrframework/layouts';

		return [
			'templates' => LayoutHelper::render('library/items_list', $layout_payload, $layouts_path),
			'filters' => LayoutHelper::render('library/filters', $filters_payload, $layouts_path)
		];
	}

	/**
	 * Returns the filters payload.
	 * 
	 * @param   object  $filters
	 * 
	 * @return  array
	 */
	private function getTemplatesFilters($filters)
	{
		// Main filters
		$data = [];

		$categories = isset($filters->categories) ? $filters->categories : [];
		if ($categories)
		{
			$data['category'] = [
				'label' => $this->library->getLibrarySetting('main_category_label', Text::_('NR_CATEGORIES_PLURAL')),
				'items' => $categories
			];
		}

		$goals = isset($filters->goals) ? $filters->goals : [];
		if ($goals)
		{
			$data['goal'] = [
				'label' => Text::_('NR_GOALS'),
				'items' => $goals
			];
		}

		// Add compatibility filter (Free/Pro filtering) only in the Lite version
		if ($this->library->getLibrarySetting('project_license_type') === 'lite')
		{
			$compatibility = isset($filters->compatibility) ? $filters->compatibility : [];
			if ($compatibility)
			{
				$data['compatibility'] = [
					'label' => Text::_('NR_COMPATIBILITY'),
					'items' => $compatibility
				];
			}
		}

		return $data;
	}

	public function hasFilters()
	{
		if (!$localTemplates = $this->getLocalTemplates())
		{
			return;
		}

		if (!isset($localTemplates->filters))
		{
			return;
		}

		$categories = isset($localTemplates->filters->categories) ? $localTemplates->filters->categories : [];
		$goals = isset($localTemplates->filters->goals) ? $localTemplates->filters->goals : [];
		$isFree = $this->library->getLibrarySetting('project_license_type') === 'lite';

		return $categories || $goals || $isFree;
	}

	/**
	 * Retrieve remote templates, store them locally and return new layout.
	 * 
	 * @return  string
	 */
	public function tf_library_ajax_refresh_templates()
	{
		return $this->getTemplates($this->getRemoteTemplatesAndStore());
	}

	/**
	 * Insert template.
	 * 
	 * @return  void
	 */
	public function tf_library_ajax_insert_template()
	{
		$template_id = $this->library->getLibrarySetting('template_id');
		
        // Get remote template
		$templates_url = str_replace('{{PROJECT}}', $this->library->getLibrarySetting('project'), TF_TEMPLATE_GET_URL);
		$templates_url = str_replace('{{DOWNLOAD_KEY}}', $this->download_key, $templates_url);
		$templates_url = str_replace('{{TEMPLATE}}', $template_id, $templates_url);

		$response = HttpFactory::getHttp()->get($templates_url);
		
		if (!$body = json_decode($response->body, true))
		{
			return [
				'error' => true,
				'message' => 'Cannot insert template.'
			];
		}

		// An error has occurred
		if (isset($body['error']) && $body['error'])
		{
			return [
				'error' => true,
				'message' => $body['response']
			];
		}

		// Prepare template
		$template = $body['response']['template'];
		// Set ID used to check if we are adding a valid template within the extension's item edit page
		$template['id'] = $body['response']['id'];

		// Save template locally so we can fetch its contents on redirect
		file_put_contents($this->library->getTemplatesPath() . 'template.json', json_encode($template));

		return [
			'error' => false,
			'message' => 'Inserting template.',
			'redirect' => $this->library->getLibrarySetting('template_use_url') . $template_id
		];
	}

    /**
     * Save templates locally
     * 
     * @param   array  $body
     * 
     * @return  void
     */
    private function saveLocalTemplate($body)
    {
        // Create directory if not exist
        if (!is_dir($this->library->getTemplatesPath()))
        {
            \NRFramework\File::createDirs($this->library->getTemplatesPath());
        }
		
        $path = $this->library->getTemplatesPath() . 'templates.json';
        
        file_put_contents($path, json_encode($body));
    }

    /**
     * Returns the local templates
     * 
     * @return  array
     */
	private function getLocalTemplates()
	{
        $path = $this->library->getTemplatesPath() . 'templates.json';

		if (!file_exists($path))
		{
			return false;
        }

		// If templates are old, fetch remote list
		if ($this->templatesRequireUpdate())
		{
			return false;
		}

		return json_decode(file_get_contents($path));
	}

	/**
	 * Checks whether the local templates list is older than X days.
	 * 
	 * @return  bool
	 */
	private function templatesRequireUpdate()
	{
        $path = $this->library->getTemplatesPath() . 'templates.json';

		$days_old = 7;

		/**
		 * If its older than X days, then request remote list
		 */
		// Get the modification time of the templates file
		$modTime = @filemtime($path);

		// Current time
		$now = time();

		// Minimum time difference
		$threshold = $days_old * 24 * 3600;

		// Do we need an update?
		return ($now - $modTime) >= $threshold;
	}

    /**
     * Returns the remote templates
     * 
     * @return  array
     */
	private function getRemoteTemplates()
	{
        // Get remote templates
		$templates_url = str_replace('{{PROJECT}}', $this->library->getLibrarySetting('project'), TF_TEMPLATES_GET_URL);

		$response = HttpFactory::getHttp()->get($templates_url);

		if (!$response = $response->body)
		{
			return;
		}

		if (!$response = json_decode($response))
		{
			$error = new \stdClass();
			$error->error = true;
			$error->message = sprintf(Text::_('NR_TEMPLATES_CANNOT_BE_RETRIEVED'), $this->library->getRefreshIcon());
			return $error;
		}

		return $response;
    }
    
    /**
     * Gets the remote templates and stores them locally
     * 
     * @return  array
     */
    private function getRemoteTemplatesAndStore()
    {
        $templates = $this->getRemoteTemplates();

        if (isset($templates->error) && $templates->error)
        {
            return $templates;
        }

        $this->saveLocalTemplate($templates);

        return $templates;
    }

    /**
     * Get templates list
     * 
     * @return  array
     */
    private function getList()
	{
		// try to find local templates with fallback remote templates
        return $this->getLocalTemplates() ?: $this->getRemoteTemplatesAndStore();
    }
}