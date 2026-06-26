<?php

function MwCampaigns()
{
   return MwCampaignsModule::instance();
}

class MwCampaignsModule
{

	protected static $_instance = null;

	public $edit_mode;

	public $campaigns;

	public $first_campaign;

	public $script_version;

	public $js_texts;

	public $is_campaign_page = false;

	public $current_campaign;

	private bool $builder_mode;

	function __construct()
	{
		$this->edit_mode = current_user_can('edit_pages') ? true : false;
		$this->builder_mode = $this->edit_mode && !isset($_GET['mw_preview']);

		$this->script_version = filemtime(get_template_directory() . '/style.css');

		$js_texts = require_once(__DIR__ . '/js/js_texts.php');
		$this->js_texts = $js_texts;

		// get all campaigns
		$this->campaigns = get_option('campaign_basic');

		if ($this->edit_mode) {
			//ajax
			add_action('wp_ajax_add_campaign_page', [$this, 'add_campaign_page']);

			add_action('wp', [$this, 'init']);

			//get first campaign for menu
			if (!empty($this->campaigns)) {
				$this->first_campaign = reset($this->campaigns['campaigns']);
			}

			add_filter('mw_fast_nav_current', [$this, 'fast_nav_current']);
		}
		if (!$this->builder_mode) {
			add_action('cms_after_facebook_meta', [$this, 'check_cookies']);
			add_action('wp_enqueue_scripts', [$this, 'load_front_scripts']);
		} else {
			add_action('wp_enqueue_scripts', [$this, 'load_admin_scripts']);
		}

		if (isset($_GET['setuser'])) {
			add_action('wp', [$this, 'set_cookies']);
		}
		if (isset($_GET['clear_cookie'])) {
			add_action('wp', [$this, 'clear_mioweb_access_cookie']);
		}
	}

	function init()
	{
		global $vePage, $post;
		if (isset($post->ID)) {
			$campaign_id = get_post_meta($post->ID, 'mioweb_campaign', true);
			if (isset($campaign_id['campaign']) && $campaign_id['campaign'] !== '') {
				$vePage->modul_type = 'campaign';
				$this->is_campaign_page = true;
				$this->current_campaign = $this->campaigns['campaigns'][$campaign_id['campaign']];
			}
		}
	}

	function load_admin_scripts()
	{
		wp_enqueue_script('mioweb_admin_script', MIOWEB_DIR . 'js/admin.js', ['jquery'], $this->script_version);
		wp_enqueue_style('mioweb_admin_css', MIOWEB_DIR . 'css/admin.css', [], $this->script_version);

		wp_localize_script('mioweb_admin_script', 'campaign_texts', $this->js_texts['admin']);
	}

	function load_front_scripts()
	{
		wp_enqueue_style('mioweb_content_css', MIOWEB_DIR . 'css/content.css', [], $this->script_version);
	}

	// Add campaign page
	function add_campaign_page()
	{
		$this->campaign_page($_POST['tagid'] . '_' . $_POST['id'], $_POST['tagname'] . '[page][' . $_POST['id'] . ']', ['page' => ''], ($_POST['id'] + 1) . '. ' . __('Stránka s obsahem zdarma', 'cms_mioweb'));
		die();
	}

