<?php

use Nette\Utils\Random;

/**
 * Class for setting
 */
function get_mw_admin_url($setting): string
{
	return add_query_arg(['setting' => $setting], home_url('/mw-admin/'));
}

function is_mw_setting(): bool
{
	return (bool) get_query_var('mw-admin');
}

function mwSetting()
{
	return mwSetting::instance();
}

class mwSetting
{

	protected static $_instance = null;

	// setting pages
	protected $_groups = []; // setting groups

	protected $_pages = []; // pages of setting

	protected $_pageStructure = []; // hierarchy of setting pages

	protected $_pageSettingTypes = []; // defined own page types

	protected $_currentPage;

	protected $_currentGroup;

	protected $_isSetting = false; //is setting page

	protected $_objects = [];

	protected $_currentObject;

	protected $_settingPage;

	protected $_userSetting = [];

	public $currentUser;

	private $backUrl = '';

	function __construct()
	{
		add_action('wp', [$this, 'init'], 10);

		// add mw-admin as rewrite rule
		add_action('init', [$this, 'add_rewrite_rule']);
		add_action('query_vars', [$this, 'set_query_var']);
		add_filter('template_include', [$this, 'include_template'], 1000, 1);

		add_action('wp_ajax_mwSaveSetting', [$this, 'saveSetting_ajax']);
		add_action('wp_ajax_mwSaveObject', [$this, 'saveObject_ajax']);
		add_action('wp_ajax_mwSaveObjectSet', [$this, 'saveObjectSet_ajax']);
		add_action('wp_ajax_mwAddNewObject', [$this, 'addNewObject_ajax']);
		add_action('wp_ajax_mwDeleteObjectItem', [$this, 'deleteObjectItem_ajax']);
		add_action('wp_ajax_mwArchiveItem', [$this, 'archiveItem_ajax']);
		add_action('wp_ajax_mwDeArchiveItem', [$this, 'deArchiveItem_ajax']);
		add_action('wp_ajax_mwRestoreObjectItem', [$this, 'restoreObjectItem_ajax']);
		add_action('wp_ajax_mwSetObjectVisibility', [$this, 'setObjectVisibility_ajax']);
		add_action('wp_ajax_mwSetListReload', [$this, 'setListReload_ajax']);
		add_action('wp_ajax_mwEmptyTrash', [$this, 'emptyTrash_ajax']);

		add_action('wp_ajax_mwSetDefaultItem', [$this, 'setDefaultItem_ajax']);

		// comments
		add_action('wp_ajax_mwSetCommentStatus', [$this, 'setCommentStatus_ajax']);

		// fast add
		add_action('wp_ajax_mwOpenFastAdd', [$this, 'openFastAdd_ajax']);
		add_action('wp_ajax_mwSaveFastAddObject', [$this, 'addFastNewObject_ajax']);

		// fast copy
		add_action('wp_ajax_mwOpenFastCopy', [$this, 'openFastCopy_ajax']);
		add_action('wp_ajax_mwSaveFastCopyObject', [$this, 'addFastCopyObject_ajax']);

		// export
		if (isset($_GET['mw_download_export'])) {
			add_action('init', [$this, 'downloadExportObjectItems']);
		}
		add_action('wp_ajax_mwCreateExportObjectItems', [$this, 'createExportObjectItems_ajax']);
		add_action('wp_ajax_mwOpenObjectExportForm', [$this, 'openObjectExportForm_ajax']);

		// WP ADMIN

		//add mw setting for posts in wp
		add_action('add_meta_boxes', [$this, 'addWpMetaBoxes'], 10, 2);
		add_action('save_post', [$this, 'saveMetasInWpAdmin']);
		add_action('edit_form_after_title', [$this, 'addEditPageButtonToWP']);
		add_filter('use_block_editor_for_post_type', [$this, 'mwDisableGutenbergForPages'], 10, 2);

		// check slug
		add_action('wp_ajax_mwCheckSlug', [$this, 'checkSlug_ajax']);

		// generate password
		add_action('wp_ajax_mwGeneratePassword', [$this, 'generatePassword_ajax']);

		// save item parent
		add_action('wp_ajax_mwUpdateOrder', [$this, 'updateOrder_ajax']);

		// terms order
		add_filter('terms_clauses', [$this, 'updateTermsOrderby'], 10, 3);
		add_action('pre_delete_term', [$this, 'reorderOnDeleteTerm'], 10, 2);

		// user contact fields
		add_filter('user_contactmethods', [$this, 'extraContactInfo']);

		add_action('wp_ajax_mwLoadSelectPageOptions', [$this, 'getSelectPageOptions_ajax']);

		$this->currentUser = mwUser::getCurrent();
	}

	public function getSelectPageOptions_ajax(): void
	{
		echo '<div class="cms_nodisp mw_select_page_options">';
		echo mwAdminComponents::selectPageOptions();
		echo '</div>';
		die();
	}

	function init()
	{
		if (is_mw_setting()) {
			if (current_user_can('edit_pages')) {
				if (!isset($_GET['setting'])) {
					wp_redirect(get_mw_admin_url('web_option_basic'));
					die();
				}
				$this->setCurrentPage();
				$this->setCurrentGroup();
				$this->setCurrentObject();
				add_action('wp_enqueue_scripts', [$this, 'loadScripts']);

				$this->_isSetting = true;

				if (isset($_POST['setting_id'])) {
					$this->savePostSendedSetting();
				}

				if (isset($_SERVER['HTTP_REFERER']) && !str_contains($_SERVER['HTTP_REFERER'], '/mw-admin') && str_contains($_SERVER['HTTP_REFERER'], get_home_url())) {
					$_SESSION['mw_admin_back_url'] = $_SERVER['HTTP_REFERER'];
				}
				$this->backUrl = $_SESSION['mw_admin_back_url'] ?? get_home_url();
			} else {
				global $wp;
				wp_redirect(wp_login_url(home_url(add_query_arg([$_GET], $wp->request))));
				die();
			}
		}
	}

	function loadScripts()
	{
		$script_version = filemtime(get_template_directory() . '/style.css');
		wp_enqueue_script('mw_setting_script', get_template_directory_uri() . '/library/admin/js/setting.js', [], $script_version);
	}

	function isSetting()
	{
		return $this->_isSetting;
	}

	public function getPageTitle(): string
	{
		$title = '';
		if ($this->object()) {
			$title = $this->object()->getLabel('title');
		} elseif ($this->page()) {
			$title = $this->page()->getTitle();
		}

		return ($title ? $title . ' | ' : '') . 'Mioweb admin';
	}

