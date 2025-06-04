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

class HubSpot3 extends Integration
{
	/**
	 * Create a new instance
	 * 
	 * @param string $key Your HubSpot API key
	 */
	public function __construct($options)
	{
		parent::__construct();

		$this->setKey($options);

		$this->setEndpoint('https://api.hubapi.com/crm/v3');

		$this->options->set('headers.Authorization', 'Bearer ' . $this->key);
	}

	/**
	 *  Create/Update a HubSpot Contact
	 *
	 *  API References:
	 *  https://developers.hubspot.com/docs/api/crm/contacts
	 *
	 *  @param   string   $email 			User's email address
	 *  @param   string   $params  			The forms extra fields
	 *  @param   bool     $update_existing  Set whether to update an existing user
	 *
	 *  @return  void
	 */
	public function subscribe($email, $params, $update_existing = true)
	{
		$contact_data = $this->contactExists($email);

		if (!$update_existing)
		{
			if ($contact_data)
			{
				throw new \Exception('Contact already exists.');
			}
		}

		$default_property = ['email' => $email];

		$other_properties = $this->validateCustomFields($params);

		$data = [
			'properties' => array_merge($default_property, $other_properties)
		];

		$method = 'post';
		$endpoint = 'objects/contacts';

		if ($update_existing && $contact_data)
		{
			$method = 'patch';
			$endpoint .= '/' . $contact_data['id'];
		};

		$this->$method($endpoint, $data);

		// If a list exists, add the contact to that list.
		if ($this->success() && isset($params['list']) && !empty($params['list']))
		{
			$this->addContactToStaticList($email, $params['list']);
		}
	}

	/**
	 * Returns all lists.
	 * 
	 * @return  array
	 */
	public function getLists()
	{
		$this->endpoint = $this->getV1Endpoint();
		
		$data = $this->get('lists/static');

		if (!$this->success())
		{
			return;
		}

		if (!is_array($data) || !count($data) || !isset($data['lists']))
		{
			return;
		}

		$lists = [];

		foreach ($data['lists'] as $key => $list)
		{
			$lists[] = [
				'id'   => $list['listId'],
				'name' => $list['name']
			];
		}

		return $lists;
	}

	/**
	 * Add contact to a static list.
	 * 
	 * @param   string  $email
	 * @param   int		$list_id
	 * 
	 * @return  void
	 */
	public function addContactToStaticList($email, $list_id)
	{
		$this->endpoint = $this->getV1Endpoint();

		$data = (object) [ 'emails' => [ $email ] ];
		
		$this->post('lists/' . $list_id . '/add', $data);
	}

	/**
	 * Return the v1 endpoint.
	 * 
	 * @return  string
	 */
	private function getV1Endpoint()
	{
		return 'https://api.hubapi.com/contacts/v1';
	}
	
	/**
	 * Check whether contact already exists.
	 * 
	 * @param   string  $email
	 * 
	 * @return  bool
	 */
	public function contactExists($email)
	{
		$contact = $this->get('objects/contacts/' . $email . '?idProperty=email');

		return $this->success() ? $contact : false;
	}

	/**
	 *  Returns a new array with valid only custom fields
	 *
	 *  API References:
	 *  https://developers.hubspot.com/docs/api/crm/properties
	 *
	 *  @param   array  $formCustomFields   Array of custom fields
	 *
	 *  @return  array  					Array of valid only custom fields
	 */
	public function validateCustomFields($formCustomFields)
	{
		$fields = [];

		if (!is_array($formCustomFields))
		{
			return $fields;
		}

		$contactCustomFields = $this->get('properties/Contact');

		if (!$this->request_successful)
		{
			return $fields;
		}

		$customFieldNames = array_map(
			function ($ar)
			{
				return $ar['name'];
			}, $contactCustomFields['results']
		);

		$formCustomFieldsKeys = array_keys($formCustomFields);

		foreach ($customFieldNames as $accountFieldName)
		{
			if (!in_array($accountFieldName, $formCustomFieldsKeys))
			{
				continue;
			}

			$fields[$accountFieldName] = $formCustomFields[$accountFieldName];
		}

		return $fields;
	}

	/**
	 *  Get the last error returned by either the network transport, or by the API.
	 *
	 *  @return  string
	 */
	public function getLastError()
	{
		$body = $this->last_response->body;
		
		if (isset($body['status']) && $body['status'] === 'error')
		{
			return $body['message'];
		}
	}
}