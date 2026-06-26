<?php

use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\Button;

global $vePage;
$mwRestaurantSubmodule = new mwRestaurantSubmodule();

class mwRestaurantSubmodule
{

	function __construct()
	{
		add_action('wp_enqueue_scripts', [$this, 'load_scripts'], 2);
		add_action('admin_menu', [$this, 'modify_menu_page'], 200);

		add_filter('rtb_booking_form_fields', [$this, 'booking_form_fields'], 10, 3);
		add_filter('rtb_booking_form_submit_button', [$this, 'booking_form_button'], 10, 1);
		add_filter('rtb_settings_page', [$this, 'modify_plugin_setting_array'], 10, 1);
		add_filter('rtb_defaults', [$this, 'modify_plugin_defaults'], 10, 1);

		//add_filter('rtb-setting-booking-page', array($this, 'modify_setting_page'), 10, 1);
	}

	function modify_plugin_defaults($defaults)
	{
		//print_r($defaults);
		$defaults['date_format'] = 'd.m. yyyy';
		$defaults['time-format'] = 'H:i';

		return $defaults;
	}

	function modify_setting_page($set)
	{
		global $post;

		$set = $post->ID;

		return $set;
	}

	function modify_menu_page()
	{
		remove_submenu_page('rtb-bookings', 'rtb-addons');
	}

	function load_scripts()
	{
		$script_version = filemtime(get_template_directory() . '/style.css');
		wp_register_style('mw_restaurant_submodule_style', get_bloginfo('template_url') . '/library/visualeditor/lib/submodules/restaurant/restaurant.css', [], $script_version);

		if (current_user_can('edit_pages')) {
			wp_enqueue_style('mw_restaurant_submodule_style');
			rtb_enqueue_assets();
		}
	}

	function booking_form_fields($fields, $request, $args)
	{
		$classes = ['ve_form_text', 've_form_field'];
		if (isset($args['light_dark_color'])) {
			$classes[] = $args['light_dark_color'];
		}

		foreach ($fields as $f_key => $f_val) {
			foreach ($fields[$f_key]['fields'] as $key => $val) {
				if (!isset($fields[$f_key]['fields'][$key]['callback_args'])) {
					$fields[$f_key]['fields'][$key]['callback_args'] = [
						'classes' => $classes,
					];
				} else {
					$fields[$f_key]['fields'][$key]['callback_args']['classes'] = $classes;
				}
			}
		}

		if (isset($args['button'])) {
			$fields['button'] = [
				'legend' => '',
				'fields' => [
					'button' => [
						'title' => '',
						'request_input' => '',
						'callback' => 'mw_rtb_print_form_button',
						'callback_args' => [
							'style' => $args['button']['style'] ?? '',
							'css_id' => $args['css_id'] ?? '',
						],
					],
				],

			];
		}

		return $fields;
	}

	function booking_form_button($button)
	{
		$button = '';

		return $button;
	}

	function modify_plugin_setting_array($sap)
	{
		//print_r($sap);

		return $sap;
	}
}

function mw_rtb_print_form_button($slug, $title, $value, $args = [])
{
	global $vePage;

	//$type = empty( $args['input_type'] ) ? 'text' : esc_attr( $args['input_type'] );
	//$class = isset( $args['class'] ) ? $args['class'] : 've_content_button';

	$but_set = [
		'style' => $args['style'] ?? [],
		'text' => __('Request Booking', 'restaurant-reservations'),
		'tag' => 'button',
		'attrs' => 'type="submit"',
	];

	echo Button::createButton(
		$but_set,
		$vePage->display->element_css,
		'',
		'.ve_content_button',
		false,
		false
	);

	//echo '<button class="'.$class.'" type="submit"><span class="ve_but_text">'.__( 'Request Booking', 'restaurant-reservations' ).'</span></button>';
}

$vePage->add_elements([
	'table_reservation' => [
		'name' => __('Rezervace stolu', 'cms_ve'),
		'description' => __('Pomocí tohoto elementu můžete na stránky vkládat vlastní HTML nebo Javascript kódy.', 'cms_ve'),
		'tab_setting' => [
			[
				'id' => 'appearance',
				'name' => __('Vzhled', 'cms_ve'),
				'setting' => [
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'form-look',
								'title' => __('Vzhled formulářových polí', 'cms_ve'),
								'type' => 'imageselect',
								'content' => '1',
								'options' => [
									'1' => VS_DIR . 'images/image_select/forminput1.png',
									'2' => VS_DIR . 'images/image_select/forminput2.png',
									'3' => VS_DIR . 'images/image_select/forminput3.png',
									'4' => VS_DIR . 'images/image_select/forminput4.png',
									'5' => VS_DIR . 'images/image_select/forminput5.png',
								],
								'onedit' => [
									'action' => 'change_class',
									'target' => ' .in_element_content',
									'class' => 've_form_input_style_',
								],
							],
							[
								'id' => 'background',
								'title' => __('Barva formulářových polí', 'cms_ve'),
								'type' => 'color',
								'group' => 'input',
								'content' => '#eeeeee',
								'onedit' => [
									'action' => 'change_rform_background_color',
									'target' => ' .ve_form_field',
								],
							],
							[
								'name' => __('Zakulacení rohů', 'cms_ve'),
								'id' => 'corners',
								'type' => 'imageoption',
								'options' => [
									'sharp' => [
										'icon' => 'sharp_corner',
										'text' => __('Ostré', 'cms_ve'),
									],
									'rounded' => [
										'icon' => 'rounded_corner',
										'text' => __('Zakulacené', 'cms_ve'),
									],
									'round' => [
										'icon' => 'round_corner',
										'text' => __('Kulaté', 'cms_ve'),
									],
								],
								'onedit' => [
									'action' => 'imageoption',
									'class' => 've_form_corners_',
									'target' => ' .in_element_content',
								],
								'content' => 'sharp',
							],
							/*
							array(
								'id'=>'form-font',
								'title'=>__('Písmo formulářových polí','cms_ve'),
								'type'=>'font',
								'group'=>'input',
								'content'=>array(
									'font-size'=>'',
									//'color'=>'',
								),
								'setting'=>array(
									'max_font_size'=>'25',
								),
								'onedit'=>array(
									'action'=>'change_font',
									'target'=>' .ve_form_field input'
								)
							), */
						],
					],
					[
						'type' => 'group',
						'class' => 'mw_visual_group',
						'setting' => [
							[
								'id' => 'button',
								'title' => __('Styl tlačítka', 'cms_ve'),
								'type' => 'button',
								'onedit' => [
									'action' => 'change_button',
									'target' => ' .ve_content_button',
								],
							],
						],
					],
				],
			],
		],
	],
], 'basic');

function ve_element_table_reservation($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;
	wp_enqueue_style('mw_restaurant_submodule_style');

	$vePage->display->element_css->addStyles(
		[
			//'font'=>$element['style']['form-font'],
			'background-color' => $element['style']['background'],
		],
		$css_id . ' input, ' . $css_id . ' select, ' . $css_id . ' textarea'
	);

	$content = '<div class="in_element_content in_element_content_table_reservation ve_content_form ' . (isset($element['style']['corners']) ? 've_form_corners_' . $element['style']['corners'] : '') . ' ve_form_input_style_' . $element['style']['form-look'] . '">';

	$args = [
		'button' => [
			'style' => $element['style']['button'] ?? [],
		],
		'css_id' => $css_id,
		'light_dark_color' => Colors::isLightColor($element['style']['background']) ? ' input_light_color' : ' input_dark_color',
	];
	$content .= rtb_print_booking_form($args);

	$content .= '</div>';

	return $content;
}