	public function currentUser()
	{
		return $this->currentUser;
	}

	function addGroup($group)
	{
		$this->_groups[$group['order']] = $group;
	}

	function addPage($page)
	{
		$this->_pages[$page['id']] = $page;

		if (!isset($page['hide_in_menu'])) {
			if (isset($page['parent'])) {
				$this->_pageStructure[$page['group']]['subpages'][$page['parent']][] = $page['id'];
			} else {
				$this->_pageStructure[$page['group']]['pages'][] = $page['id'];
			}
		}
	}

	function addPageSetting($pageId, $setting)
	{
		if (isset($this->_pages[$pageId])) {
			$this->_pages[$pageId]['setting'] = $setting;
		}
	}

	function addObjectSetting($setting, $objects, $pos = null)
	{
		foreach ($objects as $object) {
			if (isset($this->_objects[$object])) {
				if ($pos && count($this->_objects[$object]['setting'])) {
					$i = 1;
					$neworder = [];
					foreach ($this->_objects[$object]['setting'] as $key => $val) {
						if ($i == $pos) {
							$neworder[$i] = $setting;
							$i++;
						}
						$neworder[$i] = $val;
						$i++;
					}
					$this->_objects[$object]['setting'] = $neworder;
				} else {
					$this->_objects[$object]['setting'][] = $setting;
				}
			}
		}
	}
	function addObjectFastSetting($setting, $objects)
	{
		foreach ($objects as $object) {
			if (isset($this->_objects[$object])) {
				$this->_objects[$object]['fast_add_setting'] = $setting;
			}
		}
	}

	function addObjectSettingCategory($category, $objects)
	{
		foreach ($objects as $object) {
			if (isset($this->_objects[$object])) {
				$this->_objects[$object]['setting_categories'][] = $category;
			}
		}
	}

	function addUserSetting($setting)
	{
		$this->_userSetting[] = $setting;
	}

	function getUserSetting(): array
	{
		return $this->_userSetting;
	}

	function registerPageSettingType($name, $args)
	{
		$this->_pageSettingTypes[$name] = $args;
	}

	function registerPostType($name, $mwArgs, $wpArgs)
	{
		register_post_type($name, $wpArgs);
		$this->registerObject($name, $mwArgs);
	}

	function registerObject($name, $args)
	{
		if (!isset($this->_objects[$name])) {
			$this->_objects[$name] = $args;
		}
	}
	function registerObjectCopy($name, $copyof, $args = [])
	{
		if (!isset($this->_objects[$name]) && isset($this->_objects[$copyof])) {
			$this->_objects[$name] = array_merge($this->_objects[$copyof], $args);
		}
	}

	function registerTaxonomy($name, $postType, $mwArgs, $wpArgs)
	{
		register_taxonomy($name, $postType, $wpArgs);
		$this->registerObject($name, $mwArgs);
	}

	function getGroupPages($group)
	{
		return $this->_pageStructure[$group]['pages'] ?? null;
	}

	function getSubpages($page)
	{
		return $this->_pageStructure[$page->getGroup()]['subpages'][$page->getId()] ?? null;
	}

	function printLeftBar()
	{
		$content = '<div class="mw_setting_left_bar mw_bg_dark">';

		$content .= '<div class="mw_setting_left_bar_top">';

		$content .= '<a href="' . $this->backUrl . '" class="mw_setting_logo"></a>';

		$content .= mwAdminComponents::iconLink([
			'icon' => 'arrow-left',
			'link' => $this->backUrl,
			'text' => '<span>' . __('Zpět na web', 'cms') . '</span>',
		], 'mw_setting_left_bar_item mw_setting_back');

		ksort($this->_groups);

		foreach ($this->_groups as $g) {
			$group = new mwSettingGroup($g);

			$class = 'mw_setting_left_bar_item';
			if ($this->group() && $group->getId() == $this->group()->getId()) {
				$class .= ' active';
			}
			$content .= mwAdminComponents::iconLink([
				'icon' => $group->getIcon(),
				'text' => '<span>' . $group->getTitle() . '</span>',
				'link' => $group->getUrl(),
			], $class);
		}

		$content .= '</div>';
		$content .= '<div class="mw_setting_left_bar_bottom">';

		// support
		$content .= mwAdminComponents::iconLink([
			'icon' => 'help-circle',
			'text' => '<span>' . __('Podpora', 'cms') . '</span>',
			'link' => MW_SUPPORT_URL,
			'target' => '_blank',
		], 'mw_setting_left_bar_item');

		// wp link
		$content .= mwAdminComponents::iconLink([
			'icon' => 'wp',
			'text' => '<span>' . __('Do wordpressu', 'cms') . '</span>',
			'link' => admin_url(),
			'target' => '_blank',
		], 'mw_setting_left_bar_item mw_setting_left_bar_item_wp');

		// user
		$content .= '<div class="mw_setting_left_bar_user">';
		$content .= mwAdminComponents::link([
			'text' => $this->currentUser->getAvatar(28),
			'link' => MY_ACCOUNT_URL,
			'target' => '_blank',
		], 'mw_setting_left_bar_item mw_user_avatar');

		$content .= '<ul>';
		$content .= '<li>' . mwAdminComponents::link([
			'text' => __('Můj Mioweb', 'cms'),
			'link' => MY_ACCOUNT_URL,
			'target' => '_blank',
		]) . '</li>';
		$content .= '<li>' . mwAdminComponents::link([
			'text' => __('Můj profil', 'cms'),
			'link' => $this->currentUser->getEditUrl(),
		]) . '</li>';
		$content .= '<li>' . mwAdminComponents::link([
			'text' => __('Odhlásit se', 'cms'),
			'link' => wp_logout_url(),
		]) . '</li>';
		$content .= '</ul>';
		$content .= '</div>';

		$content .= '</div>';
		$content .= '</div>';
		echo $content;
	}

