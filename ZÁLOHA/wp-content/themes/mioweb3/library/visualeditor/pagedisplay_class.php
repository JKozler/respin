<?php

use Fapi\FapiClient\FapiClientFactory;
use Mioweb\VisualEditor\Lib\Icon;
use Mioweb\Shop\Upsell;
use visualeditor\Compatibility;
use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\Link;
use Mioweb\VisualEditor\Lib\Button;
use Mioweb\VisualEditor\Lib\Image;

class MWPageDisplay
{

	public $page_content = '';

	public $header_content = '';

	public $footer_content = '';

	public $home_url;

	public $home_id;

	public $layer;

	public $template;

	public $post_id;

	public $save_post_id;

	public $page_type;

	public $template_config;

	public $template_setting;

	public $page_setting;

	public $global_page_setting;

	public $header_setting;

	public $used_header;

	public $footer_setting;

	public $popups;

	public $weditor;

	public $element_info = [];

	public $element_css = [];

	public $subelement_css = [];

	public $body_styles = [];

	public $editable_type = ['page'];

	public $is_mobile;

	public $edit_mode = false;

	public $google_fonts = [];

	public $file_fonts = [];

	public $window_editor;

	public $css;

	public $header_css;

	public $setting_container = [];

	public $scriptsToEnqueue = [];

	public $stylesToEnqueue = [];

	private $web_options;

	private ?mwCssContainer $row_styles = null;

	private $lazyLoadingDisabled = false;

	function __construct($ispage, $edit_mode)
	{
		if ($edit_mode && !isset($_GET['revision'])) {
			$this->edit_mode = true;
		}

		$this->home_url = home_url();
		$this->home_id = get_option('page_on_front');

		$this->web_options = get_option('web_option_basic');

		$this->popups = new cmsPopups();
		$this->weditor = new cmsWEditor();
		$this->css = new mwCssManager();

		$this->is_mobile = wp_is_mobile();

		$this->body_styles = $this->css->createCssContainer();
		$this->header_css = $this->css->createCssContainer();

		// if is generated page
		//
		// if header height is not defined
		add_action('wp_ajax_save_header_height', [$this, 'save_header_height']);
		if ($ispage) {
			$this->register_hooks();
		}
	}

	function isLazyLoadingDisabled(): bool
	{
		return $this->lazyLoadingDisabled;
	}

	function disableLazyLoading(): void
	{
		$this->lazyLoadingDisabled = true;
	}

	function enableLazyLoading(): void
	{
		$this->lazyLoadingDisabled = false;
	}

	function init($post_id, $save_post_id, $page_type)
	{
		$this->page_type = $page_type;
		$this->post_id = $post_id;
		$this->save_post_id = $save_post_id;
		$this->layer = $this->get_layer($save_post_id, $this->page_type, true);

		do_action('mw_page_init');

		$this->create_setting();

		//$this->css->loadCachedStyles($this->post_id,$this->edit_mode);

		// hide header and footer in window editor
		if (isset($_GET['window_editor'])) {
			$this->header_setting['show'] = 'noheader';
			$this->footer_setting['show'] = 'nofooter';
		}

		//$this->generateContent();
	}

	// used in wp_head hook
	function generateContent()
	{
		// get header content (and css)
		$this->header_content = $this->printHeader(false);
		// get footer content (and css)
		$this->footer_content = $this->printFooter(false);

		// get page content (and css)
		if ((is_home() || $this->is_editable()) && (!post_password_required($this->post_id) || $this->edit_mode) && !is_feed()) {
			$main = true;
			if (is_singular('mwproduct')) {
				$main = false;
			}
			$this->page_content = $this->write_content($this->layer, $this->edit_mode, '', $main, false);
		}

		// get popups content (and css)
		$this->popups->generate_popups();

		//button styles
		foreach (mwButtonStyles()->getStyles() as $bkey => $button) {
			if ($button) {
				$this->css->addGlobalStyles(Button::getButtonStyles($button, '.ve_content_button_style_' . $bkey));
			}
		}

		do_action('mw_global_styles');

		// load custom codes and scripts
		$this->loadCodes();
	}

	function resetPageId($post_id)
	{
		$this->post_id = $post_id;
		$this->layer = $this->get_layer($this->post_id, $this->page_type, true);
	}

	function load_scripts()
	{
		//enqueue scripts
		wp_enqueue_script('front_menu');
		wp_enqueue_script('ve-front-script');

		wp_enqueue_script('mw-svg-ie-use');

		//enqueue styles
		wp_enqueue_style('ve-content-style');

		// generated styles
		// wp_enqueue_style('ve-generated-style', admin_url('admin-ajax.php').'?action=mw_create_dynamic_css&post_id='.$this->post_id, array(), $this->script_version);

		// get template css
		if (isset($this->template_config['custom_styles']) && $this->template_config['custom_styles']) {
			wp_enqueue_style('ve-template-style', $this->get_template_file('style.css', true));
		}

		if ($this->edit_mode) {
			wp_enqueue_style('mw_pgb_page');
			wp_enqueue_script('mw_pgb_page_script');

			wp_enqueue_script('tiny_mce_js');
			//wp_enqueue_script( 'mw_tinymce_plugin');

			wp_enqueue_script('jquery-ui-droppable');

			wp_enqueue_script('ve_lightbox_script');
			wp_enqueue_style('ve_lightbox_style');
			wp_enqueue_script('ve_waypoints_script');
			wp_enqueue_style('ve_animate_style');
			//wp_enqueue_script('ve_admin_image_gallery');
			wp_enqueue_script('ve_countdown_script');
			wp_enqueue_style('ve_countdown_style');
			wp_enqueue_style('ve_miocarousel_style');
			wp_enqueue_script('ve_miocarousel_script');
			wp_enqueue_script('ve_google_maps');
			wp_enqueue_script('ve_social_sprinters');
		}

		foreach ($this->scriptsToEnqueue as $script) {
			wp_enqueue_script($script);
		}
		foreach ($this->stylesToEnqueue as $style) {
			wp_enqueue_style($style);
		}
	}

	function add_enqueue_script($script)
	{
		// for enqueue after load_scripts()
		wp_enqueue_script($script);
		// for enqueue before load_scripts()
		$this->scriptsToEnqueue[$script] = $script;
	}
	function add_enqueue_style($style)
	{
		// for enqueue after load_scripts()
		wp_enqueue_style($style);
		// for enqueue before load_scripts()
		$this->stylesToEnqueue[$style] = $style;
	}

	function register_hooks()
	{
		add_action('wp_enqueue_scripts', [$this, 'load_scripts'], 100);
		add_action('body_class', [$this, 'add_bodyclass']);
		add_action('wp_footer', [$this, 'add_page_footer'], 10);
		add_action('wp_head', [$this, 'add_page_header_scripts']);
		add_filter('the_content', [$this, 'create_content'], 100);

		if (!$this->edit_mode) {
			remove_filter('the_content', 'convert_smilies', 20);
			add_filter('the_content', 'convert_smilies', 120);
		}

		add_filter('page_template', [$this, 'hook_locate_page_template'], 200, 2);
	}

	function save_header_height()
	{
		echo 'saved height: ' . $_POST['header_height'] . '/' . $_POST['used_header'] . '/' . $_POST['post_id'];

		if ($_POST['used_header'] == 'page' && $_POST['post_id']) {
			$meta = get_post_meta($_POST['post_id'], 've_header', true);
			if ($meta) {
				$meta['header_height'] = $_POST['header_height'];
				update_post_meta($_POST['post_id'], 've_header', $meta);
			}
		} elseif ($_POST['used_header'] == 'member_header') {
			if ($_POST['post_id']) {
				mwMemberModule()->builderMemberInit($_POST['post_id']);
				$option = get_option($_POST['used_header']);
				if (mwMemberModule()->memberSection() && isset($option['members'][mwMemberModule()->memberSection()->getId()])) {
					$option['members'][mwMemberModule()->memberSection()->getId()]['header_height'] = $_POST['header_height'];
					update_option($_POST['used_header'], $option);
				}
			}
		} else {
			$option = get_option($_POST['used_header']);
			if ($option) {
				$option['header_height'] = $_POST['header_height'];
				update_option($_POST['used_header'], $option);
			}
		}
		die();
	}

	function hook_locate_page_template($located, $templateName)
	{
		if (isset($_GET['window_editor']) && file_exists(TEMPLATEPATH . '/window_editor.php')) {
			$located = TEMPLATEPATH . '/window_editor.php';
		}

		return $located;
	}

	function add_bodyclass($classes)
	{
		//if (isset($this->template_config['body_class'])) $classes[] = $this->template_config['body_class'];
		if (isset($this->page_setting['li'])) {
			$classes[] = 've_list_style' . $this->page_setting['li'];
		}
		if (isset($this->page_setting['narrow_content'])) {
			$classes[] = 'fixed_width_page';
		}
		$classes[] = $this->is_mobile ? 'mobile_view' : 'desktop_view';

		if ($this->edit_mode) {
			$classes[] = 'mw_edit_mode';
		}

		if ($this->isTransparentHeader()) {
			$classes[] = 'page_with_transparent_header';
		}

		return $classes;
	}

	function isTransparentHeader()
	{
		return !$this->header_setting['background_color']['color1'] || $this->header_setting['background_color']['transparency1'] < 1 || (isset($this->header_setting['background_color']['gradient']) && $this->header_setting['background_color']['transparency2'] < 1);
	}

	/* load page / web setting ********
	*******************************************************************************  */

	function create_setting()
	{
		if ($this->post_id) {
			$this->template = get_post_meta($this->post_id, 've_page_template', true);

			if (!$this->template || $this->template == 'landing/ebook3/') {
				$this->template = $this->page_type == 'cms_popup' ? ['type' => 'cms_popup', 'directory' => 'popups/1/'] : ['type' => 'page', 'directory' => 'page/1/'];
				add_post_meta($this->post_id, 've_page_template', $this->template);
			}

			$this->template_config = get_post_meta($this->post_id, 've_page_config', true);
			$this->template_setting = get_post_meta($this->post_id, 've_page_setting', true);
		} else {
			$this->template_config = [];
			$this->template_setting = [];
		}

		// Visual setting
		$this->page_setting = get_option('ve_appearance');
		// Header setting
		$this->header_setting = get_option('ve_header');
		$this->used_header = 've_header';

		// Footer setting
		$this->footer_setting = get_option('ve_footer');
		// Popups setting
		$this->popups->popups_setting = get_option('ve_popups');

		do_action('ve_global_setting', $this->post_id);

		$this->global_page_setting = $this->page_setting;

		//Page setting
		$this->page_setting = mwBackCompatibility::page_set($this->page_setting);

		if (!is_home()) { // dont use local page setting for blog home page
			$this->mergeWithPageSetting();

			//Page header setting
			$p_header = get_post_meta($this->post_id, 've_header', true);

			if (isset($p_header['show']) && $p_header['show'] == 'page') {
				$this->header_setting = $p_header;
				$this->used_header = 'page';
			}
			$this->header_setting['show'] = $p_header['show'] ?? 'global';

			// Page footer setting
			$p_footer = get_post_meta($this->post_id, 've_footer', true);
			if (isset($p_footer['show']) && $p_footer['show'] == 'page') {
				$this->footer_setting = $p_footer;
			}
			$this->footer_setting['show'] = $p_footer['show'] ?? 'global';

			// Page popups setting
			$p_popups = get_post_meta($this->post_id, 've_popup', true);
			if (isset($p_popups['show']) && $p_popups['show'] == 'page') {
				$this->popups->popups_setting = $p_popups;
			}
		} else {
			$this->footer_setting['show'] = 'global';
			$this->header_setting['show'] = 'global';
		}

		$this->footer_setting = mwBackCompatibility::footer_set($this->footer_setting);
		$this->header_setting = mwBackCompatibility::header_set($this->header_setting);
	}

	function mergeWithPageSetting($p_appearance = null)
	{
		if (empty($p_appearance)) {
			$p_appearance = !isset($_GET['window_editor']) ? get_post_meta($this->post_id, 've_appearance', true) : [
					'use_page_background' => '1',
					'background_color' => '#ffffff',
					'background_image' => [],
			];
		}

		if ($p_appearance) {

			$p_appearance = mwBackCompatibility::page_set($p_appearance, 'page');

			if (!isset($p_appearance['use_page_background'])) {
				$p_appearance['background_image'] = [];
				$p_appearance['background_color'] = [];
				$p_appearance['background_setting'] = 'image';
			} else {
				$this->page_setting['background_image'] = $p_appearance['background_image'] ?? '';
				$this->page_setting['background_color'] = $p_appearance['background_color'] ?? '';
			}

			$this->page_setting = $this->merge_setting($p_appearance, $this->page_setting);
		}
	}

	/* merge setting */
	function merge_setting($set1, $set2, $if = true)
	{
		if ($if && $set1) {
			foreach ($set1 as $key => $value) {
				if (is_array($value)) {
					if ($key != 'background_image' || (isset($value['image']) && $value['image'])) {
						foreach ($value as $val_key => $val) {
							if ($val != '') {
								$set2[$key][$val_key] = $val;
							}
						}
					}
				} elseif ($value != '') {
					$set2[$key] = $value;
				}
			}
		}

		return $set2;
	}

	public function getPageWidth(): int
	{
		if (!isset($this->page_setting['page_width_preset'])) {
			return 970;
		}
		if ($this->page_setting['page_width_preset'] === 'custom' && isset($this->page_setting['page_width']['size']) && $this->page_setting['page_width']['size']) {
			return intval($this->page_setting['page_width']['size']);
		}

		if ($this->page_setting['page_width_preset'] === '800px') {
			return 800;
		}

		if ($this->page_setting['page_width_preset'] === '970px') {
			return 970;
		}

		if ($this->page_setting['page_width_preset'] === '1024px') {
			return 1024;
		}

		if ($this->page_setting['page_width_preset'] === '1200px') {
			return 1200;
		}

		if ($this->page_setting['page_width_preset'] === '90%') {
			return 90;
		}

		return 970;
	}

	public function getPageWidthUnit(): string
	{
		if (!isset($this->page_setting['page_width_preset'])) {
			return 'px';
		}
		if ($this->page_setting['page_width_preset'] === 'custom' && isset($this->page_setting['page_width']['unit']) && $this->page_setting['page_width']['unit']) {
			return $this->page_setting['page_width']['unit'];
		}

		if ($this->page_setting['page_width_preset'] === '90%') {
			return '%';
		}

		return 'px';
	}

	/* templates */
	function get_template_file($file, $url = false, $directory = false)
	{
		$temp = explode('/', $this->template['directory']);
		if (!$directory && isset(MW()->p_templates[$temp[0]])) {
			$directory = MW()->p_templates[$temp[0]]['path'] . $temp[1] . '/';
		} else {
			$temp[0] = 'page';
			$temp[1] = '1';
			$directory = MW()->p_templates[$temp[0]]['path'] . $temp[1] . '/';
		}

		return $url ? MW()->get_template_url($temp[0]) . $directory . $file : MW()->get_template_dir($temp[0]) . $directory . $file;
	}


	/* write page ********
	*******************************************************************************  */

	function get_layer($post_id, $page_type = 'page', $get_rev = false)
	{
		if (isset($_GET['revision']) && current_user_can('edit_pages') && $get_rev) {
			if ($_GET['revision_type'] == 'mw_hb_revisions' || $_GET['revision_type'] == 'mw_sc_revisions' || $_GET['revision_type'] == 'mw_sp_revisions') {
				$revision = get_post($_GET['revision']);
			} elseif (wp_get_post_revision($_GET['revision'])) {
				$revision = wp_get_post_revision($_GET['revision']);
			}

			return mwBackCompatibility::layer_set(visualEditor::decode($revision->post_content));
		} elseif (isset($_SESSION['ve_layer_autosave'][$post_id])) {
			// save setting with not saved layer
			$layer = $_SESSION['ve_layer_autosave'][$post_id];
			unset($_SESSION['ve_layer_autosave'][$post_id]);

			return visualEditor::decode($layer);
		} else {
			global $wpdb;
			$result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='" . $page_type . "' AND vpl_post_id=" . $post_id);
			// ****************** temporary
			if (!$wpdb->num_rows && $page_type == 'blog' && $post_id == 0) {
				$wpdb->update($wpdb->prefix . 've_posts_layer', ['vpl_post_id' => 0], ['vpl_type' => 'blog']);
				$result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='" . $page_type . "' AND vpl_post_id=" . $post_id);
			}
			// ****************** end temporary

			if ($wpdb->num_rows) {
				$return = mwBackCompatibility::layer_set(visualEditor::decode($result->vpl_layer));

				return $return;
			}

			return '';
		}
	}

	function is_editable()
	{
		return is_404() || in_array($this->page_type, $this->editable_type);
	}

	function create_content($content = '')
	{
		if ($this->is_editable() && (!post_password_required() || $this->edit_mode) && !is_feed()) {
			$content = $this->printContent();
		}

		return apply_filters('ve_content', $content);
	}
	function printContent()
	{
		return do_shortcode($this->page_content);
	}

	function write_content($layer, $edit_mode, $pre = '', $main = true, $doShortcodes = true)
	{
		$content = '';

		if ($layer) {
			foreach ($layer as $row_key => $row) {
				$content .= $this->generate_row($row, $row_key, 0, $edit_mode, $pre);
			}
			$row_key++;
		} else {
			$row_key = 0;
		}

		if ($edit_mode) {
			$content = '<div class="mw_page_builder_content">'
			. '<div class="mw_page_builder_droparea mw_page_builder_droparea_row" data-group="row">'
			. '<div class="mw_page_builder_emptyrow_info"><span>+</span>' . __('Začněte přetažením bloku z levého panelu na tuto plochu', 'cms_ve') . '</div>'
			. '</div>'
			. $content
			. '</div>';
		}

		$vs_class = '';
		if (isset($this->template_config['content_class']) && $pre == '') {
			$vs_class = $this->template_config['content_class'];
		}
		if ($main) {
			$vs_class .= ' visual_content_main';
		}
		if ($doShortcodes) {
			$content = do_shortcode($content);
		}

		return '<div class="visual_content ' . $vs_class . ' ' . ($row_key ? '' : 'empty_content') . '">' . $content . '</div>';
	}

