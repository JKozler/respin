<?php

define('FUNNEL_USER_COOKIE', 'mwf_funnel_access');
define('FUNNEL_POST_META', 'mwf_funnel_id');

function MWF()
{
	return mwFunnels::instance();
}

class mwFunnels
{

	/** @var mwFunnels Single instance holder. */
	protected static $_instance = null;

	public $edit_mode;

	public $funnels = [];

	public $script_version;

	public $js_texts;

	public $is_funnel_page = false;

	public $current_funnel;

	public $currentPageData;

	public $userCookie = null;

	public $userCookieName;

	private $pageTypes = [];

	private $items_setting = [];

	public $abTest;

	public $templates;

	private $builder_mode;

	function __construct()
	{
		$this->edit_mode = current_user_can('edit_pages') ? true : false;
		$this->builder_mode = $this->edit_mode && !isset($_GET['mw_preview']);

		$this->script_version = filemtime(get_template_directory() . '/style.css');

		add_action('wp', [$this, 'oninit']);

		if (!$this->builder_mode) {
			add_action('wp_enqueue_scripts', [$this, 'load_front_scripts']);
		} else {
			add_action('wp_enqueue_scripts', [$this, 'load_admin_scripts']);
			add_filter('mw_fast_nav_current', [$this, 'fast_nav_current']);

			//ajax
			//add_action('wp_ajax_add_new_funnel', [$this, 'add_new_funnel']);
			//add_action('wp_ajax_mw_create_new_funnel', [$this, 'ajaxCreateNewFunnel']);
			add_action('wp_ajax_mwInstallNewFunnel', [$this, 'ajaxInstallNewFunnel']);

			add_action('wp_ajax_mw_add_funnel_item', [$this, 'add_funnel_item']);
			add_action('wp_ajax_mwGenerateFunnelItemSetting', [$this, 'generateFunnelItemSetting']);
			add_action('wp_ajax_mwSaveFunnelItems', [$this, 'saveFunnelItems']);
			add_action('wp_ajax_mwReloadFunnel', [$this, 'reloadFunnel']);
			add_action('wp_ajax_mwReloadDashboardStatistics', [$this, 'ajaxDashboardStatistics']);
			add_action('wp_ajax_mwFunnelResetStatistics', [$this, 'ajaxFunnelResetStatistics']);
			add_action('wp_ajax_mwSaveFunnelSetting', [$this, 'ajaxSaveFunnelSetting']);
			add_action('wp_ajax_mwFunnelCreateABTestPage', [$this, 'ajaxCreateABTestPage']);
			add_action('wp_ajax_mwFunnelDeclareABTestWinner', [$this, 'ajaxDeclareABTestWinner']);

			add_action('mw_add_list', [$this, 'hookAddList']);

			$this->checkVersion();
			$this->addFastNav();
			$this->loadPageTypes();
			$this->loadInstallator();
		}

		add_action('cms_activation', [$this, 'activation']);
		add_action('wp_ajax_nopriv_mwSendMailConversion', [$this, 'ajaxSendMailConversion']);
		add_action('wp_ajax_mwSendMailConversion', [$this, 'ajaxSendMailConversion']);
	}

	function load_admin_scripts()
	{
		wp_enqueue_script('funnels_admin_script', FUNNELS_DIR . 'js/admin.js', ['jquery'], $this->script_version);
		wp_enqueue_style('funnels_admin_css', FUNNELS_DIR . 'css/admin.css', [], $this->script_version);
		/*
		require_once(__DIR__ . '/js/js_texts.php');
		$this->js_texts = $js_texts;

		wp_localize_script('funnels_admin_script', 'funnel_texts', $this->js_texts['admin']);*/
	}

	function load_front_scripts()
	{
		wp_enqueue_style('funnels_content_css', FUNNELS_DIR . 'css/content.css', [], $this->script_version);
	}

	// init funnel page

	function oninit()
	{
		global $post, $vePage;
		if (isset($post->ID)) {
			$this->init($post->ID);
			if ($this->current_funnel) {
				if (isset($_GET['clear_cookie'])) {
					$this->clearUserCookie();
				}

				$this->checkAccess();
			}
		}
	}
	function init($page_id)
	{
		global $vePage;
		$page_data = get_post_meta($page_id, FUNNEL_POST_META, true);
		if ($page_data && $page_data !== '') {
			$this->current_funnel = $this->getById($page_data);
			if ($this->current_funnel) {
				$vePage->modul_type = 'funnel';
				$this->is_funnel_page = true;
				$this->userCookieName = FUNNEL_USER_COOKIE . '_' . $this->current_funnel->id;
				$this->currentPageData = $this->getFunnelItem($page_id);

				if (!$this->edit_mode && $this->currentPageData['ab_page'] && get_post_status($this->currentPageData['ab_page']) == 'publish') {
					$this->abTest = $vePage->createABTest([
						'0' => $this->currentPageData['page_id'],
						'1' => $this->currentPageData['ab_page'],
					]);
				}
			}
		}
	}

	// Cookies

	function getUserCookie()
	{
		if (isset($_COOKIE[$this->userCookieName])) {
			$this->userCookie = unserialize(stripslashes($_COOKIE[$this->userCookieName]));
		}
	}
	function setUserCookie($days = 365)
	{
		if (!$days) {
			$days = 365;
		}
		setcookie($this->userCookieName, serialize($this->userCookie), current_time('timestamp') + (60 * 60 * 24 * $days), COOKIEPATH, COOKIE_DOMAIN);
	}
	function clearUserCookie()
	{
		unset($_COOKIE[$this->userCookieName]);
		setcookie($this->userCookieName, '', current_time('timestamp') - 3600, '/');
	}
	function getAccessTime()
	{
		return isset($this->userCookie['time']) && $this->userCookie['time'] ? $this->userCookie['time'] : null;
	}

	function checkAccess()
	{
		global $post;
		if (!$this->edit_mode) {
			$this->getUserCookie();

			if (isset($_GET['setuser'])) {
				$this->setUser();
			}

			$nextPage = $this->current_funnel->getNextItem($post->ID);

			$event_tags = [];
			if ($this->abTest) {
				$event_tags = [
					'ab_test_' . $post->ID . '_' . $this->currentPageData['ab_page'] => $this->abTest,
				];
			}

			// on squeeze
			if ($this->currentPageData['type'] == 'squeeze') {
				// redirect from squeeze
				if ($this->userCookie && $this->current_funnel->redirect) {
					$first_content_item = $this->current_funnel->getFirstContentItem();
					if ($first_content_item && $this->hasAccess($first_content_item)) {
						$url = get_permalink($first_content_item['page_id']) . $this->makeatt($_GET);
						wp_redirect($url);
						die();
					}
				}

				$event_tags['squeeze'] = $post->ID;
				if ($nextPage) {
					core()->getAnalytics()->logEvent('page', $post->ID, null, $event_tags, [], 'page', $nextPage['page_id']);
				} else {
					core()->getAnalytics()->logEvent('page', $post->ID, null, $event_tags);
				}
			} else {
				if (isset($this->currentPageData['limited_access']) && $this->currentPageData['limited_access']) {
					if (!$this->hasAccess($this->currentPageData) || (!$this->current_funnel->evergreen && !$this->isPagePublished($this->currentPageData))) {
						$url = $this->current_funnel->getHomeUrl();
						if (!$url) {
							$url = get_home_url();
						}
						$url .= $this->makeatt($_GET);
						wp_redirect($url);
						die();
					}
				}

				if ($nextPage) {
					core()->getAnalytics()->logEvent('page', $post->ID, null, $event_tags, [], 'page', $nextPage['page_id']);
				} else {
					core()->getAnalytics()->logEvent('page', $post->ID, null, $event_tags);
				}
			}
			if ((isset($_GET['vs']) && $this->current_funnel->sale_platform == 'fapi')
				|| (isset($_GET['id_objednavky']) && $this->current_funnel->sale_platform == 'simpleshop')
				|| (isset($_GET['vs']) && $this->current_funnel->sale_platform == 'mioweb')
			) {
				$id = (int) ($this->current_funnel->sale_platform == 'simpleshop') ? $_GET['id_objednavky'] : $_GET['vs'];
				$this->makeSaleConversion($id);
			}
		}
	}
	function makeSaleConversion($id)
	{
		$data = MwSellingApi()->getPurchaseEventData($this->current_funnel->sale_platform, $id, $this->current_funnel);

		if ($data) {
			//print_r($data);
			core()->getAnalytics()->logEvent('funnel_purchase_f' . $this->current_funnel->id, null, $data['vs'] . '_' . $data['id'], [], [
				'price' => $data['price'],
				'currency' => $data['currency'],
				'upsell' => $data['upsell'],
				'bump' => $data['bump'],
				'vs' => $data['vs'],
				'api' => $this->current_funnel->sale_platform,
			]);

			if ($data['email']) {
					core()->getAnalytics()->setUser($data['email']);
			}
		}
	}

	function isPagePublished($item)
	{
		return !$item['publishtimestamp'] || current_time('timestamp') >= $item['publishtimestamp'];
	}

	function hasAccess($page)
	{
		return $page['limited_access'] ? isset($this->userCookie['access']) && (($this->current_funnel->evergreen && in_array($page['id'], explode(',', $this->userCookie['access']))) || (!$this->current_funnel->evergreen && $this->userCookie['access'] == 'all')) : true;
	}

	function setUser()
	{
		if ($_GET['setuser'] == $this->current_funnel->code) {
			if ($this->current_funnel->evergreen) {
				// generate list of pages for evergreen
				$pages = [];
				foreach ($this->current_funnel->getContentItems() as $c_page) {
					$pages[] = $c_page['id'];
					if ($c_page['id'] == $this->currentPageData['id']) {
						break;
					}
				}

				$this->userCookie['access'] = implode(',', $pages);
			} else {
				$this->userCookie['access'] = 'all';
			}

			if (!isset($this->userCookie['time'])) {
				$userTime = $this->getUserTime();
				$this->userCookie['time'] = $userTime ?: current_time('timestamp');
			}

			$days = $this->current_funnel->cookie_time !== '' ? (int) $this->current_funnel->cookie_time : 365;

			$this->setUserCookie($days);

			$url = get_permalink($this->currentPageData['page_id']) . $this->makeatt($_GET);
			wp_redirect($url);
			die();
		}
	}
	function getUserTime()
	{
		$user_email = core()->getAnalytics()->getUser();
		$time = null;
		if ($user_email) {
			$eventData = core()->getAnalytics()->getEventData(
				'newContact_f' . $this->current_funnel->id,
				null,
				$user_email
			);
			if (isset($eventData['time']) && $eventData['time']) {
					$time = $eventData['time'];
			}
		}

		return $time;
	}

	function getAll()
	{
		global $wpdb;
		if (empty($this->funnels)) {
			$funnels = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'mw_funnels' . ' ORDER BY funnel_id DESC');

			$this->funnels = [];
			foreach ($funnels as $funnel) {
				$this->funnels[] = new MwFunnel($funnel);
			}
		}

