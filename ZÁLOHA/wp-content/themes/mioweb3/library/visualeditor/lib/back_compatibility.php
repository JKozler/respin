<?php
use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\Link;

class mwBackCompatibility
{

	public function __construct()
	{
	}

	public static function is_enable()
	{
		return (bool) get_option('mw_back_compatibility');
	}

	public static function layer_set($set, $force = false)
	{
		if (is_array($set)) {
			foreach ($set as $row_id => $row) {
				$row = self::layer_set_row($row, $force);

				$set[$row_id] = $row;
			}
		}

		return $set;
	}

	public static function layer_set_row($row, $force = false)
	{
		if (isset($row['type']) && $row['type'] == 'slider') {
			$row['style'] = self::slider_row_set($row['style'], $force);
		} elseif (isset($row['type']) && $row['type'] == 'slide') {
			$row['style'] = self::slide_row_set($row['style'], $force);
		} else {
			$row['style'] = self::row_set($row['style'], $force);
		}

		if (isset($row['content'])) {
			$inside_texts = 'custom';

			foreach ($row['content'] as $col_id => $col) {
				if (isset($col['content'])) {
					$count = 1;
					foreach ($col['content'] as $element_id => $element) {
						if (!isset($element['style']['mw30'])) {
							$last_element = $count == count($col['content']) ? true : false;

							$element = self::element_set($element, $inside_texts, $force, $row['style'], $last_element);

							if ($element['type'] == 'twocols' || $element['type'] == 'box') {
								$inside_texts = 'auto';

								foreach ($element['content'] as $subcol_id => $subcol) {
									foreach ($subcol as $subel_id => $subelement) {
										$subelement = self::element_set($subelement, $inside_texts, $force, $row['style']);
										$element['content'][$subcol_id][$subel_id] = $subelement;
									}
								}

								if ($element['type'] == 'box') {
									$element['style']['text'] = isset($element['style']['font']['color']) && $element['style']['font']['color'] ? mwBackCompatibility::get_text_lightnes($element['style']['font']['color'], $inside_texts) : $inside_texts;
								}
								$inside_texts = 'custom';
							}
							$col['content'][$element_id] = $element;

							$count++;
						}
					}
				}
				$row['content'][$col_id] = $col;
			}

			/*
			if($inside_texts!='custom') {
			$row['style']['text']=$inside_texts;
			}*/
		}

		return $row;
	}

	public static function option_set($set, $id, $force = false)
	{
		if (empty($set)) {
			$set = [];
		}

		if ($id == 've_header' || $id == 'eshop_header' || $id == 'blog_header' || $id == 'member_header') {
			$set = self::header_set($set, $force);
		} elseif ($id == 've_footer' || $id == 'eshop_footer' || $id == 'blog_footer' || $id == 'member_footer') {
			$set = self::footer_set($set, $force);
		} elseif ($id == 've_appearance' || $id == 'blog_appearance' || $id == 'member_appearance') {
			$set = self::page_set($set, 'global', $force);
		} elseif ($id == 'eshop_appearance') {
			$set = self::eshop_set($set, $force);
		}

		return $set;
	}

	public static function meta_set($set, $id, $force = false)
	{
		if (empty($set)) {
			$set = [];
		}

		if ($id == 've_header') {
			$set = self::header_set($set, $force);
		} elseif ($id == 've_footer') {
			$set = self::footer_set($set, $force);
		} elseif ($id == 've_appearance') {
			$set = self::page_set($set, 'page', $force);
		}

		return $set;
	}

	public static function header_set($set, $force = false)
	{
		if ((self::is_enable() || $force) && !isset($set['menu_style'])) {
			$set['menu_color'] = isset($set['menu_font']) ? $set['menu_font']['color'] ?? '' : '';

			if (isset($set['menu_bg'])) {
				$set['menu_bg'] = $set['menu_bg']['color1'];
			}

			if (isset($set['menu_active_color'])) {
				$set['menu_submenu_bg'] = $set['menu_active_color'];
			} else {
				$set['menu_active_color'] = '';
				$set['menu_submenu_bg'] = '';
			}

			if (isset($set['header_padding_fix']['size'])) {
				$set['header_padding_fix'] = $set['header_padding_fix']['size'];
			}

			if (isset($set['background_color'])) {
				$set['background_color'] = self::setRgbaBg($set['background_color'], true);
			}
			if (isset($set['background_color_fix'])) {
				$set['background_color_fix'] = self::setRgbaBg($set['background_color_fix'], true);
			}

			$set['menu_style'] = '1';
			if (isset($set['appearance'])) {
				if ($set['appearance'] == 'type1') {
					$set['appearance'] = 'type1';
					$set['menu_style'] = '1';
				} elseif ($set['appearance'] == 'type1c') {
					$set['appearance'] = 'type1';
					$set['menu_style'] = '2';
				} elseif ($set['appearance'] == 'type1b') {
					$set['appearance'] = 'type1c';
					$set['menu_style'] = '1';
				} elseif ($set['appearance'] == 'type2') {
					$set['appearance'] = 'type1';
					$set['menu_style'] = '3';
				} elseif ($set['appearance'] == 'type3') {
					$set['appearance'] = 'type1';
					$set['menu_style'] = '4';
				} elseif ($set['appearance'] == 'type4') {
					$set['appearance'] = 'type1';
					$set['menu_style'] = '5';
				} elseif ($set['appearance'] == 'type7') {
					$set['appearance'] = 'type6';
				} elseif ($set['appearance'] == 'type10') {
					if (isset($set['logo_setting']) && $set['logo_setting'] == 'text' && (!isset($set['logo_text']) || !$set['logo_text'])) {
						$set['appearance'] = 'type13';
					} elseif (!isset($set['logo']) || !$set['logo'] && $set['logo_setting'] != 'text') {
						$set['appearance'] = 'type13';
					}
				}
			} else {
				$set['appearance'] = 'type1';
			}

			if (isset($set['header_width']) && isset($set['header_width']['size']) && $set['header_width']['size']) {
				$set['header_width_preset'] = $set['header_width']['size'] . $set['header_width']['unit'];
			}

			if (isset($set['show'])) {
				if ($set['show'] == 'noheader') {
					$set['show'] = 'none';
				} elseif ($set['show'] == 'eshop') {
					$set['show'] = '1';
				} elseif ($set['show'] == 'blog') {
					$set['show'] = '1';
				}
			}
			if (isset($set['logo'])) {
				$set['logo'] = ['image' => $set['logo']];
			}

			if (isset($set['background_image'])) {
				$set['background_image'] = self::setBgImageCompatibility($set['background_image']);
			}
			//print_r($set);
		}

		return $set;
	}

	public static function footer_set($set, $force = false)
	{
		if ((self::is_enable() || $force) && !isset($set['footer_width_preset'])) {
			$set['footer_width_preset'] = '';
			$set['background_color'] = isset($set['background_color']) ? self::setRgbaBg($set['background_color'], true) : [];
			if (isset($set['background_image'])) {
				$set['background_image'] = self::setBgImageCompatibility($set['background_image']);
			}

			if (isset($set['footer_width']) && isset($set['footer_width']['size']) && $set['footer_width']['size']) {
				$set['footer_width_preset'] = $set['footer_width']['size'] . $set['footer_width']['unit'];
			}

			if (isset($set['show'])) {
				if ($set['show'] == 'nofooter') {
					$set['show'] = 'none';
				} elseif ($set['show'] == 'eshop') {
					$set['show'] = '1';
				} elseif ($set['show'] == 'blog') {
					$set['show'] = '1';
				}
			}
		}

		//print_r($set);
		return $set;
	}