	function generate_row($row, $row_key = '', $post_id = 0, $edit_mode = true, $pre = '', $added = false)
	{
		//if(isset($row['type']) && $row['type']=='slider') $row['style']=mwBackCompatibility::slider_row_set($row['style']);
		//else if(isset($row['type']) && $row['type']=='slide') $row['style']=mwBackCompatibility::slide_row_set($row['style']);
		//else $row['style']=mwBackCompatibility::row_set($row['style']);

		$row_key = $row_key === '' ? md5(microtime()) : $row_key;
		$row_id = $pre . 'row_' . $row_key;

		if (isset($row['style']['type']) && $row['style']['type'] == 'fixed') {
			$rowclass = 'row_fixed';
		} elseif (isset($row['style']['type']) && $row['style']['type'] == 'full') {
			$rowclass = 'row_full';
		} else {
			$rowclass = 'row_basic';
		}

		if ($rowclass != 'row_basic') {
			if ((isset($row['style']['padding_left']) && $row['style']['padding_left']['size'] == '0' && isset($row['style']['padding_right']) && $row['style']['padding_right']['size'] == '0') || $row['style']['row_padding'] == 'none') {
				$rowclass .= ' row_full_0';
			}
		}

		$rowclass .= $pre ? ' row_' . $pre : ' row_content';

		if (isset($row['type'])) {
			$rowclass .= ' row_' . $row['type'];

			if ($row['type'] == 'slide') {
				$row['style']['row_height'] = '';
				//$row['style']['background_image']['cover']='1';
			}
		}

		if (isset($row['style']['css_class']) && $row['style']['css_class']) {
			$rowclass .= ' ' . $row['style']['css_class'];
		}

		if (isset($row['style']['content_align'])) {
			$rowclass .= ' ve_valign_' . $row['style']['content_align'];
		}

		if (isset($row['style']['row_height'])) {
			$rowclass .= ' row_height_' . $row['style']['row_height'];
		}

		if (isset($row['style']['row_padding'])) {
			$rowclass .= ' row_padding_' . $row['style']['row_padding'];
		} else {
			$rowclass .= ' row_padding_custom';
		}

		$rowclass .= ' row_text_' . ($row['style']['text'] ?? 'auto');

		if ((isset($row['style']['background_color']) && Colors::isLightColor($row['style']['background_color']['color1'])) && !(isset($row['style']['background_image']) && isset($row['style']['background_image']['image']) && $row['style']['background_image']['image'])) {
			$rowclass .= ' light_color';
		} else {
			$rowclass .= ' dark_color';
		}

		if (isset($row['style']['scroll_arrow'])) {
			$rowclass .= ' row_with_arrow';
		}

		if (isset($row['style']['mobile_visibility'])) {
			$rowclass .= ' hide_on_mobile';
		}
		if (isset($row['style']['tablet_visibility'])) {
			$rowclass .= ' hide_on_tablet';
		}
		if (isset($row['style']['desktop_visibility'])) {
			$rowclass .= ' hide_on_desktop';
		}

		// display delay
		$datadelay = '';
		if (isset($row['style']['delay']) && $row['style']['delay'] && !$edit_mode) {
			//$content .= '<div class="row_container_delay" data-delay="'.$row['style']['delay'].'">';
			$rowclass .= ' row_container_delay';
			$datadelay = 'data-delay="' . $row['style']['delay'] . '"';
		}

		if ($edit_mode) {
			$rowclass .= ' elements_container';
			if (isset($row['content'])) {
				$rowclass .= $this->isRowEmpty($row['content']) ? ' empty_container' : '';
			}
		}

		//row styles
		$this->row_styles = $this->css->createCssContainer();
		$this->generate_row_styles($row['style'], $row_id);

		$content = '';
		$content .= '<div ' . ($row_id ? 'id="' . $row_id . '"' : '') . ' class="row ' . $rowclass . '" ' . $datadelay . '>';

		$content .= $this->generate_background($row['style'], $row_id, $added);

		global $mwContainer;
		// top shape
		if ((isset($row['style']['shape_top']) && isset($row['style']['shape_top']['show'])) || $edit_mode) {
			$shape = $row['style']['shape_top']['shape'] ?? 'tilt';

			$t_shape_class = 'mw_row_shape_divider mw_row_shape_divider_top mw_row_shape_' . $shape;
			if (!isset($row['style']['shape_top']['show'])) {
				$t_shape_class .= ' ve_nodisp';
			}

			$content .= '<div class="' . $t_shape_class . ' ' . (isset($row['style']['shape_top']['flip']) ? 'mw_row_shape_divider_flip' : '') . '" data-shape="' . $shape . '">';
			if (isset($row['style']['shape_top']['show'])) {
				$content .= $mwContainer->list['shape_dividers'][$shape];
			}
			$content .= '</div>';
		}
		// bottom shape
		if ((isset($row['style']['shape_bottom']) && isset($row['style']['shape_bottom']['show'])) || $edit_mode) {
			$shape = $row['style']['shape_bottom']['shape'] ?? 'tilt';

			$b_shape_class = 'mw_row_shape_divider mw_row_shape_divider_bottom mw_row_shape_' . $shape;
			if (!isset($row['style']['shape_bottom']['show'])) {
				$b_shape_class .= ' ve_nodisp';
			}

			$content .= '<div class="' . $b_shape_class . ' ' . (isset($row['style']['shape_bottom']['flip']) ? 'mw_row_shape_divider_flip' : '') . '" data-shape="' . $shape . '">';
			if (isset($row['style']['shape_bottom']['show'])) {
				$content .= $mwContainer->list['shape_dividers'][$shape];
			}
			$content .= '</div>';
		}

		if (!isset($row['type'])) {
			$row['type'] = 'basic';
		}

		if ($edit_mode) {
			$row_setting = $row;
			if (isset($row_setting['content'])) {
				foreach ($row_setting['content'] as $key_col => $col) {
					unset($row_setting['content'][$key_col]['content']);
				}
			}
			$this->add_to_setting_container($row_id, $row_setting);
			$content .= $this->generate_row_edit_bar($row['type']);
		}

		$content .= $this->css->printCss($this->row_styles, $row_id . '_style', $this->edit_mode);

		if (isset($row['type']) && $row['type'] == 'slider') {
			$content .= $this->generate_slider_row($row, $row_key, $post_id, $edit_mode, $pre, $added);
		} else {
			$content .= $this->generate_basic_row($row, $row_id, $post_id, $rowclass, $row_key, $edit_mode, $pre, $added);
		}

		// anchor
		if (isset($row['style']['row_anchor']) && $row['style']['row_anchor']) {
			$content .= '<a id="' . $row['style']['row_anchor'] . '" class="mw_row_anchor"></a>';
		}

		if ($edit_mode) {
			$content .= '<div class="mw_page_builder_droparea mw_page_builder_droparea_row" data-group="row"></div>';
		}

		$content .= '</div>';

		return $content;
	}

	public static function isFullWidthRow(array $row_set): bool
	{
		return isset($row_set['type']) && $row_set['type'] === 'full';
	}

	function add_to_setting_container($id, $setting)
	{
		//$setting=$this->stripslashes_deep($setting);
		//print_r($setting);
		$this->setting_container[$id] = $setting;
	}

	function stripslashes_deep($value)
	{
		$value = is_array($value) ?
		array_map('stripslashes_deep', $value) :
		stripslashes($value);

		return $value;
	}


	function generate_basic_row($row, $row_id, $post_id, $rowclass, $row_key, $edit_mode, $pre, $added)
	{
		$content = '';

		$content .= '<div class="row_fix_width">';

		$col_num = 0;
		foreach ($row['content'] as $col_key => $col) {
			if (!isset($col['type']) || !$col['type']) {
				$col['type'] = 'col-one';
			}

			$class = 'col ' . $col['type'];
			$class .= $row_key ? ' col_' . $row_key . '_' . $col_key : '';
			if (empty($col['content'])) {
				$class .= ' empty_col';
			}
			if ($col_num == 0) {
				$class .= ' col-first';
			}
			if ($edit_mode) {
				$class .= ' sortable-col';
			}
			if (($col_key == count($row['content']) - 1) || isset($col['break'])) {
				$class .= ' col-last';
			}
			$content .= '<div class="' . $class . '">';
			if ($edit_mode) {
				$content .= '<div class="mw_page_builder_droparea mw_page_builder_droparea_element" data-group="element">'
				. '<div class="mw_page_builder_emptyelement_info admin_feature">+</div>'
				. '</div>';
			}

			foreach ($col['content'] ?? [] as $content_key => $code) {
				$content .= $this->generate_element($code, 'element_' . $row_key . '_' . $col_key . '_' . $content_key, $post_id, $edit_mode, $pre, $added, false, $row['style'], $col['type']);
			}

			$content .= '</div>';

			$col_num++;

			if (isset($col['break'])) {
				$col_num = 0;
				$content .= '<div class="ve_row_break"></div>';
			}
		}
		$content .= '</div>';
		if ($rowclass == 'row_basic') {
			$content .= '</div>';
		}

		if (isset($row['style']['scroll_arrow']) || $edit_mode) {
			$content .= $this->generate_next_to_scroll_link();
		}

		return $content;
	}

	function generate_slider_row($row, $row_key, $post_id, $edit_mode, $pre, $added)
	{
		$content = '';
		$css_id = '#row_' . $row_key;
		$row_id = 'row_' . $row_key;

		$styles = [];

		$this->add_enqueue_script('ve_miocarousel_script');
		$this->add_enqueue_style('ve_miocarousel_style');

		$carousel_set = '';
		if (!isset($row['style']['miocarousel_setting']['autoplay'])) {
			$carousel_set .= ' data-autoplay="0"';
		}
		if ($row['style']['miocarousel_setting']['delay']) {
			$carousel_set .= ' data-duration="' . $row['style']['miocarousel_setting']['delay'] . '"';
		}
		if ($row['style']['miocarousel_setting']['speed']) {
			$carousel_set .= ' data-speed="' . $row['style']['miocarousel_setting']['speed'] . '"';
		}
		//if(!$row['style']['slider_height']) $carousel_set.=' data-height="full"';
		if ($row['style']['miocarousel_setting']['animation'] && $row['style']['miocarousel_setting']['animation'] != 'fade') {
			$carousel_set .= ' data-animation="' . $row['style']['miocarousel_setting']['animation'] . '"';
		}

		$carousel_class = 'miocarousel_' . $row['style']['miocarousel_setting']['color_scheme'];
		if (isset($row['style']['miocarousel_setting']['hide_navigation'])) {
			$carousel_class .= ' miocarousel_hide_nav';
		}

		$content .= '<div class="miocarousel miocarousel_style_3 ' . $carousel_class . '" ' . $carousel_set . '>';
		$content .= '<div class="miocarousel-inner">';

		//$slide_styles=$this->css->createCssContainer();

		$this->row_styles = $this->css->createCssContainer();

		$row_num = 1;

		if (isset($row['style']['slides'])) {
			foreach ($row['style']['slides'] as $slide) {
				$row_class = 'slide ve_valign_center row_height_' . $row['style']['row_height'];

				if ($row_num == 1) {
					$row_class .= ' active';
				}

				if (isset($slide['slider_content']) && $slide['slider_content'] && get_post($slide['slider_content'])) {
					$layer = $this->get_layer($slide['slider_content'], 'mw_slider');

					//$layer[0]['style']['background_image']['cover']=1;
					if (!isset($layer[0]['style'])) {
						return '';
					}
					$style = $layer[0]['style'];

					//$style=mwBackCompatibility::slide_row_set($style);
					if (!isset($style['row_padding'])) {
						$style['row_padding'] = 'small';
					}
					if (!isset($style['padding_top'])) {
						$style['padding_top'] = '50';
					}
					if (!isset($style['padding_bottom'])) {
						$style['padding_bottom'] = '50';
					}

					$row_class .= ' row_padding_' . $style['row_padding'];

					if (isset($style['text'])) {
						$row_class .= ' text_' . $style['text'];

						if ($style['text'] == 'auto' && ((isset($style['background_color']) && Colors::isLightColor($style['background_color']['color1'])) && !(isset($style['background_image']) && isset($style['background_image']['image']) && $style['background_image']['image']))) {
							$row_class .= ' light_color';
						} else {
							$row_class .= ' dark_color';
						}
					}

					$this->generate_row_styles($style, $row_id . ' #mw_slider_slide_' . $row_num);

					$content .= '<div id="mw_slider_slide_' . $row_num . '" class="' . $row_class . '">';
					$content .= $this->generate_background($layer[0]['style'], $row_id, $added);
					$content .= '<div class="row_fix_width">';

					// cols
					$col_num = count($layer[0]['content']);

					foreach ($layer[0]['content'] as $col_key => $col) {
						$class = 'col ' . $col['type'];
						$class .= $row_key ? ' col_' . $row_key . '_' . $col_key : '';
						if ($col_num == 0) {
							$class .= ' col-first';
						}
						if (($col_key == count($col['content']) - 1) || isset($col['break'])) {
							$class .= ' col-last';
						}
						$content .= '<div class="' . $class . '">';

						// elements
						$i = 0;
						foreach ($col['content'] as $content_key => $code) {
							$new_css_id = str_replace('#element_', '', $row_key) . '_' . $col_key . '_' . $i;
							if (!$this->is_mobile || !isset($code['config']['mobile_visibility'])) {
								$content .= $this->generate_element($code, 'element_' . str_replace('#', '', $new_css_id), $post_id, false, 'var' . $slide['slider_content'] . '_');
							}
							$i++;
						}

						$content .= '</div>';

						$col_num++;

						if (isset($col['break'])) {
							$col_num = 0;
							$content .= '<div class="ve_row_break"></div>';
						}
					}

					$content .= '<div class="cms_clear"></div></div>';
					$content .= '</div>';

					$row_num++;
				}
			}
		}

		$content .= $this->css->printCss($this->row_styles, $row_id . '_slider_style', $this->edit_mode);

		if ($row_num == 1) {
			$content .= '<div class="row_fix_width" style="text-align:center;color:#888;">' . __('Tento slider nemá žádný obsah. Pravděpodobně byl smazán.', 'cms_ve') . '</div>';
		}

		$content .= '</div>'; //slider end
		$content .= '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
		$content .= '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
		if ($added) {
			$content .= '<script>
          jQuery(function() {
            mwGetIframeContent().set_miocarousel("' . $css_id . ' .miocarousel");
          });
        </script>';
		}

		$content .= '</div><div class="cms_clear"></div>';

		return $content;
	}

