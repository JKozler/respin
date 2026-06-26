<?php

use Mioweb\Api\Recaptcha\ReCaptchaValidator;
use Mioweb\Mailing\Exceptions\TooManyRecipientsException;
use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\Link;
use Mioweb\VisualEditor\Lib\Button;
use Mioweb\Mailing\Mailer;

class visualEditor
{

	public $builder;

	public $display;

	public $edit_mode;

	public $builder_mode;

	public $google_map_api;

	public $modul_type; // moved to pagebuilder

	public $page_type;

	public $object_id;

	public $post_id;

	public $window_editor;

	public $window_editor_setting = [];

	public $shortcodes = [];

	public $shortcode_groups = [];

	public $ab_test;

	private $styles = [];

	private $mobile_styles = [];

	private array $element_scripts = [];

	//public $is_mobile=false;


	function __construct()
	{
		$this->check_version();

		$this->edit_mode = current_user_can('edit_pages') ? true : false;
		$this->builder_mode = $this->edit_mode && !isset($_GET['mw_preview']) ? true : false;

		// window editor init (visual editor in iframe window - popups)
		if (isset($_GET['window_editor'])) {
			$this->window_editor = true;
			$this->window_editor_setting['type'] = $_GET['window_editor'];
			if (isset($_GET['id'])) {
				$this->window_editor_setting['id'] = $_GET['id'];
				$this->window_editor_setting['new'] = false;
			} else {
				$this->window_editor_setting['id'] = 0;
				$this->window_editor_setting['new'] = true;
			}
		}

		if ($this->builder_mode) {
			$this->builder = new MWPageBuilder();
			$this->display = new MWPageDisplay(false, true);
		} else {
			$this->display = new MWPageDisplay(true, $this->edit_mode);
		}

		//$this->is_mobile = wp_is_mobile();

		$this->registerHooks();

		/*
		if ($this->edit_mode) {
			$this->tutorials = new MwTutorials();
			if (isset($_GET['mw_tutorial'])) {
				$this->tutorials->setTutorial('game');
			}
		} */

		$googleMapsApi = mwApiConnect()->getApi('google_maps');
		$this->google_map_api = $googleMapsApi !== null ? $googleMapsApi->getOption() : null;
	}

	function registerHooks()
	{
		add_action('wp', [$this, 'wp_loaded'], 20);
		add_action('wp_enqueue_scripts', [$this, 'load_scripts'], 2);
		add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts'], 10);
		add_filter('show_admin_bar', '__return_false');

		// send contact form
		add_action('wp_ajax_nopriv_ve_send_contact_form', [$this, 'send_contact_form']);
		add_action('wp_ajax_ve_send_contact_form', [$this, 'send_contact_form']);

		// save form data
		add_action('wp_ajax_nopriv_ve_save_form_data', [$this, 'save_form_data']);
		add_action('wp_ajax_ve_save_form_data', [$this, 'save_form_data']);

		add_action('init', [$this, 'init_hook']);

		//shortcodes
		add_action('wp_ajax_open_shortcode_select', [$this, 'open_shortcode_select']);
		add_action('wp_ajax_open_new_shortcode_setting', [$this, 'open_new_shortcode_setting']);

		// mailer failed
		add_action('wp_mail_failed', [$this, 'action_wp_mail_failed'], 10, 1);

		// redirect after login
		add_filter('login_redirect', [$this, 'login_redirect'], 10, 3);

		// save cookie consent
		add_action('wp_ajax_nopriv_mwSaveCookieConsent', ['MwCookieManagement', 'saveConsent_ajax']);
		add_action('wp_ajax_mwSaveCookieConsent', ['MwCookieManagement', 'saveConsent_ajax']);

		if ($this->edit_mode) {
			// add image sizes to media library
			add_filter('image_size_names_choose', [$this, 'display_custom_image_sizes']);

			//add custom file fonts to editor
			add_action('wp_head', function () {
				$this->display->printFileFonts(MW()->file_fonts);
			});
		}

		if ($this->builder_mode) {
			// add / reload element
			add_action('wp_ajax_create_element', [$this, 'create_element_ajax']);
			// add/reload row
			add_action('wp_ajax_add_new_row', [$this, 'add_new_row']);
			// reload row background
			add_action('wp_ajax_reload_row_background', [$this, 'reload_row_background']);
			// reload logo
			add_action('wp_ajax_reload_header_logo', [$this, 'reload_header_logo']);
			// reload header
			add_action('wp_ajax_reload_header', [$this, 'reload_header']);
			// reload footer
			add_action('wp_ajax_reload_footer', [$this, 'reload_footer']);
			// reload body styles
			add_action('wp_ajax_reload_body', [$this, 'reload_body']);
			// reload popup body styles
			add_action('wp_ajax_reload_popup_body', [$this, 'reload_popup_body']);
			// reload body background
			add_action('wp_ajax_reload_body_background', [$this, 'reload_body_background']);
			// save page
			add_action('wp_ajax_save_page', [$this, 'save_page']);

			// copy paste row
			add_action('wp_ajax_paste_row', [$this, 'paste_row']);
			add_action('wp_ajax_copy_row', [$this, 'copy_row']);
			add_action('wp_ajax_clear_clipboard', [$this, 'clear_clipboard']);

			// save revision
			add_action('wp_ajax_mw_save_revision', [$this, 'save_revision']);
		}
	}

	function removeBlockedPlugins($plugins)
	{
		$plugins = MwCookies()->removeBlockedPlugins($plugins);

		return $plugins;
	}

	function load_scripts()
	{
		$script_version = filemtime(get_template_directory() . '/style.css');
		$js_texts = require_once(__DIR__ . '/js/js_texts.php'); // variable $js_texts

		//register scripts
		wp_register_script('ve_lightbox_script', get_bloginfo('template_url') . '/library/visualeditor/includes/lightbox/lightbox.js', ['jquery'], $script_version, true);
		wp_register_script('ve_waypoints_script', get_bloginfo('template_url') . '/library/visualeditor/includes/animate/waypoints.min.js', ['jquery'], true);
		wp_register_script('front_menu', get_bloginfo('template_url') . '/library/visualeditor/js/front_menu.js', ['jquery'], $script_version, true);
		wp_register_script('ve_countdown_script', get_bloginfo('template_url') . '/library/visualeditor/includes/countdown/jquery.countdown.js', ['jquery'], $script_version, true);
		wp_localize_script('ve_countdown_script', 'velang', $js_texts['countdown']);
		wp_register_script('ve_miocarousel_script', get_bloginfo('template_url') . '/library/visualeditor/includes/miocarousel/miocarousel.js', ['jquery'], $script_version, true);
		wp_register_script('ve_masonry_script', get_bloginfo('template_url') . '/library/visualeditor/includes/mansory/mansory.min.js', ['jquery'], $script_version, true);
		wp_register_script('ve-front-script', get_bloginfo('template_url') . '/library/visualeditor/js/front.js', ['jquery'], $script_version, true);
		wp_localize_script('ve-front-script', 'front_texts', $js_texts['front']);

		//wp_register_script('mw-svg-ie-use', get_bloginfo('template_url') . '/library/visualeditor/js/svgxuse.min.js', ['jquery'], 1, true);

		wp_register_script('ve_youtube_api', 'https://www.youtube.com/iframe_api', false, false, 3);

		wp_register_script('ve_social_sprinters', 'https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.14/iframeResizer.min.js');

		//google_maps_api
		if ($this->google_map_api !== null) {
			$gmap_api_key = isset($this->google_map_api['api_key']) && $this->google_map_api['api_key']
					? $this->google_map_api['api_key']
					: 'AIzaSyDSyH51Ik2gY3QGHo4Isn45ogmUvfqKC6I';

			if ($this->edit_mode) {
				$gmap_api_key .= '&libraries=places';
			}

			wp_register_script('ve_google_maps', 'https://maps.googleapis.com/maps/api/js?key=' . $gmap_api_key . '&callback=initialize_google_maps', false, false, 3);
		}

		if ($this->edit_mode) {
			wp_register_script('ve_uppy', 'https://releases.transloadit.com/uppy/v1.26.1/uppy.min.js');
			wp_register_script('ve_uppy_locale', 'https://releases.transloadit.com/uppy/locales/v1.17.2/' . get_locale() . '.min.js');
			wp_enqueue_script('ve_uppy');
			wp_enqueue_script('ve_uppy_locale');
		}

		//register styles
		wp_register_style('ve_lightbox_style', get_bloginfo('template_url') . '/library/visualeditor/includes/lightbox/lightbox.css', [], $script_version);
		wp_register_style('ve_animate_style', get_bloginfo('template_url') . '/library/visualeditor/includes/animate/animate.css', [], $script_version);
		wp_register_style('ve_countdown_style', get_bloginfo('template_url') . '/library/visualeditor/includes/countdown/jquery.countdown.css', [], $script_version);
		wp_register_style('ve_miocarousel_style', get_bloginfo('template_url') . '/library/visualeditor/includes/miocarousel/miocarousel.css', [], $script_version);

		wp_register_script('jquery-ui-nestedsortable', get_bloginfo('template_url') . '/library/visualeditor/js/jquery.mjs.nestedSortable.js', ['jquery-ui-sortable']);
		wp_register_style('ve-content-style', get_bloginfo('template_url') . '/style.css', [], $script_version);

		wp_enqueue_script('jquery');

		if ($this->edit_mode) {
			wp_register_script('mw_droppable_iframe', get_bloginfo('template_url') . '/library/visualeditor/js/jquery-ui-droppable-iframe.js', ['jquery', 'jquery-ui-droppable'], $script_version);
			wp_register_script('mw_pgb_editor_script', get_bloginfo('template_url') . '/library/visualeditor/js/pgb_editor.js', ['jquery', 'jquery-ui-nestedsortable', 've_weditor_admin_script'], $script_version, false);
			wp_localize_script('mw_pgb_editor_script', 'texts', $js_texts['pgb_editor']);

			wp_register_script('tiny_mce_js', includes_url('js/tinymce/') . 'wp-tinymce.php', ['jquery'], false, false);
			//wp_register_script( 'mw_tinymce_plugin', get_bloginfo('template_url') . '/library/visualeditor/includes/tinymce/mwplugin.js', array(), $script_version );
			wp_register_script('mw_pgb_page_script', get_bloginfo('template_url') . '/library/visualeditor/js/pgb_page.js', ['jquery', 'tiny_mce_js'], $script_version, false);
			wp_localize_script('mw_pgb_page_script', 'texts', $js_texts['pgb_editor']);

			wp_register_script('ve_install_scripts', get_bloginfo('template_url') . '/library/visualeditor/lib/install/install.js', [], $script_version);
			wp_register_style('ve_install_style', get_bloginfo('template_url') . '/library/visualeditor/lib/install/style.css', [], $script_version);

			wp_register_style('mw_pgb_page', get_bloginfo('template_url') . '/library/visualeditor/css/pgb_page.css', [], $script_version);
			wp_register_style('mw_pgb_editor', get_bloginfo('template_url') . '/library/visualeditor/css/pgb_editor.css', [], $script_version);

			// ** // check if needed
			wp_register_style('cms_admin_styles', get_template_directory_uri() . '/library/admin/css/admin.css', [], $script_version);

			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');

			wp_register_script('mw-touch-support', get_bloginfo('template_url') . '/library/visualeditor/js/touch_support.js', ['jquery'], $script_version, false);
			wp_enqueue_script('mw-touch-support');

			wp_register_style('ve_uppy', 'https://releases.transloadit.com/uppy/v1.26.1/uppy.min.css');
			wp_enqueue_style('ve_uppy');
		}
	}

	function load_admin_scripts()
	{
		$script_version = filemtime(get_template_directory() . '/style.css');
		$js_texts = require_once(__DIR__ . '/js/js_texts.php'); // variable $js_texts

		wp_register_script('jquery-ui-nestedsortable', get_bloginfo('template_url') . '/library/visualeditor/js/jquery.mjs.nestedSortable.js', ['jquery-ui-sortable']);
		wp_register_script('mw_pgb_editor_script', get_bloginfo('template_url') . '/library/visualeditor/js/pgb_editor.js', ['jquery', 'jquery-ui-nestedsortable', 've_weditor_admin_script', 'cms_admin_script'], $script_version, false);
		wp_localize_script('mw_pgb_editor_script', 'texts', $js_texts['pgb_editor']);
		wp_localize_script('cms_lightbox_script', 'texts', $js_texts['pgb_editor']);
		wp_register_script('ve_install_scripts', get_bloginfo('template_url') . '/library/visualeditor/lib/install/install.js', [], $script_version);
		wp_register_style('ve_install_style', get_bloginfo('template_url') . '/library/visualeditor/lib/install/style.css', [], $script_version);

		$current_screen = get_current_screen();

		if (isset($_GET['page']) || isset($_GET['post']) || ($current_screen->action == 'add' && $current_screen->base == 'post')) {
			wp_enqueue_script('mw_pgb_editor_script');
			wp_enqueue_script('ve_install_scripts');
			wp_enqueue_style('ve_install_style');
		}
	}

	function login_redirect($redirect_to)
	{
		if (!isset($_POST['cms_abort_redirect']) && $redirect_to == get_admin_url()) {
			$redirect_to = home_url();
		}

		return $redirect_to;
	}


