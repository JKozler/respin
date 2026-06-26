<?php
class mwPageSelectorTab
{

	private $_title;

	private $_id;

	private $_serviceClass;

	private $_service = null;

	public function __construct(array $tab)
	{
		$this->_id = $tab['id'];
		$this->_title = $tab['title'];
		$this->_serviceClass = 'mwPageSelectorTabService_' . $this->_id;
	}

	public function getId(): string
	{
		return $this->_id;
	}

	public function getTitle(): string
	{
		return $this->_title;
	}

	public function service(): mwPageSelectorTabService
	{
		if (!$this->_service) {
			$this->_service = $this->newService();
		}

		return $this->_service;
	}

	private function newService(): mwPageSelectorTabService
	{
		return new $this->_serviceClass($this);
	}

}
