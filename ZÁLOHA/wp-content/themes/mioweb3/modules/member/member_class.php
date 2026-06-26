<?php

/** Name of meta field of user, where its "billing id" is stored. */
define('META_BILLING_ID', 'billing_user_id');

use Fapi\FapiClient\AuthorizationException;
use Fapi\FapiClient\FapiClientFactory;
use Fapi\FapiClient\Tools\SecurityChecker;
use Mioweb\Lib\LockFactory;
use Mioweb\VisualEditor\Lib\Image;
use Mioweb\Member\Membership;
use Mioweb\Member\MemberPage;
use Mioweb\Member\MemberLevel;
use Mioweb\Member\MemberAccess;
use Mioweb\Member\MemberProfileAdmin;
use Mioweb\Member\Installer;
use Mioweb\Mailing\Mailer;

function mwMemberModule(): mwMemberModule
{
   return mwMemberModule::instance();
}

class mwMemberModule
{

	/** @var mwMemberModule Single instance holder. */
	protected static ?mwMemberModule $_instance = null;

	/** @var bool if is edit mode */
	private bool $_editMode;

	/** @var bool if is builder mode */
	private $_builderMode;

	/** @var array of MwMemberSection - list of all member sections */
	private ?array $_memberSections = null;

	/** @var null|MemberPage data about member page */
	private $_memberPage = null;

	/** @var bool if show default login */
	private $_isDefaultLogin = false;

	/** @var mwMember logged member */
	private mwMember $_currentMember;

	/** @var null|MwMemberSection */
	private $_memberSection = null;

	/** @var null|array of back compatibility convert table */
	private $_convertTable = null;

	/** @var null|array texts for javascripts */
	private ?array $_javascriptTexts = null;

	/** @var null|int current post id */
	private ?int $_postId = null;

	private ?WP_User $user;

	function __construct()
	{
		$this->_editMode = current_user_can('edit_pages') ? true : false;
		$this->_builderMode = $this->isEditMode() && !isset($_GET['mw_preview']);

		$this->initMemberModule();
		$this->addHooks();
	}

	public function initMemberModule(): void
	{
		// load member section objects and post types
		MwMemberSection::registerMemberSections();
		MwMemberNew::registerMemberNews();
		mwMember::registerMembers();
		MwMemberCustomField::registerMemberCustomFields();
		MemberLevel::registerMemberSectionLevels();

		// check version and migrations
		Installer::installUpdates();

		// load logged member
		$this->user = wp_get_current_user();
		$this->_currentMember = mwMember::createNew(wp_get_current_user());

		// add member sections to fast navigation
		if ($this->isBuilderMode()) {
			$this->addMemberToFastNav();
		}
	}

	public function addHooks(): void
	{
		// init member section in display
		add_action('ve_global_setting', [$this, 'displayMemberInit'], 1);
		// init member section in builder
		add_action('mw_builder_init', [$this, 'builderMemberInit'], 1);

		// enqueue scripts and styles
		add_action('wp_enqueue_scripts', [$this, 'enqueue_member_scripts']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 10);

		if ($this->isEditMode()) {
			// change option name in switch between global a local setting
			add_filter('mw_change_switch_option', [$this, 'change_switch_option'], 10, 2);

			add_filter('mw_fast_nav_current', [$this, 'fast_nav_current']);
		}

		//login custom
		add_action('login_head', [$this, 'custom_login_css']);

		//menu
		add_filter('wp_nav_menu_objects', [$this, 'member_menu']);

		// add role member
		add_role('member', __('Člen', 'cms_member'), ['read' => true, 'edit_posts' => false, 'delete_posts' => false]);

		// redirect after login if member login via wp login page
		add_filter('login_redirect', [$this, 'login_redirect'], 15, 3);

		// save user
		add_action('mw_save_user', ['MwMember', 'onSaveUser_hook'], 10, 2);
		add_action('mw_add_user', ['MwMember', 'onAddUser_hook'], 10, 3);

		// save checklist
		add_action('wp_ajax_nopriv_mwmSaveMemberChecklist', ['MwMember', 'saveMemberChecklist_ajax']);
		add_action('wp_ajax_mwmSaveMemberChecklist', ['MwMember', 'saveMemberChecklist_ajax']);

		// send reg form
		add_action('wp_ajax_nopriv_send_registration_form', ['Mioweb\Member\RegisterForm', 'sendRegisterForm']);
		add_action('wp_ajax_send_registration_form', ['Mioweb\Member\RegisterForm', 'sendRegisterForm']);

		if (!$this->isBuilderMode()) {
			add_action('body_class', [$this, 'addBodyClass']);

			add_action('wp', [$this, 'checkAccess'], 100);

			// load member profile admin
			new MemberProfileAdmin();
		}

		add_action('mw_header_icon', [$this, 'headerMemberAvatar']);

		add_action('wp_ajax_mwImportMembers', ['Mioweb\Member\MembersImport', 'importMembers_ajax']);

		// statistics
		add_action('wp_ajax_mwMemberReloadDashboardStatistics', ['Mioweb\Member\Dashboard', 'dashboardStatistics_ajax']);

		// disable wp app password
		//@TODO disable only for MW endpoints
		add_filter('wp_is_application_passwords_available', '__return_false');
		add_filter('wp_is_application_passwords_available_for_user', '__return_false');

		if (isset($_GET['add_new_member'])) {
			add_action('init', ['Mioweb\Member\Notifications', 'addMembershipNotify']);
		}
		if (isset($_GET['stop_membership'])) {
			add_action('init', ['Mioweb\Member\Notifications', 'stopMembershipNotify']);
		}
	}