	public static function page_set($set, $type = 'global', $force = false)
	{
		if ((self::is_enable() || $force) && (!isset($set['inverse_text_color']))) {
			global $vePage;

			if ($type == 'page') {
				if (!is_array($set)) {
					$set = [];
				}
				if (isset($set['background_slides']) && count($set['background_slides'])) {
					$newslides = [];
					foreach ($set['background_slides'] as $slide) {
						$newslides[] = $slide['image']['imageid'];
					}
					$set['background_slides'] = $newslides;
				}
				$set['video_type'] = 'custom';
				if (isset($set['video_setting']) && isset($set['video_setting']['show_mobile'])) {
					$set['show_mobile'] = '1';
				}

				if (!isset($set['background_setting'])) {
					$set['background_setting'] = 'image';
				}

				if ((isset($set['background_color']) && $set['background_color'])
					|| (isset($set['background_setting']) && $set['background_setting'] == 'video' && ((isset($set['background_video_webm']) && ($set['background_video_webm']) || (isset($set['background_video_mp4']) && $set['background_video_mp4']) || (isset($set['background_video_ogg']) && $set['background_video_ogg']))))
					|| (isset($set['background_setting']) && $set['background_setting'] == 'slider' && isset($set['background_slides']) && count($set['background_slides']))
					|| (isset($set['background_setting']) && $set['background_setting'] == 'image' && ((isset($set['background_image']['image']) && $set['background_image']['image']) || (isset($set['background_image']['pattern']) && $set['background_image']['pattern'])))
				) {
					$set['use_page_background'] = '1';
				}

				if (!isset($set['page_width_preset'])) {
					$set['page_width_preset'] = isset($set['page_width']) && $set['page_width']['size'] ? 'custom' : '';
				}

				$set['inverse_text_color'] = '';

				//$this->template_config = get_post_meta($this->post_id, 've_page_config', true);
			} elseif (!isset($set['page_width_preset'])) {
				$set['inverse_text_color'] = '#ffffff';
				$set['subtitle_font'] = $set['title_font'];
				$set['page_width_preset'] = '970px';
			}

			if (isset($set['background_image'])) {
				$set['background_image'] = self::setBgImageCompatibility($set['background_image']);
			}
		}

		return $set;
	}

	public static function eshop_set($set, $force = false)
	{
		if ((self::is_enable() || $force) && (!isset($set['font_title']))) {
			// product_thumbnail
			$set['product_thumbnail'] = isset($set['product_thumbnail']) && $set['product_thumbnail'] == 'mio_columns_c' ? 'original' : '43';

			// background_image
			if (isset($set['background_image'])) {
				$set['background_image'] = self::setBgImageCompatibility($set['background_image']);
			}

			if (!isset($set['background_color']) || $set['background_color'] == '') {
				$set['background_color'] = '#ffffff';
			}

			// product_style
			if (isset($set['product_style'])) {
				if ($set['product_style'] == '4') {
					$set['product_style'] = 'pre4';
					//$set['hide_desc']='1';
				} elseif ($set['product_style'] == '1') {
					$set['product_style'] = 'pre1';
				} elseif ($set['product_style'] == '2') {
					$set['product_style'] = 'pre2';
				} elseif ($set['product_style'] == '3') {
					$set['product_style'] = 'pre3';
				}
			} else {
				$set['product_style'] = 'pre1';
			}

			// font_title
			$set['font_title'] = [
				'use-font' => 'title',
				'font-size' => '',
				'color' => '',
			];
			// font_price
			$set['font_price'] = [
				'use-font' => 'title',
				'font-size' => '',
				'color' => '',
			];
		}

		return $set;
	}

	public static function row_set($set, $force = false)
	{
		if ((self::is_enable() || $force) && !isset($set['row_height'])) {
			global $vePage;

			if (isset($set['background_slides']) && count($set['background_slides'])) {
				$newslides = [];
				foreach ($set['background_slides'] as $slide) {
					$newslides[] = $slide['image']['imageid'];
				}
				$set['background_slides'] = $newslides;
			}
			$set['video_type'] = 'custom';
			if (isset($set['video_setting']) && isset($set['video_setting']['show_mobile'])) {
				$set['show_mobile'] = '1';
			}

			// row height and text align
			if (isset($set['height_setting'])) {
				// row full height
				if (isset($set['height_setting']['full_height'])) {
					$set['row_height'] = 'full';
				}
				// row content align
				$set['content_align'] = isset($set['height_setting']['centered_content']) ? 'center' : 'top';
				// scroll arrow
				if (isset($set['height_setting']['arrow'])) {
					$set['scroll_arrow'] = '1';
				}
				// arrow color
				if (isset($set['height_setting']['arrow_color'])) {
					$set['arrow_color'] = $set['height_setting']['arrow_color'];
				}
			} else {
				// row content align
				if (!isset($set['content_align'])) {
					$set['content_align'] = 'top';
				}
			}

			if (isset($set['min-height']) && $set['min-height'] && !isset($set['row_height'])) {
				$set['row_height'] = 'custom';
			} elseif (!isset($set['row_height'])) {
				$set['row_height'] = '';
			}

			// background color
			if (isset($set['background_color'])) {
				$set['background_color'] = self::setRgbaBg($set['background_color'], true);
			}

			// row font
			if (!isset($set['text'])) {
				if (isset($set['font']['color']) && $set['font']['color']) {
					$set['text'] = 'custom';
				} else {
					$set['text'] = isset($set['background_image']) && isset($set['background_image']['image']) && $set['background_image']['image'] ? 'custom' : 'auto';
				}
			}

			// padding
			if (!isset($set['row_padding'])) {
				$set['row_padding'] = 'custom';
				//print_r($set);
			}

			// margin
			if (!isset($set['margin_top'])) {
				$set['margin_top'] = isset($set['margin_t']) ? $set['margin_t']['size'] : '';
			}
			if (!isset($set['margin_bottom'])) {
				$set['margin_bottom'] = isset($set['margin_b']) ? $set['margin_b']['size'] : '';
			}

			// background
			if (isset($set['background_image'])) {
				$set['background_image'] = self::setBgImageCompatibility($set['background_image']);
			}

			//$set = mwBackCompatibility::templateBCrow($set);
		}

		return $set;
	}

	public static function slider_row_set($set, $force = false)
	{
		if ((self::is_enable() || $force) && !isset($set['row_height'])) {
			global $vePage;
			if ($set['slider_height']) {
				$set['row_height'] = 'custom';
				$set['min-height'] = $set['slider_height'];
			} else {
				$set['row_height'] = 'full';
			}

			if (!isset($set['style']['miocarousel_setting'])) {
				$set['miocarousel_setting']['animation'] = $set['animation'];
				$set['miocarousel_setting']['color_scheme'] = $set['color_scheme'];
				$set['miocarousel_setting']['delay'] = $set['delay'];
				$set['miocarousel_setting']['speed'] = $set['speed'];
				if (!isset($set['off_autoplay'])) {
					$set['miocarousel_setting']['autoplay'] = 1;
				}
			}
		}

		return $set;
	}

	public static function slide_row_set($set, $force = false)
	{
		if ((self::is_enable() || $force) && !isset($set['text'])) {
			// background color
			if (isset($set['background_color'])) {
				$set['background_color'] = self::setRgbaBg($set['background_color'], true);
			}

			// row font
			if (!isset($set['text'])) {
				$set['text'] = isset($set['font']['color']) && $set['font']['color'] ? 'custom' : 'auto';
			}

			if (isset($set['background_image'])) {
				$set['background_image'] = self::setBgImageCompatibility($set['background_image']);
			}
		}

		return $set;
	}