	public static function campaign_page($id, $name, $content, $title, $delete = true, $campaign = [])
	{
		?>
		<label class="campaign_set_box_label" for="<?php echo $id; ?>"><?php echo $title; ?></label>
		<div class="campaign_set_box_content">
			<div class="mw_flex_field">
				<?php
				echo mwAdminComponents::selectPage([
				   'name' => $name . '[page]',
				   'tag_id' => $id,
				   'add_button' => true,
				   'whisperer' => true,
				], $content['page'], 'campaing_select_page');

				echo mwAdminComponents::iconLink([
					'icon' => 'settings',
				], 'mw_icon_button mioweb_setting_campaign_page');
				?>
			</div>
			<?php
			if (isset($content['page']) && $content['page']) {
				echo '<div class="campaign_set_page_url">';
				echo get_permalink($content['page']);
				if (get_option('permalink_structure')) {
					echo '?';
				} else {
					echo '&';
				}
				echo 'setuser=' . $campaign['code'];
				echo '</div>';
			}
			?>
		</div>

		<div class="campaign_page_set">
			<div class="set_form_subrow">
		<?php
		$val = isset($content['exclude']) ? '1' : '';
		cms_generate_field_switch($name . '[exclude]', $id . '_exclude', $val, ['label' => __('Nevypisovat v menu kampaně', 'cms_mioweb')]);
		?>
			</div>
			<div class="set_form_subrow">
				<div class="label"><?php echo __('Datum zveřejnění', 'cms_mioweb'); ?></div>
				<?php
				echo mwAdminComponents::dateTimeInput([
					'name' => $name . '[publishdate]',
				], $content['publishdate'] ?? []);
				?>
			</div>
			<div class="set_form_subrow">
				<div class="label"><?php echo __('Název stránky v menu', 'cms_mioweb'); ?></div>
				<?php
				echo mwAdminComponents::input([
					'name' => $name . '[name]',
				], isset($content['name']) ? stripslashes($content['name']) : '');
				?>
			</div>
			<div class="set_form_subrow">
				<div class="label"><?php echo __('Název před zveřejněním', 'cms_mioweb'); ?></div>
				<?php
				echo mwAdminComponents::input([
					'name' => $name . '[csname]',
				], isset($content['csname']) ? stripslashes($content['csname']) : '');
				?>
			</div>
			<div class="set_form_subrow campaign_page_set_image_container">
				<div class="label"><?php echo __('Náhledový obrázek v menu', 'cms_mioweb'); ?></div>
		<?php echo cms_generate_field_image_url($name . '[thumb]', $id . '_thumb', isset($content['thumb']) ? stripslashes($content['thumb']) : null, []); ?>
				<div class="cms_clear"></div>
			</div>
			<div class="set_form_subrow campaign_page_set_image_container">
				<div class="label"><?php echo __('Náhledový obrázek před zveřejněním', 'cms_mioweb'); ?></div>
		<?php echo cms_generate_field_image_url($name . '[csthumb]', $id . '_csthumb', isset($content['csthumb']) ? stripslashes($content['csthumb']) : null, []); ?>
				<div class="cms_clear"></div>
			</div>
			<div class="cms_clear"></div>
		</div>
		<?php
		if ($delete) {
			echo '<a class="mioweb_delete_campaign_page" href="#" title="' . __('Odstranit', 'cms_mioweb') . '">' . mw_icon('icon-x') . '</a>';
		} ?>
		<div class="campaign_set_box_arrow"><?php echo mw_icon('icon-chevron-down'); ?></div>
		<?php
	}

	// Menu

	function create_mioweb_fast_submenu()
	{
		$menu = '';
		if (isset($this->campaigns['campaigns'])) {
			$count = count($this->campaigns['campaigns']);
			if ($count > 0) {
				$menu .= '<ul>';
				foreach ($this->campaigns['campaigns'] as $camp) {
					$menu .= '<li><a href="' . get_permalink($camp['squeeze']) . '">' . $camp['name'] . '</a></li>';
				}
				$menu .= '</ul>';
			}
		}

		return $menu;
	}

	function fast_nav_current($current)
	{
		if ($this->is_campaign_page) {
			$current['title'] = __('Kampaň', 'cms_mioweb');
			$current['url'] = get_permalink($this->current_campaign['squeeze']);
		}

		return $current;
	}

	// Cookies

