<?php

class MWPageBuilder
{

	public $script_version;

	public $editable_type = ['page', 've_elvar', 've_header', 'cms_footer', 'cms_popup', 'weditor', 'mw_slider'];

	public $top_panel_menu = [];

	public $fast_nav = [];

	public $post_id;

	public $css;

	private $save_post_id = null;

	function __construct()
	{
		$this->script_version = filemtime(get_template_directory() . '/style.css');

		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: -1');

		add_action('wp_enqueue_scripts', [$this, 'load_pagebuilder_scripts'], 10);

		$this->register_hooks();

		if (!isset($_SESSION['ve_used_colors'])) {
			$_SESSION['ve_used_colors'] = [];
			if (get_option('ve_used_colors')) {
				$_SESSION['ve_used_colors'] = get_option('ve_used_colors');
			}
		}
		if (!isset($_SESSION['ve_used_fonts'])) {
			$_SESSION['ve_used_fonts'] = [];
			if (get_option('ve_used_fonts')) {
				$_SESSION['ve_used_fonts'] = get_option('ve_used_fonts');
			}
		}

		$this->css = new mwCssManager();
	}

	function init($post_id, $save_post_id)
	{
		$this->post_id = $post_id;
		$this->save_post_id = $save_post_id;
		do_action('mw_builder_init', $post_id);
	}

	function load_pagebuilder_scripts()
	{
		wp_enqueue_script('media-upload');
		wp_enqueue_media();

		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-nestedsortable');
		wp_enqueue_script('jquery-ui-tooltip');

		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('cms_datepicker_style');
		wp_enqueue_script('cms_datepicker_cs');

		wp_enqueue_script('ve_minicolor_script', get_template_directory_uri() . '/library/includes/minicolors/jquery.minicolors.js');
		wp_enqueue_style('ve_minicolor_css', get_template_directory_uri() . '/library/includes/minicolors/jquery.minicolors.css');

		wp_enqueue_script('cms_admin_script');

		wp_enqueue_script('ve_weditor_admin_script');
		wp_enqueue_script('mw_droppable_iframe');
		wp_enqueue_script('mw_pgb_editor_script');

		wp_enqueue_style('cms_admin_styles');

		// lightbox
		wp_enqueue_script('cms_lightbox_script');
		wp_enqueue_style('cms_lightbox_style');

		// insatall
		wp_enqueue_style('ve_install_style');
		wp_enqueue_script('ve_install_scripts');

		// scroll
		wp_enqueue_script('mw-croll-script');
		wp_enqueue_style('mw-croll-style');

		// google maps
		wp_enqueue_script('ve_google_maps');

		// chosen - autocomplete
		wp_enqueue_style('mw-chosen-styles');
		wp_enqueue_script('mw-chosen-support');

		// password streangth
		wp_enqueue_script('password-strength-meter');

		// styles
		wp_enqueue_style('mw_pgb_editor');

		wp_enqueue_script('mw_api_script');

		wp_enqueue_style('mw_pgb_font_os', 'https://fonts.googleapis.com/css?family=Open+Sans:400,700');

		wp_localize_script('mw_pgb_editor_script', 've_used_colors', $_SESSION['ve_used_colors']);
	}