	function generate_background($set, $id, $added = false)
	{
		$background_class = '';
		$background = '';

		$tag_id = $id == 'body' ? $id : '#' . $id;
		$pre = $id == 'body' ? $id : 'row';

		// slider on row background
		if (isset($set['background_setting']) && $set['background_setting'] == 'slider') {
			$background_class .= 'background_slider_container';

			if (isset($set['background_slides'])) {
				$background .= $this->generate_slider_background($set['background_slides'], /*$row['style']['background_delay']*/ 2500, /*$row['style']['background_speed']*/ 2000, 'miocarousel_slider_' . $id);
				if ($added) {
					$background .= "<script>

                    jQuery(document).ready(function(){
                      mwGetIframeContent().set_miocarousel('" . $tag_id . ' .' . $pre . "_background_container .miocarousel');
                      console.log('miocarou');
                    });
                    </script>";
				}
			}
			// video on row background
		} elseif (isset($set['background_setting']) && $set['background_setting'] == 'video') {
			$background_class .= 'background_video_container background_cover';
//			if (!isset($set['show_mobile'])) {
			$background_class .= ' background_video_hide_onmobile';
//			}

			if (!$this->is_mobile || isset($set['show_mobile']) || $this->edit_mode) {
				if ($set['video_type'] == 'iframe' && isset($set['video_url']) && $set['video_url']) {
					$video_setting = [
						'autoplay' => 1,
						'controls' => 0,
						'mute' => 1,
						'loop' => 1,
					];

					$background .= '<div class="background_video">';
					$background .= $this->getVideoCode($set['video_url'], $video_setting, $id);
					$background .= '</div>';

					if ($added) {
						$background .= "<script>jQuery(function() {
                          mwGetIframeContent().setBackgroundVideo('" . $tag_id . " .background_video iframe');
                        });</script>";
					}
				} elseif ($set['video_type'] == 'custom' && (isset($set['background_video_webm']) && ($set['background_video_webm']) || (isset($set['background_video_mp4']) && $set['background_video_mp4']) || (isset($set['background_video_ogg']) && $set['background_video_ogg']))) {
					//if (!$this->is_mobile || isset($set['video_setting']['show_mobile'])) {
					$background .= '<video autoplay="true" loop="true" muted="true">';
					if ($set['background_video_webm']) {
						$background .= '<source src="' . $set['background_video_webm'] . '" type="video/webm">';
					}
					if ($set['background_video_mp4']) {
						$background .= '<source src="' . $set['background_video_mp4'] . '" type="video/mp4">';
					}
					if ($set['background_video_ogg']) {
						$background .= '<source src="' . $set['background_video_ogg'] . '" type="video/ogg">';
					}
					$background .= '</video>';
					$background .= '<!--[if lt IE 9]><script>document.createElement("video");</script><![endif]-->';
					//}
				}
			}
		} else {
			if (isset($set['background_image']['image']) && $set['background_image']['image']) {
				if (isset($set['background_image']['cover']) && $set['background_image']['cover']) {
					$background_class .= ' background_cover';
				}
				if (isset($set['background_image']['efect']) && $set['background_image']['efect'] == 'fixed') {
					$background_class .= ' background_fixed';
				} elseif (isset($set['background_image']['efect']) && $set['background_image']['efect'] == 'parallax' && $pre == 'row') {
					$background_class .= ' background_parallax';
				}
			}
		}

		$content = '<div class="' . $pre . '_background_container background_container ' . $background_class . '">';
		$content .= $background;
		$content .= '<div class="background_overlay"></div>';
		$content .= '</div>';

		return apply_filters('mw_background_content', $content, $set, $id);
	}

	function generate_simple_background($pre, $set)
	{
		$background_class = '';

		if (isset($set['background_image']['image']) && $set['background_image']['image']) {
			if (isset($set['background_image']['cover']) && $set['background_image']['cover']) {
				$background_class .= ' background_cover';
			}
			if (isset($set['background_image']['efect']) && $set['background_image']['efect'] == 'fixed') {
				$background_class .= ' background_fixed';
			} elseif (isset($set['background_image']['efect']) && $set['background_image']['efect'] == 'parallax') {
				$background_class .= ' background_parallax';
			}
		}

		$content = '<div class="' . $pre . '_background_container background_container ' . $background_class . '">';
		$content .= '<div class="background_overlay"></div>';
		$content .= '</div>';

		return $content;
	}

	function generate_slider_background($slides, $duration, $speed, $id = 'miocarousel_page_background')
	{
		$this->add_enqueue_script('ve_miocarousel_script');
		$this->add_enqueue_style('ve_miocarousel_style');
		$content = '';
		if (is_array($slides)) {
			$content = '<div id="' . $id . '" class="miocarousel miocarousel_background" data-speed="' . $speed . '" data-duration="' . $duration . '" data-indicators="0"><div class="miocarousel-inner">';
			$i = 0;
			foreach ($slides as $slide) {
				$image = str_starts_with($slide, 'http') ? $slide : wp_get_attachment_image_src($slide, 'full')[0];
				$content .= '<div class="slide slide_' . $i . ' ' . ($i == 0 ? 'active' : '') . '" style="background-image: url(' . $image . ');"></div>';
				$i++;
			}
			$content .= '</div></div>';
		}

		return $content;
	}

	function generate_row_styles($row_style, $row_id)
	{
		$styles = [];

		if (isset($row_style['font'])) {
			$this->row_styles->addStyles(['font' => $row_style['font']], '#' . $row_id . '.row_text_custom');
			//$this->row_styles->addStyles(array('font' => $row_style['font']),'#' . $row_id . '.row_text_custom .element_container.title_element_container');
		}

		$this->row_styles->addVariableStyles(
			[
				'#' . $row_id . '.row_text_custom' => ['color'],
				//'#' . $row_id . '.row_text_custom .element_container.title_element_container'=>array('color'),
			],
			'--font-row-color-' . '#' . $row_id,
			isset($row_style['font']['color']) && $row_style['font']['color'] ? $row_style['font']['color'] : ''
		);

		$this->row_styles->addStyles([
			'bg' => ['background_color' => $row_style['background_color'] ?? ''],
		], '#' . $row_id);

		$this->row_styles->addStyles([
			'padding-top' => mwisset($row_style, 'padding_top', 'px'),
			'padding-bottom' => mwisset($row_style, 'padding_bottom', 'px'),
		], '#' . $row_id . '.row_padding_custom');

		if (isset($row_style['shape_top']) && isset($row_style['shape_top']['show'])) {
			$this->row_styles->addStyles([
				'fill' => $row_style['shape_top']['color'] ?: '#ffffff',
			], '#' . $row_id . ' .mw_row_shape_divider_top svg');
			$this->row_styles->addStyles([
				'height' => $row_style['shape_top']['size'] . 'px',
			], '#' . $row_id . ' .mw_row_shape_divider_top');

			if (isset($row_style['shape_top']['mobile']) && $row_style['shape_top']['mobile']['size']) {
				$this->row_styles->addMobileStyles([
					'height' => $row_style['shape_top']['mobile']['size'] . 'px',
				], '#' . $row_id . ' .mw_row_shape_divider_top');
			}

			if (isset($row_style['shape_top']['tablet']) && $row_style['shape_top']['tablet']['size']) {
				$this->row_styles->addTabletStyles([
					'height' => $row_style['shape_top']['tablet']['size'] . 'px',
				], '#' . $row_id . ' .mw_row_shape_divider_top');
			}
		}
		if (isset($row_style['shape_bottom']) && isset($row_style['shape_bottom']['show'])) {
			$this->row_styles->addStyles([
				'fill' => $row_style['shape_bottom']['color'] ?: '#ffffff',
			], '#' . $row_id . ' .mw_row_shape_divider_bottom svg');
			$this->row_styles->addStyles([
				'height' => $row_style['shape_bottom']['size'] . 'px',
			], '#' . $row_id . ' .mw_row_shape_divider_bottom');

			if (isset($row_style['shape_bottom']['mobile']) && $row_style['shape_bottom']['mobile']['size']) {
				$this->row_styles->addMobileStyles([
					'height' => $row_style['shape_bottom']['mobile']['size'] . 'px',
				], '#' . $row_id . ' .mw_row_shape_divider_bottom');
			}

			if (isset($row_style['shape_bottom']['tablet']) && $row_style['shape_bottom']['tablet']['size']) {
				$this->row_styles->addTabletStyles([
					'height' => $row_style['shape_bottom']['tablet']['size'] . 'px',
				], '#' . $row_id . ' .mw_row_shape_divider_bottom');
			}
		}

		// image on row background
		if (isset($row_style['background_setting']) && $row_style['background_setting'] == 'slider') {
		} elseif (isset($row_style['background_setting']) && $row_style['background_setting'] == 'video') {
			if ((!isset($row_style['show_mobile']) || $this->edit_mode) && isset($row_style['video_image'])) {
				$row_style['video_image']['cover'] = true;
				$this->row_styles->addBgStyle($row_style['video_image'] ?? [] ?: [], '#' . $row_id . ' .row_background_container', $this->edit_mode);
			}
		} else {
			$this->row_styles->addBgStyle($row_style['background_image'] ?? [] ?: [], '#' . $row_id . ' .row_background_container', $this->edit_mode);
		}

		// color cover for image
		$background_overlay_color = '';
		if (!(isset($row_style['background_setting']) && $row_style['background_setting'] != 'image') && isset($row_style['background_image']['image']) && $row_style['background_image']['image'] && isset($row_style['background_image']['color_filter'])) {
			$background_overlay_color = $row_style['background_image']['overlay_color']['rgba'];
		} elseif (isset($row_style['background_setting']) && $row_style['background_setting'] == 'slider' && isset($row_style['slider_overlay_color'])) {
			$background_overlay_color = $row_style['slider_overlay_color']['rgba'] ?? '';
		} elseif (isset($row_style['background_setting']) && $row_style['background_setting'] == 'video' && isset($row_style['video_overlay_color'])) {
			$background_overlay_color = $row_style['video_overlay_color']['rgba'] ?? '';
		}

		if ($background_overlay_color) {
			$this->row_styles->addStyles(['background-color' => $background_overlay_color], '#' . $row_id . ' .row_background_container .background_overlay');
		}

		// color of arrow
		if (isset($row_style['scroll_arrow']) || $this->edit_mode) {
			$this->row_styles->addStyles(['color' => mwisset($row_style, 'arrow_color')], '#' . $row_id . ' .mw_scroll_tonext_icon');
		}

		// left and right padding
		if (isset($row_style['padding_left']) && $row_style['padding_left']['size'] != '') {
			$this->row_styles->addStyles(['padding-left' => $row_style['padding_left']['size'] . $row_style['padding_left']['unit']], '#' . $row_id . '.row_padding_custom .row_fix_width');

			if (!isset($row_style['mobile']) || !isset($row_style['mobile']['padding_left']) || $row_style['mobile']['padding_left']['size'] == '') {
				$row_style['mobile']['padding_left'] = [
					'size' => '0',
					'unit' => 'px',
				];
			}
		}
		if (isset($row_style['padding_right']) && $row_style['padding_right']['size'] != '') {
			$this->row_styles->addStyles(['padding-right' => $row_style['padding_right']['size'] . $row_style['padding_right']['unit']], '#' . $row_id . '.row_padding_custom .row_fix_width');

			if (!isset($row_style['mobile']) || !isset($row_style['mobile']['padding_right']) || $row_style['mobile']['padding_right']['size'] == '') {
				$row_style['mobile']['padding_right'] = [
					'size' => '0',
					'unit' => 'px',
				];
			}
		}

		// top bottom margin
		if (isset($row_style['margin_top']) && $row_style['margin_top'] != '') {
			$this->row_styles->addStyles(['margin-top' => $row_style['margin_top'] . 'px'], '#' . $row_id);
		}
		if (isset($row_style['margin_bottom']) && $row_style['margin_bottom'] != '') {
			$this->row_styles->addStyles(['margin-bottom' => $row_style['margin_bottom'] . 'px'], '#' . $row_id);
		}

		// border
		if (isset($row_style['border-top']) && $row_style['border-top']['size'] && $row_style['border-top']['color']) {
			$this->row_styles->addStyles(['border-top' => $row_style['border-top']['size'] . 'px ' . $row_style['border-top']['style'] . ' ' . $row_style['border-top']['color']], '#' . $row_id);
		}
		if (isset($row_style['border-bottom']) && $row_style['border-bottom']['size'] && $row_style['border-bottom']['color']) {
			$this->row_styles->addStyles(['border-bottom' => $row_style['border-bottom']['size'] . 'px ' . $row_style['border-bottom']['style'] . ' ' . $row_style['border-bottom']['color']], '#' . $row_id);
		}

		if (isset($row_style['row_height']) && $row_style['row_height'] == 'custom' && $row_style['min-height']) {
			$this->row_styles->addStyles(['min-height' => $row_style['min-height'] . 'px'], '#' . $row_id . '.row_height_custom');
			$this->row_styles->addStyles(['min-height' => $row_style['min-height'] . 'px'], '#' . $row_id . '.row_height_custom .miocarousel');
		}

		if (isset($row_style['link_color']) && $row_style['link_color']) {
			$this->row_styles->addStyles(['color' => $row_style['link_color']], '#' . $row_id . '.row_text_custom a:not(.ve_content_button)');
		}

		if (isset($row_style['tablet'])) {
			$this->row_styles->addTabletStyles([
				'padding-top' => mwisset($row_style['tablet'], 'padding_top', 'px'),
				'padding-bottom' => mwisset($row_style['tablet'], 'padding_bottom', 'px'),
			], '#' . $row_id . '.row_padding_custom');

			if (isset($row_style['tablet']['padding_left']) && $row_style['tablet']['padding_left']['size'] != '' && isset($row_style['tablet']['padding_left']['unit'])) {
				$this->row_styles->addTabletStyles(['padding-left' => $row_style['tablet']['padding_left']['size'] . $row_style['tablet']['padding_left']['unit']], '#' . $row_id . '.row_padding_custom .row_fix_width');
			}
			if (isset($row_style['tablet']['padding_right']) && $row_style['tablet']['padding_right']['size'] != '' && isset($row_style['tablet']['padding_right']['unit'])) {
				$this->row_styles->addTabletStyles(['padding-right' => $row_style['tablet']['padding_right']['size'] . $row_style['tablet']['padding_right']['unit']], '#' . $row_id . '.row_padding_custom .row_fix_width');
			}
		}
		if (isset($row_style['mobile'])) {
			$this->row_styles->addMobileStyles([
				'padding-top' => mwisset($row_style['mobile'], 'padding_top', 'px'),
				'padding-bottom' => mwisset($row_style['mobile'], 'padding_bottom', 'px'),
			], '#' . $row_id . '.row_padding_custom');

			if (isset($row_style['mobile']['padding_left']) && $row_style['mobile']['padding_left']['size'] != '' && isset($row_style['mobile']['padding_left']['unit'])) {
				$this->row_styles->addMobileStyles(['padding-left' => $row_style['mobile']['padding_left']['size'] . $row_style['mobile']['padding_left']['unit']], '#' . $row_id . '.row_padding_custom .row_fix_width');
			}
			if (isset($row_style['mobile']['padding_right']) && $row_style['mobile']['padding_right']['size'] != '' && isset($row_style['mobile']['padding_right']['unit'])) {
				$this->row_styles->addMobileStyles(['padding-right' => $row_style['mobile']['padding_right']['size'] . $row_style['mobile']['padding_right']['unit']], '#' . $row_id . '.row_padding_custom .row_fix_width');
			}
		}

		do_action('mw_generate_row_styles', $row_style, $row_id);

		/*


		if (isset($row['style']['margin_t']) && $row['style']['margin_t']['size'] != '') {
		$styles[] = array(
		'styles' => array('margin_top' => $row['style']['margin_t']['size']),
		'element' => '#' . $row_id,
		);
		}
		if (isset($row['style']['margin_b']) && $row['style']['margin_b']['size'] != '') {
		$styles[] = array(
		'styles' => array('margin_bottom' => $row['style']['margin_b']['size']),
		'element' => '#' . $row_id,
		);
		}

		// fixed background for iphones
		if ($this->is_iphone) {
		$styles[] = array(
		'styles' => array('background-attachment' => 'scroll'),
		'element' => '#' . $row_id,
		);
		}

		// styles for mobile devices
		if (isset($row['style']['background_image']) && isset($row['style']['background_image']['mobile_hide'])) {
		$this->add_style(
		'#' . $row_id,
		array('background-image' => 'none'),
		'640'
		);
		}
		*/
		//$this->row_styles->$styles;
	}

	function generate_next_to_scroll_link()
	{
		return '<a href="#" class="mw_scroll_tonext_icon mw_scroll_tonext">' . mw_content_icon_set('chevron-down') . '</a>';
	}

	function isRowEmpty($content)
	{
		$empty = true;
		foreach ($content as $col) {
			if (!empty($col['content'])) {
				$empty = false;
			}
		}

		return $empty;
	}

	function generate_element($code, $key = '', $post_id = '', $edit_mode = true, $pre = '', $added = false, $single = false, $row_set = [], $col_type = 'col_one')
	{
		global $post, $mwContainer;
		$content = '';

		//$code=mwBackCompatibility::element_set($code);
		$this->element_css = $this->css->createCssContainer();

		$this->element_info = [];

		$post_id = $this->post_id ?: $post_id;
		$element_id = $key ? $pre . $key : $pre . 'element_' . md5(microtime());
		$elconfig = '';
		if (isset($code['config'])) {
			$b_styles = [
				'padding-bottom' => mwisset($code['config'], 'margin_bottom', 'px'),
				'max-width' => mwisset($code['config'], 'max_width', 'px'),
			];
			if (isset($code['config']['margin_top']) && $code['config']['margin_top'] < 0) {
				$b_styles['margin-top'] = mwisset($code['config'], 'margin_top', 'px');
			} else {
				$b_styles['padding-top'] = mwisset($code['config'], 'margin_top', 'px');
			}
			$this->element_css->addStyles($b_styles, '#' . $element_id . ' > .element_content');

			if (isset($code['config']['mobile'])) {
				$b_styles = [
					'padding-bottom' => mwisset($code['config']['mobile'], 'margin_bottom', 'px'),
					'max-width' => mwisset($code['config']['mobile'], 'max_width', 'px'),
				];
				if (isset($code['config']['mobile']['margin_top']) && $code['config']['mobile']['margin_top'] < 0) {
					$b_styles['margin-top'] = mwisset($code['config']['mobile'], 'margin_top', 'px');
					$b_styles['padding-top'] = '0px';
				} else {
					$b_styles['padding-top'] = mwisset($code['config']['mobile'], 'margin_top', 'px');
					$b_styles['margin-top'] = '0px';
				}
				$this->element_css->addMobileStyles($b_styles, '#' . $element_id . ' > .element_content');
			}
			if (isset($code['config']['tablet'])) {
				$b_styles = [
					'max-width' => mwisset($code['config']['tablet'], 'max_width', 'px'),
					'padding-bottom' => mwisset($code['config']['tablet'], 'margin_bottom', 'px'),
				];
				if (isset($code['config']['tablet']['margin_top']) && $code['config']['tablet']['margin_top'] < 0) {
					$b_styles['margin-top'] = mwisset($code['config']['tablet'], 'margin_top', 'px');
					$b_styles['padding-top'] = '0px';
				} else {
					$b_styles['margin-top'] = '0px';
					$b_styles['padding-top'] = mwisset($code['config']['tablet'], 'margin_top', 'px');
				}
				$this->element_css->addTabletStyles($b_styles, '#' . $element_id . ' > .element_content');
			}
		}

		$el_class = 'element_container ' . $code['type'] . '_element_container ' . ($code['config']['class'] ?? '');

		if (isset($mwContainer->elements[$code['type']]['subelements'])) {
			$el_class .= ' subelement_container';
			$type = 'subelement';
			$this->subelement_css['#' . $element_id] = $this->element_css;
		} elseif ($code['type'] == 'variable_content') {
			$this->subelement_css['#' . $element_id] = $this->element_css;
			$type = 'element';
		} else {
			$type = 'element';
		}

		// outside element
		if ($single) {
			$el_class .= ' element_single';
		}

		// animate
		if (isset($code['config']['animate']) && $code['config']['animate']) {
			$el_class .= ' ve_animation';
			$animate = 'data-animation="' . $code['config']['animate'] . '"';

			if (isset($code['config']['delay']) && $code['config']['delay'] && !$edit_mode) {
				$animate .= ' data-delay="' . $code['config']['delay'] . '"';
			}

			$this->add_enqueue_script('ve_waypoints_script');
			$this->add_enqueue_style('ve_animate_style');
		} else {
			$animate = '';
		}

		if (isset($code['config']['mobile_visibility'])) {
			$el_class .= ' hide_on_mobile';
		}
		if (isset($code['config']['tablet_visibility'])) {
			$el_class .= ' hide_on_tablet';
		}
		if (isset($code['config']['desktop_visibility'])) {
			$el_class .= ' hide_on_desktop';
		}

		$content .= '<div ' . $animate . ' ' . ($element_id ? 'id="' . $element_id . '"' : '') . ' ' . $elconfig . ' class="' . $el_class . '">';

		// back compatibility (temporary)
		$code = Compatibility::setNewCompatibility($code);
		// back compatibility end

		$el_set_id = '';
		if (isset($code['config']['id']) && $code['config']['id']) {
			$el_set_id = 'id="' . $code['config']['id'] . '"';
		}
		$content .= '<div ' . $el_set_id . ' class="element_content ' . (isset($code['config']['element_align']) ? 'element_align_' . $code['config']['element_align'] : '') . '">';

		// editbar
		if ($edit_mode) {
			$content .= '<div class="content_element_editbar"><div class="ce_editbar">';
			if (!$single) {
				$content .= '<div class="ece_move" href="#" data-icon="' . MW_UI_ICONS_URL . 'elements.svg#element-' . $code['type'] . '" data-element="' . $code['type'] . '" title="Přesunout"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-move"></use></svg></div>';
			}
			$content .= '<a class="ece_edit" data-type="' . $type . '" href="#" title="' . __('Editovat', 'cms_ve') . ' - ' . (isset($mwContainer->elements[$code['type']]) ? $mwContainer->elements[$code['type']]['name'] : '') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-edit-2"></use></svg></a>';
			if (!$single) {
				$content .= '<a class="ece_copy" href="#" title="' . __('Kopírovat', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-copy"></use></svg></a>';
			}
			if (!$single) {
				$content .= '<a class="ece_delete" href="#" title="' . __('Smazat', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-trash-2"></use></svg></a>';
			}
			$content .= '</div></div>';
		}

		//print_r($code);
		if (isset($code['config']['delay']) && $code['config']['delay'] && !$edit_mode) {
			$content .= '<div class="element_container_delay" data-delay="' . $code['config']['delay'] . '">';
		}

		if (mw_is_lite_editor()) {
			$allowed_elements = ['text', 'title', 'image', 'button', 'icon', 'bullets', 'graphic', 'seform', 'features', 'video', 'image_gallery', 'testimonials', 'fapi', 'cookie_management', 'html', 'box', 'recent_posts', 'like', 'likebox'];
			if (!in_array($code['type'], $allowed_elements)) {
				$code['type'] = '';
			}
		}

		if (function_exists('ve_element_' . $code['type'])) {
			$content .= mw_add_element_nbsp(call_user_func_array('ve_element_' . $code['type'], [$code, '#' . $element_id, $post_id, $edit_mode, $added, $row_set, $col_type]), $code['type']);
		} else {
			$this->add_element_info(__('Tento element nelze zobrazit, pravděpodobně není v této verzi Miowebu podporován. Smažte jej nebo zvyšte verzi Miowebu.', 'cms_ve'));
		}

		$content .= $this->generate_element_info($code['type'], $edit_mode);

		if (isset($code['config']['delay']) && $code['config']['delay'] && !$edit_mode) {
			$content .= '</div>';
		}

		$content .= '</div>'; // element_content

		if ($edit_mode) {
			$this->add_to_setting_container($element_id, $code);
		}

		if ($edit_mode) {
			$content .= '<div class="mw_page_builder_droparea mw_page_builder_droparea_element" data-group="element"></div>';
		}

		// print element styles
		if (isset($mwContainer->elements[$code['type']]['subelements']) || $code['type'] == 'variable_content') {
			$content .= $this->css->printCss($this->subelement_css['#' . $element_id], $element_id . '_style', $this->edit_mode);
		} else {
			$content .= $this->css->printCss($this->element_css, $element_id . '_style', $this->edit_mode);
		}

		$content .= '</div>';

		return $content;
	}

	function print_single_element($key, $post_id, $setting = [])
	{
		$content = get_post_meta($post_id, 'single_elements', true);
		$code = isset($content[$key]) ? visualEditor::decode($content[$key]) : $setting;

		return $this->generate_element($code, 'element_' . $key, $post_id, $this->edit_mode, '', false, true);
	}

	/**
	 * @param $row_type
	 *
	 * @return string
	 */
	function generate_row_edit_bar($row_type)
	{
		$class = 'row_edit_container admin_feature ';
		if (isset($this->template_config['hide_rows']) || $row_type === 'slide') {
			$class .= 'row_edit_container_editonly';
		}
		if (isset($this->template_config['delete_rows'])) {
			$class .= 'row_edit_container_noedit';
		}

		$content = '<div class="' . $class . '">';

		$content .= '<a href="#" class="row_edit" title="' . __('Editovat blok', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-edit-2"></use></svg></a>';

		$content .= '<div class="row_edit_more cms_nodisp">';
		$content .= '<div class="row_move row_edit_more_item" title="' . __('Přesunout', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-move"></use></svg></div>';
		$content .= '<a class="row_copy row_edit_more_item" href="#" title="' . __('Vytvořit kopii bloku', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-copy"></use></svg></a>';

		$content .= '<a class="row_copy_memory row_edit_more_item" href="#" title="' . __('Kopírovat blok do schránky', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-clipboard"></use></svg></a>';
		$content .= apply_filters('mw_developer_edit_row', '');
		$content .= '<a class="row_layout row_edit_more_item" href="#" title="' . __('Změnit rozložení bloku', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-twocols"></use></svg></a>';
		$content .= '<a class="row_delete row_edit_more_item" href="#" title="' . __('Smazat blok', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-trash-2"></use></svg></a>';
		$content .= '</div>';

		$content .= '<div class="row_layouts hidden">';
		$content .= '<span class="row_layouts_arrow"></span>';
		$content .= '<span class="row_layouts_label">' . __('Změnit rozložení bloku na:', 'cms_ve') . '</span>';
		global $mwContainer;
		foreach ($mwContainer->empty_rows as $row) {
			$content .= '<div class="row_layout" title="' . $row['title'] . '" data-content="' . $row['content'] . '" data-type="row" data-rowtype="empty">' . mw_icon('row-' . $row['thumb'], '', MW_UI_ICONS_URL . 'rows.svg') . '</div>';
		}
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	function generate_element_info($type, $edit_mode = false)
	{
		$content = '';
		if ($edit_mode) {
			if (isset($this->element_info['info'])) {
				$content .= '<div class="mw_info_box admin_feature ece_edit" data-type="' . $type . '">'
				. '<svg role="img"><use xlink:href="' . MW_UI_ICONS_URL . 'elements.svg#element-' . $type . '"></use></svg>'
				. $this->element_info['info']
				. '</div>';
			} elseif (isset($this->element_info['error'])) {
				$content .= '<div class="mw_error_box admin_feature">' . $this->element_info['error'] . '</div>';
			}
			$this->element_info = [];
		}

		return $content;
	}

	function add_element_info($text, $type = 'error')
	{
		$this->element_info[$type] = $text;
	}

	function itemEditButton($objectId, $itemId)
	{
		if ($this->edit_mode) {
			$link = mwSetting()->getObject($objectId)->getEditUrl($itemId);
			$content = '<a target="_blank" class="mw_edit_but" title="' . __('Editovat', 'cms_ve') . '" href="' . $link . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-edit-2"></use></svg></a>';

			return $content;
		}

		return '';
	}


	/* Footer ********
	*******************************************************************************  */
	function add_page_footer()
	{
		if ($this->edit_mode) {
			echo '<div class="cms_nodisp">';
			wp_editor('', '', [
				'textarea_rows' => '',
				'quicktags' => false,
				'media_buttons' => false,
				'tinymce' => [
					'selector' => '',
					'inline' => true,
					'content_css' => false,
					'skin_url' => get_bloginfo('template_url') . '/library/visualeditor/includes/tinymce/mioweb',
					'skin' => 'mioweb',
				],
			]);
			echo '</div>';

			echo '<script type="text/javascript">
	          /* <![CDATA[ */
	          var mw_header_height = "' . ($this->header_setting['header_height'] ?? '') . '";
	          var mw_used_header = "' . $this->used_header . '";
	          var mw_page_id = "' . $this->post_id . '";
	          var mw_setting_container=' . json_encode($this->setting_container) . '
	          /* ]]> */
	          </script>';
		} else {
			MwCookies()->printCookieBar();
		}

		// print page background
		echo $this->generate_background($this->page_setting, 'body');

		// cookie bar



		// print popups on page
		$this->popups->print_popups();

		echo $this->css->printGlobalCss($this->edit_mode);

		MwCodes()->printFooterCodes();
		MwCodes()->printConversionCodes();
	}

	function printBodyStyles($page_setting = null, $name = 'body_style')
	{
		if (empty($page_setting)) {
			$page_setting = $this->page_setting;
		}

		if (!isset($page_setting['background_setting'])) {
			$page_setting['background_setting'] = 'image';
		}

		if (!isset($page_setting['title_font']['line-height']) || $page_setting['title_font']['line-height'] == '') {
			$page_setting['title_font']['line-height'] = '1.2';
		}

		$this->body_styles->addStyles([
			'background-color' => isset($page_setting['background_color']) && $name != 'global_body_style' ? $page_setting['background_color'] : '',
			'font' => $page_setting['font'] ?? '',
		], 'body');

		if (isset($page_setting['font']['line-height']) && $page_setting['font']['line-height']) {
			$this->body_styles->addVariableStyles(
				[
					'.entry_content ul:not(.in_element_content) li' => ['background-position-y'],
				],
				'--page-list-background-pos',
				'calc(' . ($page_setting['font']['line-height'] / 2) . 'em - 13px)'
			);
		}

		$this->body_styles->addVariableStyles(
			[
				'.row_text_auto.light_color' => ['color'],
				'.row_text_default' => ['color'],
				'.row .light_color' => ['color'],
				'.row .text_default' => ['color'],
			],
			'--page-text-color',
			isset($page_setting['font']['color']) && $page_setting['font']['color'] ? $page_setting['font']['color'] : '#111111'
		);

		$this->body_styles->addVariableStyles(
			[
				'a' => ['color'],
				'.row_text_auto.light_color a:not(.ve_content_button)' => ['color'],
				'.row_text_default a:not(.ve_content_button)' => ['color'],
			//'.row .light_color a:not(.ve_content_button)' => array('color'),
				'.row .ve_content_block.text_default a:not(.ve_content_button)' => ['color'],
			//'.row.row_text_default a:not(.ve_content_button)' => array('color'),
			],
			'--page-link-color',
			isset($page_setting['link_color']) && $page_setting['link_color'] ? $page_setting['link_color'] : 'blue'
		);

		$this->body_styles->addVariableStyles(
			[
				'a:not(.ve_content_button):hover' => ['color'],
				'.row_text_auto.light_color a:not(.ve_content_button):hover' => ['color'],
				'.row_text_default a:not(.ve_content_button):hover' => ['color'],
				'.row .ve_content_block.text_default a:not(.ve_content_button):hover' => ['color'],
			],
			'--page-link-hover-color',
			isset($page_setting['hover_color']) && $page_setting['hover_color'] ? $page_setting['hover_color'] : (isset($page_setting['link_color']) && $page_setting['link_color'] ? $page_setting['link_color'] : 'blue')
		);

		$this->body_styles->addVariableStyles(
			[
				'.row_text_auto.dark_color' => ['color'],
				'.row_text_invers' => ['color'],
				'.row .text_invers' => ['color'],
				'.row .dark_color:not(.text_default)' => ['color'],

				'.blog_entry_content .dark_color:not(.text_default)' => ['color'],

				'.row_text_auto.dark_color a:not(.ve_content_button)' => ['color'],
				'.row .dark_color:not(.text_default) a:not(.ve_content_button)' => ['color'],

				'.row_text_invers a:not(.ve_content_button)' => ['color'],
				'.row .ve_content_block.text_invers a:not(.ve_content_button)' => ['color'],
			],
			'--page-text-inverse-color',
			isset($page_setting['inverse_text_color']) && $page_setting['inverse_text_color'] ? $page_setting['inverse_text_color'] : '#ffffff'
		);

		$this->body_styles->addVariableStyles(
			[
				'.row .light_color a:not(.ve_content_button)' => ['color'],
			],
			'--page-link-color',
			isset($page_setting['link_color']) && $page_setting['link_color'] ? $page_setting['link_color'] : 'blue'
		);

		$this->body_styles->addVariableStyles(
			[
				'.row .light_color a:not(.ve_content_button):hover' => ['color'],
			],
			'--page-link-hover-color',
			isset($page_setting['hover_color']) && $page_setting['hover_color'] ? $page_setting['hover_color'] : (isset($page_setting['link_color']) && $page_setting['link_color'] ? $page_setting['link_color'] : 'blue')
		);

		$this->body_styles->addFontVariableStyles(
			'.title_element_container,'
			. '.mw_element_items_style_4 .mw_element_item:not(.dark_color) .title_element_container,'
			. '.mw_element_items_style_7 .mw_element_item:not(.dark_color) .title_element_container,'
			. '.in_features_element_4 .mw_feature:not(.dark_color) .title_element_container,'
			. '.in_element_image_text_2 .el_it_text:not(.dark_color) .title_element_container,'
			. '.in_element_image_text_3 .el_it_text:not(.dark_color) .title_element_container,'
			. '.entry_content h1,'
			. '.entry_content h2,'
			. '.entry_content h3,'
			. '.entry_content h4,'
			. '.entry_content h5,'
			. '.entry_content h6',
			'--page-title-font',
			$page_setting['title_font']
		);

		$this->body_styles->addFontVariableStyles(
			'.subtitle_element_container,'
			. '.mw_element_items_style_4 .mw_element_item:not(.dark_color) .subtitle_element_container,'
			. '.mw_element_items_style_7 .mw_element_item:not(.dark_color) .subtitle_element_container,'
			. '.in_features_element_4 .mw_feature:not(.dark_color) .subtitle_element_container,'
			. '.in_element_image_text_2 .el_it_text:not(.dark_color) .subtitle_element_container,'
			. '.in_element_image_text_3 .el_it_text:not(.dark_color) .subtitle_element_container',
			'--page-subtitle-font',
			$page_setting['subtitle_font'] ?? $page_setting['title_font']
		);

		$this->body_styles->addStyles([
			'font' => $page_setting['h1_font'],
		], '.entry_content h1');
		$this->body_styles->addStyles([
			'font' => $page_setting['h2_font'],
		], '.entry_content h2');
		$this->body_styles->addStyles([
			'font' => $page_setting['h3_font'],
		], '.entry_content h3');
		$this->body_styles->addStyles([
			'font' => $page_setting['h4_font'],
		], '.entry_content h4');
		$this->body_styles->addStyles([
			'font' => $page_setting['h5_font'],
		], '.entry_content h5');
		$this->body_styles->addStyles([
			'font' => $page_setting['h6_font'],
		], '.entry_content h6');

		$page_width = '';
		if (isset($page_setting['page_width_preset'])) {
			$page_width = $page_setting['page_width_preset'];
		}
		if ($page_width == 'custom' && isset($page_setting['page_width']['size']) && $page_setting['page_width']['size']) {
			$page_width = $page_setting['page_width']['size'] . $page_setting['page_width']['unit'];
		} elseif ($page_width == 'custom') {
			$page_width = '';
		}

		if ($page_width || $this->edit_mode) {
			//if(!$page_width) $page_width='970px';
			$this->body_styles->addVariableStyles(
				[
					'.row_fix_width,'
					. '.fix_width,'
					. '.fixed_width_content .visual_content_main,'
					. '.row_fixed,'
					. '.fixed_width_page .visual_content_main,'
					. '.fixed_width_page header,'
					. '.fixed_width_page footer,'
					. '.fixed_narrow_width_page #wrapper,'
					. '.ve-header-type2 .header_nav_container' => ['max-width'],
				],
				'--page-width, 970px',
				$page_width
			);
		}

		if ($name != 'global_body_style') {
			// image on background
			if (isset($page_setting['background_setting']) && $page_setting['background_setting'] == 'slider') {
			} elseif (isset($page_setting['background_setting']) && $page_setting['background_setting'] == 'video') {
				if (!isset($page_setting['show_mobile']) || $this->edit_mode) {
					$this->body_styles->addBgStyle($page_setting['video_image'] ?? [] ?: [], '.body_background_container', $this->edit_mode);
				}
			} else {
				$this->body_styles->addBgStyle($page_setting['background_image'] ?? [] ?: [], '.body_background_container', $this->edit_mode);
			}

			// color cover for image
			$background_overlay_color = '';
			if (!(isset($page_setting['background_setting']) && $page_setting['background_setting'] != 'image') && isset($page_setting['background_image']['image']) && $page_setting['background_image']['image'] && isset($page_setting['background_image']['color_filter'])) {
				$background_overlay_color = $page_setting['background_image']['overlay_color']['rgba'];
			} elseif (isset($page_setting['background_setting']) && $page_setting['background_setting'] == 'slider' && isset($page_setting['slider_overlay_color'])) {
				$background_overlay_color = $page_setting['slider_overlay_color']['rgba'] ?? '';
			} elseif (isset($page_setting['background_setting']) && $page_setting['background_setting'] == 'video' && isset($page_setting['video_overlay_color'])) {
				$background_overlay_color = $page_setting['video_overlay_color']['rgba'] ?? '';
			}

			if ($background_overlay_color) {
				$this->body_styles->addStyles(['background-color' => $background_overlay_color], '.body_background_container .background_overlay');
			}
		}

		return $this->css->printCss($this->body_styles, $name, $this->edit_mode);
	}

	function get_layer_fonts($layer, $fonts)
	{
		if ($layer && is_array($layer)) {
			foreach ($layer as $row) {
				$fonts = $this->get_row_fonts($row, $fonts);
			}
		}

		return $fonts;
	}

	function get_row_fonts($row, $fonts)
	{
		if (isset($row['style']['font'])) {
			$fonts = $this->get_item_fonts($row['style']['font'], $fonts);
		}
		if (isset($row['content'])) {
			foreach ($row['content'] as $col) {
				foreach ($col['content'] as $element) {
					$fonts = $this->get_element_fonts($element, $fonts);
				}
			}
		}
		if (isset($row['type']) && $row['type'] == 'slider' && isset($row['style']['slides'])) {
			foreach ($row['style']['slides'] as $key => $val) {
				if (!array_key_exists('file', $fonts)) {
					$fonts['file'] = [];
				}
				if (!array_key_exists('google', $fonts)) {
					$fonts['google'] = [];
				}
				$weditor_fonts = $this->get_weditor_fonts($val['slider_content']);
				$fonts['file'] = $this->merge_fonts($fonts['file'], $weditor_fonts['file']);
				$fonts['google'] = $this->merge_fonts($fonts['google'], $weditor_fonts['google']);
			}
		}

		return $fonts;
	}

	function get_element_fonts($element, $fonts)
	{
		if (empty($fonts['file'])) {
			$fonts['file'] = [];
		}
		if (empty($fonts['google'])) {
			$fonts['google'] = [];
		}
		// get popup fonts
		if ($element['type'] == 'button' && isset($element['style']['show']) && $element['style']['show'] == 'popup' && $element['style']['popup']) {
			$weditor_fonts = $this->get_weditor_fonts($element['style']['popup']);
			$fonts['file'] = $this->merge_fonts($fonts['file'], $weditor_fonts['file']);
			$fonts['google'] = $this->merge_fonts($fonts['google'], $weditor_fonts['google']);
		}
		if ($element['type'] == 'variable_content' && isset($element['style']['content']) && $element['style']['content']) {
			$weditor_fonts = $this->get_weditor_fonts($element['style']['content']);
			$fonts['file'] = $this->merge_fonts($fonts['file'], $weditor_fonts['file']);
			$fonts['google'] = $this->merge_fonts($fonts['google'], $weditor_fonts['google']);
		}
		if ($element['type'] == 'text' && isset($element['style']['content']) && $element['style']['content']) {
			preg_match_all('/\[(popup|content) id=(\d+)\]/', $element['style']['content'], $text_popups, PREG_PATTERN_ORDER);
			foreach ($text_popups[2] as $tpop) {
				$fonts['file'] = $this->merge_fonts($fonts['file'], get_post_meta($tpop, 've_file_fonts', true));
				$fonts['google'] = $this->merge_fonts($fonts['google'], get_post_meta($tpop, 've_google_fonts', true));
			}
		}
		if (isset($element['style'])) {
			$fonts = $this->get_setting_fonts($element['style'], $fonts);
		}

		if ($element['type'] == 'twocols' || $element['type'] == 'box') {
			if (isset($element['content'][0]) && is_array($element['content'][0])) {
				foreach ($element['content'][0] as $subelement) {
					$fonts = $this->get_setting_fonts($subelement['style'], $fonts);
				}
			}
			if (isset($element['content'][1]) && is_array($element['content'][1])) {
				foreach ($element['content'][1] as $subelement) {
					$fonts = $this->get_setting_fonts($subelement['style'], $fonts);
				}
			}
		}

		return $fonts;
	}

	function get_weditor_fonts($id)
	{
		$fonts['google'] = get_post_meta($id, 've_google_fonts', true);
		$fonts['file'] = get_post_meta($id, 've_file_fonts', true);

		return $fonts;
	}

	function merge_fonts($font1, $font2)
	{
		if (!empty($font2) && is_array($font2)) {
			foreach ($font2 as $key => $val) {
				if (isset($font1[$key])) {
					$font1[$key] += $val;
				} else {
					$font1[$key] = $val;
				}
			}
		}

		return $font1;
	}

	function get_setting_fonts($set, $fonts)
	{
		if (is_array($set)) {
			foreach ($set as $key => $val) {
				if (strpos($key, 'font') !== false) {
					$fonts = $this->get_item_fonts($set[$key], $fonts);
				} elseif (is_array($val)) {
					foreach ($val as $subkey => $subval) {
						if (strpos($subkey, 'font') !== false) {
							$fonts = $this->get_item_fonts($set[$key][$subkey], $fonts);
						} elseif (is_array($subval)) {
							foreach ($subval as $subsubkey => $susubbval) {
								if (strpos($subsubkey, 'font') !== false) {
									$fonts = $this->get_item_fonts($set[$key][$subkey][$subsubkey], $fonts);
								}
							}
						}
					}
				}
			}
		}

		return $fonts;
	}

	function get_item_fonts($element, $fonts)
	{
		$weight = $element['weight'] ?? '';
		if (isset($element['font-family'])) {
			if (isset(MW()->google_fonts[$element['font-family']])) {
				$fonts['google'][$element['font-family']][$weight] = $weight;
			}
			if (isset(MW()->file_fonts[$element['font-family']])) {
				$fonts['file'][$element['font-family']][$weight] = $weight;
			}
		}

		return $fonts;
	}

	/* Header ********
	*******************************************************************************  */

	function printLogo()
	{
		?>
		<a href="<?php echo $this->home_url; ?>" id="site_title" title="<?php echo get_bloginfo('name'); ?>">
		<?php
		if (isset($this->header_setting['logo_setting']) && $this->header_setting['logo_setting'] == 'text') {
			echo stripslashes($this->header_setting['logo_text']);
		} else {
			$logo = new Image($this->header_setting['logo'] ?? [] ?: []);
			if ($this->edit_mode || !$logo->isEmpty()) {
				$class = $logo->isEmpty() ? 'cms_nodisp' : '';
				echo $logo->printImg([
				'max_width' => (isset($this->header_setting['logo_size']) && $this->header_setting['logo_size'] ? $this->header_setting['logo_size'] : 120),
						'alt' => get_bloginfo('name'),
						'lazy_loading' => false,
				], $class, $this->edit_mode);
			}
		}
		?>
		</a>
		<?php
	}

	function showHeader()
	{
		return $this->header_setting['show'] != 'noheader' && $this->header_setting['show'] != 'none';
	}

	function printHeader($echo = true)
	{
		global $mwContainer;
		$content = '';

		if ($this->showHeader() && isset($mwContainer->list['headers'][$this->header_setting['appearance']])) {
			$header_set = $mwContainer->list['headers'][$this->header_setting['appearance']];

			$with_content = false;
			if (isset($this->header_setting['before_header']) && $this->header_setting['before_header']) {
				$content .= $this->weditor->create_content($this->header_setting['before_header'], 've_header');
				$with_content = true;
			}

			if (!$this->header_setting['menu_color']) {
				$this->header_setting['menu_color'] = '#111';
			}
			if (!$this->header_setting['menu_active_color']) {
				$this->header_setting['menu_active_color'] = $this->header_setting['menu_color'];
			}
			if (!$this->header_setting['menu_submenu_bg']) {
				$this->header_setting['menu_submenu_bg'] = $this->header_setting['menu_active_color'];
			}

			$header_class = 've-header-type' . $header_set['type'];

			$menu_style = $header_set['menu_type'] ?? $this->header_setting['menu_style'] ?? '3';
			$header_class .= ' menu_style_h' . $menu_style;

			$header_class .= ' menu_active_color_' . (Colors::isLightColor($this->header_setting['menu_active_color']) ? 'light' : 'dark');
			$header_class .= ' menu_submenu_bg_' . (Colors::isLightColor($this->header_setting['menu_submenu_bg']) ? 'light' : 'dark');

			if (isset($this->header_setting['fixed_header']) && $this->showHeader()) {
				$header_class .= ' ve_fixed_header';

				if (isset($this->header_setting['header_shadow_fix'])) {
					$header_class .= ' ve_fixed_with_shadow';
				}

				if (isset($this->header_setting['header_desktop_only_fix'])) {
					$header_class .= ' ve_fixed_desktop_only';
				}
			}

			if (isset($this->header_setting['background_image']['cover'])) {
				$header_class .= ' background_cover';
			}

			$align = 'center';
			if ($this->header_setting['appearance'] == 'type1c' || $this->header_setting['appearance'] == 'type9') {
				$align = 'left';
			} elseif ($this->header_setting['appearance'] == 'type1' || $this->header_setting['appearance'] == 'type5') {
				$align = 'right';
			}
			$header_class .= ' header_menu_align_' . $align;

			if ($this->isTransparentHeader()) {
				$header_class .= ' mw_transparent_header';
			}

			if ($with_content) {
				$content .= '<div class="header_with_content">';
			}
			$content .= '<div id="header" class="mw_header ' . $header_class . '">';

			$content .= $this->generate_simple_background('header', $this->header_setting);

			ob_start();
			load_template($header_set['file'], true);
			$content .= ob_get_contents();
			ob_end_clean();


			if ($this->edit_mode /*&& is_page()) || (defined('DOING_AJAX') && DOING_AJAX)*/) {
				$post_id = defined('DOING_AJAX') && DOING_AJAX ? $_POST['post_id'] : $this->post_id;
				$content .= '<a href="#" class="mw_edit_but mw_edit_option_onpage admin_feature" data-edit="header" data-objectid="page" data-itemid="' . $post_id . '" title="' . __('Editovat hlavičku', 'cms_ve') . '" data-title="' . __('Hlavička', 'cms_ve') . '">' . mw_icon('icon-edit-2') . '</a>';
			}

			$content .= '</div>';
			if ($with_content) {
				$content .= '</div>';
			}

			$content .= $this->printHeaderCss();

			if ($echo) {
				echo $content;
			} else {
 return $content;
			}
		}
	}

	function printHeaderCss()
	{
		// header styles

		$this->header_css->addStyles(
			[
				'bg' => [
					'background_color' => $this->header_setting['background_color'],
				],
				'border_bottom' => mwisset_array($this->header_setting['border-bottom']),
			],
			'#header'
		);

		$this->header_css->addBgStyle($this->header_setting['background_image'] ?? [] ?: [], '#header .header_background_container', $this->edit_mode);

		// color cover for image
		if (isset($this->header_setting['background_image']) && isset($this->header_setting['background_image']['image']) && $this->header_setting['background_image']['image'] && isset($this->header_setting['background_image']['color_filter'])) {
			$this->header_css->addStyles(['background-color' => $this->header_setting['background_image']['overlay_color']['rgba']], '#header .header_background_container .background_overlay');
		}

		if (isset($this->header_setting['background_image']) && isset($this->header_setting['background_image']['mobile_hide'])) {
			$this->header_css->addMobileStyles([
				'background-image' => 'none',
			], '#header');
		}
		$this->header_css->addStyles(
			[
				'font' => ($this->header_setting['logo_font'] ?? ''),
			],
			'#site_title'
		);
		$this->header_css->addStyles(
			[
				'max-width' => (isset($this->header_setting['logo_size']) && $this->header_setting['logo_size'] ? $this->header_setting['logo_size'] . 'px' : '120px'),
			],
			'#site_title img'
		);

		/* menu */
		$this->header_css->addStyles(
			[
				'font' => ($this->header_setting['menu_font'] ?? ''),
			],
			'.mw_header .menu > li > a'
		);

		$menu_color = $this->header_setting['menu_color'] ?? '';

		$this->header_css->addVariableStyles(
			[
				'.header_nav_container nav .menu > li > a, .mw_header_icons a' => ['color'],
				'.menu_style_h3 .menu > li:after, .menu_style_h4 .menu > li:after' => ['color'],
				'#mobile_nav' => ['color'],
				'#mobile_nav svg' => ['fill'],
				'.mw_to_cart svg' => ['fill'],
			],
			'--menu-item-color',
			$menu_color
		);
		$this->header_css->addVariableStyles(
			[
				'.header_nav_container .sub-menu' => ['background-color'],
			],
			'--menu-item-submenu-color',
			($this->header_setting['menu_submenu_bg'] ?? '')
		);
		$this->header_css->addVariableStyles(
			[
				'.menu_style_h6 .menu,'
				. '.menu_style_h6 .mw_header_right_menu,'
				. '.menu_style_h7 .header_nav_fullwidth_container,'
				. '.menu_style_h6 #mobile_nav,'
				. '.menu_style_h7 #mobile_nav' => ['background-color'],
			],
			'--menu-background-color',
			($this->header_setting['menu_bg'] ?? '')
		);
		$this->header_css->addVariableStyles(
			[
				'.mw_to_cart:hover svg' => ['fill'],

				'.menu_style_h1 .menu > li.current-menu-item a, ' .
				'.menu_style_h1 .menu > li.current-page-ancestor > a, ' .
				'.menu_style_h3 .menu > li.current-menu-item a, ' .
				'.menu_style_h3 .menu > li.current-page-ancestor > a, ' .
				'.menu_style_h4 .menu > li.current-menu-item a, ' .
				'.menu_style_h4 .menu > li.current-page-ancestor > a, ' .
				'.menu_style_h8 .menu > li.current-menu-item a, ' .
				'.menu_style_h9 .menu > li.current-menu-item a, ' .
				'.mw_header_icons a:hover, ' .
				'.menu_style_h1 .menu > li:hover > a, ' .
				'.menu_style_h3 .menu > li:hover > a, ' .
				'.menu_style_h4 .menu > li:hover > a, ' .
				'.menu_style_h8 .menu > li:hover > a, ' .
				'.menu_style_h9 .menu > li:hover > a' => ['color'],

				'.menu_style_h2 li span' => ['background-color'],
				'#mobile_nav:hover' => ['color'],
				'#mobile_nav:hover svg' => ['fill'],

				'.menu_style_h5 .menu > li:hover > a, ' .
				'.menu_style_h5 .menu > li.current-menu-item > a, ' .
				'.menu_style_h5 .menu > li.current-page-ancestor > a, ' .
				'.menu_style_h5 .menu > li.current_page_parent > a, ' .
				'.menu_style_h5 .menu > li:hover > a:before' => ['background-color'],

				'.menu_style_h5 .sub-menu' => ['background-color'],

				'.menu_style_h6 .menu > li:hover > a, ' .
				'.menu_style_h6 .menu > li.current-menu-item > a, ' .
				'.menu_style_h6 .menu > li.current-page-ancestor > a, ' .
				'.menu_style_h6 .menu > li.current_page_parent > a, ' .
				'.menu_style_h7 .menu > li:hover > a, ' .
				'.menu_style_h7 .menu > li.current-menu-item > a, ' .
				'.menu_style_h7 .menu > li.current-page-ancestor > a, ' .
				'.menu_style_h7 .menu > li.current_page_parent > a' => ['background-color'],

				'.menu_style_h6 .sub-menu, ' .
				'.menu_style_h7 .sub-menu' => ['background-color'],
			],
			'--menu-item-active-color',
			($this->header_setting['menu_active_color'] ?? '')
		);

		if (isset($this->header_setting['header_icons_size']) && $this->header_setting['header_icons_size'] !== '') {
			$this->header_css->addStyles(
					[
							'font-size' => $this->header_setting['header_icons_size'] . 'px',
					],
					'.mw_header_icons'
			);
		}

		if (isset($this->header_setting['header_padding']) && $this->header_setting['header_padding'] !== '') {
			$this->header_css->addStyles(
				[
					'padding-top' => $this->header_setting['header_padding'] . 'px',
					'padding-bottom' => $this->header_setting['header_padding'] . 'px',
				],
				'#header_in'
			);
		}

		if (isset($this->header_setting['fixed_header'])) {
			$this->header_css->addStyles(
				[
					'bg' => isset($this->header_setting['background_color_fix']) ? ['background_color' => $this->header_setting['background_color_fix']] : '',
					//'box-shadow' => (isset($this->header_setting['header_shadow_fix'])) ? array('horizontal' => 0, 'vertical' => 3, 'size' => 3, 'transparency' => 8) : array(),
				],
				'#header.ve_fixed_header_scrolled'
			);
			$this->header_css->addStyles(
				[
					'padding-top' => isset($this->header_setting['header_padding_fix']) ? $this->header_setting['header_padding_fix'] . 'px' : '',
					'padding-bottom' => isset($this->header_setting['header_padding_fix']) ? $this->header_setting['header_padding_fix'] . 'px' : '',
				],
				'.ve_fixed_header_scrolled #header_in'
			);
			$this->header_css->addStyles(
				[
					'color' => isset($this->header_setting['logo_color_fix']) && $this->header_setting['logo_color_fix'] ? $this->header_setting['logo_color_fix'] : '',
				],
				'.ve_fixed_header_scrolled #site_title'
			);

			$this->header_css->addVariableStyles(
				[
					'.ve_fixed_header_scrolled .header_nav_container nav .menu > li > a, .ve_fixed_header_scrolled .mw_header_icons a' => ['color'],
					'.ve_fixed_header_scrolled.menu_style_h3 .menu > li:after, .ve_fixed_header_scrolled.menu_style_h4 .menu > li:after' => ['color'],
					'.ve_fixed_header_scrolled #mobile_nav' => ['color'],
					'.ve_fixed_header_scrolled #mobile_nav svg, .ve_fixed_header_scrolled .mw_to_cart svg' => ['fill'],
				],
				'--fixed-menu-item-color, var(--menu-item-color)',
				(isset($this->header_setting['fixed_menu_color']) && $this->header_setting['fixed_menu_color'] ? $this->header_setting['fixed_menu_color'] : '')
			);

			$this->header_css->addVariableStyles(
				[
					'.ve_fixed_header_scrolled .mw_to_cart:hover svg' => ['fill'],

					'.ve_fixed_header_scrolled.menu_style_h1 .menu > li.current-menu-item a, ' .
					'.ve_fixed_header_scrolled.menu_style_h1 .menu > li.current-page-ancestor > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h3 .menu > li.current-menu-item a, ' .
					'.ve_fixed_header_scrolled.menu_style_h3 .menu > li.current-page-ancestor > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h4 .menu > li.current-menu-item a, ' .
					'.ve_fixed_header_scrolled.menu_style_h4 .menu > li.current-page-ancestor > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h8 .menu > li.current-menu-item a, ' .
					'.ve_fixed_header_scrolled.menu_style_h9 .menu > li.current-menu-item a, ' .
					'.ve_fixed_header_scrolled .mw_header_icons a:hover, ' .
					'.ve_fixed_header_scrolled.menu_style_h1 .menu > li:hover > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h3 .menu > li:hover > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h4 .menu > li:hover > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h8 .menu > li:hover > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h9 .menu > li:hover > a' => ['color'],

					'.ve_fixed_header_scrolled.menu_style_h2 li span' => ['background-color'],
					'.ve_fixed_header_scrolled #mobile_nav:hover' => ['color'],
					'.ve_fixed_header_scrolled #mobile_nav:hover svg' => ['fill'],

					'.ve_fixed_header_scrolled.menu_style_h5 .menu > li:hover > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h5 .menu > li.current-menu-item > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h5 .menu > li.current-page-ancestor > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h5 .menu > li.current_page_parent > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h5 .menu > li:hover > a:before' => ['background-color'],

					'.ve_fixed_header_scrolled.menu_style_h5 .sub-menu' => ['background-color'],

					'.ve_fixed_header_scrolled.menu_style_h6 .menu > li:hover > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h6 .menu > li.current-menu-item > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h6 .menu > li.current-page-ancestor > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h6 .menu > li.current_page_parent > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h7 .menu > li:hover > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h7 .menu > li.current-menu-item > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h7 .menu > li.current-page-ancestor > a, ' .
					'.ve_fixed_header_scrolled.menu_style_h7 .menu > li.current_page_parent > a' => ['background-color'],

					'.ve_fixed_header_scrolled.menu_style_h6 .sub-menu, ' .
					'.ve_fixed_header_scrolled.menu_style_h7 .sub-menu' => ['background-color'],
				],
				'--fixed-menu-item-active-color, var(--menu-item-active-color)',
				(isset($this->header_setting['fixed_menu_active_color']) && $this->header_setting['fixed_menu_active_color'] ? $this->header_setting['fixed_menu_active_color'] : '')
			);
		}
		//print_r($this->header_setting);
		//echo $this->header_setting['header_height'];

		if (isset($this->header_setting['header_height']) && $this->header_setting['header_height'] !== '') {
			$this->header_css->addStyles(
				[
					'min-height' => 'calc(100vh - ' . $this->header_setting['header_height'] . 'px)',
				],
				'body:not(.page_with_transparent_header) .row_height_full'
			);

			$this->header_css->addStyles(
				[
					'padding-top' => ($this->header_setting['header_height'] - 20) . 'px',
				],
				'.page_with_transparent_header .visual_content_main:not(.mw_transparent_header_padding) > .row:first-child .row_fix_width, .page_with_transparent_header .visual_content_main:not(.mw_transparent_header_padding) .mw_page_builder_content > .row:nth-child(2) .row_fix_width'
			);
			$this->header_css->addStyles(
				[
					'padding-top' => ($this->header_setting['header_height'] + 10) . 'px',
				],
				'.page_with_transparent_header .visual_content_main:not(.mw_transparent_header_padding) > .row:first-child .miocarousel .row_fix_width, .page_with_transparent_header .visual_content_main:not(.mw_transparent_header_padding) .mw_page_builder_content > .row:nth-child(2) .miocarousel .row_fix_width'
			);
			$this->header_css->addStyles(
				[
					'padding-top' => $this->header_setting['header_height'] . 'px',
				],
				'.page_with_transparent_header #wrapper > .empty_content, .page_with_transparent_header .mw_transparent_header_padding'
			);
		}

		$header_width = '';
		if (isset($this->header_setting['header_width_preset'])) {
			$header_width = $this->header_setting['header_width_preset'];
		}

		if ($header_width || $this->edit_mode) {
			$this->header_css->addVariableStyles(
				[
					'#header_in.fix_width, ' .
					'div.ve-header-type2 .header_nav_container' => ['max-width'], // ".div" selector is here to prioritize "header-width" variable over "page-width" variable
				],
				'--header-width, var(--page-width)',
				$header_width
			);
		}

		return $this->css->printCss($this->header_css, 'header_style', $this->edit_mode);
	}

	function header_menu()
	{
		global $mwContainer;
		$head_set = $mwContainer->list['headers'][$this->header_setting['appearance']];
		$menu = $this->header_setting['menu'] ?? '';
		?>

		<a href="#" id="mobile_nav">
			<div class="mobile_nav_inner">
				<span><?php echo __('MENU', 'cms_ve'); ?></span>
				<svg role="img">
					<use xlink:href="<?php echo MW_UI_ICONS_DEF; ?>#icon-menu"></use>
				</svg>
			</div>
		</a>
		<?php
		$menu_class = '';
		if ($head_set['type'] == '2') {
			echo '<div class="header_nav_fullwidth_container">';
			$menu_class = 'fix_width';
		}
		echo '<div class="header_nav_container">';

		if (($menu !== null && wp_get_nav_menu_items($menu)) || $this->edit_mode) {
			?>

			<nav>
				<?php
				echo '<div id="mobile_nav_close">' . mw_icon('icon-x') . '</div>';
				echo '<div class="mw_header_menu_container">';
				echo '<div class="mw_header_menu_wrap">';
				if (isset($menu) && wp_get_nav_menu_items($menu)) {
					wp_nav_menu(['menu' => $menu, 'after' => '<span></span>', 'container' => false]);
				}
				echo '</div>';

				$rightMenu = $this->headerIcons();
				$rightMenu .= $this->headerButtons();

				if ($rightMenu) {
					echo '<div class="mw_header_right_menu">';
					echo $rightMenu;
					echo '</div>';
				}

				?>
				</div>

			</nav>

			<?php
		} /*else if ($this->edit_mode) {
		?>
		<div class="admin_feature add_menu_container">
		<a class="ve_add_menu" data-location="site_header_nav"
		data-modul="<?php echo $this->modul_type; ?>"
		href="#"><?php echo __('Přidat menu', 'cms_ve'); ?></a>
		</div>
		<?php
		}*/

		echo '</div>';
		if ($head_set['type'] == '2') {
			echo '</div>';
		}
	}

	function headerIcons(): string
	{
		if (isset($this->header_setting['header_icons'])) {
			$content = '<ul class="mw_header_icons">';
			foreach ($this->header_setting['header_icons'] as $hIcon) {
				$icon = new Icon($hIcon['icon']);
				$link = new Link($hIcon['link']);

				$content .= '<li>';
				$content .= $link->printLink([
						'text' => $icon->printIcon(),
				]);
				$content .= '</li>';
			}
			$content .= '</ul>';

			return $content;
		}

		return '';
	}

	function headerButtons(): string
	{
		if (isset($this->header_setting['show_primary_button']) || isset($this->header_setting['show_secondary_button'])) {
			$content = '<div class="mw_header_buttons_container">';
			if (isset($this->header_setting['show_primary_button'])) {
				$content .= Button::createButton([
						'style' => $this->header_setting['primary_button_style'],
						'text' => $this->header_setting['primary_button_text'],
						'link' => $this->header_setting['primary_button_link'],
				], $this->header_css, 'mw_head_primary_button', '.mw_head_primary_button', false, $this->edit_mode);
			}
			if (isset($this->header_setting['show_secondary_button'])) {
				$content .= Button::createButton([
						'style' => $this->header_setting['secondary_button_style'],
						'text' => $this->header_setting['secondary_button_text'],
						'link' => $this->header_setting['secondary_button_link'],
				], $this->header_css, 'mw_head_secondary_button', '.mw_head_secondary_button', false, $this->edit_mode);
			}
			$content .= '</div>';

			return $content;
		}

		return '';
	}

	function add_page_header_scripts()
	{
		$this->generateContent();

		// favicon
		$this->printFavicon();

		// meta description, robots, keywords
		$seo = get_option('seo_basic');
		if (!isset($seo['seo'])) {
			$this->printSeoMeta();
		}

		// facebook meta
		$foption = get_option('social_option');
		if (!isset($foption['hide_facebook'])) {
			$this->printFacebookMeta();
		}

		do_action('cms_after_facebook_meta');

		//google site verification
		if (isset($this->web_options['site_verification']) && $this->web_options['site_verification'] != '') {
			echo '<meta name="google-site-verification" content="' . esc_attr($this->web_options['site_verification']) . '"/>';
		}

		// cannonical
		if (!isset($seo['seo'])) {
			$can_url = get_permalink();
			if (is_home() && get_option('show_on_front') === 'posts') {
				$can_url = get_home_url();
			} elseif (is_home()) {
				$can_url = get_permalink(get_option('page_for_posts'));
			} elseif (is_category()) {
				global $wp_query;
				$can_url = get_category_link($wp_query->query_vars['cat']);
			} elseif (is_tag()) {
				global $wp_query;
				$can_url = get_term_link($wp_query->query_vars['tag_id']);
			} elseif (is_tax()) {
				$can_url = get_term_link(get_queried_object()->term_id);
			} elseif (is_author()) {
				global $wp_query;
				$can_url = get_author_posts_url($wp_query->query_vars['author']);
			}

			$cur_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			if ($cur_url !== $can_url) {
				echo '<link rel="canonical" href="' . $can_url . '"/>';
			}
		}

		// wp comments
		if (is_singular() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
		}

		//Add JS "ajaxurl" variable for everyone, including non-authorized users.
		echo '<script type="text/javascript">
		var ajaxurl = "' . admin_url('admin-ajax.php', 'relative') . '";
		var locale = "' . get_locale() . '";
		var template_directory_uri = "' . get_template_directory_uri() . '";
		</script>';

		// page fonts
		$page_google_fonts = false;
		$page_file_fonts = false;

		if ($this->page_type === 'blog' && (int) $this->save_post_id === 0) {
			$page_google_fonts = get_option('ve_blog_google_fonts');
			$page_file_fonts = get_option('ve_blog_file_fonts');
		} elseif ($this->page_type === 'eshop_cate') {
			$page_google_fonts = get_term_meta($this->save_post_id, 've_google_fonts', true);
			$page_file_fonts = get_term_meta($this->save_post_id, 've_file_fonts', true);
		}

		if ($page_google_fonts === false) {
			$page_google_fonts = get_post_meta($this->post_id, 've_google_fonts', true);
		}
		if ($page_file_fonts === false) {
			$page_file_fonts = get_post_meta($this->post_id, 've_file_fonts', true);
		}


		if (!$page_google_fonts || !$page_file_fonts) {
//		if ($page_fonts === false) { // TODO optimize - update only if $page_fonts === false (not [])
			$page_fonts = $this->get_layer_fonts($this->layer, []);
			if (isset($page_fonts['google']) && $page_fonts['google']) {
				update_post_meta($this->post_id, 've_google_fonts', $page_fonts['google']);
				$page_google_fonts = $page_fonts['google'];
			}
			if (isset($page_fonts['file']) && $page_fonts['file']) {
				update_post_meta($this->post_id, 've_file_fonts', $page_fonts['file']);
				$page_file_fonts = $page_fonts['file'];
			}
		}

		$this->google_fonts = $this->merge_fonts($this->google_fonts, $page_google_fonts);
		$this->file_fonts = $this->merge_fonts($this->file_fonts, $page_file_fonts);
		//classic popup fonts
		if (isset($this->popups->popups_setting['clasic_popup']) && $this->popups->popups_setting['clasic_popup']) {
			$popup_google_fonts = get_post_meta($this->popups->popups_setting['clasic_popup'], 've_google_fonts', true);
			$popup_file_fonts = get_post_meta($this->popups->popups_setting['clasic_popup'], 've_file_fonts', true);
			$this->google_fonts = $this->merge_fonts($this->google_fonts, $popup_google_fonts);
			$this->file_fonts = $this->merge_fonts($this->file_fonts, $popup_file_fonts);
		}
		//exit popup fonts
		if (isset($this->popups->popups_setting['exit_popup']) && $this->popups->popups_setting['exit_popup']) {
			$popup_google_fonts = get_post_meta($this->popups->popups_setting['exit_popup'], 've_google_fonts', true);
			$popup_file_fonts = get_post_meta($this->popups->popups_setting['exit_popup'], 've_file_fonts', true);
			$this->google_fonts = $this->merge_fonts($this->google_fonts, $popup_google_fonts);
			$this->file_fonts = $this->merge_fonts($this->file_fonts, $popup_file_fonts);
		}
		//button fonts
		foreach (mwButtonStyles()->getStyles() as $button) {
			if (isset($button['font']) && isset($button['font']['font-family'])) {
				if (isset(MW()->google_fonts[$button['font']['font-family']])) {
					$this->google_fonts[$button['font']['font-family']][$button['font']['weight']] = $button['font']['weight'];
				}
				if (isset(MW()->file_fonts[$button['font']['font-family']])) {
					$this->file_fonts[$button['font']['font-family']][$button['font']['weight']] = $button['font']['weight'];
				}
			}
		}

		// popups in blog posts
		if (is_single()) {
			global $post;
			preg_match_all('/\[(popup|content) id=(\d+)\]/', $post->post_content, $text_popups, PREG_PATTERN_ORDER);
			foreach ($text_popups[2] as $tpop) {
				$this->google_fonts = $this->merge_fonts($this->google_fonts, get_post_meta($tpop, 've_google_fonts', true));
				$this->file_fonts = $this->merge_fonts($this->file_fonts, get_post_meta($tpop, 've_file_fonts', true));
			}
			// weditor after post
			if (isset(mwBlog()->setting['content_after_post'])) {
				$this->google_fonts = $this->merge_fonts($this->google_fonts, get_post_meta(mwBlog()->setting['content_after_post'], 've_google_fonts', true));
				$this->file_fonts = $this->merge_fonts($this->file_fonts, get_post_meta(mwBlog()->setting['content_after_post'], 've_file_fonts', true));
			}
		}

		//custom header
		if (isset($this->header_setting['before_header']) && $this->header_setting['before_header']) {
			$header_google_fonts = get_post_meta($this->header_setting['before_header'], 've_google_fonts', true);
			$header_file_fonts = get_post_meta($this->header_setting['before_header'], 've_file_fonts', true);
			$this->google_fonts = $this->merge_fonts($this->google_fonts, $header_google_fonts);
			$this->file_fonts = $this->merge_fonts($this->file_fonts, $header_file_fonts);
		}

		//custom footer
		if (isset($this->footer_setting['custom_footer']) && $this->footer_setting['custom_footer']) {
			$footer_google_fonts = get_post_meta($this->footer_setting['custom_footer'], 've_google_fonts', true);
			$footer_file_fonts = get_post_meta($this->footer_setting['custom_footer'], 've_file_fonts', true);
			$this->google_fonts = $this->merge_fonts($this->google_fonts, $footer_google_fonts);
			$this->file_fonts = $this->merge_fonts($this->file_fonts, $footer_file_fonts);
		}

		// header logo font
		if (isset($this->header_setting['logo_font'])) {
			if (isset(MW()->google_fonts[$this->header_setting['logo_font']['font-family']])) {
				$this->google_fonts[$this->header_setting['logo_font']['font-family']][$this->header_setting['logo_font']['weight']] = $this->header_setting['logo_font']['weight'];
			}
			if (isset(MW()->file_fonts[$this->header_setting['logo_font']['font-family']])) {
				$this->file_fonts[$this->header_setting['logo_font']['font-family']][$this->header_setting['logo_font']['weight']] = $this->header_setting['logo_font']['weight'];
			}
		}
		// header menu font
		if (isset(MW()->google_fonts[$this->header_setting['menu_font']['font-family']])) {
			$this->google_fonts[$this->header_setting['menu_font']['font-family']][$this->header_setting['menu_font']['weight']] = $this->header_setting['menu_font']['weight'];
		}
		if (isset(MW()->file_fonts[$this->header_setting['menu_font']['font-family']])) {
			$this->file_fonts[$this->header_setting['menu_font']['font-family']][$this->header_setting['menu_font']['weight']] = $this->header_setting['menu_font']['weight'];
		}
		// page font
		if (isset(MW()->google_fonts[$this->page_setting['font']['font-family']])) {
			$this->google_fonts[$this->page_setting['font']['font-family']][$this->page_setting['font']['weight']] = $this->page_setting['font']['weight'];
		}
		if (isset(MW()->file_fonts[$this->page_setting['font']['font-family']])) {
			$this->file_fonts[$this->page_setting['font']['font-family']][$this->page_setting['font']['weight']] = $this->page_setting['font']['weight'];
		}
		// footer font
		if (isset(MW()->google_fonts[$this->footer_setting['font']['font-family']])) {
			$this->google_fonts[$this->footer_setting['font']['font-family']][$this->footer_setting['font']['weight']] = $this->footer_setting['font']['weight'];
		}
		if (isset(MW()->file_fonts[$this->footer_setting['font']['font-family']])) {
			$this->file_fonts[$this->footer_setting['font']['font-family']][$this->footer_setting['font']['weight']] = $this->footer_setting['font']['weight'];
		}
		// title font
		if (isset(MW()->google_fonts[$this->page_setting['title_font']['font-family']])) {
			$this->google_fonts[$this->page_setting['title_font']['font-family']][$this->page_setting['title_font']['weight']] = $this->page_setting['title_font']['weight'];
		}
		if (isset(MW()->file_fonts[$this->page_setting['title_font']['font-family']])) {
			$this->file_fonts[$this->page_setting['title_font']['font-family']][$this->page_setting['title_font']['weight']] = $this->page_setting['title_font']['weight'];
		}
		// subtitle font
		if (!isset($this->page_setting['subtitle_font'])) {
			$this->page_setting['subtitle_font'] = $this->page_setting['title_font'];
		}
		if (isset(MW()->google_fonts[$this->page_setting['subtitle_font']['font-family']])) {
			$this->google_fonts[$this->page_setting['subtitle_font']['font-family']][$this->page_setting['subtitle_font']['weight']] = $this->page_setting['subtitle_font']['weight'];
		}
		if (isset(MW()->file_fonts[$this->page_setting['subtitle_font']['font-family']])) {
			$this->file_fonts[$this->page_setting['subtitle_font']['font-family']][$this->page_setting['subtitle_font']['weight']] = $this->page_setting['subtitle_font']['weight'];
		}

		// global fonts / needed if local setting is set to default
		if ($this->edit_mode) {
			// title font
			if (isset(MW()->google_fonts[$this->global_page_setting['title_font']['font-family']])) {
				$this->google_fonts[$this->global_page_setting['title_font']['font-family']][$this->global_page_setting['title_font']['weight']] = $this->global_page_setting['title_font']['weight'];
			}
			if (isset(MW()->file_fonts[$this->global_page_setting['title_font']['font-family']])) {
				$this->file_fonts[$this->global_page_setting['title_font']['font-family']][$this->global_page_setting['title_font']['weight']] = $this->global_page_setting['title_font']['weight'];
			}
			// subtitle font
			if (!isset($this->global_page_setting['subtitle_font'])) {
				$this->global_page_setting['subtitle_font'] = $this->global_page_setting['title_font'];
			}
			if (isset(MW()->google_fonts[$this->global_page_setting['subtitle_font']['font-family']])) {
				$this->google_fonts[$this->global_page_setting['subtitle_font']['font-family']][$this->global_page_setting['subtitle_font']['weight']] = $this->global_page_setting['subtitle_font']['weight'];
			}
			if (isset(MW()->file_fonts[$this->global_page_setting['subtitle_font']['font-family']])) {
				$this->file_fonts[$this->global_page_setting['subtitle_font']['font-family']][$this->global_page_setting['subtitle_font']['weight']] = $this->global_page_setting['subtitle_font']['weight'];
			}
		}

		$this->printGoogleFonts($this->google_fonts);

		$all_file_fonts = MW()->file_fonts;
		if ($this->edit_mode) {
			$this->printFileFonts($all_file_fonts);

			if (!isset($_GET['window_editor'])) {
				echo $this->printBodyStyles($this->global_page_setting, 'global_body_style');
			}
		} else {
			$file_fonts_print = [];
			foreach ($this->file_fonts as $key => $weights) {
				foreach ($weights as $wkey => $weight) {
					if (isset($all_file_fonts[$key][$wkey]) && $all_file_fonts[$key][$wkey]) {
						$file_fonts_print[$key][$wkey] = $all_file_fonts[$key][$wkey];
					}
				}
			}

			$this->printFileFonts($file_fonts_print);
		}

		echo $this->printBodyStyles();
		echo $this->css->printGlobalCss($this->edit_mode);

		MwCodes()->printHeaderCodes();

		MwCodes()->printCss();
	}

	function getTitle($sep = '|')
	{
		global $post, $page, $paged;
		$title = wp_title($sep, false, 'right');

		$seo = get_option('seo_basic');
		if (!isset($seo['seo'])) {
			if ($sep) {
				$title .= stripslashes(get_bloginfo('name'));
			}
			$site_description = get_bloginfo('description', 'display');
			if ($site_description && (is_home() || is_front_page())) {
				$title .= " | $site_description";
			}
			if ($paged >= 2 || $page >= 2) {
				$title .= ' | ' . sprintf(__('Strana %s', 'wpcms'), max($paged, $page));
			}

			if (is_home()) {
				$hometitle = get_option('mw_blog_seo');
				if (isset($hometitle['home_metatitle']) && $hometitle['home_metatitle'] != '') {
					$title = esc_attr($hometitle['home_metatitle']);
				}
			} elseif (isset($post->ID) && (is_single() || is_page())) {
				$metatitle = get_post_meta($post->ID, 'page_seo', true);
				if (isset($metatitle['metatitle']) && $metatitle['metatitle'] != '') {
					$title = esc_attr($metatitle['metatitle']);
				}
			}
		}

		return $title;
	}

	function printFavicon()
	{
		if (isset($this->web_options['favicon']) && $this->web_options['favicon'] != '') {
			$imgUrl = $this->web_options['favicon'];
			$url = strpos($imgUrl, 'http://') === 0 || strpos($imgUrl, 'https://') === 0 ? $imgUrl : site_url() . $imgUrl;

			echo '<link rel="icon" type="image/png" href="' . $url . '">';
			echo '<link rel="apple-touch-icon" href="' . $url . '">';
		} else {
			echo '<link rel="icon" type="image/png" href="' . get_template_directory_uri() . '/library/visualeditor/images/mioweb_icon.png">';
			echo '<link rel="apple-touch-icon" href="' . get_template_directory_uri() . '/library/visualeditor/images/mioweb_icon.png">';
		}
	}

	function printGoogleFonts($fonts)
	{
		foreach ($fonts as $key => $val) {
			// add bold to text
			if ((isset($val['400']) || isset($val['300'])) && !isset($val['700']) && isset(MW()->google_fonts[$key]['weights']['700'])) {
				$val['700'] = '700';
			}
			// print google font link
			if ($key) {
				echo '<link id="mw_gf_' . str_replace(' ', '_', $key) . '" href="https://fonts.googleapis.com/css?family=' . str_replace(' ', '+', $key) . ':' . implode(',', $val) . '&subset=latin,latin-ext&display=swap" rel="stylesheet" type="text/css">';
			}
		}
	}

	function printFileFonts($fonts)
	{
		echo '<style>';
		foreach ($fonts as $fontName => $weights) {
			foreach ($weights as $weight => $data) {
				echo '@font-face { font-family: "' . $fontName . '"; src: url("' . $data['file'] . '"); font-style: normal; font-weight: ' . $weight . '; }';
			}
		}

		echo '</style>';
	}

	function printSeoMeta()
	{
		global $post;
		$page_meta = isset($post->ID) && (is_single() || is_page()) ? get_post_meta($post->ID, 'page_seo', true) : '';

		$blog_home_meta = get_option('mw_blog_seo');

		// description
		$metadesc = '';

		if (is_home()) {
			if (isset($blog_home_meta['home_metadesc'])) {
				$metadesc = esc_attr($blog_home_meta['home_metadesc']);
			}
		}
		if (is_category()) {
			$metadesc = strip_tags(category_description());
		} elseif (is_tax()) {
			$metadesc = strip_tags(term_description());
		} elseif (is_tag()) {
			$metadesc = strip_tags(tag_description());
		} elseif (is_author()) {
			$metadesc = strip_tags(get_the_author_meta('description'));
		} else {
			if (isset($page_meta['metadesc']) && $page_meta['metadesc'] != '') {
				$metadesc = esc_attr($page_meta['metadesc']);
			}
			if (isset($post->ID) && !$metadesc && is_single()) {
				$metadesc = strip_tags(get_the_excerpt());
			}
		}
		if ($metadesc != '') {
			echo '<meta name="description" content="' . $metadesc . '" />';
		}

		// robots
		$robots = [];
		if (is_home()) {
			if (isset($blog_home_meta['home_robots'])) {
				$robots = $blog_home_meta['home_robots'];
			}
		} elseif (isset($page_meta['robots'])) {
			$robots = $page_meta['robots'];
		}

		if (isset($robots['noindex']) || isset($robots['nofollow']) || isset($robots['noarchive'])) {
			if (isset($robots['is_saved'])) {
				unset($robots['is_saved']);
			}

			$robots_val = [];
			if (isset($robots['noindex'])) {
				$robots_val[] = 'noindex';
			}
			if (isset($robots['nofollow'])) {
				$robots_val[] = 'nofollow';
			}
			if (isset($robots['noarchive'])) {
				$robots_val[] = 'noarchive';
			}
			?>
			<meta name="robots" content="<?php echo implode(', ', $robots_val); ?>"/>
		<?php }

		// keywords
		$keywords = '';
		if (is_home()) {
			if (isset($blog_home_meta['home_metakey'])) {
				$keywords = esc_attr($blog_home_meta['home_metakey']);
			}
		} elseif (isset($page_meta['metakey']) && $page_meta['metakey'] != '') {
			$keywords = esc_attr($page_meta['metakey']);
		}

		if ($keywords != '') {
			echo '<meta name="keywords" content="' . $keywords . '" />';
		}
	}

	function printFacebookMeta()
	{
		global $post;

		$metas = '';

		$page_fac = [];
		$global_fac = get_option('social_option');
		if (isset($post->ID) && (is_single() || is_page())) {
			$page_fac = get_post_meta($post->ID, 'page_facebook', true);
		}

		if (is_category()) {
			global $wp_query;
			$can_url = get_category_link($wp_query->query_vars['cat']);
		} elseif (is_tag()) {
			global $wp_query;
			$can_url = get_term_link($wp_query->query_vars['tag_id']);
		} elseif (is_author()) {
			global $wp_query;
			$can_url = get_author_posts_url($wp_query->query_vars['author']);
		} elseif (is_tax()) {
			$can_url = get_term_link(get_queried_object()->term_id);
		} else {
			$can_url = get_permalink();
		}
		// blog home page facebook
		if (is_home()) {
			$home_blog_fac = get_option('blog_facebook');
			$page_fac = $home_blog_fac;
			$can_url = get_option('show_on_front') == 'posts' ? get_home_url() : get_permalink(get_option('page_for_posts'));
		}
		// facebook title
		$title = isset($page_fac['fac_title']) && $page_fac['fac_title'] != '' ? esc_attr($page_fac['fac_title']) : $this->getTitle('');

		// facebook image
		$ogimage = false;
		if (isset($page_fac['fac_image']) && $page_fac['fac_image'] != '') {
			$ogimage = esc_attr(home_url() . $page_fac['fac_image']);
		} elseif (isset($post->ID) && has_post_thumbnail() && (is_single() || is_page())) {
			$ogimage = wp_get_attachment_url(get_post_thumbnail_id($post->ID, 'facebook'));
		} elseif (is_category() || is_tag()) {
			if (isset(mwBlog()->top_panel['image']) && !mwBlog()->top_panel['image']->isEmpty()) {
				$ogimage = esc_attr(mwBlog()->top_panel['image']->getUrl());
			}
		} elseif (isset($global_fac['fac_img']) && $global_fac['fac_img']) {
			$ogimage = esc_attr(home_url() . $global_fac['fac_img']);
		}

		if (is_tax()) {
			$mwTerm = mwTerm::createNew(get_queried_object());
			$image = $mwTerm->getThumbnail()->getUrl();

			if ($image) {
				$ogimage = esc_attr($image);
			}
		}

		$type = is_singular() ? 'article' : 'website';

		$metas .= '<meta property="og:title" content="' . $title . '"/>';

		if (isset($ogimage) && $ogimage) {
			$metas .= '<meta property="og:image" content="' . $ogimage . '"/>';
		}
		// facebook description

		if (is_tax()) {
			$metas .= '<meta property="og:description" content="' . strip_tags(term_description()) . '"/>';
		} elseif (isset($page_fac['fac_desc']) && $page_fac['fac_desc'] != '') {
			$metas .= '<meta property="og:description" content="' . esc_attr($page_fac['fac_desc']) . '"/>';
		}
		// facebook admin id
		if (isset($global_fac['fac_admin_id']) && $global_fac['fac_admin_id']) {
			$metas .= '<meta property="fb:admins" content="' . esc_attr($global_fac['fac_admin_id']) . '"/>';
		}
		// facebook admin id
		if (isset($global_fac['fac_api']) && $global_fac['fac_api']) {
			$metas .= '<meta property="fb:app_id" content="' . esc_attr($global_fac['fac_api']) . '"/>';
		}
		// facebook url
		if ($can_url) {
			$metas .= '<meta property="og:url" content="' . $can_url . '"/>';
		}
		$metas .= '<meta property="og:site_name" content="' . get_bloginfo('name') . '"/>';
		$metas .= '<meta property="og:locale" content="' . get_locale() . '"/>';

		echo apply_filters('mw_print_facebook_atributes', $metas);
	}

	function facebook_script()
	{
		if (MwCookies()->isPermitted('marketing')) {
			$fac = get_option('social_option');
			echo '<div id="fb-root"></div>';
			echo '<script async defer crossorigin="anonymous" src="https://connect.facebook.net/' . get_locale() . '/sdk.js#xfbml=1&version=v12.0'
			. (isset($fac['fac_api']) && $fac['fac_api'] ? '&appId=' . $fac['fac_api'] : '') . '&autoLogAppEvents=1"></script>';
		}
	}

	function showFooter()
	{
		return $this->footer_setting['show'] != 'nofooter' && $this->footer_setting['show'] != 'none';
	}

	function printFooter($echo = true)
	{
		global $mwContainer, $menu;

		$content = '';

		if ($this->showFooter() && isset($mwContainer->list['footers'][$this->footer_setting['appearance']])) {
			$menu = $this->footer_setting['menu'] ?? '';

			$content .= '<div id="footer">';

			if (isset($this->footer_setting['custom_footer']) && $this->footer_setting['custom_footer']) {
				$content .= $this->weditor->create_content($this->footer_setting['custom_footer'], 'cms_footer');
			}

			if (!isset($this->footer_setting['hide_footer_end'])) {
				$footer_end_class = 'footer_' . $this->footer_setting['appearance'];

				if (isset($this->footer_setting['background_image']['cover']) && $this->footer_setting['background_image']['cover']) {
					$footer_end_class .= ' background_cover';
				}

				$content .= '<div class="footer_end ' . $footer_end_class . '">';
				$content .= '<div class="background_overlay"></div>';
				ob_start();
				load_template($mwContainer->list['footers'][$this->footer_setting['appearance']]['file'], true);
				$content .= ob_get_contents();
				ob_end_clean();
				$content .= '</div>';
			}

			$content .= $this->printFooterCss();

			if ($this->edit_mode) {
				$post_id = defined('DOING_AJAX') && DOING_AJAX ? $_POST['post_id'] : $this->post_id;
				$content .= '<a href="#" class="mw_edit_but mw_edit_option_onpage admin_feature" data-edit="footer" data-objectid="page" data-itemid="' . $post_id . '" title="' . __('Editovat patičku', 'cms_ve') . '" data-title="' . __('Patička', 'cms_ve') . '">' . mw_icon('icon-edit-2') . '</a>';
			}

			$content .= '</div>';
		} elseif ($this->edit_mode) {
			$content .= '<div id="footer"></div>';
		}

		if ($echo) {
			echo $content;
		} else {
 return $content;
		}
	}

	function printFooterCss()
	{
		$footer_css = $this->css->createCssContainer();

		$footer_css->addStyles(
			[
				'bg' => [
					'background_color' => $this->footer_setting['background_color'],
				],
				'font' => $this->footer_setting['font'],
			],
			'#footer .footer_end'
		);

		$footer_css->addBgStyle($this->footer_setting['background_image'] ?? [] ?: [], '#footer .footer_end', $this->edit_mode);

		if (isset($this->footer_setting['background_image']) && $this->footer_setting['background_image']['image'] && isset($this->footer_setting['background_image']['color_filter'])) {
			$footer_css->addStyles(
				[
					'background-color' => $this->footer_setting['background_image']['overlay_color']['rgba'],
				],
				'#footer .footer_end .background_overlay'
			);
		}

		if (isset($this->footer_setting['background_image']) && isset($this->footer_setting['background_image']['mobile_hide'])) {
			$footer_css->addMobileStyles([
				'background-image' => 'none',
			], '#footer .footer_end');
			$footer_css->addMobileStyles(['background-color' => 'transparent'], '#footer .footer_end .background_overlay');
		}

		$footer_width = '';
		if (isset($this->footer_setting['footer_width_preset'])) {
			$footer_width = $this->footer_setting['footer_width_preset'];
		}

		if ($footer_width || $this->edit_mode) {
			$footer_css->addVariableStyles(
				[
					'#footer-in, footer .row_fix_width' => ['max-width'],
				],
				'--footer-width, var(--page-width)',
				$footer_width
			);
		}

		return $this->css->printCss($footer_css, 'footer_style', $this->edit_mode);
	}

	function footer_menu($menu)
	{
		$ismenu = isset($menu) && $menu != '' && wp_get_nav_menu_object($menu) && wp_get_nav_menu_object($menu)->count ? true : false;

		if ($ismenu) {
			echo '<div id="site_footer_nav">';
			wp_nav_menu(['menu' => $menu, 'depth' => 1, 'container' => false]);
			echo '</div>';
		}
	}

	/* Scripts
	**************************************************************************** */
	function loadCodes()
	{
		global $post;

		MwCodes()->addCodesFromOption('mw_web_codes');
		// page codes
		if (is_single() || is_page()) {
			MwCodes()->addCodesFromMeta('mw_page_codes', $post);
		}
	}

	function create_background_set_class($bg_set, $css_id, $class)
	{
		$bg_class = '';
		if (isset($bg_set['color'])) {
			$this->element_css->addStyles(['background-color' => $bg_set['color']], $css_id . $class);
		}

		if (isset($bg_set['corner']) && $bg_set['corner']) {
			$bg_class .= ' mw_element_item_corners' . $bg_set['corner'];
		}
		if (isset($bg_set['shadow']) && $bg_set['shadow']) {
			$bg_class .= ' mw_element_item_shadow' . $bg_set['shadow'];
		}
		if (isset($bg_set['border']) && $bg_set['border']) {
			$bg_class .= ' mw_element_item_borders';
		}

		if (isset($bg_set['color']) && $bg_set['color']) {
			$bg_class .= Colors::isLightColor($bg_set['color']) ? ' light_color' : ' dark_color';
		}

		return $bg_class;
	}

	// @TODO make as visual component
	function create_background_set_arrow_class($bg_set, $css_id, $class = ' .mw_box_arrow .arrow')
	{
		$bg_class = '';
		if (isset($bg_set['color'])) {
			$this->element_css->addStyles(['background-color' => $bg_set['color']], $css_id . $class);
		}

		if (isset($bg_set['shadow']) && $bg_set['shadow']) {
			$bg_class .= ' mw_element_item_shadow' . $bg_set['shadow'];
		}
		if (isset($bg_set['border']) && $bg_set['border']) {
			$bg_class .= ' mw_element_item_borders';
		}

		return $bg_class;
	}

	// @TODO make as visual component
	function generate_element_items($args, $items, $added = false, $row_set = [])
	{
		$content = '';

		$defaults = [
			'style' => '1',
			'cols' => 3,
			'autocols' => false,
			'cols_type' => 'cols',
			'inside_col_type' => 'col-one',
			'slider' => false,
			'hover_content' => false,
			'hide_image' => false,
			'styles' => [
				'hover_color' => '',
				'font_title' => '',
				'font_subtitle' => '',
				'font_description' => '',
				'font_price' => '',
			],
			'background_set' => [
				'corner' => '',
				'shadow' => '',
				'color' => '#ffffff',
			],
			'cssid' => '',
			'added' => false,
			'show_price' => false,
		];

		$args = wp_parse_args($args, $defaults);

		$catalog_class = 'in_element_content mw_element_items';
		$carousel_set = '';

		if ($args['style'] == '4b') {
			$args['style'] = '4';
			$catalog_class .= ' mw_element_items_wib';
		} elseif ($args['style'] == '7b') {
			$args['style'] = '7';
			$catalog_class .= ' mw_element_items_wib';
		} elseif ($args['style'] == '1b') {
			$args['style'] = '1';
		}

		$catalog_class .= ' mw_element_items_style_' . $args['style'];

		if ($args['style'] != '4' && $args['style'] != '7') {
			$args['background_set'] = [];
		}

		if ($args['slider']) {
			$this->add_enqueue_script('ve_miocarousel_script');
			$this->add_enqueue_style('ve_miocarousel_style');

			if ($this->is_mobile) {
				$args['cols'] = 1;
			}
			if ($args['added']) {
				$content .= '<script>
                jQuery(function() {
                  mwGetIframeContent().set_miocarousel("' . $args['cssid'] . ' .miocarousel");
                });
              </script>';
			}

			$catalog_class .= ' miocarousel miocarousel_style_1';
			if ($args['slider_setting']['color_scheme']) {
				$catalog_class .= ' miocarousel_' . $args['slider_setting']['color_scheme'];
			}

			if (isset($args['slider_setting']['autoplay'])) {
				$carousel_set .= ' data-autoplay="1"';
			} else {
				$carousel_set .= ' data-autoplay="0"';
			}
			if ($args['slider_setting']['delay']) {
				$carousel_set .= ' data-duration="' . $args['slider_setting']['delay'] . '"';
			}
			if ($args['slider_setting']['speed']) {
				$carousel_set .= ' data-speed="' . $args['slider_setting']['speed'] . '"';
			}
			if ($args['slider_setting']['animation'] && $args['slider_setting']['animation'] != 'fade') {
				$carousel_set .= ' data-animation="' . $args['slider_setting']['animation'] . '"';
			}
			if (isset($args['slider_setting']['hide_navigation'])) {
				$catalog_class .= ' miocarousel_hide_nav ';
			}
		}

		if ($args['style'] == '3' || $args['style'] == '6') {
			$args['cols_type'] = 'cols';
		}
		$catalog_class .= ' cols-' . $args['cols'] . ' ' . $args['cols_type'];

		if ($args['style'] == '3') {
			$catalog_class .= ' mw_element_rows_b';
		}
		if ($args['hover_content'] && $args['style'] == '1') {
			$catalog_class .= ' mw_element_item_hover_content';
		}

		if ($args['hide_image'] && ($args['style'] == '5' || $args['style'] == '1' || $args['style'] == '2')) {
			$args['hide_image'] = false;
		}
		if (isset($args['hide_image']) && $args['hide_image']) {
			$catalog_class .= ' mw_element_item_hidden_images';
		}

		if ($args['autocols']) {
			$catalog_class .= ' mw_element_autocols';
		}

		if (!isset($args['thumb'])) {
			$args['thumb'] = 'mio_columns_c1';
			if ((isset($row_set['type']) && $row_set['type'] == 'full') || $added) {
				$args['thumb'] = 'full';
			} elseif ($this->is_mobile) {
				$args['thumb'] = 'mio_columns_c1';
			} elseif ($args['cols'] > 3) {
				$args['thumb'] = 'mio_columns_c3';
			}
		}

		$rows = array_chunk($items, $args['cols']);

		$content .= '<div class="' . $catalog_class . '" ' . $carousel_set . '>';

		if (isset($args['categories'])) {
			$cur_url = get_permalink($args['post_id']);
			$active_cat = isset($_GET['event_calendar_cat']) && $_GET['event_calendar_cat'] ? $_GET['event_calendar_cat'] : '';

			$content .= '<div class="mw_element_items_cats_container mw_vertical_menu mw_vertical_menu_center">';

			$categories = get_categories(['taxonomy' => $args['categories']]);
			$content .= '<ul>';
			$content .= '<li><a class="mw_element_item_cat ' . ($active_cat == '' ? 'active' : '') . '" title="' . __('Vše', 'cms_ve') . '" href="' . $cur_url . $args['cssid'] . '">' . __('Vše', 'cms_ve') . '</a></li>';

			foreach ($categories as $cat) {
				$content .= '<li><a class="mw_element_item_cat ' . ($active_cat == $cat->term_id ? 'active' : '') . '" title="' . $cat->name . '" href="' . add_query_arg(['event_calendar_cat' => $cat->term_id], $cur_url) . $args['cssid'] . '">' . $cat->name . '</a></li>';
			}
			$content .= '</ul>';

			// select
			$script = $this->edit_mode ? 'parent.location.href=this.value' : 'document.location.href=this.value';

			$content .= '<select class="mw_element_item_cat_select" onchange="' . $script . '">';
			$content .= '<option value="' . $cur_url . $args['cssid'] . '">' . __('Kategorie:', 'cms_ve') . ' ' . __('Vše', 'mwshop') . '</option>';
			foreach ($categories as $cat) {
				$cur = $active_cat == $cat->term_id ? true : false;
				$content .= '<option ' . ($cur ? 'selected="selected"' : '') . '" title="' . $cat->name . '" value="' . add_query_arg(['event_calendar_cat' => $cat->term_id], $cur_url) . $args['cssid'] . '">' . __('Kategorie:', 'cms_ve') . ' ' . $cat->name . '</option>';
			}
			$content .= '</select>';

			$content .= '</div>';
		}

		if (!empty($items)) {
			if ($args['slider']) {
				$content .= '<div class="miocarousel-inner">';
			}

			$i = 1;
			$el_i = 0;
			foreach ($rows as $row) {
				if ($args['slider']) {
					$row_class = 'mw_element_row slide';
					if ($i == 1) {
						$row_class .= ' active';
					}
				} else {
					$row_class = 'mw_element_row';
				}

				$content .= '<div class="' . $row_class . '">';

				foreach ($row as $item) {
					$item_args = wp_parse_args($args, $item);

					$content .= $this->generate_element_item($item_args, $el_i, $row_set);

					$el_i++;
				}

				$content .= '</div>';

				$i++;
			}

			if ($args['slider']) {
				$content .= '</div>'; //slider end
				$content .= '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
				$content .= '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
			}
		} elseif (isset($args['empty']) && $args['empty']) {
			$content .= '<div class="mw_element_items_info_box">' . $args['empty'] . '</div>';
		}

		$content .= '</div>';

		/*
		if($element['style']['style']=='3' || $element['style']['style']=='6')
		$vePage->display->element_css->addStyles(array('font'=>(isset($element['style']['font_color'])? $element['style']['font_color']:'')), $css_id." .mw_element_item");
		*/
		if ($args['cssid']) {
			$this->element_css->addStyles(['font' => $args['styles']['font_title']], $args['cssid'] . ' h3');
			$this->element_css->addStyles(['font' => ($args['styles']['font_description'] ?? '')], $args['cssid'] . ' .mw_element_item_description');
			$this->element_css->addStyles(['font' => ($args['styles']['font_price'] ?? '')], $args['cssid'] . ' .mw_element_item_price');
			$this->element_css->addStyles(['font' => ($args['styles']['font_subtitle'] ?? '')], $args['cssid'] . ' .mw_element_item_subtitle');

			if (isset($args['background_set']['color'])) {
				$this->element_css->addStyles(['background-color' => $args['background_set']['color']], $args['cssid'] . ' .mw_element_item');
			}

			if (isset($args['styles']['hover_color']) && $args['styles']['hover_color']) {
				$this->element_css->addStyles(['background-color' => $args['styles']['hover_color']], $args['cssid'] . ' .mw_element_item_image_hover');
			}
		}

		return $content;
	}

	// element item
	// @TODO make as visual component
	function generate_element_item($args, $el_i = '0', $row_set = [])
	{
		$defaults = [
			'style' => '1',
			'cols' => 3,
			//'cols_style'=>'',
			'hover_style' => '',
			'link' => '',
			'target' => false,
			'image_ratio' => '43',
			'image' => null,
			'class' => '',
			'thumb' => 'mio_columns_c3',
			'title' => '',
			'subtitle' => '',
			'description' => '',
			'price' => '',
			'edit_button' => '',
			'align' => '',
			'hide_image' => false,
			'image_hover' => false,
			'image_hover_link' => false,
			'image_hover_content' => '',
			'tags' => '',
			'img_col_size' => '',
			'show_description' => true,
			'show_link' => false,
			'link_text' => __('Více informací', 'cms_ve'),
			'background_set' => [
				'corner' => '',
				'shadow' => '',
				'color' => '#ffffff',
			],
			'empty_image' => '',
			'custom_footer' => '',
			'custom_header' => '',
			'after_image' => '',
			'open_popup' => false,
			'popup_content' => '',
			'show_price' => false,
			'footer_align_bottom' => false,
			'vertical_align' => '',
			'button_style' => [],
			'show_button' => false,
			'button_text' => __('Více informací', 'cms_ve'),
			'labels' => [],
			'inside_col_type' => 'col-one',
		];

		$args = wp_parse_args($args, $defaults);

		// back compatibility for plugins
		$img = is_string($args['image']) ? new Image(['image' => $args['image']]) : ($args['image'] ?? new Image(['image' => '']));

		\assert($img instanceof Image);

		$show_content = true;
		$show_price = $args['show_price'];

		$target = '';
		if ($args['target']) {
			$target = 'target="_blank"';
		}

		$link_atr = '';
		if ($args['open_popup']) {
			$args['link'] = '#';
			$link_atr = 'data-popup="' . $args['cssid'] . '_' . $el_i . '_mw_element_item_popup"';
		}

		if ($args['style'] == '1' || $args['style'] == '8') {
			$show_content = false;
		} elseif ($args['style'] == '2' || $args['style'] == '5') {
			$args['show_description'] = false;
			$args['show_link'] = false;
			$show_price = false;
			$args['show_button'] = false;
		}

		$tags = '';
		if ($args['tags']) {
			$tags .= 'mw_tag_item';
			foreach ($args['tags'] as $tag) {
				$tags .= ' mw_tag_item_' . $tag;
			}
		}

		$html_start_tag = 'div';
		$html_end_tag = 'div';
		if ($args['link']) {
			$html_start_tag = 'a href="' . $args['link'] . '" ' . $link_atr . ' ' . $target . ' ';
			$html_end_tag = 'a';
		}

		if ($img && $img->getPosition() && isset($args['cssid']) && $args['cssid']) {
			$this->element_css->addStyles(['object-position' => $img->getPosition()], $args['cssid'] . ' .mw_element_item_' . $el_i . ' img');
		}

		$content = '';
		$image_hover = '';
		if ($args['image_hover']) {
			$image_hover = '<' . ($args['image_hover_link'] ? $html_start_tag : 'div') . ' class="mw_element_item_image_hover"><div class="mw_element_item_image_hover_content">' . $args['image_hover_content'] . '</div></' . ($args['image_hover_link'] ? $html_end_tag : 'div') . '>';
			$html_start_tag = 'div';
			$html_end_tag = 'div';
		}

		$divisorMultiplier = $args['autocols'] && $args['inside_col_type'] !== 'col-one' ? 1 : $args['cols'];
		$image = $img->printImg([
			'size' => $args['thumb'],
			'full_row' => self::isFullWidthRow($row_set),
			'image_ratio' => $args['image_ratio'],
			//'alt' => $args['title'],
			'col_divisor' => Image::getColDivisor($args['inside_col_type']) * $divisorMultiplier,
			'empty_image_url' => $args['empty_image'],
		], '', $this->edit_mode);

		if ($args['image_ratio']) {
			$image = '<div class="mw_image_ratio mw_image_ratio_' . $args['image_ratio'] . '">' . $image . '</div>';
		}

		$class = $args['class'];
		$class .= $args['hover_style'] ? ' image_hover_' . $args['hover_style'] : ''; // hover
		$class .= $args['img_col_size'] && ($args['style'] == '6' || $args['style'] == '7') ? ' mw_element_item_image_1-' . $args['img_col_size'] : '';

		if (isset($args['background_set']['corner']) && $args['background_set']['corner']) {
			$class .= ' mw_element_item_corners' . $args['background_set']['corner'];
		}
		if (isset($args['background_set']['shadow']) && $args['background_set']['shadow']) {
			$class .= ' mw_element_item_shadow' . $args['background_set']['shadow'];
		}
		if (isset($args['background_set']['border']) && $args['background_set']['border']) {
			$class .= ' mw_element_item_borders';
		}

		if (isset($args['background_set']['color']) && $args['background_set']['color']) {
			$class .= Colors::isLightColor($args['background_set']['color']) ? ' light_color' : ' dark_color';
		}

		if ($args['open_popup']) {
			$class .= ' mw_element_item_popup';
		}

		if ($args['vertical_align']) {
			$class .= ' valign_' . $args['vertical_align'];
		}

		$class .= ' mw_element_item_' . $el_i;

		$content .= '<div class="mw_element_item ' . $class . ' col col-' . $args['cols'] . ' ' . $tags . '">';

		$labels = '';
		if (count($args['labels'])) {
			$labels .= '<div class="mw_element_item_labels">';
			foreach ($args['labels'] as $label) {
				$labels .= mwFrontComponents::textLabel($label);
			}
			$labels .= '</div>';
		}

		if (!$args['hide_image']) {
			$content .= '<div class="mw_element_item_image_container">';
			$content .= '<' . $html_start_tag . ' class="responsive_image mw_element_item_image_link">';
			$content .= $image;
			$content .= $image_hover;
			$content .= '</' . $html_end_tag . '>';
			$content .= $args['after_image'];
			$content .= $labels;
			$content .= '</div>';
		}

		if ($show_content) {
			$content .= '<div class="mw_element_item_content ' . ($args['align'] == 'center' ? 've_center' : '') . '">';
			$content .= '<div class="mw_element_item_content_in">';
			$content .= $args['custom_header'];

			// title
			$content .= '<div class="mw_element_item_title">';
			if ($args['link']) {
				$content .= '<a href="' . $args['link'] . '" ' . $link_atr . ' ' . $target . '>';
			}
			$content .= '<h3 class="' . $this->get_font_class($args['styles']['font_title']) . '">' . $args['title'] . '</h3>';
			if ($args['link']) {
				$content .= '</a>';
			}
			$content .= $this->printContentContainer($args['subtitle'], 'mw_element_item_subtitle', 'div');
			$content .= '</div>';
			// description
			if ($args['show_description']) {
				$content .= $this->printContentContainer($args['description'], 'mw_element_item_description');
			}
			// price
			if ($show_price) {
				$content .= $this->printContentContainer($args['price'], 'mw_element_item_price ' . $this->get_font_class($args['styles']['font_price']));
			}

			// link
			if ($args['show_link'] && $args['link']) {
				$content .= '<a class="mw_element_item_more lwi" href="' . $args['link'] . '" ' . $target . '>' . $args['link_text'] . ' ' . mw_icon('icon-arrow-right') . '</a>';
			}

			$content .= '</div>';
			$content .= $args['custom_footer'];

			if ($args['show_button']) {
				$but_set = [
					'style' => $args['button_style'],
					'link' => [
						'link' => $args['link'],
					],
					'text' => $args['button_text'],
				];
				if ($args['target']) {
					$but_set['link']['target'] = 1;
				}
				$content .= '<div class="mw_element_item_button">'
				. Button::createButton(
					$but_set,
					$this->element_css,
					'',
					$args['cssid'] . ' .ve_content_button',
					false,
					$this->edit_mode
				)
				. '</div>';
			}

			$content .= '</div>';
		}
		if ($args['edit_button']) {
			$content .= $args['edit_button'];
		}

		if ($args['open_popup']) {
			$this->add_enqueue_script('ve_lightbox_script');
			$this->add_enqueue_style('ve_lightbox_style');
			$content .= '<div class="cms_nodisp">
              <div id="' . str_replace('#', '', $args['cssid']) . '_' . $el_i . '_mw_element_item_popup" class="mw_element_item_popup">
                  ' . $args['popup_content'] . '
              </div>
          </div>';
		}

		$content .= '</div>';

		return $content;
	}

	public static function get_font_class(&$font, $def = 'title')
	{
		$font_class = isset($font) && isset($font['use-font']) ? $font['use-font'] . '_element_container' : $def . '_element_container';

		return $font_class;
	}

	// generate image
	// @TODO replace image functions by functions of class Images
	function generate_image($image, $class = '', $alt = '', $empty = false)
	{
		if (!$alt && isset($image['imageid'])) {
			$alt = get_post_meta($image['imageid'], '_wp_attachment_image_alt', true);
		}

		if ($class) {
			$class = 'class="' . $class . '"';
		}

		$image_url = $this->generate_image_url($image);

		$return_image = '';

		if ($image_url) {
			$return_image = '<img ' . $class . ' src="' . $image_url . '" alt="' . $alt . '" />';
		} elseif ($empty) {
			$return_image = '<img ' . $class . ' src="' . Image::getEmptyImageUrl() . '" alt="' . $alt . '" />';
		}

		return $return_image;
	}
	// @TODO replace image functions by functions of class Images
	public static function generate_image_url($image, $empty = false)
	{
		$image_url = $image['image'] ?? $image;

		if ($image_url) {
			$image_url = self::get_image_url($image_url);
		} elseif ($empty) {
			$image_url = MW_IMAGE_LIBRARY . 'misc/empty_image.jpg';
		}

		return $image_url;
	}
	// @TODO replace image functions by functions of class Images
	public static function get_image_url($image)
	{
		return substr($image, 0, 4) == 'http' ? $image : site_url() . $image;
	}

	// @TODO create class for videos
	function getVideoCode($text, $video_setting, $tag_id)
	{
		$content = '';
		$iframe_url = '';

		$controls = $video_setting['controls'];
		$autoplay = $video_setting['autoplay'];

		$mute = $video_setting['mute'] ?? 0;
		$loop = $video_setting['loop'] ?? 0;

		if (strpos($text, 'youtube')) {
			if ($controls == '1') {
				$controls .= '&autohide=1';
			}
			$url = parse_url($text);
			parse_str($url['query'], $atributes);
			$para = '?wmode=transparent&enablejsapi=1&rel=0&autoplay=' . $autoplay . '&controls=' . $controls . '&mute=' . $mute;
			if ($loop) {
				$para .= '&loop=1&playlist=' . $atributes['v'];
			}
			$iframe_url = MwCookies()->isPermitted('analytics') ? '//www.youtube.com/' : '//www.youtube-nocookie.com/';
			$iframe_url .= 'embed/' . $atributes['v'] . $para;
			$attrs = 'frameborder="0" allowfullscreen';
		} elseif (strpos($text, 'youtu.be')) {
			if ($controls == '1') {
				$controls .= '&autohide=1';
			}
			$para = '?wmode=transparent&enablejsapi=1&rel=0&autoplay=' . $autoplay . '&controls=' . $controls . '&mute=' . $mute;
			if ($loop) {
				$para .= '&loop=1';
			}
			$iframe_url = MwCookies()->isPermitted('analytics') ? '//www.youtube.com/' : '//www.youtube-nocookie.com/';
			$iframe_url .= 'embed/' . basename($text) . $para;
			$attrs = 'frameborder="0" allowfullscreen';
		} elseif (strpos($text, 'vimeo')) {
			$url = parse_url($text);
			$para = '?autoplay=' . $autoplay . '&title=0&byline=0&portrait=0';
			if (!MwCookies()->isPermitted('analytics')) {
				$para .= '&dnt=1';
			}
			$iframe_url = $url['host'] == 'player.vimeo.com' ? '//player.vimeo.com' . $url['path'] . $para : '//player.vimeo.com/video' . $url['path'] . $para;
			$attrs = 'frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen';
		}

		if ($iframe_url) {
			$content .= '<iframe id="' . $tag_id . '_video" src="' . $iframe_url . '" ' . $attrs . '></iframe>';
		}

		return $content;
	}

	function getAutoCols($cols_set, $item_count, $fixed = 3, $auto = true, $style = null)
	{
		if ($cols_set) {
			$cols = intval($cols_set);
		} else {
			$cols = $fixed;
			if ($auto && $item_count < $fixed) {
				$cols = $item_count;
			}
			if ($style == '6' || $style == '7' || $style == '7b') {
				$cols = 1;
			}
		}

		return $cols;
	}

	function printContentContainer($var, $class, $tag = 'div', $before = '', $after = '', $attr = '')
	{
		$content = '';
		$var = stripslashes($var);
		if ($var || $this->edit_mode) {
			if (!$var && $this->edit_mode) {
				$class .= ' ve_nodisp';
			}
			$content = $before . '<' . $tag . ' ' . $attr . ' class="' . $class . '">' . $var . '</' . $tag . '>' . $after;
		}

		return $content;
	}

	// @TODO use general function for multiple translations
	function get_comments_text($count)
	{
		$text = '';
		if ($count > 4 || $count == 0) {
			$text = $count . ' ' . __('Komentářů', 'cms_ve');
		} elseif ($count > 1) {
			$text = $count . ' ' . __('Komentáře', 'cms_ve');
		} else {
			$text = $count . ' ' . __('Komentář', 'cms_ve');
		}

		return $text;
	}

	/* breadcrumbs
	************************************************************************** */

	// @TODO add to components
	function mw_print_breadcrumbs($crumbs, $separator = '/')
	{
		$content = '<ul class="mw_breadcrumbs in_element_content">';
		$i = 1;
		foreach ($crumbs as $crumb) {
			$class = 'mw_breadcrumb_item mw_breadcrumb_item_' . $crumb['type'];
			$title = $crumb['title'];
			if ($i == 1) {
				$class = ' mw_breadcrumb_item_home';
				$title = mw_content_icon_set('home');
			}
			$content .= '<li class="' . $class . '">';
			if (isset($crumb['href'])) {
				$content .= '<a href="' . $crumb['href'] . '" title="' . $crumb['title'] . '">' . $title . '</a>';
			} else {
				$content .= '<span>' . $title . '</span>';
			}

			$content .= '</li>';
			if (isset($crumb['href']) && count($crumbs) > 1) {
				$content .= '<li class="mw_breadcrumb_separator"> ' . $separator . ' </li>';
			}
			$i++;
		}
		$content .= '</ul>';

		return $content;
	}


	function mw_breadcrumbs($post_id, $separator = '/', $hide_cat = false)
	{
		$post = get_post($post_id);

		$crumbs = [
			[
				'href' => $this->home_url,
				'title' => __('Domovská stránka', 'cms_ve'),
				'type' => 'home',
			],
		];

		if (is_home()) {
			if (!is_front_page()) {
				$post_id = get_option('page_for_posts');
				$post = get_post($post_id);
			} else {
				$crumbs[] = [
					'title' => __('Blog', 'cms_ve'),
					'type' => 'blog_home',
				];
			}
		}

		if ($post->post_type == 'page') {
			// Standard page
			if ($post->post_parent && $post->ID != $this->home_id) {
				// If child page, get parents
				$anc = get_post_ancestors($post->ID);

				// Parent page loop
				$ancestors = [];
				foreach ($anc as $ancestor) {
					if ($ancestor == $this->home_id) {
						break;
					}

					$ancestors[] = [
						'href' => get_permalink($ancestor),
						'title' => get_the_title($ancestor),
						'type' => 'page',
					];
				}
				// Get parents in the right order
				$ancestors = array_reverse($ancestors);
				$crumbs = array_merge($crumbs, $ancestors);
			}

			// Current page
			$crumbs[] = [
				'title' => $post->post_title,
				'type' => 'page',
			];
		} elseif ($post->post_type == 'post' && (!is_home() || !is_front_page())) {
			// Get post category info
			$category = get_the_category();
			if (!empty($category) && !$hide_cat) {
				// Get last category post is in
				$array_cats = array_values($category);
				$last_category = end($array_cats);

				// Get parent any categories and create array
				$get_cat_parents = rtrim(get_category_parents($last_category->term_id, false, ','), ',');
				$cat_parents = explode(',', $get_cat_parents);

				// Loop through parent categories and store in variable $cat_display
				foreach ($cat_parents as $parents) {
					$crumbs[] = [
						'href' => '',//get_term_link($parents),
						'title' => $parents,
						'type' => 'category',
					];
				}
			}

			$crumbs[] = [
				'title' => get_the_title($post->ID),
				'type' => 'post',
			];
		} elseif ($post->post_type == 'mwproduct') {
			$eshop_home_id = MWS()->getHomePageId();
			if (get_permalink($eshop_home_id) != get_home_url()) {
				$eshop_home = get_post($eshop_home_id);
				$crumbs[] = [
					'href' => get_permalink($eshop_home->ID),
					'title' => $eshop_home->post_title,
					'type' => 'eshop_home',
				];
			}

			// If it's a custom post type within a custom taxonomy
			$taxonomy_exists = taxonomy_exists(MWS_PRODUCT_CAT_SLUG);
			if ($taxonomy_exists && !$hide_cat) {
				$taxonomy_terms = get_the_terms($post->ID, MWS_PRODUCT_CAT_SLUG);
				if (isset($taxonomy_terms[0])) {
					$cat_id = $taxonomy_terms[0]->term_id;
					$cat_nicename = $taxonomy_terms[0]->slug;
					$cat_link = get_term_link($taxonomy_terms[0]->term_id, MWS_PRODUCT_CAT_SLUG);
					$cat_name = $taxonomy_terms[0]->name;

					$crumbs[] = [
						'href' => $cat_link,
						'title' => $cat_name,
						'type' => 'category_' . $cat_id,
					];
				}
			}

			$crumbs[] = [
				'title' => get_the_title($post->ID),
				'type' => 'mwproduct',
			];
		} elseif (is_archive() && !is_tax() && !is_category() && !is_tag()) {
			$crumbs[] = [
				'title' => post_type_archive_title('', false),
				'type' => 'archive',
			];
		} elseif (is_archive() && is_tax() && !is_category() && !is_tag()) {
			// If post is a custom post type
			$post_type = get_post_type();

			// If it is a custom post type display name and link
			if ($post_type != 'post') {
				$post_type_object = get_post_type_object($post_type);
				$post_type_archive = get_post_type_archive_link($post_type);

				$crumbs[] = [
					'href' => $post_type_archive,
					'title' => $post_type_object->labels->name,
					'type' => 'post_type_' . $post_type,
				];
			}

			$custom_tax_name = get_queried_object()->name;

			$crumbs[] = [
				'title' => $custom_tax_name,
				'type' => 'archive',
			];
		} elseif (is_category()) {
			// Category page
			$crumbs[] = [
				'title' => single_cat_title('', false),
				'type' => 'category',
			];
		} elseif (is_tag()) {
			// Tag page

			// Get tag information
			$term_id = get_query_var('tag_id');
			$taxonomy = 'post_tag';
			$args = 'include=' . $term_id;
			$terms = get_terms($taxonomy, $args);
			$get_term_id = $terms[0]->term_id;
			$get_term_slug = $terms[0]->slug;
			$get_term_name = $terms[0]->name;

			// Display the tag name
			$crumbs[] = [
				'title' => $get_term_name,
				'type' => 'tag',
			];
		}

		$crumbs = apply_filters('mw_breadcrumb_items', $crumbs);

		return $this->mw_print_breadcrumbs($crumbs, $separator);
	}

	/** @deprecated */
	function create_button($setting, $css_id, $class = '', $attrs = '', $added = false, $edit_mode = false)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated. Use static method ' . Button::class . '::createButton instead', E_USER_DEPRECATED);

		$setting['attrs'] = $attrs;

		return Button::createButton($setting, $this->element_css, $class, $css_id, $added, $edit_mode);
	}

	/** @deprecated */
	function shift_color($color, $coef = 0.8, $torgba = false)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated. Use static method ' . Colors::class . '::shiftColor instead', E_USER_DEPRECATED);

		return Colors::shiftColor($color, $coef, $torgba);
	}

	/** @deprecated */
	function is_light_color($color, $for_transparent = true)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated. Use static method ' . Colors::class . '::isLightColor instead', E_USER_DEPRECATED);

		return Colors::isLightColor($color, $for_transparent);
	}

}