	public static function element_set($set, &$inside_texts, $force = false, $row_set = [], $last_element = false)
	{
		if ((self::is_enable() || $force) && !isset($set['style']['mw30'])) {
			global $vePage;

			$set['style']['mw30'] = '1';

			if (isset($set['config']['max_width']) && $set['config']['max_width'] == '0') {
				$set['config']['max_width'] = '';
			}

			if (!isset($set['config']['margin_bottom']) || $set['config']['margin_bottom'] === '') {
				$set['config']['margin_bottom'] = $last_element ? '' : '20';
			}

			// content
			if ($set['type'] != 'box' && $set['type'] != 'twocols') {
				if (isset($set['content']) && !isset($set['style']['content'])) {
					$set['style']['content'] = $set['content'];
					unset($set['content']);
				}
			}

			// miocarousel
			if ($set['type'] == 'image_gallery' || $set['type'] == 'catalog' || $set['type'] == 'testimonials') {
				if (!isset($set['style']['miocarousel_setting']) && isset($set['style']['animation'])) {
					$set['style']['miocarousel_setting']['animation'] = $set['style']['animation'];
					$set['style']['miocarousel_setting']['color_scheme'] = $set['style']['color_scheme'];
					$set['style']['miocarousel_setting']['delay'] = $set['style']['delay'];
					$set['style']['miocarousel_setting']['speed'] = $set['style']['speed'];
					if (!isset($set['style']['off_autoplay'])) {
						$set['style']['miocarousel_setting']['autoplay'] = 1;
					}
				}
			}

			// element text
			if ($set['type'] == 'text') {
				$set['style']['content'] = self::removeElementDivs($set['style']['content']);

				if (isset($set['style']['p-background-color']) && isset($set['style']['style']) && $set['style']['style'] == '2') {
					$set['style']['p-background-color'] = self::setRgbaBg($set['style']['p-background-color']);
					if (isset($set['style']['font']) && isset($set['style']['font']['color']) && $set['style']['font']['color'] == '') {
						$set['style']['font']['color'] = isset($row_set['font']) && isset($row_set['font']['color']) && $row_set['font']['color'] ? $row_set['font']['color'] : '#111111';
					}
				} else {
					$set['style']['p-background-color'] = ['color1' => '#e8e8e8', 'transparency1' => '1', 'rgba1' => 'rgba(232,232,232,1)'];
				}
				$inside_texts = mwBackCompatibility::get_text_lightnes($set['style']['font']['color'], $inside_texts);
			} elseif ($set['type'] == 'title') {
				// element title

				$set['style']['content'] = self::removeElementDivs($set['style']['content']);

				if (isset($set['style']['background-color']) && isset($set['style']['style']) && ($set['style']['style'] == '2' || $set['style']['style'] == '3')) {
					$set['style']['background-color'] = self::setRgbaBg($set['style']['background-color']);
					if (isset($set['style']['font']) && isset($set['style']['font']['color']) && $set['style']['font']['color'] == '') {
						$set['style']['font']['color'] = isset($row_set['font']) && isset($row_set['font']['color']) && $row_set['font']['color'] ? $row_set['font']['color'] : '#111111';
					}
				} else {
					$set['style']['background-color'] = ['color1' => '#e8e8e8', 'transparency1' => '1', 'rgba1' => 'rgba(232,232,232,1)'];
				}
			} elseif ($set['type'] == 'button') {
				// button
				//print_r($set);

				if (isset($set['style']['button'])) {
					$set['style']['button_style'] = self::setButtonCompatibility($set['style']['button']);
				}

				if (isset($set['style']['button2'])) {
					$set['style']['button_style2'] = self::setButtonCompatibility($set['style']['button2']);
				}
				//echo '<br><br>';
				//print_r($set);
			} elseif ($set['type'] == 'video') {
				// element video
				if (!isset($set['style']['video_code']) && !empty($set['style']['code'])) {
					$set['style']['video_code'] = $set['style']['code'];
					$set['style']['own_video'] = 1;
				}
				if (isset($set['style']['image']) && !isset($set['style']['image']['image'])) {
					$img = $set['style']['image'];
					$set['style']['image'] = [];
					$set['style']['image']['image'] = $img;
				}
				if (isset($set['style']['popupbutton'])) {
					$set['style']['popupbutton'] = self::setButtonCompatibility($set['style']['popupbutton']);
				}
			} elseif ($set['type'] == 'image') {
				// element image
				if (!isset($set['style']['thumb_name'])) {
					$set['style']['thumb_name'] = '';
					if (isset($set['style']['hover_color']) && !is_array($set['style']['hover_color'])) {
						$set['style']['hover_color'] = [
							'color' => $set['style']['hover_color'],
							'transparency' => 0.7,
							'rgba' => Colors::hex2rgba($set['style']['hover_color'], 0.7),
						];
					} elseif (!isset($set['style']['hover_color'])) {
						$set['style']['hover_color'] = [
							'color' => '#000000',
							'transparency' => 0.3,
							'rgba' => 'rgba(0,0,0,0.3)',
						];
					}
				}
			} elseif ($set['type'] == 'image_text') {
				// element image text
				if (!isset($set['style']['image_ratio'])) {
					$set['style']['font'] = [
						'use-font' => 'title',
						'font-size' => $set['style']['font']['font-size'],
						'color' => $set['style']['font']['color'],
					];

					if (isset($set['style']['font_text'])) {
						$set['style']['font_text'] = [
							'font-size' => $set['style']['font_text']['font-size'],
							//'color'=>$set['style']['font_text']['color'],
						];
					}

					$set['style']['content'] = self::removeElementDivs($set['style']['content']);

					if ($set['style']['content'] == '') {
						$set['style']['hide_text'] = '1';
					}
					if ($set['style']['title'] == '') {
						$set['style']['hide_title'] = '1';
					}

					if (isset($set['style']['button_link']) && Link::create_link($set['style']['button_link'])) {
						$set['style']['show_button'] = '1';
					}

					if (isset($set['style']['button'])) {
						$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
					}
				}
			} elseif ($set['type'] == 'graphic') {
				// element graphic
				if (!isset($set['style']['type'])) {
					$set['style']['type'] = $set['style']['style']['itemtype'];
					if ($set['style']['type'] == 'hr') {
						if ($set['style']['style']['item'] == '1' || $set['style']['style']['item'] == '3' || $set['style']['style']['item'] == '5') {
							$set['style']['graphic_hr'] = '1';
							$set['style']['border'] = [
								'color' => '#000000',
								'transparency' => 0.2,
								'rgba' => 'rgba(0,0,0,0.2)',
								'size' => '1',
								'style' => 'solid',
							];
							if ($set['style']['style']['item'] == '3') {
								$set['style']['border']['style'] = 'dotted';
							}
							if ($set['style']['style']['item'] == '5') {
								$set['style']['border']['style'] = 'dashed';
							}
						}
						if ($set['style']['style']['item'] == '2' || $set['style']['style']['item'] == '4' || $set['style']['style']['item'] == '6') {
							$set['style']['graphic_hr'] = '1';
							$set['style']['border'] = [
								'color' => '#ffffff',
								'transparency' => 0.2,
								'rgba' => 'rgba(255,255,255,0.2)',
								'size' => '1',
								'style' => 'solid',
							];
							if ($set['style']['style']['item'] == '4') {
								$set['style']['border']['style'] = 'dotted';
							}
							if ($set['style']['style']['item'] == '6') {
								$set['style']['border']['style'] = 'dashed';
							}
						} elseif ($set['style']['style']['item'] == '7') {
							$set['style']['graphic_hr'] = '7';
							$set['style']['hr_color'] = 'dark';
						} elseif ($set['style']['style']['item'] == '8') {
							$set['style']['graphic_hr'] = '7';
							$set['style']['hr_color'] = 'light';
						} elseif ($set['style']['style']['item'] == '9') {
							$set['style']['graphic_hr'] = '9';
							$set['style']['hr_color'] = 'dark';
						} elseif ($set['style']['style']['item'] == '10') {
							$set['style']['graphic_hr'] = '9';
							$set['style']['hr_color'] = 'light';
						} elseif ($set['style']['style']['item'] == '11') {
							$set['style']['graphic_hr'] = '11';
							$set['style']['hr_color'] = 'dark';
						} elseif ($set['style']['style']['item'] == '12') {
							$set['style']['graphic_hr'] = '11';
							$set['style']['hr_color'] = 'light';
						}
					} elseif ($set['style']['type'] == 'img') {
						$set['style']['graphic_img'] = $set['style']['style']['item'];
					}
				}
				//print_r($set);
			} elseif ($set['type'] == 'catalog') {
				// element catalog
				$set['style']['background_set'] = [
					'color' => '#ffffff',
					'shadow' => '1',
				];
				if (!isset($set['style']['hover_color'])) {
					$set['style']['hover_color'] = '#000000';
				}
				if (!isset($set['style']['hover_color']['rgba'])) {
					$set['style']['hover_color'] = [
						'color' => $set['style']['hover_color'],
						'transparency' => 0.7,
						'rgba' => Colors::hex2rgba($set['style']['hover_color'], 0.7),
					];
				}
				$set['style']['image_ratio'] = '43';
				if ($set['style']['style'] == '7') {
					$set['style']['style'] = '4';
					$set['style']['background_set'] = [
						'color' => '#ffffff',
						'shadow' => '1',
						'border' => '1',
					];
				} elseif ($set['style']['style'] == '4') {
					$set['style']['background_set'] = [
						'color' => '#ffffff',
					];
				} elseif ($set['style']['style'] == '1') {
					$set['style']['hide_content'] = '1';
				}

				if (isset($set['style']['cols_type']) && $set['style']['cols_type']) {
					if ($set['style']['cols_type'] == 's') {
						$set['style']['cols_type'] = 'smallcols';
					}
					if ($set['style']['cols_type'] == 'full') {
						$set['style']['cols_type'] = 'fullcols';
					}
				}
				//fonts
				$set['style']['font_title'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['font_title']['font-size'] ?? '',
					'color' => $set['style']['font_color'] ?? '',
				];
				$set['style']['font_description'] = [
					'font-size' => $set['style']['font_description']['font-size'] ?? '',
					'color' => $set['style']['font_color'] ?? '',
				];
				$set['style']['font_price'] = [
					'use-font' => 'subtitle',
					'font-size' => $set['style']['font_price']['font-size'] ?? '',
					'color' => $set['style']['font_price']['color'] ?? '',
				];
			} elseif ($set['type'] == 'event_calendar') {
				// element event calendar
				$set['style']['background_set'] = [
					'color' => '#ffffff',
					'shadow' => '1',
				];
				$set['style']['color'] = '#158ebf';
				$set['style']['hide_date'] = '1';
				$set['style']['image_ratio'] = '43';
				if ($set['style']['style'] == '7') {
					$set['style']['style'] = '4';
					$set['style']['background_set'] = [
						'color' => '#ffffff',
						'shadow' => '1',
						'border' => '1',
					];
				}

				//fonts
				if (isset($set['style']['font_title'])) {
					$set['style']['font_title'] = [
						'use-font' => 'title',
						'font-size' => $set['style']['font_title']['font-size'],
						'color' => $set['style']['font_color'],
					];
				}
				if (isset($set['style']['font_description'])) {
					$set['style']['font_description'] = [
						'font-size' => '',
						'color' => $set['style']['font_color'],
					];
				}
			} elseif ($set['type'] == 'recent_posts') {
				// element blog posts
				$set['style']['background_set'] = [
					'color' => '#ffffff',
					'shadow' => '1',
				];
				$set['style']['image_ratio'] = '43';
				$set['style']['hover'] = '';
				if ($set['style']['style'] == '2') {
					$set['style']['background_set'] = [
						'color' => '#ffffff',
						'border' => '1',
					];
				}

				//fonts
				$set['style']['font'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
				$set['style']['font_text'] = [
					'font-size' => $set['style']['font_text']['font-size'],
					//'color'=>$set['style']['font_text']['color'],
				];
			} elseif ($set['type'] == 'menu' && !isset($set['style']['menu_style'])) {
				// element menu
				$set['style']['menu_style'] = $set['style']['style'];
				if ($set['style']['style'] == '2') {
					$set['style']['background_set'] = [
						'color' => '#ffffff',
						'shadow' => '1',
					];
				} elseif ($set['style']['style'] == '7') {
					$set['style']['menu_style'] = '6';
				}

				//fonts
				if (isset($set['style']['title_font'])) {
					$set['style']['title_font'] = [
						'use-font' => 'subtitle',
						'font-size' => $set['style']['title_font']['font-size'],
						'color' => $set['style']['title_font']['color'],
					];
				}
				if (isset($set['style']['font'])) {
					$set['style']['font'] = [
						'font-size' => $set['style']['font']['font-size'],
						'color' => $set['style']['font']['color'],
					];
				}
			} elseif ($set['type'] == 'box') {
				// element box
				if (!isset($set['style']['padding']['size'])) {
					$size = (int) $set['style']['padding']['top'] < (int) $set['style']['padding']['left'] ? $set['style']['padding']['top'] : $set['style']['padding']['left'];
					$set['style']['padding']['size'] = $size;
				}

				if (isset($set['style']['background_color'])) {
					$set['style']['background_color'] = self::setRgbaBg($set['style']['background_color']);
				} elseif (isset($set['style']['background-color'])) {
					$set['style']['background_color'] = self::setRgbaBg(['color1' => $set['style']['background-color']]);
				}

				if (isset($set['style']['title_bg'])) {
					$set['style']['title_bg'] = self::setRgbaBg($set['style']['title_bg']);
				}

				if (isset($set['style']['title_border'])) {
					$set['style']['title_border'] = self::setRgba($set['style']['title_border']);
				}

				if (isset($set['style']['corner'])) {
					if ($set['style']['corner'] > 8) {
						$set['style']['corner'] = '2';
					} elseif ($set['style']['corner'] > 2) {
						$set['style']['corner'] = '1';
					} elseif ($set['style']['corner'] > 2) {
						$set['style']['corner'] = '';
					}
				}
				if (isset($set['style']['box-shadow']) && $set['style']['box-shadow']['size']) {
					$set['style']['shadow'] = '1';
					if ($set['style']['box-shadow']['horizontal'] || $set['style']['box-shadow']['vertical']) {
						$set['style']['shadow'] = '2';
					}
				} else {
					$set['style']['shadow'] = '';
				}

				if (isset($set['style']['background_image']['overlay_transparency'])) {
					$transparency = $set['style']['background_image']['overlay_transparency'];
					if ($transparency > 1) {
						$transparency /= 100;
					}
					$set['style']['background_image']['overlay_color'] = [
						'color' => $set['style']['background_image']['overlay_color'],
						'transparency' => $transparency,
						'rgba' => Colors::hex2rgba($set['style']['background_image']['overlay_color'], $transparency),
					];
					unset($set['style']['background_image']['overlay_transparency']);
				}

				//fonts
				$set['style']['title-font'] = isset($set['style']['title-font']) ? [
						'use-font' => 'subtitle',
						'font-size' => $set['style']['title-font']['font-size'],
						'color' => $set['style']['title-font']['color'],
						'align' => $set['style']['title-font']['align'],
				] : [
						'use-font' => 'subtitle',
						'font-size' => '',
						'color' => '',
						'align' => 'center',
				];
			} elseif ($set['type'] == 'image_gallery') {
				// element gallery
				if (isset($set['style']['thumb_name'])) {
					if ($set['style']['thumb_name'] == 'mio_columns_c') {
						$set['style']['thumb_name'] = '';
					}
					if ($set['style']['thumb_name'] == 'mio_columns_') {
						$set['style']['thumb_name'] = '43';
					}
				} else {
					$set['style']['thumb_name'] = '32';
				}
				if (isset($set['style']['cols_type']) && $set['style']['cols_type']) {
					if ($set['style']['cols_type'] == 's') {
						$set['style']['cols_type'] = 'smallcols';
					}
					if ($set['style']['cols_type'] == 'full') {
						$set['style']['cols_type'] = 'fullcols';
					}
				}
				if (isset($set['style']['hover_color']) && !is_array($set['style']['hover_color'])) {
					$set['style']['hover_color'] = [
						'color' => $set['style']['hover_color'],
						'transparency' => 0.7,
						'rgba' => Colors::hex2rgba($set['style']['hover_color'], 0.7),
					];
				}

				$set['style']['font'] = [
					'use-font' => 'text',
					'font-size' => $set['style']['font']['font-size'],
					'align' => $set['style']['font']['align'],
				];
			} elseif ($set['type'] == 'seform') {
				// form
				if ($set['style']['type'] == 'html') {
					$set = [
						'type' => 'html',
						'style' => [
							'content' => $set['style']['html'],
						],
					];
				} else {
					if (isset($set['style']['button'])) {
						$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
					}
					if (isset($set['style']['popupbutton'])) {
						$set['style']['popupbutton'] = self::setButtonCompatibility($set['style']['popupbutton']);
					}

					$set = self::setFormInputCompatibility($set);

					$set['style']['form-font'] = [
						'font-size' => $set['style']['form-font']['font-size'],
					];
				}
			} elseif ($set['type'] == 'contactform') {
				// contact form
				if (isset($set['style']['button'])) {
					$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
				}

				$set = self::setFormInputCompatibility($set, 'form-style');

				$set['style']['form-font'] = [
					'font-size' => $set['style']['form-font']['font-size'],
				];
			} elseif ($set['type'] == 'features') {
				// element features
				if (!isset($set['style']['icon_size'])) {
					global $mwContainer;

					// convert feature to icon
					$converted = false;
					if (isset($set['style']['features']) && count($set['style']['features']) == 1) {
						$feature = reset($set['style']['features']);
						if (!$feature['title'] && !$feature['text']) {
							$set['type'] = 'icon';
							$set['style']['icon'] = $feature['icon'];
							$set['style']['size'] = $feature['icon']['size'];
							$set['style']['color'] = $feature['icon']['color'];
							$set['style']['align'] = $set['style']['style'] == '2' || $set['style']['style'] == '1' ? 'center' : 'right';
							if (in_array($feature['icon']['icon'], $mwContainer->list['iconsets']['awesome']['icons'])) {
								$set['style']['icon']['icon_set'] = 'awesome';
							} else {
								$set['style']['icon']['icon_set'] = 'linecons';
								$icon = str_replace('-1', '', $feature['icon']['icon']);
								$icon = str_replace('-2', '', $icon);
								$set['style']['icon']['icon'] = $icon;
							}
							if (isset($set['style']['style'])) {
								if ($set['style']['style'] == '2' || $set['style']['style'] == '4') {
									$set['style']['icon_style'] = '2';
								} elseif ($set['style']['style'] == '5') {
									$set['style']['icon_style'] = '3';
								} else {
									$set['style']['icon_style'] = '1';
								}
							}
							$set['style']['style'] = 'beside';
							$converted = true;
						}
					}

					if (!$converted) {
						if (isset($set['style']['features'])) {
							foreach ($set['style']['features'] as $key => $f) {
								if (in_array($f['icon']['icon'], $mwContainer->list['iconsets']['awesome']['icons'])) {
									$set['style']['features'][$key]['icon']['icon_set'] = 'awesome';
								} else {
									$set['style']['features'][$key]['icon']['icon_set'] = 'linecons';
									$icon = str_replace('-1', '', $f['icon']['icon']);
									$icon = str_replace('-2', '', $icon);
									$set['style']['features'][$key]['icon']['icon'] = $icon;
								}

								$set['style']['icon_size'] = isset($f['icon']['tab']) && $f['icon']['tab'] == 'image' ? '1000' : $f['icon']['size'];

								if ($set['style']['style'] == '1' || $set['style']['style'] == '3') {
									$set['style']['background-color'] = $f['icon']['color'];
								}
							}
						}

						$set['style']['icon_style'] = '1';
						$newstyle = '1';
						if (isset($set['style']['style'])) {
							if ($set['style']['style'] == '2' || $set['style']['style'] == '4') {
								$set['style']['icon_style'] = '2';
							}
							if ($set['style']['style'] == '5') {
								$set['style']['icon_style'] = '3';
							}
							if ($set['style']['style'] == '3' || $set['style']['style'] == '4' || $set['style']['style'] == '5') {
								$newstyle = '2';
							}
						}

						$set['style']['style'] = $newstyle;

						$use_font = 'title';
						if ($set['style']['font']['weight'] == '400') {
							$use_font = 'text';
						}

						//fonts
						$set['style']['font'] = [
							'use-font' => $use_font,
							'font-size' => $set['style']['font']['font-size'],
							'color' => $set['style']['font']['color'],
						];

						if (isset($set['style']['font_text'])) {
							$use_font = 'text';
							if ($set['style']['font_text']['weight'] == '700') {
								$use_font = 'title';
							}
							//$inside_texts=mwBackCompatibility::get_text_lightnes($set['style']['font_text']['color'],$inside_texts);

							$set['style']['font_text'] = [
								'use-font' => $use_font,
								'font-size' => $set['style']['font_text']['font-size'],
								'color' => $set['style']['font_text']['color'],
							];
						}

						if (isset($set['style']['button'])) {
							$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
						}
					}
				}
			} elseif ($set['type'] == 'numbers') {
				// numbers
				//fonts
				$set['style']['number_font'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['number_font']['font-size'],
					'color' => $set['style']['number_font']['color'],
				];
				$set['style']['text_font'] = [
					'font-size' => $set['style']['text_font']['font-size'],
					'color' => $set['style']['text_font']['color'],
				];
			} elseif ($set['type'] == 'testimonials') {
				// testimonials
				//fonts
				$set['style']['font'] = [
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
				$set['style']['font-author'] = isset($set['style']['font-author']) ? [
						'font-size' => $set['style']['font-author']['font-size'],
						'color' => $set['style']['font-author']['color'],
				] : [
						'font-size' => '',
						'color' => '',
				];
			} elseif ($set['type'] == 'faq') {
				// faq
				//fonts
				$set['style']['question_font'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['question_font']['font-size'],
					'color' => $set['style']['question_font']['color'],
				];
				$set['style']['answer_font'] = [
					'font-size' => $set['style']['answer_font']['font-size'],
					'color' => $set['style']['answer_font']['color'],
				];
			} elseif ($set['type'] == 'progressbar' || $set['type'] == 'member_progress') {
				// progressbar
				//fonts
				$set['style']['font'] = [
					'font-size' => $set['style']['font']['font-size'],
				];
			} elseif ($set['type'] == 'link') {
				// link
				//fonts
				$set['style']['font'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
			} elseif ($set['type'] == 'table') {
				// table
				$set['style']['text_align'] = 'left';
				//fonts
				$set['style']['font'] = [
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
			} elseif ($set['type'] == 'wpcomments') {
				// wp comments
				if (isset($set['style']['button'])) {
					$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
				}
				$set['style']['button']['button_size'] = 'medium';
			} elseif ($set['type'] == 'peoples') {
				// peoples
				if ($set['style']['style'] == '3') {
					$set['style']['background_set'] = [
						'color' => '#ffffff',
						'border' => '1',
					];
				}
				if ($set['style']['style'] == '5') {
					$set['style']['background_set'] = [
						'color' => '#ffffff',
						'shadow' => '1',
					];
				}
				$set['style']['hover'] = '';
				$set['style']['image_ratio'] = 'original';
				//fonts
				$set['style']['font'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
				$set['style']['font_position'] = isset($set['style']['font_position']) ? [
						'font-size' => $set['style']['font_position']['font-size'],
						'color' => $set['style']['font_position']['color'],
				] : [
						'font-size' => '',
						'color' => '',
				];
				$set['style']['font_text'] = isset($set['style']['font_text']) ? [
						'font-size' => $set['style']['font_text']['font-size'],
						'color' => $set['style']['font_text']['color'],
				] : [
						'font-size' => '',
						'color' => '',
				];
			} elseif ($set['type'] == 'pricelist') {
				// pricelist
				$set['style']['cols_type'] = 'fullcols';
				$set['style']['background_set'] = [
					'color' => '#ffffff',
					'border' => '1',
				];
				$set['style']['row_font'] = [
					'font-size' => '',
				];
				if (isset($set['style']['button'])) {
					$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
				}
				$set['style']['popular_button'] = $set['style']['button'];

				if (!isset($set['style']['pricelist_type'])) {
					$set['style']['pricelist_type'] = 'cols';
				}
				if ($set['style']['pricelist_type'] == 'cols') {
					if ($set['style']['style'] == '2') {
						$set['style']['background_set'] = [
							'color' => '#ffffff',
							'border' => '1',
							'corner' => '1',
						];
					} elseif ($set['style']['style'] == '3') {
						$set['style']['cols_type'] = 'cols';
						$set['style']['background_set'] = [
							'color' => $set['style']['background_color'],
						];
					}
				} elseif ($set['style']['pricelist_type'] == 'rows') {
					if (isset($set['style']['font_title'])) {
						$set['style']['row_font'] = [
							'font-size' => $set['style']['font_title']['font-size'],
							'color' => $set['style']['font_title']['color'],
							'font-use' => 'title',
						];
					}

					if (isset($set['style']['font'])) {
						$set['style']['row_font_price'] = [
							'font-size' => $set['style']['font']['font-size'],
							'color' => $set['style']['font']['color'],
							'font-use' => 'text',
						];
					}

					if (isset($set['style']['font_description'])) {
						$set['style']['row_font_desc'] = [
							'font-size' => $set['style']['font_description']['font-size'],
						];
					}
				}

				//fonts
				$set['style']['font_title'] = isset($set['style']['font_title']) ? [
						'font-size' => $set['style']['font_title']['font-size'],
						'color' => $set['style']['font_title']['color'],
				] : [
						'font-size' => '',
						'color' => '',
				];

				$set['style']['font'] = [
					'use-font' => 'subtitle',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
			} elseif ($set['type'] == 'countdown') {
				// countdown
				if (!isset($set['style']['countdown_type']) && MW()->is_module_active('mioweb')) {
					$type = 'date';
					if (isset($set['style']['evergreen_days']) && $set['style']['evergreen_days']) {
						$type = 'campaign';
					} elseif (isset($set['style']['evergreen_hours']) && $set['style']['evergreen_hours']) {
						$type = 'campaign';
					} elseif (isset($set['style']['evergreen_minutes']) && $set['style']['evergreen_minutes']) {
						$type = 'campaign';
					}

					$set['style']['countdown_type'] = $type;
				}
				if ($set['style']['style'] == '2') {
					$set['style']['style'] = '1';
					if (!$set['style']['background-color']) {
						$set['style']['background-color'] = '#eeeeee';
					}
				} elseif ($set['style']['style'] == '4') {
					$set['style']['style'] = '3';
					if (!$set['style']['background-color']) {
						$set['style']['background-color'] = '#eeeeee';
					}
				} elseif ($set['style']['style'] == '5') {
					if ($set['style']['font']['color']) {
						$set['style']['background-color'] = $set['style']['font']['color'];
					}
				}
				$set['style']['size'] = $set['style']['font']['font-size'];
				//fonts
				$set['style']['font'] = [
					'use-font' => 'text',
				];
			} elseif ($set['type'] == 'html') {
				// html
				$set['style']['content'] = stripslashes($set['style']['content']);
			} elseif ($set['type'] == 'fac_share') {
				// share on facebook
				if (isset($set['style']['button']['icon'])) {
					$set['style']['icon'] = $set['style']['button']['icon'];
				}
				if (isset($set['style']['button'])) {
					$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
				}
			} elseif ($set['type'] == 'likebox') {
				// share on facebook
				$set['style']['width'] = '500';
			} elseif ($set['type'] == 'fapi') {
				// sale form
				if (isset($set['style']['button'])) {
					$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
				}

				$set['style']['font_text'] = [
					'font-size' => $set['style']['font_text']['font-size'],
				];
				//print_r($set);
			} elseif ($set['type'] == 'mioweb_nav') {
				// campaign menu
				//fonts
				$set['style']['font'] = [
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
			} elseif ($set['type'] == 'se_count') {
				// campaign menu
				//fonts
				$set['style']['font'] = [
					'use-font' => 'text',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
			} elseif ($set['type'] == 'campaign_date') {
				// campaign date
				//fonts
				$set['style']['font'] = [
					'use-font' => 'text',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
					'align' => $set['style']['font']['align'],
				];
			} elseif ($set['type'] == 'member_download') {
				// login form
				//print_r($set);
				$icon_style = 1;
				if (isset($set['style']['content'])) {
					foreach ($set['style']['content'] as $key => $f) {
						$icon_style = $f['icon'];
						$icon = 'download';
						switch ($f['icon']) {
							case '2':
							case '10':
								$icon = 'arrow-down';

								break;
							case '3':
							case '11':
								$icon = 'file';

								break;
							case '4':
							case '12':
								$icon = 'play';

								break;
							case '5':
							case '13':
								$icon = 'film';

								break;
							case '6':
							case '14':
								$icon = 'image';

								break;
							case '7':
							case '15':
								$icon = 'music';

								break;
							case '8':
							case '16':
								$icon = 'volume-2';

								break;
						}

						$set['style']['content'][$key]['icon'] = [
							'icon' => $icon,
							'icon_set' => 'feather',
						];
					}
				}
				$set['style']['icon_style'] = $icon_style > 8 ? '3' : '2';
				$set['style']['background_set'] = [
					'shadow' => '1',
					'color' => '#ffffff',
				];
				if ($set['style']['style'] == '1') {
					$set['style']['background_set'] = [
						'border' => '1',
						'shadow' => '2',
						'corner' => '1',
						'color' => '#ffffff',
					];
				} elseif ($set['style']['style'] == '4') {
					$set['style']['background_set'] = [
						'color' => $set['style']['color'],
					];
					$set['style']['icon_style'] = '1';
					$set['style']['color'] = '#ffffff';
				}

				$set['style']['font'] = [
					'font-size' => $set['style']['font']['font-size'],
				];
			} elseif ($set['type'] == 'member_login') {
				// login form
				if (isset($set['style']['button'])) {
					$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
				}
				$set = self::setFormInputCompatibility($set, 'input-style');

				$set['style']['form-font'] = [
					'font-size' => $set['style']['form-font']['font-size'],
				];
			} elseif ($set['type'] == 'member_regform') {
				// registration form
				if (isset($set['style']['button'])) {
					$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
				}
				$set = self::setFormInputCompatibility($set, 'input-style');

				$set['style']['form-font'] = [
					'font-size' => $set['style']['form-font']['font-size'],
				];
			} elseif ($set['type'] == 'member_subpages') {
				// member subpages
				if (!isset($set['style']['item_style'])) {
					$set['style']['background_set'] = [
						'shadow' => '2',
						'corner' => '',
						'color' => '#ffffff',
					];

					$set['style']['image_ratio'] = 'original';
					$set['style']['hover'] = '';
					$set['style']['cols_type'] = 'smallcols';
					if ($set['style']['style'] == '1') {
						$set['style']['item_style'] = (isset($set['style']['structure']) && $set['style']['structure'] == '2') || !isset($set['style']['structure']) ? '7b' : '4b';
						$set['style']['background_set'] = [
							'border' => '1',
							'shadow' => '2',
							'corner' => '1',
							'color' => '#ffffff',
						];
					} elseif ($set['style']['style'] == '2') {
						$set['style']['item_style'] = (isset($set['style']['structure']) && $set['style']['structure'] == '2') || !isset($set['style']['structure']) ? '7b' : '4b';
						$set['style']['background_set'] = [
							'shadow' => '1',
							'corner' => '',
							'color' => '#ffffff',
						];
					} elseif ($set['style']['style'] == '3' || $set['style']['style'] == '5') {
						$set['style']['item_style'] = (isset($set['style']['structure']) && $set['style']['structure'] == '2') || !isset($set['style']['structure']) ? '6' : '3';
					} elseif ($set['style']['style'] == '4') {
						$set['style']['item_style'] = (isset($set['style']['structure']) && $set['style']['structure'] == '2') || !isset($set['style']['structure']) ? '7b' : '4b';
						$set['style']['background_set'] = [
							'shadow' => '',
							'corner' => '',
							'color' => $set['style']['color'] ?? '#ffffff',
						];
					}

					if (isset($set['style']['setting'])) {
						if (isset($set['style']['setting']['hide_comments'])) {
							$set['style']['hide_comments'] = '1';
						}
						if (isset($set['style']['setting']['hide_image'])) {
							$set['style']['hide_image'] = '1';
						}
						if (isset($set['style']['setting']['hide_desc'])) {
							$set['style']['hide_desc'] = '1';
						}
					}

					if (isset($set['style']['image_size']['size'])) {
						if ($set['style']['image_size']['size'] > 40) {
							$set['style']['image_size'] = '2';
						} elseif ($set['style']['image_size']['size'] > 28) {
							$set['style']['image_size'] = '3';
						} else {
							$set['style']['image_size'] = '4';
						}
					}

					/*if(isset($set['style']['default_image']) && !is_array($set['style']['default_image']))
					$set['style']['default_image']=array('image'=>$set['style']['default_image']);*/

					$set['style']['font'] = [
						'use-font' => 'title',
						'font-size' => $set['style']['font']['font-size'] ?? '',
						'color' => $set['style']['font']['color'] ?? '',
					];
				}
			} elseif ($set['type'] == 'member_checklist') {
				// checklist
				$set['style']['corners'] = 'sharp';
				if (isset($set['style']['icon']['corner'])) {
					if ($set['style']['icon']['corner'] > 15) {
						$set['style']['corners'] = 'round';
					}
					if ($set['style']['icon']['corner'] > 3) {
						$set['style']['corners'] = 'rounded';
					}
				}
				$set['style']['checkbox_color'] = $set['style']['icon']['background'];

				$set['style']['font'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['font']['font-size'],
				];
				$set['style']['font_text'] = [
					'font-size' => $set['style']['font_text']['font-size'],
				];
			} elseif ($set['type'] == 'members_list') {
				// member sections
				$set['style']['background_set'] = [
					'color' => '#ffffff',
					'shadow' => '1',
				];
				$set['style']['hover_color'] = [
					'color' => '#000000',
					'transparency' => 0.5,
					'rgba' => 'rgba(0,0,0,0.5)',
				];
				$set['style']['image_ratio'] = 'original';
				$set['style']['hover'] = '';
				$set['style']['cols_type'] = 'cols';
				if ($set['style']['style'] == '1') {
					$set['style']['style'] = '3';
				}
				//fonts
				$set['style']['text_align'] = $set['style']['font']['align'];
				$set['style']['font'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
			} elseif ($set['type'] == 'member_users') {
				// member users
				//fonts
				$set['style']['font_title'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['font_title']['font-size'],
					'color' => $set['style']['font_title']['color'],
				];

				$set['style']['font_description'] = isset($set['style']['font']) ? [
						'font-size' => $set['style']['font']['font-size'],
						'color' => $set['style']['font']['color'],
				] : [
						'font-size' => '',
						'color' => '',
				];

				$set['style']['hover_color'] = [
					'rgba' => 'rgba(33,158,209,0.85)',
					'color' => '#219ed1',
					'transparency' => '0.85',
				];
			} elseif ($set['type'] == 'member_news') {
				// member news
				//fonts
				$set['style']['font_title'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['font_title']['font-size'],
					'color' => $set['style']['font_title']['color'],
				];
				$set['style']['font'] = [
					'font-size' => $set['style']['font']['font-size'],
				];
			} elseif ($set['type'] == 'pay_button') {
				// buy button
				if (isset($set['style']['button'])) {
					$set['style']['button'] = self::setButtonCompatibility($set['style']['button']);
				}
			} elseif ($set['type'] == 'product_price') {
				// product price
				//fonts
				$set['style']['align'] = $set['style']['font']['align'];
				$set['style']['font'] = [
					'use-font' => 'text',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
			} elseif ($set['type'] == 'product_list') {
				// product price
				// product_style
				if (isset($set['style']['product_style'])) {
					if ($set['style']['product_style'] == '1') {
						$set['style']['product_style'] = '3';
					} elseif ($set['style']['product_style'] == '2') {
						$set['style']['product_style'] = '6';
					} elseif ($set['style']['product_style'] == '3') {
						$set['style']['product_style'] = '3';
						$set['style']['hide_buy'] = '1';
					}
				} else {
					$set['style']['product_style'] = '3';
				}

				if (isset($set['style']['font']['color']) && $set['style']['font']['color']) {
					$set['style']['font_title'] = [
						'use-font' => 'title',
						'font-size' => '',
						'color' => $set['style']['font']['color'],
					];
					$set['style']['font_description'] = [
						'font-size' => '',
						'color' => $set['style']['font']['color'],
					];
					$set['style']['font_price'] = [
						'font-size' => '',
						'color' => $set['style']['font']['color'],
					];
				}
			} elseif ($set['type'] == 'eshop_category_list') {
				// eshop categories
				$set['style']['background_set'] = [
					'color' => '#ffffff',
					'shadow' => '1',
				];
				$set['style']['hover_color'] = [
					'color' => '#000000',
					'transparency' => 0.5,
					'rgba' => 'rgba(0,0,0,0.5)',
				];
				$set['style']['image_ratio'] = '43';
				$set['style']['hover'] = '';
				$set['style']['cols_type'] = 'cols';
				if ($set['style']['style'] == '1') {
					$set['style']['style'] = '1b';
				}
				//fonts
				$set['style']['text_align'] = $set['style']['font']['align'];
				$set['style']['vmenu_font'] = [
					'use-font' => 'text',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
				$set['style']['font'] = [
					'use-font' => 'title',
					'font-size' => $set['style']['font']['font-size'],
					'color' => $set['style']['font']['color'],
				];
			}

			/*
			// back compatibility
			if($element['type']=='bullets') {

			if(!isset($element['style']['style'])) {
			if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='1') || ($element['style']['type']=='image' && $element['style']['style_image']=='1')) {
			$element['style']['style']='2';
			$element['style']['size']='40';
			$element['style']['space']='30';
			$element['style']['title_font']['font-size']='35';
			}
			else if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='2') || ($element['style']['type']=='image' && $element['style']['style_image']=='2')) {
			$element['style']['style']='2';
			$element['style']['size']='20';
			$element['style']['space']='15';
			}
			else if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='3') || ($element['style']['type']=='image' && $element['style']['style_image']=='3')) {
			$element['style']['style']='1';
			$element['style']['size']='40';
			$element['style']['space']='30';
			$element['style']['title_font']['font-size']='35';
			}
			else if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='4') || ($element['style']['type']=='image' && $element['style']['style_image']=='4')) {
			$element['style']['style']='1';
			$element['style']['size']='20';
			$element['style']['space']='15';
			}

			if($element['style']['custom_image']['image']) $element['style']['type']='own_image';

			if($element['style']['icon']=='1') {
			$element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/library/visualeditor/images/icons/right2.svg", true);
			$element['style']['bullet_icon']['icon']='right2';
			}
			else if($element['style']['icon']=='2') {
			$element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/library/visualeditor/images/icons/check1.svg", true);
			$element['style']['bullet_icon']['icon']='check1';
			}
			else if($element['style']['icon']=='3') {
			$element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/library/visualeditor/images/icons/right1.svg", true);
			$element['style']['bullet_icon']['icon']='right1';
			}
			else if($element['style']['icon']=='4') {
			$element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/library/visualeditor/images/icons/right3.svg", true);
			$element['style']['bullet_icon']['icon']='right3';
			}
			}

			}
			// back compatibility end
			*/

			//$set = mwBackCompatibility::templateBCel($set);
		}

		return $set;
	}


	public static function setBgImageCompatibility($set)
	{
		global $vePage;

		if (isset($set['fixed'])) {
			$set['efect'] = 'fixed';
		}
		if (!isset($set['efect'])) {
			$set['efect'] = '';
		}
		if (isset($set['position'])) {
			if ($set['position'] == 'center center') {
				$set['position'] = '50% 50%';
			}
			if ($set['position'] == 'left top') {
				$set['position'] = '0% 0%';
			}
			if ($set['position'] == 'center top') {
				$set['position'] = '50% 0%';
			}
			if ($set['position'] == 'right top') {
				$set['position'] = '100% 0%';
			}
			if ($set['position'] == 'right center') {
				$set['position'] = '100% 50%';
			}
			if ($set['position'] == 'left center') {
				$set['position'] = '0% 50%';
			}
			if ($set['position'] == 'left bottom') {
				$set['position'] = '0% 100%';
			}
			if ($set['position'] == 'center bottom') {
				$set['position'] = '50% 100%';
			}
			if ($set['position'] == 'right bottom') {
				$set['position'] = '100% 100%';
			}
		}

		if (isset($set['overlay_transparency'])) {
			$transparency = $set['overlay_transparency'];
			if ($transparency > 1) {
				$transparency /= 100;
			}
			$set['overlay_color'] = [
				'color' => $set['overlay_color'],
				'transparency' => $transparency,
				'rgba' => Colors::hex2rgba($set['overlay_color'], $transparency),
			];
			unset($set['overlay_transparency']);
		} elseif (!isset($set['overlay_color']) || !$set['overlay_color']['color']) {
			$set['overlay_color'] = [
				'color' => '#000000',
				'transparency' => '0.5',
				'rgba' => 'rgba(0,0,0,0.5)',
			];
		}

		return $set;
	}

	public static function setFormInputCompatibility($set, $name = 'form-look')
	{
		if ($set['style'][$name] == '11') {
			$set['style'][$name] = 1;
			$set['style']['corners'] = 'round';
		} elseif ($set['style'][$name] > 5) {
			$set['style'][$name] -= 5;
			$set['style']['corners'] = 'rounded';
		}

		return $set;
	}

	public static function setButtonCompatibility($button)
	{
		if (!isset($button['button_size'])) {
			$custom_button = [
				'style' => $button['style'],
				'background_color' => self::setRgbaBg($button['background_color'], true),
				'font' => [
					'font-family' => $button['font']['font-family'],
					'weight' => $button['font']['weight'],
				],
				'font-color' => $button['font']['color'],
				'corner' => $button['corner'] ?? '',
				'corner_size' => $button['corner'] ?? '',
				'height_padding' => $button['height_padding'] ?? '0.5',
				'width_padding' => $button['width_padding'] ?? '1.2',
				'border-color' => $button['border-color'] ?? '',

				'hover_effect' => $button['hover_effect'] ?? '',
				'hover_color' => isset($button['hover_color']) ? self::setRgbaBg($button['hover_color'], true) : [],
				'hover_font_color' => $button['hover_font_color'] ?? [],
				'border_hover-color' => $button['border_hover-color'] ?? [],
			];

			$button = [
				'style' => 'custom_button',
				'button_size' => 'custom',
				'custom_size' => $button['font']['font-size'],
				'custom_setting' => $custom_button,
			];
		}

		//$button=mwBackCompatibility::defaultButtonForTemplate($button);

		//print_r($button);
		return $button;
	}

	public static function setRgbaBg($color, $gradient = false)
	{
		global $vePage;
		if (!isset($color['rgba1']) && isset($color['color1'])) {
			$transparency = $color['transparency'] ?? 1;
			if ($transparency >= 100) {
				$transparency /= 100;
			}
			if ($transparency > 1) {
				$transparency /= 100;
			}
			unset($color['transparency']);
			$color['transparency1'] = $transparency;
			$color['rgba1'] = $color['color1'] ? Colors::hex2rgba($color['color1'], $transparency) : '';
			if ($gradient) {
				$color['transparency2'] = $transparency;
				if (!isset($color['color2'])) {
					$color['color2'] = '';
				}
				if ($color['color2']) {
					$color['rgba2'] = Colors::hex2rgba($color['color2'], $transparency);
					$color['gradient'] = '1';
				} else {
					$color['rgba2'] = '';
				}
			}
		}

		return $color;
	}

	public static function setRgba($color)
	{
		global $vePage;
		if (!isset($color['rgba']) && isset($color['color'])) {
			$transparency = $color['transparency'] ?? 1;
			if ($transparency >= 100) {
				$transparency /= 100;
			}
			if ($transparency > 1) {
				$transparency /= 100;
			}
			$color['transparency'] = $transparency;
			$color['rgba'] = $color['color'] ? Colors::hex2rgba($color['color'], $transparency) : '';
		}

		return $color;
	}

	public static function get_text_lightnes(&$color, $cl)
	{
		global $vePage;
		$cl = isset($color) && $color && Colors::isLightColor($color) ? 'invers' : 'default';

		return $cl;
	}

	// template temporary
	public static function templateBCrow($set)
	{
		$set['text'] = 'auto';
		$set['font']['color'] = '';
		$set['row_padding'] = 'big';
		$set['padding_top'] = '100';
		$set['padding_bottom'] = '100';

		if (!isset($set['background_image']) || !isset($set['background_image']['image']) || !$set['background_image']['image']) {
			$set['background_image']['overlay_color'] = [
				'color' => '#000000',
				'transparency' => '0.5',
				'rgba' => 'rgba(0,0,0,0.5)',
			];
			$set['background_image']['cover'] = '1';
		}

		return $set;
	}

	// template temporary
	public static function templateBCel($set)
	{
		// element text
		if ($set['type'] == 'text') {
			$set['style']['font']['color'] = '';
			$set['style']['font']['font-family'] = '';
		} elseif ($set['type'] == 'title') {
			// element title
			$set['style']['font']['color'] = '';
			$set['style']['font']['font-family'] = '';
		}

		return $set;
	}

	public static function defaultButtonForTemplate($button)
	{
		$button = [
			'style' => 'basic',
			'button_size' => 'medium',
			'custom_size' => '18',
		];

		return $button;
	}

	public static function removeElementDivs($text)
	{
		/*
		$matches=preg_match_all('#<div(.*) class="(.*)element_cont(.*)">#', stripslashes($text));
		if($matches) {
		$text=preg_replace('#<div(.*) class="(.*)element_cont(.*)">#', '', stripslashes($text));
		$text=preg_replace('#</div>#', '', stripslashes($text));
		}*/
		$text = str_replace('element_', '', $text);
		$text = str_replace('ce_editbar', '', $text);

		return $text;
	}


}