	function printSettingMenu()
	{
		if ($this->page()) {
			$pages = $this->getGroupPages($this->page()->getGroup());
			$content = '';
			if (count($pages) > 1) {
				$content .= '<div class="mw_setting_menu mw_bg_light">';
				$content .= mwAdminComponents::title([
					'text' => $this->group()->getTitle(),
				]);
				$content .= '<ul>';

				foreach ($pages as $pageId) {
					$page = $this->getPage($pageId);

					$liClass = $page->getId() == $this->page()->getId() || $this->page()->getParent() == $page->getId() ? 'class="open"' : '';
					$content .= '<li ' . $liClass . '>';

					$content .= mwAdminComponents::iconLink([
						'icon' => $page->getIcon(),
						'text' => $page->getTitle() . $page->getSettingMenuInfo(),
						'link' => $page->getUrl(),
						'target' => $page->getType() == 'link' ? '_blank' : '',
					], $page->getId() == $this->page()->getId() ? 'active' : '');

					$subpages = $this->getSubpages($page);

					if ($subpages) {
						$content .= '<ul>';
						foreach ($subpages as $subpageId) {
							$subpage = $this->getPage($subpageId);
							$content .= '<li>';
							$content .= mwAdminComponents::link([
								'text' => $subpage->getTitle(),
								'link' => $subpage->getUrl(),
							], $subpage->getId() == $this->page()->getId() ? 'active' : '');
							$content .= '</li>';
						}
						$content .= '</ul>';
					}

					$content .= '</li>';
				}
				$content .= '</ul>';
				$content .= '</div>';
			}
			echo $content;
		}
	}

	function printSettingPage()
	{
		echo '<div class="mw_setting_content_container mw_admin_setting_container">';
		echo $this->messageBox();

		echo '<div class="mw_setting_content mw_bg_light">';

		if (isset($this->_pageSettingTypes[$this->_settingPage])) {
			$settingType = $this->_pageSettingTypes[$this->_settingPage];

			if (isset($settingType['file'])) {
				if (file_exists($settingType['file'])) {
					include $settingType['file'];
				}
			} elseif (isset($settingType['static_class'])) {
				$class = $settingType['static_class'];
				$function = $settingType['function'];
				$class::$function();
			} else {
				$class = $settingType['class'];
				$function = $settingType['function'];
				$class()->$function();
			}
		} elseif (file_exists(__DIR__ . '/pages/' . $this->_settingPage . '.php')) {
			include __DIR__ . '/pages/' . $this->_settingPage . '.php';
		}

		echo '</div>';
		echo '</div>';
	}

	function getBodyClass(): string
	{
		$class = 'mw_setting_page_' . $this->_settingPage;
		if ($this->page()) {
			$class .= ' mw_setting_type_' . $this->page()->getId();
			if (!$this->page()->alertOnLeave()) {
				$class .= ' mw_setting_dont_alert_on_leave';
			}
		}

		return $class;
	}

	function topMenu()
	{
		$content = '<ul>';
		ksort($this->_groups);
		foreach ($this->_groups as $g) {
			$group = new mwSettingGroup($g);
			$content .= '<li>';
			$content .= mwAdminComponents::link([
				'text' => $group->getTitle(),
				'link' => $group->getUrl(),
			]);
			$content .= '</li>';
		}
		$content .= '</ul>';
		echo $content;
	}

	function messageBox(): string
	{
		$content = '<div class="mw_setting_message_box"></div>';

		return $content;
	}

	public static function printSettingActions($actions, $itemId, $object): string
	{
		if (count($actions)) {
			$content = '<div class="mw_table_actions">';
			/*
			foreach($actions as $action)
			{
				if($action == 'edit')
				{
					$content .= mwAdminComponents::iconLink([
						'icon' => 'edit-2',
						'title' => __('Upravit','cms'),
						'link' => $object->getEditUrl($itemId),
					],'mw_table_list_item_edit');
				}
				elseif($action == 'duplicate'){
					$content .= mwAdminComponents::iconLink([
						'icon' => 'copy',
						'title' => __('Duplikovat','cms'),
						'link' => $object->getDuplicateUrl($itemId),
					],'mw_table_list_item_duplicate');
				}
				elseif($action == 'delete')
				{
					$content .= mwAdminComponents::iconLink([
						'icon' => 'trash-2',
						'title' => __('Smazat','cms'),
						'attrs' =>  'data-id="'.$itemId.'" data-objectid="'.$object->getId().'"',
					],'mw_table_list_item_delete');
				}
			}
			*/
			$items = [];
			foreach ($actions as $action) {
				if ($action == 'edit') {
					$items[] = [
						'text' => __('Upravit', 'cms'),
						'icon' => '',
						'class' => 'mw_table_list_item_edit',
						'link' => $object->getEditUrl($itemId),
					];
				} elseif ($action == 'wp_edit') {
					$items[] = [
						'text' => __('Upravit', 'cms'),
						'icon' => '',
						'class' => 'mw_table_list_item_edit',
						'link' => $object->getEditWPUrl($itemId),
						'target' => '_blank',
					];
				} elseif ($action == 'duplicate') {
					$items[] = [
						'text' => __('Duplikovat', 'cms'),
						'icon' => 'copy',
						'class' => 'mw_table_list_item_duplicate',
						'link' => $object->getDuplicateUrl($itemId),
					];
				} elseif ($action == 'delete') {
					$items[] = [
						'text' => __('Smazat', 'cms'),
						'icon' => 'trash-2',
						'class' => $object->isHierarchical() ? 'mw_hierarchical_list_item_delete' : 'mw_table_list_item_delete',
						'attrs' => 'data-id="' . $itemId . '" data-objectid="' . $object->getId() . '"',
						'link' => '#',
					];
				} elseif ($action == 'show_page') {
					$items[] = [
						'text' => __('Zobrazit stránku', 'cms'),
						'icon' => 'trash-2',
						'target' => '_blank',
						'link' => $object->getItemUrl($itemId),
					];
				} elseif ($action == 'restore') {
					$items[] = [
						'text' => __('Obnovit', 'cms'),
						'class' => 'mw_table_list_item_restore',
						'attrs' => 'data-id="' . $itemId . '" data-objectid="' . $object->getId() . '"',
					];
				} elseif ($action === 'renew') {
					$items[] = [
						'text' => __('Obnovit', 'cms'),
						'class' => 'mw_table_list_item_renew',
						'attrs' => 'data-id="' . $itemId . '" data-objectid="' . $object->getId() . '"',
					];
				} elseif ($action === 'createArchive') {
					$items[] = [
						'text' => __('Archivovat', 'cms'),
						'class' => 'mw_table_list_item_create_archive',
						'attrs' => 'data-id="' . $itemId . '" data-objectid="' . $object->getId() . '"',
					];
				}
			}

			$content .= mwAdminComponents::dropIcon([
				'items' => $items,
			]);
			$content .= '</div>';

			return $content;
		}

		return '';
	}

	public static function saveBar($button_text = '', $class = 'mw_setting_save_but'): string
	{
		$content = mwAdminComponents::saveBar([
			'save_button_text' => $button_text ?: __('Uložit', 'cms'),
			'save_button_class' => $class,
			'hide_storno' => 1,
		]);

		return $content;
	}

