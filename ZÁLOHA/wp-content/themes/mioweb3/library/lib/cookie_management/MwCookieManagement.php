<?php
use Mioweb\VisualEditor\Lib\Link;

function MwCookies()
{
	return MwCookieManagement::instance();
}

class MwCookieManagement
{

	protected static $_instance = null;

	/** @var array */
	private $setting;

	/** @var null|array */
	private $visitorPermissions;

	/** @var array */
	private $blockedList = null;

	function __construct()
	{
		$this->setting = get_option('web_option_others');
		$this->visitorPermissions = $this->getVisitorPermissions();
	}

	public function setting(): array
	{
		return $this->setting;
	}

	public function getBlockedSetting()
	{
		if ($this->blockedList === null) {
			$this->blockedList = get_option('mw_script_blocker');
		}

		return $this->blockedList;
	}

	public function getVisitorPermissions(): ?stdClass
	{
		if (isset($_COOKIE['mw_cookie_permissions'])) {
			return json_decode(stripslashes($_COOKIE['mw_cookie_permissions']));
		}

		return null;
	}

	public function isAccepted(): bool
	{
		return isset($this->visitorPermissions->id);
	}

	public function isPermitted(string $type): bool
	{
		global $current_user;

		if (!$this->useCookieManagement() || (isset($current_user->allcaps['edit_pages']) && $current_user->allcaps['edit_pages']) || $type === 'necessary') {
			return true;
		}

		return isset($this->visitorPermissions->permissions) && $this->visitorPermissions->permissions->$type;
	}

	public function useCookieManagement(): bool
	{
		return isset($this->setting['use_cookie']);
	}

	public function showDenyButton(): bool
	{
		return isset($this->setting['show_deny']) && $this->setting['show_deny'] === '1';
	}

	public function getMoreInfoLink()
	{
		return Link::create_link($this->setting['cookie_url_info'] ?? '', false);
	}

	public function printCookieBar(): void
	{
		if ($this->useCookieManagement()) {
			if ($this->setting['button_color']) {
				global $vePage;
				$vePage->display->css->addGlobalStyles([
					'.mw_cookie_button_secondary' => [
						'color' => $this->setting['button_color'] . ' !important',
						'border-color' => $this->setting['button_color'],
					],
					'.mw_cookie_button_primary, .mw_cookie_button_secondary:hover' => [
						'background-color' => $this->setting['button_color'],
					],
					'.mw_cookie_allow_all_button:hover' => [
						'box-shadow' => '0 0 2px ' . $this->setting['button_color'],
					],
				]);
			}

			$content = '';
			$moreInfoUrl = $this->getMoreInfoLink();

			$content .= '<div class="mw_cookie_management_container mw_cookie_bar_management_container mw_cookie_management_bg_' . ($this->setting['style'] ?? 'light') . '">';

			$content .= '<div class="mw_cookie_bar mw_cookie_bar_position_' . ($this->setting['position'] ?? 'bottom') . '">';

			$content .= '<div class="mw_cookie_bar_text">';
			if (isset($this->setting['main_title']) && $this->setting['main_title']) {
				$content .= '<div class="mw_cookie_bar_title mw_cookie_banner_title">' . $this->setting['main_title'] . '</div>';
			}
			$content .= '<p>';
			$content .= nl2br($this->setting['main_text']);
			if ($moreInfoUrl) {
				$content .= ' <a class="mw_cookie_more" target="_blank" href="' . $moreInfoUrl . '">' . __('Více informací', 'cms') . '</a>';
			}
			$content .= '</p>';
			$content .= '</div>';

			$content .= '<div class="mw_cookie_bar_buttons">';
			$content .= '<a class="mw_cookie_bar_setting_link mw_cookie_open_setting" href="#">' . __('Nastavení cookies', 'cms') . '</a>';
			if ($this->showDenyButton()) {
				$content .= '<a class="mw_cookie_button mw_cookie_button_secondary mw_cookie_deny_all_button" href="#">' . $this->setting['deny_all_text'] . '</a>';
			}
			$content .= '<a class="mw_cookie_button mw_cookie_button_primary mw_cookie_allow_all_button" href="#">' . $this->setting['allow_all_text'] . '</a>';
			$content .= '</div>';

			$content .= '</div>';

			$content .= $this->printCookieSettingPopup();

			$content .= '</div>';

			echo $content;
		}
	}