	public function setPostId(int $postId): void
	{
		$this->_postId = $postId;
	}

	public function getPostId(): ?int
	{
		return $this->_postId;
	}

	public function isEditMode(): bool
	{
		return $this->_editMode;
	}

	public function isBuilderMode(): bool
	{
		return $this->_builderMode;
	}

	public function getMemberSections(): array
	{
		if ($this->_memberSections === null) {
			$this->_memberSections = MwMemberSection::getAll();
		}

		return $this->_memberSections;
	}

	public function getConvertTable(): array
	{
		if ($this->_convertTable === null) {
			$this->_convertTable = MWDB()->getOption('mwms_migration_compatibility', []);
		}

		return $this->_convertTable;
	}

	public function getConvertTableSection(int $sectionId): ?array
	{
		$convertTable = $this->getConvertTable();
		if ($convertTable && isset($convertTable[$sectionId])) {
			return $convertTable[$sectionId];
		}

		return null;
	}

	public function getConvertTableSectionId(int $sectionId): ?int
	{
		$convertTable = $this->getConvertTable();
		if ($convertTable && isset($convertTable[$sectionId])) {
			return $convertTable[$sectionId]['id'];
		}

		return null;
	}

	public function getConvertTableLevelId(int $sectionId, int $levelId): ?int
	{
		$convertTable = $this->getConvertTable();
		if ($convertTable && isset($convertTable[$sectionId]) && isset($convertTable[$sectionId]['levels'][$levelId])) {
			return $convertTable[$sectionId]['levels'][$levelId];
		}

		return null;
	}

	public function getMemberSection(int $id): ?MwMemberSection
	{
		return $this->getMemberSections()[$id] ?? null;
	}

	public function memberSectionIdExist(int $id): bool
	{
		return isset($this->getMemberSections()[$id]);
	}

	public function isMemberPage(): bool
	{
		return $this->memberPage() !== null;
	}

	public function setMemberPage(): void
	{
		if ($this->getPostId()) {
			$this->_memberPage = MemberPage::getOneById($this->getPostId());
		}
	}

	public function memberPage(): ?MemberPage
	{
		return $this->_memberPage;
	}

	public function setMemberSection(): void
	{
		if ($this->memberPage()) {
			$this->_memberSection = MwMemberSection::getOneById($this->memberPage()->getMemberSectionId());
		}
	}

	public function memberSection(): ?MwMemberSection
	{
		return $this->_memberSection;
	}

	public function isLoginPage(): bool
	{
		return $this->memberSection() && $this->memberSection()->getLoginId() === $this->getPostId();
	}

	public function isDefaultLogin(): bool
	{
		return $this->_isDefaultLogin;
	}

	public function isLogin(): bool
	{
		return $this->isLoginPage() || $this->isDefaultLogin();
	}

	function currentMember(): MwMember
	{
		return $this->_currentMember;
	}

	function currentMembership(): ?Membership
	{
		return $this->memberPage() ? $this->currentMember()->getMembership($this->memberPage()->getMemberSectionId()) : null;
	}