	function wp_loaded()
	{
		global $post;
		$save_id = 0;
		if (!$this->modul_type) {
			$this->modul_type = 'web';
		}

		$this->object_id = null;

		// editor for post types like popups, footers... opened in iframe window
		if (is_404()) {
			$web_option = get_option('web_option_basic');
			if (isset($web_option['404page']) && $web_option['404page']) {
				$save_id = $post_id = $web_option['404page'];
				$this->page_type = 'page';
			} else {
				$post_id = 0;
				$this->page_type = '404';
			}
		} elseif ($this->window_editor) {
			$save_id = $post_id = $this->window_editor_setting['id'];
			$this->page_type = $this->window_editor_setting['type'];
			$this->object_id = $this->window_editor_setting['type'];
		} elseif (is_home()) {
			$this->page_type = 'blog';
			$this->object_id = 'page';
			$post_id = is_home() && ! is_front_page() ? get_option('page_for_posts') : 0;
			$save_id = 0;
		} elseif (is_tax()) {
			$post_id = 0;
			$save_id = get_queried_object_id();
			$this->page_type = substr(get_query_var('taxonomy'), 0, 10);
			$this->object_id = get_query_var('taxonomy');
		} elseif ($this->is_blog()) {
			$post_id = 0;
			$this->page_type = 'blog_type';
			if (is_author()) {
				$this->page_type = 'author';
				$this->object_id = 'users';
			} elseif (is_category()) {
				$this->page_type = 'category';
				$this->object_id = 'category';
			} elseif (is_tag()) {
				$this->page_type = 'tag';
				$this->object_id = 'post_tag';
			}
		} elseif (isset($post->ID)) {
			$save_id = $post_id = $post->ID;
			// A/B testing in funnels by mw analytics
			if ($this->ab_test) {
				$save_id = $post_id = $this->ab_test;
			} elseif (!$this->edit_mode) {
				// old way of A/B test
				$original_id = $post_id;
				$page_statistics = get_post_meta($original_id, 'page_statistics', true);

				$abPage = null;
				// if user confirm analytics cookies
				if (MwCookies()->isPermitted('analytics') && isset($_SESSION['ve_ab_page_' . $original_id])) {
					setcookie('ve_ab_page_' . $original_id, $_SESSION['ve_ab_page_' . $original_id], time() + (60 * 60 * 24 * 2), COOKIEPATH, COOKIE_DOMAIN);
					$abPage = $_SESSION['ve_ab_page_' . $original_id];
					unset($_SESSION['ve_ab_page_' . $original_id]);
				} else {
					$abPage = $_SESSION['ve_ab_page_' . $original_id] ?? $_COOKIE['ve_ab_page_' . $original_id] ?? null;
				}

				if (isset($page_statistics['pages']) && is_array($page_statistics['pages'])) {
					// Remove empty values from array
					$page_statistics['pages'] = array_filter($page_statistics['pages'], function ($pageId) {
						return $pageId !== null && $pageId !== '';
					});

					if ($page_statistics['pages']) {
						if (isset($abPage) && (in_array($abPage, $page_statistics['pages']) || $abPage == $original_id) && get_page($abPage)) {
							$save_id = $post_id = $abPage;
						} else {
							$pag_count = count($page_statistics['pages']);
							$show_page_id = rand(0, $pag_count);

							if ($show_page_id != $pag_count) {
								// delete deleted pages
								if (!get_page($page_statistics['pages'][$show_page_id])) {
									$new_page_statistics = [];
									foreach ($page_statistics['pages'] as $spage) {
										if (get_page($spage)) {
											$new_page_statistics[] = $spage;
										}
									}
									$page_statistics['pages'] = $new_page_statistics;
									update_post_meta($original_id, 'page_statistics', $page_statistics);
								} else {
									// set post id to a/b variant
									$save_id = $post_id = $page_statistics['pages'][$show_page_id];
								}
							}

							if (isset($_COOKIE['ve_ab_page_' . $original_id])) {
								unset($_COOKIE['ve_ab_page_' . $original_id]);
							}
							if (isset($_SESSION['ve_ab_page_' . $original_id])) {
								unset($_SESSION['ve_ab_page_' . $original_id]);
							}

							$abPage = null;
						}
					}
				}

				if (isset($_SESSION['ve_page_statistics'])) {
					// count conversion
					if ($_SESSION['ve_page_statistics']['target'] == $original_id) {
						$count = get_post_meta($_SESSION['ve_page_statistics']['page'], 'page_conversion_rate', true);
						if (isset($count[$_SESSION['ve_page_statistics']['source']]['con_target'])) {
							$count[$_SESSION['ve_page_statistics']['source']]['con_target']++;
						} elseif (is_array($count)) {
							$count[$_SESSION['ve_page_statistics']['source']]['con_target'] = 1;
						} else {
							$count = [$_SESSION['ve_page_statistics']['source'] => ['con_target' => 1]];
						}
						update_post_meta($_SESSION['ve_page_statistics']['page'], 'page_conversion_rate', $count);
					}

					// delete session and disable conversion
					// if is undirect, delete only on conversion
					if ($_SESSION['ve_page_statistics']['undirect']) {
						if ($_SESSION['ve_page_statistics']['target'] == $original_id) {
							unset($_SESSION['ve_page_statistics']);
						}
					} elseif ($_SESSION['ve_page_statistics']['page'] != $original_id) {
						// if is direct, delete on any page (not on enter page)
						unset($_SESSION['ve_page_statistics']);
					}
				}
				// on visit
				if (isset($page_statistics['target']) && $page_statistics['target']) {
					if (!$abPage) {
						$visit = get_post_meta($original_id, 'page_conversion_rate', true);

						if (!is_array($visit)) {
							$visit = [];
						}

						if (!array_key_exists($post_id, $visit) || !is_array($visit[$post_id])) {
							$visit[$post_id] = [];
						}

						if (!array_key_exists('con_source', $visit[$post_id]) || !is_int($visit[$post_id]['con_source'])) {
							$visit[$post_id]['con_source'] = 0;
						}

						$visit[$post_id]['con_source']++;

						update_post_meta($original_id, 'page_conversion_rate', $visit);
						$_SESSION['ve_page_statistics'] = ['page' => $original_id, 'source' => $post_id, 'target' => $page_statistics['target'], 'undirect' => isset($page_statistics['undirect_conversion']) ? true : false];
					}
				}

				if (!$abPage) {
					if (MwCookies()->isPermitted('analytics')) {
						setcookie('ve_ab_page_' . $original_id, $post_id, time() + (60 * 60 * 24 * 2), COOKIEPATH, COOKIE_DOMAIN);
					} else {
						$_SESSION['ve_ab_page_' . $original_id] = $post_id;
					}
				}
			}
			if (is_single()) {
				$this->page_type = $post->post_type;
				$this->object_id = $post->post_type;
			} else {
				$this->page_type = 'page';
				$this->object_id = 'page';
			}
		} else {
			$post_id = 0;
			$this->page_type = 'none';
		}

		$this->page_type = apply_filters('ve_page_type', $this->page_type, $post_id);

		$this->init($post_id, $save_id);
	}

	function init($post_id, $save_id = 0)
	{
		//$save_post_id = ($save_id) ? $save_id : $post_id;
		$this->post_id = $post_id;

		if ($this->builder_mode) {
			$this->builder->init($post_id, $save_id);
		} else {
			$this->display->init($post_id, $save_id, $this->page_type);
		}
	}
	function resetPageId($post_id)
	{
		$this->post_id = $post_id;
		$this->display->resetPageId($post_id);
	}

	function createABTest($ab)
	{
		if (count($ab)) {
			// if user confirm analytics cookies
			if (MwCookies()->isPermitted('analytics') && isset($_SESSION['ve_ab_page_' . $ab[0]])) {
				setcookie('ve_ab_page_' . $ab[0], $_SESSION['ve_ab_page_' . $ab[0]], time() + (60 * 60 * 24 * 2), COOKIEPATH, COOKIE_DOMAIN);
				$abPage = $_SESSION['ve_ab_page_' . $ab[0]];
				unset($_SESSION['ve_ab_page_' . $ab[0]]);
			} else {
				$abPage = $_SESSION['ve_ab_page_' . $ab[0]] ?? $_COOKIE['ve_ab_page_' . $ab[0]] ?? null;
			}
			// if user visited page and ab test is setted.

			if ($abPage && in_array($abPage, $ab) && get_post_status($abPage) == 'publish') {
				$this->ab_test = $abPage;
			} else {
				// if user visited page and ab test is not setted.
				$this->ab_test = $ab[rand(0, 1)];
				if (MwCookies()->isPermitted('analytics')) {
					setcookie('ve_ab_page_' . $ab[0], $this->ab_test, time() + (60 * 60 * 24 * 2), COOKIEPATH, COOKIE_DOMAIN);
				} else {
					$_SESSION['ve_ab_page_' . $ab[0]] = $this->ab_test;
				}
			}
		}

		return $this->ab_test;
	}


	/* Save page ********
	*******************************************************************************  */
	function save_page()
	{
		global $wpdb;

		if (isset($_POST['new_save'])) {
			// create layer
			$layer = MWPageBuilder::create_post_layer();
			// save single_elements
			$fonts = $this->display->get_layer_fonts($layer, []);

			$layer = self::code($layer);

			$postId = $_POST['post_id'];
			$pageType = $_POST['page_type'];

			if ($pageType === 'blog' && (int) $postId === 0) {
				if (isset($fonts['google']) && $fonts['google']) {
					update_option('ve_blog_google_fonts', $fonts['google']);
				}
				if (isset($fonts['file']) && $fonts['file']) {
					update_option('ve_blog_file_fonts', $fonts['file']);
				}
			} elseif ($pageType === 'eshop_cate') {
				if (isset($fonts['google']) && $fonts['google']) {
					update_term_meta($postId, 've_google_fonts', $fonts['google']);
				}
				if (isset($fonts['file']) && $fonts['file']) {
					update_term_meta($postId, 've_file_fonts', $fonts['file']);
				}
			} else {
				if (isset($fonts['google']) && $fonts['google']) {
					update_post_meta($postId, 've_google_fonts', $fonts['google']);
				}
				if (isset($fonts['file']) && $fonts['file']) {
					update_post_meta($postId, 've_file_fonts', $fonts['file']);
				}
			}

			$allowed = ['page', 'cms_footer', 'weditor', 've_header', 've_elvar', 'mw_slider', 'mwupsell'];

			if (in_array($pageType, $allowed, true)) {
				wp_update_post([
					'ID' => $postId,
					//'post_status' => 'publish',
					//'post_author' => get_the_author_meta('ID'),
					'post_content' => $layer,
				]);
			} elseif ($pageType === 'blog') {
				$this->create_mw_revision('mw_hb_revisions', $layer);
			} elseif ($pageType === 'eshop_cate') {
				$this->create_mw_revision('mw_sc_revisions', $layer, $postId);
			} elseif ($pageType === 'mwproduct') {
				$this->create_mw_revision('mw_sp_revisions', $layer, $postId);
			}

			$wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='" . $pageType . "' AND vpl_post_id=" . $postId);
			if ($wpdb->num_rows) {
				$wpdb->update($wpdb->prefix . 've_posts_layer', ['vpl_layer' => $layer], ['vpl_post_id' => $postId, 'vpl_type' => $pageType]);
			} else {
				$wpdb->insert($wpdb->prefix . 've_posts_layer', ['vpl_post_id' => $postId, 'vpl_type' => $pageType, 'vpl_layer' => $layer]);
			}

			// Delete ve_layer_autosave
			if (isset($_SESSION['ve_layer_autosave'][$postId])) {
				unset($_SESSION['ve_layer_autosave'][$postId]);
			}

			if (
				($pageType === 'page' && $postId == get_option('page_on_front'))
				|| ($pageType === 'blog' && get_option('show_on_front') === 'posts')
			) {
				MW()->getLicense()->sendNotify();
			}
		}
		die();
	}

	function create_mw_revision($type, $layer, $parent = '')
	{
		$args = [
			'post_type' => $type,
			'post_parent' => $parent,
			'post_status' => 'publish',
			//'post_author' => get_the_author_meta('ID'),
			'post_content' => $layer,
		];
		wp_insert_post($args);

		$revisions_to_keep = $this->builder->control_revisions();

		$all_revisions = get_posts([
			'post_type' => $type,
			'post_status' => 'publish',
			'post_parent' => $parent,
			'order' => 'ASC',
		]);

		$delete = count($all_revisions) - $revisions_to_keep;

		if ($delete > 0) {
			$revisions = array_slice($all_revisions, 0, $delete);

			foreach ($revisions as $revision) {
				wp_delete_post($revision->ID);
			}
		}
	}

	/* Save revision
	************************************************************************** */

