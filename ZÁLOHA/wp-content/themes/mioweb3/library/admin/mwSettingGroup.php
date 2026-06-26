<?php
class mwSettingGroup
{

	private $_id;

	private $_icon;

	private $_title;

	private $_home;

	public function __construct($args)
	{
		$this->_id = $args['id'];
		$this->_icon = $args['icon'] ?? 'settings';
		$this->_title = $args['title'];
		$this->_home = $args['home'];
	}

	public function getId(): string
	{
		return $this->_id;
	}

	public function getIcon(): string
	{
		return $this->_icon;
	}

	public function getTitle(): string
	{
		return $this->_title;
	}

	public function getUrl(): string
	{
		return get_mw_admin_url($this->_home);
	}

}