	function displayMemberInit($postId): void
	{
		$this->setPostId($postId);

		$this->memberSectionInit();

		if ($this->isMemberPage()) {
			global $vePage;
			$vePage->display->used_header = 'member_header';
			$vePage->display->page_setting = $this->memberSection()->getAppearanceSetting();
			$vePage->display->header_setting = $this->memberSection()->getHeaderSetting();
			$vePage->display->footer_setting = $this->memberSection()->getFooterSetting();

			if ($this->currentMember()->isLogged()) {
				$vePage->display->popups->popups_setting = $this->memberSection()->getPopupsSetting();
			}

			if ($this->memberSection()->getDashboardId()) {
				$vePage->display->home_url = $this->memberSection()->getUrl();
				$vePage->display->home_id = $this->memberSection()->getDashboardId();
			}
		}
	}

	public function builderMemberInit(int $postId): void
	{
		$this->checkUrl();
		$this->setPostId($postId);
		$this->memberSectionInit();
	}

	function memberSectionInit(): void
	{
		// set member page if is this page in member section
		$this->setMemberPage();

		// if is page in member section load data about member section
		$this->setMemberSection();

		//if is page of member section
		if ($this->isMemberPage()) {
			global $vePage;
			$vePage->modul_type = 'member';
			$vePage->object_id = 'member_page';
		}
	}

	public function checkAccess(): void
	{
		if ($this->isMemberPage()) {
			global $vePage;

			if ($this->isLoginPage() || $this->isEditMode()) {
				return;
			}
			// if user is not logged and page is not login page
			if (!$this->currentMember()->isLogged()) {
				// for redirect to myo
				do_action('mw_member_user_not_logged', $this->memberSection()->getId(), $this->getPostId());

				if ($this->memberSection()->getLoginId()) {
					//$vePage->resetPageId($this->memberSection()->getLoginId());
					$loginUrl = add_query_arg('redirect_to', urlencode(get_permalink($this->getPostId())), $this->memberSection()->getLoginUrl());
					wp_redirect($loginUrl);
					die();
				} else {
					// print default login page
					$this->_isDefaultLogin = true;

					$vePage->display->header_setting['show'] = 'none';
					$vePage->display->footer_setting['show'] = 'none';
					$vePage->display->page_setting['background_color'] = '#dbdbdb';
					$vePage->display->page_setting['background_image'] = [];
					$vePage->display->page_setting['page_width'] = ['size' => '400', 'unit' => 'px'];
					$vePage->display->template['directory'] = 'page/1/';
					$vePage->display->layer = $this->getDefaultLayer('login');
				}

				return;
			}

			if ($this->currentMembership() !== null) {
				$this->currentMember()->saveActivity($this->memberSection()->getId());
			}

			$access = new MemberAccess($this->currentMembership(), $this->memberPage(), $this->memberSection());
			if (!$access->checkAccess()) {
				$vePage->display->header_setting['show'] = 'none';
				$vePage->display->footer_setting['show'] = 'none';
				$access->showNoAccessPage();
			}
		}
	}

	function getDefaultLayer(string $pageName, array $args = []): array
	{
		$layer = require_once(__DIR__ . '/templates/default_templates/' . $pageName . '.php');

		return $layer ?? [];
	}

	function member_menu($items): array
	{
		$new_items = [];
		foreach ($items as $item) {
			if (!$item instanceof WP_Post) {
				// May be a plugin object like WPML_LS_Menu_Item - ignore it
				$new_items[] = $item;

				continue;
			}

			$objectId = $item->object_id;

			if (is_string($objectId) && is_numeric($objectId)) {
				$objectId = (int) $objectId;
			}

			if ($this->isPageVisible($objectId)) {
				$new_items[] = $item;
			}
		}

		return $new_items;
	}

	// @TODO use some function from element_print.php / element_memeber_subpages()
	function isPageVisible(int $postId): bool
	{
		if ($this->isEditMode()) {
			return true;
		}

		$memberPage = MemberPage::getOneById($postId);

		if ($memberPage) {
			if ($this->currentMembership() && $this->currentMembership()->hasLevelAccess($memberPage->getLevels())) {
				return true;
			}
			foreach ($memberPage->getLevels() as $mLevel) {
				$level = MemberLevel::getOneById($mLevel);
				if ($level && $level->isVisible()) {
					return true;
				}
			}

			return false;
		}

		return true;
	}