	function setCurrentGroup()
	{
		if ($this->page()) {
			foreach ($this->_groups as $group) {
				if ($group['id'] == $this->page()->getGroup()) {
					$this->_currentGroup = new mwSettingGroup($group);
				}
			}
		}
	}

	function setCurrentPage()
	{
		$this->_currentPage = $this->getPage($_GET['setting']);

		if (!$this->page()) {
			$this->_settingPage = '404';
		} elseif (isset($_GET['add'])) {
			$this->_settingPage = 'add';
		} elseif (isset($_GET['edit'])) {
			$this->_settingPage = 'edit';
		} elseif (isset($_GET['trash'])) {
			$this->_settingPage = 'trash';
		} elseif (isset($_GET['archives'])) {
			$this->_settingPage = 'archives';
		} else {
			$this->_settingPage = $this->_currentPage->getType();
		}
	}

	function setCurrentObject()
	{
		if (isset($this->_objects[$_GET['setting']])) {
			$this->_currentObject = $this->getObject($_GET['setting']);
			// clear filter
			if (isset($_SESSION['mwObjectListFilter'])) {
				$filter = $_SESSION['mwObjectListFilter'];
				if (isset($filter['object']) && $filter['object'] != $_GET['setting']) {
					$_SESSION['mwObjectListFilter'] = [];
				}
			}
		} else {
			$this->_currentObject = null;
			$_SESSION['mwObjectListFilter'] = [];
		}
	}

	function getObject($id): ?mwSettingObject
	{
		return isset($this->_objects[$id]) ? new mwSettingObject($id, $this->_objects[$id]) : null;
	}

	function getPage($id): ?mwSettingPage
	{
		return isset($this->_pages[$id]) ? new mwSettingPage($this->_pages[$id]) : null;
	}
	function getPages(): array
	{
		return $this->_pages;
	}

	function group()
	{
		return $this->_currentGroup;
	}

	function page()
	{
		return $this->_currentPage;
	}

	function object()
	{
		return $this->_currentObject;
	}

	public static function verifyNonce($nonce = '')
	{
		return isset($_POST[$nonce]) && wp_verify_nonce($_POST[$nonce], $nonce) ? true : false;
	}

	public static function getPeriod($period, $cFrom = '', $cTo = '')
	{
		if ($period == 'custom') {
			$from = $cFrom ? new \DateTimeImmutable($cFrom) : null;
			$to = $cTo ? new \DateTimeImmutable($cTo) : null;
			if ($to) {
				$to = $to->setTime(23, 59, 59);
			}
		} else {
			$from = null;
			$to = null;
			$now = new \DateTimeImmutable(date('Y-m-d', current_time('timestamp')));

			if ($period == 'today') {
				$from = $now;
				$to = $now->setTime(23, 59, 59);
			} elseif ($period == 'yesterday') {
				$from = $now->modify('-1 day');
				$to = $from->setTime(23, 59, 59);
			} elseif ($period == 'last-7-days') {
				$from = $now->modify('-7 days');
				$to = $now->setTime(23, 59, 59);
			} elseif ($period == 'last-30-days') {
				$from = $now->modify('-30 days');
				$to = $now->setTime(23, 59, 59);
			} elseif ($period == 'this-month') {
				$from = $now->modify('first day of this month');
				$to = $now->modify('last day of this month')->setTime(23, 59, 59);
			} elseif ($period == 'last-month') {
				$from = $now->modify('first day of previous month');
				$to = $now->modify('last day of previous month')->setTime(23, 59, 59);
			} elseif ($period == 'this-year') {
				$from = $now->modify('first day of january this year');
				$to = $now->modify('last day of December this year')->setTime(23, 59, 59);
			} elseif ($period == 'last-year') {
				$from = $now->modify('first day of january previous year');
				$to = $now->modify('last day of December previous year')->setTime(23, 59, 59);
			}
		}

		return [
			'from' => $from,
			'to' => $to,
		];
	}

	public function printObjectMenu($objectId, $module, $pageType, $itemId, $isWindowEditor = false)
	{
		$object = $this->getObject($objectId);

		if (is_category() || is_tax()) {
			$itemId = get_queried_object_id();
		}

		if ($object && $itemId) {
			$menu = $object->service()->getMenu($module, $pageType);
			$actions = $object->service()->getActions($itemId);

			echo '<div class="mw_editor_panel_setting_menu">';

			if (count($actions) && !is_home()) {
				echo '<div class="mw_editor_panel_setting_actions_container">';
				echo '<span>' . __('Akce', 'cms') . ':</span>';
				echo '<ul class="mw_editor_panel_setting_actions">';
				foreach ($actions as $actionId => $action) {
					echo '<li>';
					echo mwAdminComponents::iconLink([
						'icon' => $action['icon'],
						'link' => $action['link'] ?? '#',
						'target' => $action['target'] ?? '',
						'attrs' => 'data-id="' . $itemId . '" data-objectid="' . $objectId . '" data-title="' . $action['title'] . '" ' . ($action['attrs'] ?? ''),
					], 'mw_editor_object_action mw_tooltip ' . ($action['class'] ?? ''));
					echo '</li>';
				}
				echo '</ul>';

				echo '</div>';
			}

			if (count($menu)) {
				$i = 1;
				foreach ($menu as $menu_item) {
					if (count($menu_item['submenu'])) {
						if (isset($menu_item['title'])) {
							echo '<h3>' . $menu_item['title'] . '</h3>';
						}
						echo '<ul class="mw_editor_panel_menu mw_editor_panel_menu_' . $i . '">';

						foreach ($menu_item['submenu'] as $submenu_item) {
							if (isset($submenu_item['class'])) {
								$link_class = $submenu_item['class'];
							} else {
								$link_class = isset($submenu_item['inpanel']) && $submenu_item['inpanel'] ? 've_open_setting_inpanel' : 've_open_setting';
							}

							$attrs = '';
							if (isset($submenu_item['attrs'])) {
								foreach ($submenu_item['attrs'] as $attr_key => $attr_val) {
									$attrs .= ' ' . $attr_key . '="' . $attr_val . '"';
								}
							}
							if (!isset($submenu_item['attrs']['postid'])) {
								$attrs .= ' data-itemid="' . $itemId . '"';
							}

							echo '<li>';
							echo mwAdminComponents::link([
								'title' => $submenu_item['name'],
								'attrs' => $attrs,
								'text' => $submenu_item['name'] . mw_icon('icon-chevron-right'),
							], $link_class);
							echo '</li>';
						}

						echo '</ul>';

						$i++;
					}
				}
			}

			echo '</div>';

			// @TODO Do differently - remove this part
			if ($isWindowEditor) {
				$weditor_title = get_the_title($itemId);
				echo '<input id="weditor_post_title" value="' . $weditor_title . '" type="hidden" />';
				echo '<div id="mw_change_weditor_title_container" class="cms_nodisp">'
				. '<div class="mw_admin_setting_container">'
				. '<input class="mw_input mw_input_weditor_name required" value="" type="text" placeholder="' . __('Zadejte název', 'cms') . '" />'
				. '</div>'
				. '</div>';
			}
		}
	}