	function save_revision()
	{
		global $wpdb;

		if (isset($_POST['rev_id']) && $_POST['rev_id']) {
			if ($_POST['rev_type'] == 'mw_hb_revisions') {
				$post = get_post($_POST['rev_id']);
				$wpdb->update($wpdb->prefix . 've_posts_layer', ['vpl_layer' => $post->post_content], ['vpl_post_id' => 0, 'vpl_type' => 'blog']);
				$this->create_mw_revision('mw_hb_revisions', $post->post_content);
			} elseif ($_POST['rev_type'] == 'mw_sc_revisions') {
				$post = get_post($_POST['rev_id']);
				$wpdb->update($wpdb->prefix . 've_posts_layer', ['vpl_layer' => $post->post_content], ['vpl_post_id' => $post->post_parent, 'vpl_type' => 'eshop_cate']);
				$this->create_mw_revision('mw_sc_revisions', $post->post_content, $post->post_parent);
			} elseif ($_POST['rev_type'] == 'mw_sp_revisions') {
				$post = get_post($_POST['rev_id']);
				$wpdb->update($wpdb->prefix . 've_posts_layer', ['vpl_layer' => $post->post_content], ['vpl_post_id' => $post->post_parent, 'vpl_type' => 'mwproduct']);
				$this->create_mw_revision('mw_sp_revisions', $post->post_content, $post->post_parent);
			} else {
				wp_restore_post_revision($_POST['rev_id'], ['post_content']);
			}
		}
		die();
	}


	/* Builder ajax ********
	*******************************************************************************  */

	/* reload header */

	function reload_header()
	{
		$this->display->header_setting = $_POST['header_setting'];
		$this->display->printHeader();

		if (isset($this->display->header_setting['fixed_header']) && $this->display->showHeader()) {
			echo '<script>
          jQuery(function() {
            mwGetIframeContent().setFixedHeader();
          });
          </script>';
		}

		die();
	}

	/* reload header logo */

	function reload_header_logo()
	{
		$this->display->header_setting = $_POST['header_setting'];
		$this->display->printLogo();

		die();
	}

	/* reload footer */

	function reload_footer()
	{
		$this->display->footer_setting = $_POST['footer_setting'];
		$this->display->printFooter($_POST['footer_setting']);

		die();
	}

	/* reload body */

	function reload_body()
	{
		$this->display->post_id = $_POST['post_id'];

		$this->display->page_setting = get_option('ve_appearance');
		do_action('ve_global_setting', $this->display->post_id);

		$this->display->page_setting = mwBackCompatibility::page_set($this->display->page_setting);

		//print_r($_POST['body_setting']);
		$this->display->mergeWithPageSetting($_POST['body_setting']);

		//print_r($this->display->page_setting);

		$return['styles'] = $this->display->printBodyStyles();
		$return['background'] = $this->display->generate_background($this->display->page_setting, 'body', true);

		wp_send_json($return);
		die();
	}

	function reload_popup_body()
	{
		$this->display->post_id = $_POST['post_id'];

		$this->display->page_setting = get_option('ve_appearance');
		do_action('ve_global_setting', $this->display->post_id);

		$this->display->page_setting = mwBackCompatibility::page_set($this->display->page_setting);

		$this->display->popups->get_popup_setting_by_id($_POST['post_id']);

		$this->display->mergeWithPageSetting($_POST['body_setting']);

		$return['styles'] = $this->display->printBodyStyles();

		wp_send_json($return);
		die();
	}

	function reload_body_background()
	{
		$this->display->post_id = $_POST['post_id'];

		$this->display->page_setting = get_option('ve_appearance');
		do_action('ve_global_setting', $this->display->post_id);

		$this->display->page_setting = mwBackCompatibility::page_set($this->display->page_setting);

		$this->display->mergeWithPageSetting($_POST['set']);

		echo $this->display->generate_background($this->display->page_setting, 'body', true);

		die();
	}

	/* create / reload element */

	function create_element_ajax()
	{
		if (isset($_POST['layer']) && $_POST['layer']) {
			$element = $_POST['layer'];
			$element = $this->display->stripslashes_deep($element);
		} else {
			$element = [];
		}

		if (isset($_POST['newelement'])) {
			$element = $this->get_element_default_setting($_POST['element_type']);
			$element['type'] = $_POST['element_type'];
			$element['style']['mw30'] = '1'; // for back compatibility - element is 3.0
		}

		$return['newkey'] = $_POST['el_id'] ?? 'element_' . md5(microtime());

		$single = isset($_POST['single']) && $_POST['single'] ? true : false;

		$return['content'] = $this->display->generate_element($element, $return['newkey'], $_POST['post_id'], true, '', true, $single);
		$el_google_fonts = $this->display->get_element_fonts($element, [])['google'];

		if (count($el_google_fonts) > 0) {
			$fonts = [];
			foreach ($el_google_fonts as $key => $val) {
				$fonts[] = str_replace(' ', '+', $key) . ':' . implode(',', array_keys($val));
			}

			$return['font'] = implode('|', $fonts);
		} else {
			$return['font'] = '';
		}

		// if (isset($_POST['type'])) $return['type'] = $_POST['type'];

		mwSetting()->saveUsed($element['style']);

		$return['code'] = $element;

		wp_send_json($return);

		die();
	}

	function get_element_default_setting($element_type)
	{
		global $mwContainer;
		$element = [];
		if (isset($mwContainer->elements[$element_type]['tab_setting'])) {
			foreach ($mwContainer->elements[$element_type]['tab_setting'] as $tab_val) {
				$element = $this->get_element_default_setting_group($tab_val['setting'], $element);
			}
		} else {
			$element = $this->get_element_default_setting_group($mwContainer->elements[$element_type]['setting'], $element);
		}

		return $element;
	}

	function get_element_default_setting_group($setting, $element = [])
	{
		foreach ($setting as $set_val) {
			if ($set_val['type'] == 'group') {
				$element = $this->get_element_default_setting_group($set_val['setting'], $element);
			} elseif (isset($set_val['content'], $set_val['id'])) {
				$element['style'][$set_val['id']] = $set_val['content'];
			}
		}

		return $element;
	}


	/* Row actions ******** */


	/* create / reload row */
	function add_new_row()
	{
		if ($_POST['rowtype'] == 'template') { // template row
			require_once(__DIR__ . '/templates/rows/' . $_POST['content'] . '.php');
			$newrow = $content;
		} elseif ($_POST['rowtype'] == 'custom') { // custom row
			$row_temp = get_post($_POST['content']);
			$newrow = $row_temp ? visualEditor::decode($row_temp->post_content) : [];
		} elseif ($_POST['rowtype'] == 'clipboard') { // row from clipboard
			$newrow = $_SESSION['ve_copy_row'];
		} elseif ($_POST['rowtype'] == 'ajax') { // reload
			$newrow = $_POST['content'];
		} else { // empty row
			$cols = explode('-', $_POST['content']);
			foreach ($cols as $col) {
				$col_type = str_replace('/', '', $col);
				$newcol_set = [
					'type' => 'col-' . $col_type,
					'class' => '',
					'content' => [],
				];
				if ($col != $col_type) {
					$newcol_set['break'] = '1';
				}

				$newcols[] = $newcol_set;
			}
			$newrow = [
				'class' => '',
				'style' => [
					'background_color' => ['color1' => '#ffffff', 'rgba1' => 'rgba(255,255,255,1)', 'color2' => '', 'rgba2' => '', 'transparency1' => '1', 'transparency2' => '1'],
					'background_image' => [
						'cover' => '1',
						'overlay_color' => [
							'color' => '#000000',
							'transparency' => '0.2',
							'rgba' => 'rgba(0, 0, 0, 0.2)',
						],
					],
					'content_align' => 'top',
					'link_color' => '',
					'row_padding' => 'big',
					'font' => [
						'font-size' => '',
						'font-family' => '',
						'weight' => '',
						'color' => '',
					],
				],
				'content' => $newcols,
			];
		}

		$return['id'] = isset($_POST['row_id']) ? str_replace('row_', '', $_POST['row_id']) : md5(microtime());
		$return['row_type'] = $newrow['type'] ?? 'row';

		if (isset($newrow['type']) && $newrow['type'] == 'slider') {
			if ($_POST['rowtype'] == 'template') {
				$existing_slides = get_posts(['post_type' => 'mw_slider', 'posts_per_page' => -1]);

				foreach ($newrow['style']['slides'] as $key => $slide) {
					$s_title = $this->new_nodup_name($slide['slider_content']['title'], $existing_slides);

					$new_post = [
						'post_title' => $s_title,
						'post_status' => 'publish',
						'post_type' => 'mw_slider',
					];
					$newslider_id = $this->builder->save_new_window_post($new_post, $slide['slider_content']['theme'], self::code($slide['slider_content']['content']), 'mw_slider');
					$newrow['style']['slides'][$key]['slider_content'] = $newslider_id;
				}
			}
		}

		$return['row'] = $this->display->generate_row($newrow, $return['id'], $_POST['post_id'] ?? 0, true, '', true);
		$return['settings'] = $this->display->setting_container;

		wp_send_json($return);

		die();
	}

	function reload_row_background()
	{
		$row = $_POST['row'];
		echo $this->display->generate_background($row['style'], $_POST['row_id'], true);

		die();
	}

	function unstrip_array($array)
	{
		foreach ($array as $key => $val) {
			$array[$key] = is_array($val) ? $this->unstrip_array($val) : stripslashes($val);
		}

		return $array;
	}

	/* copy row to memory */
	function copy_row()
	{
		$content = [];
		if (isset($_POST['row'])) {
			$row_decoded = visualEditor::json_decode($_POST['row']);

			$elements = $this->unstrip_array(visualEditor::json_decode($_POST['element']));
			$subelements = $this->unstrip_array(visualEditor::json_decode($_POST['subelement']));

			$content = $row_decoded;
			if ($row_decoded['content']) {
				foreach ($row_decoded['content'] as $ckey => $col) {
					$content['content'][$ckey]['content'] = [];
					if (isset($elements[$ckey])) {
						$i = 0;
						foreach ($elements[$ckey] as $element) {
							if ($element) {
								$content['content'][$ckey]['content'][$i] = $element;
								// if subelement
								if ($content['content'][$ckey]['content'][$i]['type'] == 'twocols' || $content['content'][$ckey]['content'][$i]['type'] == 'box') {
									$content['content'][$ckey]['content'][$i]['content'] = [];
									//first col
									if (isset($subelements[$ckey][$i][0]) && is_array($subelements[$ckey][$i][0])) {
										foreach ($subelements[$ckey][$i][0] as $subelement) {
											if ($subelement) {
												$content['content'][$ckey]['content'][$i]['content'][0][] = $subelement;
											}
										}
									}
									//second col
									if (isset($subelements[$ckey][$i][1]) && is_array($subelements[$ckey][$i][1])) {
										foreach ($subelements[$ckey][$i][1] as $subelement) {
											if ($subelement) {
												$content['content'][$ckey]['content'][$i]['content'][1][] = $subelement;
											}
										}
									}
								}
							}
							$i++;
						}
					}
				}
			}
		}

		$_SESSION['ve_copy_row'] = $content;
		//echo '<div style="text-align: center; padding: 30px;">' . __('Řádek byl zkopírován do paměti, nyní jej můžete vložit na jakoukoli stránku.', 'cms_ve') . '</div>';

		die();
	}

	function clear_clipboard()
	{
		unset($_SESSION['ve_copy_row']);
		die();
	}

	// function for finding not duplicate names
	function new_nodup_name($name, $existing_pages, $after = '', $i = 1)
	{
		foreach ($existing_pages as $p) {
			if ($p->post_title == $name . $after) {
				$i++;
				$after = ' (' . $i . ')';

				return $name = $this->new_nodup_name($name, $existing_pages, $after, $i);
			}
		}

		return $name . $after;
	}

	/*
	function create_row_set($template_set) { /*
	$this->template_visual_setting=array(
	'dark_bg'=>'#158ebf',
	'text_color'=>'#cad6db'
	);
	return wp_parse_args( $this->template_visual_setting, $template_set  );
	}*/

	function open_new_shortcode_setting()
	{
		$this->open_new_setting($this->shortcodes);
		die();
	}

	function open_new_setting($items)
	{
		global $wpdb;
		$style = [];

		foreach ($items[$_POST['type']]['setting'] as $el_style) {
			$style[$el_style['id']] = $el_style['content'] ?? '';
		}

		$element = [
			'type' => $_POST['type'],
			'style' => $style,
		];

		echo '<div class="mw_admin_setting_container mw_setting_padding_content">';
		write_meta($items[$element['type']]['setting'], $element, 've_style', 've_style', '', 've');
		echo '</div>';

		?>

		<input type="hidden" name="element_type" value="<?php echo $element['type'] ?>"/>
		<?php
	}

	function open_shortcode_select()
	{
		$this->item_shortcode_selector($this->shortcodes, $this->shortcode_groups);
	}


