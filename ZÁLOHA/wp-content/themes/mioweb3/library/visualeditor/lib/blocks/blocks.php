<?php
namespace Mioweb\VisualEditor\Guttenberg;

class Blocks
{

	function __construct()
	{
		add_action('init', [$this, 'register_blocks']);
	}

	function register_blocks()
	{
		wp_register_script(
			'mw_custom_blocks_script',
			get_template_directory_uri() . '/library/visualeditor/lib/blocks/blocks.js',
			[
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-components',
			],
			'1'
		);

		register_block_type('mw-custom-blocks/custom-content', [
			'editor_script' => 'mw_custom_blocks_script',
			'render_callback' => 'mw_print_block_content',
			'attributes' => [
				'id' => [
					'default' => 1,
				],
			],
		]);
	}


	function print_shortcode_popup($atts, $text = null)
	{
		global $vePage;

		extract(shortcode_atts([
			'id' => '',
		], $atts));

		$content = '';

		if ($id && get_post($id)) {
			$vePage->display->popups->popups_onpage[$id] = 1;

			$content .= '<a class="open_mw_popup" data-id="' . $id . '" href="#">' . $text . '</a>';

			//$content.='<script type="text/javascript"> jQuery(document).ready(function($) { $(".open_text_popup_'.$id.'").click(function(){ ve_show_popup('.$id.'); return false; }); });</script>';
		} else {
			$content .= $text;
		}

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


}

function mw_print_block_content($atts)
{
	global $vePage;

	$id = $atts['id'];

	$content = '';
	if ($id) {
		$layer = $vePage->display->get_layer($id, 've_elvar');
		$var = $layer[0]['content'][0]['content'];
		$i = 0;
		foreach ($var as $content_key => $code) {
			$shortcode_id = 'shortcode_' . $i . '_' . $id;
			$content .= $vePage->display->generate_element($code, $shortcode_id, '', false);
			$i++;
		}
	}

	return $content;
}
