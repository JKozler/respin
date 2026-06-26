<?php

use Mioweb\Config\Config;
use Mioweb\VisualEditor\Lib\Link;
use Nette\Http\UrlScript;
use Nette\Utils\Validators;
use Mioweb\Lib\License;

function MW(): mioweb
{
   return mioweb::instance();
}

class mioweb
{

	protected static $_instance = null;

	public $p_templates = [];

	public $shortcodes = [];

	public $fonts = [];

	public $google_fonts = [];

	public $file_fonts = [];

	public $sidebars;

	public $modules;

	public $versions;

	public array $installed_versions;

	private ?License $license = null;

	public $hosting;

	public $is_mobile;

	public $container = [];

	public $script_version;

	public $edit_mode;

	public $builder_mode;

	public $installed_web;

	public $devices = [
		'desktop' => [
			'resolution' => '',
			'icon' => 'd',
		],
		'tablet' => [
			'resolution' => '768',
			'icon' => 't',
		],
		'mobile' => [
			'resolution' => '640',
			'icon' => 'm',
		],
	];

	/** @var Config */
	private $config;

	function __construct()
	{
		$this->config = mwConfig();
		$this->script_version = filemtime(get_template_directory() . '/style.css');

		$this->edit_mode = current_user_can('edit_pages') ? true : false;
		$this->builder_mode = $this->edit_mode && !isset($_GET['mw_preview']) ? true : false;

		$installedVersionsRaw = get_option('cms_versions');
		$this->installed_versions = is_array($installedVersionsRaw) ? $installedVersionsRaw : [];
		$this->installed_web = get_option('ve_installed_web');

		$this->license = new License($this->edit_mode, $this->installed_versions);

		// what happend when is hosting
		if ($this->edit_mode) {
			$this->setMwPermission();
			add_filter('site_status_tests', [$this, 'edit_site_status_tests']);
		}
		remove_action('wp_head', 'rel_canonical');
		remove_action('wp_head', 'index_rel_link');
		remove_action('wp_head', 'start_post_rel_link');
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'wp_generator');

		add_action('phpmailer_init', [$this, 'cms_init_smtp']);

		add_action('wp_headers', [$this, 'mw_send_headers']);

		add_action('template_redirect', [$this, 'cms_redirect_page']);

		//widgets
		add_action('widgets_admin_page', [$this, 'widgets_content']);
		add_action('widgets_init', [$this, 'register_sidebars']);
		if (isset($_POST['widget_action'])) {
			add_action('init', [$this, 'widget_actions'], 1);
		}

		//widgets-ajax
		add_action('wp_ajax_cms_delete_sidebar', [$this, 'delete_sidebar']);

		//smtp test
		add_action('wp_ajax_mw_smtp_test_email', [$this, 'send_test_email']);

		// theme activation
		add_action('after_switch_theme', [$this, 'cms_activation']);

		// turn off auto update emails
		add_filter('auto_core_update_send_email', [$this, 'mw_stop_auto_update_emails'], 10, 4);

		// last login
		add_action('wp_login', [$this, 'saveUserLastLogin'], 10, 2);

		//multielement ajax
		add_action('wp_ajax_cms_generate_multielement', [$this, 'generate_multielement_ajax']);

		//Image meta update
		if ($this->edit_mode) {
			add_action('wp_ajax_mio_image_gallery_edit_meta', [$this, 'update_image_meta']);

			// Disables the block editor from managing widgets in the Gutenberg plugin.
			add_filter('gutenberg_use_widgets_block_editor', '__return_false');
			// Disables the block editor from managing widgets.
			add_filter('use_widgets_block_editor', '__return_false');
		}

		// Disable xmlrpc pingback
		add_filter('xmlrpc_methods', [$this, 'disable_xmlrpc_pingback']);

		// Disable sending e-mail if no AntiSpam plugin is installed
		add_filter('comment_notification_recipients', [$this, 'comment_notification_recipients']);
		add_filter('comment_moderation_recipients', [$this, 'comment_notification_recipients']);

		// add mime types
		add_filter('upload_mimes', [$this, 'add_mime_types']);
		add_filter('wp_check_filetype_and_ext', [$this, 'font_filetypes'], 10, 5);