	// selector for elements and shortcodes
	function item_shortcode_selector($items, $groups)
	{
		foreach ($groups as $key => $group) {
			echo '<div class="add_shortcode_items">';
			if (count($group['elements'])) {
				foreach ($group['elements'] as $el_key) {
					if (isset($items[$el_key])) {
						?>
						<a class="add_shortcode_item_c open_new_shortcode_setting add_type add_type_<?php echo $el_key; ?>"
						   data-desc="<?php echo $items[$el_key]['description'] ?? ''; ?>"
						   data-type="<?php echo $el_key; ?>"
						   href="#">
							<div class="add_shortcode_item">
						<?php if (isset($items[$el_key]['icon'])) {
							echo '<img src="' . $items[$el_key]['icon'] . '" title="" alt="" />';
						} else { ?>
									<i>
										<svg role="img">
											<use
												xlink:href="<?php echo MW_UI_ICONS_URL; ?>shortcodes.svg#element-<?php echo $el_key; ?>"></use>
										</svg>
									</i>
						<?php } ?>
								<span><?php echo $items[$el_key]['name']; ?></span>
							</div>
						</a>
						<?php
					}
				}
			}
			echo '<div class="cms_clear"></div></div>';
		}
		die();
	}

	/* Styles ********
	*******************************************************************************  */

	function print_styles($styles, $element, $mode = 'inline')
	{
		if ($this->edit_mode) {
			if ($mode == 'inline') {
				return $styles ? 'style="' . $this->generate_style_atribut($styles) . '"' : ''; //inline styles
			}

			return $styles ? '<style>' . $element . '{' . $this->generate_style_atribut($styles) . '}' . '</style>' : ''; //<style>styles</style>
		}

		if (isset($this->styles[$element])) {
			$this->styles[$element] .= $this->generate_style_atribut($styles);
		} else {
			$this->styles[$element] = $this->generate_style_atribut($styles);
		}

		return '';
	}

	function print_styles_array($styles_array, $id = '')
	{
		$content = '';
		if ($this->edit_mode) {
			$content = '<style ' . ($id ? 'id="' . $id . '"' : '') . '>';
			foreach ($styles_array as $styles) {
				$content .= $styles['styles'] ? $styles['element'] . '{' . $this->generate_style_atribut($styles['styles']) . '}' : '';
			}
			$content .= '</style>';
		} else {
			foreach ($styles_array as $styles) {
				if (isset($this->styles[$styles['element']])) {
					$this->styles[$styles['element']] .= $this->generate_style_atribut($styles['styles']);
				} else {
					$this->styles[$styles['element']] = $this->generate_style_atribut($styles['styles']);
				}
			}
		}

		return $content;
	}

	function add_style($element, $styles, $mobile = false)
	{
		if ($mobile) {
			if (isset($this->mobile_styles[$mobile]) && isset($this->mobile_styles[$mobile][$element])) {
				$this->mobile_styles[$mobile][$element] .= $this->generate_style('inline', $styles);
			} else {
				$this->mobile_styles[$mobile][$element] = $this->generate_style('inline', $styles);
			}
		} else {
			if (isset($this->styles[$element])) {
				$this->styles[$element] .= $this->generate_style('inline', $styles);
			} else {
				$this->styles[$element] = $this->generate_style('inline', $styles);
			}
		}
	}

	function add_styles($styles, $mobile = false)
	{
		foreach ($styles as $element => $style) {
			$this->add_style($element, $style, $mobile);
		}
	}

	function generate_style_atribut($row_style, $element = 'row')
	{
		$style = '';
		$style .= $this->generate_style('inline', [
			'bg' => $row_style,
			'background-color' => $row_style['background-color'] ?? '',
			'background-position' => $row_style['background-position'] ?? '',
			'background-image' => isset($row_style['background-image']) && $row_style['background-image'] ? 'url(' . $row_style['background-image'] . ')' : '',
			'background-attachment' => isset($row_style['background-attachment']) && $row_style['background-attachment'] ? $row_style['background-attachment'] : '',
			'border-color' => $row_style['border-color'] ?? '',
			'border-top-color' => $row_style['border-top-color'] ?? '',
			'border-bottom-color' => $row_style['border-bottom-color'] ?? '',
			'padding-bottom' => isset($row_style['padding_bottom']) ? $row_style['padding_bottom'] . 'px' : '',
			'margin-bottom' => isset($row_style['margin_bottom']) ? $row_style['margin_bottom'] . 'px' : '',
			'margin-top' => isset($row_style['margin_top']) ? $row_style['margin_top'] . 'px' : '',
			'margin-left' => isset($row_style['margin_left']) ? $row_style['margin_left'] . 'px' : '',
			'margin-right' => isset($row_style['margin_right']) ? $row_style['margin_right'] . 'px' : '',
			'font' => $row_style['font'] ?? '',
			'text-align' => $row_style['align'] ?? '',
			'shadow' => $row_style['shadow'] ?? '',
			'box-shadow' => $row_style['box-shadow'] ?? [],
			'shadow_color' => $row_style['shadow_color'] ?? '',
			'text-shadow' => isset($row_style['font']) && isset($row_style['font']['text-shadow']) ? $row_style['font']['text-shadow'] : '',
			'color' => $row_style['color'] ?? '',
			'fill' => $row_style['fill'] ?? '',
			//'padding-top' => (isset($row_style['padding_top'])) ? $row_style['padding_top'] . "px" : '',
			'top' => isset($row_style['top']) ? $row_style['top'] . 'px' : '',
			'right' => isset($row_style['right']) ? $row_style['right'] . 'px' : '',
			'bottom' => isset($row_style['bottom']) ? $row_style['bottom'] . 'px' : '',
			'position' => $row_style['position'] ?? '',
			'width' => $row_style['width'] ?? '',
			'min-width' => $row_style['min-width'] ?? '',
			'height' => isset($row_style['height']) ? $row_style['height'] . 'px' : '',
			'min-height' => isset($row_style['min-height']) ? $row_style['min-height'] . 'px' : '',
			'max-width' => isset($row_style['max-width']) ? $row_style['max-width'] . 'px' : '',
			'font-weight' => isset($row_style['font-weight']) ? $row_style['font-weight'] . '' : '',
			'corner' => isset($row_style['corner']) && $row_style['corner'] > 0 ? $row_style['corner'] . 'px' : '',
			'padding' => $row_style['padding'] ?? '',
			'paddingem' => $row_style['paddingem'] ?? '',
			'paddingc' => $row_style['paddingc'] ?? '',
			'padding-left' => $row_style['padding-left'] ?? (isset($row_style['padding_left']) && !is_array($row_style['padding_left']) ? $row_style['padding_left'] . 'px' : ''),
			'padding-right' => $row_style['padding-right'] ?? (isset($row_style['padding_right']) && !is_array($row_style['padding_right']) ? $row_style['padding_right'] . 'px' : ''),
			'padding-bottom' => $row_style['padding-bottom'] ?? (isset($row_style['padding_bottom']) && !is_array($row_style['padding_bottom']) ? $row_style['padding_bottom'] . 'px' : ''),
			'padding-top' => $row_style['padding-top'] ?? (isset($row_style['padding_top']) && !is_array($row_style['padding_top']) ? $row_style['padding_top'] . 'px' : ''),
			'opacity' => $row_style['opacity'] ?? '',
		]);

		if (isset($row_style['border-bottom']) && isset($row_style['border-bottom']['size'])) {
			$style .= $this->generate_style('inline', [
				'border-bottom' => $row_style['border-bottom'],
			]);
		}
		if (isset($row_style['border-top']) && isset($row_style['border-top']['size'])) {
			$style .= $this->generate_style('inline', [
				'border-top' => $row_style['border-top'],
			]);
		}
		if (isset($row_style['border']) && isset($row_style['border']['size'])) {
			$style .= $this->generate_style('inline', [
				'border' => $row_style['border'],
			]);
		}

		return $style;
	}


