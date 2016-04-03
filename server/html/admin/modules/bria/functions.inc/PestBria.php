<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
//If Pest doesn't exist, include it
if (!class_exists('Pest')) {
	include_once('vendor/autoload.php');
}

class PestBria extends PestXML{

	public $base_url = 'https://ccs3.cloudprovisioning.com:50443/ccs/';

	/**
		* Class constructor
		* @param string $user
		* @param string $pass
		* @throws Exception
		*/
	public function __construct($user, $pass) {

		try {
			parent::__construct($this->base_url);
			parent::setupAuth($user, $pass, $auth = 'digest');
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
		* Check to see if required params are available, and remove anything data
		* not in the param map
		* @param array $data
		* @param array $param_map
		* @throws Exception
		*/
	private function checkParamMap($data = array(), $param_map = array()) {
		foreach ($data as $key => $value) {
			if (!in_array($key, array_keys($param_map))) {
				unset($data[$key]);
			}

			if ($param_map[$key] && !isset($data[$key])) {
				throw new Exception ($key . ' is not set and is required');
			}
		}
		return $data;
	}

	/**
		* Obtain group information on all groups, on all top-level groups, or on the
		* child groups of a group. The response includes only groups that the
		* requesting administrator is associated with.
		*
		* @param array $data
		*/
	public function getGroup($data = array()) {
		$param_map = array(
			'groupName' => false,
			'parentGroupName' => false,
			'next' => false,
		);

		//Check our data
		$data = $this->checkParamMap($data, $param_map);

		return parent::get($this->base_url.'usergroup', $data, array());
	}

	/**
		* Obtain a specific user or users or all users in a group, and include all
		* the user-attributes defined for each user
		*
		* @param array $data
		*/
	public function getUser($data = array()) {
		$param_map = array(
			'groupName' => false,
			'userName' => false,
			'userNameSearch' => false,
			'offset' =>	false,
			'includeDevice' => false,
			'next' => false,
		);

		$data = $this->checkParamMap($data, $param_map);

		return parent::get($this->base_url.'user', $data, array());
	}

	/**
		* Add a user to a group and associate an existing profile with that user.
		* In addition, you can optionally create user- attributes for the user.
		*
		* @param array $data
		*/
	public function addUser($data = array()) {
		$base_url = $this->base_url.'user';
		$attributeString = null;
		$use_attributes = false;

		$headers = array();

		$param_map = array(
			'groupName' => false,
			'userName' => true,
			'password' => false,
			'profileName' =>	false,
		);

		//Go through our array and grab anything that is not in the param map, as
		//those are attributes
		foreach ($data as $key => $value) {
			if (!isset($param_map[$key])) {
				$attributeString .= $key . '=' . $value . "\n";
				unset($data[$key]);
			}
		}

		$data = $this->checkParamMap($data, $param_map);

		//If we have attributes, set the header to text/plain, and construct the url
		if (!empty($attributeString)) {
			$headers = array('Content-Type: text/plain');

			if (!empty($data)) {
				$base_url .= '?';
			}

			$count = 0;

			foreach($data as $urlKey => $urlValue) {
				if ($count > 0) {
					$base_url .= '&';
				}

				$base_url .= $urlKey . '=' . $urlValue;

				$count++;
			}
			$use_attributes = true;
		}

		return parent::post($base_url, ($use_attributes)?$attributeString:$data, $headers);
	}

	/**
		* Add user attributes to an existing user. This can also be done at user creation.
		*
		* @param array $data
		*/
	public function addUserAttribute($data = array()) {
		$headers = array();
		$param_map = array(
			'groupName' => false,
			'userName' => true,
			'password' => false,
			'profileName' =>	false,
			'attributeName' => true,
			'attributeValue' => true,
		);

		$data = $this->checkParamMap($data, $param_map);

		return parent::post($this->base_url.'user/attribute', $data, $headers);
	}

	/**
		* Modify the following information for a user: the user name, the password,
		* the profile associated with the user, the suspended state of the user.
		*
		* @param array $data
		*/
	public function editUser($data = array()) {
		$headers = array();

		$param_map = array(
			'groupName' => false,
			'userName' => true,
			'password' => false,
			'profileName' =>	false,
			'suspended' => false,
			'newUserName' => false,
		);

		$data = $this->checkParamMap($data, $param_map);

		return parent::put($this->base_url.'user', $data, $headers);
	}

	/**
		* Delete a user, including all its user-attributes.
		*
		* @param array $data
		*/
	public function deleteUser($data = array()) {
		$param_map = array(
			'groupName' => false,
			'userName' => true,
		);

		$data = $this->checkParamMap($data, $param_map);

		if (!empty($data)) {
			$url = $this->base_url.'user';

			if (count($data) > 0) {
				$url .= '?';
			}

			$count = 0;
			foreach ($data as $name => $value) {
				if ($count > 0) {
					$url .= '&';
				}

				$url .= $name .'='.$value;

				$count++;
			}

			return parent::delete($url, array());
		}
		return false;
	}
}