	public function printObjectSet($objectId, $setId, $itemId)
	{
		$object = $this->getObject($objectId);
		$set = $object->getSetting($setId);

		echo '<div class="mw_setting_object_detail_content">';
		echo '<div class="mw_setting_object_detail_form">';
		$object->service()->printSet($itemId, $set);
		echo '</div>';

		if (isset($set['show_sidebar'])) {
			$item = $object->service()->getItem($itemId);
			echo $object->service()->printFormSidebar($item, false, true);
		}
		echo '</div>';

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="set_id" value="' . $setId . '"/>';
		echo '<input type="hidden" name="object_id" value="' . $objectId . '"/>';
		echo '<input type="hidden" name="item_id" value="' . $itemId . '"/>';
	}

	public function printOptionSetting(string $settingPageId)
	{
		// member_registered
		$page = $this->getPage($settingPageId);
		if ($page) {
			$page->printForm();
		} elseif (substr($settingPageId, 0, 4) == 'mwms') {
			$option = MWDB()->getOption($settingPageId);
			$slug = explode('_', $settingPageId);
			$setId = 'member_' . $slug[1];

			$option = mwBackCompatibility::option_set($option, $setId);

			$setting = $this->getObject('member_sections')->getSetting($setId);

			write_meta($setting['fields'], $option, 'setting', 'setting');

			wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');

			echo '<input type="hidden" name="member_section_setting" value="' . $settingPageId . '"/>';
		}
	}

	function savePostSendedSetting()
	{
		if ($this->verifyNonce('mw_save_setting_nonce') && isset($_POST['setting_id']) && $_POST['setting_id'] && $_POST['setting_id'] == $this->page()->getId()) {
			$this->saveSetting($_POST['setting_id'], $_POST['setting'] ?? []);
		}
		wp_redirect($this->page()->getUrl());
		die();
	}

	function saveSetting_ajax()
	{
		if ($this->verifyNonce('mw_save_setting_nonce') && isset($_POST['setting_id']) && $_POST['setting_id']) {
			$this->saveSetting($_POST['setting_id'], $_POST['setting'] ?? []);
		} else {
			mwMessages()->error(__('Nastavení se nepodařilo uložit.', 'cms'));
		}

		wp_send_json([
			'success' => mwMessages()->success,
			'errors' => mwMessages()->errors,
			'html' => mwMessages()->writeHtml(),
		]);

		die();
	}

	function saveSetting($id, $tosave)
	{
		$settingPage = $this->getPage($id);

		if ($settingPage) {
			if ($settingPage->getId() == 've_header') {
				MW()->getLicense()->sendNotify();
			}

			$settingPage->saveSetting($tosave);
		}
	}

	function openFastAdd_ajax()
	{
		$objectId = $_POST['object_id'];
		$object = $this->getObject($objectId);
		$object->service()->printFastAddForm();
		die();
	}

	function openFastCopy_ajax()
	{
		$objectId = $_POST['object_id'];
		$object = $this->getObject($objectId);
		$object->service()->printFastCopyForm($_POST['item_id']);
		die();
	}


	function saveObject_ajax()
	{
		if ($this->verifyNonce('mw_save_setting_nonce') && isset($_POST['object_id']) && $_POST['object_id'] && isset($_POST['item_id'])) {
			$this->saveObject($_POST['object_id'], $_POST['item_id'], $_POST);
		} else {
			mwMessages()->error(__('Došlo k chybě a uložení se nezdařilo. Prosím zkuste to znovu.', 'cms'));
		}

		wp_send_json([
			'success' => mwMessages()->success,
			'errors' => mwMessages()->errors,
			'html' => mwMessages()->writeHtml(),
		]);

		die();
	}

	function saveObject($objectId, $itemId, $tosave)
	{
		$object = $this->getObject($objectId);

		if ($object) {
			$status = $object->checkData($tosave, $itemId);

			if ($status) {
				$object->service()->save($itemId, $tosave);
			}
		}
	}

	function saveObjectSet_ajax()
	{
		$redirectUrl = '';
		if ($this->verifyNonce('mw_save_setting_nonce')) {
			$redirectUrl = $this->saveObjectSet($_POST['object_id'], $_POST['set_id'], $_POST['item_id'], $_POST);
		} else {
			mwMessages()->error(__('Došlo k chybě a uložení se nezdařilo. Prosím zkuste to znovu.', 'cms'));
		}

		wp_send_json([
			'redirect' => $redirectUrl, // if page change url redirect to new url (on front edit)
			'success' => mwMessages()->success,
			'errors' => mwMessages()->errors,
			'html' => mwMessages()->writeHtml(),
		]);

		die();
	}

	function saveObjectSet($object_id, $set_id, $item_id, $tosave)
	{
		$return = '';
		$object = $this->getObject($object_id);

		if ($object) {
			$set = $object->getSetting($set_id);
			$status = $object->checkDataSet($tosave, $set['fields'], $set_id, $item_id);

			if ($status) {
				$redirect = false;
				if (isset($tosave['post_name']) || isset($tosave['post_parent'])) {
					$item = $object->service()->getItem($item_id);

					if (isset($tosave['post_name']) && $tosave['post_name'] != $item->getSlug()) {
						$redirect = true;
					}

					if (isset($tosave['post_parent']) && $tosave['post_parent'] != $item->getParentId()) {
						$redirect = true;
					}
				}

				$object->service()->save($item_id, $tosave);

				if ($redirect) {
					return $object->service()->getItemUrl($item_id);
				}
			}
		}

		return $return;
	}