		return $this->funnels;
	}

	function getById($funnelId)
	{
		global $wpdb;
		$funnel = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'mw_funnels WHERE funnel_id= "' . $funnelId . '"');

		return $funnel ? new MwFunnel($funnel) : null;
	}

	function getFunnelItem($postId)
	{
		global $wpdb;
		$row = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'mw_funnel_pages WHERE fp_page_id= "' . $postId . '" OR fp_ab_page= "' . $postId . '"');

		return (bool) $row ? MwFunnel::formatItem($row) : null;
	}

	// Add funnel item
	function add_funnel_item()
	{
		$type = $_POST['type'];
		$target = 'content';

		if ($type == 'source') {
			$setting = $this->createDefaultPageSetting('source');
			$setting['id'] = md5(microtime());
			$item = $this->generateSourceItem($setting);
			$target = 'source';
		} else {
			$setting = $this->createDefaultPageSetting($type);
			$setting['id'] = md5(microtime());
			if ($type == 'squeeze') {
				$item = $this->generateSqueezeItem($setting);
				$target = 'squeeze';
			} else {
				$item = $this->generateItem($setting);
			}
		}
		wp_send_json([
			'item' => $item,
			'setting' => $setting,
			'item_id' => $setting['id'],
			'target' => $target,
		]);

		wp_die();
	}

	// funnel item

	function writeSqueezeItems($funnel)
	{
		$items = $funnel->getItemsByType('squeeze');

		$content = '<div class="mwcb_funnel_row mwcb_funnel_squeeze_row ' . (count($items) ? '' : 'empty') . '">';

		$content .= '<div class="mwcb_funnel_row_stats">';
		$sumVisits = $funnel->getSumVisits('squeeze');
		$content .= '<span class="mwcb_funnel_row_stats_visits mw_funnel_tooltip" title="' . __('Unikátních návštěvníků', 'mw_funnels') . '">' . $sumVisits . '</span>';
		$content .= '<span class="mwcb_funnel_row_stats_percent mw_funnel_tooltip" title="' . __('Kolik procent unikátních návštěvníků bylo v tomto kroku', 'mw_funnels') . '">' . $funnel->getPercentVisits($sumVisits) . '</span>';
		$content .= '</div>';

		$content .= '<div class="mwcb_squeeze_container">';

		foreach ($items as $item) {
			$content .= $this->generateSqueezeItem($item, $funnel);
			$this->items_setting[$item['id']] = $item;
		}

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}
	function generateSqueezeItem($item, $funnel = null)
	{
		$content = '';

		$visits = 0;
		$conversion = ['count' => null, 'percent' => null];
		$hasPage = isset($item['page_id']) && $item['page_id'] && get_permalink($item['page_id']) ? true : false;

		if ($funnel && $hasPage) {
			$visits = $funnel->getItemVisits($item['page_id']);
			$conversion = $funnel->getItemConversion($item['page_id']);
		}

		$tested = isset($item['ab_page']) && $item['ab_page'] && get_post_status($item['ab_page']) === 'publish';
		$hasPageId = isset($item['page_id']) && $item['page_id'];
		$page = $hasPageId ? get_post($item['page_id']) : null;
		$isPageDeleted = $page === null;
		$isPageTrashed = $page !== null && $page->post_status === 'trash';

		$content .= '<div id="mwcb_item_' . $item['id'] . '" class="mwcb_item mwcb_squeeze_item ' . ($tested ? 'mwcb_item_wtest ' : '') . ($hasPageId ? 'mwcb_item_wpage ' : '') . ($isPageDeleted ? 'mwcb_item_wdeleted ' : '') . ($isPageTrashed ? 'mwcb_item_wtrashed ' : '') . '" data-type="squeeze" data-id="' . $item['id'] . '">';

		$content .= '<div class="mwcb_item_box_container">';
		$content .= '<div class="mwcb_item_box mw_rounded">';
		$content .= $this->itemBar();

		if ($hasPageId) {
			if ($isPageDeleted) {
				$content .= mwAdminComponents::icon([
					'icon' => 'alert-triangle',
					'text' => __('Stránka je smazaná', 'mw_funnels'),
				], 'mwcb_item_error_info');
			} elseif ($isPageTrashed) {
				$content .= mwAdminComponents::icon([
					'icon' => 'alert-triangle',
					'text' => __('Stránka je v koši', 'mw_funnels'),
				], 'mwcb_item_error_info');
			} else {
				$content .= mwAdminComponents::icon([
					'icon' => 'eye',
					'text' => $visits,
					'title' => __('Unikátních návštěvníků', 'mw_funnels'),
				], 'mwcb_stats mw_funnel_tooltip');
			}
		} else {
			$content .= mwAdminComponents::icon([
				'icon' => 'alert-triangle',
				'text' => __('Není nastavena stránka', 'mw_funnels'),
			], 'mwcb_item_error_info');
		}

		if ($tested) {
			$abTest = $funnel->getItemABVisits($item['page_id']);
			$content .= $this->itemABInfo($abTest);
		}

		$content .= '<div class="mwcb_item_title">' . $item['title'] . '</div>';

		if (!$isPageDeleted && !$isPageTrashed) {
			$content .= mwAdminComponents::iconLink([
				'icon' => 'edit',
				'link' => get_permalink($item['page_id']),
				'target' => '_blank',
				'text' => __('Upravit stránku', 'mw_funnels'),
			], 'mwcb_item_edit_link');
		}
		$content .= '</div>';
		if (!$isPageDeleted && !$isPageTrashed) {
			$content .= $this->itemUrlBar($item, $funnel);
		}
		$content .= '</div>'; // item_box_container

		$content .= $this->funnelArrow($conversion['percent'], $conversion['count']);

		$content .= '</div>';

		return $content;
	}

	function writeSourceItems($funnel)
	{
		$items = $funnel->getItemsByType('source');
		$content = '<div class="mwcb_source_container mwcb_funnel_row ' . (count($items) ? '' : 'empty') . '">';

		foreach ($items as $item) {
			$visits = $funnel->getSourceVisits($item['id']);

			$content .= $this->generateSourceItem($item, $visits);
			$this->items_setting[$item['id']] = $item;
		}

		$content .= '</div>';

		return $content;
	}
	function generateSourceItem($item, $visits = 0)
	{
		$content = '';
		$content .= '<div id="mwcb_source_' . $item['id'] . '" class="mwcb_item mwcb_source_item mw_rounded" data-type="source" data-id="' . $item['id'] . '">';

		$content .= '<div class="mwcb_item_box mw_funnel_tooltip" title="' . $item['title'] . '">';
		$content .= $this->itemBar();
		$content .= '<div class="mwcb_source_item_icon" ' . ($item['color'] ? 'style="color:' . $item['color'] . '"' : '') . '>' . mw_content_icon_set($item['icon']['icon'], $item['icon']['icon_set']) . '</div>';
		$content .= '</div>';

		$content .= '<div class="mwcb_item_source_arrow">';
		$content .= '<span class="mw_funnel_tooltip"  title="' . __('Počet návštěv z tohoto zdroje', 'mw_funnels') . '">' . $visits . '</span>';
		$content .= mw_icon('icon-arrow-down');
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	function writeItems($funnel)
	{
		$items = $funnel->getItems();
		$content = '<div class="mwcb_content_container mwcb_funnel_row ' . (count($items) ? '' : 'empty') . '">';

		foreach ($items as $item) {
			if ($item['type'] != 'squeeze' && $item['type'] != 'source') {
				$content .= $this->generateItem($item, $funnel);
				$this->items_setting[$item['id']] = $item;
			}
		}

		$content .= '</div>';

		return $content;
	}
	function generateItem($item, $funnel = null)
	{
		$content = '';

		$visits = 0;
		$percents = '0%';
		$conversion = ['count' => null, 'percent' => null];
		$hasPage = isset($item['page_id']) && $item['page_id'] && get_permalink($item['page_id']) ? true : false;

		if ($funnel && $hasPage) {
			$visits = $funnel->getItemVisits($item['page_id']);
			$percents = $funnel->getPercentVisits($visits);
			$conversion = $funnel->getItemConversion($item['page_id']);
		}

		$tested = isset($item['ab_page']) && $item['ab_page'] && get_post_status($item['ab_page']) === 'publish';
		$hasPageId = isset($item['page_id']) && $item['page_id'];
		$page = $hasPageId ? get_post($item['page_id']) : null;
		$isPageDeleted = $page === null;
		$isPageTrashed = $page !== null && $page->post_status === 'trash';

		$content .= '<div id="mwcb_item_' . $item['id'] . '" class="mwcb_item mwcb_' . $item['type'] . '_item ' . ($tested ? 'mwcb_item_wtest ' : '') . ($hasPageId ? 'mwcb_item_wpage ' : '') . ($isPageDeleted ? 'mwcb_item_wdeleted ' : '') . ($isPageTrashed ? 'mwcb_item_wtrashed ' : '') . '" data-type="' . $item['type'] . '" data-id="' . $item['id'] . '">';

		$content .= '<div class="mwcb_funnel_row_stats">';
		$content .= '<span class="mwcb_funnel_row_stats_visits mw_funnel_tooltip" title="' . __('Unikátních návštěvníků', 'mw_funnels') . '">' . $visits . '</span>';
		$content .= '<span class="mwcb_funnel_row_stats_percent mw_funnel_tooltip" title="' . __('Kolik procent unikátních návštěvníků bylo v tomto kroku', 'mw_funnels') . '">' . $percents . '</span>';
		$content .= '</div>';

		$content .= '<div class="mwcb_item_box_space">';
		$content .= '<div class="mwcb_item_box_container">';
		$content .= '<div class="mwcb_item_box mw_rounded">';
		$content .= $this->itemBar(true);
		if ($hasPageId) {
			if ($isPageDeleted) {
				$content .= mwAdminComponents::icon([
					'icon' => 'alert-triangle',
					'text' => __('Stránka je smazaná', 'mw_funnels'),
				], 'mwcb_item_error_info');
			} elseif ($isPageTrashed) {
				$content .= mwAdminComponents::icon([
					'icon' => 'alert-triangle',
					'text' => __('Stránka je v koši', 'mw_funnels'),
				], 'mwcb_item_error_info');
			} else {
				$content .= mwAdminComponents::icon([
					'icon' => 'eye',
					'text' => $visits,
					'title' => __('Unikátních návštěvníků', 'mw_funnels'),
				], 'mwcb_stats mw_funnel_tooltip');
			}
		} else {
			$content .= mwAdminComponents::icon([
				'icon' => 'alert-triangle',
				'text' => __('Není nastavena stránka', 'mw_funnels'),
			], 'mwcb_item_error_info');
		}

		if ($tested) {
			$abTest = $funnel->getItemABVisits($item['page_id']);
			$content .= $this->itemABInfo($abTest);
		}

		$content .= '<div class="mwcb_item_title">' . $item['title'] . '</div>';

		if (!$isPageDeleted && !$isPageTrashed) {
			$content .= mwAdminComponents::iconLink([
				'icon' => 'edit',
				'link' => get_permalink($item['page_id']),
				'target' => '_blank',
				'text' => __('Upravit stránku', 'mw_funnels'),
			], 'mwcb_item_edit_link');
		}

		$content .= '</div>'; // item_box
		if (!$isPageDeleted && !$isPageTrashed) {
			$content .= $this->itemUrlBar($item, $funnel);
		}
		$content .= '</div>'; // item_box_container
		$content .= '</div>'; // item_box_space

		$content .= $this->funnelArrow($conversion['percent'], $conversion['count']);

		$content .= '</div>'; // item

		return $content;
	}

	function funnelArrow($percent = null, $count = null)
	{
		$content = '<div class="mwcb_funnel_arrow">';
		$content .= mw_icon('icon-arrow-down');
		if ($percent !== null) {
			$content .= '<div class="mwcb_funnel_arrow_percent mw_funnel_tooltip" title="' . __('Úspěšnost stránky. Procentuální vyjádření podílu návštěvníků stránky a návštěvníku, kteří přešli do dalšího kroku cesty zákazníka.', 'mw_funnels') . '">' . $percent . '</div>';
		}
		if ($count !== null) {
			$content .= '<div class="mwcb_funnel_arrow_count mw_funnel_tooltip" title="' . __('Počet návštěvníků, kteří přešli do dalšího kroku', 'mw_funnels') . '">' . $count . '</div>';
		}
		$content .= '</div>';

		return $content;
	}

	function itemBar($move = false)
	{
		$content = '';
		if ($move) {
			$content .= mwAdminComponents::icon([
				'icon' => 'move',
				'title' => __('Přesunout', 'mw_funnels'),
			], 'mwcb_move_item mw_funnel_tooltip');
		}
		$content .= '<div class="mwcb_item_bar">';
		$content .= mwAdminComponents::iconLink([
			'icon' => 'settings',
			'title' => __('Nastavení', 'mw_funnels'),
		], 'mwcb_edit_item mw_funnel_tooltip');
		$content .= mwAdminComponents::iconLink([
			'icon' => 'trash-2',
			'title' => __('Smazat', 'mw_funnels'),
		], 'mwcb_delete_item mw_funnel_tooltip');
		$content .= '</div>';

		return $content;
	}
	function itemABInfo($abTest)
	{
		$content = '<div class="mwcb_item_ab_test">';
		$content .= '<div class="mwcb_item_ab_variant">';
		$content .= '<div class="mwcb_ab_stats mw_funnel_tooltip" title="' . __('Úspěšnost originální stránky v A/B testu', 'mw_funnels') . '"><span>A</span> ' . $abTest['original']['conversions'] . '</div>';
		$content .= '</div>';
		$content .= '<div class="mwcb_item_ab_variant">';
		$content .= '<div class="mwcb_ab_stats mw_funnel_tooltip" title="' . __('Úspěšnost testované varianty stránky v A/B testu', 'mw_funnels') . '"><span>B</span> ' . $abTest['variant']['conversions'] . '</div>';
		$content .= '</div>';
		$content .= '</div>';

		$content .= $this->itemTag(__('A/B test', 'mw_funnels'), 'ab');

		return $content;
	}
	function itemUrlBar($item, $funnel)
	{
		$content = '<div class="mwcb_item_url mw_rounded">';
		$content .= '<input type="text" onClick="this.select();" value="' . $funnel->getPermalink($item) . '" readonly="readonly">';
		$content .= mwAdminComponents::iconLink([
			'icon' => 'clipboard',
			'attrs' => 'data-url="' . $funnel->getPermalink($item) . '" title="' . __('Zkopírovat URL adresu do schránky', 'mw_funnels') . '"',
		], 'mwcb_item_url_to_clipboard mw_funnel_tooltip');
		$content .= '</div>';

		return $content;
	}
	function itemTag($text, $name)
	{
		return '<div class="mwcb_item_tag mwcb_item_tag_' . $name . '">' . $text . '</div>';
	}

	function generateFunnelItemSetting()
	{
		$page_setting = $_POST['setting'];

		$type = $page_setting['type'];

		write_meta($this->pageTypes[$type]['setting'], $page_setting, 'cp', 'cp');
		echo '<input type="hidden" name="type" value="' . $type . '" />';
		echo '<input type="hidden" name="order" value="' . ($page_setting['order'] ?? '') . '" />';

		die();
	}

	function openFunnelsSetting()
	{
		$funnels = $this->getAll();

		$content = mwAdminComponents::title([
			'text' => __('Celkové statistiky', 'mw_funnels'),
			'onright' => mwAdminComponents::rangeSelect([]),
		], 'h2');

		// statistics
		$content .= '<div class="mw_dashboard_statistics">';
		$content .= $this->dashboardStatistics($funnels);
		$content .= '</div>';

		// list

		$content .= mwAdminComponents::title([
			'text' => __('Cesty zákazníka', 'mw_funnels'),
			'onright' => mwAdminComponents::button([
					'button_text' => __('Přidat', 'mw_funnels'),
					'icon' => 'plus',
			], 'mw_open_funnel_installator'),
		], 'h2');

		$object = mwSetting()->getObject('mw_funnels');
		$listArgs = $object->service()->getListArgs(1, -1);

		$content .= '<div class="mw_dashboard_list_container">';
		$content .= mwAdminComponents::table($listArgs, 'mw_table_list');
		$content .= '</div>';

		echo $content;
	}

	function ajaxDashboardStatistics()
	{
		$funnels = $this->getAll();
		$period = mwSetting::getPeriod($_POST['period'], $_POST['from'] ?? '', $_POST['to'] ?? '');
		echo $this->dashboardStatistics($funnels, $period['from'], $period['to']);
		die();
	}

	function ajaxFunnelResetStatistics()
	{
		$funnel = $this->getById($_POST['funnel_id']);

		if ($funnel === null) {
			mwMessages()->error(__('Nastavení se nepodařilo uložit.', 'mw_funnels'));
			wp_send_json([
				'success' => mwMessages()->success,
				'errors' => mwMessages()->errors,
				'html' => mwMessages()->writeHtml(),
			]);
			die();
		}

		global $wpdb;
		$now = new \DateTimeImmutable();

		$status = $wpdb->update($wpdb->prefix . 'mw_funnels', [
			'funnel_statistics_reset_at' => $now->format('Y-m-d H:i:s'),
		], [
			'funnel_id' => $_POST['funnel_id'],
		]);

		if ($status) {
			$funnel = $this->getById($_POST['funnel_id']);
			$funnel->loadStatistics();

			mwMessages()->success(__('Statistiky byly vynulovány.', 'mw_funnels'));
			wp_send_json([
				'builder' => $this->generateFunnelDiagram($funnel),
				'statistics' => $this->generateFunnelStatistics($funnel),
				'success' => mwMessages()->success,
				'errors' => mwMessages()->errors,
				'html' => mwMessages()->writeHtml(),
			]);
		} else {
			mwMessages()->error(__('Nastavení se nepodařilo uložit.', 'mw_funnels'));
			wp_send_json([
				'success' => mwMessages()->success,
				'errors' => mwMessages()->errors,
				'html' => mwMessages()->writeHtml(),
			]);
		}

		die();
	}

	function dashboardStatistics($funnels, $from = null, $to = null)
	{
		$sumOrders = 0;
		$contacts = 0;
		$visitors = 0;

		foreach ($funnels as $funnel) {
			$funnel->loadStatistics($from, $to);
			$visitors += $funnel->getSumVisits();
			$contacts += $funnel->getContactsNum();
			$sumOrders += $funnel->statistics['sum_orders'];
		}

		$content = mwAdminComponents::statisticsMainBox([
			'value' => number_format($sumOrders, 2, '.', ' ') . ' Kč',
			'text' => __('Tržby celkem', 'mw_funnels'),
			'icon' => 'dollar-sign',
		]);
		$content .= mwAdminComponents::statisticsBox([
			'value' => number_format($contacts, 0, '.', ' '),
			'text' => __('Kontaktů celkem', 'mw_funnels'),
			'icon' => 'mail',
		]);
		$content .= mwAdminComponents::statisticsBox([
			'value' => number_format($visitors, 0, '.', ' '),
			'text' => __('Návštěv celkem', 'mw_funnels'),
			'icon' => 'eye',
		]);

		return $content;
	}

	function open_funnel_setting($funnel)
	{
		$funnel->loadStatistics(null, null /*, ['utm_source'=>'facebook','utm_medium'=>'','utm_funnel'=>'']*/);

		$object = mwSetting()->getObject('mw_funnels');

		$content = '<div class="mw_funnel_detail">';

		$content .= mwAdminComponents::modalHead([
			'title' => $funnel->name,
			'back' => true,
			'style' => 'big',
			'close_link' => $object->getUrl(),
			'menu' => [
				[
					'text' => __('Cesta zákazníka', 'mw_funnels'),
					'icon' => '',
					'link' => '#mw_funnel_builder_tab',
				],
				[
					'text' => __('Nastavení', 'mw_funnels'),
					'icon' => '',
					'link' => '#mw_funnel_setting_tab',
				],
			],
		]);

		$content .= '<div id="mw_funnel_builder_tab" class="mw_funnel_tab mw_funnel_builder_tab ' . ($funnel->hasItems() ? '' : 'empty') . ' active">';

		// builder
		$content .= '<div class="mw_funnel_builder mw_animated">';

		// filter
		$content .= '<div class="mwcb_filter">';
		$content .= mwAdminComponents::rangeSelect([]);
		//$usedUTMs = $funnel->getUsedUTMs();
		//print_r($usedUTMs);
		$sources = $funnel->getItemsByType('source');
		$source_options = [];
		foreach ($sources as $source) {
			$source_options[$source['id']] = $source['title'];
		}
		$content .= mwAdminComponents::filter([
			'items' => [
				[
					'name' => 'source',
					'label' => __('Zdroje návštěvnosti', 'mw_funnels'),
					'empty' => __('- Vyberte zdroj návštěvnosti -', 'mw_funnels'),
					'type' => 'select',
					'options' => $source_options,
				],
				[
					'name' => 'divider',
					'label' => __('nebo', 'mw_funnels'),
					'type' => 'divider',
				],
				[
					'name' => 'utm_source',
					'label' => __('UTM source', 'mw_funnels'),
					'type' => 'text',
				],
				[
					'name' => 'utm_medium',
					'label' => __('UTM medium', 'mw_funnels'),
					'type' => 'text',
				],
				[
					'name' => 'utm_campaign',
					'label' => __('UTM campaign', 'mw_funnels'),
					'type' => 'text',
				],
				[
					'name' => 'utm_term',
					'label' => __('UTM term', 'mw_funnels'),
					'type' => 'text',
				],
				[
					'name' => 'utm_content',
					'label' => __('UTM content', 'mw_funnels'),
					'type' => 'text',
				],
			],
		]);
		$content .= '</div>';

		// statistics
		$content .= '<div class="mwcb_statistics mw_animated">';
		$content .= $this->generateFunnelStatistics($funnel);
		$content .= '</div>';

		// diagram
		$content .= '<div class="mwcb_diagram">';
		$content .= $this->generateFunnelDiagram($funnel);
		$content .= '</div>';

		$content .= '</div>'; // end mw_funnel_builder

		// add item
		$args = [
			'button_text' => __('Přidat', 'mw_funnels'),
			'icon' => 'plus',
		];
		foreach ($this->pageTypes as $type => $setting) {
			$args['items'][] = [
				'text' => $setting['menu_title'],
				'icon' => '',
				'link' => $type,
			];
		}
		$content .= mwAdminComponents::dropButton($args, 'mwcb_add_item mwcb_for_edit');

		$content .= mwAdminComponents::button([
			'button_text' => __('Upravit', 'mw_funnels'),
			'icon' => 'edit-2',
		], 'mwcb_edit_funnel mwcb_edit_funnel_but');

		// admin panel
		$content .= '<div class="mwcb_edit_panel mw_animated mw_admin_setting_container">'
			. '<div class="mwcb_edit_panel_content">'
			. '<form id="mwcb_item_setting" action="" method="post">'
			. '</form>'
			. '</div>'
			. mwAdminComponents::iconLink([
				'icon' => 'check',
				'text' => __('Hotovo', 'mw_funnels'),
			], 'mwcb_close_edit_panel')
			. '</div>';

		$content .= mwAdminComponents::saveBar(['save_button_class' => 'mwcb_save_funnel'], 'mwcb_for_edit mwcb_funnel_footer mw_animated');

		$content .= '</div>'; // end mw_funnel_builder_tab

		// funnel setting
		$content .= '<div id="mw_funnel_setting_tab" class="mw_funnel_tab mw_funnel_setting_tab">';
		$content .= '<div class="mw_funnel_setting_tab_content">';
		$content .= '<form id="mw_funnel_setting_form" action="" method="post">';
		$content .= '<div class="mw_funnel_setting mw_admin_setting_container mw_admin_setting_narrow_container">';
		$content .= '<div class="mw_messages_container"></div>';
		$setting = [

			[
				'id' => 'basic_setting',
				'type' => 'toggle_group',
				'open' => true,
				'title' => __('Základní nastavení', 'mw_funnels'),
				'setting' => [
					[
						'id' => 'name',
						'title' => __('Název cesty zákazníka', 'mw_funnels'),
						'required' => 1,
						'type' => 'text',
					],
				],
			],
			[
				'id' => 'statistics_setting',
				'type' => 'toggle_group',
				'open' => true,
				'title' => __('Nastavení statistik', 'mw_funnels'),
				'setting' => [
					[
						'id' => 'show_sell',
						'title' => __('Zobrazení tržeb', 'mw_funnels'),
						'label' => __('Zobrazovat tržby ve statistikách', 'mw_funnels'),
						'type' => 'switch',
						'show' => 'show_sell',
					],
					[
						'id' => 'sell_setting',
						'title' => __('Prodejní nástroj', 'mw_funnels'),
						'type' => 'funnel_sell_setting',
						'show_group' => 'show_sell',
						'show_val' => '1',
					],
					/*
					[
						'id' => 'upsell',
						'title' => __('Upsell produkt', 'mw_funnels'),
						'type' => 'products_select',
						'show_group' => 'show_sell',
						'show_val' => '1',
					],
					[
						'id' => 'bump',
						'title' => __('Miniupsell produkt (bump produkt)', 'mw_funnels'),
						'type' => 'products_select',
						'show_group' => 'show_sell',
						'show_val' => '1',
					],
					*/
					[
						'id' => 'show_contacts',
						'title' => __('Zobrazení kontaktů', 'mw_funnels'),
						'label' => __('Zobrazovat počet získaných kontaktů ve statistikách', 'mw_funnels'),
						'type' => 'switch',
						'show_group' => 'show_sell',
						'show_val' => '1',
					],
					[
						'id' => 'statistics_reset',
						'title' => __('Vynulovat statistiky', 'mw_funnels'),
						'type' => 'static',
						'content' => mwAdminComponents::button([
							'icon' => 'refresh-cw',
							'button_text' => __('Vynulovat statistiky', 'mw_funnels'),
						], 'mw_statistics_reset'),
					],
				],
			],
			[
				'id' => 'funnel_setting',
				'type' => 'toggle_group',
				'open' => true,
				'title' => __('Pokročilé nastavení', 'mw_funnels'),
				'setting' => [
					[
						'id' => 'code',
						'title' => __('Přístupový kód', 'mw_funnels'),
						'type' => 'text',
						'maxlength' => 10,
						'required' => 1,
						'tooltip' => __('Přístupový kód k neveřejným stránkám cesty zákazníka. Tento kód budete používat jako hodnotu atributu "setuser" v URL adrese stránek, na které chcete umožnit vstup pouze registrovaným uživatelům (zadali vám v rámci cesty zákazníka e-mailovou adresu). Přístupový kód musí být jedinečný pro každou cestu zákazníka. Maximální délka kódu je 10 znaků a může obsahovat pouze čísla a znaky bez diakritiky a bez mezer.', 'mw_funnels'),
					],
					[
						'id' => 'duration',
						'title' => __('Délka platnosti přístupu', 'mw_funnels'),
						'type' => 'text',
						'content' => '365',
						'tooltip' => __('Doba od registrace, po kterou může uživatel vstoupit na neveřejné stránky cesty zákazníka. Po jejím vypršení se uživateli přístup znemožní. Při vytvoření nového přístupu se uživateli vynulují všechny odpočty a bude cestou zákazníka procházet opět od začátku. Pokud nic nevyplníte, platnost přístupu se nastaví na jeden rok.', 'mw_funnels'),
					],
					[
						'id' => 'redirect',
						'title' => __('Přesměrování ze vstupní stránky', 'mw_funnels'),
						'label' => __('Automaticky přesměrovávat registrované uživatele ze vstupní stránky na první obsahovou stránku', 'mw_funnels'),
						'type' => 'switch',
						'tooltip' => __('Pokud už je uživatel registrovaný v rámci vaší cesty zákazníka a znovu se pokouší o přístup na stránku s registrací, můžete ho místo toho nasměrovat rovnou na první stránku s obsahem.', 'mw_funnels'),
					],
					[
						'id' => 'evergreen',
						'title' => __('Evergreen', 'mw_funnels'),
						'label' => __('Aktivovat evergreen mód', 'mw_funnels'),
						'type' => 'switch',
						'desc' => __('Aktivní evergreen mód zpřístupní registrovaným uživatelům pouze ty stránky, na které je přímo odkážete (za použití výše nastaveného přístupového kódu v URL např. při odkazování z vaší e-mailové kampaně navázané na cestu zákazníka). Pokud jsme u stránek nastavili čas zveřejnění, toto nastavení se při aktivním evegreen módu nebere v úvahu. Při neaktivním evergreen módu budou stránky cesty zákazníka přístupné ihned, případně v čas nastavený u konkrétní stránky cesty zákazníka.', 'mw_funnels'),
					],
				],
			],

		];
		ob_start();
		write_meta($setting, [
			'name' => $funnel->name,
			'code' => $funnel->code,
			'sell_setting' => [
				'api' => $funnel->sale_platform,
				'upsell' => $funnel->upsell,
				'bump' => $funnel->bump,
			],
			'duration' => $funnel->cookie_time,
			'evergreen' => $funnel->evergreen,
			'redirect' => $funnel->redirect,
			'show_sell' => $funnel->show_sell,
			'show_contacts' => $funnel->show_contacts,
		], 'funnel', 'funnel');
		$content .= ob_get_contents();
		ob_end_clean();
		$content .= '</div>';

		$content .= '</form>';
		$content .= '</div>';
		$content .= mwAdminComponents::saveBar([
			'save_button_text' => __('Uložit změny', 'mw_funnels'),
			'hide_storno' => 1,
			'save_button_class' => 'mwcb_save_funnel_setting',
		], 'mwcb_setting_footer');
		$content .= '</div>'; // end mw_funnel_setting_tab

		$content .= '</div>';

		return $content;
	}

	function generateFunnelDiagram($funnel, $utm_filter = [])
	{
		// path diagram
		$content = '';

		if (count($utm_filter)) {
			$content .= '<div class="mwcb_diagram_filter_info mw_rounded">';
			$content .= __('Statistiky jsou filtrovány podle ');
			if (isset($utm_filter['source'])) {
				$content .= __('zdroje návštěvnosti') . ' <strong>' . $utm_filter['source'] . '</strong>';
			} else {
				$filter_list = [];
				foreach ($utm_filter as $utm_key => $utm_val) {
					if ($utm_key == 'utm_source') {
						$text = __('UTM source');
					} elseif ($utm_key == 'utm_medium') {
						$text = __('UTM medium');
					} elseif ($utm_key == 'utm_campaign') {
						$text = __('UTM campaign');
					} elseif ($utm_key == 'utm_term') {
						$text = __('UTM campaign');
					} elseif ($utm_key == 'utm_content') {
						$text = __('UTM campaign');
					}

					$filter_list[] = $text . ': <strong>' . $utm_val . '</strong>';
				}

				$content .= implode(', ', $filter_list);
			}

			$content .= ' ... ' . mwAdminComponents::link([
				'text' => __('Zrušit filtr', 'cms'),
			], 'mw_statistic_filter_reset');
			$content .= '</div>';
		}

		$content .= '<div class="mw_messages_container"></div>';

		$content .= $this->writeSourceItems($funnel);
		$content .= $this->writeSqueezeItems($funnel);
		$content .= $this->writeItems($funnel);

		if ($funnel->hasItems() && $funnel->show_sell) {
			$content .= $this->funnelArrow();
			$content .= '<div class="mwcb_diagram_sum">' . $funnel->getSumOrders() . '</div>';
		}

		$content .= '<div class="mwcb_diagram_empty_info">'
			. '<p>' . __('V cestě zákazníka nemáte žádné stránky. Klikněte na tlačítko Přidat stránky do cesty zákazníka a poté přidejte stránky.', 'mw_funnels') . '</p>'
			. mwAdminComponents::button([
				'button_text' => __('Přidat stránky do cesty zákazníka', 'mw_funnels'),
				'icon' => 'plus',
				'style' => 'big',
			], 'mwcb_edit_funnel')
			. '</div>';

		$content .= '<script type="text/javascript">
			  /* <![CDATA[ */
			  var mw_funnel_items_setting=' . (count($this->items_setting) ? json_encode($this->items_setting) : '{}') . '
			  var mw_funnel_id=' . $funnel->id . '
			  /* ]]> */
			  </script>';

		return $content;
	}

	function generateFunnelStatistics($funnel)
	{
		$content = $funnel->show_sell ? mwAdminComponents::statisticsMainBox([
				'value' => $funnel->getSumOrders(),
				'text' => __('Tržby celkem', 'mw_funnels'),
				'icon' => 'dollar-sign',
		]) : mwAdminComponents::statisticsMainBox([
				'value' => $funnel->getContactsNum(),
				'text' => __('Kontaktů', 'mw_funnels'),
				'icon' => 'mail',
		]);

		$content .= '<div class="mwcb_funnel_statistics mw_rounded">';

		$content .= '<div class="mwcb_funnel_statistics_row">';
		$content .= mwAdminComponents::icon([
			'icon' => 'eye',
			'title' => __('Počet unikátních návštěvníků v cestě zákazníka', 'mw_funnels'),
		], 'mw_funnel_tooltip');
		$content .= '<div class="mwcb_funnel_statistics_val">' . $funnel->getSumVisits() . '</div>';
		$content .= '<div class="mwcb_funnel_statistics_text">' . __('Návštěvníků', 'mw_funnels') . '</div>';
		$content .= '<div class="mwcb_funnel_statistics_percent">100%</div>';
		$content .= '<div class="mwcb_funnel_statistics_bar"><span class="mw_rounded" style="width:100%"></span></div>';
		$content .= '</div>';

		if (($funnel->show_sell && $funnel->show_contacts) || !$funnel->show_sell) {
			$content .= '<div class="mwcb_funnel_statistics_row">';
			$content .= mwAdminComponents::icon([
				'icon' => 'mail',
				'title' => __('Počet získaných e-mailových kontaktů', 'mw_funnels'),
			], 'mw_funnel_tooltip');
			$content .= '<div class="mwcb_funnel_statistics_val">' . $funnel->getContactsNum() . '</div>';
			$content .= '<div class="mwcb_funnel_statistics_text">' . __('Kontaktů', 'mw_funnels') . '</div>';
			$percent = $funnel->getPercentVisits($funnel->getContactsNum(), true);
			$content .= '<div class="mwcb_funnel_statistics_percent">' . $percent . '</div>';
			$content .= '<div class="mwcb_funnel_statistics_bar"><span class="mw_rounded" style="width:' . $percent . '"></span></div>';
			$content .= '</div>';
		}

		if ($funnel->show_sell) {
			$content .= '<div class="mwcb_funnel_statistics_row">';
			$content .= mwAdminComponents::icon([
				'icon' => 'shopping-cart',
				'title' => __('Počet prodejů', 'mw_funnels'),
			], 'mw_funnel_tooltip');
			$content .= '<div class="mwcb_funnel_statistics_val">' . $funnel->getOrdersCount() . '</div>';
			$content .= '<div class="mwcb_funnel_statistics_text">' . __('Prodejů', 'mw_funnels') . '</div>';
			$percent = $funnel->getPercentVisits($funnel->getOrdersCount(), true);
			$content .= '<div class="mwcb_funnel_statistics_percent">' . $percent . '</div>';
			$content .= '<div class="mwcb_funnel_statistics_bar"><span class="mw_rounded" style="width:' . $percent . '"></span></div>';
			$content .= '</div>';

			if ($funnel->upsell) {
				$content .= '<div class="mwcb_funnel_statistics_row">';
				$content .= mwAdminComponents::icon([
					'icon' => 'trending-up',
					'title' => __('Počet prodaných upsellů', 'mw_funnels'),
				], 'mw_funnel_tooltip');
				$content .= '<div class="mwcb_funnel_statistics_val">' . $funnel->getUpsellsCount() . '</div>';
				$percent = $funnel->getPercentVisits($funnel->getUpsellsCount(), true);
				$content .= '<div class="mwcb_funnel_statistics_text">' . __('Upsellů', 'mw_funnels') . '</div>';
				$content .= '<div class="mwcb_funnel_statistics_percent">' . $percent . '</div>';
				$content .= '<div class="mwcb_funnel_statistics_bar"><span class="mw_rounded" style="width:' . $percent . '"></span></div>';
				$content .= '</div>';
			}

			if ($funnel->bump || ($funnel->sale_platform == 'mioweb' && $funnel->getBumpsCount())) {
				$content .= '<div class="mwcb_funnel_statistics_row">';
				$content .= mwAdminComponents::icon([
					'icon' => 'check-circle',
					'title' => __('Počet prodaných miniupsellů', 'mw_funnels'),
				], 'mw_funnel_tooltip');
				$content .= '<div class="mwcb_funnel_statistics_val">' . $funnel->getBumpsCount() . '</div>';
				$percent = $funnel->getPercentVisits($funnel->getBumpsCount(), true);
				$content .= '<div class="mwcb_funnel_statistics_text">' . __('Miniupsellů (bump)', 'mw_funnels') . '</div>';
				$content .= '<div class="mwcb_funnel_statistics_percent">' . $percent . '</div>';
				$content .= '<div class="mwcb_funnel_statistics_bar"><span class="mw_rounded" style="width:' . $percent . '"></span></div>';
				$content .= '</div>';
			}
		}

		$content .= '</div>';

		if ($funnel->show_sell) {
			$content .= mwAdminComponents::statisticsBox([
				'value' => $funnel->getAverageOrder(),
				'text' => __('Průměrná objednávka', 'mw_funnels'),
				'icon' => 'target',
			]);
			$content .= '<div class="mwcb_statistics_boxes">';
			$content .= mwAdminComponents::statisticsBox([
				'value' => $funnel->getOrdersPerVisits(),
				'text' => __('Výnos/návštěva', 'mw_funnels'),
				'icon' => 'dollar-sign',
			]);
			if (($funnel->show_sell && $funnel->show_contacts) || !$funnel->show_sell) {
				$content .= mwAdminComponents::statisticsBox([
					'value' => $funnel->getOrdersPerContacts(),
					'text' => __('Výnos/kontakt', 'mw_funnels'),
					'icon' => 'dollar-sign',
				]);
			}
			$content .= '</div>';
		}

		return $content;
	}

	// Add funnel item
	function reloadFunnel()
	{
		$funnel = $this->getById($_POST['cId']);

		$period = isset($_POST['period']) ? mwSetting::getPeriod($_POST['period'], $_POST['from'], $_POST['to']) : [
				'from' => null,
				'to' => null,
		];

		$utm = [];
		$filter = false;
		if (isset($_POST['source']) && $_POST['source']) {
			$item = $funnel->getItemsById($_POST['source']);
			if (isset($item['utm_source']) && $item['utm_source']) {
					$utm['utm_source'] = $item['utm_source'];
			}
			if (isset($item['utm_medium']) && $item['utm_medium']) {
					$utm['utm_medium'] = $item['utm_medium'];
			}
			if (isset($item['utm_campaign']) && $item['utm_campaign']) {
					$utm['utm_campaign'] = $item['utm_campaign'];
			}
			if (isset($item['utm_term']) && $item['utm_term']) {
					$utm['utm_term'] = $item['utm_term'];
			}
			if (isset($item['utm_content']) && $item['utm_content']) {
					$utm['utm_content'] = $item['utm_content'];
			}

			$utm['source'] = $item['title'];
		} else {
			if (isset($_POST['utm_source']) && $_POST['utm_source']) {
					$utm['utm_source'] = $_POST['utm_source'];
			}
			if (isset($_POST['utm_medium']) && $_POST['utm_medium']) {
					$utm['utm_medium'] = $_POST['utm_medium'];
			}
			if (isset($_POST['utm_campaign']) && $_POST['utm_campaign']) {
					$utm['utm_campaign'] = $_POST['utm_campaign'];
			}
			if (isset($_POST['utm_term']) && $_POST['utm_term']) {
					$utm['utm_term'] = $_POST['utm_term'];
			}
			if (isset($_POST['utm_content']) && $_POST['utm_content']) {
					$utm['utm_content'] = $_POST['utm_content'];
			}
		}
		if (count($utm)) {
				$filter = true;
		}

		$funnel->loadStatistics($period['from'], $period['to'], $utm);

		wp_send_json([
			'builder' => $this->generateFunnelDiagram($funnel, $utm),
			'statistics' => $this->generateFunnelStatistics($funnel),
			'filter' => $filter,
		]);

		wp_die();
	}

	// New funnel
	function ajaxInstallNewFunnel()
	{
		//print_r($_POST['form']);
		global $wpdb;

		$wpdb->insert($wpdb->prefix . 'mw_funnels', [
			'funnel_title' => $_POST['name'],
			'funnel_code' => mt_rand(10000, 99999),
			'funnel_show_sell' => '1',
			'funnel_show_contacts' => '1',
		]);
		$funnel_id = $wpdb->insert_id;
		if ($_POST['select_type']) {
			$template_path = MWInstallator()->getTemplate('funnel', $_POST['select_type']);
			if ($template_path) {
				$install = require_once($template_path . 'install.php');
				$installed_pages = [];
				foreach ($install['items'] as $item) {
					// install pages
					if (isset($item['page']) && $item['page']) {
						$post_id = MwWebInstall()->install_page($item['page'], $template_path, $installed_pages);
						$item['page_id'] = $post_id;
						$installed_pages[$item['page']] = $post_id;
					}
					$this->createItem($item, $funnel_id);
				}

				if (isset($install['setting'])) {
					$wpdb->update($wpdb->prefix . 'mw_funnels', [
						'funnel_show_sell' => $install['setting']['show_sell'] ?? '1',
						'funnel_show_contacts' => $install['setting']['show_contacts'] ?? '1',
					], [
						'funnel_id' => $funnel_id,
					]);
				}
			}
		}

		$object = mwSetting()->getObject('mw_funnels');
		echo $object->getEditUrl($funnel_id);

		die();
	}

	// Menu

	function addFastNav()
	{
		$funnels = $this->getAll();
		if ($this->existFunnels()) {
			global $vePage;
			$vePage->addFastNav(
				[
					'id' => 'funnels',
					'title' => __('Cestu zákazníka', 'mw_funnels'),
					'url' => '#',
					'submenu' => $this->create_fast_submenu($funnels),
				],
				19
			);
		}
	}

	function create_fast_submenu($funnels)
	{
		$menu = '<ul>';
		foreach ($funnels as $funnel) {
			$menu .= '<li><a href="' . $funnel->getHomeUrl() . '">' . $funnel->name . '</a></li>';
		}
		$menu .= '</ul>';

		return $menu;
	}

	function fast_nav_current($current)
	{
		if ($this->is_funnel_page) {
			$current['title'] = __('Cesta zákazníka', 'mw_funnels');
			$current['url'] = $this->current_funnel->getHomeUrl();
		}

		return $current;
	}

	function makeatt($q)
	{
		$att = [];
		if (is_array($q)) {
			foreach ($q as $k => $v) {
				if ($k != 'setuser' && $k != 'Errors' && $k != 'clear_cookie' && $k != 'p' && $k != 'page_id') {
					$att[] = $k . '=' . urlencode($v);
				}
			}
		}

		return count($att) ? '?' . implode('&', $att) : '';
	}

	function existFunnels()
	{
		return !empty($this->funnels) ? true : false;
	}

	function saveFunnelItems()
	{
		global $wpdb;

		$funnel_id = $_POST['funnel_id'];
		$sets = $_POST['set'];

		// check if is page in different funnel
		if (count($sets)) {
			$fitems = $wpdb->get_results('SELECT fp_page_id FROM ' . $wpdb->prefix . 'mw_funnel_pages WHERE fp_funnel_id != ' . $funnel_id);
			foreach ($fitems as $fitem) {
				foreach ($sets as $set) {
					if (isset($set['page_id']) && $set['page_id'] && $fitem->fp_page_id == $set['page_id']) {
						$used_post = get_post($set['page_id']);
						mwMessages()->error(sprintf(__('Cesta zákazníka nesmí obsahovat stránky, které jsou zařazeny už v jiné cestě zákazníka. Stránka "%s" v kroku "%s" je již použita v jiné cestě zákazníka.', 'mw_funnels'), $used_post->post_title, $set['title']));
						wp_send_json([
							'success' => mwMessages()->success,
							'errors' => mwMessages()->errors,
							'html' => mwMessages()->writeHtml(true),
						]);
						die();
					}
				}
			}
		}


		$fp_items = $wpdb->get_results('SELECT fp_id FROM ' . $wpdb->prefix . 'mw_funnel_pages WHERE fp_funnel_id = ' . $funnel_id);

		// delete items
		foreach ($fp_items as $item) {
			if (!isset($sets[$item->fp_id])) {
				$wpdb->delete($wpdb->prefix . 'mw_funnel_pages', ['fp_id' => $item->fp_id]);
			}
		}

		$pages = new WP_Query([
			'post_type' => 'page',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_key' => FUNNEL_POST_META,
			'meta_value' => $funnel_id,
		]);
		foreach ($pages->posts as $page_id) {
			delete_post_meta($page_id, FUNNEL_POST_META);
		}


		if (count($sets)) {
			foreach ($sets as $id => $set) {
				$result = $wpdb->get_row('SELECT fp_id FROM ' . $wpdb->prefix . 'mw_funnel_pages WHERE fp_id= "' . $id . '"');
				$update = $wpdb->num_rows ? $id : 0;

				$this->createItem($set, $funnel_id, $update);
			}
		}

		$funnel = $this->getById($funnel_id);
		$funnel->loadStatistics();

		wp_send_json([
			'success' => 1,
			'builder' => $this->generateFunnelDiagram($funnel),
			'statistics' => $this->generateFunnelStatistics($funnel),
		]);

		die();
	}

	function createItem($set, $funnel_id, $update = false)
	{
		global $wpdb;

		$cols = [
			'fp_page_id' => $set['page_id'] ?? null,
			'fp_title' => $set['title'],
			'fp_limited_access' => isset($set['limited_access']) && $set['limited_access'] ? 1 : 0,
			'fp_type' => $set['type'],
			'fp_utm_source' => $set['utm_source'] ?? null,
			'fp_utm_medium' => $set['utm_medium'] ?? null,
			'fp_utm_campaign' => $set['utm_campaign'] ?? null,
			'fp_utm_term' => $set['utm_term'] ?? null,
			'fp_utm_content' => $set['utm_content'] ?? null,
			'fp_order' => $set['order'] ?? 0,
			'fp_icon' => isset($set['icon']) ? serialize($set['icon']) : null,
			'fp_color' => $set['color'] ?? null,
			'fp_ab_page' => $set['ab_page'] ?? null,
			'fp_publishtime' => isset($set['publishtime']) && isset($set['publishtime']['date']) ? serialize($set['publishtime']) : null,
			'fp_nav_hide' => $set['nav_hide'] ?? 0,
			'fp_nav_title' => $set['nav_title'] ?? '',
			'fp_nav_ba_title' => $set['nav_ba_title'] ?? '',
			'fp_nav_image' => isset($set['nav_image']) && isset($set['nav_image']['image']) && $set['nav_image']['image'] ? serialize($set['nav_image']) : null,
			'fp_nav_ba_image' => isset($set['nav_ba_image']) && isset($set['nav_ba_image']['image']) && $set['nav_ba_image']['image'] ? serialize($set['nav_ba_image']) : null,
		];

		if ($update) {
			$wpdb->update($wpdb->prefix . 'mw_funnel_pages', $cols, [
				'fp_id' => $update,
			]);
		} else {
			$cols['fp_funnel_id'] = $funnel_id;

			$wpdb->insert($wpdb->prefix . 'mw_funnel_pages', $cols);
		}

		if (isset($set['page_id']) && $set['page_id']) {
			update_post_meta($set['page_id'], FUNNEL_POST_META, $funnel_id);
		}
		if (isset($set['ab_page']) && $set['ab_page']) {
			update_post_meta($set['ab_page'], FUNNEL_POST_META, $funnel_id);
		}
	}

	function ajaxSaveFunnelSetting()
	{
		global $wpdb;

		if (!preg_match('/^[A-Za-z0-9]+$/i', $_POST['funnel']['code'])) {
			mwMessages()->error(__('Nastavení se nepodařilo uložit. Přistůpový kód má špatný formát. Maximální délka kódu je 10 znaků a může obsahovat pouze čísla a znaky bez diakritiky a bez mezer.', 'mw_funnels'));
			wp_send_json([
				'success' => mwMessages()->success,
				'errors' => mwMessages()->errors,
				'html' => mwMessages()->writeHtml(true),
			]);
			die();
		}

		$status = $wpdb->update($wpdb->prefix . 'mw_funnels', [
			'funnel_title' => $_POST['funnel']['name'],
			'funnel_upsell' => $_POST['funnel']['sell_setting']['upsell'] ?? null,
			'funnel_bump' => $_POST['funnel']['sell_setting']['bump'] ?? null,
			'funnel_sale_platform' => $_POST['funnel']['sell_setting']['api'],
			'funnel_code' => $_POST['funnel']['code'],
			'funnel_cookie_time' => $_POST['funnel']['duration'],
			'funnel_redirect' => $_POST['funnel']['redirect'] ?? null,
			'funnel_evergreen' => $_POST['funnel']['evergreen'] ?? null,
			'funnel_show_sell' => $_POST['funnel']['show_sell'] ?? null,
			'funnel_show_contacts' => $_POST['funnel']['show_contacts'] ?? null,
		], [
			'funnel_id' => $_POST['funnel_id'],
		]);

		if ($status === false) {
			mwMessages()->error(__('Nastavení se nepodařilo uložit.', 'mw_funnels'));
			wp_send_json([
				'success' => mwMessages()->success,
				'errors' => mwMessages()->errors,
				'html' => mwMessages()->writeHtml(true),
			]);
		} else {
			$funnel = $this->getById($_POST['funnel_id']);
			$funnel->loadStatistics();

			wp_send_json([
				'builder' => $this->generateFunnelDiagram($funnel),
				'statistics' => $this->generateFunnelStatistics($funnel),
				'title' => $_POST['funnel']['name'],
				'success' => mwMessages()->success,
				'errors' => mwMessages()->errors,
				'html' => mwMessages()->writeHtml(true),
			]);
		}

		die();
	}

	function ajaxSendMailConversion()
	{
		mwlog('funnel', 'contact ' . ($_POST['contact'] ?? '') . ' sended (funnel id ' . ($_POST['funnel_id'] ?? '') . ')', MWLL_INFO);
		if (isset($_POST['contact']) && $_POST['contact'] && isset($_POST['funnel_id']) && $_POST['funnel_id']) {
			core()->getAnalytics()->setUser($_POST['contact']);
			core()->getAnalytics()->logEvent('newContact_f' . $_POST['funnel_id'], null, $_POST['contact'], [], [
				'time' => current_time('timestamp'),
			]);

			mwlog('funnel', 'contact ' . ($_POST['contact'] ?? '') . ' saved (funnel id ' . ($_POST['funnel_id'] ?? '') . ')', MWLL_INFO);
		}
		die();
	}

	// a/b testing
	function ajaxCreateABTestPage()
	{
		if ($_POST['pageId']) {
			global $wpdb;

			$original_id = $_POST['pageId'];

			if ($_POST['ab_page_type'] == 'copy' || $_POST['ab_page_type'] == 'new') {
				$post = get_post($original_id);
				$layer = '';

				$new_post = [
					'post_title' => 'AB TEST - ' . $post->post_title . ' ' . date('d-m-Y', current_time('timestamp')),
					'post_name' => $post->post_name,
					'post_status' => 'publish',
					'post_type' => 'page',
					'comment_status' => 'open',
					'post_content' => '',
					'post_excerpt' => $post->post_excerpt,
					'post_parent' => $original_id,
				];

				$post_id = wp_insert_post($new_post);

				if ($_POST['ab_page_type'] == 'copy') {
					$layer = MWDB()->getLayer($original_id, 'page');
					wp_update_post(['ID' => $post_id, 'post_content' => $post->post_content]);

					$post_meta = get_post_meta($original_id);
					foreach ($post_meta as $key => $val) {
						if ($key != '_edit_last' && $key != '_edit_lock') {
							add_post_meta($post_id, $key, @unserialize($val[0]));
						}
					}
				} elseif ($_POST['ab_page_type'] == 'new') {
					global $vePage;
					$vePage->builder->create_page_setting($post_id, $_POST['template']);
				}

				// save layer
				MWDB()->addLayer($post_id, $layer);
			} elseif ($_POST['ab_page_type'] == 'existing' && $_POST['ab_page_id']) {
				$post_id = $_POST['ab_page_id'];
			} else {
				$post_id = null;
			}

			if ($post_id) {
				$wpdb->update($wpdb->prefix . 'mw_funnel_pages', ['fp_ab_page' => $post_id], [
					'fp_page_id' => $original_id,
				]);

				wp_send_json([
					'ab_id' => $post_id,
					'ab_content' => $this->abTestSetting($original_id, $post_id),
				]);
				die();
			}

			wp_send_json([
				'ab_id' => '',
			]);
		}
		die();
	}
	function ajaxDeclareABTestWinner()
	{
		if ($_POST['original_id'] && $_POST['variant_id']) {
			global $wpdb;
			$delete_ab = false;
			if ($_POST['winner'] == 'original') {
				$delete_ab = true;
			} elseif ($_POST['winner'] == 'variant') {
				$layer = MWDB()->getLayer($_POST['variant_id']);
				MWDB()->setLayer($_POST['original_id'], 'page', $layer);
				MWDB()->updatePost(['ID' => $_POST['original_id'], 'post_content' => $layer]);
				$delete_ab = true;
			}

			if ($delete_ab) {
				$wpdb->update($wpdb->prefix . 'mw_funnel_pages', ['fp_ab_page' => null], [
					'fp_page_id' => $_POST['original_id'],
				]);
				wp_trash_post($_POST['variant_id']);
			}
		}
		die();
	}

	private function getPageStats($page_id, $ab_page_id, $values, ?\DateTimeImmutable $from)
	{
		$ab = core()->getAnalytics()->getStats()
			->filterByEvent('page', [$page_id])
			->filterByTag('ab_test_' . $page_id . '_' . $ab_page_id, $values)
			->unique();

		if ($from !== null) {
			$ab = $ab->from($from);
		}

		return $ab->fetchAll();
	}

	function abTestSetting($page_id, $ab_page_id)
	{
		$from = null;
		$funnelItem = $this->getFunnelItem($page_id);
		if ($funnelItem !== null) {
			$funnel = (bool) $funnelItem['funnel_id'] ? MWF()->getById($funnelItem['funnel_id']) : null;

			if ($funnel !== null) {
				$from = $funnel->statistics_reset_at ?? null;
			}
		}

		$ab_1 = $this->getPageStats($page_id, $ab_page_id, [$page_id], $from);
		$ab_2 = $this->getPageStats($page_id, $ab_page_id, [$ab_page_id], $from);

		$a_visits = isset($ab_1[0]) ? $ab_1[0]['count'] : 0;
		$a_conversions = $a_visits > 0 && isset($ab_1[0]) ? round($ab_1[0]['targetsCount'] / $a_visits * 100) . '%' : '0%';

		$b_visits = isset($ab_2[0]) ? $ab_2[0]['count'] : 0;
		$b_conversions = $b_visits > 0 && isset($ab_2[0]) ? round($ab_2[0]['targetsCount'] / $b_visits * 100) . '%' : '0%';

		$content = '<div class="mw_fps_ab_variant">'
			. '<div class="mw_fps_ab_variant_title">'
			. __('Původní stránka', 'mw_funnels')
			. '<a href="' . get_permalink($page_id) . '" target="_blank">' . __('Upravit stránku', 'mw_funnels') . '</a>'
			. '</div>'
			. '<div class="mw_fps_ab_variant_stats">'
			. mwAdminComponents::icon([
				'icon' => 'eye',
				'text' => $a_visits,
			])
			. mwAdminComponents::icon([
				'icon' => 'arrow-down',
				'text' => $a_conversions,
			])
			. '</div>'
			. mwAdminComponents::button([
				'attrs' => 'data-winner="original"',
				'button_text' => __('Zvolit vítězem', 'mw_funnels'),
			], 'mw_fps_declare_winner')
			. '<div class="mw_fps_ab_variant_label">A</div>'
			. '</div>';

		$content .= '<div class="mw_fps_ab_variant">'
			. '<div class="mw_fps_ab_variant_title">'
			. __('Varianta', 'mw_funnels')
			. '<a href="' . get_permalink($ab_page_id) . '" target="_blank">' . __('Upravit stránku', 'mw_funnels') . '</a>'
			. '</div>'
			. '<div class="mw_fps_ab_variant_stats">'
			. mwAdminComponents::icon([
				'icon' => 'eye',
				'text' => $b_visits,
			])
			. mwAdminComponents::icon([
				'icon' => 'arrow-down',
				'text' => $b_conversions,
			])
			. '</div>'
			. mwAdminComponents::button([
				'attrs' => 'data-winner="variant"',
				'button_text' => __('Zvolit vítězem', 'mw_funnels'),
			], 'mw_fps_declare_winner')
			. '<div class="mw_fps_ab_variant_label">B</div>'
			. '</div>';

		$content .= '<div class="mw_fps_ab_info">' . __('probíhá A/B test', 'mw_funnels') . '</div>';

		return $content;
	}

	function createDefaultPageSetting($type)
	{
		$setting = [
			'type' => $type,
		];
		foreach ($this->pageTypes[$type]['setting'] as $set) {
			if ($set['type'] == 'group') {
				foreach ($set['setting'] as $groupset) {
					if (isset($groupset['content'])) {
						$setting[$groupset['id']] = $groupset['content'];
					}
				}
			} elseif (isset($set['content'])) {
				$setting[$set['id']] = $set['content'];
			}
		}

		return $setting;
	}
	function loadPageTypes()
	{
		$this->pageTypes = [
			'source' => [
				'menu_title' => __('Zdroj návštěvnosti', 'mw_funnels'),
				'new_item_name' => '',
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'title',
								'title' => __('Název', 'mw_funnels'),
								'type' => 'text',
								'content' => __('Zdroj', 'mw_funnels'),
								'onedit' => [
									'action' => 'change_title',
								],
							],
							[
								'id' => 'icon',
								'title' => __('Ikona', 'mw_funnels'),
								'type' => 'iconselect',
								'content' => [
									'icon' => 'download',
									'icon_set' => 'feather',
								],
								'onedit' => [
									'action' => 'change_icon',
								],
							],
							[
								'id' => 'color',
								'title' => __('Barva', 'mw_funnels'),
								'type' => 'color',
								'content' => '',
								'onedit' => [
									'action' => 'change_icon_color',
								],
							],
						],
					],
					[
						'id' => 'utm_source',
						'title' => __('UTM source', 'mw_funnels'),
						'type' => 'text',
					],
					[
						'id' => 'utm_medium',
						'title' => __('UTM medium', 'mw_funnels'),
						'type' => 'text',
					],
					[
						'id' => 'utm_campaign',
						'title' => __('UTM campaign', 'mw_funnels'),
						'type' => 'text',
					],
					[
						'id' => 'utm_term',
						'title' => __('UTM term', 'mw_funnels'),
						'type' => 'text',
					],
					[
						'id' => 'utm_content',
						'title' => __('UTM content', 'mw_funnels'),
						'type' => 'text',
					],
				],
			],
			'squeeze' => [
				'menu_title' => __('Vstupní stránku', 'mw_funnels'),
				'setting' => [
					[
						'id' => 'title',
						'title' => __('Název', 'mw_funnels'),
						'type' => 'text',
						'content' => __('Vstupní stránka', 'mw_funnels'),
						'onedit' => [
							'action' => 'change_title',
						],
					],
					[
						'id' => 'page_id',
						'title' => __('Stránka', 'mw_funnels'),
						'type' => 'funnel_page',
					],
				],
			],
			'content' => [
				'menu_title' => __('Obsahovou stránku', 'mw_funnels'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [

							[
								'id' => 'title',
								'title' => __('Název', 'mw_funnels'),
								'type' => 'text',
								'content' => __('Obsahová stránka', 'mw_funnels'),
								'onedit' => [
									'action' => 'change_title',
								],
							],
							[
								'id' => 'page_id',
								'title' => __('Stránka', 'mw_funnels'),
								'type' => 'funnel_page',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'limited_access',
								'title' => __('Omezený přístup', 'mw_funnels'),
								'label' => __('Povolit přístup jen těm, kteří se registrovali na vstupní stránce cesty zákazníka.', 'mw_funnels'),
								'type' => 'switch',
								'show' => 'limited_access',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'publishtime',
								'title' => __('Datum zveřejnění', 'mw_funnels'),
								'type' => 'datetime',
							],
						],
						'show_group' => 'limited_access',
						'show_val' => '1',
					],
					[
						'id' => 'nav_hide',
						'title' => __('Nezobrazovat v navigaci', 'mw_funnels'),
						'label' => __('Nezobrazovat stránku v navigaci, vytvořené pomocí elementu <i>Navigace cesty zákazníka</i>.', 'mw_funnels'),
						'type' => 'switch',
						'show' => 'nav_setting',
						'content' => 0,
					],
					[
						'id' => 'nav_title',
						'title' => __('Název stránky v navigaci', 'mw_funnels'),
						'desc' => __('Pokud nic nezadáte, použije se název stránky.', 'mw_funnels'),
						'type' => 'text',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_title',
						'title' => __('Název v navigaci před zveřejněním', 'mw_funnels'),
						'type' => 'text',
						'desc' => __('Pokud nic nezadáte, použije se název stránky.', 'mw_funnels'),
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_image',
						'title' => __('Obrázek náhledu', 'mw_funnels'),
						'type' => 'image',
						'desc' => __('Zobrazí se v navigaci, pokud v editaci webu zvolíte variantu navigace s obrázky.'),
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_image',
						'title' => __('Obrázek náhledu před zveřejněním', 'mw_funnels'),
						'type' => 'image',
						'desc' => __('Zobrazí se v navigaci před zveřejněním stránky, pokud v editaci webu zvolíte variantu navigace s obrázky.', 'mw_funnels'),
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
				],
			],
			'sale' => [
				'menu_title' => __('Prodejní stránku', 'mw_funnels'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [

							[
								'id' => 'title',
								'title' => __('Název', 'mw_funnels'),
								'type' => 'text',
								'content' => __('Prodejní stránka', 'mw_funnels'),
								'onedit' => [
									'action' => 'change_title',
								],
							],
							[
								'id' => 'page_id',
								'title' => __('Stránka', 'mw_funnels'),
								'type' => 'funnel_page',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'limited_access',
								'title' => __('Omezený přístup', 'mw_funnels'),
								'label' => __('Povolit přístup jen těm, kteří se registrovali na vstupní stránce cesty zákazníka.', 'mw_funnels'),
								'type' => 'switch',
								'show' => 'limited_access',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'publishtime',
								'title' => __('Datum zveřejnění', 'mw_funnels'),
								'type' => 'datetime',
							],
						],
						'show_group' => 'limited_access',
						'show_val' => '1',
					],
					[
						'id' => 'nav_hide',
						'title' => __('Nezobrazovat v navigaci', 'mw_funnels'),
						'label' => __('Nezobrazovat stránku v navigaci, vytvořené pomocí elementu <i>Navigace cesty zákazníka</i>.', 'mw_funnels'),
						'type' => 'switch',
						'show' => 'nav_setting',
						'content' => 1,
					],
					[
						'id' => 'nav_title',
						'title' => __('Název v navigaci', 'mw_funnels'),
						'type' => 'text',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_title',
						'title' => __('Název v navigaci před zveřejněním', 'mw_funnels'),
						'type' => 'text',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_image',
						'title' => __('Obrázek náhledu', 'mw_funnels'),
						'type' => 'image',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_image',
						'title' => __('Obrázek náhledu před zveřejněním', 'mw_funnels'),
						'type' => 'image',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
				],
			],
			'order' => [
				'menu_title' => __('Objednávku', 'mw_funnels'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [

							[
								'id' => 'title',
								'title' => __('Název', 'mw_funnels'),
								'type' => 'text',
								'content' => __('Objednávka', 'mw_funnels'),
								'onedit' => [
									'action' => 'change_title',
								],
							],
							[
								'id' => 'page_id',
								'title' => __('Stránka', 'mw_funnels'),
								'type' => 'funnel_page',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'limited_access',
								'title' => __('Omezený přístup', 'mw_funnels'),
								'label' => __('Povolit přístup jen těm, kteří se registrovali na vstupní stránce cesty zákazníka.', 'mw_funnels'),
								'type' => 'switch',
								'show' => 'limited_access',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'publishtime',
								'title' => __('Datum zveřejnění', 'mw_funnels'),
								'type' => 'datetime',
							],
						],
						'show_group' => 'limited_access',
						'show_val' => '1',
					],
					[
						'id' => 'nav_hide',
						'title' => __('Nezobrazovat v navigaci', 'mw_funnels'),
						'label' => __('Nezobrazovat stránku v navigaci, vytvořené pomocí elementu <i>Navigace cesty zákazníka</i>.', 'mw_funnels'),
						'type' => 'switch',
						'show' => 'nav_setting',
						'content' => 1,
					],
					[
						'id' => 'nav_title',
						'title' => __('Název v navigaci', 'mw_funnels'),
						'type' => 'text',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_title',
						'title' => __('Název v navigaci před zveřejněním', 'mw_funnels'),
						'type' => 'text',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_image',
						'title' => __('Obrázek náhledu', 'mw_funnels'),
						'type' => 'image',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_image',
						'title' => __('Obrázek náhledu před zveřejněním', 'mw_funnels'),
						'type' => 'image',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
				],
			],
			'upsell' => [
				'menu_title' => __('Upsell stránku', 'mw_funnels'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [

							[
								'id' => 'title',
								'title' => __('Název', 'mw_funnels'),
								'type' => 'text',
								'content' => __('Upsell', 'mw_funnels'),
								'onedit' => [
									'action' => 'change_title',
								],
							],
							[
								'id' => 'page_id',
								'title' => __('Stránka', 'mw_funnels'),
								'type' => 'funnel_page',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'limited_access',
								'title' => __('Omezený přístup', 'mw_funnels'),
								'label' => __('Povolit přístup jen těm, kteří se registrovali na vstupní stránce cesty zákazníka.', 'mw_funnels'),
								'type' => 'switch',
							],
						],
					],
					[
						'id' => 'nav_hide',
						'title' => __('Nezobrazovat v navigaci', 'mw_funnels'),
						'label' => __('Nezobrazovat stránku v navigaci, vytvořené pomocí elementu <i>Navigace cesty zákazníka</i>.', 'mw_funnels'),
						'type' => 'switch',
						'show' => 'nav_setting',
						'content' => 1,
					],
					[
						'id' => 'nav_title',
						'title' => __('Název v navigaci', 'mw_funnels'),
						'type' => 'text',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_title',
						'title' => __('Název v navigaci před zveřejněním', 'mw_funnels'),
						'type' => 'text',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_image',
						'title' => __('Obrázek náhledu', 'mw_funnels'),
						'type' => 'image',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_image',
						'title' => __('Obrázek náhledu před zveřejněním', 'mw_funnels'),
						'type' => 'image',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
				],
			],
			'thanks' => [
				'menu_title' => __('Děkovací stránku', 'mw_funnels'),
				'new_item_name' => __('Děkovací', 'mw_funnels'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [

							[
								'id' => 'title',
								'title' => __('Název', 'mw_funnels'),
								'type' => 'text',
								'content' => __('Děkovací stránka', 'mw_funnels'),
								'onedit' => [
									'action' => 'change_title',
								],
							],
							[
								'id' => 'page_id',
								'title' => __('Stránka', 'mw_funnels'),
								'type' => 'funnel_page',
							],
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'limited_access',
								'title' => __('Omezený přístup', 'mw_funnels'),
								'label' => __('Povolit přístup jen těm, kteří se registrovali na vstupní stránce cesty zákazníka.', 'mw_funnels'),
								'type' => 'switch',
							],
						],
					],
					[
						'id' => 'nav_hide',
						'title' => __('Nezobrazovat v navigaci', 'mw_funnels'),
						'label' => __('Nezobrazovat stránku v navigaci, vytvořené pomocí elementu <i>Navigace cesty zákazníka</i>.', 'mw_funnels'),
						'type' => 'switch',
						'show' => 'nav_setting',
						'content' => 1,
					],
					[
						'id' => 'nav_title',
						'title' => __('Název v navigaci', 'mw_funnels'),
						'type' => 'text',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_title',
						'title' => __('Název v navigaci před zveřejněním', 'mw_funnels'),
						'type' => 'text',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_image',
						'title' => __('Obrázek náhledu', 'mw_funnels'),
						'type' => 'image',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
					[
						'id' => 'nav_ba_image',
						'title' => __('Obrázek náhledu před zveřejněním', 'mw_funnels'),
						'type' => 'image',
						'show_group' => 'nav_setting',
						'show_val' => '0',
					],
				],
			],
		];
	}

	function hookAddList()
	{
		echo '<li><a class="mw_open_funnel_installator" target="_blank" href="#">' . __('Cestu zákazníka', 'mw_funnels') . '</a></li>';
	}

	function loadInstallator()
	{
		MWInstallator()->addTemplates('funnel', [
			'get_contacts' => get_template_directory() . '/modules/funnels/funnel_templates/get_contacts/',
			'product_campaign' => get_template_directory() . '/modules/funnels/funnel_templates/product_campaign/',
			'webinar_sale' => get_template_directory() . '/modules/funnels/funnel_templates/webinar_sale/',
			'classic_campaign' => get_template_directory() . '/modules/funnels/funnel_templates/classic_campaign/',
		]);

		MWInstallator()->addInstallSteps('funnel', [
			'title' => __('Přidat cestu zákazníka', 'mw_funnels'),
			'steps' => [
				[
					'id' => 'select_type',
					'title' => __('Vyberte typ cesty zákazníka', 'mw_funnels'),
					'type' => 'select_type',
					'templates' => 'funnel',
					'custom_option' => true,
					'custom_option_text' => __('vytvořit vlastní cestu zákazníka', 'mw_funnels'),
					/*
					'content' => [
						[
							'title' => __('Získávání kontaktů','mw_funnels'),
							'desc' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit.','mw_funnels'),
							'value' => 'get_contacts',
							'icon' => 'mail',
						],
						[
							'title' => __('Produktová kampaň','mw_funnels'),
							'desc' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit.','mw_funnels'),
							'value' => 'product_campaign',
							'icon' => 'dollar-sign',
						],
						[
							'title' => __('Webinářový prodej','mw_funnels'),
							'desc' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit.','mw_funnels'),
							'value' => 'webinar_sale',
							'icon' => 'mic',
						],
					]*/
				],
				[
					'id' => 'title',
					'title' => __('Zadejte název cesty zákazníka', 'mw_funnels'),
					'type' => 'name',
					'content' => [
						'button_text' => __('Vytvořit cestu zákazníka', 'mw_funnels'),
						'input_placeholder' => __('Název cesty zákazníka', 'mw_funnels'),
						'desc' => __('Název můžete kdykoli změnit.', 'mw_funnels'),
					],
				],
			],
		]);

		MWInstallator()->addInstallSteps('abtest', [
			'title' => __('Vytvořit A/B test', 'mw_funnels'),
			'hide_steps' => 1,
			'steps' => [
				[
					'id' => 'select_ab',
					'title' => __('Vytvořit stránku pro A/B test', 'mw_funnels'),
					'type' => 'select_ab_page',
				],
				[
					'id' => 'select_template',
					'title' => __('Vyber šablonu stránky', 'mw_funnels'),
					'type' => 'select_template',
				],
			],
		]);
	}

	function checkVersion()
	{
		$versions = get_option('cms_versions');

		if (!isset($versions['funnels'])) {
			$success = $this->installTables();
			if ($success) {
				$this->migrateCampaigns();
				$versions['funnels'] = FUNNELS_VERSION;
				update_option('cms_versions', $versions);
			} else {
				return;
			}
		} elseif (isset($versions['funnels']) && $versions['funnels'] != FUNNELS_VERSION) {
			global $wpdb;

			if (version_compare($versions['funnels'], '1.2.0', '<')) {
				$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'mw_funnels ADD `funnel_statistics_reset_at` datetime NULL;');
			}

			$versions['funnels'] = FUNNELS_VERSION;
			update_option('cms_versions', $versions);
		}
	}
	function activation($versions)
	{
		if (empty($versions) || !isset($versions['funnels'])) {
			$this->installTables();
		}
	}

	function installTables()
	{
		global $wpdb;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . 'mw_funnels';
		if ($wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") != $table_name) {
			$sql = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' ('
			  . 'funnel_id int(11) NOT NULL AUTO_INCREMENT,'
			  . 'funnel_title varchar(100) NOT NULL,'
			  . 'funnel_code varchar(10) NOT NULL,'
			  . 'funnel_evergreen tinyint(1) DEFAULT NULL,'
			  . 'funnel_redirect tinyint(1) DEFAULT NULL,'
			  . 'funnel_cookie_time int(11) DEFAULT NULL,'
			  . 'funnel_sale_platform varchar(250) DEFAULT NULL,'
			  . 'funnel_upsell varchar(250) DEFAULT NULL,'
			  . 'funnel_bump varchar(250) DEFAULT NULL,'
			  . 'funnel_currency varchar(10) DEFAULT NULL,'
			  . 'funnel_show_contacts tinyint(1) DEFAULT NULL,'
			  . 'funnel_show_sell tinyint(1) DEFAULT NULL,'
			  . 'funnel_statistics_reset_at datetime NULL,'
			  . "PRIMARY KEY (funnel_id)) $charset_collate;";

			dbDelta($sql);
		}

		$table_name = $wpdb->prefix . 'mw_funnel_pages';
		if ($wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") != $table_name) {
			$sql = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' ('
			  . 'fp_id bigint(20) NOT NULL AUTO_INCREMENT,'
			  . 'fp_page_id bigint(20) DEFAULT NULL,'
			  . 'fp_title varchar(100) NOT NULL,'
			  . 'fp_limited_access tinyint(1) DEFAULT NULL,'
			  . 'fp_funnel_id int(11) NOT NULL,'
			  . 'fp_type varchar(20) NOT NULL,'
			  . 'fp_utm_source varchar(250) DEFAULT NULL,'
			  . 'fp_utm_medium varchar(250) DEFAULT NULL,'
			  . 'fp_utm_campaign varchar(250) DEFAULT NULL,'
			  . 'fp_utm_term varchar(250) DEFAULT NULL,'
			  . 'fp_utm_content varchar(250) DEFAULT NULL,'
			  . 'fp_order int(11) DEFAULT NULL,'
			  . 'fp_icon text DEFAULT NULL,'
			  . 'fp_color varchar(20) DEFAULT NULL,'
			  . 'fp_ab_page bigint(20) DEFAULT NULL,'
			  . 'fp_nav_hide tinyint(1) DEFAULT NULL,'
			  . 'fp_publishtime varchar(250) DEFAULT NULL,'
			  . 'fp_nav_title varchar(250) DEFAULT NULL,'
			  . 'fp_nav_ba_title varchar(250) DEFAULT NULL,'
			  . 'fp_nav_image text DEFAULT NULL,'
			  . 'fp_nav_ba_image text DEFAULT NULL,'
			  . "PRIMARY KEY (fp_id)) $charset_collate;";

			dbDelta($sql);
		}

		return empty($wpdb->last_error);
	}

	private function migrateCampaigns()
	{
		$campaigns = get_option('campaign_basic');
		if ($campaigns) {
			global $wpdb;

			foreach ($campaigns['campaigns'] ?? [] as $campaign) {
				$wpdb->insert($wpdb->prefix . 'mw_funnels', [
					'funnel_title' => $campaign['name'] ?? '',
					'funnel_code' => $campaign['code'] ?? '',
					'funnel_evergreen' => $campaign['evergreen'] ?? 0,
					'funnel_redirect' => $campaign['noredirect'] ?? false ? 0 : 1,
					'funnel_cookie_time' => $campaign['duration'] ?? null,
				]);

				$funnelId = $wpdb->insert_id;

				$wpdb->insert($wpdb->prefix . 'mw_funnel_pages', [
					'fp_page_id' => $campaign['squeeze'],
					'fp_title' => 'Vstupní stránka',
					'fp_funnel_id' => $funnelId,
					'fp_limited_access' => 0,
					'fp_type' => 'squeeze',
					'fp_order' => 0,
					'fp_nav_hide' => 0,
					'fp_nav_title' => '',
					'fp_nav_ba_title' => '',
					'fp_nav_image' => null,
					'fp_nav_ba_image' => null,
				]);
				update_post_meta($campaign['squeeze'], 'mwf_funnel_id', $funnelId);

				global $vePage;
				foreach ($campaign['page'] ?? [] as $i => $page) {
					$wpdb->insert($wpdb->prefix . 'mw_funnel_pages', [
						'fp_page_id' => $page['page'],
						'fp_title' => 'Obsahová stránka ' . ($i + 1),
						'fp_funnel_id' => $funnelId,
						'fp_limited_access' => 1,
						'fp_type' => 'content',
						'fp_order' => $i + 1,
						'fp_publishtime' => isset($page['publishdate']) ? serialize($page['publishdate']) : null,
						'fp_nav_hide' => $page['exclude'] ?? 0,
						'fp_nav_title' => $page['name'] ?? '',
						'fp_nav_ba_title' => $page['csname'] ?? '',
						'fp_nav_image' => $page['thumb'] ? serialize(['image' => $page['thumb']]) : null,
						'fp_nav_ba_image' => $page['csthumb'] ? serialize(['image' => $page['csthumb']]) : null,
					]);
					if ($page['page']) {
						update_post_meta($page['page'], 'mwf_funnel_id', $funnelId);

						$l = MWDB()->getLayer($page['page']);

						$layer = visualEditor::decode($l);
						$replaced_layer = $vePage->replaceElements([
							'mioweb_nav' => 'funnel_nav',
							'campaign_date' => 'funnel_date',
						], $layer);
						$newlayer = visualEditor::code($replaced_layer);

						MWDB()->setLayer($page['page'], 'page', $newlayer);
						MWDB()->updatePost(['ID' => $page['page'], 'post_content' => $newlayer]);
					}
				}
			}
			// @TODO unset campaign_basic option?
		}
	}

	/** @return mwFunnels Returns singleton instance of MioShop. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

}