	function check_cookies()
	{
		global $post;
		if (!current_user_can('administrator') && isset($post->ID)) {
			$campaign_id = get_post_meta($post->ID, 'mioweb_campaign', true);

			if (isset($campaign_id['campaign'])) {
				$redirect = true;
				$sq_redirect = false;

				if (isset($_COOKIE['mioweb_campaign_access'])) {
					$access = unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
					if (is_array($access)) {
						$campaigns = get_option('campaign_basic');
						foreach ($access as $id => $c_access) {
							if ($id === $campaign_id['campaign']) {
								if (isset($campaigns['campaigns'][$campaign_id['campaign']]['evergreen'])) {
									if ($access[$id] == 'all' || in_array($post->ID, explode(',', $access[$id]))) {
										$redirect = false;
									}
								} else {
									foreach ($campaigns['campaigns'][$campaign_id['campaign']]['page'] as $page) {
										if ($page['page'] == $post->ID && current_time('timestamp') > strtotime($page['publishdate']['date'] . ' ' . $page['publishdate']['hour'] . ':' . $page['publishdate']['minute'] . ':0')) {
											$redirect = false;
										}
									}
								}
								$sq_redirect = true;
							}
						}
					}
				}
				if ($redirect && $campaign_id['type'] == 'page') {
					$campaigns = get_option('campaign_basic');
					$url = $campaigns['campaigns'][$campaign_id['campaign']]['squeeze'] ? get_permalink($campaigns['campaigns'][$campaign_id['campaign']]['squeeze']) . $this->makeatt($_GET) : get_home_url() . $this->makeatt($_GET);
				} elseif ($sq_redirect && $campaign_id['type'] == 'squeeze' && (in_array($campaigns['campaigns'][$campaign_id['campaign']]['page'][0]['page'], explode(',', $access[$campaign_id['campaign']])) || $access[$campaign_id['campaign']] == 'all')) {
					$campaigns = get_option('campaign_basic');
					$url = !isset($campaigns['campaigns'][$campaign_id['campaign']]['noredirect']) ? get_permalink($campaigns['campaigns'][$campaign_id['campaign']]['page'][0]['page']) . $this->makeatt($_GET) : null;
				} else {
					$url = null;
				}
				if ($url !== null) {
					if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
						echo $this->format_post_redirect_script($url, $_POST);
					} else {
						echo $this->format_get_redirect_script($url);
					}
					die();
				}
			}
		}
	}

	function format_get_redirect_script($url)
	{
		$html = '<script type="text/javascript">' . "\n";
		$html .= 'window.location.href = ' . json_encode($url) . ';' . "\n";
		$html .= '</script>' . "\n";

		return $html;
	}

	function format_post_redirect_script($url, $postData)
	{
		$html = '<form action="' . htmlspecialchars($url) . '" method="POST" name="frm">' . "\n";
		foreach ($postData as $key => $value) {
			$html .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars((string) $value) . '" />' . "\n";
		}
		$html .= '</form>' . "\n";
		$html .= '<script type="text/javascript">' . "\n";
		$html .= 'document.frm.submit();' . "\n";
		$html .= '</script>' . "\n";

		return $html;
	}

	function clear_mioweb_access_cookie()
	{
		unset($_COOKIE['mioweb_campaign_access']);
		setcookie('mioweb_campaign_access', '', current_time('timestamp') - 3600, '/');
	}

	function set_cookies()
	{
		global $post;
		$campaign_id = get_post_meta($post->ID, 'mioweb_campaign', true);
		if (isset($campaign_id['campaign']) && $campaign_id['type'] == 'page') {
			$campaigns = get_option('campaign_basic');
			if ($_GET['setuser'] == $campaigns['campaigns'][$campaign_id['campaign']]['code']) {
				// generate list of pages for evergreen
				$pages = [];

				if (isset($campaigns['campaigns'][$campaign_id['campaign']]['evergreen'])) {
					foreach ($campaigns['campaigns'][$campaign_id['campaign']]['page'] as $c_page) {
						$pages[] = $c_page['page'];
						if ($c_page['page'] == $post->ID) {
							break;
						}
					}
				}

				$access = isset($_COOKIE['mioweb_campaign_access']) ? unserialize(stripslashes($_COOKIE['mioweb_campaign_access'])) : [];

				// if evergreen save smaller page, if no evergreen set access to all pages
				$access[$campaign_id['campaign']] = isset($campaigns['campaigns'][$campaign_id['campaign']]['evergreen']) ? implode(',', $pages) : 'all';
				if (!isset($access['time'][$campaign_id['campaign']]) || isset($_GET['reset_time'])) {
					$access['time'][$campaign_id['campaign']] = current_time('timestamp');
				}

				$days = isset($campaigns['campaigns'][$campaign_id['campaign']]['duration']) && $campaigns['campaigns'][$campaign_id['campaign']]['duration'] !== '' ? (int) $campaigns['campaigns'][$campaign_id['campaign']]['duration'] : 365;
				setcookie('mioweb_campaign_access', serialize($access), current_time('timestamp') + (60 * 60 * 24 * $days), '/');

				$url = get_permalink($post->id) . $this->makeatt($_GET);
				if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
					echo $this->format_post_redirect_script($url, $_POST);
					die();
				}

				header('Location: ' . $url);
			}
		}
	}

	function makeatt($q)
	{
		$att = '?';
		$return = false;
		if (is_array($q)) {
			foreach ($q as $k => $v) {
				if ($k != 'setuser' && $k != 'Errors' && $k != 'clear_cookie' && $k != 'p' && $k != 'page_id') {
					$att .= $k . '=' . urlencode($v) . '&';
					$return = true;
				}
			}
		}

		return $return ? $att : '';
	}

	/** @return MwCampaignModule Returns singleton instance of member module. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}


}

?>