	function addNewObject_ajax()
	{
		$return = [];

		if ($this->verifyNonce('mw_save_setting_nonce') && isset($_POST['object_id']) && $_POST['object_id']) {
			$objectId = $_POST['object_id'];
			$object = $this->getObject($objectId);
			$fast = isset($_POST['fast_add']) ? true : false;

			if ($object) {
				$tosave = $_POST;

				// import
				if (isset($_POST['import_template_upload']) && $_POST['import_template_upload']) {
					$newItemId = MwWebInstall()->importItemZip($objectId);

					$item = $object->service()->getItem($newItemId);
				} else {
					$item = $this->addNewObject($object, $tosave, $fast);
				}

				if ($item) {
					if (isset($_POST['redirect_to_front']) && $_POST['redirect_to_front']) {
						$url = $object->getItemUrl($item->getId());
					} elseif (isset($_POST['redirect_to_list']) && $_POST['redirect_to_list']) {
						$url = $object->getUrl();
					} else {
						$url = $object->getEditUrl($item->getId()) . '&added=1';
					}

					$return = [
						'redirect' => $url,
						'url' => $object->getItemUrl($item->getId()),
						'title' => $item->getName(),
						'id' => $item->getId(),
					];
				}
			}
		} else {
			mwMessages()->error(__('Došlo k chybě a uložení se nezdařilo. Prosím zkuste to znovu.', 'cms'));
		}

		$return['success'] = mwMessages()->success;
		$return['errors'] = mwMessages()->errors;
		$return['html'] = mwMessages()->writeHtml();

		wp_send_json($return);

		die();
	}

	function addNewObject($object, $tosave, $fast = false)
	{
		if ($fast) {
			$set = $object->getFastSetting();
			$status = $object->checkDataSet($tosave, $set['fields'], $set['id']);
		} else {
			$status = $object->checkData($tosave, 0, false, true);
		}

		if ($status) {
			$newItemId = $object->service()->add($tosave, $fast);

			return $newItemId ? $object->service()->getItem($newItemId) : null;
		}

		return null;
	}

	function addFastCopyObject_ajax()
	{
		$content = '';
		$return = [];

		if ($this->verifyNonce('mw_save_setting_nonce') && isset($_POST['object_id']) && $_POST['object_id']) {
			$objectId = $_POST['object_id'];
			$object = $this->getObject($objectId);

			if ($object) {
				$original = $object->service()->getItem($_POST['item_id']);

				if ($original) {
					$item = $original->createCopy($_POST);

					if ($item) {
						$return = [
							'admin_url' => $object->getEditUrl($item->getId()) . '&added=1',
							'url' => $item->getUrl(),
							'title' => $item->getName(),
							'id' => $item->getId(),
						];
					}
				}
			}
		} else {
			mwMessages()->error(__('Došlo k chybě a uložení se nezdařilo. Prosím zkuste to znovu.', 'cms'));
		}

		$return['success'] = mwMessages()->success;
		$return['errors'] = mwMessages()->errors;
		$return['html'] = mwMessages()->writeHtml();

		wp_send_json($return);

		die();
	}

	function addFastNewObject_ajax()
	{
		$content = '';
		$return = [];

		if ($this->verifyNonce('mw_save_setting_nonce') && isset($_POST['object_id']) && $_POST['object_id']) {
			$objectId = $_POST['object_id'];
			$object = $this->getObject($objectId);

			if ($object) {
				$tosave = $_POST;

				$item = $this->addNewObject($object, $tosave, true);

				if ($item) {
					$returnType = $_POST['return_type'] ?? '';

					if ($returnType && $returnType != 'redirect') {
						$content = $object->service()->fastAddReturn($item, $returnType, $_POST['return_name'] ?? '');
					}

					$return = [
						'admin_url' => $object->getEditUrl($item->getId()) . '&added=1',
						'edit_url' => $object->getEditUrl($item->getId()),
						'url' => $item->getUrl(),
						'title' => $item->getName(),
						'id' => $item->getId(),
						'content' => $content,
					];
				}
			}
		} else {
			mwMessages()->error(__('Došlo k chybě a uložení se nezdařilo. Prosím zkuste to znovu.', 'cms'));
		}

		$return['success'] = mwMessages()->success;
		$return['errors'] = mwMessages()->errors;
		$return['html'] = mwMessages()->writeHtml();

		wp_send_json($return);

		die();
	}

	function deleteObjectItem_ajax()
	{
		if (isset($_POST['object_id']) && $_POST['object_id']) {
			$object = $this->getObject($_POST['object_id']);
			$object->service()->delete($_POST['item_id']);

			wp_send_json([
				'admin_url' => $object->getUrl(),
				'home_url' => get_home_url(),
			]);
		}

		die();
	}

	public function archiveItem_ajax(): bool
	{
		if (isset($_POST['object_id']) && $_POST['object_id']) {
			$object = $this->getObject($_POST['object_id']);

			return (bool) $object->service()->createArchive($_POST['item_id']);
		}

		return false;
	}

	public function deArchiveItem_ajax(): bool
	{
		if (isset($_POST['object_id']) && $_POST['object_id']) {
			$object = $this->getObject($_POST['object_id']);

			return (bool) $object->service()->deArchive($_POST['item_id']);
		}

		return false;
	}

	function restoreObjectItem_ajax()
	{
		if (isset($_POST['object_id']) && $_POST['object_id']) {
			$object = $this->getObject($_POST['object_id']);
			$object->service()->restore($_POST['item_id']);

			return true;
		}

		return false;

		die();
	}

	function emptyTrash_ajax()
	{
		if (isset($_POST['object_id']) && $_POST['object_id']) {
			$object = $this->getObject($_POST['object_id']);
			$object->service()->emptyTrash();

			return true;
		}

		return false;

		die();
	}

	function openObjectExportForm_ajax()
	{
		if (isset($_POST['object_id']) && $_POST['object_id']) {
			$object = $this->getObject($_POST['object_id']);
			echo $object->service()->exportForm();
		}
		die();
	}

	public function createExportObjectItems_ajax()
	{
		if (isset($_POST['object_id'], $_POST['format']) && $_POST['object_id'] && $_POST['format']) {
			$object = $this->getObject($_POST['object_id']);
			/** @var string $format */
			$format = $_POST['format'];

			if ($object !== null) {
				$result = [];

				MW()->enable_strict_error_handler();

				try {
					$export = $object->service()->createExport($format, $_POST);

					$cacheDir = get_temp_dir() . 'exports';
					@mkdir($cacheDir, 0777, true);

					$fileName = Random::generate() . '.' . $export->getFileExtension();
					$filePath = $cacheDir . '/' . $fileName;
					file_put_contents($filePath, $export->getContent());

					$result['fileName'] = $fileName;
					$result['attachmentFileName'] = $export->getAttachmentFileName();
				} catch (MwsUserException $e) {
					mwMessages()->error($e->getMessage());
				} catch (\Throwable $e) {
					mwMessages()->error(__('Při vytváření exportu došlo k chybě.'));
					mwlog(MWLS_GENERAL, $e->getMessage(), MWLL_ERROR);
				}

				wp_send_json([
					'success' => mwMessages()->success,
					'errors' => mwMessages()->errors,
					'html' => mwMessages()->writeHtml(),
				] + $result);
			}
		}

		die();
	}

