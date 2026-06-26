<?php
class mwSettingPage
{

	private $_id;

	private $_icon;

	private $_group;

	private $_parent;

	private $_title;

	private $_type;

	private $_link;

	private $_setting;

	private $_description;

	private $_infoFunction;

	private $_service;

	private $_serviceClass;

	private $_reloadOnSave;

	private $_alertOnLeave;

	public function __construct($args)
	{
		$this->_id = $args['id'];
		$this->_icon = $args['icon'] ?? 'settings';
		$this->_group = $args['group'];
		$this->_parent = $args['parent'] ?? '';
		$this->_title = $args['title'];
		$this->_type = $args['type'] ?? 'setting';
		$this->_setting = $args['setting'] ?? [];
		$this->_link = $args['link'] ?? '';
		$this->_infoFunction = $args['info_function'] ?? null;
		$this->_description = $args['description'] ?? '';
		$this->_serviceClass = $args['service_class'] ?? 'mwSettingPageService';
		$this->_reloadOnSave = $args['reload_on_save'] ?? false;
		$this->_alertOnLeave = $args['alert_on_leave'] ?? true;
	}

	public function service(): mwSettingPageService
	{
		if (!$this->_service) {
			$this->_service = $this->newService();
		}

		return $this->_service;
	}

	private function newService(): mwSettingPageService
	{
		return new $this->_serviceClass($this);
	}

	public function getId(): string
	{
		return $this->_id;
	}

	public function getIcon(): string
	{
		return $this->_icon;
	}

	public function getGroup(): string
	{
		return $this->_group;
	}

	public function getParent(): string
	{
		return $this->_parent;
	}

	public function getTitle(): string
	{
		return $this->_title;
	}

	public function getType(): string
	{
		return $this->_type;
	}

	public function getSetting(): array
	{
		return $this->_setting;
	}

	public function alertOnLeave(): bool
	{
		return $this->_alertOnLeave;
	}

	public function getDefaultSetting(): array
	{
		return mwSetting::getDefaultSetting($this->_setting);
	}

	public function getUrl(): string
	{
		return $this->_link ?: get_mw_admin_url($this->_id);
	}

	public function getSettingMenuInfo()
	{
		if ($this->_infoFunction) {
			$func = $this->_infoFunction;

			return $func();
		}

		return '';
	}

	public function getOption()
	{
		return $this->service()->getOption();
	}

	public function saveSetting($tosave)
	{
		$this->service()->saveSetting($tosave);
	}

	public function checkSaveHooks($tosave, $setting)
	{
		if (isset($setting['setting'])) {
			foreach ($setting['setting'] as $settingField) {
				$tosave = $this->checkSaveHooks($tosave, $settingField);
			}
		} elseif (isset($setting['save']) && isset($setting['id']) && !empty($setting['id'])) {
			$fieldName = $setting['id'];
			$fieldValue = &$tosave[$fieldName];
			$fieldSaved = false;

			if (isset($setting['savehook']) && is_callable($setting['savehook'])) {
				$func = $setting['savehook'];
				$func($setting, $fieldValue, $fieldSaved, $tosave);
			}

			if ($fieldSaved) {
				// Nothing to do
			} elseif ($setting['save'] == 'option') {
				$fieldSaved = true;
				update_option($fieldName, $fieldValue);
			}
			if ($fieldSaved) {
				unset($tosave[$fieldName]);
			}
		}

		return $tosave;
	}

	public function printForm()
	{
		$this->service()->printForm();
	}

	public function printTitle()
	{
		echo mwAdminComponents::title([
			'text' => $this->_title . mwSetting()->getHelpLink($this->_id),
			'description' => $this->_description,
		], 'h2');
	}

	public function printSaveBar()
	{
		if ($this->_reloadOnSave) {
			echo mwSetting::saveBar(__('Uložit', 'cms'), 'mw_setting_send_but');
		} else {
			echo mwSetting::saveBar(__('Uložit', 'cms'), 'mw_setting_save_but');
		}
	}

}
