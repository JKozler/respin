<?php
$shortcodes = new MioWebShortcodes();

class MioWebShortcodes
{

	function __construct()
	{
		$edit_mode = (bool) current_user_can('edit_pages');

		add_action('init', [$this, 'register_shortcodes']);
		add_action('wp_ajax_save_shortcode_setting', [$this, 'save_shortcode_setting']);

		if ($edit_mode) {
			add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);
		}
	}

	function register_shortcodes()
	{
		global $vePage;
		if ($vePage->edit_mode) {
			add_filter('mce_external_plugins', [$this, 'mw_add_buttons']);
			add_filter('mce_buttons', [$this, 'mw_register_buttons']);
			add_filter('mce_external_languages', [$this, 'locale_tinymce_plugin']);
		}
		foreach ($vePage->shortcodes as $key => $val) {
			add_shortcode($key, [$this, 'print_shortcode_' . $key]);
		}
	}

	function load_admin_scripts()
	{
		wp_enqueue_script('cms_lightbox_script');
		wp_enqueue_style('cms_lightbox_style');
	}

	function mw_add_buttons($plugin_array)
	{
		$plugin_array['mwshortcodes'] = get_template_directory_uri() . '/library/visualeditor/lib/shortcodes/shortcodes.v2.js';

		return $plugin_array;
	}

	function mw_register_buttons($buttons)
	{
		array_push($buttons, 'mw_addshortcode');

		return $buttons;
	}

	function save_shortcode_setting()
	{
		global $vePage;

		$type = $_POST['element_type'];

		$attrs = '';
		if (isset($_POST['ve_style']) && is_array($_POST['ve_style'])) {
			foreach ($_POST['ve_style'] as $key => $set) {
				if (is_array($set)) {
					foreach ($set as $subkey => $subset) {
						if ($subset) {
							$attrs .= ' ' . $subkey . '=1';
						}
					}
				} elseif ($set) {
					$attrs .= ' ' . $key . '=' . $set;
				}
			}
		}
		if (isset($vePage->shortcodes[$type]['type']) && $vePage->shortcodes[$type]['type'] == 'text') {
			$text = $_POST['text'] ?: __('Váš text', 'cms_ve');
		} else {
			$text = '';
		}

		echo '[' . $type . $attrs . ']' . $text . '[/' . $type . ']';

		die();
	}

	function locale_tinymce_plugin($locales)
	{
		$locales['mwshortcodes'] = plugin_dir_path(__FILE__) . 'shortcode_langs.php';

		return $locales;
	}



	// writeshortcodes
	// ************************************************************************************************************

	function print_shortcode_popup($atts, $text = null)
	{
		global $vePage;

		extract(shortcode_atts([
			'id' => '',
		], $atts));

		$content = '';

		if ($id && get_post($id)) {
			//$vePage->display->popups->popups_onpage[$id] = 1;
			//$vePage->display->popups->get_popup_to_content($id);
			$vePage->display->popups->footer_code .= $vePage->display->popups->create_popup($id);

			$content .= '<a href="#" class="open_mw_popup" data-id="' . $id . '" href="#">' . $text . '</a>';

			//$content.='<script type="text/javascript"> jQuery(document).ready(function($) { $(".open_text_popup_'.$id.'").click(function(){ ve_show_popup('.$id.'); return false; }); });</script>';
		} else {
			$content .= $text;
		}

		return $content;
	}

	function print_shortcode_box($atts, $text = null)
	{
		global $vePage;
		extract(shortcode_atts([
			'background' => '',
			'color' => '',
		], $atts));

		if (substr($text, 0, 4) == '</p>') {
			$text = substr($text, 4);
		}

		$style = '';
		if ($color) {
			$style .= 'color:' . $color . ';';
		}
		if ($background) {
			$style .= 'background:' . $background . ';';
		}
		if ($style) {
			$style = 'style="' . $style . '"';
		}

		$content = '<div class="mw_text_box" ' . $style . '>' . $text . '</div>';

		return $content;
	}

	function print_shortcode_mwvideo($atts)
	{
		global $vePage;

		extract(shortcode_atts([
			'url' => '',
			'autoplay' => 0,
			'showinfo' => 0,
			'hide_control' => 0,
			'rel' => 0,
		], $atts));

		$setting = [];
		if ($autoplay) {
			$setting['autoplay'] = 1;
		}
		if ($showinfo) {
			$setting['showinfo'] = 1;
		}
		if ($hide_control) {
			$setting['hide_control'] = 1;
		}
		if ($rel) {
			$setting['rel'] = 1;
		}

		$set = [
			'style' => [
				'content' => $url,
				'code' => '',
				'setting' => $setting,
			],
		];

		$content = ve_element_video($set, '#shortcode_video', '', false, false, [], 'col-one');

		return $content;
	}

	function print_shortcode_content($atts)
	{
		if ($atts === '' || !isset($atts['id'])) {
			return '';
		}

		global $vePage;
		extract(shortcode_atts([
			'id' => $atts['id'],
		], $atts));

		$content = '';
		if ($id) {
			$layer = $vePage->display->get_layer($id, 've_elvar');
			if ($layer) {
				$var = $layer[0]['content'][0]['content'];
				$i = 0;
				foreach ($var as $content_key => $code) {
					$inside_texts = 'custom';
					$code = mwBackCompatibility::element_set($code, $inside_texts);
					$shortcode_id = 'shortcode_' . $i . '_' . $id;
					$content .= $vePage->display->generate_element($code, $shortcode_id, '', false);
					$i++;
				}
			}
		}

		return $content;
	}


}