	public function downloadExportObjectItems(): void
	{
		if (isset($_GET['fileName']) && $_GET['fileName']) {
			$fileName = $_GET['fileName'];
			$filePath = get_temp_dir() . 'exports/' . $fileName;
			if (file_exists($filePath)) {
				$contentType = mime_content_type($filePath) ?: 'text/plain';
				$attachmentFileName = $_GET['attachmentFileName'] ?? $fileName;

				header('Content-Type: ' . $contentType);
				header('Content-disposition: attachment; filename=' . $attachmentFileName);
				header('Content-Length: ' . filesize($filePath));

				if (ob_get_level()) {
					ob_end_clean();
				}

				readfile($filePath);
				unlink($filePath);
			} else {
				echo __('Při stahování exportu došlo k chybě.', 'cms');
			}
		} else {
			wp_redirect(home_url());
		}

		die();
	}

	function setObjectVisibility_ajax()
	{
		if (isset($_POST['object_id']) && $_POST['object_id']) {
			$object = $this->getObject($_POST['object_id']);
			$object->service()->setVisibility($_POST['item_id'], $_POST['visibility']);
		}
		die();
	}

	function setDefaultItem_ajax()
	{
		if (isset($_POST['object_id']) && $_POST['object_id']) {
			$object = $this->getObject($_POST['object_id']);

			return $object->service()->setDefaultItem($_POST['item_id']);
		}

		return false;
	}

	function setCommentStatus_ajax()
	{
		mwComment::setStatus($_POST['item_id'], $_POST['status']);
		die();
	}

	function setListReload_ajax()
	{
		if (isset($_POST['object_id']) && $_POST['object_id']) {
			$object = $this->getObject($_POST['object_id']);
			$object->saveListFilter($_POST['filter'] ?? []);

			if (isset($_POST['set_default'])) {
				$object->service()->setDefaultItem($_POST['set_default']);
			}

			if (isset($_POST['bulk_action'])) {
				$this->bulkActions();
			}

			$page = $_POST['page'] ?? 1;
			$trash = isset($_POST['trash']);
			$listArgs = $object->service()->getListArgs($page, MW_DEFAULT_PER_PAGE, $trash);

			if ($page > 1 && !count($listArgs['rows'])) {
				$page--;
				$listArgs = $object->service()->getListArgs($page, MW_DEFAULT_PER_PAGE, $trash);
			}
			echo $object->service()->printList($listArgs, $page);
		}
		die();
	}

	function checkSlug_ajax()
	{
		$object = $this->getObject($_POST['object_id']);
		$newslug = $object->service()->checkSlug($_POST['slug'], $_POST['item_id']);

		wp_send_json([
			'slug' => $newslug,
			'success' => mwMessages()->success,
			'errors' => mwMessages()->errors,
			'html' => mwMessages()->writeHtml(),
		]);

		die();
	}

	function generatePassword_ajax()
	{
		echo $this->generatePassword();
		die();
	}
	function generatePassword($length = 20)
	{
		return wp_generate_password($length);
	}

	function updateOrder_ajax()
	{
		if (isset($_POST['object_id']) && isset($_POST['item_id']) && isset($_POST['parent_id'])) {
			$object = $this->getObject($_POST['object_id']);
			$object->service()->updateOrder($_POST['item_id'], $_POST['order'], $_POST['parent_id']);
		}
		die();
	}

	function bulkActions()
	{
		if (isset($_POST['bulk'])) {
			$objectId = $_POST['object_id'];
			$object = $this->getObject($objectId);
			$object->service()->bulkActions($_POST['bulk'], $_POST['bulk_action']);
		}
	}

	public static function saveUsed($array)
	{
		foreach ($array as $key => $val) {
			if (is_array($val) && $key != 'button') {
				mwSetting::saveUsed($val);
			} elseif (strpos($key, 'color') !== false) {
				// color
				if (!isset($_SESSION['ve_used_colors']) || !$_SESSION['ve_used_colors']) {
					$_SESSION['ve_used_colors'] = [];
				}

				if ($val) {
					$_SESSION['ve_used_colors'] = array_diff($_SESSION['ve_used_colors'], [$val]);
					array_unshift($_SESSION['ve_used_colors'], $val);
					$_SESSION['ve_used_colors'] = array_slice($_SESSION['ve_used_colors'], 0, 28);
				}
			} elseif ($key === 'font-family') {
				// fonts
				if (!isset($_SESSION['ve_used_fonts']) || !is_array($_SESSION['ve_used_fonts'])) {
					$_SESSION['ve_used_fonts'] = [];
				}
				if ($val) {
					$_SESSION['ve_used_fonts'] = array_diff($_SESSION['ve_used_fonts'], [$val]);
					array_unshift($_SESSION['ve_used_fonts'], $val);
					$_SESSION['ve_used_fonts'] = array_slice($_SESSION['ve_used_fonts'], 0, 6);
				}
			}
		}
		if (isset($_SESSION['ve_used_colors'])) {
			update_option('ve_used_colors', $_SESSION['ve_used_colors']);
		}
		if (isset($_SESSION['ve_used_fonts'])) {
			update_option('ve_used_fonts', $_SESSION['ve_used_fonts']);
		}
	}

	public static function getDefaultSetting($defSet, $setting = []): array
	{
		foreach ($defSet as $set) {
			if (isset($set['tabs'])) {
				foreach ($set['tabs'] as $id => $tab) {
					if (isset($set['content'])) {
						$setting[$set['id']] = $set['content'];
					}
					$setting = self::getDefaultSetting($tab['setting'], $setting);
				}
			} elseif (isset($set['type']) && ($set['type'] == 'toggle_group' || $set['type'] == 'group' || $set['type'] == 'box')) {
				if (isset($set['content'])) {
					$setting[$set['id']] = $set['content'];
				}
				$setting = self::getDefaultSetting($set['setting'], $setting);
			} elseif (isset($set['content']) && isset($set['id']) && ($set['type'] != 'info' || $set['type'] != 'static')) {
				$setting[$set['id']] = $set['content'];
			}
		}

		return $setting;
	}

	public static function message404($msg, $link = ''): string
	{
		return '<div class="mw_setting_box mw_setting_box_content mw_setting_404_message"><span>' . $msg . '</span>' . $link . '</div>';
	}

	// helplink
	public static function getHelpLink($helpId)
	{
		$help_link = mwHelp::getHelpLink($helpId);
		if ($help_link) {
			return '<a href="' . $help_link . '" target="_blank" title="' . __('Nápověda', 'cms') . '" class="mw_tooltip_container mw_tooltip_type_icon">?</a>';
		}

		return '';
	}