	function generate_style($selector, $styles)
	{
		$css = '';
		if ($selector != 'inline') {
			$css .= $selector . '{';
		}
		foreach ($styles as $key => $style) {
			if ($style && $style != 'px' && $style != ' !important') {
				if ($key == 'bg') {
					if (isset($style['background_image']['image']) && $style['background_image']['image']) {
						$background_image = substr($style['background_image']['image'], 0, 4) == 'http' ? $style['background_image']['image'] : home_url() . $style['background_image']['image'];
						if (isset($style['background_color']['color1']) && $style['background_color']['color1']) {
							$css .= 'background-color: ' . $style['background_color']['color1'] . ';';
						} elseif (isset($style['background_color']['color2']) && $style['background_color']['color2']) {
							$css .= 'background-color: ' . $style['background_color']['color2'] . ';';
						}

						$css .= 'background-image: url(' . $background_image . '); background-position: ' . $style['background_image']['position'] . '; background-repeat: ' . $style['background_image']['repeat'] . ';';

						if (isset($style['background_image']['cover']) && $style['background_image']['cover']) {
							$css .= "-webkit-background-size: cover;
                          -moz-background-size: cover;
                          -o-background-size: cover;
                          background-size: cover;
                          -ms-filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $background_image . "',sizingMethod='scale');
                          filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $background_image . "', sizingMethod='scale');
                          height: auto;";
						}
						if (isset($style['background_image']['fixed']) && $style['background_image']['fixed']) {
							$css .= 'background-attachment: fixed;';
						}
						if (isset($style['background_image']['fixed']) && $style['background_image']['fixed'] && isset($style['background_image']['cover']) && $style['background_image']['cover']) {
							//$css.='background-size: 100%;';
						}
					} elseif (isset($style['background_image']['pattern']) && $style['background_image']['pattern']) {
						global $mwContainer;
						$css .= 'background-image: url(' . $mwContainer->list['patterns'][$style['background_image']['pattern']] . $style['background_image']['pattern'] . '_p.png);';
					} elseif (isset($style['background_color']['color1']) && $style['background_color']['color1'] && isset($style['background_color']['color2']) && $style['background_color']['color2']) {
						if (isset($style['background_color']['transparency']) && $style['background_color']['transparency'] < 1) {
							$color1 = Colors::hex2rgba($style['background_color']['color1'], $style['background_color']['transparency']);
							$color2 = Colors::hex2rgba($style['background_color']['color2'], $style['background_color']['transparency']);
							$ie_color1 = str_replace('#', '#' . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color1']);
							$ie_color2 = str_replace('#', '#' . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color2']);
						} else {
							$ie_color1 = $color1 = $style['background_color']['color1'];
							$ie_color2 = $color2 = $style['background_color']['color2'];
						}

						$css .= 'background: linear-gradient(to bottom, ' . $color1 . ' 0%, ' . $color2 . ' 100%) no-repeat border-box;';
						$css .= 'background: -moz-linear-gradient(top,  ' . $color1 . ',  ' . $color2 . ') no-repeat border-box;';
						$css .= 'background: -webkit-gradient(linear, left top, left bottom, from(' . $color1 . '), to(' . $color2 . ')) no-repeat border-box;';
						$css .= "filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $ie_color1 . "', endColorstr='" . $ie_color2 . "');";
					} elseif (isset($style['background_color']['color1']) && $style['background_color']['color1']) {
						if (isset($style['background_color']['transparency']) && $style['background_color']['transparency'] < 1) {
							$css .= 'background: ' . Colors::hex2rgba($style['background_color']['color1'], $style['background_color']['transparency']) . ';';
							$css .= 'filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=' . str_replace('#', '#' . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color1']) . ', endColorstr=' . str_replace('#', '#' . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color1']) . ');';
							$css .= 'zoom:1;';
						} else {
							$css .= 'background: ' . $style['background_color']['color1'] . ';';
						}
					} elseif (isset($style['background_color']['color2']) && $style['background_color']['color2']) {
						if (isset($style['background_color']['transparency']) && $style['background_color']['transparency'] < 1) {
							$css .= 'background: ' . Colors::hex2rgba($style['background_color']['color2'], $style['background_color']['transparency']) . ';';
							$css .= 'filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=' . str_replace('#', '#' . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color2']) . ', endColorstr=' . str_replace('#', '#' . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color2']) . ');';
							$css .= 'zoom:1;';
						} else {
							$css .= 'background: ' . $style['background_color']['color2'] . ';';
						}
					}
				} elseif ($key == 'font') {
					if (isset($style['font-size']) && $style['font-size'] != '') {
						$css .= 'font-size: ' . $style['font-size'] . 'px;';
					}
					if (isset($style['font-family']) && $style['font-family']) {
						$css .= "font-family: '" . $style['font-family'] . "';";
					}
					if (isset($style['color']) && $style['color']) {
						$css .= 'color: ' . $style['color'] . ';';
					}
					if (isset($style['weight']) && $style['weight']) {
						$css .= 'font-weight: ' . $style['weight'] . ';';
					}
					if (isset($style['align']) && $style['align']) {
						$css .= 'text-align: ' . $style['align'] . ';';
					}
					if (isset($style['line-height']) && $style['line-height']) {
						$css .= 'line-height: ' . $style['line-height'] . 'em;';
					}
					if (isset($style['letter-spacing']) && $style['letter-spacing']) {
						$css .= 'letter-spacing: ' . $style['letter-spacing'] . 'px;';
					}
				} elseif ($key == 'text-shadow') {
					if ($style == 'dark') {
						$css .= 'text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5); ';
					} elseif ($style == 'light') {
						$css .= 'text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.5); ';
					}
				} elseif ($key == 'box-shadow') {
					if (isset($style['size']) && $style['size']) {
						$css .= '-webkit-box-shadow: ' . $style['horizontal'] . 'px ' . $style['vertical'] . 'px ' . $style['size'] . 'px 0 rgba(0, 0, 0, ' . ($style['transparency'] / 100) . ');
                    -moz-box-shadow: ' . $style['horizontal'] . 'px ' . $style['vertical'] . 'px ' . $style['size'] . 'px 0 rgba(0, 0, 0, ' . ($style['transparency'] / 100) . ');
                    box-shadow: ' . $style['horizontal'] . 'px ' . $style['vertical'] . 'px ' . $style['size'] . 'px 0 rgba(0, 0, 0, ' . ($style['transparency'] / 100) . '); ';
					}
				} elseif ($key == 'corner') {
					if ($style) {
						$css .= '-moz-border-radius: ' . $style . ';
                -webkit-border-radius: ' . $style . ';
                -khtml-border-radius: ' . $style . ';
                border-radius: ' . $style . ';';
					}
				} elseif ($key == 'padding') {
					if ($style) {
						$css .= 'padding: ' . $style['top'] . 'px ' . $style['right'] . 'px ' . $style['bottom'] . 'px ' . $style['left'] . 'px;';
					}
				} elseif ($key == 'paddingem') {
					if ($style) {
						$css .= 'padding: ' . $style['top'] . 'em ' . $style['right'] . 'em ' . $style['bottom'] . 'em ' . $style['left'] . 'em;';
					}
				} elseif ($key == 'paddingc') {
					if ($style) {
						$css .= 'padding: ' . $style['top'] . ' ' . $style['right'] . ' ' . $style['bottom'] . ' ' . $style['left'] . ';';
					}
				} elseif ($key == 'border-top' || $key == 'border-bottom' || $key == 'border') {
					$css .= $key . ': ' . $style['size'] . 'px ' . ($style['style'] ?? 'solid') . ' ' . $style['color'] . ';';
				} elseif ($key == 'opacity') {
					if (!empty($style)) {
						$css .= 'zoom: 1;
                  filter: alpha(opacity=' . $style . ');
                  opacity: ' . ($style / 100) . ';';
					}
				} else {
					$css .= $key . ': ' . $style . ';';
				}
			}
		}
		if ($selector != 'inline') {
			$css .= '} ';
		}

		return $css;
	}

	/* Class functions
	***************************************************************************** */

	function print_form($element, $form, $css_id)
	{
		$content = '';

		$button_text = $element['style']['button_text'] ?? '';

		$fields = $form['fields'];

		$class = $form['class'] ?? null;
		$form_class = 've_check_form ve_content_form ve_form_input_style_' . $element['style']['form-look'] . ' ve_form_style_' . $element['style']['form-style'] . ((bool) $class ? ' ' . $class : '');
		if (isset($element['style']['corners'])) {
			$form_class .= ' ve_form_corners_' . $element['style']['corners'];
		}

		$action = 'action="' . $form['url'] . '"';

		if (!$this->edit_mode) {
			$form_class .= ' ve_content_form_antispam';
			$action = 'action="" data-action="' . $form['url'] . '"';

			if ($this->modul_type == 'funnel') {
				$form_class .= ' mw_funnel_contact_conversion';
				$action .= ' data-funnel="' . MWF()->current_funnel->id . '"';
			}
		}

		$content = '<form id="' . str_replace('#', '', $css_id) . '_form" ' . $action . ' method="post" class="' . $form_class . '" >';

		foreach ($fields as $key => $input) {
			$class = '';
			if (isset($input['customfield_type']) && $input['customfield_type'] != 'radio' && $input['customfield_type'] != 'checkbox' && $input['customfield_type'] != 'bool' && $input['customfield_type'] != 'agree') {
				$class .= ' ve_form_field';
				$class .= Colors::isLightColor($element['style']['background']) ? ' light_color' : ' dark_color';
			}

			if ($input['required']) {
				$class .= ' ve_form_required';
				$input['label'] .= '*';
			}
			if ($key == 'df_emailaddress' || $key == 'field[df_emailaddress]' || (isset($input['email']) && $input['email']) || (isset($input['customfield_type']) && $input['customfield_type'] == 'email')) {
				$class .= ' ve_form_email ve_form_field';
				if (is_user_logged_in() && !$this->edit_mode) {
					$current_user = wp_get_current_user();
					$input['content'] = $current_user->user_email;
				}
				if (isset($_GET['email'])) {
					$input['content'] = esc_attr($_GET['email']);
				}
			}
			$errorm = isset($input['errormessage']) && $input['errormessage'] ? 'data-errorm="' . $input['errormessage'] . '"' : '';

			$hidden_field = isset($input['customfield_type']) && ($input['customfield_type'] == 'hidden' || $input['customfield_type'] == 'antispam');

			//content from url
			if (isset($_GET[$input['fieldname']])) {
				$input['content'] = esc_attr($_GET[$input['fieldname']]);
			}

			if (!$hidden_field) {
				$form_row_class = 've_form_row_' . $input['fieldname'];
				if (isset($input['customfield_type']) && $input['customfield_type']) {
					$form_row_class .= ' ve_form_row_type_' . $input['customfield_type'];
				}
				$content .= '<div class="ve_form_row ' . $form_row_class . '">';
			}

			if (isset($element['style']['form-labels']) && $element['style']['form-labels'] == '2' && $input['label'] && (!isset($input['customfield_type']) || ($input['customfield_type'] != 'bool' && $input['customfield_type'] != 'agree'))) {
				$content .= '<div class="ve_form_label" >' . $input['label'] . '</div>';
				$input['label'] = '';
			}

			if (!isset($input['customfield_type'])) {
				if ($input['defaultfield'] == 'notes') {
					$content .= '<textarea class="ve_form_text' . $class . '" name="' . $input['fieldname'] . '" ' . $errorm . ' placeholder="' . $input['label'] . '"></textarea>';
				} elseif ($input['defaultfield'] == 'birthday') {
					$content .= '<input class="ve_form_text ' . $class . '" type="date" name="' . $input['fieldname'] . '" ' . $errorm . ' value="" placeholder="' . $input['label'] . '" />';
				} else {
					$content .= '<input class="ve_form_text ' . $class . '" type="text" name="' . $input['fieldname'] . '" ' . $errorm . ' value="' . ($input['content'] ?? '') . '" placeholder="' . $input['label'] . '" />';
				}
			} else {
				switch ($input['customfield_type']) {
					case 'select':
						$content .= '<select class="ve_form_text ' . $class . '" data-errorm="' . __('Vyberte prosím jednu z možností.', 'cms_ve') . '" name="' . $input['fieldname'] . '" value="" placeholder="' . $input['label'] . '">';
						if ($input['label']) {
							$content .= '<option value="">' . $input['label'] . '</option>';
						}
						$foreach = $input['options']['item'] ?? $input['options'];
						foreach ($foreach as $option) {
							$content .= '<option value="' . $option['id'] . '" ' . (isset($input['content']) && $input['content'] == $option['id'] ? 'selected="selected"' : '') . '>' . $option['name'] . '</option>';
						}
						$content .= '</select>';

						break;
					case 'radio':
						if ($input['label']) {
							$content .= '<div class="ve_form_label" >' . $input['label'] . '</div>';
						}
						if ($input['required']) {
							$content .= '<div class="ve_form_radio_container ' . $class . '" ' . $errorm . '>';
						}
						$i = 1;
						if (isset($input['options']['item'])) {
							foreach ($input['options']['item'] as $option) {
								$content .= '<div class="ve_form_option_row"><label><input type="radio" name="' . $input['fieldname'] . '" value="' . $option['id'] . '" />' . $option['name'] . '</label></div>';
								$i++;
							}
						}
						if ($input['required']) {
							$content .= '</div>';
						}

						break;
					case 'checkbox':
						if ($input['label']) {
							$content .= '<div class="ve_form_label" >' . $input['label'] . '</div>';
						}
						$foreach = isset($input['options']['item'][0]) ? $input['options']['item'] : $input['options'];
						if ($input['required']) {
							$content .= '<div class="ve_form_checkbox_container ' . $class . '" ' . $errorm . '>';
						}
						foreach ($foreach as $option) {
							$content .= '<div class="ve_form_option_row"><label><input type="checkbox" name="' . $input['fieldname'] . '[]" value="' . $option['id'] . '" />' . $option['name'] . '</label></div>';
						}
						if ($input['required']) {
							$content .= '</div>';
						}

						break;
					case 'textarea':
						$content .= '<textarea class="ve_form_text' . $class . '" ' . $errorm . ' name="' . $input['fieldname'] . '" placeholder="' . $input['label'] . '"></textarea>';

						break;
					case 'hidden':
						$content .= '<input type="hidden" name="' . $input['fieldname'] . '" value="' . ($input['content'] ?? '') . '" />';

						break;
					case 'antispam':
						$content .= '<div class="ve_nodisp"><input type="text" name="' . $input['fieldname'] . '" value="' . ($input['content'] ?? '') . '" /></div>';

						break;
					case 'date':
						$content .= '<input class="ve_form_text ' . $class . '" type="date" name="' . $input['fieldname'] . '" ' . $errorm . ' value="" placeholder="' . $input['label'] . '" />';

						break;
					case 'bool':
						$content .= '<div class="ve_form_option_row"><label><input type="checkbox" class="ve_form_checkbox ' . $class . '" name="' . $input['fieldname'] . '" ' . $errorm . ' value="1" />' . $input['label'] . '</label></div>';

						break;
					case 'agree':
						$content .= '<div class="ve_form_option_row_agree"><label><input type="checkbox" class="ve_form_checkbox ' . $class . '" name="' . $input['fieldname'] . '" ' . $errorm . ' value="(' . __('Souhlasím', 'cms_ve') . ')" />' . $input['label'] . '</label></div>';

						break;
					case 'password':
						$content .= '<input class="ve_form_text' . $class . '" ' . $errorm . ' type="password" name="' . $input['fieldname'] . '" value="" placeholder="' . $input['label'] . '" />';

						break;
					default:
						$content .= '<input class="ve_form_text' . $class . '" ' . $errorm . ' type="text" name="' . $input['fieldname'] . '" value="" placeholder="' . $input['label'] . '" />';

						break;
				}
			}
			if (!$hidden_field) {
				$content .= '</div>';
			}
		}

		// Consent texts
		$consentTexts = $form['consent_texts'] ?? [];
		if ($consentTexts) {
			$content .= '<div class="ve_consent_texts ve_form_row">';
			foreach ($consentTexts as $consentText) {
				$content .= '<div class="ve_consent_text">' . $consentText . '</div>';
			}
			$content .= '</div>';
		}

		if (!$button_text) {
			$button_text = $form['submit'];
		}

		$but_set = [
			'style' => $element['style']['button'] ?? null,
			'text' => $button_text,
			'tag' => 'button',
			'attrs' => 'type="submit"',
		];

		$content .= '<div class="ve_form_button_row">';
		$content .= Button::createButton(
				$but_set,
				$this->display->element_css,
				've_form_button',
				$css_id . '_form .ve_form_button_row .ve_form_button',
		);
		$content .= '</div>';

		$content .= '</form>';

		return $content;
	}

	function print_seform($element, $form, $css_id)
	{
		if (!isset($element['style']['button']['height'])) {
			$element['style']['button']['height'] = '';
		}

		$button_text = $element['style']['button_text'] ?? '';

		$form_class = 've_check_form ve_content_form ve_form_input_style_' . $element['style']['form-look'] . ' ve_form_style_' . $element['style']['form-style'];
		if (isset($element['style']['corners'])) {
			$form_class .= ' ve_form_corners_' . $element['style']['corners'];
		}
		$action = 'action="' . $form['url'] . '"';
		if (!$this->edit_mode) {
			$form_class .= ' ve_content_form_antispam';
			$action = 'action="" data-action="' . $form['url'] . '"';

			if ($this->modul_type == 'funnel') {
				$form_class .= ' mw_funnel_contact_conversion';
				$action .= ' data-funnel="' . MWF()->current_funnel->id . '"';
			}
		}

		$content = '<form id="' . str_replace('#', '', $css_id) . '_form" ' . $action . ' method="post" class="' . $form_class . '" ' . ($form['submit_in_new_window'] ? 'target="_blank"' : '') . '>';

		//print_r($form['fields']);
		foreach ($form['fields'] as $key => $input) {
			$class = '';
			if (isset($input['html_input_type']) && $input['html_input_type'] != 'radio' && $input['html_input_type'] != 'checkbox' && $input['html_input_type'] != 'bool' && $input['html_input_type'] != 'agree') {
				$class .= ' ve_form_field';
				$class .= Colors::isLightColor($element['style']['background']) ? ' light_color' : ' dark_color';
			}

			if (isset($input['is_required']) && $input['is_required']) {
				$class .= ' ve_form_required';
				$input['label'] .= '*';
			}

			if ($input['html_input_name'] == 'df_emailaddress' || $input['html_input_name'] == 'field[df_emailaddress]') {
				$class .= ' ve_form_email';
				if (is_user_logged_in() && !$this->edit_mode) {
					$current_user = wp_get_current_user();
					$input['content'] = $current_user->user_email;
				}
				if (isset($_GET['email'])) {
					$input['content'] = esc_attr($_GET['email']);
				}
			}

			$errorm = isset($input['error_message']) && $input['error_message'] ? 'data-errorm="' . $input['error_message'] . '"' : '';

			//content from url
			if (isset($_GET[$input['html_input_name']])) {
				$input['content'] = esc_attr($_GET[$input['html_input_name']]);
			}

			$hidden_field = false;
			if ($input['html_input_type'] == 'hidden') {
				$hidden_field = true;
			}

			if (!$hidden_field) {
				$content .= '<div class="ve_form_row ve_form_row_' . $input['html_input_name'] . '">';
			}

			if (isset($element['style']['form-labels']) && $element['style']['form-labels'] == '2' && $input['label'] && ($input['html_input_type'] != 'checkbox' || isset($input['options']))) {
				$content .= '<div class="ve_form_label" >' . $input['label'] . '</div>';
				$input['label'] = '';
			}

			if ($input['html_input_type'] == 'select') {
				if ($input['label'] && (!isset($input['is_required']) || !$input['is_required'])) {
					$content .= '<div class="ve_form_label" >' . $input['label'] . '</div>';
					$input['label'] = '';
				}
				$content .= '<select class="ve_form_text ' . $class . '" ' . $errorm . ' name="' . $input['html_input_name'] . '" value="" placeholder="' . $input['label'] . '">';
				if ($input['label']) {
					$content .= '<option value="">' . $input['label'] . '</option>';
				}
				$foreach = isset($input['options']['item'][0]) ? $input['options']['item'] : $input['options'];
				foreach ($foreach as $oid => $option) {
					$content .= '<option value="' . $option['value'] . '" ' . (isset($input['content']) && $input['content'] == $option['value'] ? 'selected="selected"' : '') . '>' . $option['label'] . '</option>';
				}
				$content .= '</select>';
			} elseif ($input['html_input_type'] == 'radio') {
				if ($input['label']) {
					$content .= '<div class="ve_form_label" >' . $input['label'] . '</div>';
				}
				if (isset($input['is_required']) && $input['is_required']) {
					$content .= '<div class="ve_form_radio_container ' . $class . '" ' . $errorm . '>';
				}
				$i = 1;
				foreach ($input['options'] as $oid => $option) {
					$content .= '<div class="ve_form_option_row"><label><input type="radio" name="' . $input['html_input_name'] . '" value="' . $option['value'] . '" />' . $option['label'] . '</label></div>';
					$i++;
				}
				if (isset($input['is_required']) && $input['is_required']) {
					$content .= '</div>';
				}
			} elseif ($input['html_input_type'] == 'checkbox') {
				if (isset($input['is_required']) && $input['is_required']) {
					$content .= '<div class="ve_form_checkbox_container ' . $class . '" ' . $errorm . '>';
				}

				if (isset($input['options'])) {
					if ($input['label']) {
						$content .= '<div class="ve_form_label" >' . $input['label'] . '</div>';
					}
					foreach ($input['options'] as $oid => $option) {
						$content .= '<div class="ve_form_option_row"><label><input type="checkbox" name="' . $input['html_input_name'] . '[]" value="' . $option['value'] . '" />' . $option['label'] . '</label></div>';
					}
				} else {
					$content .= '<label><input type="checkbox" name="' . $input['html_input_name'] . '" value="1" />' . $input['label'] . '</label>';
				}

				if (isset($input['is_required']) && $input['is_required']) {
					$content .= '</div>';
				}
			} elseif ($input['html_input_type'] == 'textarea' || $input['html_input_name'] == 'df_notes' || $input['html_input_name'] == 'field[df_notes]') {
				$content .= '<textarea class="ve_form_text' . $class . '" ' . $errorm . ' name="' . $input['html_input_name'] . '" placeholder="' . $input['label'] . '">' . ($input['content'] ?? '') . '</textarea>';
			} elseif ($input['html_input_type'] == 'date') {
				$content .= '<input class="ve_form_text ' . $class . '" type="date" name="' . $input['html_input_name'] . '" ' . $errorm . ' value="" placeholder="' . $input['label'] . '" />';
			} elseif ($input['html_input_type'] == 'hidden') {
				$content .= '<input type="hidden" name="' . $input['html_input_name'] . '" value="' . ($input['content'] ?? '') . '" />';
			} elseif ($input['html_input_type'] == 'number') {
				$content .= '<input class="ve_form_text ve_form_number ' . $class . '" ' . $errorm . ' type="text" name="' . $input['html_input_name'] . '" value="' . ($input['content'] ?? '') . '" placeholder="' . $input['label'] . '" />';
			} else {
				$content .= '<input class="ve_form_text' . $class . '" ' . $errorm . ' type="text" name="' . $input['html_input_name'] . '" value="' . ($input['content'] ?? '') . '" placeholder="' . $input['label'] . '" />';
			}

			if (!$hidden_field) {
				$content .= '</div>';
			}
		}

		$purposes = '';
		if (isset($form['purposes']) && !empty($form['purposes'])) {
			//primary
			foreach ($form['purposes'] as $key => $purpose) {
				if ($purpose['checkbox_label'] && $purpose['is_primary']) {
					$purposes .= '<div class="ve_form_purpose_row"><span>';
					$purposes .= $purpose['checkbox_label'];
					if ($purpose['link_href']) {
						$purposes .= ' <a href="' . $purpose['link_href'] . '" target="_blank">' . $purpose['link_label'] . '</a>';
					}
					$purposes .= '</span></div>';
				}
			}
			foreach ($form['purposes'] as $key => $purpose) {
				if ($purpose['checkbox_label'] && !$purpose['is_primary']) {
					$purposes .= '<div class="ve_form_purpose_row"><label>';
					$purposes .= '<input type="checkbox" name="' . $purpose['html_input_name'] . '" value="1" />';
					$purposes .= $purpose['checkbox_label'];
					if ($purpose['link_href']) {
						$purposes .= ' <a href="' . $purpose['link_href'] . '" target="_blank">' . $purpose['link_label'] . '</a>';
					}
					$purposes .= '</label></div>';
				}
			}

			if ($purposes && $element['style']['form-style'] == '1') {
				$content .= '<div class="ve_form_purposes_container">' . $purposes . '</div>';
			}
		}

		if (!$button_text) {
			$button_text = $form['submit'];
		}

		$but_set = [
			'style' => $element['style']['button'] ?? null,
			'text' => $button_text,
			'tag' => 'button',
			'attrs' => 'type="submit"',
		];

		$content .= '<div class="ve_form_button_row">';
		$content .= Button::createButton(
				$but_set,
				$this->display->element_css,
				've_form_button',
				$css_id . '_form .ve_form_button_row .ve_form_button',
				false,
				$this->edit_mode
		);
		$content .= '</div>';

		// purposes for table
		if ($purposes && $element['style']['form-style'] == '2') {
			$content .= '<div class="ve_form_purposes_container">' . $purposes . '</div>';
		}

		// antispam
		$content .= '<div class="field-shift" aria-label="Please leave the following three fields empty" style="left: -9999px; position: fixed;">
                <label for="b_name">Name: </label>
                <input tabindex="-1" value="" placeholder="Freddie" id="b_name" type="text" name="b_name" autocomplete="' . wp_generate_password(12, false) . '">
                <label for="b_email">Email: </label>
                <input type="email" tabindex="-1" value="" placeholder="youremail@gmail.com" id="b_email" name="b_email" autocomplete="' . wp_generate_password(12, false) . '">
                <label for="b_comment">Comment: </label>
                <textarea tabindex="-1" placeholder="Please comment" id="b_comment" name="b_comment" autocomplete="' . wp_generate_password(12, false) . '"></textarea>
            </div>';

		$content .= '</form>';

		return $content;
	}

	function add_set_field($type, $array)
	{
		global $mwContainer;
		$mwContainer->add_set_field($type, $array);
	}

	function add_element_groups($groups, $top = false)
	{
		global $mwContainer;
		$mwContainer->add_element_groups($groups, $top);
	}

	function add_elements($elements, $group, $group_title = '')
	{
		global $mwContainer;
		$mwContainer->add_elements($elements, $group, $group_title);
	}

	function add_element_set($element, $sets, $order = 0, $tabsetting = 0, $tab = false)
	{
		global $mwContainer;
		$mwContainer->add_element_set($element, $sets, $order, $tabsetting, $tab);
	}

	function add_element_set_options($element, $set, $options, $order = 0)
	{
		global $mwContainer;
		$mwContainer->add_element_set_options($element, $set, $options, $order);
	}


	function add_rows($rows)
	{
		global $mwContainer;
		$mwContainer->add_rows($rows);
	}

	function add_shortcode_groups($groups)
	{
		$this->shortcode_groups = array_merge($this->shortcode_groups, $groups);
	}

	function add_shortcodes($shortcodes, $group, $group_title = '')
	{
		$this->shortcodes = array_merge($this->shortcodes, $shortcodes);
		if (!isset($this->shortcode_groups[$group])) {
			$this->shortcode_groups[$group]['elements'] = [];
			$this->shortcode_groups[$group]['name'] = $group_title;
		}
		foreach ($shortcodes as $key => $val) {
			$this->shortcode_groups[$group]['elements'][] = $key;
		}
	}

	function add_element_script($name, $script)
	{
		if ($this->edit_mode) {
			if (!isset($this->element_scripts[$name])) {
				return $script;
			}
		}
		$this->element_scripts[$name] = $script;
	}


	// add custom sizes to media library
	function display_custom_image_sizes($sizes)
	{
		global $_wp_additional_image_sizes;
		if (empty($_wp_additional_image_sizes)) {
			return $sizes;
		}

		foreach ($_wp_additional_image_sizes as $id => $data) {
			if (!isset($sizes[$id])) {
				if ($id == 'mio_columns_c1') {
					$sizes[$id] = __('Sloupec 1', 'cms_ve');
				}
				if ($id == 'mio_columns_c2') {
					$sizes[$id] = __('Sloupec 1/2', 'cms_ve');
				}
				if ($id == 'mio_columns_c3') {
					$sizes[$id] = __('Sloupec 1/3', 'cms_ve');
				}
				if ($id == 'mio_columns_c4') {
					$sizes[$id] = __('Sloupec 1/4', 'cms_ve');
				}
				if ($id == 'mio_columns_c5') {
					$sizes[$id] = __('Sloupec 1/5', 'cms_ve');
				}
			}
		}

		return $sizes;
	}

	function add_editable_type($type)
	{
		if ($this->builder_mode) {
			$this->builder->editable_type[] = substr($type, 0, 10);
		} else {
			$this->display->editable_type[] = substr($type, 0, 10);
		}
	}


	/* Web Actions
	***************************************************************************** */

	function send_contact_form($args)
	{
		$time = (int) current_time('timestamp') - (int) base64_decode($_POST['contact_sended']);

		// Input "fax" is honeypot for spambots
		$honeypotFilled = isset($_POST['fax']) && (bool) $_POST['fax'];
		if (!$_POST['contact_text'] || !$_POST['contact_email'] || $_POST['send_email'] || $honeypotFilled || $time <= 5) {
			$error = __('Zpráva se nepodařila odeslat.', 'cms_ve');
			if ($time <= 5) {
				$error .= __('Formulář byl odeslán příliš rychle (ochrana proti botům). Počkejte chvíli a pošlete jej znovu.', 'cms_ve');
			}


			wp_send_json(['sended' => 'error', 'message' => $error]);
			die();
		}

		// reCAPTCHA
		if (MWRecaptcha()->isActive() && !$this->edit_mode) {
			if (!isset($_POST[ReCaptchaValidator::FORM_TOKEN_PARAMETER])) {
				$error = __('Formulář se nepodařilo odeslat z důvodu interní chyby aplikace. Kontaktujte prosím správce webu e-mailem.', 'cms_ve');
				wp_send_json(['sended' => 'error', 'message' => $error]);
				die();
			}

			if (!MWRecaptcha()->validate($_POST[ReCaptchaValidator::FORM_TOKEN_PARAMETER])) {
				$error = __('Formulář se nepodařilo odeslat z důvodu ochrany proti botům (reCAPTCHA)', 'cms_ve');
				wp_send_json(['sended' => 'error', 'message' => $error]);
				die();
			}
		}

		$send_to = unserialize(base64_decode($_POST['data']));

		$message = $_POST['contact_text'];
		$message .= '<br />' . $_POST['contact_name'] . '<br />' . $_POST['contact_email'] . '<br />' . ($_POST['contact_phone'] ?? '');

		$redirect_url = $_POST['form_redirect_url'] ?? '';


		if (isset($_POST['mw_gdpr_consent'])) {
			$message .= '<br />' . $_POST['mw_gdpr_consent'];
		}

		$toEmail = $send_to['email'];
		if (!$toEmail || $toEmail === '@') {
			$error = __('Zpráva se nepodařila odeslat. Není nastavena emailová adresa, na kterou se má dotaz odeslat.', 'cms_ve');
			wp_send_json(['sended' => 'error', 'message' => $error]);
			die();
		}
		$user = isset($_POST['contact_name']) && $_POST['contact_name'] !== '' ? $_POST['contact_name'] : $send_to['email'];
		$webName = str_replace('&amp;', '&', get_bloginfo('name'));
		$subject = sprintf(__('Dotaz z webu %s od %s', 'cms_ve'), $webName, $user);
		$toEmail = explode(',', $toEmail);
		try {
			$result = miowebMailer()->send($toEmail, get_bloginfo('admin_email'), $webName, $subject, $message, $_POST['contact_email']);
		} catch (TooManyRecipientsException $e) {
			$error = __('Maximální počet příjemců je ', 'cms_ve') . $e->getLimit() . '.';
			wp_send_json(['sended' => 'error', 'message' => $error]);
			die();
		}

		if ($result) {
			do_action('mw_on_send_contact_form');
			wp_send_json([
					'sended' => 'ok',
					'message' => __('Zpráva byla úspěšně odeslána.', 'cms_ve'),
					'redirect' => $redirect_url,
			]);
		} else {
			global $phpmailer;

			$mail_error = ' ' . __('(Nefunkční funkce mail.)', 'cms_ve');

			if (isset($phpmailer)) {
				$mail_error = ' (' . $phpmailer->ErrorInfo . ')';
			}

			$error = __('Zpráva se nepodařila odeslat.', 'cms_ve') . $mail_error;
			wp_send_json(['sended' => 'error', 'message' => $error, 'redirect' => $redirect_url]);
		}

		die();
	}

	function action_wp_mail_failed($wp_error)
	{
		$message = $wp_error->get_error_message();
		mwlog(MWLS_GENERAL, $message, MWLL_WARNING);
	}

	function web_actions()
	{
		// send custom email
		if (isset($_POST['ve_customform_structure']) && $_POST['ve_customform_email'] == '') {
			$structure = unserialize(base64_decode($_POST['ve_customform_structure']));
			$content = '';
			$email = null;
			$error = '';
			$send = true;

			$time = (int) current_time('timestamp') - (int) $_POST['ve_sended_time'];
			if ($time <= 5) {
				$send = false;
				$error = 'time';
			}
			if (isset($_POST['ve_customform_email']) && !empty($_POST['ve_customform_email'])) {
				$send = false;
				$error = 'hidden_field';
			}

			foreach ($structure['form'] as $key => $field) {
				if (isset($field['required']) && (!isset($_POST['ve_custom_form_field_' . $key]) || $_POST['ve_custom_form_field_' . $key] === '')) {
					$send = false;
					$error = 'norequired';
				}

				if (isset($field['email']) && (isset($field['type']) && $field['type'] === 'text')) {
					$email = $_POST['ve_custom_form_field_' . $key];
				}
				$content .= $field['title'];

				if ($field['type'] === 'agree' && isset($field['agree_link_text'])) {
					if (isset($field['agree_link'])) {
						$href = Link::create_link($field['agree_link']);
						$content .= sprintf(' <a href="%s" target="_blank">%s</a>', $href, $field['agree_link_text']);
					} else {
						$content .= ' ' . $field['agree_link_text'];
					}
				}

				$content .= ':<br>';
				if ($field['type'] != 'checkbox') {
					$content .= htmlspecialchars($_POST['ve_custom_form_field_' . $key]) . '<br><br>';
				} elseif (isset($_POST['ve_custom_form_field_' . $key]) && is_array($_POST['ve_custom_form_field_' . $key])) {
					foreach ($_POST['ve_custom_form_field_' . $key] as $f_val) {
						$content .= htmlspecialchars($f_val) . '<br>';
					}
					$content .= '<br>';
				}
			}

			if ($send) {
				// reCAPTCHA
				if (MWRecaptcha()->isActive() && !$this->edit_mode) {
					if (!isset($_POST[ReCaptchaValidator::FORM_TOKEN_PARAMETER])) {
						$send = false;
						$error = 'internal_error';
					} elseif (!MWRecaptcha()->validate($_POST[ReCaptchaValidator::FORM_TOKEN_PARAMETER])) {
						$send = false;
						$error = 'recaptcha';
					}
				}
			}

			if ($send) {
				/*
				$headers = [
						'From: ' . get_option('blogname', true) . ' <' . get_option('admin_email', true) . '>',
						'Content-Type: text/html; charset=UTF-8',
						'Reply-To: ' . $email . ' <' . $email . '>',
				];*/

				$timestamp = $_POST['ve_sended_time'] ?? current_time('timestamp');
				$date = new DateTimeImmutable();
				$newFormatDate = $date->setTimestamp($timestamp);
				$subject = $_POST['ve_customform_subject'] . ' ' . $newFormatDate->format('d.m.Y H:i:s');
				//wp_mail($structure['email'], $subject, $content, $headers);
				$toEmail = explode(',', $structure['email']);
				$redirect_url = $_POST['ve_customform_url'];
				$query = parse_url($redirect_url, PHP_URL_QUERY);
				if (miowebMailer()->send($toEmail, get_option('admin_email', true), get_option('blogname', true), $subject, $content, $email)) {
					if ($email) {
						$redirect_url .= ($query ? '&' : '?') . 'email=' . urlencode(md5($email));
					}
				} else {
					global $phpmailer;

					$mail_error = isset($phpmailer) ? (' (' . $phpmailer->ErrorInfo . ')') : ' ' . __('(Nefunkční funkce mail.)', 'cms_ve');

					$error = __('Zpráva se nepodařila odeslat.', 'cms_ve') . $mail_error;
					$redirect_url .= ($query ? '&' : '?') . 'custom_form_error=' . urlencode($error);
				}
			} else {
				$redirect_url = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$query = parse_url($redirect_url, PHP_URL_QUERY);
				$redirect_url .= ($query ? '&' : '?') . 'custom_form_error=' . urlencode($error);
			}
			wp_redirect($redirect_url);
			die();
		}
	}

	function init_hook()
	{
		// revisions

		$labels = [
			'name' => __('Revize', 'cms_ve'),
		];

		$args = [
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'rewrite' => ['slug' => 'mw_hb_revisions'],
			'has_archive' => false,
			'hierarchical' => false,
			'supports' => [],
		];

		register_post_type('mw_hb_revisions', $args);

		$args['rewrite'] = ['slug' => 'mw_sc_revisions'];

		register_post_type('mw_sc_revisions', $args);

		$args['rewrite'] = ['slug' => 'mw_sp_revisions'];

		register_post_type('mw_sp_revisions', $args);

		// web actions
		$this->web_actions();
	}

	function check_version()
	{
		$versions = get_option('cms_versions');

		if (isset($versions['visualeditor']) && $versions['visualeditor'] != VS_VERSION) {
			global $wpdb;
			if ($versions['visualeditor'] == '0.9') {
				$wpdb->query('ALTER TABLE ' . $wpdb->prefix . 've_posts_layer ADD `vpl_type` VARCHAR( 10 ) NOT NULL AFTER `vpl_post_id` ');
				$wpdb->query('UPDATE ' . $wpdb->prefix . "ve_posts_layer SET vpl_type = 'page'");
			}
			if (version_compare($versions['visualeditor'], '0.9.2', '<')) {
				$pages = mw_get_pages(['post_status' => 'publish,private,draft']);
				foreach ($pages as $page) {
					$template = get_post_meta($page->ID, 've_page_template', true);
					$update = ['sale/1/', 'sale/2/', 'sale/3/', 'sale/4/', 'others/1/', 'others/thx2/', 'squeeze/1/', 'squeeze/4/', 'member/login1/'];
					if (in_array($template['directory'], $update)) {
						$template_config = get_post_meta($page->ID, 've_page_config', true);
						$template_config['body_class'] = 'fixed_template';
						update_post_meta($page->ID, 've_page_config', $template_config);
					}
				}
			}
			if (version_compare($versions['visualeditor'], '0.9.3', '<')) {
				update_option('ve_installed_web', ['web_theme' => 'empty']);
			}
			if (version_compare($versions['visualeditor'], '0.9.4', '<')) {
				$login = get_option('ve_connect_se');
				if ($login['connection']['status'] && $login['connection']['login'] && $login['connection']['password']) {
					$apiItem = mwApiConnect()->getApi('se');
					$new_api = $apiItem->client()->getNewApi($login['connection']['login'], $login['connection']['password']);
					if ($new_api) {
						$login['connection']['password'] = $new_api;
						$login['password'] = $new_api;
						update_option('ve_connect_se', $login);
					}
				}
			}
			if (version_compare($versions['visualeditor'], '0.9.5', '<')) {
				// repair fixed on background of page and web

				//web
				$option = get_option('ve_appearance');
				if ($option) {
					$option['background_image']['fixed'] = 'fixed';
					update_option('ve_appearance', $option);
				}

				//blog
				$option = get_option('blog_appearance');
				if ($option) {
					$option['background_image']['fixed'] = 'fixed';
					update_option('blog_appearance', $option);
				}

				//member
				$option = get_option('member_appearance');
				if ($option) {
					foreach ($option['members'] as $key => $val) {
						$option['members'][$key]['background_image']['fixed'] = 'fixed';
					}
					update_option('member_appearance', $option);
				}

				//eshop
				$option = get_option('eshop_appearance');
				if ($option) {
					$option['background_image']['fixed'] = 'fixed';
					update_option('eshop_appearance', $option);
				}

				//pages
				$pages = mw_get_pages(['post_status' => 'publish,private,draft']);
				foreach ($pages as $page) {
					$option = get_post_meta($page->ID, 've_appearance', true);
					if ($option) {
						$option['background_image']['fixed'] = 'fixed';
						update_post_meta($page->ID, 've_appearance', $option);
					}
				}
			}
			if (version_compare($versions['visualeditor'], '0.9.6', '<')) {
				// GDPR
				if (!get_option('web_option_gdpr')) {
					$setting = mwSetting()->getPage('web_option_gdpr')->getDefaultSetting();
					if (!empty($setting)) {
						add_site_option('web_option_gdpr', $setting);
					}
				}
			}
			if (version_compare($versions['visualeditor'], '3.0', '<')) {
				$pages = mw_get_pages(['post_status' => 'publish,private,draft']);
				foreach ($pages as $page) {
					$template = get_post_meta($page->ID, 've_page_template', true);
					//$config = get_post_meta($page->ID, 've_page_config', true);

					$update = ['page/2/', 'content/4/', 'content/6/', 'content/8/', 'content/11/', 'page/4/', 'page/6/', 'page/8/', 'page/11/'];
					if (in_array($template['directory'], $update)) {
						$template['directory'] = 'page/1/';
						update_post_meta($page->ID, 've_page_template', $template);

						$page_set = get_post_meta($page->ID, 've_appearance', true);
						if (!$page_set) {
							$page_set = [];
						}
						$config = get_post_meta($page->ID, 've_page_config', true);

						if ($config && isset($config['body_class'])) {
							$page_set['narrow_content'] = '1';
							update_post_meta($page->ID, 've_appearance', $page_set);
						}

						//$template_config = get_post_meta($page->ID, 've_page_config', true);
						//$template_config['body_class'] = 'fixed_template';
						//update_post_meta($page->ID, 've_page_config', $template_config);
					} elseif ($template['directory'] == 'squeeze/6/' || $template['directory'] == 'squeeze/7/' || $template['directory'] == 'landing/ebook3/') {
						$template['directory'] = 'page/1/';
						update_post_meta($page->ID, 've_page_template', $template);
					} elseif ($template['directory'] == 'webinar/1/' || $template['directory'] == 'webinar/live1/'
						|| $template['directory'] == 'webinar/3/' || $template['directory'] == 'webinar/live3/'
						|| $template['directory'] == 'sale/1/' || $template['directory'] == 'sale/3/' || $template['directory'] == 'sale/2/'
						|| $template['directory'] == 'sale/4/' || $template['directory'] == 'squeeze/5/'
						|| $template['directory'] == 'member/login1/' || $template['directory'] == 'thx/thx2/'
						|| $template['directory'] == 'thx/1/' || $template['directory'] == 'squeeze/4/'
						|| $template['directory'] == 'squeeze/1/' || $template['directory'] == 'others/1/'
					) {
						$size = '750';
						if ($template['directory'] == 'sale/1/') {
							$size = '800';
						} elseif ($template['directory'] == 'squeeze/5/') {
							$size = '700';
						} elseif ($template['directory'] == 'member/login1/' || $template['directory'] == 'squeeze/4/' || $template['directory'] == 'squeeze/1/') {
							$size = '450';
						} elseif ($template['directory'] == 'thx/thx2/' || $template['directory'] == 'thx/1/' || $template['directory'] == 'others/1/') {
							$size = '500';
						}

						$page_set = get_post_meta($page->ID, 've_appearance', true);
						if (!$page_set) {
							$page_set = [];
						}
						$page_set['page_width_preset'] = 'custom';
						$page_set['page_width'] = [
							'size' => $size,
							'unit' => 'px',
						];

						if ($template['directory'] == 'thx/1/' || $template['directory'] == 'others/1/' || $template['directory'] == 'thx/thx2/' || $template['directory'] == 'member/login1/' || $template['directory'] == 'squeeze/4/' || $template['directory'] == 'squeeze/1/' || $template['directory'] == 'sale/4/' || $template['directory'] == 'sale/3/' || $template['directory'] == 'sale/1/') {
							$config = get_post_meta($page->ID, 've_page_config', true);
							$config['content_class'] = 'mw_transparent_header_padding';
							update_post_meta($page->ID, 've_page_config', $config);

							if ($template['directory'] == 'thx/thx2/' || $template['directory'] == 'member/login1/' || $template['directory'] == 'thx/1/' || $template['directory'] == 'others/1/' || $template['directory'] == 'squeeze/4/' || $template['directory'] == 'squeeze/1/' || $template['directory'] == 'sale/4/' || $template['directory'] == 'sale/3/' || $template['directory'] == 'sale/1/') {
								global $wpdb;

								$result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='page' AND vpl_post_id=" . $page->ID);
								$layer = unserialize(base64_decode($result->vpl_layer));

								if ($template['directory'] == 'thx/thx2/') {
									$layer[0]['style']['margin_top'] = '60';
								} elseif ($template['directory'] == 'member/login1/' || $template['directory'] == 'squeeze/1/') {
									$layer[0]['style']['margin_top'] = '60';
									$layer[0]['style']['margin_bottom'] = '60';
								} elseif ($template['directory'] == 'thx/1/' || $template['directory'] == 'others/1/') {
									$layer[0]['style']['margin_top'] = '60';
									$layer[1]['style']['margin_bottom'] = '60';
								} elseif ($template['directory'] == 'squeeze/4/') {
									$layer[0]['style']['margin_top'] = '20';
									$layer[0]['style']['margin_bottom'] = '20';
								} elseif ($template['directory'] == 'sale/4/' || $template['directory'] == 'sale/3/' || $template['directory'] == 'sale/1/') {
									$i = 1;
									foreach ($layer as $row_id => $row) {
										if (!isset($row['style']['background_color']['color1']) || !$row['style']['background_color']['color1']) {
											$layer[$row_id]['style']['background_color']['color1'] = '#ffffff';
										}
										if ($i == 1 && $template['directory'] != 'sale/1/') {
											$layer[$row_id]['style']['margin_top'] = '20';
										}
										$i++;
									}
								}

								$coded_layer = base64_encode(serialize($layer));
								$wpdb->update($wpdb->prefix . 've_posts_layer', ['vpl_layer' => $coded_layer], ['vpl_post_id' => $page->ID, 'vpl_type' => 'page']);
							}

							//mwBackCompatibility::update_template($page->ID,$template['directory']);
						}

						if ($template['directory'] == 'webinar/1/' || $template['directory'] == 'webinar/live1/'
							|| $template['directory'] == 'sale/1/' || $template['directory'] == 'sale/3/'
							|| $template['directory'] == 'sale/4/' || $template['directory'] == 'member/login1/'
							|| $template['directory'] == 'thx/thx2/' || $template['directory'] == 'thx/1/'
							|| $template['directory'] == 'squeeze/4/' || $template['directory'] == 'squeeze/1/'
							|| $template['directory'] == 'others/1/'
						) {
							$page_set['narrow_content'] = '1';
						}

						update_post_meta($page->ID, 've_appearance', $page_set);
					}
				}

				add_site_option('mw_back_compatibility', '1');

				// default buttons
				$global_buttons = get_option('ve_buttons');
				if (!$global_buttons) {
					$default_buttons = [
						'buttons' => [
							'basic' => [
								'style' => '1',
								'background_color' => [
									'color1' => '#eb1e47',
									'transparency1' => '1.00',
									'rgba1' => 'rgba(235, 30, 71, 1)',
									'color2' => '',
									'transparency2' => '',
									'rgba2' => '',
								],
								'font-color' => '',
								'corner' => '8',
								'height_padding' => '1.1',
								'width_padding' => '1.8',
								'font' => [
									'font-family' => '',
									'weight' => '',
								],
								'border-color' => '',
								'border_width' => '',
								'hover_effect' => 'darker',
								'hover_color' => [
									'color1' => '',
									'transparency1' => '',
									'rgba1' => '',
									'color2' => '',
									'transparency2' => '',
									'rgba2' => '',
								],
								'hover_font_color' => '',
								'border_hover-color' => '',
							],
							'inverse' => [
								'style' => '12',
								'background_color' => [
									'color1' => '',
									'transparency1' => '',
									'rgba1' => '',
									'color2' => '',
									'transparency2' => '',
									'rgba2' => '',
								],
								'font-color' => '',
								'corner' => '8',
								'height_padding' => '1.1',
								'width_padding' => '1.8',
								'font' => [
									'font-family' => '',
									'weight' => '',
								],
								'border-color' => '#ffffff',
								'border_width' => '',
								'hover_effect' => '',
								'hover_color' => [
									'color1' => '#eb1e47',
									'transparency1' => '1.00',
									'rgba1' => 'rgba(235, 30, 71, 1)',
									'color2' => '',
									'transparency2' => '',
									'rgba2' => '',
								],
								'hover_font_color' => '',
								'border_hover-color' => '#eb1e47',
							],
						],
					];
					update_option('ve_buttons', $default_buttons);
				}
			}

			if (version_compare($versions['visualeditor'], '3.0.1', '<')) {
				$old = get_option('ve_connect_se');

				// SmartEmailing
				$se_login = [
					'login' => $old['login'] ?? '',
					'password' => $old['password'] ?? '',
					'status' => $old['connection']['status'] ?? 0,
				];
				update_option('mw_api_connection_se', $se_login);

				// GetResponse
				$getresponse_login = [
					'login' => $old['getresponse_login'] ?? '',
					'password' => $old['getresponse_password'] ?? '',
					'status' => $old['getresponse_connection']['status'] ?? 0,
				];
				update_option('mw_api_connection_getresponse', $getresponse_login);

				// MailChimp
				$mailchimp_login = [
					'login' => $old['mailchimp_login'] ?? '',
					'password' => $old['mailchimp_password'] ?? '',
					'status' => $old['mailchimp_connection']['status'] ?? 0,
				];
				update_option('mw_api_connection_mailchimp', $mailchimp_login);

				// aWeber
				$aweber_login = [
					'login' => $old['aweber_login'] ?? '',
					'password' => $old['aweber_password'] ?? '',
					'status' => $old['aweber_connection']['status'] ?? 0,
				];
				update_option('mw_api_connection_aweber', $aweber_login);

				// Fapi, Simpleshop
				$old = get_option('ve_connect_fapi');

				// Fapi
				$fapi_login = [
					'login' => $old['login'] ?? '',
					'password' => $old['password'] ?? '',
					'status' => $old['connection']['status'] ?? 0,
				];
				update_option('mw_api_connection_fapi', $fapi_login);

				// SimpleShop
				$ss_login = [
					'login' => $old['simpleshop_login'] ?? '',
					'password' => $old['simpleshop_password'] ?? '',
					'status' => $old['simpleshop_connection']['status'] ?? 0,
				];
				update_option('mw_api_connection_simpleshop', $ss_login);

				// Packeta
				$old = get_option('mw_shipping_connect');
				update_option('mw_api_connection_packeta', $old['packeta_connection'] ?? []);

				// Heureka
				$old = get_option('mw_heureka_connect');
				update_option('mw_api_connection_heureka', $old);

				// Zbozi
				$old = get_option('mw_zbozi_connect');
				update_option('mw_api_connection_zbozi', $old);

				// Google maps
				$old = get_option('ve_google_api');
				update_option('mw_api_connection_google_maps', $old);

				// blog category images
				$categories = get_terms([
					'taxonomy' => 'category',
					'hide_empty' => false,
				]);
				foreach ($categories as $cat) {
					$cat_meta = get_option('mw_category_setting_' . $cat->term_id);
					if (isset($cat_meta['image']) && $cat_meta['image']) {
						update_term_meta($cat->term_id, 'mw_thumbnail', $cat_meta);
					}
				}

				// blog tag images
				$categories = get_terms([
					'taxonomy' => 'post_tag',
					'hide_empty' => false,
				]);
				foreach ($categories as $cat) {
					$cat_meta = get_option('mw_tag_setting_' . $cat->term_id);
					if (isset($cat_meta['image']) && $cat_meta['image']) {
						update_term_meta($cat->term_id, 'mw_thumbnail', $cat_meta);
					}
				}

				// for mw-admin rewrite rule
				flush_rewrite_rules();
			}

			if (version_compare($versions['visualeditor'], '3.1.1', '<')) {
				// cookie banner setting
				$oldSetting = get_option('web_option_others');
				$cookieSetting = mwSetting()->getPage('web_option_others')->getDefaultSetting();
				if (isset($oldSetting['use_cookie'])) {
					$cookieSetting['use_cookie'] = 1;
				}
				update_option('web_option_others', $cookieSetting);

				// web codes
				$oldCodes = get_option('web_option_codes');
				$newCodes = MwCodes::convertCodesFromOldData($oldCodes, 'head_scripts', 'body_scripts', 'footer_scripts', 'css_scripts');

				if (isset($oldCodes['ga_id']) && $oldCodes['ga_id']) {
					$newCodes['codes'][] = [
						'title' => __('Google analytics', 'cms_ve'),
						'position' => 'header',
						'type' => 'analytics',
						'code' => $oldCodes['ga_id'],
					];
				}

				update_option('mw_web_codes', $newCodes);

				// post codes

				$result = $wpdb->get_results("SELECT ID, meta_value, post_type FROM {$wpdb->prefix}postmeta, {$wpdb->prefix}posts WHERE post_id = ID AND meta_key = 'page_codes' AND meta_value != 'a:4:{s:16:\"codes_conversion\";s:0:\"\";s:12:\"codes_header\";s:0:\"\";s:12:\"codes_footer\";s:0:\"\";s:9:\"codes_css\";s:0:\"\";}'");
				foreach ($result as $meta) {
					$oldCodes = maybe_unserialize($meta->meta_value);
					$newCodes = $meta->post_type === 'mwproduct' ? MwCodes::convertCodesFromOldData($oldCodes, 'codes_header', null, 'codes_footer', 'codes_css', 'product_conversion') : MwCodes::convertCodesFromOldData($oldCodes, 'codes_header', null, 'codes_footer', 'codes_css', 'codes_conversion');
					update_post_meta($meta->ID, 'mw_page_codes', $newCodes);
				}
			}

			if (version_compare($versions['visualeditor'], '3.1.2', '<')) {
				self::createEmailTable();
			}

			$versions['visualeditor'] = VS_VERSION;
			update_option('cms_versions', $versions);
		}
	}

	public static function createEmailTable(): void
	{
		MWDB()->createTable('mw_emails', '
					email_id int(11) NOT NULL AUTO_INCREMENT,
					in_module varchar(100) NOT NULL,
					item_id bigint(20) NOT NULL,
					type varchar(200) NOT NULL,
					subject varchar(200) NOT NULL,
					attachment varchar(255) DEFAULT NULL,
				 	text text NOT NULL,
					PRIMARY KEY (email_id),
				  	KEY item_id (item_id)');
	}

	/* Others */

	function is_blog()
	{
		return is_archive() || (is_author()) || (is_category()) || (is_home()) || (is_tag()) || (is_search()) ? true : false;
	}

	function getSubmodules()
	{
		$option = get_option('active_plugins');
		$active_plugins = [];
		foreach ((array) $option as $ap) {
			$string = explode('/', $ap); // Folder name will be displayed
			$active_plugins[] = $string[0];
		}

		if (in_array('restaurant-reservations', $active_plugins)) {
			require_once(__DIR__ . '/lib/submodules/restaurant/restaurant.php');
		}
	}

	/* back compatibility */
	function add_top_panel_menu($id, $menu)
	{
		if ($this->builder_mode) {
			$this->builder->add_top_panel_menu($id, $menu);
		}
	}

	function addFastNav($menu, $id)
	{
		if ($this->builder_mode) {
			$this->builder->addFastNav($menu, $id);
		}
	}

	function replaceElements($replaceElements, $layer)
	{
		foreach ($layer as $row_id => $row) {
			if (isset($row['content'])) {
				foreach ($row['content'] as $col_id => $col) {
					if (isset($col['content'])) {
						foreach ($col['content'] as $element_id => $element) {
							if ($element['type'] == 'twocols' || $element['type'] == 'box') {
								foreach ($element['content'] as $subcol_id => $subcol) {
									foreach ($subcol as $subel_id => $subelement) {
										if (isset($replaceElements[$element['type']])) {
											$layer[$row_id]['content'][$col_id]['content'][$element_id]['content'][$subcol_id][$subel_id]['type'] = $replaceElements[$element['type']];
										}
									}
								}
							} else {
								if (isset($replaceElements[$element['type']])) {
									$layer[$row_id]['content'][$col_id]['content'][$element_id]['type'] = $replaceElements[$element['type']];
								}
							}
						}
					}
				}
			}
		}

		return $layer;
	}

	public static function code($code)
	{
		return base64_encode(serialize(apply_filters('mw_layer_encode', $code)));
	}

	public static function json_decode($code)
	{
		return json_decode(stripslashes($code), true);
	}

	public static function decode($code)
	{
		return apply_filters('mw_content_layer', unserialize(base64_decode($code)), $code);
	}

	public static function json_code($code)
	{
		return json_encode($code);
	}

	/** @deprecated */
	function create_link($link, $add_args = true, $hash = false)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated. Use static method ' . Link::class . '::create_link instead', E_USER_DEPRECATED);

		return Link::create_link($link, $add_args, $hash);
	}

}