	function headerMemberAvatar(): void
	{
		if (defined('DOING_AJAX') && DOING_AJAX) {
			$this->setPostId($_POST['post_id']);
			$this->setMemberPage();
			$this->setMemberSection();
		}

		if ($this->isMemberPage() && !$this->isLogin()) {
			echo '<div id="member_user_avatar" class="member_user_menu_close">';
			echo $this->currentMember()->getAvatar(30);
			echo '<div id="member_user_menu">';

				echo '<h2 class="member_user_name">' . $this->currentMember()->getName() . '</h2>';

				echo '<ul>';
				echo '<li><a id="member_show_profile" href="#">' . __('Můj profil', 'cms_member') . '</a></li>';
				echo '<li><a href="' . $this->memberSection()->getLogoutUrl() . '" title="Logout">' . __('Odhlásit se', 'cms_member') . '</a></li>';
				echo '</ul>';

				if (count($this->currentMember()->getAllMemberships()) > 1) {
				echo '<div class="member_user_sections">' . __('Moje členské sekce:', 'cms_member') . '</div>';
				echo '<ul>';
				foreach ($this->getMemberSections() as $section) {
					if ($this->currentMember()->hasAccess($section->getId())) {
						echo '<li><a ' . ($section->getId() === $this->memberSection()->getId() ? 'class="mem_current_member"' : '') . ' href="' . $section->getUrl() . '">' . $section->getName() . '</a></li>';
					}
				}
				echo '</ul>';
				}

				if ($this->currentMembership()) {
				$end = null;
				if ($this->currentMembership()->getEnd()) {
					$end = $this->currentMembership()->getEnd('date');
				} elseif ($this->memberSection()->hasMonths()) {
					$end = $this->currentMembership()->getMonthsEnd('date');
				}
				if ($end) {
					echo '<div class="member_time_limited">';
						echo '<small>' . __('Členství do', 'cms_member') . ': <strong>' . $end . '</strong></small>';
						if ($this->memberSection()->getExtendUrl()) {
						echo '<a class="ve_content_button ve_conntent_button_1" target="_blank" href="' . $this->memberSection()->getExtendUrl() . '">' . __('Prodloužit', 'cms_member') . '</a>';
						}
						echo '</div>';
				}
				}

			echo '</div>';
			echo '</div>';
		}
	}

	public function getMemberSectionForLoginPage(int $postId): ?MwMemberSection
	{
		foreach ($this->getMemberSections() as $section) {
			if ($section->getLoginId() === $postId) {
				return $section;
			}
		}

		return null;
	}

	function getJavascriptTexts(string $key = ''): array
	{
		if ($this->_javascriptTexts === null) {
			$js_texts = require_once('js/js_texts.php');
			$this->_javascriptTexts = $js_texts;
		}

		return $this->_javascriptTexts[$key] ?? [];
	}

	function enqueue_member_scripts(): void
	{
		if ($this->isEditMode()) {
			$this->load_memmber_admin_scripts();
			wp_enqueue_script('tiny_mce_js');
		}

		if (!$this->isBuilderMode()) {
			$scriptVersion = MW()->scriptVersion();

			wp_register_script('member_front_script', MEMBER_DIR . 'js/front.js', ['ve-front-script'], $scriptVersion);
			wp_localize_script('member_front_script', 'mem_front_texts', $this->getJavascriptTexts('front'));

			wp_register_style('member_content_css', MEMBER_DIR . 'css/content.css', [], $scriptVersion);

			if ($this->isMemberPage() || $this->isEditMode()) {
				wp_enqueue_script('member_front_script');
				wp_enqueue_style('member_content_css');
			}
		}
	}
	function load_memmber_admin_scripts(): void
	{
		$scriptVersion = MW()->scriptVersion();

		wp_enqueue_script('member_admin_script', MEMBER_DIR . 'js/admin.js', ['jquery', 'cms_datepicker_cs'], $scriptVersion);
		wp_enqueue_style('member_admin_css', MEMBER_DIR . 'css/admin.css', [], $scriptVersion);
		wp_localize_script('member_admin_script', 'mem_texts', $this->getJavascriptTexts('admin'));
	}

	function enqueue_admin_scripts(): void
	{
		$current_screen = get_current_screen();
		if ($current_screen->id == 'page') {
			$this->load_memmber_admin_scripts();
		}
	}

	//body class
	function addBodyClass($classes): array
	{
		if ($this->isMemberPage()) {
			if ($this->memberSection()->hasLevels()) {
				foreach ($this->memberSection()->getLevels() as $level) {
					//print_r($level);
					$classes[] = 'member_section_level_' . $level->getId();
				}
			}
			$classes[] = 'member_section_page';
		}

		return $classes;
	}