		$this->add_version('cms', CMS_VERSION);

		$this->is_mobile = wp_is_mobile();

		$this->add_custom_fonts();
	}

	public function scriptVersion(): string
	{
		return $this->script_version;
	}

	public function getLicense(): ?License
	{
		return $this->license;
	}

	// Disable customizer, hide items in menu, enable/disable installing themes, ....
	function setMwPermission()
	{
		add_action('admin_init', [$this, 'setPermissions'], 999);
		add_filter('map_meta_cap', [$this, 'filter_to_remove_customize_capability'], 10, 4);
	}

	function setPermissions()
	{
		//remove_submenu_page('themes.php', 'theme-editor.php');
		//remove_submenu_page('themes.php', 'themes.php');

		$administrator_role = get_role('administrator');
		if ($this->license->isHosting()) {
			$administrator_role->remove_cap('install_themes');
		} else {
			$administrator_role->add_cap('install_themes');
		}
		//$administrator_role->remove_cap( 'switch_themes' );

		// Drop some customizer actions
		remove_action('plugins_loaded', '_wp_customize_include', 10);
		remove_action('admin_enqueue_scripts', '_wp_customize_loader_settings', 11);

		// Manually overrid Customizer behaviors
		add_action('load-customize.php', [$this, 'override_load_customizer_action']);
	}

	function override_load_customizer_action()
	{
		// If accessed directly
		wp_die('The Customizer is currently disabled.');
	}

	function filter_to_remove_customize_capability($caps = [], $cap = '', $user_id = 0, $args = [])
	{
		if ($cap == 'customize') {
			return ['nope']; // thanks @ScreenfeedFr, http://bit.ly/1KbIdPg
		}

		return $caps;
	}

	function cms_init_smtp(\PHPMailer\PHPMailer\PHPMailer $phpmailer)
	{
		$smtp = get_option('web_option_smtp');
		if (!$smtp || !is_email($smtp['smtp_email']) || empty($smtp['smtp_host'])) {
			return;
		}
		if (isset($smtp['use_smtp'])) {
			$phpmailer->Mailer = 'smtp';
			$phpmailer->From = $smtp['smtp_email'];
			$phpmailer->FromName = $smtp['smtp_name'];
			$phpmailer->Sender = $phpmailer->From;
			$phpmailer->AddReplyTo($phpmailer->From, $phpmailer->FromName);
			$phpmailer->Host = $smtp['smtp_host'];
			$phpmailer->SMTPSecure = $smtp['smtp_secure'];
			$phpmailer->Port = $smtp['smtp_port'];
			$phpmailer->SMTPAuth = $smtp['smtp_authentication'] == 'yes' ? true : false;
			if ($phpmailer->SMTPAuth) {
				$phpmailer->Username = $smtp['smtp_login'];
				$phpmailer->Password = $smtp['smtp_password'];
			}
		}
	}

	function send_test_email($args)
	{
		$smtp = get_option('web_option_smtp');
		if (!$smtp) {
			mwMessages()->error(__('SMTP není nastaveno. Před odesláním testovacího e-mailu nezapomeňte nastavení uložit.', 'cms'));
		} elseif (empty($smtp['smtp_host'])) {
			mwMessages()->error(__('SMTP host není vyplněn. Před odesláním testovacího e-mailu nezapomeňte nastavení uložit.', 'cms'));
		} elseif (!(bool) $smtp['smtp_email']) {
			mwMessages()->error(__('E-mailová adresa pro odesílání e-mailů není vyplněna. Před odesláním testovacího e-mailu nezapomeňte nastavení uložit.', 'cms'));
		} elseif (!is_email($smtp['smtp_email'])) {
			mwMessages()->error(__('Nesprávně nastavená adresa pro odesílání e-mailů. Před odesláním testovacího e-mailu nezapomeňte nastavení uložit.', 'cms'));
		} elseif (!is_email($_POST['email'])) {
			mwMessages()->error(__('Vyplněná adresa příjemce není platná.', 'cms'));
		} elseif ($_POST['email']) {
			$senderEmail = $smtp['smtp_email'];
			$headers = [];
			$headers[] = 'From: ' . $senderEmail . ' <' . $senderEmail . '>';
			$headers[] = 'Reply-To: ' . $senderEmail . ' <' . $senderEmail . '>';
			$message = __('Toto je testovací email, zaslaný pro ověření funkčnosti nastavení SMTP. Pokud email dorazí, tak máte vše nastaveno v pořádku.', 'cms');

			if (wp_mail($_POST['email'], __('Test nastavení SMTP', 'cms') . ' ' . get_bloginfo('name'), $message, $headers)) {
				mwMessages()->success(__('Zpráva byla úspěšně odeslána.', 'mwshop'));
			} else {
				global $phpmailer;

				$mail_error = ' (Nefunkční funkce mail.)';

				if (isset($phpmailer)) {
					$mail_error = ' (' . $phpmailer->ErrorInfo . ')';
				}

				mwMessages()->error(__('Zpráva se nepodařila odeslat.', 'cms') . $mail_error);
			}
		} else {
			mwMessages()->error(__('Není vyplněna adresa na kterou se má testovací zpráva odeslat.', 'cms'));
		}

		wp_send_json([
			'success' => mwMessages()->success,
			'errors' => mwMessages()->errors,
			'html' => mwMessages()->writeHtml(),
		]);

		die();
	}

	public function is_smtp_enabled(): bool
	{
		$smtp = get_option('web_option_smtp');

		return isset($smtp['use_smtp'], $smtp['smtp_host'], $smtp['smtp_email']) && is_email($smtp['smtp_email']) && (bool) $smtp['smtp_host'] && (bool) $smtp['use_smtp'];
	}

	function is_module_active(string $module): bool
	{
		return $this->license->hasModule($module);
	}

	// language
	function load_theme_lang($domain, $path)
	{
		load_theme_textdomain($domain, $path);
		$locale = get_locale();
		$options['lang'] = $locale;
		$locale_file = $path . "/$locale.php";
		if (is_readable($locale_file)) {
			require_once($locale_file);
		}
	}

	function add_shortcode($args)
	{
		$this->shortcodes[$args['id']] = $args;
	}

	function add_fonts($args)
	{
		$this->fonts = array_merge($this->fonts, $args);
	}

	function add_google_fonts($args)
	{
		$this->google_fonts = array_merge($this->google_fonts, $args);
	}

	function add_custom_fonts()
	{
		$fonts_s = get_option('mw_custom_fonts');
		if (isset($fonts_s['fonts']) && count($fonts_s['fonts'])) {
			foreach ($fonts_s['fonts'] as $font) {
				if (isset($font['type']) && $font['type'] === 'file') {
					$font_title = $font['title'] ?? '';

					if ($font_title && isset($font['font_weights'])) {
						$weights = [];
						foreach ($font['font_weights'] as $font_weight) {
							if (isset($font_weight['font_weight']) && $font_weight['font_weight'] && isset($font_weight['font_weight_file']) && $font_weight['font_weight_file']) {
								$t = '';
								if ($font_weight['font_weight'] === '100') {
									$t = 'Thin';
								} elseif ($font_weight['font_weight'] === '200') {
									$t = 'Extra-light';
								} elseif ($font_weight['font_weight'] === '300') {
									$t = 'Light';
								} elseif ($font_weight['font_weight'] === '400') {
									$t = 'Normal';
								} elseif ($font_weight['font_weight'] === '500') {
									$t = 'Medium';
								} elseif ($font_weight['font_weight'] === '600') {
									$t = 'Semi-bold';
								} elseif ($font_weight['font_weight'] === '700') {
									$t = 'Bold';
								} elseif ($font_weight['font_weight'] === '800') {
									$t = 'Extra-bold';
								} elseif ($font_weight['font_weight'] === '900') {
									$t = 'Black';
								}

								if ($t) {
									$file = $font_weight['font_weight_file'];
									if (Validators::isUrl($file)) {
										$url = new UrlScript($file);
										$file = $url->getPath();
									}
									$weights[$font_weight['font_weight']]['name'] = $t;
									$weights[$font_weight['font_weight']]['file'] = $file;
								}
							}
						}
						$this->file_fonts[$font_title] = $weights;
					}
				} else {
					//type is google, font type checking omitted for backwards compability
					preg_match('/link href=["\']?([^"\'>]+)["\']?/', stripslashes($font['font_code']), $link);
					if (isset($link[1])) {
						$url = parse_url(str_replace('"', '', $link[1]));

						$gf = explode('&', str_replace('family=', '', $url['query']));
						$fonts = explode('|', $gf[0]);
						foreach ($fonts as $f) {
							$font_parts = explode(':', $f);
							$font_title = str_replace('+', ' ', $font_parts[0]);
							if (isset($font_parts[1])) {
								if (!str_contains($font_parts[1], 'wght@')) {
									$font_weights = explode(',', $font_parts[1]);
								} else {
									$fw = str_replace(['wght@', 'ital,', '0,', '1,'], '', $font_parts[1]);
									$font_weights = explode(';', $fw);
								}
							}

							$weights = [];
							if (isset($font_weights) && count($font_weights)) {
								foreach ($font_weights as $w) {
									$t = 'Normal';
									if ($w === '100') {
										$t = 'Thin';
									} elseif ($w === '200') {
										$t = 'Extra-light';
									} elseif ($w === '300') {
										$t = 'Light';
									} elseif ($w === '500') {
										$t = 'Medium';
									} elseif ($w === '600') {
										$t = 'Semi-bold';
									} elseif ($w === '700') {
										$t = 'Bold';
									} elseif ($w === '800') {
										$t = 'Extra-bold';
									} elseif ($w === '900') {
										$t = 'Black';
									}

									$weights[$w] = $t;
								}
							} else {
								$weights[400] = 'Normal';
							}

							$this->google_fonts[$font_title] = [
									'weights' => $weights,
							'custom_font' => 1,
							];
						}
					}
				}
			}
		}
	}

	function create_init()
	{
		// rewritable function for init child_theme setting, modules ...
		load_child_theme();
		do_action('cms_load_plugin');
	}

	function define_set($args)
	{
		mwSetting()->addObjectSettingCategory($args, $args['include']);
	}

	function add_set($args, $set, $pos = false)
	{
		$newset = $set == 'page_set' ? ['page', 'post'] : [$set];
		mwSetting()->addObjectSetting($args, $newset, $pos);
	}

	/*
	function update_set_tab($set, $tab, $field, $order = false)
	{
		foreach ($this->p_set as $p_id => $p_set) {
			foreach ($p_set as $sub_id => $sub_set) {
				if ($sub_set['id'] == $set) {
					foreach ($sub_set['fields'] as $f_id => $f_set) {
						if (isset($f_set['id']) && $f_set['id'] == $field) {
							if ($order !== false) {
								$new_tab = [];
								$i = 1;
								foreach ($f_set['tabs'] as $t_id => $t_val) {
									if ($i == $order) {
										$new_tab = array_merge($new_tab, $tab);
										$new_tab[$t_id] = $t_val;
										$i++;
									} else {
										$new_tab[$t_id] = $t_val;
									}
									$i++;
								}
								$this->p_set[$p_id][$sub_id]['fields'][$f_id]['tabs'] = $new_tab;

							} else {
								$this->p_set[$p_id][$sub_id][$f_id]['tabs'] = array_merge($this->p_set[$p_id][$sub_id]['fields'][$f_id]['tabs'], $tab);
							}

						}
					}
				}
			}

		}
	} */

	function add_page($args)
	{
		//$this->pages[$args['menu_slug']] = $args;
	}

	function add_subpage($args)
	{
		//$this->subpages[$args['menu_slug']] = $args;
	}


	function add_page_group($args)
	{
		/*
		$args['type'] = 'setting';
		$args['group'] = $args['page'];
		$args['title'] = $args['name'];
		mwSetting()->addPage($args);
		*/
	}

	function add_page_setting($page, $args)
	{
		mwSetting()->addPageSetting($page, $args);
	}

	function add_templates($templates)
	{
		$this->p_templates = count($this->p_templates) ? array_merge($this->p_templates, $templates) : $templates;
	}

	function add_templates_topos($pos, $id, $templates)
	{
		if (count($this->p_templates)) {
			$i = 1;
			$neworder = [];
			foreach ($this->p_templates as $key => $val) {
				if ($i == $pos) {
					$neworder[$id] = $templates;
				}
				$neworder[$key] = $val;
				$i++;
			}
			$this->p_templates = $neworder;
		} else {
			$this->p_templates = $templates;
		}
	}

	function add_templates_tocat($templates)
	{
		$this->p_templates = count($this->p_templates) ? array_merge($this->p_templates, $templates) : $templates;
		foreach ($templates as $cat => $tempcat) {
			foreach ($tempcat['list'] as $subcat => $tempsubcat) {
				if (!isset($this->p_templates[$tempcat['cat']]['list'][$subcat])) {
					$this->p_templates[$tempcat['cat']]['list'][$subcat]['name'] = $tempsubcat['name'];
				}
				foreach ($tempsubcat['list'] as $temp) {
					$this->p_templates[$tempcat['cat']]['list'][$subcat]['list'][] = ['folder' => $temp, 'cat' => $cat];
				}
			}
		}
	}

	/* templates */

	function get_template_dir($temp)
	{
		return $this->p_templates[$temp]['directory'] ?? get_template_directory();
	}

	function get_template_url($temp)
	{
		return $this->p_templates[$temp]['url'] ?? get_bloginfo('template_url');
	}

	function installedWeb()
	{
		return $this->installed_web;
	}

	function isWebInstalled()
	{
		return $this->installedWeb() && $this->license->isValid() ? true : false;
	}

	/* Sidebars
	**************************************************************************** */
	function add_sidebar($args)
	{
		$this->sidebars[] = $args;
	}

	function widgets_content()
	{
		$sidebars = $this->get_sidebars();
		$selector = [];
		foreach ($sidebars as $sidebar) {
			$selector[] = $sidebar['id'];
		}
		?>
		<div class="widgets-holder-wrap cms_add_new_sidebar">

			<form action="" method="post">
				<h3><?php echo __('Vytvořit nový sidebar', 'cms'); ?></h3>
		<?php
		if (isset($_GET['add_sidebar_error'])) {
			$errors = [
				1 => __('Musíte vyplnit název sidebaru.', 'cms'),
				2 => __('Sidebar nebyl vytvořen, nepodařilo se ověřit oprávnění akce.', 'cms'),
				3 => __('Sidebar s tímto názvem již existuje. Zkuste jiný název.', 'cms'),
			];
			echo mwAdminComponents::messageBox($errors[$_GET['add_sidebar_error']], ['type' => 'error']);
		}
		if (isset($_GET['ok'])) {
			echo mwAdminComponents::messageBox(__('Sidebar byl vytvořen.', 'cms'));
		}
		?>
				<input id="cms_new_sidebar_name" class="mw_input" type="text" name="sidebar_name"
					   placeholder="<?php echo __('Název nového sidebaru', 'cms'); ?>"/>
				<input type="hidden" name="widget_action" value="cms_create_new_sidebar"/>
		<?php wp_nonce_field('cms_create_new_sidebar'); ?>
				<input type="submit" class="cms_button_secondary cms_create_new_sidebar" name="cms_create_new_sidebar"
					   value="<?php echo __('Vytvořit sidebar', 'cms'); ?>"/>
			</form>
		</div>
		<script>
			jQuery(document).ready(function ($) {
				$("#<?php echo implode(', #', $selector); ?>").append('<div class="cms_delete_widget_container">(<a class="cms_delete_widget" href="#" data-question="<?php echo __('Opravdu chcete tento sidebar smazat?', 'cms'); ?>"><?php echo __('Smazat', 'cms'); ?></a>)</div>');
			});
		</script>
		<?php
	}

	function get_sidebars()
	{
		$sidebars = get_option('cms_sidebars');
		if ($sidebars) {
			return $sidebars;
		}

		return [];
	}

	function register_sidebars()
	{
		$sidebars = $this->get_sidebars();
		foreach ($sidebars as $sidebar) {
			$this->add_sidebar($sidebar);
		}
		if (!empty($this->sidebars)) {
			$blog_set = get_option('blog_appearance');
			$widget_title = MWPageDisplay::get_font_class($blog_set['sidebar_font'], 'title');
			foreach ($this->sidebars as $sidebar) {
				$sidebar['before_title'] = '<div class="' . $widget_title . ' widgettitle">';
				$sidebar['after_title'] = '</div>';
				register_sidebar($sidebar);
			}
		}
	}

	function widget_actions()
	{
		$action = $_POST['widget_action'];
		$nonce = $_REQUEST['_wpnonce'];
		$err = 0;
		if ($action == 'cms_create_new_sidebar') {
			if (wp_verify_nonce($nonce, 'cms_create_new_sidebar')) {
				$name = stripslashes(trim($_POST['sidebar_name']));
				if (empty($name)) {
					$err = 1;
				} else {
					$sidebars = $this->get_sidebars();
					$id = 'cms_' . sanitize_html_class(sanitize_title_with_dashes($name));
					$exist = false;
					foreach ($sidebars as $sidebar) {
						if ($sidebar['id'] == $id) {
							$exist = true;
						}
					}
					if (!$exist) {
						$sidebars[] = [
							'name' => __($name, 'cms'),
							'id' => $id,
							'description' => '',
						];
						update_option('cms_sidebars', $sidebars);
					} else {
						$err = 3;
					}
				}
			} else {
				$err = 2;
			}
			$attr = $err ? '?add_sidebar_error=' . $err : '?ok=1';
			wp_redirect(admin_url('widgets.php' . $attr));
		}
	}

	function delete_sidebar()
	{
		$id = $_POST['id'];
		$sidebars = $this->get_sidebars();
		$deleted = [];
		foreach ($sidebars as $sidebar) {
			if ($sidebar['id'] != $id) {
				$deleted[] = $sidebar;
			}
		}
		update_option('cms_sidebars', $deleted);
	}

	function edit_site_status_tests(array $tests): array
	{
		unset($tests['direct']['php_sessions']); // TODO resolve this problem instead of hiding

		if ($this->getLicense()->isHosting()) {
			unset($tests['async']['loopback_requests']); // wp-cron.php is handled by a custom cron runner
		}

		return $tests;
	}

	// REDIRECT
	// **********************************************************************

	function cms_redirect_page()
	{
		global $post, $vePage;

		if (isset($post->ID) && !current_user_can('edit_pages') && (is_page() || is_single())) {
			$redirect = get_post_meta($post->ID, 'page_redirect', true);

			$redirect_url = isset($redirect['redirect_url']) ? Link::create_link($redirect['redirect_url']) : '';

			if ($redirect_url) {
				$red = true;

				$red_type = isset($redirect['redirect_type']) && $redirect['redirect_type'] ? $redirect['redirect_type'] : 302;

				if ($red_type == 302) {
					// redirect after date
					if (isset($redirect['redirect_date']) && $redirect['redirect_date']['date'] && strtotime($redirect['redirect_date']['date'] . ' ' . $redirect['redirect_date']['hour'] . ':' . $redirect['redirect_date']['minute'] . '') > current_time('timestamp')) {
						$red = false;
					}
					//redirect till x date
					if (isset($redirect['redirect_till_date']) && $redirect['redirect_till_date']['date']
							&& strtotime($redirect['redirect_till_date']['date'] . ' ' . $redirect['redirect_till_date']['hour'] . ':' . $redirect['redirect_till_date']['minute'] . '') < current_time('timestamp')
					) {
						$red = false;
					}
					// redirect x days after enter to campaign
					if (isset($redirect['redirect_campaign']) && $redirect['redirect_campaign']) {
						if (isset($_COOKIE['mioweb_campaign_access'])) {
							$campaign_id = get_post_meta($post->ID, 'mioweb_campaign', true);
							$access = unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
							$time = isset($access['time'][$campaign_id['campaign']]) ? $access['time'][$campaign_id['campaign']] + ($redirect['redirect_campaign'] * 3600 * 24) : current_time('timestamp');

							if (strtotime(date('d.m.Y', $time) . ' 23:59') > current_time('timestamp')) {
								$red = false;
							}
						} else {
							$red = false;
						}
					}
				}

				if ($red) {
					wp_redirect($redirect_url, $red_type);
					exit();
				}
			}
			$redirect_mobile_url = isset($redirect['redirect_mobile_url']) ? Link::create_link($redirect['redirect_mobile_url']) : '';
			if ($this->is_mobile && $redirect_mobile_url) {
				wp_redirect($redirect_mobile_url);
				exit();
			}
		}
	}

	// mime types

	function add_mime_types($mimes)
	{
		$mimes = array_merge($mimes, [
			'svg' => 'image/svg+xml',
			'epub|mobi' => 'application/octet-stream',
			'ttf' => 'font/ttf',
			'otf' => 'font/otf',
			'woff' => 'application/x-font-woff',
		]);

		return $mimes;
	}

	function font_filetypes(array $data, string $file, string $filename, $mimes, string $real_mime): array
	{
		if (!empty($data['ext']) && !empty($data['type'])) {
			return $data;
		}

		$wp_file_type = wp_check_filetype($filename, $mimes);

		// Check for the file type you want to enable.
		if ($wp_file_type['ext'] === 'ttf') {
			$data['ext'] = 'ttf';
			$data['type'] = 'font/ttf';
		}

		if ($wp_file_type['ext'] === 'otf') {
			$data['ext'] = 'otf';
			$data['type'] = 'font/otf';
		}

		return $data;
	}

	// Modules
	// **********************************************************************

	function add_module($name, $license = 1, $path = TEMPLATEPATH)
	{
		if ($this->license->hasModule($name) || $license == 0) {
			if ($name != 'mioweb' || !isset($this->installed_versions['funnels']) || $license == 0) {
				require_once($path . '/modules/' . $name . '/init.php');
				$this->modules[$name]['module'] = $name;
				$this->modules[$name]['license'] = $license;
			}
		}
	}

	function add_version($name, $version)
	{
		$this->versions[$name] = $version;
	}

	// Theme activation
	// **********************************************************************
	function cms_activation()
	{
		$versions = get_option('cms_versions');
		//first activation
		if (empty($versions)) {
			foreach (mwSetting()->getPages() as $id => $setPage) {
				$mwSetPage = new mwSettingPage($setPage);
				if (!$mwSetPage->getOption()) {
					$setting = $mwSetPage->getDefaultSetting();
					if (!empty($setting)) {
						add_option($id, $setting);
						mwlog(MWLS_GENERAL, 'Instalace - uložení optionu ' . $id, MWLL_INFO);
					}
				}
			}

			// for mw-admin rewrite rule
			flush_rewrite_rules();
		}

		do_action('cms_activation', $versions);

		if (empty($versions)) {
			add_option('cms_versions', $this->versions);
		} else {
			update_option('cms_versions', $this->versions);
		}
	}

	public function update_image_meta()
	{
		if (!current_user_can('edit_posts') || !isset($_POST['id'])) {
			return false;
		}

		update_post_meta(intval($_POST['id']), '_wp_attachment_image_alt', sanitize_text_field($_POST['alt']));
		wp_update_post([
			'ID' => intval($_POST['id']),
			'post_excerpt' => sanitize_text_field($_POST['caption']),
		]);

		wp_die();
	}

	/** @var int Counter how many times has been disabled saving of sets. */
	private $_save_lock_count = 0;

	public function is_save_disabled()
	{
		$res = (bool) ($this->_save_lock_count);

		//    mwlog('cms', 'is save disabled = ' . $res, MWLL_DEBUG, 'save');
		return $res;
	}

	/**
	 * Disable automatic saving of field set. Reentrant.
	 *
	 * Counterpart of this method is {@link save_enable()}. Call these methods to effectively disable automatic saving
	 * mechanism. This supports to disable saving more times in a sequence and to reenable it afterwards.
	 * Un/hooking does not work correctly for this case.
	 */
	public function save_disable()
	{
		mwlog('cms', 'save disable', MWLL_DEBUG, 'save');
		if ($this->_save_lock_count == 0) {
			//      mwlog('cms', 'UNHOOK cms_save_data', MWLL_DEBUG, 'save');
			//      remove_action('save_post', 'cms_save_data');
		}
		$this->_save_lock_count++;
	}

	/** Enable automatic saving of field set. Reentrant. */
	public function save_enable()
	{
		mwlog('cms', 'save enable', MWLL_DEBUG, 'save');
		$this->_save_lock_count--;
		if ($this->_save_lock_count == 0) {
			//      add_action('save_post', 'cms_save_data');
			//      mwlog('cms', 'HOOK cms_save_data', MWLL_DEBUG, 'save');
		}
	}

	function create_sublabel($label, $mobile = false)
	{
		$content = '<div class="sublabel">';
		$content .= '<span>' . $label . '</span>';
		if ($mobile) {
			$content .= $this->mobile_device_switcher();
		}
		$content .= '<div class="cms_clear"></div></div>';

		return $content;
	}

	function mobile_device_switcher()
	{
		$content = '<div class="mw_change_device_preview_container">';
		foreach ($this->devices as $d_key => $d_val) {
			$content .= '<a class="mw_change_device_preview mw_change_device_' . $d_key . '" href="#" data-device="' . $d_key . '">' . mw_icon('icon-' . $d_key) . '</a>';
		}
		$content .= '</div>';

		return $content;
	}

	function saveUserLastLogin($user_login, $user)
	{
		update_user_meta($user->ID, 'mw_last_login', current_time('timestamp'));
	}

	function mw_stop_auto_update_emails($send, $type, $core_update, $result)
	{
		return empty($type) || $type != 'success';
	}

	function generate_multielement_ajax()
	{
		echo MwFields::multiElementItem(unserialize(base64_decode($_POST['setting'])), [], $_POST['id'], true);
		die();
	}

	/**
	 * @param array<string, string> $headers
	 * @return array<string, string>
	 */
	public function mw_send_headers(array $headers): array
	{
		// Disable xmlrpc pingback
		unset($headers['X-Pingback']);

		if ($this->config->getHstsAge() !== null && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
			$headers['Strict-Transport-Security'] = 'max-age=' . $this->config->getHstsAge();
		}

		return array_merge($headers, $this->config->getHeaders());
	}

	public function disable_xmlrpc_pingback($methods)
	{
		unset($methods['pingback.ping']);

		return $methods;
	}

	public function enable_strict_error_handler(): void
	{
		set_error_handler(function ($severity, $message, $filename, $lineno) {
			$errorReporting = error_reporting();

			if ($errorReporting === 0) {
				return;
			}

			if ($errorReporting & $severity) {
				throw new ErrorException($message, 0, $severity, $filename, $lineno);
			}
		});
	}

	function comment_notification_recipients($emails)
	{
		$activePlugins = get_option('active_plugins');
		$antispamPlugins = [
				'akismet/akismet.php',
				'anti-spam/anti-spam.php',
				'antispam-bee/antispam_bee.php',
		];

		// Disable sending e-mail if no AntiSpam plugin is installed
		if (count(array_intersect($activePlugins, $antispamPlugins)) <= 0) {
			return [];
		}

		return $emails;
	}

	public function getModules()
	{
		global $wpdb;

		$option = 'cms_license_modules';
		$allOptions = wp_load_alloptions();

		if (isset($allOptions[$option])) {
			$value = $allOptions[$option];
		} else {
			$value = wp_cache_get($option, 'options');

			if ($value === false) {
				$row = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option));

				// Has to be get_row() instead of get_var() because of funkiness with 0, false, null values.
				if (is_object($row)) {
					$value = $row->option_value;
					wp_cache_add($option, $value, 'options');
				} else { // Option does not exist, so we must cache its non-existence.
					return [];
				}
			}
		}

		return maybe_unserialize($value);
	}

	/**
	 * @var bool Flag that tells that saving of sets is currently in progress. This can be used to check if custom save
	 * WP hooks should be skipped or not. Typically not when called within saving operation.
	 */
	public $is_saving = false;

	/** @return mioweb Returns singleton instance of mioweb. */
	public static function instance(): mioweb
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

}

/** Suspend autosave of field sets. Reentrant. */
function cms_save_disable()
{
	MW()->save_disable();
}

/** Reenable autosave of field sets. Reentrant. */
function cms_save_enable()
{
	MW()->save_enable();
}

function cms_is_saved_disabled()
{
	return MW()->is_save_disabled();
}

function cms_is_saving()
{
	return MW()->is_saving;
}

?>