	public function printCookieSettingPopup(): string
	{
		$content = '<div class="mw_cookie_setting_popup">';

		$content .= '<a href="#" class="mw_cookie_setting_popup_close mw_icon mw_icon_style_1"><i>' . mw_content_icon('icon-cross2') . '</i></a>';

		$content .= '<div class="mw_cookie_setting_text">';
		if (isset($this->setting['popup_title']) && $this->setting['popup_title']) {
			$content .= '<div class="mw_cookie_setting_title mw_cookie_banner_title">' . $this->setting['popup_title'] . '</div>';
		}
		$content .= '<p>';
		$content .= nl2br($this->setting['popup_text']);
		$moreInfoUrl = $this->getMoreInfoLink();
		if ($moreInfoUrl) {
			$content .= ' <a class="mw_cookie_more" target="_blank" href="' . $moreInfoUrl . '">' . __('Více informací', 'cms') . '</a>';
		}
		$content .= '</p>';
		$content .= '</div>';

		$content .= '<div class="mw_cookie_setting_form">';

		$content .= '<div class="mw_cookie_setting_form_title mw_cookie_banner_title">' . __('Jednotlivé souhlasy', 'cms') . '</div>';
		$content .= '<form>';

		// necessary
		$content .= '<div class="mw_cookie_setting_form_item">';
		$content .= '<div class="mw_cookie_setting_form_item_head">';
		$content .= mwFrontComponents::switch([
			'name' => 'necessary',
			'disabled' => 'true',
			'switch_label' => '<strong class="mw_cookie_banner_title">' . $this->setting['necessary_title'] . '</strong>' . ($this->setting['necessary_description'] ? ' - ' . $this->setting['necessary_description'] : ''),
		], 1);
		$content .= '<span class="mw_cookie_setting_arrow"></span>';
		$content .= '</div>';
		$content .= '<div class="mw_cookie_setting_form_item_text">' . nl2br($this->setting['necessary_text']) . '</div>';
		$content .= '</div>';

		// preferences
		if (!isset($this->setting['preferences_hide'])) {
			$content .= '<div class="mw_cookie_setting_form_item">';
			$content .= '<div class="mw_cookie_setting_form_item_head">';
			$content .= mwFrontComponents::switch([
				'name' => 'preferences',
				'switch_label' => '<strong class="mw_cookie_banner_title">' . $this->setting['preferences_title'] . '</strong>' . ($this->setting['preferences_description'] ? ' - ' . $this->setting['preferences_description'] : ''),
			], $this->isPermitted('preferences') ? 1 : 0, 'mw_cookie_setting_switch_preferences');
			$content .= '<span class="mw_cookie_setting_arrow"></span>';
			$content .= '</div>';
			$content .= '<div class="mw_cookie_setting_form_item_text">' . nl2br($this->setting['preferences_text']) . '</div>';
			$content .= '</div>';
		}

		// analytics
		if (!isset($this->setting['analytics_hide'])) {
			$content .= '<div class="mw_cookie_setting_form_item">';
			$content .= '<div class="mw_cookie_setting_form_item_head">';
			$content .= mwFrontComponents::switch([
				'name' => 'analytics',
				'switch_label' => '<strong class="mw_cookie_banner_title">' . $this->setting['analytics_title'] . '</strong>' . ($this->setting['analytics_description'] ? ' - ' . $this->setting['analytics_description'] : ''),
			], $this->isPermitted('analytics') ? 1 : 0, 'mw_cookie_setting_switch_analytics');
			$content .= '<span class="mw_cookie_setting_arrow"></span>';
			$content .= '</div>';
			$content .= '<div class="mw_cookie_setting_form_item_text">' . nl2br($this->setting['analytics_text']) . '</div>';
			$content .= '</div>';
		}

		// marketing
		if (!isset($this->setting['marketing_hide'])) {
			$content .= '<div class="mw_cookie_setting_form_item">';
			$content .= '<div class="mw_cookie_setting_form_item_head">';
			$content .= mwFrontComponents::switch([
				'name' => 'marketing',
				'switch_label' => '<strong class="mw_cookie_banner_title">' . $this->setting['marketing_title'] . '</strong>' . ($this->setting['marketing_description'] ? ' - ' . $this->setting['marketing_description'] : ''),
			], $this->isPermitted('marketing') ? 1 : 0, 'mw_cookie_setting_switch_marketing');
			$content .= '<span class="mw_cookie_setting_arrow"></span>';
			$content .= '</div>';
			$content .= '<div class="mw_cookie_setting_form_item_text">' . nl2br($this->setting['marketing_text']) . '</div>';
			$content .= '</div>';
		}

		$content .= '</div>';

		$content .= '<div class="mw_cookie_setting_buttons">';
		$content .= '<a class="mw_cookie_button mw_cookie_button_secondary mw_cookie_save_setting" href="#">' . __('Uložit nastavení', 'cms') . '</a>';
		$content .= '<div class="mw_cookie_setting_buttons_left">';
		if ($this->showDenyButton()) {
			$content .= '<a class="mw_cookie_button mw_cookie_button_secondary mw_cookie_deny_all_button" href="#">' . $this->setting['deny_all_text'] . '</a>';
		}
		$content .= '<a class="mw_cookie_button mw_cookie_button_primary mw_cookie_allow_all_button" href="#">' . $this->setting['allow_all_text'] . '</a>';
		$content .= '</div>';
		$content .= '</div>';

		$content .= '</form>';

		$content .= '</div>';

		return $content;
	}