	function register_hooks()
	{
		/* not done */
		add_action('cms_activation', [$this, 've_activation']);

		add_action('wp_restore_post_revision', [$this, 'layer_revision']);

		/* done */
		add_action('init', [$this, 'builder_actions'], 1); // for save page and global settings
		add_action('wp_ajax_mw_change_template', [$this, 'change_template']);

		add_action('body_class', [$this, 'add_bodyclass']);
		add_action('wp_footer', [$this, 'builder_footer']);

		add_action('wp_ajax_open_row_setting', [$this, 'open_row_setting']);
		add_action('wp_ajax_open_element_setting', [$this, 'open_element_setting']);

		//popup settings
		add_action('wp_ajax_openPopSetting', [$this, 'openPopSetting_ajax']);

		add_action('wp_ajax_openSettingInpanel', [$this, 'openSettingInpanel_ajax']);
		add_action('wp_ajax_switch_editor_setting_inpanel', [$this, 'switch_editor_setting_inpanel']);
		add_action('wp_ajax_save_panel_setting', [$this, 'save_panel_setting']);

		// delete post
		add_action('before_delete_post', [$this, 'delete_page_hook'], 10, 2);

		// menu edit
		//add_filter('wp_nav_menu', array($this, 'menu_filter'), 10, 2);
		// reload menu
		add_action('wp_ajax_reload_menu', [$this, 'reload_menu']);
		// manage menus
		//add_action('wp_ajax_open_menu_setting', array($this, 'open_menu_setting'));
		add_action('wp_ajax_open_create_menu', [$this, 'open_create_menu']);
		add_action('wp_ajax_open_single_menu_setting', [$this, 'open_single_menu_setting']);
		add_action('wp_ajax_save_menu_setting', [$this, 'save_menu_setting']);
		add_action('wp_ajax_ve_generate_edit_menu_item', [$this, 've_generate_edit_menu_item']);
		add_action('wp_ajax_ve_change_menu_setting', [$this, 'change_menu_setting']);
		add_action('wp_ajax_ve_create_new_menu', [$this, 'create_new_menu']);
		add_action('wp_ajax_delete_menu', [$this, 'delete_menu_ajax']);

		// load page selector
		add_action('wp_ajax_load_page_selector_content', [mwPageSelector(), 'content']);

		// revisions
		add_action('wp_ajax_mw_load_page_revisions', [$this, 'print_list_revisions']);

		// locate builder template
		add_filter('page_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('home_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('single_template', [$this, 'hook_locate_builder_template_single'], 200, 2);
		add_filter('archive_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('category_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('tag_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('date_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('search_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('author_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('index_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('404_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('taxonomy_template', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('embed', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('frontpage', [$this, 'hook_locate_builder_template'], 200, 2);
		add_filter('attachment', [$this, 'hook_locate_builder_template'], 200, 2);

		add_filter('wp_revisions_to_keep', [$this, 'control_revisions'], 10, 2);
	}

	function control_revisions($num = null, $post = null)
	{
		if (defined('MW_POST_REVISIONS')) {
			return MW_POST_REVISIONS;
		}

		return mw_is_lite_editor() ? 15 : 30;
	}

	function hook_locate_builder_template($located, $templateName)
	{
		$located = TEMPLATEPATH . '/builder.php';

		return $located;
	}
	function hook_locate_builder_template_single($located, $templateName)
	{
		global $post;

		if ($post->post_type !== 'mwdocument' && $post->post_type !== 'mwdorder') {
			$located = TEMPLATEPATH . '/builder.php';
		}

		return $located;
	}

	function add_bodyclass($classes)
	{
		if (MW()->isWebInstalled()) {
			$classes[] = 'desktop_view';
			$classes[] = 'mw_builder_body';
		}

		return $classes;
	}

	function editor_panel($object_id, $page_type, $modul_type, $window_editor_setting = [], $window_editor = false)
	{
		global $current_user;

		$setting_tab_name = __('STRÁNKA', 'cms_ve');
		?>

		<div id="ve_editor_panel" class="ve_editor_panel <?php if (!$this->is_editable($page_type)) { echo 'not_editable';} ?>">

			<div class="mw_editor_panel_main_content mw_editor_panel_container ">

		<?php
		if (!$window_editor) {
			mwPageSelector()->opener();
		} else {
			if ($window_editor_setting['type'] == 'cms_popup') {
				$setting_tab_name = __('POPUP', 'cms_ve');
			} elseif (in_array($window_editor_setting['type'], ['weditor', 'mwupsell'], true)) {
				$setting_tab_name = '';
			}
		}
		?>

				<ul class="mw_admin_tabs mw_editor_panel_tabs">
					<li><a class="mw_admin_content_tab active" href="content" data-group="mw_editor_panel"><?php echo __('OBSAH', 'cms_ve'); ?></a></li>
					<?php if ($setting_tab_name) { ?>
						<li><a class="mw_admin_setting_tab" href="setting" data-group="mw_editor_panel"><?php echo $setting_tab_name; ?></a></li>
					<?php } ?>
				</ul>

		<?php // ADD CONTENT TO PAGE

		$enable_rows = true;
		if ($window_editor && ($window_editor_setting['type'] == 'mw_slider' || $window_editor_setting['type'] == 've_elvar')) {
			$enable_rows = false;
		}
		?>
				<div class="mw_editor_panel_content mw_editor_panel_tab mw_admin_tab mw_admin_tab_1">
		<?php if ($enable_rows) { ?>
						<ul class="mw_admin_tabs mw_editor_panel_content_tabs">
							<li><a href="elements" class="mw_admin_elements_tab active"
								   data-group="mw_editor_panel_content"><?php echo __('Elementy', 'cms_ve'); ?></a></li>
							<li><a href="rows" class="mw_admin_rows_tab"
								   data-group="mw_editor_panel_content"><?php echo __('Bloky', 'cms_ve'); ?></a></li>
						</ul>
		<?php } ?>
					<div class="mw_scroll mw_editor_panel_content_in <?php if (!$enable_rows) {
						echo 'disable_rows';
																	 } ?>">
		<?php if ($enable_rows) { ?>
							<div class="mw_row_layouts_bg"></div>
							<div class="mw_editor_panel_content_rows mw_editor_panel_content_tab mw_admin_tab">
			<?php $this->row_selector(); ?>
							</div>
		<?php } ?>
						<div
							class="mw_editor_panel_content_elements mw_editor_panel_content_tab mw_admin_tab mw_admin_tab_1">
		<?php $this->element_selector($window_editor_setting, $window_editor, $page_type); ?>
						</div>
					</div>

				</div>

				<?php // SETTING ON PAGE ?>

				<div class="mw_editor_panel_setting mw_scroll mw_editor_panel_tab mw_admin_tab">
					<?php
					mwSetting()->printObjectMenu($object_id, $modul_type, $page_type, $this->post_id, $window_editor);
					?>
				</div>

				<?php // REVISIONS  ?>

				<div class="mw_editor_revision_list">
					<div class="mw_editor_web_setting_head">
						<?php echo __('HISTORIE ULOŽENÍ', 'cms_ve'); ?>
					</div>
					<div class="mw_editor_revision_list_content mw_scroll">

					</div>
					<div class="mw_editor_panel_bottom">
						<button type="submit" class="mw_save_button unactive mw_save_revision"><?php echo __('OBNOVIT', 'cms_ve'); ?> </button>
						<button type="submit" class="mw_storno_button mw_close_revisions"><?php echo __('STORNO', 'cms_ve'); ?> </button>
					</div>
				</div>

			</div>

		<?php // ROW AND ELEMENT SETTING CONTAINER ?>

			<div class="mw_editor_panel_content_setting">
				<form id="ve_save_setting_form" action="" method="post">

					<div class="mw_editor_panel_container"></div>

					<div class="mw_editor_panel_bottom">
						<button type="submit" class="mw_save_button mw_save_setting"><?php echo __('ULOŽIT', 'cms_ve'); ?> </button>
						<button type="submit" class="mw_storno_button mw_storno_setting"><?php echo __('STORNO', 'cms_ve'); ?> </button>
					</div>

				</form>
			</div>


			<div class="mw_editor_panel_load">
				<svg role="img">
					<use xlink:href="<?php echo MW_UI_ICONS_URL; ?>loading.svg#icon-loading-w"></use>
				</svg>
			</div>

		</div>
		<?php
	}

	function print_list_revisions()
	{
		$revisions = $this->mw_get_revisions($_POST['post_id'], $_POST['post_type']);
		$content = '';
		if (count($revisions)) {
			$content = mwAdminComponents::messageBox(__('Pro obnovení starší uložené verze stránky, klikněte na verzi, kterou chcete obnovit a klikněte na tlačítko Obnovit.', 'cms_ve'), [
				'type' => 'info_gray',
			], 'mw_revision_info');

			foreach ($revisions as $revision) {
				$rev_user = get_user_by('ID', $revision->post_author);
				// post_author, ID
				$url = get_permalink() . '?mw_preview=1&revision=' . $revision->ID . '&revision_type=' . $revision->post_type;
				if ($_POST['weditor'] == '1') {
					$url .= '&window_editor=' . $revision->post_type;
				}

				$content .= '<div class="mw_revision_item" data-rev="' . $url . '" data-rev-id="' . $revision->ID . '" data-rev-type="' . $revision->post_type . '">';
				//$content.=$revision->post_date;
				$content .= '<div>' .
				__('Před', 'cms_ve') . ' ' . human_time_diff(strtotime($revision->post_date), current_time('timestamp')) .
				' <span class="mw_revision_item_time">(' . date('j. n. H:i', strtotime($revision->post_date)) . ')</span>' .
				'</div>';
				$content .= '<div class="mw_revision_item_author">' . __('Autor:', 'cms_ve') . ' ' . $rev_user->user_nicename . '</div>';
				$content .= '<a href="#" class="mw_revision_item_storno mw_revision_storno">' . mw_icon('icon-x') . '</a>';
				$content .= '</div>';
			}
		} else {
			$content = mwAdminComponents::messageBox(__('Tato stránka nemá historii uložení. To znamená že na ní zatím nebyly provedeny žádné změny. Po uložení změn na stránce se vytvoří nový záznam v historii uložení.', 'cms_ve'), [
				'type' => 'info_gray',
			], 'mw_revision_info');
		}

		echo $content;
		die();
	}

	function mw_get_revisions($post_id, $type)
	{
		if ($type == 'blog') {
			$revisions = get_posts([
				'post_type' => 'mw_hb_revisions',
				'post_status' => 'publish',
			]);
		} elseif ($type == 'eshop_cate') {
			$revisions = get_posts([
				'post_type' => 'mw_sc_revisions',
				'post_status' => 'publish',
				'post_parent' => $_POST['post_id'],
			]);
		} elseif ($type == 'mwproduct') {
			$revisions = get_posts([
				'post_type' => 'mw_sp_revisions',
				'post_status' => 'publish',
				'post_parent' => $_POST['post_id'],
			]);
		} else {
			$revisions = wp_get_post_revisions($_POST['post_id']);
		}

		return $revisions;
	}

	function is_editable($page_type)
	{
		return (is_404() || is_home() || in_array($page_type, $this->editable_type)) && !isset($_GET['variant']);
	}

	function editor_top_panel($page_type, $modul_type, $weditor = false)
	{
		global $post;
		?>
		<div class="ve_editor_top_panel">

			<div class="tp_title <?php if (mw_is_lite_editor()) { echo 'tp_title_lite';} ?>"></div>

			<?php if (!$weditor) { ?>
				<ul class="top_panel_menu top_panel_menu_left">
					<li class="tp_open_edit">
						<a class="mw_open_editing_on_mobile" title="Editovat" href="#">
							<?php
							echo mw_icon('icon-edit-2');
							?>
						</a>
					</li>
					<li class="tp_add">
						<a class="create-new-page" data-type="web" title="<?php echo __('Stránku', 'cms_ve'); ?>" href="#">
							<?php
							echo mw_icon('icon-plus');
							echo '<span>' . __('Přidat', 'cms_ve') . '</span>';
							?>
						</a>
						<ul>
							<li>
								<a class="create-new-page" data-type="web"
								   title="<?php echo __('Přidat stránku', 'cms_ve'); ?>"
								   href="#"><?php echo __('Stránku', 'cms_ve'); ?></a>
							</li>
							<li>
								<a target="_blank"
								   href="<?php echo admin_url('post-new.php'); ?>"><?php echo __('Článek blogu', 'cms_ve'); ?></a>
							</li>
							<?php do_action('mw_add_list'); ?>
						</ul>
					</li>
					<li class="tp_setting">
						<a title="Nastavení" href="<?php echo get_mw_admin_url('web_option_basic'); ?>">
							<?php
							echo mw_icon('icon-settings');
							echo '<span>' . __('Nastavení', 'cms_ve') . '</span>';
							?>
						</a>
						<?php mwSetting()->topMenu(); ?>
					</li>
					<?php
					foreach ($this->top_panel_menu as $menu) {
						$url = $menu['url'] ?? '#';
						$class = $url == '#' ? 've_prevent_default ' : '';
						$class = $modul_type == $menu['id'] ? 'current_top_menu ' : '';
						$icon = $menu['icon'] ?? 'icon-zap';
						echo '<li class="tp_custom ve_top_menu_' . $menu['id'] . '">'
						. '<a class="' . $class . '" href="' . $url . '" title="' . $menu['title'] . '">'
						. mw_icon($icon)
						. '</a>'
						. $menu['submenu']
						. '</li>';
					}
					?>
					<li class="tp_nav">
						<?php

						$blogurl = get_option('show_on_front') == 'page' ? get_permalink(get_option('page_for_posts')) : home_url();

						$current = [
							'title' => __('Web', 'cms_ve'),
							'url' => home_url(),
						];

						if ($modul_type == 'blog') {
							$current = [
								'title' => __('Blog', 'cms_ve'),
								'url' => $blogurl,
							];
						}

						$current = apply_filters('mw_fast_nav_current', $current);

						?>
						<a href="<?php echo $current['url']; ?>" class="tp_nav_select">
						<?php
						echo mwAdminComponents::icon([
							'icon' => 'home',
						], 'tp_nav_home');
						echo mwAdminComponents::icon([
							'icon' => 'chevron-right',
						], 'tp_nav_chevron');
						//mw_icon('icon-home');
						echo $current['title'];
						?>
						</a>
						<ul>
							<li class="tp_nav_menu_label"><?php echo __('Přejít na:', 'cms_ve'); ?></li>
							<li><a href="<?php echo home_url(); ?>"><?php echo __('Web', 'cms_ve'); ?></a></li>
							<li><a href="<?php echo $blogurl; ?>"><?php echo __('Blog', 'cms_ve'); ?></a></li>
							<?php
							foreach ($this->fast_nav as $fast_nav_item) {
								$url = $fast_nav_item['url'] ?? '#';
								$class = $url == '#' ? 've_prevent_default ' : '';

								echo '<li>'
								. '<a class="' . $class . '" href="' . $url . '">'
								. $fast_nav_item['title']
								. '</a>';
								if (isset($fast_nav_item['submenu'])) {
									echo $fast_nav_item['submenu'];
									echo '<span>' . mw_icon('icon-chevron-right') . '</span>';
								}
								echo '</li>';
							}
							?>
						</ul>

					</li>

					<?php
					MW()->getLicense()->showAlerts();
					?>

				</ul>

			<?php } ?>

			<ul class="top_panel_menu top_panel_menu_right">

				<li class="mw_change_device_preview_container">
					<a href="#" class="mw_change_device_preview mw_change_device_desktop"
					   data-device="desktop"><?php echo mw_icon('icon-desktop'); ?></a>
					<a href="#" class="mw_change_device_preview mw_change_device_tablet"
					   data-device="tablet"><?php echo mw_icon('icon-tablet'); ?></a>
					<a href="#" class="mw_change_device_preview mw_change_device_mobile"
					   data-device="mobile"><?php echo mw_icon('icon-mobile'); ?></a>
				</li>
				<li class="tp_preview">
					<a class="mw_close_preview_link mw_open_preview">
					<?php echo __('Zavřít náhled', 'cms_ve'); ?>
					<?php echo mw_icon('icon-eye-off'); ?>
					</a>
					<a class="mw_tooltip mw_open_preview_link mw_open_preview"
					   data-title="<?php echo __('Náhled stránky', 'cms_ve'); ?>"
					   href="#"><?php echo mw_icon('icon-eye'); ?></a>
				</li>
				<li class="tp_help">
					<a target="_blank" title="<?php echo __('Nápověda', 'cms_ve'); ?>" href="<?php echo MW_SUPPORT_URL ?>"><?php echo mw_icon('icon-help-circle'); ?></a>
					<ul>
						<li>
							<a target="_blank" href="<?php echo MW_SUPPORT_URL ?>"><?php echo __('Podpora', 'cms_ve'); ?></a>
						</li>
					</ul>
				</li>
				<?php
				if (!$weditor) {
					?>
					<li class="tp_logged_user">

						<a target="_blank" href="<?php echo MY_ACCOUNT_URL; ?>">
						<?php

						echo mwSetting()->currentUser()->getAvatar(28); ?>

						</a>
						<ul>
							<li><a target="_blank"
								   href="<?php echo MY_ACCOUNT_URL; ?>"><?php echo __('Můj Mioweb', 'cms_ve'); ?></a>
							</li>
							<li><a target="_blank"
								   href="<?php echo mwSetting()->currentUser()->getEditUrl(); ?>"><?php echo __('Můj profil', 'cms_ve'); ?></a>
							</li>
							<li><a target="_blank"
								   href="<?php echo admin_url(); ?>"><?php echo __('Do Wordpressu', 'cms_ve'); ?></a>
							</li>
							<li><a href="<?php echo wp_logout_url(); ?>"><?php echo __('Odhlásit se', 'cms_ve'); ?></a>
							</li>
						</ul>
					</li>

					<li class="tp_wp">
						<a class="mw_tooltip" target="_blank" data-title="<?php echo __('Do wordpressu', 'cms_ve'); ?>"
						   href="<?php echo admin_url(); ?>"><?php echo mw_icon('icon-wp'); ?></a>
					</li>
					<?php
				}

			if ($this->is_editable($page_type)) {
					?>
					<li class="tp_history">
						<a class="mw_tooltip mw_open_revisions" data-weditor="<?php echo $weditor ? '1' : '0'; ?>"
						   data-title="<?php echo __('Historie uložení', 'cms_ve'); ?>"
						   href="#"><?php echo mw_icon('icon-rotate-ccw'); ?></a>
						<a class="mw_close_revisions_link mw_close_revisions">
						<?php echo __('Zavřít historii uložení', 'cms_ve'); ?>
						<?php echo mw_icon('icon-x'); ?>
						</a>
					</li>
					<?php
			}

		?>
				<li class="mw_editor_panel_save">

					<input id="save_id" type="hidden" name="post_id" value="<?php echo $this->save_post_id; ?>"/>
					<input id="ve_page_type" type="hidden" name="ve_page_type" value="<?php echo $page_type; ?>"/>
					<input id="ve_modul_type" type="hidden" name="ve_modul_type" value="<?php echo $modul_type; ?>"/>

		<?php if ($this->is_editable($page_type)) { ?>
						<a href="#" type="submit" class="mw_save_button mw_save_page">
							<i><?php echo mw_icon('icon-save'); ?></i>
			<?php echo __('ULOŽIT', 'cms_ve'); ?>
						</a>
		<?php } ?>
				</li>

		<?php if ($weditor) { ?>
					<li class="tp_close">
						<a class="mw_tooltip mw_close_weditor" data-title="<?php echo __('Zavřít', 'cms_ve'); ?>"
						   data-confirm="<?php echo __('Opravdu chcete okno zavřít? Neuložená data budou ztracena.', 'cms_ve'); ?>"
						   href="#"><?php echo mw_icon('icon-x'); ?></a>
					</li>
		<?php } ?>

			</ul>

		</div>
		<?php
	}

	function row_selector()
	{
		global $mwContainer;

		echo '<div class="mw_elements_group_title">' . __('Prázdné', 'cms_ve') . '</div>';
		echo '<div class="mw_empty_rows_container">';
		foreach ($mwContainer->empty_rows as $row) {
			$thumb = mw_icon('row-' . $row['thumb'], '', MW_UI_ICONS_URL . 'rows.svg');
			echo '<div class="mw_page_builder_draggable add_new_row add_new_row_item" title="' . $row['title'] . '" data-content="' . $row['content'] . '" data-type="row" data-rowtype="empty">' . $thumb . '</div>';
		}
		echo '</div>';
		echo '<div class="mw_predefined_rows_container">';
		echo '<div class="mw_elements_group_title">' . __('Předdefinované', 'cms_ve') . '</div>';

		$items = $mwContainer->rows;

		$i = 1;
		foreach ($items as $row) {
			if (mw_is_lite_editor()) {
				foreach ($row['layouts'] as $r_id => $r_val) {
					if (!isset($r_val['lite']) || !$r_val['lite']) {
						unset($row['layouts'][$r_id]);
					}
				}
			}

			$type = $row['type'] ?? '';

			if (count($row['layouts'])) {
				echo '<a href="#" data-group="' . $i . '" class="mw_row_group_title">' . $row['tab'] . mw_icon('icon-chevron-right') . '</a>';
			}

			echo '<div class="mw_row_layouts_container mw_row_layouts_container_' . $i . '">';
			echo '<div class="mw_row_layouts mw_scroll">';
			foreach ($row['layouts'] as $key => $lay) {
				$thumb = $type == 'template'
					? '<img src="' . VS_DIR . 'templates/rows/' . $lay['content'] . '.jpg" alt="">'
					: '<span></span>' . $lay['title'];
echo '<div class="mw_page_builder_draggable add_new_row add_new_row_item" title="' . $lay['title'] . '" data-content="' . $lay['content'] . '" data-type="row" data-rowtype="' . $type . '">' . $thumb . '</div>';
			}
			echo '</div>';
			echo '</div>';
			$i++;
		}
		echo '</div>';
	}

	function element_selector($window_editor_setting = [], $window_editor = false, $page_type = '')
	{
		global $mwContainer;

		$groups = $mwContainer->element_groups;
		$items = $mwContainer->elements;

		if ($window_editor && $window_editor_setting['type'] === 'mw_slider') {
			$allowed['groups'] = ['basic'];
			$allowed['elements'] = ['text', 'title', 'image', 'button'];
		} elseif ($window_editor && $window_editor_setting['type'] === 've_elvar') {
			unset($items['variable_content']);
		} elseif ($page_type === 'mwupsell') {
			$allowed['groups'] = ['upsell', 'basic', 'structure'];
			unset(
				$items['seform'],
				$items['contactform'],
				$items['catalog'],
				$items['fapi'],
				$items['pricelist'],
				$items['menu'],
				$items['event_calendar'],
					$items['wpcomments'],
				$items['breadcrumbs'],
				$items['cookie_management'],
				$items['recent_posts'],
				$items['google_map'],
				$items['member_download']
				);
		}

		if ($page_type !== 'mwupsell') {
			unset($groups['upsell']);
		}

		if (mw_is_lite_editor()) {
			$allowed['groups'] = ['basic', 'structure', 'social'];
			$allowed['elements'] = ['text', 'title', 'image', 'button', 'icon', 'bullets', 'graphic', 'seform', 'features', 'video', 'image_gallery', 'testimonials', 'fapi', 'cookie_management', 'html', 'box', 'recent_posts', 'like', 'likebox'];
		}

		if (isset($allowed)) {
			foreach ($groups as $key => $group) {
				if (!in_array($key, $allowed['groups'])) {
					unset($groups[$key]);
				} elseif (count($group['elements']) && isset($allowed['elements'])) {
					foreach ($group['elements'] as $el_key => $el_val) {
						if (!in_array($el_val, $allowed['elements'])) {
							unset($groups[$key]['elements'][$el_key]);
						}
					}
				}
			}
		}

		echo '<div class="mw_elements_search_container">'
		. '<input type="text" name="" placeholder="' . __('Najít element', 'cms_ve') . '" class="mw_elements_search" />'
		. '<span class="mw_element_search_icon">' . mw_icon('icon-search') . '</span>'
		. '<a href="#" class="mw_element_search_storno">' . mw_icon('icon-x') . '</a>'
		. '</div>';

		$i = 0;
		foreach ($groups as $key => $group) {
			echo '<div class="mw_elements_group_container mw_elements_group_container_' . $i . '">';
			if (isset($group['name'])) {
				echo '<div class="mw_elements_group_title">' . $group['name'] . '</div>';
			}
			if (count($group['elements'])) {
				foreach ($group['elements'] as $el_key) {
					if (isset($items[$el_key])) {
						?>
						<div class="add_element_item_c" data-name="<?php echo $items[$el_key]['name']; ?>">
						<div class="mw_page_builder_draggable add_element_item add_element_item_<?php echo $el_key; ?>"
							 data-element="<?php echo $el_key; ?>" data-type="element">
						<?php if (isset($items[$el_key]['icon'])) {
							echo '<img src="' . $items[$el_key]['icon'] . '" title="" alt="" />';
						} elseif (isset($items[$el_key]['svg_icon'])) { ?>
								<i>
									<svg role="img">
										<use xlink:href="<?php echo $items[$el_key]['svg_icon']; ?>"></use>
									</svg>
								</i>
						<?php } else {
							$iconName = $items[$el_key]['element_icon'] ?? $el_key;
							?>
								<i>
									<svg role="img">
										<use xlink:href="<?php echo MW_UI_ICONS_URL; ?>elements.svg#element-<?php echo $iconName; ?>"></use>
									</svg>
								</i>
						<?php } ?>
							<span><?php echo $items[$el_key]['name']; ?></span>
						</div>
						</div><?php
					}
				}
			}
			echo '<div class="cms_clear"></div></div>';

			$i++;
		}
		echo '<div class="mw_elements_search_empty">'
		. __('Nebyl nalezen žádný element', 'cms_ve')
		. '<br><a href="#" class="mw_element_search_storno">' . __('Zrušit vyhledávání', 'cms_ve') . '</a>'
		. '</div>';
	}

	function open_row_setting()
	{
		global $mwContainer;
		$row_setting = $mwContainer->row_setting;
		$decoded_layer = $_POST['code'];
		$row = $decoded_layer['style'];

		$tabs = [];

		if (isset($decoded_layer['type']) && $decoded_layer['type'] == 'slider') {
			$tabs = [
				'slider' => __('Slider', 'cms_ve'),
				'slider_set' => __('Nastavení', 'cms_ve'),
				'show' => __('Viditelnost', 'cms_ve'),
			];
		} elseif (isset($decoded_layer['type']) && $decoded_layer['type'] == 'slide') {
			$tabs = [
				'slide_set' => __('Nastavení slidu', 'cms_ve'),
				'slide_advance' => __('Pokročilé', 'cms_ve'),
			];
		} else {
			$tabs = [
				'basic' => __('Základní', 'cms_ve'),
				'advance' => __('Pokročilé', 'cms_ve'),
				'show' => __('Viditelnost', 'cms_ve'),
			];
		}

		echo '<div class="mw_editor_panel_head">';
		echo '<span class="mw_editor_panel_head_title">' . __('Blok', 'cms_ve') . '</span>';
		echo '<a class="mw_editor_panel_head_help mw_tooltip" data-title="' . __('Nápověda k bloku', 'cms_ve') . '" href="' . mwHelp::getHelpLink('block') . '" target="_blank">?</a>';
		echo '</div>';

		echo '<ul class="mw_admin_tabs mw_panel_setting_tabs mw_panel_row_setting_tabs">';
		// row set tabs
		$i = 1;
		foreach ($tabs as $tab_key => $tab_val) {
			echo '<li><a href="' . $tab_key . '" data-group="mw_panel_row_setting" ' . ($i == 1 ? 'class="active"' : '') . '>' . $tab_val . '</a></li>';
			$i++;
		}
		echo '</ul>';

		// row set setting
		$i = 1;
		foreach ($tabs as $tab_key => $tab_val) {
			echo '<div class="mw_panel_setting mw_scroll mw_panel_row_setting mw_panel_row_setting_tab mw_panel_row_setting_' . $tab_key . ' mw_admin_tab mw_admin_tab_' . $i . '">';

			write_meta($row_setting[$tab_key], $row, 've_style', 've_style');

			echo '</div>';
			$i++;
		}

		die();
	}

	/* Element actions ********
	*******************************************************************************  */

	function open_element_setting()
	{
		global $mwContainer;

		$element = $_POST['code'];
		$element_set = $mwContainer->elements[$element['type']];

		echo '<div class="mw_editor_panel_head">';
		echo '<span class="mw_editor_panel_head_title">' . $element_set['name'] . '</span>';
		if (isset($element_set['help'])) {
			echo '<a class="mw_editor_panel_head_help mw_tooltip" data-title="' . __('Nápověda k elementu', 'cms_ve') . '" href="' . $element_set['help'] . '" target="_blank">?</a>';
		}
		echo '</div>';

		if (isset($element_set['tab_setting'])) {
			echo '<ul class="mw_admin_tabs mw_panel_setting_tabs mw_panel_element_setting_tabs">';
			$i = 1;
			foreach ($element_set['tab_setting'] as $set_tab) {
				echo '<li><a href="' . $set_tab['id'] . '" data-group="mw_panel_element_setting" ' . ($i == 1 ? 'class="active"' : '') . '>' . $set_tab['name'] . '</a></li>';
				$i++;
			}
			echo '<li><a href="advanced" data-group="mw_panel_element_setting">' . __('Pokročilé', 'cms_ve') . '</a></li>';
			echo '</ul>';
			$i = 1;
			foreach ($element_set['tab_setting'] as $set_tab) {
				echo '<div class="mw_panel_setting mw_scroll mw_panel_element_setting mw_panel_element_setting_tab mw_panel_element_setting_' . $set_tab['id'] . ' mw_admin_tab mw_admin_tab_' . $i . '">';
				write_meta($set_tab['setting'], $element, 've_style', 've_style', '', 've');
				echo '</div>';
				$i++;
			}

			// config
			echo '<div class="mw_panel_setting mw_scroll mw_panel_element_setting mw_panel_element_setting_tab mw_panel_element_setting_advanced mw_admin_tab">';

			$el_config['style'] = $element['config'] ?? [];
			write_meta($mwContainer->element_config, $el_config, 've_config', 've_config', '', 've');

			echo '<input type="hidden" name="ve_style[mw30]" value="1" />';

			echo '</div>';
		} else {
			echo '<ul class="mw_admin_tabs mw_panel_setting_tabs mw_panel_element_setting_tabs">';
			echo '<li><a href="advanced" class="active" data-group="mw_panel_element_setting">' . __('Pokročilé', 'cms_ve') . '</a></li>';
			echo '</ul>';
			echo '<div class="mw_panel_element_setting mw_panel_setting mw_scroll">';
			//write_meta($mwContainer->elements[$element['type']]['setting'], $element, 've_style', 've_style', '', 've');
			$el_config['style'] = $element['config'];
			write_meta($mwContainer->element_config, $el_config, 've_config', 've_config', '', 've');
			echo '</div>';
		}
		?>
		<input type="hidden" name="element_type" value="<?php echo $element['type'] ?>"/>
		<input type="hidden" name="type" value="<?php echo $_POST['type'] ?>"/>
		<?php
		die();
	}

	function builder_actions()
	{
		// create page
		if (isset($_POST['ve_change_template_action']) && $_POST['import_template_upload'] && $_POST['object_id'] === 'page') {
			MwWebInstall()->importItemZip($_POST['object_id'], $_POST['item_id']);
		}
		//change page template
		if (isset($_POST['ve_change_template_action'])) {
			$object = mwSetting()->getObject($_POST['object_id'] ?? 'page');
			$object_id = $object->getObjectType() === 'page' ? 'page' : $object->getId();

			$item_id = $_POST['item_id'];
			$template = $_POST['template'] ?? 'page/1/';

			$temp = explode('/', $template);

			require_once(MW()->get_template_dir($temp[0]) . MW()->p_templates[$temp[0]]['path'] . $temp[1] . '/config.php');

			foreach ($object->getSettingForCategory('appearance') as $set) {
				MWDB()->deletePostMeta($item_id, $set['id']);
			}

			if (!empty($config['setting'])) {
				foreach ($config['setting'] as $key => $val) {
					MWDB()->setPostMeta($item_id, $key, $val);
				}
			}
			MWDB()->deletePostMeta($item_id, 've_page_config');
			MWDB()->deletePostMeta($item_id, 've_page_template');
			if (isset($config['config'])) {
				MWDB()->setPostMeta($item_id, 've_page_config', $config['config']);
			}
			MWDB()->setPostMeta($item_id, 've_page_template', ['type' => $object_id, 'directory' => $template]);

			if (isset($config['layer'])) {
			// change layer
			MWDB()->deleteLayer($item_id, $object_id);
			MWDB()->addLayer($item_id, $object_id, $config['layer']);
			MWDB()->updatePost(['ID' => $item_id, 'post_content' => $config['layer']]);
			}
			$url = isset($_GET['window_editor']) ? add_query_arg($_GET, get_home_url()) : get_permalink($item_id);

			wp_redirect($url);

			die();
		}

		// set home
		if (isset($_GET['ve_set_home'])) {
			update_option('show_on_front', 'page');
			update_option('page_on_front', $_GET['ve_set_home']);
			MW()->getLicense()->sendNotify();
			wp_redirect(home_url());
			die();
		}

		// add licence key
		if (isset($_POST['add_license_key_field']) && wp_verify_nonce($_POST['add_license_key_field'], 'add_license_key')) {
			delete_option('web_option_license');
			add_option('web_option_license', ['license' => $_POST['licence_key']]);
			wp_redirect('//' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
			die();
		}
	}

	function change_template()
	{
		$object = mwSetting()->getObject($_POST['object_id']);
		$template = $object->service()->getTemplate($_POST['item_id']);

		$objectId = $object->getObjectType() === 'page' ? 'page' : $object->getId();

		?>
		<div class="mw_admin_setting_container mw_page_template_selector">
			<input type="hidden" name="item_id" value="<?php echo $_POST['item_id']; ?>">
			<input type="hidden" name="object_id" value="<?php echo $_POST['object_id']; ?>">
			<input type="hidden" name="ve_change_template_action" value="1">
			<?php
			echo MWInstallator()->templateSelector($objectId, $template, true);
			?>
		</div>
		<?php
		die();
	}

	function openPopSetting_ajax()
	{
		if ($_POST['edited']) {
			$_SESSION['ve_layer_autosave'][$_POST['item_id']] = visualEditor::code(MWPageBuilder::create_post_layer());
		}

		echo '<div class="mw_admin_setting_container mw_admin_setting_narrow_container">';
		echo '<div class="mw_messages_container"></div>';
		mwSetting()->printObjectSet($_POST['object_id'], $_POST['set_id'], $_POST['item_id']);
		echo '</div>';

		die();
	}

	function openSettingInpanel_ajax()
	{
		echo '<div class="mw_editor_panel_head">';
		echo '<span class="mw_editor_panel_head_title">' . $_POST['title'] . '</span>';

		$help_link = mwHelp::getHelpLink($_POST['set_id']);
		if ($help_link) {
			echo '<a class="mw_editor_panel_head_help mw_tooltip" data-title="' . __('Nápověda k nastavení', 'cms_ve') . '" href="' . $help_link . '" target="_blank">?</a>';
		}

		echo '</div>';

		echo '<div class="mw_panel_setting mw_scroll mw_panel_option_setting">';

		$object = mwSetting()->getObject($_POST['object_id']);
		$set = $object->getSetting($_POST['set_id']);

		if (isset($set['switch'])) {
			$show_switch = isset($_POST['show_switch']) ? intval($_POST['show_switch']) : 1;
			$this->writeSwitchSetting($object, $set, $show_switch);
		} else {
			mwSetting()->printObjectSet($_POST['object_id'], $_POST['set_id'], $_POST['item_id']);
		}

		echo '</div>';
		die();
	}

	function writeSwitchSetting($object, $setting, $show_switch = 1)
	{
		$meta = $object->service()->getMeta($_POST['item_id'], $setting['id']);

		$setting['switch']['options']['global']['option'] = apply_filters('mw_change_switch_option', $setting['switch']['options']['global']['option']);

		$content = $meta[$setting['switch']['id']] ?? 'global';

		if ($show_switch) {
			echo '<div class="mw_toggle_group mw_onedit_action" data-type="toggle_group">';

			echo '<div class="mw_toggle_group_content mw_switch_global_local">';

			echo '<div class="set_form_row">';
			echo '<div class="label"><span>' . $setting['switch']['label'] . '</span><div class="cms_clear"></div></div>';
			echo '<div class="mw_onedit_action" data-type="switch_setting" data-action="switch_setting" data-target="" data-class="" data-css="" data-setname="" data-setting="" data-device="desktop">';

			echo '<select name="' . $_POST['set_id'] . '[' . $setting['switch']['id'] . ']">';

			foreach ($setting['switch']['options'] as $sw_key => $switch) {
				if (!mw_is_lite_editor() || $sw_key != 'page') {
					$sw_attr = 'data-setid=""';
					if ($sw_key != 'none') {
						$sw_attr = 'data-setid="' . $switch['option'] . '"';
					}

					echo '<option ' . $sw_attr . ' value="' . $sw_key . '"' . ($sw_key == $content ? ' selected="selected"' : '') . '>' . $switch['name'] . '</option>';
				}
			}
			echo '</select>';
			echo '</div>';
			echo '</div>';

			echo '</div>';
			echo '</div>';
		} else {
			if ($content == 'page') {
				if ($_POST['set_id'] == 've_header') {
					$text = __('Stránka používá vlastní hlavičku', 'cms_ve');
					$tooltip = __('Stránka má nastavenou vlastní hlavičku. Nastavení jakou hlavičku použít najdete v nastavení vzhledu stránky.', 'cms_ve');
				} else {
					$text = __('Stránka používá vlastní patičku', 'cms_ve');
					$tooltip = __('Stránka má nastavenou vlastní patičku. Nastavení jakou patičku použít najdete v nastavení vzhledu stránky.', 'cms_ve');
				}
				echo '<div class="set_form_info_row">';
				echo $text;
				echo ' ' . mwAdminComponents::tooltip([
					'text' => $tooltip,
				]);
				echo '</div>';
			}
			echo '<input type="hidden" name="' . $_POST['set_id'] . '[' . $setting['switch']['id'] . ']" value="' . $content . '" checked="checked"/>';
		}

		echo '<div class="mw_panel_setting_toswitch_container ' . ($show_switch && $content == 'global' ? 'cms_nodisp' : '') . '">';

		if (!$meta || $content == 'global') {
			mwSetting()->printOptionSetting($setting['switch']['options']['global']['option']);
		} elseif ($meta[$setting['switch']['id']] == 'page') {
			mwSetting()->printObjectSet('page', $_POST['set_id'], $_POST['item_id']);
		}

		echo '</div>';

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="save_switch_setting" value="' . $setting['id'] . '"/>';
		echo '<input type="hidden" name="item_id" value="' . $_POST['item_id'] . '"/>';
	}

	function switch_editor_setting_inpanel()
	{
		if ($_POST['type'] == 'page') {
			mwSetting()->printObjectSet('page', $_POST['set_id'], $_POST['item_id']);
		} elseif ($_POST['type'] == 'global') {
			mwSetting()->printOptionSetting($_POST['set_id']);
		}

		die();
	}

	function save_panel_setting()
	{
		if (isset($_POST['object_id'])) {
			if (mwSetting()->verifyNonce('mw_save_setting_nonce')) {
				mwSetting()->saveObjectSet($_POST['object_id'], $_POST['set_id'], $_POST['item_id'], $_POST);
			}
		} elseif (isset($_POST['setting_id'])) {
			if (mwSetting()->verifyNonce('mw_save_setting_nonce')) {
				mwSetting()->saveSetting($_POST['setting_id'], $_POST['setting']);
			}
		} elseif (isset($_POST['member_section_setting'])) {
			// @TODO remove global vs local header/footer member sections exception
			if (mwSetting()->verifyNonce('mw_save_setting_nonce')) {
				update_option($_POST['member_section_setting'], $_POST['setting']);
			}
		}

		// save show option from switch to meta of page
		if (isset($_POST['save_switch_setting']) && mwSetting()->verifyNonce('mw_save_setting_nonce')) {
			$option_name = $_POST['save_switch_setting'];
			$meta = get_post_meta($_POST['item_id'], $option_name, true);
			if (!$meta || !is_array($meta)) {
				$meta = [];
			}
			$meta['show'] = $_POST[$option_name]['show'];
			update_post_meta($_POST['item_id'], $option_name, $meta);
			//echo $_POST['save_switch_setting'];
			//print_r($meta);
		}

		die();
	}

	public static function create_post_layer()
	{
		$content = [];
		$layer = isset($_POST['layer']) ? visualEditor::json_decode($_POST['layer']) : [];

		if (isset($layer['rows'])) {
			foreach ($layer['rows'] as $rkey => $row_decoded) {
				$content[$rkey] = $row_decoded;
				if (isset($row_decoded['content']) && $row_decoded['content']) {
					foreach ($row_decoded['content'] as $ckey => $col) {
						$content[$rkey]['content'][$ckey]['content'] = [];
						if (isset($layer['elements'][$rkey][$ckey])) {
							$i = 0;
							foreach ($layer['elements'][$rkey][$ckey] as $element) {
								if ($element) {
									$content[$rkey]['content'][$ckey]['content'][$i] = $element;
									// if subelement
									if ($content[$rkey]['content'][$ckey]['content'][$i]['type'] == 'twocols' || $content[$rkey]['content'][$ckey]['content'][$i]['type'] == 'box') {
										$content[$rkey]['content'][$ckey]['content'][$i]['content'] = [];
										//first col
										if (isset($layer['subelements'][$rkey][$ckey][$i][0]) && is_array($layer['subelements'][$rkey][$ckey][$i][0])) {
											foreach ($layer['subelements'][$rkey][$ckey][$i][0] as $subelement) {
												if ($subelement) {
													$content[$rkey]['content'][$ckey]['content'][$i]['content'][0][] = $subelement;
												}
											}
										}
										//second col
										if (isset($layer['subelements'][$rkey][$ckey][$i][1]) && is_array($layer['subelements'][$rkey][$ckey][$i][1])) {
											foreach ($layer['subelements'][$rkey][$ckey][$i][1] as $subelement) {
												if ($subelement) {
													$content[$rkey]['content'][$ckey]['content'][$i]['content'][1][] = $subelement;
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
		}

		return apply_filters('mw_create_layer', $content);
	}

	function add_top_panel_menu($id, $menu)
	{
		$this->top_panel_menu[$id] = $menu;
	}

	function addFastNav($item, $id)
	{
		$this->fast_nav[$id] = $item;
	}

	/* Create page setting
	************************************************************************** */

	function save_new_page($post_setting, $template, $layer = '', $type = 'page')
	{
		$post_id = wp_insert_post($post_setting);
		$this->create_page_setting($post_id, $template, $layer, $type);

		return $post_id;
	}

	function save_new_window_post($post_setting, $template, $layer = '', $type = 'editor')
	{
		$post_id = wp_insert_post($post_setting);
		$this->create_page_setting($post_id, $template, $layer, $type);

		return $post_id;
	}

	function create_page_setting($post_id, $template, $layer = '', $type = 'page')
	{
		if ($template) {
			$temp = explode('/', $template);
			if (!isset(MW()->p_templates[$temp[0]]) || !file_exists(MW()->get_template_dir($temp[0]) . MW()->p_templates[$temp[0]]['path'] . $temp[1] . '/config.php')) {
				$temp[0] = 'page';
				$temp[1] = '1';
			}
			require(MW()->get_template_dir($temp[0]) . MW()->p_templates[$temp[0]]['path'] . $temp[1] . '/config.php');
			global $config;

			if (!empty($config['setting'])) {
				foreach ($config['setting'] as $key => $val) {
					update_post_meta($post_id, $key, $val);
				}
			}
			$newlayer = $layer ?: ($config['layer'] ?? '');
			if (isset($config['config'])) {
				add_post_meta($post_id, 've_page_config', $config['config']);
			}
			add_post_meta($post_id, 've_page_template', ['type' => $type, 'directory' => $template]);
		} else {
			$newlayer = $layer ?: '';
		}
		// save layer
		wp_update_post(['ID' => $post_id, 'post_content' => $newlayer]);
		MWDB()->setLayer($post_id, $type, $newlayer);
	}

	function builder_footer()
	{
		if (!is_mw_setting()) {
			$template_config = get_post_meta($this->post_id, 've_page_config', true);
			if (!isset($template_config['hide_rows']) && !isset($template_config['delete_rows'])) {
				$class = 'mw_clipboard_row_item';
				if (isset($_SESSION['ve_copy_row']) && $_SESSION['ve_copy_row']) {
					$class .= ' mw_clipboard_row_item_show';
				}

				echo '<div class="' . $class . '">'
				. '<div class="mw_page_builder_draggable mw_clipboard_item" data-type="row" data-rowtype="clipboard">'
				. '<svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-clipboard"></use></svg>'
				. '</div>'
				. '<a href="" class="mw_clipboard_row_item_close"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-x"></use></svg></a>'
				. '</div>';
			}
		}

		// buttons
		global $vePage;
		$google_fonts = [];
		$file_fonts = [];
		$all_file_fonts = MW()->file_fonts;
		$all_google_fonts = MW()->google_fonts;
		foreach (mwButtonStyles()->getStyles() as $button) {
			if (isset($button['font']) && isset($button['font']['font-family'])) {
				if (isset($all_google_fonts[$button['font']['font-family']]) && $all_google_fonts[$button['font']['font-family']]) {
					$google_fonts[$button['font']['font-family']][$button['font']['weight']] = $button['font']['weight'];
				}
				if (isset($all_file_fonts[$button['font']['font-family']]) && $all_file_fonts[$button['font']['font-family']]) {
					$file_fonts[$button['font']['font-family']][$button['font']['weight']] = $all_file_fonts[$button['font']['font-family']][$button['font']['weight']];
				}
			}
		}
		$vePage->display->printGoogleFonts($google_fonts);
		$vePage->display->printFileFonts($file_fonts);

		$this->alert_zone();

		//Add JS "ajaxurl" variable for everyone, including non-authorized users.
		echo '<script type="text/javascript">
		var ajaxurl = "' . admin_url('admin-ajax.php', 'relative') . '";
		var locale = "' . get_locale() . '";
		var template_directory_uri = "' . get_template_directory_uri() . '";
		</script>';
	}

	function alert_zone()
	{
		$alerts = [];

		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
			$alerts[] = sprintf(
					__('Váš prohlížeč není podporován, proto nemusí editace fungovat správně. Prosím použijte %s nebo %s.', 'cms_ve'),
					'<a href="https://www.mozilla.org/firefox/new/" target="_blank">Firefox</a>',
					'<a href="https://www.google.com/chrome/" target="_blank">Google Chrome</a>'
			);
		}

		global $wp_version;
		if ($wp_version && version_compare($wp_version, MW_MINIMUM_WP_VERSION, '<')) {
			$updateLink = home_url('wp-admin/update-core.php');
			$alerts[] = __('Používáte nepodporovanou verzi Wordpressu (verzi ' . $wp_version . '). Proveďte prosím <a href="' . $updateLink . '" target="_blank">aktualizaci na vyšší verzi</a>. V opačném případě nemusí Mioweb v budoucnu pracovat správně.', 'cms_ve');
		}

		if (!MW()->getLicense()->isHosting()) {
			// Check PHP version
			$phpVersion = phpversion();
			if (version_compare($phpVersion, MW_MINIMUM_PHP_VERSION_SOFT, '<')) {
				$alerts[] = __('Používáte nepodporovanou verzi PHP (verzi ' . $phpVersion . '). Mioweb vyžaduje verzi ' . MW_MINIMUM_PHP_VERSION_SOFT . '. Obraťte se na podporu svého hostingu a proveďte aktualizaci na vyšší verzi PHP. V opačném případě nemusí Mioweb v budoucnu pracovat správně.', 'cms_ve');
			}

			if (!MW()->getLicense()->isRecurrent()) {
				// Check latest available MW version
				$licenseInfo = get_option('mw_licence_info');
				$latestAvailableRelease = $licenseInfo['last_version'] ?? null;
				$allowedVersion = $licenseInfo['allowed_version'] ?? true;

				if ($latestAvailableRelease !== null && !$allowedVersion) {
					$themeData = wp_get_theme();
					$installedVersion = $themeData instanceof WP_Theme ? $themeData->get('Version') : null;

					if ($installedVersion !== null && version_compare($installedVersion, $latestAvailableRelease->version, '>')) {
						global $wp;
						$refreshUrl = home_url(add_query_arg(['checkLicense' => 1], $wp->request));

						$alerts[] = sprintf(
							__('Máte nainstalovaný Mioweb ve verzi %s. Poslední verze, na kterou máte s Vaší licencí
						nárok, je %s. Pokud chcete nadále využívat současnou nebo novější verzi Miowebu, aktivujte si službu
						<a href="%s/licenses?openModal=supportOrderFormModal" target="_blank">Podpora a aktualizace</a>.
						V opačném případě si prosím nainstalujte <a href="%s" target="_blank">Mioweb verze %s</a>.
						Po provedení aktivace Podpora a aktualizací nebo snížení verze klikněte
						<a href="%s">zde pro ověření aktuálního stavu.</a>', 'cms_ve'),
							$installedVersion,
							$latestAvailableRelease->version,
							MY_ACCOUNT_URL,
							$latestAvailableRelease->download_url,
							$latestAvailableRelease->version,
							$refreshUrl
						);
					}
				}
			}

			// TODO Remove this check when `member-sections` branch is merged and released
			// Check MySQL table storage engine
			$engineError = $this->checkTableEngines();
			if ($engineError !== null) {
				$alerts[] = $engineError;
			}
		}

		foreach ($alerts as $alert) {
			echo '<div class="mw_builder_alert_info">'
					. '<span>' . mw_icon('icon-alert-triangle') . '</span>'
					. $alert
					. '<a class="close" href="#">' . mw_icon('icon-x') . '</a>'
					. '</div>';
		}
	}

	// TODO Remove this check when `member-sections` branch is merged and released
	private function checkTableEngines(): ?string
	{
		if (!MW()->is_module_active('member')) {
			return null;
		}

		global $wpdb;
		$wpPostsTable = $wpdb->prefix . 'posts';
		$wpUsersTable = $wpdb->prefix . 'users';

		$wpPostsStatus = self::getTableStatus($wpPostsTable);
		$wpUsersStatus = self::getTableStatus($wpUsersTable);

		if (isset($wpPostsStatus['Engine'], $wpUsersStatus['Engine'], $wpPostsStatus['Data_length'], $wpUsersStatus['Data_length'])) {
			$tableList = [];
			$sizeThreshold = 350000000; // 350 MB

			if (strtolower($wpPostsStatus['Engine']) !== 'innodb' && $wpPostsStatus['Data_length'] > $sizeThreshold) {
				$tableList[] = $wpPostsTable;
			}

			if (strtolower($wpUsersStatus['Engine']) !== 'innodb' && $wpUsersStatus['Data_length'] > $sizeThreshold) {
				$tableList[] = $wpUsersTable;
			}

			if ($tableList) {
				$sqlList = '<ul>';
				$sqlList .= '<li><code>SET @@sql_mode := REPLACE(REPLACE(@@sql_mode, "NO_ZERO_DATE", ""), "NO_ZERO_IN_DATE", "");</code></li>';
				foreach ($tableList as $table) {
					$sqlList .= '<li><code>ALTER TABLE ' . $table . ' ENGINE=InnoDB;</code></li>';
				}
				$sqlList .= '</ul>';

				return sprintf(
						__('<p>Databáze vašeho webu využívá storage engine "%s", který již nebude od příští verze Miowebu podporovaný.
						Pro zachování funkčnosti webu v příštích verzích Miowebu je potřeba, abyste ve spolupráci s technickou
						podporou vašeho webhostingu změnili storage engine databázových tabulek "%s" na <strong>InnoDB</strong>.
						Toho lze docílit spuštěním těchto SQL dotazů:</p><br>
						%s', 'cms_ve'),
						strtolower($wpPostsStatus['Engine']) !== 'innodb' ? $wpPostsStatus['Engine'] : $wpUsersStatus['Engine'],
						implode('" a "', $tableList),
						$sqlList
				);
			}
		}

		return null;
	}

	// TODO Remove this method when `member-sections` branch is merged and released
	private function getTableStatus(string $tableName): array
	{
		return (array) MWDB()->getRow("SHOW TABLE STATUS WHERE Name = '$tableName'");
	}

	/*
	function menu_filter($nav_menu, $args = array())
	{

	if(isset($args->menu) && !isset($args->menu->term_id)) {
	$nav_menu = '<div class="menu_editbar_container">
	<div class="content_element_editbar">
	<a class="ve_edit_menu" data-modul="' . $this->modul_type . '" data-menuid="' . $args->menu . '" href="#" title="' . __('Editovat menu', 'cms_ve') . '"></a>
	</div>
	' . $nav_menu . '
	<div class="cms_clear"></div>
	</div>';
	}
	return $nav_menu;
	} */

	/* Editace menu
	************************************************************************** */

	function reload_menu()
	{
		if (isset($_POST['menu_id']) && wp_get_nav_menu_items($_POST['menu_id'])) {
			wp_nav_menu(['menu' => $_POST['menu_id'], 'after' => '<span></span>', 'container' => false]);
		}
		die();
	}

	function delete_menu_ajax()
	{
		wp_delete_nav_menu($_POST['page_id']);
		die();
	}

	//open create menu
	function open_create_menu()
	{
		echo '<div id="add_new_single_menu_container" class="add_new_menu_container">'
		. '<input id="add_new_menu_name" type="text" class="mw_input required" name="add_new_menu" placeholder="' . __('Jméno nového menu', 'cms_ve') . '" />'
		. '</div>';
		die();
	}

	//open single menu setting
	function open_single_menu_setting()
	{
		if ($_POST['menu_id']) {
			echo $this->ve_generate_edit_menu($_POST['menu_id']);
		}
		die();
	}

	// generate list of menu items
	function ve_generate_edit_menu($menu_id)
	{
		$menu = new \Mioweb\VisualEditor\Lib\NavMenu($menu_id);
		$menu_items = $menu->getNestedMenuItems();

		$pages = mwPage::getPages(['post_status' => 'publish,draft,private', 'hierarchical' => true]);

		$menu_list = mwAdminComponents::messageBox(__('Zanoření menu můžete ovládat posunutím položek vlevo nebo vpravo. Lze vytvářet menu pouze do třetí úrovně zanoření.', 'cms_ve'), [
			'type' => 'info_gray',
		]);

		$menu_list .= '<div class="mw_menu_manager_container mw_admin_setting_container">';

		$menu_list .= '<input type="hidden" value="' . $menu_id . '" name="menu_id">';


		$menu_list .= '<ol class="ve_nestedsortable ve_menu_manager_list ve_items_container_open_under">';

		foreach ($menu_items as $item) {
			$menu_list .= '<li class="ve_nestedsortable__item">';
			$menu_list .= $this->print_edit_menu_item($item, $pages);
			$menu_list .= $this->ve_generate_edit_menu_child_pages($item->children, $pages);
			$menu_list .= '</li>';
		}

		$menu_list .= '</ol>';
		$menu_list .= mwAdminComponents::button([
			'icon' => 'plus',
			'style' => 'secondary',
			'button_text' => __('Přidat položku menu', 'cms_ve'),
			'attrs' => 'data-id="0"',
		], 'mw_add_menu_item');

		$menu_list .= '</div>';

		return $menu_list;
	}

	/**
	 * Recursive function for infinite deep levels of menu children
	 *
	 * @param $menu_items array Array of menu items with nested children
	 * @param $pages array cache for existing WP pages
	 *
	 * @return string
	 */
	function ve_generate_edit_menu_child_pages($menu_items, $pages)
	{
		$return = '';

		if (!empty($menu_items)) {
			$return .= '<ol>';
			foreach ($menu_items as $item) {
				$return .= '<li class="ve_nestedsortable__item">';
				$return .= $this->print_edit_menu_item($item, $pages);
				$return .= $this->ve_generate_edit_menu_child_pages($item->children, $pages);
				$return .= '</li>';
			}
			$return .= '</ol>';
		}

		return $return;
	}

	// create new menu item
	function ve_generate_edit_menu_item()
	{
		if (!current_user_can('edit_posts')) {
			wp_die();
		}

		$pages = mwPage::getPages(['post_status' => 'publish,draft,private', 'hierarchical' => true]);
		$menu_item = new stdClass();
		$menu_item->ID = 'new_' . $_POST['id'];
		$menu_item->title = '';
		$menu_item->url = '';
		$menu_item->type = '';
		$menu_item->object_id = '0';
		$menu_item->db_id = '0';
		$menu_item->type = '';
		$menu_item->classes = [];
		$menu_item->menu_item_parent = '0';

		echo $this->print_edit_menu_item($menu_item, $pages, true);
		die();
	}

	// generate menu item
	function print_edit_menu_item($menu_item, $pages, $new = false)
	{
		$content = '<div class="ve_nestedsortable__item__wrap ve_item_container ' . ($new ? 'open' : '') . '">';
		$content .= '<div class="ve_item_head">';
		$content .= mwAdminComponents::icon(['icon' => 'move'], 've_sortable_handler');
		$content .= '<span class="ve_item_head_title">' . ($menu_item->title ?? __('Nová položka', 'cms_ve')) . '</span>';
		$content .= '<div class="ve_item_head_edit">';
		$content .= mwAdminComponents::iconLink([
			'icon' => 'edit-2',
			'title' => __('Editovat', 'cms'),
		], 've_edit_setting');
		$content .= mwAdminComponents::iconLink([
			'icon' => 'trash-2',
			'title' => __('Smazat', 'cms'),
		], 've_delete_setting');
		$content .= '</div>';
		$content .= '</div>';
		$content .= '<div class="ve_item_body">';

		$custom_url_val = $menu_item->type == 'custom' ? 1 : 0;

		$content .= mwAdminComponents::input([
				'name' => 'menu_item[' . $menu_item->ID . '][menu-item-classes]',
				'type' => 'hidden',
		], implode(' ', $menu_item->classes));

		// menu page
		$content .= '<div class="mw_menu_manager_item_set ' . ($custom_url_val ? 'mw_menu_item_type_custom' : '') . '">';
		$content .= '<div class="mw_menu_manager_item_url mw_menu_manager_item_set_col">';
		$content .= mwAdminComponents::inputLabel([
			'label' => __('Odkazovat na', 'cms_ve'),
		]);

		$content .= mwAdminComponents::selectPage([
			'pages' => $pages,
			'name' => 'menu_item[' . $menu_item->ID . '][menu-item-object-id]',
			'show_empty' => false,
		], $menu_item->object_id, 'mw_menu_manager_item_pageselect');

		$url = get_permalink($menu_item->object_id) != $menu_item->url ? $menu_item->url : 'https://';
		$content .= mwAdminComponents::input([
			'name' => 'menu_item[' . $menu_item->ID . '][menu-item-url]',
		], $url, 'mw_menu_manager_item_customurl');

		$content .= '<div class="">';
		$content .= mwAdminComponents::switch([
			'name' => 'custom_url[' . $menu_item->ID . ']',
			'switch_label' => __('Zadat vlastní URL', 'cms'),
		], $custom_url_val, 'mw_menu_manager_item_switch_url');
		$content .= '</div>';

		$new_window_val = isset($menu_item->target) && $menu_item->target == '_blank' ? 1 : 0;
		$content .= '<div class="">';
		$content .= mwAdminComponents::switch([
			'name' => 'menu_item[' . $menu_item->ID . '][menu-item-target]',
			'switch_label' => __('Otevřít odkaz v novém okně', 'cms_ve'),
			'value' => '_blank',
		], $new_window_val);
		$content .= '</div>';

		$content .= '</div>';

		// menu title
		$content .= '<div class="mw_menu_manager_item_title mw_menu_manager_item_set_col">';
		$content .= mwAdminComponents::inputLabel([
			'label' => __('Text odkazu', 'cms_ve'),
		]);
		$content .= '<input id="edit-menu-item-title-' . $menu_item->ID . '" class="mw_input" type="text" value="' . $menu_item->title . '" name="menu_item[' . $menu_item->ID . '][menu-item-title]">';
		$content .= '</div>';

		$content .= '</div>';

		if ($new) {
			$content .= '<input type="hidden" value="1" name="menu_item[' . $menu_item->ID . '][new]">';
		} else {
			$content .= '<input type="hidden" value="' . implode(' ', $menu_item->classes) . '" name="menu_item[' . $menu_item->ID . '][menu-item-classes]">';
			$content .= '<input type="hidden" value="' . $menu_item->attr_title . '" name="menu_item[' . $menu_item->ID . '][menu-item-attr-title]">';
			$content .= '<input type="hidden" value="' . $menu_item->object . '" name="menu_item[' . $menu_item->ID . '][menu-item-object]">';
			$content .= '<input type="hidden" value="' . $menu_item->type . '" name="menu_item[' . $menu_item->ID . '][menu-item-type]">';
		}
		$content .= '<input class="menu-item-data-db-id" type="hidden" value="' . $menu_item->ID . '" name="menu_item[' . $menu_item->ID . '][menu-item-db-id]">';
		$content .= '<input class="menu-item-data-parent-id" type="hidden" value="' . $menu_item->menu_item_parent . '" name="menu_item[' . $menu_item->ID . '][menu-item-parent-id]">';

		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	// create new menu
	function create_new_menu()
	{
		$menu_exists = wp_get_nav_menu_object($_POST['name']);

		if (!$menu_exists) {
			$menu_id = wp_create_nav_menu($_POST['name']);

			wp_send_json([
				'title' => $_POST['name'],
				'id' => $menu_id,
				'content' => $this->ve_generate_edit_menu($menu_id),
			]);
		} else {
			wp_send_json([
				'title' => '',
				'id' => '',
				'content' => 'false',
			]);
		}

		die();
	}

	// save menu
	function save_menu_setting()
	{
		$edit = false;
		if ($_POST['menu_id']) {
			$menu_items = wp_get_nav_menu_items($_POST['menu_id']);
			foreach ((array) $menu_items as $menu_item) {
				if (!isset($_POST['menu_item'][$menu_item->ID])) {
					wp_delete_post($menu_item->ID, true);
				}
			}
			$i = 1;
			if (isset($_POST['menu_item']) && is_array($_POST['menu_item'])) {
				$new_items_binding = [];

				foreach ($_POST['menu_item'] as $key => $item) {
					$item['menu-item-position'] = $i;
					$item['menu-item-status'] = 'publish';

					if (isset($_POST['custom_url'][$key])) {
						$item['menu-item-type'] = 'custom';
						$item['menu-item-object'] = 'custom';
						$item['menu-item-object-id'] = '0';
						if ($item['menu-item-url'] == '') {
							$item['menu-item-url'] = '#';
						}
						if ($item['menu-item-title'] == '') {
							$item['menu-item-title'] = __('Nová položka', 'cms_ve');
						}
					} else {
						$item['menu-item-type'] = 'post_type';
						$item['menu-item-object'] = 'page';
					}

					//If this is child of new item, we have to get new item ID from data, we have created
					if (substr($item['menu-item-parent-id'], 0, 4) === 'new_') {
						$item['menu-item-parent-id'] = $new_items_binding[$item['menu-item-parent-id']];
					}

					if (isset($item['new'])) {
						$new_item_id = wp_update_nav_menu_item($_POST['menu_id'], 0, $item);
						//Store new item WP ID for possible children
						$new_items_binding[$item['menu-item-db-id']] = $new_item_id;
					} else {
						//existing menu item
						wp_update_nav_menu_item($_POST['menu_id'], $item['menu-item-db-id'], $item);
					}

					$i++;
				}
				$edit = true;
			}
		} else {
			$edit = true;
		}

		$menu = $_POST['menu_id'] ?: '';

		if ($edit && isset($_POST['modul'])) {
			//save menu to right place - global x local, web x blog x member..., header x footer
			$page_set = get_post_meta($_POST['post_id'], 've_' . $_POST['location'], true);

			if (isset($page_set['show']) && $page_set['show'] == 'page') {
				$page_set['menu'] = $menu;
				update_post_meta($_POST['post_id'], 've_' . $_POST['location'], $page_set);
			} else {
				$mod = $_POST['modul'] == 'web' ? 've' : $_POST['modul'];

				$global_set = get_option($mod . '_' . $_POST['location']);

				if ($global_set['show'] == 'global') {
					$mod = 've';
					$global_set = get_option($mod . '_' . $_POST['location']);
				}
				if ($mod == 'member') {
					mwMemberModule()->builderMemberInit($_POST['post_id']);
					$global_set['members'][mwMemberModule()->memberSection()->getId()]['menu'] = $menu;
				} else {
					$global_set['menu'] = $menu;
				}
				update_option($mod . '_' . $_POST['location'], $global_set);
			}
		}
		//print new menu
		if (isset($_POST['location'])) {
			$this->modul_type = $_POST['modul'];
			if (!$edit) {
				$menu = '';
			}
			if ($_POST['location'] == 'header') {
				$this->header_menu($menu);
			} else {
				$this->footer_menu($menu);
			}
//		} else {
			//wp_send_json(['id' => $menu]);
		}

		die();
	}

	/* Get user browser
	************************************************************************** */

	function get_user_browser()
	{
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$ub = '';

		if (preg_match('/MSIE/i', $u_agent)) {
			$ub = 'ie';
		} elseif (preg_match('/Firefox/i', $u_agent)) {
			$ub = 'firefox';
		} elseif (preg_match('/Chrome/i', $u_agent)) {
			$ub = 'chrome';
		} elseif (preg_match('/Safari/i', $u_agent)) {
			$ub = 'safari';
		} elseif (preg_match('/Flock/i', $u_agent)) {
			$ub = 'flock';
		} elseif (preg_match('/Opera/i', $u_agent)) {
			$ub = 'opera';
		}

		return $ub;
	}


	/* Admin edit page
	************************************************************************** */

	/* Mazání postu ************** */

	function delete_page_hook($pid, $post)
	{
		global $wpdb;
		if ($wpdb->get_var($wpdb->prepare('SELECT vpl_post_id FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_post_id = %d AND vpl_type='" . $post->post_type . "'", $pid))) {
			return $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_post_id = %d AND vpl_type='" . $post->post_type . "'", $pid));
		}

		return true;
	}

	/* Obnovení revize
	************** */

	function layer_revision($post_id)
	{
		$post = get_post($post_id);
		$allowed = ['page', 'cms_footer', 'weditor', 've_header', 've_elvar', 'mw_slider'];
		if (in_array($post->post_type, $allowed)) {
			global $wpdb;
			$wpdb->update($wpdb->prefix . 've_posts_layer', ['vpl_layer' => $post->post_content], ['vpl_post_id' => $post_id]);
		}
	}


	/* Aktivace šablony
	************************************************************************** */

	function ve_activation($versions)
	{
		if (empty($versions) || !isset($versions['visualeditor'])) {
			global $wpdb;
			$temp_dir = str_replace(home_url(), '', get_bloginfo('template_url'));

			$db_table_name = $wpdb->prefix . 've_posts_layer';
			if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
				$charset_collate = '';

				if (!empty($wpdb->charset)) {
					$charset_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if (!empty($wpdb->collate)) {
					$charset_collate .= " COLLATE $wpdb->collate";
				}

				$sql = 'CREATE TABLE IF NOT EXISTS  ' . $db_table_name . " (
          vpl_id bigint(20) NOT NULL AUTO_INCREMENT,
          vpl_post_id bigint(20) NOT NULL,
          vpl_type varchar(10) NOT NULL,
          vpl_layer longtext NOT NULL,
          PRIMARY KEY (`vpl_id`)) $charset_collate;";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
			}

			visualEditor::createEmailTable();

			// set layer and template for all pages
			$pages = get_pages(['post_status' => 'publish,inherit,pending,private,future,draft,trash']);
			foreach ($pages as $page) {
				$oldcontent = [
					'0' => [
						'class' => '',
						'style' => [
							'background_color' => [
								'color1' => '#ffffff',
								'transparency1' => '1.00',
								'rgba1' => 'rgba(255, 255, 255, 1)',
								'color2' => '',
								'transparency2' => '',
								'rgba2' => '',
							],
							'background_setting' => 'image',
							'background_image' => [
								'position' => '',
								'image' => '',
								'imageid' => '',
								'pattern' => '',
								'tablet' => [
									'position' => '',
									'image' => '',
									'imageid' => '',
									'pattern' => '',
								],
								'mobile' => [
									'position' => '',
									'image' => '',
									'imageid' => '',
									'pattern' => '',
								],
								'overlay_color' => [
									'color' => '#000000',
									'transparency' => '0.5',
									'rgba' => 'rgba(0,0,0,0.5)',
								],
								'efect' => '',
								'repeat' => 'no-repeat',
							],
							'slider_overlay_color' => [
								'color' => '',
								'transparency' => '0.7',
								'rgba' => '',
							],
							'video_type' => 'iframe',
							'video_url' => '',
							'background_video_mp4' => '',
							'background_video_webm' => '',
							'background_video_ogg' => '',
							'video_image' => [
								'position' => '50% 50%',
								'image' => '',
								'imageid' => '',
								'cover' => '1',
							],
							'video_overlay_color' => [
								'color' => '',
								'transparency' => '0.7',
								'rgba' => '',
							],
							'row_height' => 'default',
							'min-height' => '100',
							'arrow_color' => '#fff',
							'content_align' => 'top',
							'text' => 'auto',
							'font' => [
								'font-family' => '',
								'weight' => '',
								'font-size' => '',
								'color' => '',
							],
							'link_color' => '',
							'type' => 'basic',
							'row_padding' => 'big',
							'padding_top' => '50',
							'padding_bottom' => '50',
							'padding_left' => [
								'size' => '',
								'unit' => 'px',
							],
							'padding_right' => [
								'size' => '',
								'unit' => 'px',
							],
							'border-top' => [
								'size' => '',
								'style' => 'solid',
								'color' => '',
							],
							'border-bottom' => [
								'size' => '',
								'style' => 'solid',
								'color' => '',
							],
							'margin_top' => '',
							'margin_bottom' => '',
							'css_class' => '',
							'row_anchor' => '',
							'delay' => '',
						],
						'content' => [
							'0' => [
								'type' => 'col-one',
								'class' => '',
								'content' => [
									'0' => [
										'type' => 'text',
										'style' => [
											'font' => [
												'font-size' => '',
												'font-family' => '',
												'weight' => '',
												'line-height' => '',
												'color' => '',
											],
											'li' => '',
											'content' => $page->post_content,
											'p-background-color' => [
												'color1' => '#e8e8e8',
												'transparency1' => '1',
												'rgba1' => 'rgba(232,232,232,1)',
											],
										],
										'config' => [
											'margin_top' => '0',
											'margin_bottom' => '20',
										],
									],
								],
							],
						],
					],

				];
				$this->create_page_setting($page->ID, 'page/1/', visualEditor::code($oldcontent));
			}
			// 404

			$page_404 = [
				'0' => [
					'class' => '',
					'style' => [
						'background_color' => [
							'color1' => '',
							'transparency1' => '1.00',
							'rgba1' => '',
							'color2' => '',
							'transparency2' => '1',
							'rgba2' => '',
						],
						'background_setting' => 'image',
						'background_image' => [
							'position' => '',
							'image' => '',
							'imageid' => '',
							'pattern' => '',
							'tablet' => [
								'position' => '',
								'image' => '',
								'imageid' => '',
							],
							'mobile' => [
								'position' => '',
								'image' => '',
								'imageid' => '',
							],
							'cover' => '1',
							'overlay_color' => [
								'color' => '#000000',
								'transparency' => '0.2',
								'rgba' => 'rgba(0, 0, 0, 0.2)',
							],
							'efect' => '',
							'repeat' => 'no-repeat',
						],
						'slider_overlay_color' => [
							'color' => '',
							'transparency' => '0.7',
							'rgba' => '',
						],
						'video_type' => 'iframe',
						'video_url' => '',
						'background_video_mp4' => '',
						'background_video_webm' => '',
						'background_video_ogg' => '',
						'video_image' => [
							'position' => '50% 50%',
							'image' => '',
							'imageid' => '',
							'cover' => '1',
						],
						'video_overlay_color' => [
							'color' => '',
							'transparency' => '0.7',
							'rgba' => '',
						],
						'row_height' => 'full',
						'min-height' => '100',
						'arrow_color' => '#fff',
						'content_align' => 'center',
						'text' => 'auto',
						'font' => [
							'font-family' => '',
							'weight' => '',
							'font-size' => '',
							'color' => '',
						],
						'link_color' => '',
						'type' => 'basic',
						'row_padding' => 'big',
						'padding_top' => '50',
						'tablet' => [
							'padding_top' => '',
							'padding_bottom' => '',
							'padding_left' => [
								'size' => '',
								'unit' => 'px',
							],
							'padding_right' => [
								'size' => '',
								'unit' => 'px',
							],
						],
						'mobile' => [
							'padding_top' => '',
							'padding_bottom' => '',
							'padding_left' => [
								'size' => '',
								'unit' => 'px',
							],
							'padding_right' => [
								'size' => '',
								'unit' => 'px',
							],
						],
						'padding_bottom' => '50',
						'padding_left' => [
							'size' => '',
							'unit' => 'px',
						],
						'padding_right' => [
							'size' => '',
							'unit' => 'px',
						],
						'border-top' => [
							'size' => '',
							'style' => 'solid',
							'color' => '',
						],
						'border-bottom' => [
							'size' => '',
							'style' => 'solid',
							'color' => '',
						],
						'shape_top' => [
							'shape' => 'tilt',
							'code' => '',
							'size' => '100',
							'tablet' => [
								'size' => '',
							],
							'mobile' => [
								'size' => '',
							],
							'color' => '',
						],
						'shape_bottom' => [
							'shape' => 'tilt',
							'code' => '',
							'size' => '100',
							'tablet' => [
								'size' => '',
							],
							'mobile' => [
								'size' => '',
							],
							'color' => '',
						],
						'margin_top' => '',
						'margin_bottom' => '',
						'css_class' => '',
						'row_anchor' => '',
						'delay' => '',
					],
					'content' => [
						'0' => [
							'type' => 'col-one',
							'class' => '',
							'content' => [
								'0' => [
									'style' => [
										'background_color' => [
											'color1' => '#ffffff',
											'transparency1' => '1.00',
											'rgba1' => 'rgba(255, 255, 255, 1)',
										],
										'background_image' => [
											'position' => '',
											'image' => '',
											'imageid' => '',
											'pattern' => '0',
											'cover' => '1',
											'overlay_color' => [
												'color' => '#158ebf',
												'transparency' => '',
												'rgba' => '',
											],
										],
										'border' => [
											'size' => '0',
											'style' => 'solid',
											'color' => '#eeeeee',
										],
										'corner' => '2',
										'padding' => [
											'size' => '55',
											'unit' => 'px',
										],
										'shadow' => '1',
										'text' => 'auto',
										'title' => '',
										'title_bg' => [
											'color1' => '',
											'transparency1' => '1',
											'rgba1' => '',
										],
										'title_border' => [
											'size' => '1',
											'style' => 'solid',
											'color' => '#000000',
											'transparency' => '0.2',
											'rgba' => 'rgba(0,0,0,0.2)',
										],
										'title-font' => [
											'use-font' => 'title',
											'font-size' => '20',
											'color' => '',
											'align' => 'center',
										],
										'mw30' => '1',
									],
									'type' => 'box',
									'config' => [
										'margin_top' => '',
										'tablet' => [
											'margin_top' => '',
											'margin_bottom' => '',
											'max_width' => '',
										],
										'mobile' => [
											'margin_top' => '',
											'margin_bottom' => '',
											'max_width' => '',
										],
										'margin_bottom' => '',
										'max_width' => '720',
										'element_align' => 'center',
										'animate' => '',
										'id' => '',
										'class' => '',
										'delay' => '',
									],
									'content' => [
										'0' => [
											'0' => [
												'style' => [
													'font' => [
														'font-size' => '50',
														'tablet' => [
															'font-size' => '',
														],
														'mobile' => [
															'font-size' => '',
														],
														'color' => '',
														'font-family' => '',
														'weight' => '',
														'line-height' => '1.2',
														'letter-spacing' => '0',
														'text-shadow' => 'none',
													],
													'style' => '1',
													'border' => [
														'size' => '1',
														'style' => 'solid',
														'color' => '#d5d5d5',
													],
													'background-color' => [
														'color1' => '#e8e8e8',
														'transparency1' => '1',
														'rgba1' => 'rgba(232,232,232,1)',
													],
													'decoration-color' => '#158ebf',
													'align' => 'center',
													'content' => '<p style="text-align: center;">' . __('Stránka s touto adresou neexistuje.', 'cms_ve') . '</p>',
													'mw30' => '1',
												],
												'type' => 'title',
												'config' => [
													'margin_top' => '',
													'tablet' => [
														'margin_top' => '',
														'margin_bottom' => '',
														'max_width' => '',
													],
													'mobile' => [
														'margin_top' => '',
														'margin_bottom' => '',
														'max_width' => '',
													],
													'margin_bottom' => '10',
													'max_width' => '560',
													'element_align' => 'center',
													'animate' => '',
													'id' => '',
													'class' => '',
													'delay' => '',
												],
											],
											'1' => [
												'style' => [
													'font' => [
														'font-family' => '',
														'weight' => '',
														'font-size' => '17',
														'color' => '',
														'line-height' => '',
													],
													'style' => '1',
													'p-background-color' => [
														'color1' => '#e8e8e8',
														'transparency1' => '1',
														'rgba1' => 'rgba(232,232,232,1)',
													],
													'content' => '<p style="text-align: center;">' . __('Přejít na domovskou stránku', 'cms_ve') . '</p>',
													'li' => '',
													'mw30' => '1',
												],
												'type' => 'text',
												'config' => [
													'margin_top' => '',
													'tablet' => [
														'margin_top' => '',
														'margin_bottom' => '',
														'max_width' => '',
													],
													'mobile' => [
														'margin_top' => '',
														'margin_bottom' => '',
														'max_width' => '',
													],
													'margin_bottom' => '49',
													'max_width' => '',
													'element_align' => 'center',
													'animate' => '',
													'id' => '',
													'class' => '',
													'delay' => '',
												],
											],
											'2' => [
												'style' => [
													'content' => __('Přejít na domovskou stránku', 'cms_ve'),
													'button_style' => [
														'custom_setting' => [
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
														'button_size' => 'medium',
														'custom_size' => '18',
													],
													'show' => 'url',
													'link' => [
														'page' => '',
														'link' => '/',
														'use_url' => '1',
													],
													'popup' => '',
													'align' => 'center',
													'text2' => 'Button text',
													'button_style2' => [
														'custom_setting' => [
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
														'button_size' => 'medium',
														'custom_size' => '18',
													],
													'show2' => 'url',
													'link2' => [
														'page' => '',
														'link' => '',
													],
													'popup2' => '',
													'mw30' => '1',
												],
												'type' => 'button',
												'config' => [
													'margin_top' => '',
													'tablet' => [
														'margin_top' => '',
														'margin_bottom' => '',
														'max_width' => '',
													],
													'mobile' => [
														'margin_top' => '',
														'margin_bottom' => '',
														'max_width' => '',
													],
													'margin_bottom' => '',
													'max_width' => '',
													'element_align' => 'center',
													'animate' => '',
													'id' => '',
													'class' => '',
													'delay' => '',
												],
											],
										],
									],
								],
							],
						],
					],
				],
			];
			MWDB()->setLayer(0, '404', visualEditor::code($page_404));
		}
	}

	// back compatibility functions
	public static function save_layer($post_id, $type, $layer, $rewrite = false)
	{
		MWDB()->setLayer($post_id, $type, $layer, $rewrite);
	}


}