	/** @return mwSetting Returns singleton instance of MioShop. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/* WP hooks
	*************************************************************************** */

	function updateTermsOrderby($pieces, $taxonomies, $args)
	{
		if ($args['orderby'] == 'mw_order') {
			global $wpdb;
			$pieces['orderby'] = 'ORDER BY ' . $wpdb->prefix . 'termmeta.meta_value+0, t.name';
		}

		return $pieces;
	}

	function reorderOnDeleteTerm($termId, $taxonomy)
	{
		$object = $this->getObject($taxonomy);
		if ($object && $object->isHierarchical()) {
			$termToDelete = $object->service()->getItem($termId);
			$termChilds = mwTerm::getAll($taxonomy, [
				'parent' => $termToDelete->getId(),
			]);

			if (count($termChilds)) {
				$parentChilds = mwTerm::getAll($taxonomy, [
					'parent' => $termToDelete->getParentId(),
				]);

				if (count($parentChilds) > 1) {
					$i = 0;
					foreach ($parentChilds as $pTerm) {
						if ($pTerm->getId() == $termToDelete->getId()) {
							foreach ($termChilds as $tTerm) {
								MWDB()->updateTermMeta($tTerm->getId(), 'mw_order', $i);
								$i++;
							}
						} else {
							MWDB()->updateTermMeta($pTerm->getId(), 'mw_order', $i);
							$i++;
						}
					}
				}
			}
		}
	}

	/* WP setting
	*************************************************************************** */

	function add_rewrite_rule()
	{
		add_rewrite_rule('^mw-admin?', 'index.php?mw-admin=1', 'top');
	}
	function set_query_var($vars)
	{
		array_push($vars, 'mw-admin');

		return $vars;
	}
	function include_template($template)
	{
		if (is_mw_setting()) {
			$new_template = TEMPLATEPATH . '/setting.php';
			if (file_exists($new_template)) {
				$template = $new_template;
			}
		}

		return $template;
	}

	function extraContactInfo($contactmethods)
	{
		unset($contactmethods['aim']);
		unset($contactmethods['yim']);
		unset($contactmethods['jabber']);
		$contactmethods['facebook'] = 'Facebook';
		$contactmethods['twitter'] = 'Twitter';
		$contactmethods['linkedin'] = 'LinkedIn';
		$contactmethods['youtube'] = 'YouTube';
		$contactmethods['instagram'] = 'Instagram';

		return $contactmethods;
	}

	function saveMetasInWpAdmin($post_id)
	{
		if ($this->verifyNonce('admin_save_nonce')) {
			if (MW()->is_save_disabled()) {
				mwlog('cms', "saving sets SKIPPED for [$post_id], saving is disabled", MWLL_DEBUG, 'save');

				return;
			}

			MW()->is_saving = true;
			try {
// verify nonce
				// check autosave
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
					return $post_id;
				}
				// check permissions
				if (isset($_POST['post_type']) && $_POST['post_type'] == 'page') {
					if (!current_user_can('edit_page', $post_id)) {
						return $post_id;
					}
				} elseif (!current_user_can('edit_post', $post_id)) {
					return $post_id;
				}

				$object = $this->getObject($_POST['post_type']);

				if ($object) {
					foreach ($object->getSetting() as $set) {
						if (isset($_POST[$set['id']])) {
							$object->service()->saveObjectSetting($post_id, $set, $_POST[$set['id']]);
						}
					}
				}

				MW()->is_saving = false;
			} catch (Exception $e) {
				MW()->is_saving = false;

				throw $e;
			}
		}
	}

	function addWpMetaBoxes($post_type, $post)
	{
		if ($post_type == 'page') {
			add_meta_box('page_set', __('Nastavení', 'cms'), [$this, 'showWpMetaBox'], 'page', 'normal', 'high', ['set_cat' => '', 'object_id' => 'page']);
		} elseif ($post_type == 'post') {
			add_meta_box('page_set', __('Nastavení', 'cms'), [$this, 'showWpMetaBox'], 'post', 'normal', 'high', ['set_cat' => '', 'object_id' => 'post']);
		}
		/*
		$object = $this->getObject($post_type);
		if($object)
		{
			foreach ($object->getSettingCategories() as $cat) {
				add_meta_box($cat['id'], $cat['title'], [$this, 'showWpMetaBox'], $post_type, $cat['context'], $cat['priority'], ['set_cat' => $cat['id'], 'object_id' => $post_type]);
			}
		}*/
	}

	function showWpMetaBox($post, $metabox)
	{
		$object = $this->getObject($metabox['args']['object_id']);
		$catId = $metabox['args']['set_cat'];
		$sets = $object->getSettingForCategory($catId);

		echo '<div class="mw_admin_setting_container">';
		echo '<input type="hidden" name="admin_save_nonce" value="', wp_create_nonce('admin_save_nonce'), '" />'; //becose of this save hook save meta boxes only from admin

		$group = 'mw_page_setting_tab_' . $catId;

		$single = (count($sets) < 2);

		if (!$single) {
			$tabs = [];
			foreach ($sets as $set) {
				if (!isset($set['hide_in_wp'])) {
					$tabs[] = [
						'id' => $set['id'],
						'name' => $set['title'],
					];
				}
			}

			echo mwAdminComponents::tabs([
				'tabs' => $tabs,
				'group' => $group,
			], '', 'mw_setting_tabs');
		}

		$i = 1;
		foreach ($sets as $set) {
			if (!isset($set['hide_in_wp'])) {
				if (!$single) {
					echo '<div id="' . $group . '_' . $set['id'] . '" class="mw_tab mw_setting_padding_content ' . $group . '_container ' . ($i == 1 ? 'active' : '') . '">';
				}

				$object->service()->printSet($post->ID, $set);

				if (!$single) {
					echo '</div>';
				}
				$i++;
			}
		}

		echo '</div>';
	}

	function addEditPageButtonToWP()
	{
		global $post;
		if (get_post_type($post) == 'page') {
			echo '<div class="postbox ve_admin_editbut_container">';
			echo mwAdminComponents::button([
				'link' => get_permalink($post->id),
				'button_text' => __('Spustit editor vzhledu', 'cms'),
			]);
			echo '</div>';
			echo '<style>#postdivrich {display: none;}</style>';
		}
	}

	function mwDisableGutenbergForPages($is_enabled, $post_type)
	{
		if ($post_type === 'page') {
			return false; // change book to your post type
		}

		return $is_enabled;
	}

}