	public function printVideoInfo(string $url): string
	{
		if (!$this->isPermitted('analytics') && !isset($_COOKIE['mw_allow_video_youtube']) && (strpos($url, 'youtube') || strpos($url, 'youtu.be'))) {
			$content = '<div class="video_element_gdpr_content">';
			$content .= '<p>' . __('Přehráním videa souhlasíte se zásadami ochrany osobních údajů YouTube.', 'cms_ve') . '</p>';
			$content .= '<a href="https://policies.google.com/privacy" target="_blank">' . __('Zjistit více', 'cms_ve') . '</a>';
			$content .= '<a class="video_element_gdpr_agree_but ve_content_button ve_cb_hover_scale ve_content_button_size_medium" href="#">' . __('Povolit video', 'cms_ve') . '</a>';
			$content .= '<label><input type="checkbox" checked="checked"/>' . __('Vždy povolit Youtube videa', 'cms_ve') . '</label>';
			$content .= '</div>';

			return $content;
		}

		return '';
	}

	public static function saveConsent_ajax()
	{
		if ($_POST['id']) {
			self::saveConsent(
				$_POST['id'],
				$_POST['analytics'] ?? 0,
				$_POST['marketing'] ?? 0,
				$_POST['preferences'] ?? 0,
			);
		}
		die();
	}

	public static function saveConsent(string $id, int $analytics, int $marketing, int $preferences)
	{
		if (self::checkTable()) {
			MWDB()->insert('mw_cookie_consents', [
				'cc_identifier' => $id,
				'cc_statistics' => $analytics,
				'cc_preferences' => $preferences,
				'cc_marketing' => $marketing,
			]);
		}
	}

	public static function checkTable(): bool
	{
		if (!MWDB()->tableExist('mw_cookie_consents')) {
			return MWDB()->createTable(
				'mw_cookie_consents',
				'cc_id int(11) NOT NULL AUTO_INCREMENT,'
				. 'cc_identifier varchar(50) NOT NULL,'
				. 'cc_statistics tinyint(1) NOT NULL,'
				. 'cc_preferences tinyint(1) NOT NULL,'
				. 'cc_marketing tinyint(1) NOT NULL,'
				. 'cc_time timestamp,'
				. 'PRIMARY KEY (cc_id), INDEX (cc_identifier)',
				throw: false,
			);
		}

		return true;
	}

	/** @return MwCookieManagement Returns singleton instance of MwCookieManagement. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

}
