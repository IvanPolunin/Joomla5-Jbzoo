<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Integrations;

// No direct access
defined('_JEXEC') or die;

class GetResponse extends Integration
{
	/**
	 * Create a new instance
	 * 
	 * @param array $options The service's required options
	 */
	public function __construct($options)
	{
		parent::__construct();
		$this->setKey($options);
		$this->endpoint = 'https://api.getresponse.com/v3';
		$this->options->set('headers.X-Auth-Token', 'api-key ' . $this->key);
		$this->options->set('headers.Accept-Encoding', 'gzip,deflate');
	}

	/**
	 *  Subscribe user to GetResponse Campaign
	 *
	 *  https://apidocs.getresponse.com/v3/resources/contacts#contacts.create
	 *
	 *  TODO: Update existing contact
	 *
	 *  @param   string   $email        	  Email of the Contact
	 *  @param   string   $name    			  The name of the Contact
	 *  @param   int	  $dayOfCycle		  Enter 0 to add to the start day of the cycle.
	 *  @param   object   $campaign  		  Campaign ID
	 *  @param   object   $customFields  	  Collection of custom fields
	 *  @param   object   $update_existing    Update existing contact
	 *  @param   array    $tags			      Set user tags
	 * 	@param 	 string	  $tags_replace		  Determines what changes to make to the subscriber's tags. Values: add_only, replace_all
	 *
	 *  @return  void
	 */
	public function subscribe($email, $name, $campaign, $customFields, $update_existing, $dayOfCycle = 0, $tags = [], $tags_replace = 'add_only')
	{
		$data = [
			'email' 			=> $email,
			'name'				=> $name,
			'dayOfCycle'		=> $dayOfCycle,
			'campaign' 			=> ['campaignId' => $campaign],
			'customFieldValues'	=> $this->validateCustomFields($customFields),
			'ipAddress' 		=> \NRFramework\User::getIP()
		];

		if (empty($name) || is_null($name))
		{
			unset($data['name']);
		}

		$contactId = null;
		$service_tags = [];

		if ($tags)
		{
			$service_tags = $this->getServiceTags();
		}

		// Replace all existing contact tags with new ones
		if ($tags && $tags_replace === 'replace_all')
		{
			$data['tags'] = $this->validateTags($tags, $service_tags, $tags_replace);
		}

		if ($update_existing) 
		{
			$contactId = $this->getContact($email);
		}

		$endpoint = 'contacts';
		$endpoint = !empty($contactId) ? $endpoint . '/' . $contactId : $endpoint;

		$this->post($endpoint, $data);

		// Add new tags to the contact
		if ($tags && $tags_replace === 'add_only' && $contactId)
		{
			$data = ['tags' => $this->validateTags($tags, $service_tags, $tags_replace)];

			$this->post('contacts/' . $contactId . '/tags', $data);
		}
	}

	/**
	 * Return all service tags.
	 * 
	 * @return  array
	 */
	private function getServiceTags()
	{
		$tags = [];

		foreach ($this->get('tags') as $tag)
		{
			$tags[$tag['tagId']] = $tag['name'];
		}

		return $tags;
	}
	
	/**
	 * Validates and returns the valid tags.
	 * 
	 * @param   array  $tags
	 * @param   array  $service_tags
	 * 
	 * @return  array
	 */
	private function validateTags($tags = [], $service_tags = [], $tags_replace = 'add_only')
	{
		$final_tags = [];

		foreach ($tags as $index => $tag)
		{
			$valid = false;
			
			// Find tag in service tags and add it to final tags list
			foreach ($service_tags as $tagId => $tagName)
			{
				if ($tagId === $tag || $tagName === $tag)
				{
					$valid = true;

					// Add to final list
					$final_tags[] = [
						'tagId' => $tagId
					];
				}
			}

			// Add invalid tags
			if (!$valid && $tags_replace == 'add_only')
			{
				$new_tag = $this->createTag($tag);
				$final_tags[] = [
					'tagId' => $new_tag['tagId']
				];
			}
		}

		return $final_tags;
	}

	private function createTag($tag)
	{
		$data = [
			'name' => $tag
		];
		
		return $this->post('tags', $data);
	}

	/**
	 *  Returns a new array with valid only custom fields
	 *
	 *  @param   array  $customFields   Array of custom fields
	 *
	 *  @return  array  Array of valid only custom fields
	 */
	public function validateCustomFields($customFields)
	{
		$fields = [];
	
		if (!is_array($customFields))
		{
			return $fields;
		}

		$accountCustomFields = $this->get('custom-fields');

		if (!$this->request_successful)
		{
			return $fields;
		}

		foreach ($accountCustomFields as $key => $customField)
		{
			if (!isset($customFields[$customField['name']]))
			{
				continue;
			}
				
			$fields[] = [
				'customFieldId' => $customField['customFieldId'],
				'value'			=> [$customFields[$customField['name']]]
			];
		}

		return $fields;
	}

	/**
	 * Get the last error returned by either the network transport, or by the API.
	 * If something didn't work, this should contain the string describing the problem.
	 * 
	 * @return  string  describing the error
	 */
	public function getLastError()
	{
		$body = $this->last_response->body;
		
		if (!isset($body['context']) || !isset($body['context'][0]))
		{
			return $body['codeDescription'] . ' - ' . $body['message'];
		}

		$error = $body['context'][0];

		// GetResponse returns a JSON string as $error and we try to decode it so we can return a more human-friendly error message
		$error = is_string($error) && json_encode($error, true) ? json_decode($error, true) : $error;

		if (is_array($error) && isset($error['fieldName'])) 
		{
			$errorFieldName = is_array($error['fieldName']) ? implode(' ', $error['fieldName']) : $error['fieldName'];
			return $errorFieldName . ': ' . $error['message'];
		}
		
		return (is_array($error)) ? implode(' ', $error) : $error;
		
	}

	/**
	 *  Returns all available GetResponse campaigns
	 *
	 *  https://apidocs.getresponse.com/v3/resources/campaigns#campaigns.get.all
	 *
	 *  @return  array
	 */
	public function getLists()
	{
		$data = $this->get('campaigns');

		if (!$this->success())
		{
			return;
		}

		if (!is_array($data) || !count($data))
		{
			return;
		}

		$lists = [];

		foreach ($data as $key => $list)
		{
			$lists[] = [
				'id'   => $list['campaignId'],
				'name' => $list['name']
			];
		}

		return $lists;
	}

	/**
	 *  Get the Contact resource
	 *
	 *  @param   string  $email  The email of the contact which we want to retrieve
	 *
	 *  @return  string          The Contact ID
	 */
	public function getContact($email)
	{
		if (!isset($email)) 
		{
			return;
		}

		$data = $this->get('contacts', ['query[email]' => $email]);

		if (empty($data)) 
		{
			return;
		}

		// the returned data is an array with only one contact
		$contactId = $data[0]['contactId'];

		return ($contactId) ? $contactId : null;
		
	}
}