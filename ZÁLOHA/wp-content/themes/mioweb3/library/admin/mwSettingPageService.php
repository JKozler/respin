<?php
class mwSettingPageService
{

	private $_settingPage;

	private $_option;

	public function __construct(mwSettingPage $settingPage)
	{
		$this->_settingPage = $settingPage;
	}

	public function settingPage()
	{
		return $this->_settingPage;
	}

	public function getOption()
	{
		if (!$this->_option) {
			$this->_option = MWDB()->getOption($this->settingPage()->getId());
		}

		return $this->_option;
	}

	public function saveSetting($tosave)
	{
		foreach ($this->settingPage()->getSetting() as $setField) {
			$tosave = $this->settingPage()->checkSaveHooks($tosave, $setField);
		}

		MWDB()->setOption($this->settingPage()->getId(), $tosave);

		// TODO remove hook ? back compatibility
		do_action('ve_after_save_options');

		mwSetting::saveUsed($tosave);
	}

	public function printForm()
	{
		$option = $this->getOption();
		$option = mwBackCompatibility::option_set($option, $this->settingPage()->getId());

		write_meta($this->settingPage()->getSetting(), $option, 'setting', 'setting');

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');

		echo '<input type="hidden" name="setting_id" value="' . $this->settingPage()->getId() . '"/>';
	}

}