	// Top panel member menu
	function addMemberToFastNav(): void
	{
		if (count($this->getMemberSections())) {
			$fastNav = [
				'id' => 'member',
				'title' => __('Členskou sekci', 'cms_member'),
			];
			$i = 1;
			$menu = '';
			foreach ($this->getMemberSections() as $section) {
				if ($section->getDashboardId()) {
					$menu .= '<li><a href="' . $section->getUrl('#') . '">' . $section->getName() . '</a></li>';
				}

				if ($i === 1) {
					$fastNav['url'] = $section->getUrl('#');
				}
				$i++;
			}
			if (!$menu) {
				$menu .= '<li><a href="#">' . __('Žádná z členských sekcí nemá nastavenou nástěnku.', 'cms_member') . '</a></li>';
			}
			$fastNav['submenu'] = '<ul>' . $menu . '</ul>';

			global $vePage;
			$vePage->addFastNav(
				$fastNav,
				13
			);
		}
	}

	function fast_nav_current($current)
	{
		if ($this->isMemberPage()) {
			$current['title'] = __('Členská sekce', 'cms_member');
			$current['url'] = $this->memberSection()->getUrl();
		}

		return $current;
	}

	function change_switch_option($option)
	{
		$opt = $option;
		$this->setPostId($_POST['item_id']);
		$this->setMemberPage();
		if ($this->memberPage()) {
			if ($option == 've_header') {
				$opt = 'mwms_header_' . $this->memberPage()->getMemberSectionId();
			} elseif ($option == 've_footer') {
				$opt = 'mwms_footer_' . $this->memberPage()->getMemberSectionId();
			}
		}

		return $opt;
	}

	public function getApiKey(): string
	{
		$api = get_option('member_api');
		if (!isset($api['token']) || !$api['token']) {
			$api['token'] = wp_generate_password(24, false);
			update_option('member_api', $api);
		}

		return $api['token'];
	}

	function login_redirect($redirect_to, $request, $user): string
	{
		// for redirect from wp login page
		if (!isset($_POST['cms_abort_redirect']) && isset($user->roles) && ($redirect_to === home_url()) && (is_array($user->roles) && (in_array('member', $user->roles) || in_array('subscriber', $user->roles)))) {
			$member = mwMember::createNew($user);
			$memberships = $member->getAllMemberships();
			if (count($memberships)) {
				$firstMembership = reset($memberships);
				if ($this->memberSectionIdExist($firstMembership->getMemberSectionId())) {
					$redirect_to = $this->getMemberSection($firstMembership->getMemberSectionId())->getUrl() ?: $redirect_to;
				}
			}
		}

		return $redirect_to;
	}

	function custom_login_css(): void
	{
		global $vePage;

		$login_css = get_option('member_login');

		echo '<style>';

		if (is_array($login_css)) {
			echo $vePage->generate_style('body.login', ['bg' => ['background_color' => ['color1' => $login_css['background_color'], 'color2' => ''], 'background_image' => $login_css['background_image']]]);
			echo $vePage->generate_style('.login #nav a, .login #backtoblog a, .login #nav a:hover, .login #backtoblog a:hover', ['color' => $login_css['font-color']]);

			echo $vePage->generate_style('.login h1 a', [
					'background-image' => 'url(' . Image::generateImageUrl($login_css['logo']) . ')',
					'background-size' => $login_css['width']['size'] . 'px ' . $login_css['height']['size'] . 'px',
					'width' => $login_css['width']['size'] . 'px',
					'height' => $login_css['height']['size'] . 'px',
			]);

			if (isset($login_css['background_image']) && isset($login_css['background_image']['cover']) && isset($login_css['background_image']['image']) && $login_css['background_image']['image']) {
				echo $vePage->generate_style('body.login', ['background-attachment' => 'fixed']);
			}
		}

		echo 'html {height: 100%;min-height: 100%;} body {min-height: 100%;}';
		echo '</style>';
	}

	function checkUrl(): void
	{
		if (get_option('mw_old_url') != get_home_url()) {
			MwSellingApi()->sendMemberInfo(); // send info when web url change
			update_option('mw_old_url', get_home_url());
		}
	}

	/** @return mwMemberModule Returns singleton instance of member module. */
	public static function instance(): mwMemberModule
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

}
