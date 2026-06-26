<?php

use Mioweb\VisualEditor\Lib\Button;
use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\Icon;
use Mioweb\VisualEditor\Lib\Link;
use Mioweb\VisualEditor\Lib\GDPR;
use Mioweb\VisualEditor\Lib\Image;

function ve_element_text($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$vePage->display->add_enqueue_script('ve_lightbox_script');
	$vePage->display->add_enqueue_style('ve_lightbox_style');

	$type = $element['style']['style'] ?? 1;

	$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .in_element_content');
	if (isset($element['style']['p-background-color']) && isset($element['style']['p-background-color']['rgba1'])) {
		$vePage->display->element_css->addStyles(['background' => $element['style']['p-background-color']['rgba1']], $css_id . ' .element_text_style_2');
	}
	if (isset($element['style']['font']['line-height']) && $element['style']['font']['line-height']) {
		$vePage->display->element_css->addStyles(['background-position-y' => 'calc(' . ((float) $element['style']['font']['line-height'] / 2) . 'em - 13px)'], $css_id . ' .in_element_content ul li');
	}

	$size = 'small';
	if (isset($element['style']['font'])) {
		$size = $element['style']['font']['font-size'] < 28 ? 'medium' : 'big';
	}

	if (isset($element['style']['font']['mobile'])) {
		$vePage->display->element_css->addMobileStyles(['font-size' => mwisset($element['style']['font']['mobile'], 'font-size', 'px')], $css_id . ' .in_element_content');
	}
	if (isset($element['style']['font']['tablet'])) {
		$vePage->display->element_css->addTabletStyles(['font-size' => mwisset($element['style']['font']['tablet'], 'font-size', 'px')], $css_id . ' .in_element_content');
	}

	if (isset($element['style']['li'])) {
		$class = ' element_text_li' . $element['style']['li'];
	}
	$class = 've_text ve_text_style_' . $type;
	$class .= ' element_text_size_' . $size . ' element_text_style_' . $type;
	if (isset($element['style']['li'])) {
		$class .= ' element_text_li' . $element['style']['li'];
	}

	if ($type == '2') {
		$class .= Colors::isLightColor($element['style']['p-background-color']['color1']) ? ' light_color' : ' dark_color';
	}
	if ((int) $type === 3 || (int) $type === 4) {
		$vePage->display->element_css->addStyles(['_border' => $element['style']['border']], $css_id . ' .ve_text');
	}

	// shortcode paragraph repair
	$array = [
		'<p>[' => '[',
		']</p>' => ']',
		']<br />' => ']',
	];
	$element['style']['content'] = strtr($element['style']['content'], $array);

	$attr = '';
	if ($edit_mode) {
		$class .= ' mw_inline_editable_text';
		$attr .= 'data-target="content"';
	}

	$content = '<div class="in_element_content entry_content ve_content_text ' . $class . '" ' . $attr . '>';
	$content .= add_lightbox(stripslashes(wpautop($element['style']['content'])));

	$content .= '</div>';

	if ($added) {
		$content .= '<script>
        jQuery(function() {
          mwGetIframeContent().mw_init_tinymce();
        });
      </script>';
	}

	return $content;
}

function ve_element_title($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$type = $element['style']['style'] ?? 1;

	$vePage->display->element_css->addStyles([
		'font' => $element['style']['font'],
		'text-shadow' => $element['style']['font']['text-shadow'],
	], $css_id . ' .ve_title');

	if (!isset($element['style']['font']['mobile']) || (!$element['style']['font']['mobile']['font-size'] && !$element['style']['font']['tablet']['font-size'])) {
		if ($element['style']['font']['font-size'] > 100) {
			$element['style']['font']['mobile']['font-size'] = 70;
		} elseif ($element['style']['font']['font-size'] > 50) {
			$element['style']['font']['mobile']['font-size'] = 50;
		} elseif ($element['style']['font']['font-size'] > 35) {
			$element['style']['font']['mobile']['font-size'] = 35;
		}
	}

	$class = 've_title ve_title_style_' . $type;

	if ($type == 6) {
		if ($element['style']['decoration-color']) {
			$vePage->display->element_css->addStyles(['background-color' => $element['style']['decoration-color']], $css_id . ' .ve_title_decoration');
		}

		if (isset($element['style']['decoration-padding']) && $element['style']['decoration-padding'] != '') {
			$vePage->display->element_css->addStyles(['padding-bottom' => intval($element['style']['decoration-padding']) . 'px'], $css_id . ' .ve_title');
		}
	}
	if ($type == 2 || $type == 3) {
		$vePage->display->element_css->addStyles(['background-color' => $element['style']['background-color']['rgba1']], $css_id . ' .ve_title');
		$class .= Colors::isLightColor($element['style']['background-color']['color1']) ? ' light_color' : ' dark_color';
	}
	if (isset($element['style']['font']['mobile'])) {
		$vePage->display->element_css->addMobileStyles(['font-size' => mwisset($element['style']['font']['mobile'], 'font-size', 'px')], $css_id . ' .ve_title');
	}
	if (isset($element['style']['font']['tablet'])) {
		$vePage->display->element_css->addTabletStyles(['font-size' => mwisset($element['style']['font']['tablet'], 'font-size', 'px')], $css_id . ' .ve_title');
	}

	if ($type == 4 || $type == 5 || $type == 7 || $type == 8) {
		$vePage->display->element_css->addStyles(['_border' => $element['style']['border']], $css_id . ' .ve_title');
	}

	if ($type == 3 || $type == 6 || $type == 7 || $type == 8) {
		$class .= ' ve_title_' . $element['style']['align'];
	}

	$content = '<div class="' . $class . '">';

	if ($edit_mode) {
		$content .= '<div class="mw_inline_editable_title" data-target="content">';
	}

	// shortcode paragraph repair
	$array = [
		'<p>[' => '[',
		']</p>' => ']',
		']<br />' => ']',
	];
	$element['style']['content'] = strtr($element['style']['content'], $array);

	$content .= stripslashes(wpautop($element['style']['content']));

	if ($edit_mode) {
		$content .= '</div>';
	}

	if ($type == 6) {
		$content .= '<span class="ve_title_decoration"></span>';
	}

	$content .= '</div>';
	$content .= '<div class="cms_clear"></div>';

	// add inline editor after drag on page
	if ($added) {
		$content .= '<script>
        jQuery(function() {
          mwGetIframeContent().mw_init_tinymce();
        });
      </script>';
	}

	return $content;
}

function ve_element_button($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$class = 'in_element_content in_element_content_button in_element_content_button_' . $element['style']['align'];
	if (isset($element['style']['show_but2'])) {
		$class .= ' in_element_content_button_double';
	}

	$content = '<div class="' . $class . '">';

	$but_set1 = [
		'style' => $element['style']['button_style'] ?? [],
		'show' => $element['style']['show'] ?? '',
		'popup' => $element['style']['popup'] ?? '',
		'link' => $element['style']['link'] ?? null,
		'text' => $element['style']['content'],
		'subtext' => $element['style']['subtext'] ?? '',
		'icon_align' => $element['style']['icon_align'] ?? 'left',
	];

	if (isset($element['style']['show_icon']) && isset($element['style']['icon'])) {
		$but_set1['icon'] = new Icon($element['style']['icon'], $element['style']['icon_size'], 'em');
	}
	$content .= Button::createButton($but_set1, $vePage->display->element_css, 've_content_first_button', $css_id . ' .ve_content_first_button', $added, $edit_mode);

	if (isset($element['style']['show_but2'])) {
		$but_set2 = [
			'style' => $element['style']['button_style2'] ?? [],
			'show' => $element['style']['show2'] ?? '',
			'popup' => $element['style']['popup2'] ?? '',
			'link' => $element['style']['link2'] ?? null,
			'text' => $element['style']['text2'],
			'subtext' => $element['style']['subtext2'] ?? '',
		];
		if (isset($element['style']['show_icon2']) && isset($element['style']['icon2'])) {
			$but_set2['icon'] = new Icon($element['style']['icon2'], $element['style']['icon_size2'], 'em');
			$but_set2['icon_align'] = $element['style']['icon_align2'];
		}
		$content .= Button::createButton($but_set2, $vePage->display->element_css, 've_content_second_button', $css_id . ' .ve_content_second_button', $added, $edit_mode);
	}

	$content .= '</div>';

	return $content;
}

function ve_element_link($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$content = '';

	if ($element['style']['content']) {
		$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .ve_content_link a');

		if (isset($element['style']['show']) && $element['style']['show'] == 'popup' && $element['style']['popup']) {
			$content .= $vePage->display->popups->get_popup_to_content($element['style']['popup'], $added, $css_id . ' .open_mw_popup', $edit_mode);

			$link = '<a href="#" class="open_mw_popup" data-id="' . $element['style']['popup'] . '">';
		} else {
			$target = isset($element['style']['link']['target']) && $element['style']['link']['target'] == 1 ? 'target="_blank"' : '';
			$link = '<a href="' . Link::create_link($element['style']['link']) . '" ' . $target . '>';
		}

		$content .= '<p class="ve_content_link ' . $vePage->display->get_font_class($element['style']['font'], 'text') . ' ve_' . $element['style']['align'] . '">' . $link . stripslashes($element['style']['content']) . '</a></p>';
	} else {
		$vePage->display->add_element_info(__('Zadejte text odkazu.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_html($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;
	$content = '';
	if ($element['style']['content'] ?? false) {
		if (MwCookies()->isPermitted($element['style']['purpose'] ?? 'necessary')) {
			$content = '<div class="in_element_content">';
			if ($added) {
				$content .= do_shortcode($element['style']['content']);
			} else {
				$content .= $element['style']['content'];
			}
			$content .= '</div>';
		}
	} else {
		$vePage->display->add_element_info(__('Není zadán žádný HTML kód.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_video($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$text = $element['style']['content'] ?? '';
	$empty = false;
	$container_class = '';

	$tag_id = str_replace('#', '', $css_id);

	$content = '<div class="video_content_fullwidth">';
	if (isset($element['style']['own_video'])) {
		if ($element['style']['video_code']) {
			$content .= stripslashes($element['style']['video_code']);
		} else {
			$vePage->display->add_element_info(__('V nastavení tohoto elementu zadejte kód videa.', 'cms_ve'), 'info');
			$empty = true;
		}
	} elseif ($text) {
		$autoplay = isset($element['style']['autoplay']) ? '1' : '0';
		$controls = isset($element['style']['hide_control']) ? '0' : '1';
		$showinfo = '0';

		if (isset($element['style']['noclick'])) {
			$autoplay = '1';
			$controls = '0';
		}

		$video_setting = [
			'autoplay' => $autoplay,
			'showinfo' => $showinfo,
			'controls' => $controls,
		];

		$video = $vePage->display->getVideoCode($text, $video_setting, $tag_id);

		if ($video) {
			$content .= $video;
		} else {
			$vePage->display->add_element_info(__('URL stránky s videem není v tomto tvaru podporováno.', 'cms_ve'));
			$empty = true;
		}
	} else {
		$vePage->display->add_element_info(__('V nastavení tohoto elementu zadejte odkaz na své video.', 'cms_ve'), 'info');
		$empty = true;
	}

	if (isset($element['style']['noclick'])) {
		$content .= '<div class="video_element_overlay ve_nodisp"></div>';
	}

	$content .= MwCookies()->printVideoInfo($text);

	$content .= '</div>';

	if (isset($element['style']['popup'])) {
		$vePage->display->add_enqueue_script('ve_lightbox_script');
		$vePage->display->add_enqueue_style('ve_lightbox_style');

		if ($element['style']['popup_type'] == 'button') {
			$but_set = [
				'style' => $element['style']['popupbutton'],
				'link' => '#',
				'text' => $element['style']['button_text'],
				'align' => $element['style']['align'] ?? 'center',
				'attrs' => 'data-open="' . $css_id . '_popup"',
			];

			$link = Button::createButton(
				$but_set,
				$vePage->display->element_css,
				'open_video_lightbox',
				$css_id . ' .open_video_lightbox',
				$added,
				$edit_mode
			);
		} elseif ($element['style']['popup_type'] == 'icon') {
			$vePage->display->element_css->addStyles(
				['font-size' => $element['style']['play']['size'] . 'px'],
				$css_id . ' .video_play_button'
			);
			$vePage->display->element_css->addStyles(
				['fill' => $element['style']['play']['color']],
				$css_id . ' .video_play_button svg'
			);

			$text = $element['style']['icon_text'] ?? '';

			$container_class = 'mw_icon_text mw_icon_text_style_' . $element['style']['icon_text_align'] . ' mw_icon_text_align_' . $element['style']['align'] . ' mw_icon_text_vertical_align_center';
			$attr = ' data-open="' . $css_id . '_popup" href="#"';

			$link = '<a class="mw_icon_text-icon video_play_button open_video_lightbox" ' . $attr . '>'
			. '<div class="mw_icon mw_icon_style_1">'
			. '<i>' . mw_content_icon('icon-' . $element['style']['play']['icon']) . '</i>'
			. '</div>'
			. '</a>'
			. '<div class="mw_icon_text_content">'
			. $vePage->display->printContentContainer($text, 'mw_icon_text-text open_video_lightbox ' . $vePage->display->get_font_class($element['style']['font'], 'text'), 'a', '', '', $attr)
			. '</div>';
		} else {
			/*
			$vePage->display->element_css->addStyles(
			array('width'=>$element['style']['play']['size']."px",'height'=>$element['style']['play']['size']."px"),
			$css_id." .video_play_button svg"
			); */
			$vePage->display->element_css->addStyles(
				['font-size' => $element['style']['play']['size'] . 'px'],
				$css_id . ' .video_play_button'
			);
			$vePage->display->element_css->addStyles(
				['fill' => $element['style']['play']['color']],
				$css_id . ' .video_play_button svg'
			);

			$img = '';
			$image = new Image($element['style']['image']);
			$link_class = '';
			if (isset($element['style']['align'])) {
				$link_class = 'element_image_' . $element['style']['align'];
			}

			if (!$image->isEmpty()) {
				$link_class .= ' element_image';
				$img = $image->printImg([
					'col_divisor' => Image::getColDivisor($col_type),
					'full_row' => MWPageDisplay::isFullWidthRow($row_set),
				], '', $edit_mode);
			}

			$link = '<a class="open_video_lightbox ' . $link_class . '" href="#" data-open="' . $css_id . '_popup">' . $img . '
                    <div class="video_play_button">' . mw_content_icon('icon-' . $element['style']['play']['icon']) . '</div>
                </a>';
		}
		if ($added) {
			$content .= "<script>
                jQuery(function() {
                  mwGetIframeContent().mw_init_video_popup('" . $css_id . " .open_video_lightbox');
                });
              </script>";
		}
		$content = $link . '
            <div style="display: none;">
                <div id="' . str_replace('#', '', $css_id) . '_popup" class="popup_video_container">
                    ' . $content . '
                </div>
            </div>';

		$container_class .= isset($element['style']['popup_type']) ? ' in_element_video_' . $element['style']['popup_type'] : '';
	} elseif (isset($element['style']['noclick'])) {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'Safari') !== false || strpos($user_agent, 'Chrome') !== false) {
			$vePage->display->add_enqueue_script('ve_youtube_api');

			$content .= '<script>
              var player;
              function onYouTubeIframeAPIReady() {
                player = new YT.Player("' . $tag_id . '_video", {
                  events: {
                    "onStateChange": function(event) {
                        if (event.data == YT.PlayerState.PLAYING) {
                            jQuery("' . $css_id . ' .video_element_overlay").show();
                        }
                    },
                  }
                });
              }
              </script>';
		}
	}

	$ret = '';
	if (!$empty) {
		$ret = '<div class="in_element_content ' . $container_class . '">' . $content . '</div><div class="cms_clear"></div>';
	}

	return $ret;
}


function ve_element_graphic($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$content = '';

	if ($element['style']['type'] == 'hr') {
		if ($element['style']['graphic_hr'] == '1' && $element['style']['border']['size']) {
			$vePage->display->element_css->addStyles(
				[
					'border_top' => [
						'color' => $element['style']['border']['rgba'],
						'style' => $element['style']['border']['style'],
						'size' => $element['style']['border']['size'],
					],
				],
				$css_id . ' .graphic_element_hr'
			);
		}

		$color_class = $element['style']['graphic_hr'] == '1' ? '' : 'graphic_element_hr_color_' . $element['style']['hr_color'];

		$content = '<hr class="graphic_element graphic_element_hr graphic_element_' . $element['style']['graphic_hr'] . ' ' . $color_class . '">';
	} elseif ($element['style']['type'] == 'img') {
		$content = '<div class="graphic_element graphic_element_img graphic_element_' . $element['style']['graphic_img'] . '"><img src="' . VS_DIR . 'images/image_select/' . $element['style']['graphic_img'] . '.png" alt="" /></div>';
	} elseif ($element['style']['type'] == 'books') {
		$vePage->display->element_css->addStyles(
			[
				'background-color' => $element['style']['book_background_color'],
			],
			$css_id . ' .graphic_element_book_cover'
		);

		$vePage->display->element_css->addBgStyle($element['style']['book_background_image'] ?? [] ?: [], $css_id . ' .graphic_element_book_cover', $edit_mode, 'large');

		$vePage->display->element_css->addStyles(
			[
				'font' => $element['style']['title_font'],
			],
			$css_id . ' .graphic_element_book_title'
		);
		if (isset($element['style']['title_font']['mobile'])) {
			$vePage->display->element_css->addMobileStyles(['font-size' => mwisset($element['style']['title_font']['mobile'], 'font-size', 'px')], $css_id . ' .graphic_element_book_title');
		}
		if (isset($element['style']['title_font']['tablet'])) {
			$vePage->display->element_css->addTabletStyles(['font-size' => mwisset($element['style']['title_font']['tablet'], 'font-size', 'px')], $css_id . ' .graphic_element_book_title');
		}

		$vePage->display->element_css->addStyles(
			[
				'font' => $element['style']['author_font'],
			],
			$css_id . ' .graphic_element_book_author'
		);

		$background_overlay_color = isset($element['style']['book_background_image']['overlay_color']['rgba']) && isset($element['style']['book_background_image']['image']) && $element['style']['book_background_image']['image'] && isset($element['style']['book_background_image']['color_filter']) ? $element['style']['book_background_image']['overlay_color']['rgba'] : '';
		if ($background_overlay_color) {
			$vePage->display->element_css->addStyles(['background-color' => $background_overlay_color], $css_id . ' .graphic_element_book_cover .background_overlay');
		}

		$contentAlign = $element['style']['content_vertical_align'] ?? 'center';
		$element['style']['content_vertical_align'] = 've_align_title_' . $contentAlign;

		$content = '<div class="graphic_element graphic_element_book graphic_element_book_' . $element['style']['graphic_book'] . '">'
		. '<div class="graphic_element_book_cover background_cover">'
		. '<img class="graphic_element_book_shadow" src="' . VS_DIR . 'images/content/ebook_shadow' . $element['style']['graphic_book'] . '.png" alt="" />'
		. '<div class="background_overlay"></div>'
		. '<div class="graphic_element_book_content ve_' . $element['style']['content_align'] . '">'
		. '<div class="graphic_element_book_title title_element_container ' . $element['style']['content_vertical_align'] . '"><span>' . $element['style']['book_title'] . '</span></div>'
		. '<div class="graphic_element_book_author">' . $element['style']['book_author'] . '</div>'
		. '</div>'
		. '<img class="graphic_element_book_overlay" src="' . VS_DIR . 'images/content/ebook_overlay.png" alt="" />'
		. '</div>'
		. '</div>';
	} elseif ($element['style']['type'] == 'mockups') {
		if (isset($element['style']['mockup_image']['position'])) {
			$vePage->display->element_css->addStyles(['object-position' => $element['style']['mockup_image']['position']], $css_id . ' .graphic_mockup_image img');
		}

		$image = new Image($element['style']['mockup_image']);

		$mockup = '<div class="graphic_element graphic_element_mockup graphic_element_mockup_' . $element['style']['graphic_mockups'] . '">'
		. '<img src="' . VS_DIR . 'images/content/mockup_' . $element['style']['graphic_mockups'] . '.png" alt="" />'
		. '<div class="graphic_mockup_image">'
		. $image->printImg([
				//'max_width' => $maxWidth,
				'col_divisor' => Image::getColDivisor($col_type),
				'full_row' => MWPageDisplay::isFullWidthRow($row_set),
		], '', $edit_mode)
		. '</div>'
		. '</div>';

		if (!isset($element['style']['click_action'])) {
			$element['style']['click_action'] = 'none';
		}
		if ($element['style']['click_action'] == 'link') {
			$content .= '<a href="' . Link::create_link($element['style']['link']) . '" ' . (isset($element['style']['link']['target']) ? 'target="_blank"' : '') . '>';
			$content .= $mockup;
			$content .= '</a>';
		} elseif ($element['style']['click_action'] == 'popup' && $element['style']['popup']) {
			$content .= $vePage->display->popups->get_popup_to_content($element['style']['popup'], $added, $css_id . ' .open_mw_popup', $edit_mode);

			$content .= '<a class="open_mw_popup" href="#" data-id="' . $element['style']['popup'] . '">';
			$content .= $mockup;
			$content .= '</a>';
		} else {
			$content .= $mockup;
		}
	} else {
		$vePage->display->add_element_info(__('Vyberte grafický prvek, který chcete zobrazit.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_image($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;
	// new id in images compatibility (temporary)
	if (isset($element['style']['image']) && !is_array($element['style']['image'])) {
		$element['style']['image'] = ['image' => $element['style']['image']];
	}
	if (isset($element['style']['large_image']) && !is_array($element['style']['large_image'])) {
		$element['style']['large_image'] = ['image' => $element['style']['large_image']];
	}
	// end temporary

	$content = '';

	$image = new Image($element['style']['image']);

	if (!$image->isEmpty()) {
		$link = false;

		// back compatibility
		if (!isset($element['style']['hover_color']['color'])) {
			$old = $element['style']['hover_color'] ?? '';
			$element['style']['hover_color'] = [];
			$element['style']['hover_color']['color'] = $old;
		}
		// end back compatibility

		if (isset($element['style']['hover_color']['rgba']) && $element['style']['hover_color']['rgba']) {
			$vePage->display->element_css->addStyles(['background-color' => $element['style']['hover_color']['rgba']], $css_id . ' .element_image_overlay_icon_container');
		}

		if (isset($element['style']['max-width']) && $element['style']['max-width'] || $edit_mode) {
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .element_image_container' => ['max-width'],
					$css_id . ' .element_image_label' => ['max-width'],
				],
				'--image-width-' . $css_id,
				isset($element['style']['max-width']) && $element['style']['max-width'] ? $element['style']['max-width'] . 'px' : '100%'
			);
		}

		$vePage->display->element_css->addStyles(['object-position' => $image->getPosition()], $css_id . ' .element_image_container img');

		if (isset($element['style']['border']) && $element['style']['border']['size']) {
			$vePage->display->element_css->addStyles(
				[
					'_border' => $element['style']['border'],
				],
				$css_id . ' .element_image_container'
			);
		}

		$hover_class = isset($element['style']['hover']) && $element['style']['hover'] ? 'image_hover_' . $element['style']['hover'] : '';

		//if($element['style']['style']=='6') $element['style']['thumb_name']='11';

		$type = '';
		$class = '';
		if (!isset($element['style']['click_action'])) {
			if ($element['style']['link']['link']) {
				$type = 'link';
			} elseif (isset($element['style']['large_image']['image']) && $element['style']['large_image']['image']) {
				$type = 'image';
			}
		} else {
			$type = $element['style']['click_action'];
		}

		$align = $element['style']['align'] ?? 'center';

		$content = '<div class="element_image element_image_' . $element['style']['img_style'] . ' ve_' . $align . ' ' . $hover_class . '">';

		$attr = '';
		if ($type == 'alert' && $element['style']['alert']) {
			$attr = 'data-alert="' . $element['style']['alert'] . '"';
		}

		$container_class = '';
		if (isset($element['style']['corner']) && $element['style']['corner']) {
			$container_class .= ' mw_element_item_corners' . $element['style']['corner'];
		}
		if (isset($element['style']['shadow']) && $element['style']['shadow']) {
			$container_class .= ' mw_element_item_shadow' . $element['style']['shadow'];
		}

		$container_class .= ' element_image_container_' . $image->getExt();

		$content .= '<div class="element_image_container ' . (!$element['style']['thumb_name'] ? 'element_image_container_inline' : '') . ' ' . $container_class . '" ' . $attr . '>';
		$inside_class = '';

		if (isset($element['style']['hover']) && ($element['style']['hover'] == 'zoom' || $element['style']['hover'] == 'overlay_icon')) {
			$inside_class .= 'element_image_hover_container';
		}

		if (isset($element['style']['thumb_name']) && $element['style']['thumb_name']) {
			$inside_class .= ' mw_image_ratio mw_image_ratio_' . $element['style']['thumb_name'];
		}

		if ($inside_class) {
			$content .= '<div class="' . $inside_class . '">';
		}

		if ($type == 'link') {
			$img_title = $image->getTitle();
			$content .= '<a href="' . Link::create_link($element['style']['link']) . '" ' . (isset($element['style']['link']['target']) ? 'target="_blank"' : '') . ' ' . ($img_title ? 'title="' . $img_title . '"' : '') . '>';
			$link = true;
		} elseif ($type == 'image') {
			$vePage->display->add_enqueue_script('ve_lightbox_script');
			$vePage->display->add_enqueue_style('ve_lightbox_style');

			$largeImage = new Image($element['style']['large_image']);
			$img = $largeImage->getUrl('full') ?: $image->getUrl('full');

			$content .= '<a class="open_lightbox element_image_large" href="' . $img . '">';
			$link = true;

			if ($added) {
				$content .= '<script>
                      jQuery(function() {
                        mwGetIframeContent().mw_init_basic_popup("' . $css_id . ' .open_lightbox");
                      });
                    </script>';
			}
		} elseif ($type == 'alert') {
			$class .= 'element_image_alert';
			$jqselector = $added ? '("#mw_page_builder").contents().find' : '';
			$content .= '<script type="text/javascript">
                jQuery(document).ready(function($) {
                    $' . $jqselector . '("' . $css_id . ' .element_image_container").click(function(){
                        alert($(this).attr("data-alert"));
                    });
                });
                </script>';
		}
		if ($type == 'popup' && $element['style']['popup']) {
			$content .= $vePage->display->popups->get_popup_to_content($element['style']['popup'], $added, $css_id . ' .open_mw_popup', $edit_mode);

			$content .= '<a class="open_mw_popup" href="#" data-id="' . $element['style']['popup'] . '">';
			$link = true;
		}

		$maxWidth = isset($element['style']['max-width']) && $element['style']['max-width'] ? intval($element['style']['max-width']) : null;
		$content .= $image->printImg([
			'max_width' => $maxWidth,
			'image_ratio' => $element['style']['thumb_name'] ?? '',
			'full_row' => MWPageDisplay::isFullWidthRow($row_set),
			'col_divisor' => Image::getColDivisor($col_type),
		], $class, $edit_mode, true);

		if (isset($element['style']['hover']) && $element['style']['hover'] == 'overlay_icon') {
			$content .= '<div class="element_image_overlay_icon_container">' . mw_content_icon_set('zoom-in') . '</div>';
		}

		if ($inside_class) {
			$content .= '</div>';
		}

		if ($link) {
			$content .= '</a>';
		}

		$content .= '</div>'; // end of element_image_container

		$label = isset($element['style']['label']) && $element['style']['label'] ? $element['style']['label'] : '';
		$content .= $vePage->display->printContentContainer($label, 'element_image_label', 'div');

		$content .= '</div><div class="cms_clear"></div>';
	} else {
		$vePage->display->add_element_info(__('V nastavení elementu nahrajte obrázek.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_image_gallery($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;
	$output = '';
	$htmlTag = 'a';
	$class_open = 'open_lightbox';

	$isOneCol = isset($element['style']['use_slider']) && $vePage->display->is_mobile;
	$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .image_gallery_element__item__caption');
	if (isset($element['style']['hover_color']) && isset($element['style']['hover_color']['rgba'])) {
		$vePage->display->element_css->addStyles(['background-color' => $element['style']['hover_color']['rgba']], $css_id . ' .image_gallery_element_item_thumb .element_image_overlay_icon_container');
	}
	if (!isset($element['style']['image_gallery_items']) || empty($element['style']['image_gallery_items']) || !is_array($element['style']['image_gallery_items'])) {
		$vePage->display->add_element_info(__('Galerie je prázdná', 'cms_ve'), 'info');
	} else {
		foreach ($element['style']['image_gallery_items'] as $key => $item) {
			if (!Image::existImage((int) $item) || $item === '') {
				unset($element['style']['image_gallery_items'][$key]);
			}
		}

		if (count($element['style']['image_gallery_items']) === 0) {
			$vePage->display->add_element_info(__('Galerie je prázdná', 'cms_ve'), 'info');

			return '';
		}

		$vePage->display->add_enqueue_script('ve_lightbox_script');
		$vePage->display->add_enqueue_style('ve_lightbox_style');

		$cols = $vePage->display->getAutoCols($isOneCol ? 1 : $element['style']['cols'], count($element['style']['image_gallery_items']));

		$image_rows = array_chunk($element['style']['image_gallery_items'], $cols);

		$image_row_classes = 'col col-' . $cols;

		$rel_attr = 'mio_image_gallery_' . substr(md5(serialize($element['style'])), 0, 10); //unique frontend ID for each gallery on page

		$gallery_class = 'image_gallery_element in_element_content is-theme-' . $element['style']['gallery_style'] . ' cols-' . $cols;
		if (isset($element['style']['cols_type']) && $element['style']['cols_type']) {
			$gallery_class .= ' ' . $element['style']['cols_type'];
		}

		// hover class
		if (isset($element['style']['hover']) && $element['style']['hover']) {
			$gallery_class .= ' image_gallery_hover_' . $element['style']['hover'];
		}
		//mw_image_ratio
		$carousel_set = '';
		if (isset($element['style']['use_slider'])) {
			$vePage->display->add_enqueue_script('ve_miocarousel_script');
			$vePage->display->add_enqueue_style('ve_miocarousel_style');

			$element['style']['cols'] = $isOneCol ? 1 : $element['style']['cols'];

			if ($added) {
				$output .= '<script>
              jQuery(function() {
                mwGetIframeContent().set_miocarousel("' . $css_id . ' .miocarousel");
              });
            </script>';
			}

			$gallery_class .= ' miocarousel miocarousel_style_1';
			if ($element['style']['miocarousel_setting']['color_scheme']) {
				$gallery_class .= ' miocarousel_' . $element['style']['miocarousel_setting']['color_scheme'];
			}

			if (isset($element['style']['miocarousel_setting']['hide_navigation'])) {
				$gallery_class .= ' miocarousel_hide_nav';
			}

			if (isset($element['style']['miocarousel_setting']['autoplay'])) {
				$carousel_set .= ' data-autoplay="1"';
			} else {
				$carousel_set .= ' data-autoplay="0"';
			}
			if ($element['style']['miocarousel_setting']['delay']) {
				$carousel_set .= ' data-duration="' . $element['style']['miocarousel_setting']['delay'] . '"';
			}
			if ($element['style']['miocarousel_setting']['speed']) {
				$carousel_set .= ' data-speed="' . $element['style']['miocarousel_setting']['speed'] . '"';
			}
			if ($element['style']['miocarousel_setting']['animation'] && $element['style']['miocarousel_setting']['animation'] != 'fade') {
				$carousel_set .= ' data-animation="' . $element['style']['miocarousel_setting']['animation'] . '"';
			}
		}

		$image_item_class = '';
		if (isset($element['style']['thumb_name']) && $element['style']['thumb_name']) {
			$image_item_class = 'mw_image_ratio mw_image_ratio_' . $element['style']['thumb_name'];
		}

		$output .= '<div class="' . $gallery_class . '" ' . $carousel_set . '>';
		if (isset($element['style']['use_slider'])) {
			$output .= '<div class="miocarousel-inner">';
		}

		$row_num = 1;
		foreach ($image_rows as $row) {
			if (isset($element['style']['use_slider'])) {
				$row_class = ' mw_element_row slide';
				if ($row_num == 1) {
					$row_class .= ' active';
				}
			} else {
				$row_class = 'mw_element_row';
			}

			$output .= '<div class="' . $row_class . '">';

			foreach ($row as $image) {
				if (str_starts_with($image, 'http')) {
					$img = new Image(['image' => $image]);
					$href = 'href="' . $image . '"';
					$caption_html = '';
				} else {
					$img = new Image(['imageid' => $image]);
					$caption_html = $img->getExcerpt() ? '<div class="image_gallery_element__item__caption ' . $vePage->display->get_font_class($element['style']['font'], 'text') . '">' . $img->getExcerpt() . '</div>' : '';
					$href = 'href="' . $img->getUrl('full') . '"';
				}

				$show_image = $img->printImg([
					'col_divisor' => Image::getColDivisor($col_type) * $cols,
					'image_ratio' => $element['style']['thumb_name'] ?? '',
					'full_row' => MWPageDisplay::isFullWidthRow($row_set),
				], '', $edit_mode);

				// colored hover with image

				if (isset($element['style']['hover']) && $element['style']['hover'] == 'overlay_icon') {
					$show_image .= '<div class="element_image_overlay_icon_container">' . mw_content_icon_set('zoom-in') . '</div>';
				}

				// do not show gallery

				if (isset($element['style']['click_action'])) {
					if ($element['style']['click_action'] === 'none') {
						$href = ' ';
						$htmlTag = 'div';
						$class_open = ' ';
					}
				}

				$output .= '<' . $htmlTag . ' ' . $href . ' class="image_gallery_element__item ' . $class_open . ' ' . $image_row_classes . '" rel="' . $rel_attr . '" id="change" >';
				$output .= '<div class="image_gallery_element_item_thumb ' . $image_item_class . '" id="content">' . $show_image . '</div>';
				$output .= $caption_html;
				$output .= '</' . $htmlTag . '>';
			}

			$output .= '</div>';
			if ($added) {
				$output .= '<script>
              jQuery(function() {
                mwGetIframeContent().mw_init_basic_popup("' . $css_id . ' .open_lightbox");
              });
            </script>';
			}
			$row_num++;
		}



		if (isset($element['style']['use_slider'])) {
			$output .= '</div>'; //slider end
			$output .= '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
			$output .= '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';


			if ($added) {
				$output .= "<script>
            jQuery(function() {
                function imageLoaded() {
                   counter--;
                   if( counter === 0 ) {
                        mwGetIframeContent().set_miocarousel('" . $css_id . " .miocarousel');
                   }
                }
                var images = jQuery('" . $css_id . " img');
                var counter = images.length;

                images.each(function() {
                    if( this.complete ) {
                        imageLoaded.call( this );
                    } else {
                        jQuery(this).one('load', imageLoaded);
                    }
                });
            });
            </script>";
			}
		}

		$output .= '</div><div class="cms_clear"></div>';
	}

	return $output;
}

function ve_element_image_text($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;
	$content = '';

	if (!isset($element['style']['hide_text']) || !isset($element['style']['hide_title']) || $element['style']['image']['image']) {
		$visual_style = $element['style']['visual_style'] ?? 1;
		$text_align = $element['style']['text-align'] ?? 'left';

		$class = '';

		if ($visual_style == '5') {
			$element['style']['align'] = 'left';
			$bgcolor = isset($element['style']['overlay_color']) ? $element['style']['overlay_color']['rgba'] : '';
			if (isset($element['style']['hide_content'])) {
				$class .= 'el_it_hover_content';
			}
		} else {
			$bgcolor = $element['style']['background_color'] ?? '';
		}

		// new id in images compatibility (temporary)
		if (isset($element['style']['image']) && !is_array($element['style']['image'])) {
			$element['style']['image'] = ['image' => $element['style']['image']];
		}

		$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .el_it_text h3');
		$vePage->display->element_css->addStyles(['font' => $element['style']['font_text'] ?? ''], $css_id . ' .el_it_text .entry_content');
		if (isset($element['style']['font']['mobile'])) {
			$vePage->display->element_css->addMobileStyles(['font-size' => mwisset($element['style']['font']['mobile'], 'font-size', 'px')], $css_id . ' .el_it_text h3');
		}
		if (isset($element['style']['font']['tablet'])) {
			$vePage->display->element_css->addTabletStyles(['font-size' => mwisset($element['style']['font']['tablet'], 'font-size', 'px')], $css_id . ' .el_it_text h3');
		}
		if (isset($element['style']['font_text']['mobile'])) {
			$vePage->display->element_css->addMobileStyles(['font-size' => mwisset($element['style']['font_text']['mobile'], 'font-size', 'px')], $css_id . ' .el_it_text .entry_content');
		}
		if (isset($element['style']['font_text']['tablet'])) {
			$vePage->display->element_css->addTabletStyles(['font-size' => mwisset($element['style']['font_text']['tablet'], 'font-size', 'px')], $css_id . ' .el_it_text .entry_content');
		}

		$vePage->display->element_css->addStyles(['background-color' => $bgcolor], $css_id . ' .el_it_background');

		if (isset($element['style']['image']['position'])) {
			$vePage->display->element_css->addStyles(['object-position' => $element['style']['image']['position']], $css_id . ' .el_it_image img');
		}

		$valign = $element['style']['valign'] ?? 'center';

		$content .= '<div class="in_element_content in_element_image_text in_element_image_text_' . $visual_style . ' in_element_image_text_is_' . $element['style']['style'] . ' ve_valign_' . $valign . ' ' . ($visual_style == 2 || $visual_style == 3 || $visual_style == 4 ? 'fullcols' : '') . ' ' . $class . '">';

		$img_class = 'el_it_image';
		$text_class = 'el_it_text ' . ($element['style']['align'] == 'left' ? 'el_it_text_second' : 'el_it_text_first') . ' ve_' . $text_align;
		$text_wrap_class = 'el_it_text_wrapper';

		$el_cols = 'one';
		$el_text_cols = 'one';
		$colRatio = 1;

		if ($visual_style == '1' || $visual_style == '2' || $visual_style == '3' || $visual_style == '6') {
			if ($element['style']['style'] == '1' || $element['style']['style'] == 'two') {
				$el_cols = 'two';
				$el_text_cols = 'two';
				$colRatio = 2;
			} elseif ($element['style']['style'] == '2' || $element['style']['style'] == 'three') {
				$el_cols = 'three';
				$el_text_cols = 'twothree';
				$colRatio = 3;
			} elseif ($element['style']['style'] == '3' || $element['style']['style'] == 'four') {
				$el_cols = 'four';
				$el_text_cols = 'threefour';
				$colRatio = 4;
			} elseif ($element['style']['style'] == '4' || $element['style']['style'] == 'five') {
				$el_cols = 'five';
				$el_text_cols = 'fourfive';
				$colRatio = 5;
			} elseif ($element['style']['style'] == 'twothree') {
				$el_cols = 'twothree';
				$el_text_cols = 'three';
				$colRatio = 3 / 2;
			}
		}

		$img_class .= ' col-' . $el_cols;
		$text_class .= ' col-' . $el_text_cols;

		if ($visual_style == '6') {
			$element['style']['image_ratio'] = '11';
		}

		$image = new Image($element['style']['image']);
		$img = $image->printImg([
			'col_divisor' => Image::getColDivisor($col_type) * $colRatio,
			'full_row' => MWPageDisplay::isFullWidthRow($row_set),
			'image_ratio' => $element['style']['image_ratio'] ?? '',
		], '', $edit_mode);

		if (isset($element['style']['image_ratio']) && $element['style']['image_ratio']) {
			$img = '<div class="mw_image_ratio mw_image_ratio_' . $element['style']['image_ratio'] . '">' . $img . '</div>';
		}

		// image
		$col_img = '<div class="' . $img_class . '">' . $img . '</div>';

		$attr_text = '';
		$class_text = 'el_it_item';
		$class_title = 'el_it_item';
		$attr_title = '';
		if ($edit_mode) {
			$class_text .= ' mw_inline_editable_simple_text';
			$attr_text = 'data-target="content"';
			$attr_title = ' data-target="title"';
			$class_title .= ' mw_inline_editable_simple_title';
		}

		if ($visual_style == '2') {
			$text_class .= ' el_it_background';
			$text_class .= Colors::isLightColor($bgcolor) ? ' light_color' : ' dark_color';
		} elseif ($visual_style == '3') {
			$text_wrap_class .= ' el_it_background';
			$text_wrap_class .= Colors::isLightColor($bgcolor) ? ' light_color' : ' dark_color';
		} elseif ($visual_style == '5') {
			$text_class .= ' el_it_background';
		}

		// content
		$col_text = '<div class="' . $text_class . '">';

		$col_text .= '<div class="' . $text_wrap_class . '">';

		if (($element['style']['title'] || $vePage->edit_mode) && !isset($element['style']['hide_title'])) {
			$col_text .= '<h3 class="' . $vePage->display->get_font_class($element['style']['font']) . ' ' . $class_title . '" ' . $attr_title . '>' . stripslashes($element['style']['title']) . '</h3>';
		}

		if (($element['style']['content'] || $vePage->edit_mode) && !isset($element['style']['hide_text'])) {
			$col_text .= '<div class="entry_content ' . $class_text . '" ' . $attr_text . '>';
			$col_text .= stripslashes($element['style']['content']);
			$col_text .= '</div>';
		}

		if (isset($element['style']['show_button'])) {
			$link = isset($element['style']['button_link']) ? Link::create_link($element['style']['button_link']) : '';

			$but_set = [
				'style' => $element['style']['button'],
				'link' => $element['style']['button_link'],
				'text' => $element['style']['button_text'],
			];

			$col_text .= Button::createButton(
				$but_set,
				$vePage->display->element_css,
				'el_it_item',
				$css_id . ' .ve_content_button',
				$added,
				$edit_mode
			);
		}

		$col_text .= '</div>';

		$col_text .= '</div>';

		if ($element['style']['align'] == 'left') {
			$content .= $col_img . $col_text;
		} else {
			$content .= $col_text . $col_img;
		}

		if ($added) {
			$content .= '<script>
            jQuery(function() {
              mwGetIframeContent().mw_init_tinymce();
            });
          </script>';
		}

		$content .= '</div>';
	} else {
		$vePage->display->add_element_info(__('Není zadán žádný obsah', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_wpcomments($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage, $mw_comment_set;

	$content = '';
	if ($post_id == get_option('page_on_front')) {
		$vePage->display->add_element_info(__('Komentáře nelze na úvodní stránce zobrazit.', 'cms_ve'));
	} else {
		if (!isset($element['style']['button'])) {
			$element['style']['button'] = ['style' => 'x'];
		}

		$content .= '<div class="in_element_content element_comment_' . $element['style']['style'] . '">';

		if ($added) {
			global $post;
			$post = get_post($post_id);
			query_posts('p=' . $post_id);
		}

		if (!isset($element['style']['button']['style'])) {
			$element['style']['button']['style'] = 'basic';
		}
		$button_style = mwButtonStyles()->getStyle($element['style']['button']['style']);

		$mw_comment_set = [
			'button_style' => $element['style']['button'],
			'button_hover' => $button_style['hover_effect'],
			'comment_style' => $element['style']['style'],
			'css_id' => $css_id,
		];
		ob_start();
		comments_template('/comments.php');
		$comments = ob_get_contents();
		ob_end_clean();

		if ($added) {
			$comments = str_replace('window.parent', "''", $comments);
		}

		$content .= $comments;
		//$content.=cms_wp_comments($post_id, $element['style']);
		$content .= '</div>';
	}

	return $content;
}

function ve_element_seform($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;
	$content = '';
	$custom = false;

	$vePage->display->element_css->addStyles([
		'font' => $element['style']['form-font'],
		'background-color' => $element['style']['background'],
	], $css_id . '_form .ve_form_field');

	$vePage->display->element_css->addStyles([
		'font' => $element['style']['form-font'],
	], $css_id . '_form .ve_form_label');

	if ($element['style']['form-style'] == 2 || !isset($element['style']['form-labels'])) {
		$element['style']['form-labels'] = 1;
	}

	// back compatibility (temporary)
	if (isset($element['style']['content']) && !is_array($element['style']['content'])) {
		$old_content = $element['style']['content'];
		$element['style']['content'] = [];
		$element['style']['content']['id'] = $old_content;
	}
	if (!isset($element['style']['content']['api'])) {
		$element['style']['content']['api'] = 'se';
	}
	// end temporary

	if ((!isset($element['style']['type']) || $element['style']['type'] == 'smartemailing') && isset($element['style']['content']['id']) && $element['style']['content']['id']) {
		$form = mwEmailingApi()->get_form($element['style']['content'], $vePage->edit_mode);

		if ($form != false) {
			$content .= mwEmailingApi()->print_form($element['style']['content']['api'], $element, $form, $css_id, $added);
		} else {
			$vePage->display->add_element_info(__('Při získávání formuláře došlo k chybě. Mohlo dojít k výpadku Vašeho email marketingového poskytovatele nebo mohla vypršet platnost vašich přístupových údajů. Prosím zkontrolujte to.', 'cms_ve'));
			$content .= '';
		}
	} elseif (isset($element['style']['type']) && (($element['style']['type'] == 'custom' && isset($element['style']['custom_form'])) || ($element['style']['type'] == 'custom_url' && isset($element['style']['custom_form'])))) {
		$form = [];
		$custom = true;

		if ($element['style']['type'] == 'custom') {
			if (!isset($element['style']['email']) || $element['style']['email'] === '') {
				$vePage->display->add_element_info(__('Prosím, V nastavení zadejte emailovou adresu.', 'cms_ve'));
			}
			if (isset($_GET['custom_form_error'])) {
				$content .= '<div class="mw_input_error_text">';
				switch ($_GET['custom_form_error']) {
					case 'norequired':
						$content .= __('Nejsou vyplněny všechny povinné pole.', 'cms_ve');

						break;
					case 'hidden_field':
						$content .= __('Neprošels ochranou proti botům.', 'cms_ve');

						break;
					case 'internal_error':
						$content .= __('Formulář se nepodařilo odeslat z důvodu interní chyby aplikace. Kontaktujte prosím správce webu e-mailem.', 'cms_ve');

						break;
					case 'recaptcha':
						$content .= __('Formulář se nepodařilo odeslat z důvodu ochrany proti botům (reCAPTCHA)', 'cms_ve');

						break;
					case 'time':
						$content .= __('Formulář byl odeslán příliš rychle (ochrana proti botům).', 'cms_ve');

						break;
					default:
						$content .= $_GET['custom_form_error'];

						break;
				}
				$content .= '</div>';
			}

			$use_form = $element['style']['custom_form'];
			$form['url'] = get_permalink($post_id);
			$thx_url = Link::create_link($element['style']['thx_url'], false);

			$form['fields']['customform_subject'] = [
				'label' => '',
				'fieldname' => 've_customform_subject',
				'defaultfield' => '',
				'content' => $element['style']['subject'],
				'customfield_type' => 'hidden',
				'required' => '',
			];
			$form['fields']['customform_url'] = [
				'label' => '',
				'fieldname' => 've_customform_url',
				'defaultfield' => '',
				'content' => $thx_url ?: get_permalink($post_id),
				'customfield_type' => 'hidden',
				'required' => '',
			];
			$form['fields']['customform_structure'] = [
				'label' => '',
				'fieldname' => 've_customform_structure',
				'defaultfield' => '',
				'content' => base64_encode(serialize(['form' => $element['style']['custom_form'], 'email' => $element['style']['email']])),
				'customfield_type' => 'hidden',
				'required' => '',
			];
			$form['fields']['customform_email'] = [
				'label' => '',
				'fieldname' => 've_customform_email',
				'defaultfield' => '',
				'content' => '',
				'customfield_type' => 'antispam',
				'required' => '',
			];
			$form['fields']['customform_time_sended'] = [
				'label' => '',
				'fieldname' => 've_sended_time',
				'defaultfield' => '',
				'content' => current_time('timestamp'),
				'customfield_type' => 'hidden',
				'required' => '',
			];

			if (MWRecaptcha()->isActive()) {
				$form['class'] = 'g-recaptcha-form';
				$consentTexts = $form['consent_texts'] ?? [];
				$consentTexts[] = MWRecaptcha()->getPrivacyConsentText();
				$form['consent_texts'] = $consentTexts;
			}
		} else {
			$use_form = $element['style']['custom_form'];
			$form['url'] = $element['style']['url'] ?: get_permalink($post_id);
		}

		$form['submit'] = __('Odeslat', 'cms_ve');

		foreach ($use_form as $key => $field) {
			$label = $field['title'];

			if ($field['type'] == 'agree' && (($field['agree_link'] && $field['agree_link_text']) || $vePage->display->edit_mode)) {
				$label = '<span>' . $label . '</span>';
				$label .= ' <a href="' . Link::create_link($field['agree_link'], false, true) . '" target="_blank">' . $field['agree_link_text'] . '</a>';
			}
			$form['fields']['field_' . $key] = [
				'label' => $label,
				'fieldname' => isset($field['name']) && $field['name'] ? $field['name'] : 've_custom_form_field_' . $key,
				'defaultfield' => '',
				'required' => isset($field['required']) ? '1' : '',
				'customfield_type' => $field['type'],
			];
			if (isset($field['email']) && (isset($field['type']) && $field['type'] === 'text')) {
				$form['fields']['field_' . $key]['email'] = 1;
			}
			if (isset($use_form[$key]['subitems'])) {
				foreach ($use_form[$key]['subitems'] as $f_key => $f_val) {
					if ($f_val['text']) {
						$form['fields']['field_' . $key]['options']['item'][$f_key]['id'] = $f_val['text'];
						$form['fields']['field_' . $key]['options']['item'][$f_key]['name'] = $f_val['text'];
						$form['fields']['field_' . $key]['options']['item'][$f_key]['order'] = $f_key;
					}
				}
			} else {
				$form['fields']['field_' . $key]['options'] = [];
			}
		}
	} else {
		// default for templates
		$custom = true;

		$form = [];
		$form['url'] = '';
		$form['submit'] = __('Odeslat', 'cms_ve');
		$form['fields']['field[df_emailaddress]'] = [
			'label' => __('Vložte svůj e-mail', 'cms_ve'),
			'fieldname' => 'cms_email',
			'defaultfield' => '',
			'required' => '',
		];
		$vePage->display->add_element_info(__('Formulář není funkční. Propojte jej s jedním z podporovaných e-mail marketingových nástrojů a vyberte formulář, který chcete použít, nebo vytvořte svůj vlastní formulář.', 'cms_ve'));
	}

	// print custom form
	if ($custom) {
		$content .= $vePage->print_form($element, $form, $css_id);
	}

	if (isset($element['style']['popup'])) {
		$vePage->display->add_enqueue_script('ve_lightbox_script');
		$vePage->display->add_enqueue_style('ve_lightbox_style');

		if (!isset($element['style']['popup_type'])) {
			$type = isset($element['style']['text_link']) ? 'link' : 'button';
		} else {
			$type = $element['style']['popup_type'];
		}

		if ($type == 'link') {
			$vePage->display->element_css->addStyles([
				'font' => $element['style']['link_font'],
			], $css_id . ' .open_element_lightbox');

			$but_content = '<a class="open_element_lightbox" href="#" data-popup="' . $css_id . '_popup">' . ($element['style']['link_text'] ?? $element['style']['popup_text']) . '</a>';
		} elseif ($type == 'image') {
			$image = new Image($element['style']['image'] ?: []);

			$but_content = '<a class="open_element_lightbox element_image" href="#" data-popup="' . $css_id . '_popup">'
				. $image->printImg([
					'col_divisor' => Image::getColDivisor($col_type),
					'full_row' => MWPageDisplay::isFullWidthRow($row_set),
				])
				. '</a>';
		} else {
			$but_set = [
				'style' => $element['style']['popupbutton'] ?? [],
				'text' => $element['style']['popup_text'],
				'attrs' => 'data-popup="' . $css_id . '_popup"',
			];

			$but_content = Button::createButton(
				$but_set,
				$vePage->display->element_css,
				'open_element_lightbox',
				$css_id . ' .open_element_lightbox',
				$added,
				$edit_mode
			);
		}
		if ($added) {
			$content .= "<script>
            jQuery(function() {
              mwGetIframeContent().mw_init_element_popup('" . $css_id . " .open_element_lightbox');
            });
          </script>";
		}

		if (!isset($element['style']['textinpopup'])) {
			$element['style']['textinpopup'] = '';
		}

		$content = '
        <div class="ve_pupup_button_container ve_' . ($element['style']['align'] ?? 'center') . '">' . $but_content . '</div>'
		. '<div style="display: none;">'
		. '<div id="' . str_replace('#', '', $css_id) . '_popup" class="popup_form_container">'
		. $vePage->display->printContentContainer(stripslashes($element['style']['popup_title']), 'popup_form_title title_element_container', 'p')
		. $vePage->display->printContentContainer(nl2br(stripslashes($element['style']['textinpopup'])), 'popup_form_text', 'p')
		. $content
		. '</div>'
		. '</div>';
	}
	if ($added) {
		$content .= "<script>
        jQuery(function() {
          mwGetIframeContent().mw_init_form('" . $css_id . " .ve_check_form');
        });
      </script>";
	}

	$content = '<div class="in_element_content ve_form_element_labels_' . $element['style']['form-labels'] . '">' . $content . '</div>';

	return $content;
}

function ve_element_contactform($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;


	$form_style = $element['style']['form-appearance'] ?? 3;

	$content = '';

	$vePage->display->element_css->addStyles(['font' => $element['style']['form-font'], 'background-color' => $element['style']['background']], $css_id . ' .ve_form_field');

	$thx_url = Link::create_link($element['style']['thx_url'] ?? '', false);

	$class = 've_form_field';
	$class .= Colors::isLightColor($element['style']['background']) ? ' light_color' : ' dark_color';

	$reCaptchaClass = MWRecaptcha()->isActive() ? ' g-recaptcha-form' : '';
	$content .= '<form action="" method="post" class="in_element_content ve_content_form ve_contact_form ve_contact_form_' . $form_style . ' ' . (isset($element['style']['corners']) ? 've_form_corners_' . $element['style']['corners'] : '') . ' ve_form_input_style_' . $element['style']['form-style'] . $reCaptchaClass . '">

        <div class="ve_form_message">
            <svg class="error_icon" role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-x-circle"></use></svg>
            <svg class="ok_icon" role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-check-circle"></use></svg>
            <span></span>
        </div>

        <div class="cms_nodisp"><input tabindex="-1" type="text" name="send_email" value="" autocomplete="' . wp_generate_password(12, false) . '" /></div>
        <div class="cms_nodisp"><input tabindex="-1" type="text" name="contact_sended" value="' . base64_encode(current_time('timestamp')) . '" /></div>
        <div class="ve_form_row ve_form_row_half"><input class="' . $class . '" type="text" name="contact_name" placeholder="' . __('Jméno', 'cms_ve') . '" /></div>
        <div class="ve_form_row ve_form_row_half_r"><input class="ve_form_required ve_form_email ' . $class . '" type="text" name="contact_email" placeholder="' . __('E-mail (povinný)', 'cms_ve') . '" /></div>';
	if (!isset($element['style']['hide']['phone'])) {
		$content .= '<div class="ve_form_row cms_clear"><input class="' . $class . '" type="text" name="contact_phone" placeholder="' . __('Telefon', 'cms_ve') . '" /></div>';
	}
	if ($thx_url) {
		$content .= '<input type="hidden" name="form_redirect_url" value="' . $thx_url . '" data-target="' . ($vePage->edit_mode ? 'parent' : '') . '">';
	}
	$content .= '<div class="ve_form_row cms_clear"><textarea class="ve_form_required ' . $class . '" name="contact_text" rows="4" placeholder="' . __('Zpráva', 'cms_ve') . '"></textarea></div>';

	$showRecaptcha = MWRecaptcha()->isActive();
	$consents = GDPR::printConsent('contact');

	if ($consents || $showRecaptcha) {
		$content .= '<div class="mw_field_contact_form_consents">';

		$content .= $consents;

		if ($showRecaptcha) {
			$content .= MWRecaptcha()->getPrivacyConsentText();
		}

		$content .= '</div>';
	}

	$but_set = [
		'style' => $element['style']['button'] ?? [],
		'text' => $element['style']['button_text'],
		'loading' => true,
		'tag' => 'button',
		'attrs' => 'type="submit"',
	];

	$content .= '<div class="ve_contact_form_buttonrow">';
	$content .= Button::createButton(
		$but_set,
		$vePage->display->element_css,
		'',
		$css_id . ' .ve_content_button',
		$added,
		$edit_mode
	);
	$content .= '</div>';
	$content .= '<input type="hidden" name="data" value="' . base64_encode(serialize(['email' => $element['style']['email']])) . '" />';
	// Input on the next line is just "honeypot" for spambots. It is hidden by CSS.
	$content .= '<div class="cms_nodisp"><input type="text" name="fax" style="display:none !important; width: 0 !important;" tabindex="-1" autocomplete="nope"></div>';
	$content .= '</form>';

	if (!$element['style']['email'] || $element['style']['email'] == '@') {
		$vePage->display->add_element_info(__('Formulář není funkční. Nastavte emailovou adresu na kterou se mají dotazy zasílat.', 'cms_ve'));
	}

	if ($added) {
		$content .= "<script>
        jQuery(function() {
          mwGetIframeContent().mw_init_contact_form('" . $css_id . " .ve_contact_form');
        });
      </script>";
	}

	return $content;
}

function ve_element_menu($element, $css_id, $post_id)
{
	global $vePage;

	$menu = '';
	$content = '';

	if ($element['style']['type'] == 'subpage') {
		$parent = $element['style']['page'] ?: $post_id;

		$args = [
			'sort_order' => 'ASC',
			'sort_column' => 'menu_order',
			'echo' => 0,
			'title_li' => '',
			'child_of' => $parent,
		];

		$menu = wp_list_pages($args);
		if ($menu) {
			$menu = '<ul class="menu">' . $menu . '</ul>';
		}
	} else {
		if (isset($element['style']['menu']) && wp_get_nav_menu_items($element['style']['menu'])) {
			$menu .= wp_nav_menu(['menu' => $element['style']['menu'], 'echo' => false]);
		} else {
			$vePage->display->add_element_info(__('Není vybráno žádné menu.', 'cms_ve'), 'info');
		}
	}

	if ($menu) {
		$vePage->display->element_css->addStyles(['font' => $element['style']['font'] ?? ''], $css_id . ' li');
		$vePage->display->element_css->addStyles(['font' => $element['style']['title_font'] ?? ''], $css_id . ' .menu_element_title');

		if ($element['style']['menu_style'] == 3) {
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' li:hover > a:hover' => ['background-color'],
					$css_id . ' li.current_page_item > a' => ['background-color'],
				],
				'--active-color-' . $css_id,
				$element['style']['color-active'],
				true
			);
		} elseif ($element['style']['menu_style'] != 5) {
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' li > a:hover' => ['color'],
					$css_id . ' li.current_page_item > a' => ['color'],
				],
				'--active-color-' . $css_id,
				$element['style']['color-active'],
				true
			);
		}

		$bg_class = '';
		if (isset($element['style']['background_set']) && ($element['style']['menu_style'] == '9' || $element['style']['menu_style'] == '2')) {
			$bg_class = $vePage->display->create_background_set_class($element['style']['background_set'], $css_id, ' .mw_menu_element');
		}

		$content = '<div class="mw_menu_element menu_element_type' . $element['style']['menu_style'] . ' ' . $bg_class . '">';

		if ($element['style']['menu_style'] == 5) {
			$content .= '<div class="menu_top">';
		}
		$content .= $vePage->display->printContentContainer(stripslashes($element['style']['title']), 'menu_element_title ' . $vePage->display->get_font_class($element['style']['title_font'], 'subtitle'));
		if ($element['style']['menu_style'] == 5) {
			$content .= '</div>';
		}

		$content .= $menu;

		if ($element['style']['menu_style'] == 5) {
			$content .= '<div class="menu_bottom"></div>';
		}
		$content .= '</div>';
	} else {
		$vePage->display->add_element_info(__('Menu je prázdné.', 'cms_ve'));
	}

	return $content;
}

function ve_element_twocols($element, $css_id, $post_id, $edit_mode, $added)
{
	$css_id = str_replace('element_', 'element_sub_', $css_id);
	$css_id = str_replace('#', '', $css_id);

	global $vePage;

	$class = '';
	if ($vePage->edit_mode) {
		$class .= ' elements_container';
		if (!isset($element['content'][0]) && !isset($element['content'][1])) {
			$class .= ' empty_container';
		}
	}

	$content = '<div class="subcol_container in_element_twocols ' . $class . '">';
	// col 1
	$content .= '<div class="subcol subcol-first subcol-two sortable-col">';
	if ($edit_mode) {
		$content .= '<div class="mw_page_builder_droparea mw_page_builder_droparea_element" data-group="subelement">'
		. '<div class="mw_page_builder_emptyelement_info admin_feature">+</div>'
		. '</div>';
	}
	if (isset($element['content'][0]) && is_array($element['content'][0])) {
		$i = 0;
		foreach ($element['content'][0] as $subelement) {
			$content .= $vePage->display->generate_element($subelement, $css_id . '_0_' . $i, $post_id, $edit_mode, '', $added);
			$i++;
		}
	}
	//if($edit_mode) $content.=$vePage->generate_new_element_but(1);
	// col 2
	$content .= '</div><div class="subcol subcol-last subcol-two sortable-col">';
	if ($edit_mode) {
		$content .= '<div class="mw_page_builder_droparea mw_page_builder_droparea_element" data-group="subelement">'
		. '<div class="mw_page_builder_emptyelement_info admin_feature">+</div>'
		. '</div>';
	}
	if (isset($element['content'][1]) && is_array($element['content'][1])) {
		$i = 0;
		foreach ($element['content'][1] as $subelement) {
			$content .= $vePage->display->generate_element($subelement, $css_id . '_1_' . $i, $post_id, $edit_mode, '', $added);
			$i++;
		}
	}
	//if($edit_mode) $content.=$vePage->generate_new_element_but(1);
	$content .= '</div></div>';

	return $content;
}

function ve_element_box($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$vePage->display->subelement_css[$css_id]->addStyles(
		[
			'background-color' => $element['style']['background_color']['rgba1'] ?? '',
			'bg' => ['background_image' => $element['style']['background_image'] ?? ''],
			'_border' => $element['style']['border'],
			//'font'=>$element['style']['font'],
		],
		$css_id . ' .ve_content_block'
	);

	$size = Image::getLayoutImageSize(Image::getColDivisor($col_type), null, $edit_mode, false, 2);

	$vePage->display->subelement_css[$css_id]->addBgStyle($element['style']['background_image'] ?? [] ?: [], $css_id . ' .ve_content_block', $edit_mode, $size);

	$vePage->display->subelement_css[$css_id]->addStyles(
		[
			'padding' => isset($element['style']['padding']) ? $element['style']['padding']['size'] . 'px' : '',
		],
		$css_id . ' .ve_content_block_content'
	);

	$vePage->display->subelement_css[$css_id]->addStyles(
		[
			'font' => $element['style']['title-font'],
			'background-color' => isset($element['style']['title_bg']) ? $element['style']['title_bg']['rgba1'] : '',
			'border-bottom' => isset($element['style']['title_border']) ? $element['style']['title_border']['size'] . 'px solid ' . $element['style']['title_border']['rgba'] : '',
		],
		$css_id . ' .ve_content_block_title'
	);

	$class = '';

	//$class=Colors::isLightColor($element['style']['background_color']['rgba1'])?' light_color':' dark_color';
	if (!isset($element['style']['background_color']) || $element['style']['background_color']['rgba1'] == '') {
	} elseif (isset($element['style']['background_color']) && Colors::isLightColor($element['style']['background_color']['rgba1']) && !(isset($element['style']['background_image']) && isset($element['style']['background_image']['image']) && $element['style']['background_image']['image'])) {
		$class .= ' light_color';
	} else {
		$class .= ' dark_color';
	}

	$titleclass = isset($element['style']['title_bg']) && Colors::isLightColor($element['style']['title_bg']['color1']) ? ' light_color' : ' dark_color';

	$class .= isset($element['style']['text']) ? ' text_' . $element['style']['text'] : ' text_auto';

	if (isset($element['style']['corner']) && $element['style']['corner']) {
		$class .= ' mw_element_item_corners' . $element['style']['corner'];
	}
	if (isset($element['style']['shadow']) && $element['style']['shadow']) {
		$class .= ' mw_element_item_shadow' . $element['style']['shadow'];
	}

	if (isset($element['style']['background_image']['image']) && $element['style']['background_image']['image']) {
		if ($element['style']['background_image']['overlay_color']['rgba'] && isset($element['style']['background_image']['color_filter'])) {
			$vePage->display->subelement_css[$css_id]->addStyles(['background-color' => $element['style']['background_image']['overlay_color']['rgba']], $css_id . ' .ve_content_block .background_overlay');
		}

		if (isset($element['style']['background_image']['cover']) && $element['style']['background_image']['cover']) {
			$class .= ' background_cover';
		}
	}

	if ($vePage->edit_mode) {
		$class .= ' elements_container';
		if (!isset($element['content'][0])) {
			$class .= ' empty_container';
		}
	}

	$content = '<div class="ve_content_block' . $class . '">';
	$content .= '<div class="background_overlay"></div>';

	$box_title = isset($element['style']['title']) ? stripslashes($element['style']['title']) : '';
	$content .= $vePage->display->printContentContainer($box_title, 've_content_block_title ' . $titleclass . ' ' . $vePage->display->get_font_class($element['style']['title-font']), 'h3');

	$content .= '<div class="subcol subcol-first ve_content_block_content">';
	if ($edit_mode) {
		$content .= '<div class="sortable-col subcol">'
		. '<div class="mw_page_builder_droparea mw_page_builder_droparea_element" data-group="subelement">'
		. '<div class="mw_page_builder_emptyelement_info admin_feature">+</div>'
		. '</div>';
	}

	if (isset($element['content'][0]) && is_array($element['content'][0])) {
		$css_id = str_replace('#element_', 'element_sub_', $css_id);
		$css_id = str_replace('#', '', $css_id);
		$i = 0;
		foreach ($element['content'][0] as $subelement) {
			$content .= $vePage->display->generate_element($subelement, $css_id . '_0_' . $i, $post_id, $edit_mode, '', $added);
			$i++;
		}
	}

	if ($edit_mode) {
		$content .= '</div>'; //$content.=$vePage->generate_new_element_but(1);
	}

	$content .= '</div>';

	$content .= '</div>';

	return $content;
}

// Countdown

function ve_element_countdown($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;

	$vePage->display->add_enqueue_script('ve_countdown_script');
	$vePage->display->add_enqueue_style('ve_countdown_style');

	$content = '';

	if (!isset($element['style']['background-color'])) {
		$element['style']['background-color'] = '#333333';
	} elseif (!$element['style']['background-color'] && ($element['style']['style'] == '1' || $element['style']['style'] == '3' || $element['style']['style'] == '6')) {
		$element['style']['background-color'] = '#333333';
	}

	$vePage->display->element_css->addVariableStyles(
		[
			$css_id . ' .ve_countdown .count_time:after' => ['color'],
			$css_id . ' .ve_countdown_5 .position' => ['color'],
			$css_id . ' .ve_countdown_7 span' => ['color'],
			$css_id . ' .ve_countdown_3 .count_time, ' . $css_id . ' .ve_countdown_6 .count_time' => ['background-color'],
			$css_id . ' .ve_countdown_1 .digit' => ['background-color'],
		],
		'--countdown-background-' . $css_id,
		$element['style']['background-color']
	);
	$vePage->display->element_css->addStyles(
		[
			'font-size' => $element['style']['size'] . 'px',
		],
		$css_id . ' .ve_countdown'
	);

	if ($element['style']['style'] === '5') {
		$mobileSize = '40';
	} elseif ($element['style']['style'] === '7') {
		$mobileSize = '16';
	} else {
		$mobileSize = '25';
	}
	$mobileSize = isset($element['style']['mobile']['size']) && $element['style']['mobile']['size'] ? $element['style']['mobile']['size'] : $mobileSize;
	$vePage->display->element_css->addMobileStyles(['font-size' => $mobileSize . 'px'], $css_id . ' .ve_countdown');

	if (isset($element['style']['tablet']['size']) && $element['style']['tablet']['size']) {
		$vePage->display->element_css->addTabletStyles(['font-size' => $element['style']['tablet']['size'] . 'px'], $css_id . ' .ve_countdown');
	}

	if ($element['style']['style'] == '1' || $element['style']['style'] == '5' || $element['style']['style'] == '6') {
		$vePage->display->element_css->addStyles(
			[
				'color' => isset($element['style']['font-text']['color']) && $element['style']['font-text']['color'] ? $element['style']['font-text']['color'] : '',
			],
			$css_id . ' .ve_countdown .position_title'
		);
	}

	$color_class = Colors::isLightColor($element['style']['background-color']) ? ' light_countdown_color' : ' dark_countdown_color';

	if (!isset($element['style']['content']) || !is_array($element['style']['content']) || $element['style']['content']['date'] === '' || $element['style']['content']['date'] === '00000') {
		$time = strtotime('today midnight');
		if (isset($element['style']['content']) && ($element['style']['content']['hour'] || $element['style']['content']['minute'])) {
			$h = $element['style']['content']['hour'] ?: 0;
			$m = $element['style']['content']['minute'] ?: 0;
		} else {
			$h = 23;
			$m = 59;
		}
		$time += ($h * 3600 + $m * 60);
	} else {
		$time = strtotime($element['style']['content']['date']);
		$h = $element['style']['content']['hour'];
		$m = $element['style']['content']['minute'];
		$time += ($h * 3600 + $m * 60);
	}

	$enterTime = null;

	if (((isset($element['style']['evergreen_days']) &&
		$element['style']['evergreen_days'] != '') ||
		(isset($element['style']['evergreen_minutes']) &&
		$element['style']['evergreen_minutes'] != '') ||
		(isset($element['style']['evergreen_hours']) &&
		$element['style']['evergreen_hours'] != '')) &&
		isset($element['style']['countdown_type']) &&
		($element['style']['countdown_type'] == 'page' ||
		$element['style']['countdown_type'] == 'campaign')
	) {
		$enterTime = $time = current_time('timestamp');
		if ($element['style']['countdown_type'] == 'page') {
			if ($post_id && isset($_COOKIE['mw_page_access_' . $post_id])) {
				$enterTime = $time = (int) $_COOKIE['mw_page_access_' . $post_id];
			}
		} elseif ($element['style']['countdown_type'] == 'campaign') {
			if (function_exists('MWF')) {
				if ($added) {
					MWF()->init($post_id);
				}

				if (MWF()->getAccessTime() && MWF()->is_funnel_page) {
					$time = MWF()->getAccessTime();
				}
			} elseif (isset($_COOKIE['mioweb_campaign_access'])) {
				$campaign_id = get_post_meta($post_id, 'mioweb_campaign', true);
				$access = unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
				if (isset($campaign_id['campaign'])) {
					$time = $access['time'][$campaign_id['campaign']];
				}
			}
		}
		if (isset($element['style']['evergreen_days']) && $element['style']['evergreen_days'] != '') {
			$time += ($element['style']['evergreen_days'] * 3600 * 24);
		}

		$h = 0;
		$m = 0;

		if (isset($element['style']['evergreen_hours']) && $element['style']['evergreen_hours'] != '') {
			$h = (int) $element['style']['evergreen_hours'];
		}

		if (isset($element['style']['evergreen_minutes']) && $element['style']['evergreen_minutes'] != '') {
			$m = (int) $element['style']['evergreen_minutes'];
		}
		if (isset($element['style']['evergreen_start']) && $element['style']['evergreen_start'] == 'mid') {
			$time = strtotime(date('d.m.Y', $time));
			$time += 24 * 3600;
			$time += ($h * 3600 + $m * 60);
		} elseif (isset($element['style']['evergreen_start']) && $element['style']['evergreen_start'] == 'start') {
			$time = strtotime(date('d.m.Y', $time));
			$time += ($h * 3600 + $m * 60);

			if (current_time('timestamp') > $time) {
				$time += 24 * 3600;
			}
		} else {
			$time += ($h * 3600 + $m * 60);
		}

		$interval = $time - current_time('timestamp');
	} else {
		$timestamp = new DateTimeImmutable();
		$timestamp = $timestamp->setTimestamp($time);
		$interval = $timestamp->getTimestamp() - current_time('timestamp');
	}

	if (isset($element['style']['redirect'])) {
		$redirect_link = Link::create_link($element['style']['redirect']);
		if ($redirect_link == get_permalink($post_id)) {
			$redirect_link = '';
		}
	} else {
		$redirect_link = '';
	}

	$text_before = isset($element['style']['text_before']) && $element['style']['text_before'] && $element['style']['style'] == 7 ? $element['style']['text_before'] : '';
	$show_text_before = $element['style']['style'] == 7 && ($text_before || $vePage->edit_mode) ? 1 : 0;

	$limitOfCountdown = 1000 * 24 * 3600; //maximum time the countdown can count is 1000 days in the future

	if ($interval >= $limitOfCountdown) {
		$vePage->display->add_element_info(__('Odpočet lze nastavit maximálně 999 dnů do budoucnosti.', 'cms_ve'));
		$interval = 0;
	}

	$attr = ' data-time_diff="' . $interval . '"';
	$attr .= ' data-color="' . $color_class . '"';
	$attr .= ' data-redirect="' . ($redirect_link && !$vePage->edit_mode ? $redirect_link : '') . '"';
	$attr .= ' data-before="' . $text_before . '"';
	$attr .= ' data-show_text="' . $show_text_before . '"';
	$attr .= ' data-set_cookie="' . ($element['style']['countdown_type'] == 'page' && !isset($_COOKIE['mw_page_access_' . $post_id]) ? 1 : 0) . '"';
	$attr .= ' data-page_id="' . $post_id . '"';
	$attr .= ' data-cookie_time="' . ($enterTime ?? '') . '"';

	$content .= '<div class="ve_countdown ve_countdown_' . $element['style']['style'] . ' ' . $color_class . ' ' . $vePage->display->get_font_class($element['style']['font'], 'text') . '" ' . $attr . '></div>';

	if ($added) {
		$content .= '<script>' .
			'jQuery(function(){' .
			'mwGetIframeContent().mw_init_countdown("' . $css_id . ' .ve_countdown");' .
			'});' .
			'</script>';
	}

	return $content;
}

// Testimonials

function ve_element_testimonials($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;
	$class = '';
	$carousel_set = '';
	$content = '';

	if (isset($element['style']['testimonials'])) {
		$style = 1;
		if (in_array($element['style']['style'], ['7', '8', '9'])) {
			$style = 1;
		} elseif (in_array($element['style']['style'], ['10', '6', '3'])) {
			$style = 2;
		} elseif (in_array($element['style']['style'], ['1', '11', '2'])) {
			$style = 3;
		} elseif (in_array($element['style']['style'], ['4', '5'])) {
			$style = 4;
		} elseif (in_array($element['style']['style'], ['12', '13'])) {
			$style = 5;
		}

		if (isset($element['style']['use_slider'])) {
			$vePage->display->add_enqueue_script('ve_miocarousel_script');
			$vePage->display->add_enqueue_style('ve_miocarousel_style');
			if ($vePage->display->is_mobile) {
				$element['style']['cols'] = 'one';
			}

			$class .= ' miocarousel miocarousel_style_1';
			if ($element['style']['miocarousel_setting']['color_scheme']) {
				$class .= ' miocarousel_' . $element['style']['miocarousel_setting']['color_scheme'];
			}

			if (isset($element['style']['miocarousel_setting']['autoplay'])) {
				$carousel_set .= ' data-autoplay="1"';
			} else {
				$carousel_set .= ' data-autoplay="0"';
			}
			if ($element['style']['miocarousel_setting']['delay']) {
				$carousel_set .= ' data-duration="' . $element['style']['miocarousel_setting']['delay'] . '"';
			}
			if ($element['style']['miocarousel_setting']['speed']) {
				$carousel_set .= ' data-speed="' . $element['style']['miocarousel_setting']['speed'] . '"';
			}
			if ($element['style']['miocarousel_setting']['animation'] && $element['style']['miocarousel_setting']['animation'] != 'fade') {
				$carousel_set .= ' data-animation="' . $element['style']['miocarousel_setting']['animation'] . '"';
			}

			if (isset($element['style']['miocarousel_setting']['hide_navigation'])) {
				$carousel_set .= ' data-indicators="0"';
			} else {
				$carousel_set .= ' data-indicators="1"';
			}
		}

		if (isset($element['style']['background_set'])) {
			$bg_class = $vePage->display->create_background_set_class($element['style']['background_set'], $css_id, ' .ve_content_testimonial_box');
			$arrow_class = $vePage->display->create_background_set_arrow_class($element['style']['background_set'], $css_id);
		} else {
			$bg_class = 'mw_element_item_shadow1 mw_element_item_borders';
			$arrow_class = '';
		}

		$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .ve_content_testimonial_text');
		$vePage->display->element_css->addStyles(['font' => $element['style']['font-author'] ?? ''], $css_id . ' .ve_content_testimonial_name');

		if (isset($element['style']['image_size']) && $element['style']['image_size']) {
			$class .= ' ve_content_testimonial_img' . $element['style']['image_size'];
		}

		switch ($element['style']['cols']) {
			case 'one':
				$cols = 1;

				break;
			case 'two':
				$cols = 2;

				break;
			case 'three':
				$cols = 3;

				break;
			case 'four':
				$cols = 4;

				break;
		}

		$content .= '<div class="in_element_content ' . $class . ' ve_content_testimonial ve_content_testimonial_s' . $style . ' ve_content_testimonial_' . $element['style']['style'] . ' cols cols-' . $cols . '" ' . $carousel_set . '>';

		if (isset($element['style']['use_slider'])) {
			$content .= '<div class="miocarousel-inner">';
		}

		$el_rows = array_chunk($element['style']['testimonials'], $cols);

		$tes_class = '';
		$class = '';
		$tes_text_class = '';

		if (in_array($element['style']['style'], ['8', '9', '10', '11', '12', '13'])) {
			$tes_class = 've_content_testimonial_box ' . $bg_class;
		} elseif (in_array($element['style']['style'], ['3', '2'])) {
			$tes_text_class = 've_content_testimonial_box ' . $bg_class;
		} elseif (in_array($element['style']['style'], ['4'])) {
			$class = 've_content_testimonial_box ' . $bg_class;
		}

		$arrow = '<div class="mw_box_arrow"><div class="arrow ' . $arrow_class . '"></div></div>';

		$row_num = 1;
		$i = 0;
		foreach ($el_rows as $row) {
			if (isset($element['style']['use_slider'])) {
				$row_class = 'mw_element_row slide';
				if ($row_num == 1) {
					$row_class .= ' active';
				}
			} else {
				$row_class = 'mw_element_row';
			}

			$content .= '<div class="' . $row_class . '">';

			foreach ($row as $testimonial) {
				// new id in images compatibility (temporary)
				if (isset($testimonial['image']) && !is_array($testimonial['image'])) {
					$testimonial['image'] = ['image' => $testimonial['image']];
				}
				// end temporary


				if (isset($testimonial['image']['position'])) {
					$vePage->display->element_css->addStyles(['object-position' => $testimonial['image']['position']], $css_id . ' .ve_content_testimonial_item_' . $i . ' img');
				}
				$img_content = '';
				$image = new Image($testimonial['image']);
				if (!$image->isEmpty()) {
					$img_content = '<div class="ve_content_testimonial_img_container rounded "><div class="mw_image_ratio mw_image_ratio_11 ">';
					$img_content .= $image->printImg([
						'max_width' => 150,
						'size' => 'medium',
					], '', $edit_mode);
					$img_content .= '</div></div>';
				}

				$content .= '<div class="col col-' . $element['style']['cols'] . ' ve_content_testimonial_item ve_content_testimonial_item_' . $i . ' ' . $class . ' ' . ($testimonial['image']['image'] ? ' wimg' : '') . '">';

				if ($style == '5') {
					$content .= $img_content;
				}

				$content .= '<blockquote class="' . $tes_class . '">';

				if ($style == '1' || $style == '4') {
					$content .= $img_content;
				}

				$content .= '<div class="ve_content_testimonial_text ' . $tes_text_class . '">';
				$content .= stripslashes($testimonial['text']);
				if ($element['style']['style'] == '2' || $element['style']['style'] == '3') {
					$content .= $arrow;
				}
				$content .= '</div>';

				$content .= '<div class="ve_content_testimonial_author">';

				if ($style == '2' || $style == '3') {
					$content .= $img_content;
				}

				$content .= '<div class="ve_content_testimonial_name">'
				. '<span class="ve_content_testimonial_author_name">' . stripslashes($testimonial['name']) . '</span>'
				. $vePage->display->printContentContainer(stripslashes($testimonial['company']), 've_content_testimonial_company', 'span')
				. '</div><div class="cms_clear"></div>';

				$content .= '</div>';

				if (($element['style']['style'] == '12' || $element['style']['style'] == '13') && $testimonial['image']['image']) {
					$content .= $arrow;
				}

				$content .= '</blockquote>';

				$content .= '</div>';

				$i++;
			}

			$content .= '</div>';
			$row_num++;
		}

		if (isset($element['style']['use_slider'])) {
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
		}

		$content .= '</div><div class="cms_clear"></div>';
	} else {
		$vePage->display->add_element_info(__('Seznam referencí je prázdný.', 'cms_ve'), 'info');
	}

	return $content;
}

// Features

function ve_element_features($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$content = '';

	if (!empty($element['style']['features'])) {
		switch ($element['style']['cols']) {
			case 'auto':
				$cols_set = 0;

				break;
			case 'one':
				$cols_set = 1;

				break;
			case 'two':
				$cols_set = 2;

				break;
			case 'three':
				$cols_set = 3;

				break;
			case 'four':
				$cols_set = 4;

				break;
			case 'five':
				$cols_set = 5;

				break;
		}

		$cols = $vePage->display->getAutoCols($cols_set, count($element['style']['features']), 3, false);

		$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' h3');
		$vePage->display->element_css->addStyles(['font' => $element['style']['font_text'] ?? ''], $css_id . ' .mw_feature_description');
		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .mw_feature_icon' => ['font-size'],
				$css_id . ' .feature_col_image img' => ['max-width'],
			],
			'--icon-size-' . $css_id,
			$element['style']['icon_size'] . 'px'
		);
		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .mw_icon' => ['color'],
				$css_id . ' .mw_icon_style_2' => ['background-color'],
				$css_id . ' .mw_icon_style_3' => ['background-color'],
				$css_id . ' .mw_icon_style_4' => ['border-color'],
				$css_id . ' .mw_icon_style_5' => ['border-color'],
			],
			'--icon-color-' . $css_id,
			$element['style']['background-color']
		);

		$icon_class = Colors::isLightColor($element['style']['background-color']) ? ' light_color' : ' dark_color';

		$rows = array_chunk($element['style']['features'], $cols);

		$features_class = '';
		if ($element['style']['style'] != '4') {
			$features_class = 'mw_element_rows_b';
		} elseif (isset($element['style']['cols_type'])) {
			$features_class .= ' ' . $element['style']['cols_type'];
		}

		$bg_class = '';
		if (isset($element['style']['background_set']) && $element['style']['style'] == '4') {
			$bg_class = $vePage->display->create_background_set_class($element['style']['background_set'], $css_id, ' .mw_feature');
		}

		if (!$cols_set) {
			$features_class .= ' mw_element_autocols';
		}

		$content .= '<div class="in_element_content in_features_element in_features_element_' . $element['style']['style'] . ' cols cols-' . $cols . ' ' . $features_class . '">';

		foreach ($rows as $row) {
			$content .= '<div class="mw_element_row">';

			foreach ($row as $key => $feature) {
				$img = !isset($feature['icon']['tab']) || $feature['icon']['tab'] == 'icon' ? 'icon' : 'image';

				$content .= '<div class="col mw_feature feature_col_' . $img . ' col-' . $cols . ' ' . $bg_class . '">';

				$url_link = isset($feature['link']) ? Link::create_link($feature['link']) : '';

				if ($url_link) {
					$target = isset($feature['link']['target']) && $feature['link']['target'] == 1 ? 'target="_blank"' : '';
					$link = '<a class="mw_feature_title_link" href="' . $url_link . '" ' . $target . '>';
					$link_icon = '<a class="mw_feature_icon" href="' . $url_link . '" ' . $target . '>';
					$link_close = '</a>';
					$link_icon_close = '</a>';
				} else {
					$target = '';
					$link = '';
					$link_icon = '<div class="mw_feature_icon">';
					$link_close = '';
					$link_icon_close = '</div>';
				}

				if ($img == 'icon') {
					$content_icon = $link_icon . '<div class="mw_icon mw_icon_style_' . $element['style']['icon_style'] . ' ' . $icon_class . '"><i><svg role="img"><use xlink:href="' . MW_ICONS_URL . $feature['icon']['icon_set'] . '/symbol-defs.svg#icon-' . $feature['icon']['icon'] . '"></use></svg></i></div>' . $link_icon_close;
				} else {
					// new id in images compatibility (temporary)
					if (isset($feature['icon']['image']) && !is_array($feature['icon']['image'])) {
						$feature['icon']['image'] = ['image' => $feature['icon']['image']];
					}

					$image = new Image($feature['icon']['image']);

					$divisorMultiplier = $element['style']['cols'] === 'auto' && $col_type !== 'col-one' ? 1 : $cols;

					$content_icon = !$image->isEmpty() ? '<div class="mw_feature_icon">'
							. $link
							. $image->printImg([
								'max_width' => $element['style']['icon_size'],
								'col_divisor' => Image::getColDivisor($col_type) * $divisorMultiplier,
								'full_row' => MWPageDisplay::isFullWidthRow($row_set),
							], '', $edit_mode)
							. $link_close
							. '</div>' : '';
				}

				$content_text = '<div class="feature_text">';
				$content_text .= $vePage->display->printContentContainer(stripslashes($feature['title']), $vePage->display->get_font_class($element['style']['font']), 'h3', $link, $link_close);
				$content_text .= $vePage->display->printContentContainer(stripslashes($feature['text']), 'mw_feature_description ' . $vePage->display->get_font_class($element['style']['font_text'], 'text'), 'div');

				if (isset($element['style']['show_button']) && ($url_link || $edit_mode)) {
					$but_set = [
						'style' => $element['style']['button'],
						'show' => $element['style']['show'] ?? '',
						'link' => $feature['link'],
						'text' => $feature['button_text'],
						'align' => 'center',
					];
					$content_text .= Button::createButton(
						$but_set,
						$vePage->display->element_css,
						'',
						$css_id . ' .ve_content_button',
						$added,
						$edit_mode
					);
				}
				$content_text .= '</div>';

				if ($element['style']['style'] == '5') {
					$content .= $content_text . $content_icon;
				} else {
					$content .= $content_icon . $content_text;
				}

				$content .= '</div>';
			}

			$content .= '</div>';
		}
		$content .= '</div>';
	} else {
		$vePage->display->add_element_info(__('Seznam vlastností je prázdný.', 'cms_ve'), 'info');
	}

	return $content;
}

// Peoples

function ve_element_peoples($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$content = '';

	if (!empty($element['style']['peoples'])) {
		switch ($element['style']['cols']) {
			case 'auto':
				$cols_set = 0;

				break;
			case 'one':
				$cols_set = 1;

				break;
			case 'two':
				$cols_set = 2;

				break;
			case 'three':
				$cols_set = 3;

				break;
			case 'four':
				$cols_set = 4;

				break;
			case 'five':
				$cols_set = 5;

				break;
		}

		switch ($element['style']['style']) {
			case '2':
			case '1':
				$style = '3';

				break;
			case '3':
				$style = '4';

				break;
			case '4':
				$style = '6';

				break;
			case '5':
				$style = '4b';

				break;
			case '6':
				$style = '1';

				break;
		}

		$cols = $vePage->display->getAutoCols($cols_set, count($element['style']['peoples']), 3, false, $style);

		if ($style == '1') {
			$element['style']['icons_color'] = '#ffffff';
		} elseif (!isset($element['style']['icons_color'])) {
			$element['style']['icons_color'] = '#ababab';
		}

		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .mw_social_icon_bg' => 'color',
			],
			'--social-icon-color-' . $css_id,
			$element['style']['icons_color']
		);
		if (isset($element['style']['font_contacts'])) {
			$vePage->display->element_css->addStyles(['font' => $element['style']['font_contacts']], $css_id . ' .mw_peoples_contacts_container');
		}

		$img_col_size = $element['style']['image_size'] ?? 3;
		$hover_style = $element['style']['hover'] ?? '';
		$image_ratio = $element['style']['style'] == '3' || $element['style']['style'] == '5' || $element['style']['style'] == '6' ? $element['style']['image_ratio'] ?? '32' : '11';

		$text_align = $element['style']['style'] == '4' ? 'left' : 'center';

		$items = [];
		foreach ($element['style']['peoples'] as $item) {
			// new id in images compatibility (temporary)
			if (isset($item['image']) && !is_array($item['image'])) {
				$item['image'] = ['image' => $item['image']];
			}

			// end temporary

			$footer = '';
			$social_icons = '';
			$contacts = '';

			if (isset($item['phone']) && $item['phone']) {
				$contacts .= '<a href="tel:' . $item['phone'] . '">' . mw_content_icon_set('phone') . ' ' . $item['phone'] . '</a>';
			}
			if (isset($item['email']) && $item['email']) {
				$contacts .= '<a href="mailto:' . $item['email'] . '">' . mw_content_icon_set('mail') . ' ' . $item['email'] . '</a>';
			}

			if ($contacts) {
				$soc_class = Colors::isLightColor($element['style']['icons_color']) ? ' light_hover_color' : ' dark_hover_color';
				$footer .= '<div class="mw_peoples_contacts_container ' . $soc_class . '">' . $contacts . '</div>';
			}

			if (isset($item['facebook']) && $item['facebook']) {
				$social_icons .= '<a href="' . $item['facebook'] . '" target="_blank" class="mw_social_icon_bg">' . mw_content_icon('icon-facebook1', 'social-icons.svg') . '</a>';
			}
			if (isset($item['linkedin']) && $item['linkedin']) {
				$social_icons .= '<a href="' . $item['linkedin'] . '" target="_blank" class="mw_social_icon_bg">' . mw_content_icon('icon-linkedin1', 'social-icons.svg') . '</a>';
			}
			if (isset($item['youtube']) && $item['youtube']) {
				$social_icons .= '<a href="' . $item['youtube'] . '" target="_blank" class="mw_social_icon_bg">' . mw_content_icon('icon-youtube1', 'social-icons.svg') . '</a>';
			}
			if (isset($item['twitter']) && $item['twitter']) {
				$social_icons .= '<a href="' . $item['twitter'] . '" target="_blank" class="mw_social_icon_bg">' . mw_content_icon('icon-twitter1', 'social-icons.svg') . '</a>';
			}
			if (isset($item['google']) && $item['google']) {
				$social_icons .= '<a href="' . $item['google'] . '" target="_blank" class="mw_social_icon_bg">' . mw_content_icon('icon-google-plus1', 'social-icons.svg') . '</a>';
			}
			if (isset($item['instagram']) && $item['instagram']) {
				$social_icons .= '<a href="' . $item['instagram'] . '" target="_blank" class="mw_social_icon_bg">' . mw_content_icon('icon-instagram1', 'social-icons.svg') . '</a>';
			}
			if (isset($item['web']) && $item['web']) {
				$social_icons .= '<a href="' . $item['web'] . '" target="_blank" class="mw_social_icon_bg">' . mw_content_icon('icon-web1', 'social-icons.svg') . '</a>';
			}

			if ($social_icons) {
				$soc_class = Colors::isLightColor($element['style']['icons_color']) ? ' light_hover_color' : ' dark_hover_color';
				$footer .= '<div class="mw_social_icons_container mw_social_icons_container_3 ' . $soc_class . '">' . $social_icons . '</div>';
			}

			$args = [
				'link' => Link::create_link($item['link']),
				'target' => isset($item['link']['target']) ? true : false,
				'image' => new Image($item['image']),
				'title' => $item['title'],
				'subtitle' => $item['position'],
				'description' => $item['text'],
				'custom_footer' => $footer,
			];

			if ($style == '1') {
				if ($args['link'] && $footer) {
					$args['image_hover_content'] = '<a href="' . $args['link'] . '" ' . ($args['target'] ? 'target="_blank"' : '') . '><h3 class="title_element_container">' . $item['title'] . '</h3></a>';
				} else {
					$args['image_hover_content'] = '<h3 class="' . $vePage->display->get_font_class($element['style']['font']) . '">' . $item['title'] . '</h3>';
				}
				$args['image_hover_content'] .= '<span class="mw_element_item_subtitle">' . $item['position'] . '</span>';
				$args['image_hover_content'] .= $footer;
				$args['image_hover_link'] = $args['link'] && !$footer ? true : false;
			}

			$items[] = $args;
		}

		$items_args = [
			'style' => $style,
			'cols' => $cols,
			'inside_col_type' => $col_type,
			'autocols' => !$cols_set ? true : false,
			'cols_type' => $element['style']['cols_type'] ?? 'cols',
			'hover_style' => $hover_style,
			'image_ratio' => $image_ratio,
			'align' => $text_align,
			'img_col_size' => $img_col_size,
			'empty_image' => get_bloginfo('template_url') . '/library/visualeditor/images/content/person.png',
			'styles' => [
				'hover_color' => isset($element['style']['hover_color']) ? $element['style']['hover_color']['rgba'] : 'rgba(0,0,0,0,5)',
				'font_title' => $element['style']['font'],
				'font_subtitle' => $element['style']['font_position'],
				'font_description' => $element['style']['font_text'],
			],
			'cssid' => $css_id,
			'added' => $added,
			'slider' => isset($element['style']['use_slider']),
			'slider_setting' => $element['style']['miocarousel_setting'] ?? '',
		];

		if (isset($element['style']['background_set'])) {
			$items_args['background_set'] = $element['style']['background_set'];
		}

		if ($style == '1') {
			$items_args['image_hover'] = true;
			$items_args['hover_content'] = true;
		}

		$content = '<div class="in_element_content in_people_element in_people_element_' . $element['style']['style'] . '">';

		$content .= $vePage->display->generate_element_items($items_args, $items, $added, $row_set);

		$content .= '</div>';
	} else {
		$vePage->display->add_element_info(__('Seznam osob je prázdný.', 'cms_ve'), 'info');
	}

	return $content;
}

// Price list

function ve_element_pricelist($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$content = '';
	$empty = false;

	// rows table
	if (isset($element['style']['pricelist_type']) && $element['style']['pricelist_type'] == 'rows') {
		if (!empty($element['style']['row_pricelist'])) {
			if (isset($element['style']['row_font'])) {
				$vePage->display->element_css->addStyles(['font' => $element['style']['row_font']], $css_id . ' .pricelist_title');
			}

			if (isset($element['style']['row_font_price'])) {
				$vePage->display->element_css->addStyles(['font' => $element['style']['row_font_price']], $css_id . ' .pricelist_price');
			}

			if (isset($element['style']['row_font_desc'])) {
				$vePage->display->element_css->addStyles(['font' => $element['style']['row_font_desc']], $css_id . ' .ve_row_pricelist_desc');
			}

			$content .= '<table class="mw_table mw_table_pricelist in_element_content mw_table_style_' . $element['style']['row_table_style'] . '">';
			$i = 0;
			foreach ($element['style']['row_pricelist'] as $row) {
				$content .= '<tr ' . ($i == 0 ? 'class="even"' : '') . '>
                    <td>
                        <div class="pricelist_title ' . $vePage->display->get_font_class($element['style']['row_font']) . '">' . $row['title'] . '</div>
                        ' . $vePage->display->printContentContainer(stripslashes($row['text']), 've_row_pricelist_desc', 'span') . '
                    </td>
                    <td class="pricelist_price ' . $vePage->display->get_font_class($element['style']['row_font_price'], 'text') . '">
                        <span>' . str_replace(' ', '&nbsp;', $row['price']) . '</span>
                    </td>
                </tr>';
				$i = $i == 0 ? 1 : 0;
			}

			$content .= '</table>';
		} else {
			$empty = true;
		}
	} else {
		// cols table
		if (!empty($element['style']['pricelist'])) {
			$cols = count($element['style']['pricelist']);

			if ($element['style']['style'] == '4') {
				$element['style']['cols_type'] = 'fullcols';
			}

			$vePage->display->element_css->addStyles(['font' => $element['style']['font_title']], $css_id . ' .pricelist_title');
			$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .pricelist_price');
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .pricelist_popular_text' => ['background-color'],
					$css_id . ' .mw_cols_pricelist_2 .pricelist_col_popular .pricelist_title' => ['background-color'],
					$css_id . ' .mw_cols_pricelist_1 .pricelist_col_popular .pricelist_head' => ['border-color'],
				],
				'--popular-color-' . $css_id,
				$element['style']['popular_color']
			);

			$bg_class = '';
			if (isset($element['style']['background_set'])) {
				$bg_class = $vePage->display->create_background_set_class($element['style']['background_set'], $css_id, ' .pricelist_col_bg');
			}

			$content .= '<div class="in_element_content mw_cols_pricelist mw_cols_pricelist_' . $element['style']['style'] . ' ' . $element['style']['cols_type'] . '">';
			$i = 1;
			foreach ($element['style']['pricelist'] as $item) {
				$cols_class = '';
				if (($element['style']['style'] == '4' && isset($item['popular'])) || $element['style']['style'] != '4') {
					$cols_class .= ' pricelist_col_bg' . $bg_class;
				}

				$content .= '<div class="pricelist_col pricelist_col_' . $i . ' col col-' . $cols . ' ' . (isset($item['popular']) ? 'pricelist_col_popular' : '') . ' ' . $cols_class . '">';
				$content .= '<div class="pricelist_content">';
				$content .= '<div class="pricelist_head">';

				$content .= $vePage->display->printContentContainer(stripslashes($item['title']), 'pricelist_title');
				$content .= $vePage->display->printContentContainer(stripslashes($item['sale_price']), 'pricelist_sale_price');
				$content .= '<div class="pricelist_price ' . $vePage->display->get_font_class($element['style']['font']) . '">' . $item['price'] . '</div>';
				$content .= $vePage->display->printContentContainer(stripslashes($item['per']), 'pricelist_per');

				if (isset($item['popular'])) {
					$popular_text = isset($item['popular_text']) ? stripslashes($item['popular_text']) : __('NEJPRODÁVANĚJŠÍ', 'cms_ve');
					$content .= $vePage->display->printContentContainer($popular_text, 'pricelist_popular_text');
				}
				$content .= '</div>';

				if (!isset($item['description'])) {
					$item['description'] = '';
				}
				$content .= $vePage->display->printContentContainer(stripslashes($item['description']), 'pricelist_description');

				if (!empty($item['features'])) {
					$content .= '<div class="pricelist_features">';
					foreach ($item['features'] as $feature) {
						$content .= '<div class="pricelist_feature">' . stripslashes($feature['text']) . '</div>';
					}
					$content .= '</div>';
				}

				$content .= '</div>';

				//$target=(isset($item['link']['target']))? 'target="_blank"' : "";

				//$content.='<div class="pricelist_button"><a class="ve_content_button ve_content_button_'.$element['style']['button']['style'].' '.$but_class.'" href="'.Link::create_link($item['link']).'" '.$target.'>'.stripslashes($item['button_text']).'</a></div>';

				$pricelist_info = $vePage->display->printContentContainer(stripslashes($item['text']), 'pricelist_info');

				if ($edit_mode || !isset($item['button_hide'])) {
					$but_set = [
						'style' => $element['style']['button'],
						'link' => $item['link'],
						'text' => stripslashes($item['button_text']),
					];

					$content .= isset($item['button_hide']) ? '<div class="pricelist_button" style="display: none;">' : '<div class="pricelist_button">';

					if (isset($item['popular'])) {
						$popular_but_set = [
							'style' => $element['style']['popular_button'],
							'link' => $item['link'],
							'text' => stripslashes($item['button_text']),
						];
						$content .= Button::createButton(
							$popular_but_set,
							$vePage->display->element_css,
							've_popular_button',
							$css_id . ' .ve_popular_button',
							$added,
							$edit_mode
						);
					} else {
						$content .= Button::createButton(
							$but_set,
							$vePage->display->element_css,
							've_nopopular_button',
							$css_id . ' .ve_nopopular_button',
							$added,
							$edit_mode
						);
					}

					$content .= $pricelist_info . '</div>';
				} else {
					$content .= $pricelist_info;
				}
				$content .= '</div>';
				$i++;
			}
			$content .= '</div>';
		} else {
			$empty = true;
		}
	}

	if ($empty) {
		$vePage->display->add_element_info(__('Ceník je prázdný.', 'cms_ve'), 'info');
	}

	return $content;
}

// Bullets
// back compatibility (temporary)
function ve_element_classic_bullets($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	return ve_element_bullets($element, $css_id, $post_id, $edit_mode, $added, $row_set);
}

// back compatibility end

function ve_element_bullets($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$content = '';

	if (isset($element['style']['bullets']) && !empty($element['style']['bullets'])) {
		// back compatibility
		if (!isset($element['style']['style'])) {
			if (($element['style']['type'] == 'decimal' && $element['style']['style_decimal'] == '1') || ($element['style']['type'] == 'image' && $element['style']['style_image'] == '1')) {
				$element['style']['style'] = '2';
				$element['style']['size'] = '40';
				$element['style']['space'] = '30';
				$element['style']['title_font']['font-size'] = '35';
			} elseif (($element['style']['type'] == 'decimal' && $element['style']['style_decimal'] == '2') || ($element['style']['type'] == 'image' && $element['style']['style_image'] == '2')) {
				$element['style']['style'] = '2';
				$element['style']['size'] = '20';
				$element['style']['space'] = '15';
			} elseif (($element['style']['type'] == 'decimal' && $element['style']['style_decimal'] == '3') || ($element['style']['type'] == 'image' && $element['style']['style_image'] == '3')) {
				$element['style']['style'] = '1';
				$element['style']['size'] = '40';
				$element['style']['space'] = '30';
				$element['style']['title_font']['font-size'] = '35';
			} elseif (($element['style']['type'] == 'decimal' && $element['style']['style_decimal'] == '4') || ($element['style']['type'] == 'image' && $element['style']['style_image'] == '4')) {
				$element['style']['style'] = '1';
				$element['style']['size'] = '20';
				$element['style']['space'] = '15';
			}
			if (isset($element['style']['custom_image']) && isset($element['style']['custom_image']['image']) && $element['style']['custom_image']['image']) {
				$element['style']['type'] = 'own_image';
			}

			if ($element['style']['icon'] == '1') {
				$element['style']['bullet_icon']['icon'] = 'right2';
				$element['style']['bullet_icon']['code'] = mw_content_icon('right2');
			} elseif ($element['style']['icon'] == '2') {
				$element['style']['bullet_icon']['icon'] = 'check1';
				$element['style']['bullet_icon']['code'] = mw_content_icon('check1');
			} elseif ($element['style']['icon'] == '3') {
				$element['style']['bullet_icon']['icon'] = 'right1';
				$element['style']['bullet_icon']['code'] = mw_content_icon('right1');
			} elseif ($element['style']['icon'] == '4') {
				$element['style']['bullet_icon']['icon'] = 'right3';
				$element['style']['bullet_icon']['code'] = mw_content_icon('right3');
			}
		}
		// back compatibility end

		if ($element['style']['style'] == '4' && $element['style']['type'] != 'own_image') {
			$height = $element['style']['text_font']['font-size'] ? round($element['style']['text_font']['font-size'] * 1.6) : 24;
			$height_title = $element['style']['title_font']['font-size'] ? $element['style']['title_font']['font-size'] * 1.2 : 24;
		}

		$vePage->display->element_css->addStyles(['margin-bottom' => $element['style']['space'] . 'px'], $css_id . ' .mw_element_bullets li');
		$vePage->display->element_css->addStyles(['font' => $element['style']['text_font'] ?? []], $css_id . ' .bullet_text');
		$vePage->display->element_css->addStyles(['font' => $element['style']['title_font'] ?? []], $css_id . ' .bullet_text_title');

		$id = str_replace('#', '', $css_id);

		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .bullet_icon svg' => ['width', 'height'],
				$css_id . ' .bullet_icon' => ['font-size'],
			],
			'--bullet-size-' . $id,
			$element['style']['size'] . 'px'
		);

		if ($element['style']['style'] == '1' || $element['style']['style'] == '2') {
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .bullet_icon' => ['background-color'],
				],
				'--bullet-color-' . $id,
				$element['style']['bullet_color']
			);
		} elseif ($element['style']['style'] == '3' || $element['style']['style'] == '5') {
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .bullet_icon svg' => ['fill'],
					$css_id . ' .bullet_icon' => ['border-color', 'color'],
				],
				'--bullet-color-' . $id,
				$element['style']['bullet_color']
			);
		} elseif ($element['style']['style'] == '4') {
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .bullet_icon svg' => ['fill'],
					$css_id . ' .bullet_icon' => ['color'],
				],
				'--bullet-color-' . $id,
				$element['style']['bullet_color']
			);
		}

		$class = 'mw_element_bullets mw_element_bullets_' . $element['style']['style'];
		$class .= Colors::isLightColor($element['style']['bullet_color']) ? ' light_bullet_color' : ' dark_bullet_color';

		if ($element['style']['type'] == 'own_image') {
			$bulletImage = new Image($element['style']['custom_image']);
			$bullet_icon = $bulletImage->printImg([
				'max_width' => $element['style']['size'] * 2,
				'empty_image' => false,
			], '', $edit_mode);
			$class .= ' mw_element_bullets_ownimage';
		} else {
			$bullet_icon = mw_content_icon('icon-' . $element['style']['bullet_icon']['icon']);
		}

		//stripslashes($element['style']['bullet_icon']['code']);

		$content .= '<ul class="in_element_content ' . $class . '">';

		$i = $element['style']['start_number'] ?: 1;

		foreach ($element['style']['bullets'] as $bullet) {
			if (!isset($bullet['title'])) {
				$bullet['title'] = '';
			}
			$bullet_icon = $element['style']['type'] == 'decimal' ? $i : $bullet_icon;
			$bullet_class = 'mw_bullet_item mw_bullet_item_' . $i;
			if (isset($bullet['title']) && $bullet['title']) {
				$bullet_class .= ' mw_bullet_item_wtitle';
			}
			$content .= '<li class="' . $bullet_class . '">';
			$content .= '<div class="bullet_icon">' . $bullet_icon . '</div>';
			$content .= '<div class="bullet_text">';
			$content .= $vePage->display->printContentContainer(stripslashes($bullet['title']), 'bullet_text_title ' . $vePage->display->get_font_class($element['style']['title_font'], 'subtitle'));
			$content .= $vePage->display->printContentContainer(stripslashes($bullet['text']), 'bullet_text_text');
			$content .= '</div>';
			$content .= '</li>';

			$i++;
		}
		$content .= '</ul>';
	} else {
		$vePage->display->add_element_info(__('Element je prázdný. Přidejte odrážky.', 'cms_ve'), 'info');
	}

	return $content;
}


function ve_element_numbers($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;
	$content = '';
	$count = 0;

	if (isset($element['style']['numbers']) && count($element['style']['numbers'])) {
		$vePage->display->element_css->addStyles(['font' => $element['style']['number_font']], $css_id . ' .ve_number_count');
		$vePage->display->element_css->addStyles(['font' => $element['style']['text_font']], $css_id . ' .ve_number_text');

		switch ($element['style']['cols']) {
			case 'one':
				$cols_set = 1;

				break;
			case 'two':
				$cols_set = 2;

				break;
			case 'three':
				$cols_set = 3;

				break;
			case 'four':
				$cols_set = 4;

				break;
			case 'five':
				$cols_set = 5;

				break;
			case '':
				$cols_set = 0;

				break;
		}
		$cols = $vePage->display->getAutoCols($cols_set, count($element['style']['numbers']), 5);

		$rows = array_chunk($element['style']['numbers'], $cols);

		$content .= '<div class="in_element_content ve_element_number ve_element_number' . $element['style']['style'] . ' cols cols-' . $cols . ' ' . (!$cols_set ? 'mw_element_autocols' : '') . '">';

		$i = 0;
		foreach ($rows as $row) {
			$content .= '<div class="mw_element_row">';

			foreach ($row as $key => $num) {
				$count = 0;

				if ($num['type'] == 'custom') {
					$count = $num['number'] ?: 0;
				} elseif ($num['se']) {
					// back compatibility (temporary)
					$num['se'] = mwEmailingApi()->repair_content_val($num['se']);
					// back compatibility end

					if (isset($num['se']['id']) && $num['se']['id']) {
						$apiItem = mwApiConnect()->getApi($num['se']['api']);

						$show_deleted = isset($num['show_deleted']) ? true : false;
						$count = $apiItem->client()->get_list_count($num['se']['id'], true, $edit_mode, $show_deleted);

						if ($count < 0) {
							$count = 0;
						}
					}
				}

				// Remove white spaces from numeric string
				$count = preg_replace('/\s+/', '', $count);

				// Replace "," with "."
				$count = str_replace(',', '.', $count);

				$content .= '<div class="ve_number_count_item col col-' . $cols . '">';
				$content .= '<div class="ve_number_count ve_number_count_' . $i . ' ' . $vePage->display->get_font_class($element['style']['number_font']) . '" data-number="' . $count . '">';
				$content .= '<span class="num">0</span>';
				$content .= $vePage->display->printContentContainer($num['unit'], 'unit');
				$content .= '</div>';
				$content .= $vePage->display->printContentContainer($num['title'], 've_number_text');

				$content .= '</div>';

				$i++;
			}
			$content .= '</div>';
		}

		$content .= '</div>';

		if ($added) {
			$content .= "<script>
            jQuery(function() {
              mwGetIframeContent().numberAnimationIncrease('" . $css_id . "');
            });
          </script>";
		}

		$vePage->display->add_enqueue_script('ve_waypoints_script');
	} else {
		$vePage->display->add_element_info(__('Element je prázdný. Přidejte čísla nebo element smažte.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_progressbar($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;

	$percent = intval($element['style']['percent']);
	if ($percent > 100) {
		$percent = 100;
	}

	$vePage->display->element_css->addStyles(['min-width' => $percent . '%', 'background-color' => $element['style']['color1']], $css_id . ' .ve_progressbar_prog');
	$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .ve_progressbar');

	$id = str_replace('#', '', $css_id);

	$vePage->display->element_css->addVariableStyles(
		[
			$css_id . ' .ve_progressbar_prog' => ['background-color'],
			$css_id . ' .ve_progressbar_3 .ve_progressbar_bg' => ['border-color'],
		],
		'--progress-color-' . $id,
		$element['style']['color1']
	);

	if ($element['style']['style'] != 3) {
		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .ve_progressbar_bg' => ['background-color'],
			],
			'--progress-color-bg-' . $id,
			$element['style']['color2']
		);
	}

	$content = '';

	$text1 = '';
	$text2 = $element['style']['text'];
	$text3 = $percent . '%';
	if ($element['style']['style'] == 1) {
		$text1 = '<span class="ve_progressbar_text">' . $element['style']['text'] . '</span> <strong class="ve_progressbar_percentage">' . $percent . '%</strong>';
		$text2 = '';
		$text3 = '';
	} elseif ($element['style']['style'] == 4) {
		$text1 = '<span class="ve_progressbar_text">' . $element['style']['text'] . '</span>';
		$text2 = '';
	}

	$class = Colors::isLightColor($element['style']['color1']) ? ' light_progressbar_color' : ' dark_progressbar_color';

	$content .= '<div class="in_element_content ve_progressbar ' . (isset($element['style']['rounded']) ? 've_progressbar_rounded' : '') . ' ve_progressbar_' . $element['style']['style'] . ' ' . $class . '">';
	if ($text1) {
		$content .= '<div class="ve_progressbar_title">' . $text1 . '</div>';
	}
	$content .= '<div class="ve_progressbar_bg">';
	$content .= '<div class="ve_progressbar_prog">';
	if ($element['style']['style'] == '6') {
		$content .= '<div class="ve_progressbar_content">';
	}
	if ($text2) {
		$content .= '<span class="ve_progressbar_text">' . $text2 . '</span>';
	}
	if ($text3) {
		$content .= '<span class="ve_progressbar_percentage">' . $text3 . '</span>';
	}
	if ($element['style']['style'] == '6') {
		$content .= '</div>';
	}
	$content .= '</div></div></div>';

	return $content;
}

// Facebook
function ve_element_share($element, $css_id, $post_id, $edit_mode, $added)
{
	if (MwCookies()->isPermitted('marketing')) {
		global $vePage;

		$url = Link::create_link($element['style']['content'], false);
		$url = $url ?: get_permalink($post_id);

		//$g_style='medium';
		$t_style = 'vertical';
		$f_style = 'button';

		$twitter = 'false';
		$linkedin = 'false';

		$content = '<div class="in_element_content in_share_element ve_' . ($element['style']['align'] ?? 'center') . '">';
		//facebook
		if (isset($element['style']['show']['facebook'])) {
			$content .= '<div class="mw_share_element_container"><div class="fb-like" data-href="' . $url . '" data-layout="' . $f_style . '" data-action="like" data-show-faces="false" data-share="false"></div></div>';
		}

		//twitter
		if (isset($element['style']['show']['twitter'])) {
			$content .= '<div class="mw_share_element_container">';
			$content .= '<div class="twitter-like"><a href="https://twitter.com/share" class="twitter-share-button" data-url="' . $url . '" data-count="' . $t_style . '" data-lang="cs">Tweet</a></div>';
			if (!$added) {
				$content .= '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>';
			}
			$content .= '</div>';
			$twitter = 'true';
		}

		//linkedin
		if (isset($element['style']['show']['linkedin'])) {
			$content .= '<div class="mw_share_element_container">';
			$content .= '<script src="https://platform.linkedin.com/in.js" type="text/javascript">lang: ' . get_locale() . '</script>';
			$content .= '<script type="IN/Share" data-url="' . $url . '"></script>';
			$content .= '</div>';
			$linkedin = 'true';
		}

		if ($added) {
			$content .= "<script>
	          jQuery(function() {
	            mwGetIframeContent().mw_init_socials('.$twitter.','.$linkedin.');
	          });
	        </script>";
		}

		$content .= '</div>';

		return $content;
	}

	return '';
}

function ve_element_like($element, $css_id, $post_id, $edit_mode, $added)
{
	$content = '';
	if (MwCookies()->isPermitted('marketing')) {
		global $vePage;
		$share = isset($element['style']['setting']['share']) ? 'true' : 'false';

		$url = Link::create_link($element['style']['content'], false);
		$url = $url ?: get_permalink($post_id);

		$content .= '<div class="in_element_content ve_' . $element['style']['align'] . '">';
		$content .= '<div class="fb-like ve_center" data-href="' . $url . '" data-width="450" data-colorscheme="' . $element['style']['scheme'] . '" data-layout="' . $element['style']['layout'] . '" data-action="like" data-share="' . $share . '"></div>';
		$content .= '</div>';
	}

	return $content;
}

function ve_element_fac_share($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$url = Link::create_link($element['style']['content'] ?? '', false);
	$url = $url ?: get_permalink($post_id);

	$content = '<div class="in_element_content ve_' . $element['style']['align'] . '">';

	if ($element['style']['appearance'] == 'classic' && $element['style']['layout'] != '4') {
		if (MwCookies()->isPermitted('marketing')) {
			$content .= '<div class="fb-share-button" data-href="' . $url . '" data-layout="' . $element['style']['layout'] . '" data-mobile-iframe="true"></div>';
		}
	} elseif ($element['style']['appearance'] == 'button') {
		$but_set = [
			'style' => $element['style']['button'],
			'link' => [
				'link' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url),
				'target' => 1,
			],
			'text' => $element['style']['button_text'],
			'icon' => new Icon($element['style']['icon']),
		];
		$content .= Button::createButton(
			$but_set,
			$vePage->display->element_css,
			'',
			$css_id . ' .ve_content_button',
			$added,
			$edit_mode
		);
	} else {
		$image = new Image($element['style']['image']);
		$text = $image->printImg([
			'col_divisor' => Image::getColDivisor($col_type),
			'full_row' => MWPageDisplay::isFullWidthRow($row_set),
		], '', $edit_mode);
		$content .= '<a class="responsive_image" href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url) . '" target="_blank">' . $text . '</a>';
	}
	$content .= '</div>';

	return $content;
}

function ve_element_fcomments($element, $css_id, $post_id, $edit_mode, $added)
{
	$content = '';
	if (MwCookies()->isPermitted('marketing')) {
		global $vePage;

		$url = isset($element['style']['content']) && $element['style']['content'] ? Link::create_link($element['style']['content'], false) : get_permalink($post_id);
		if (!$url) {
			$url = get_permalink($post_id);
		}

		$per_page = (int) $element['style']['per_page'];
		if (!$per_page) {
			$per_page = 10;
		}

		$content = '<div class="in_element_content in_element_fcomments">';
		$scheme = $element['style']['scheme'] ?? 'light';
		$content .= cms_facebook_comments($url, $per_page, $scheme, '100%');
		$content .= '</div>';
	}

	return $content;
}

function ve_element_likebox($element, $css_id, $post_id, $edit_mode, $added)
{
	$content = '';
	if (MwCookies()->isPermitted('marketing')) {
		global $vePage;

		if ($element['style']['content']) {
			$faces = isset($element['style']['setting']['faces']) ? 'false' : 'true';
			$cover = isset($element['style']['setting']['cover']) ? 'true' : 'false';
			$cta = isset($element['style']['setting']['cta']) ? 'true' : 'false';
			$header = isset($element['style']['setting']['header']) ? 'true' : 'false';
			$tabs = (isset($element['style']['tabs']) ? implode(', ', array_keys($element['style']['tabs'])) : '');
			$width = $element['style']['width'] ?? '';
			$content = '<div class="in_element_content in_element_likebox">
	                <div class="fb-page" data-href="' . $element['style']['content'] . '" data-height="' . $element['style']['height'] . '" data-width="' . $width . '" data-max-width="' . $width . '"
	                data-tabs="' . $tabs . '" data-hide-cover="' . $cover . '" data-show-facepile="' . $faces . '" data-hide-cta="' . $cta . '" data-adapt-container-width="true" data-small-header="' . $header . '"></div>
	            </div>';
		} else {
			$vePage->display->add_element_info(__('Zadejte URL adresu vaší facebookové stránky', 'cms_ve'), 'info');
		}
	}

	return $content;
}

// FAPI

function ve_element_fapi($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;

	$content = '';

	// back compatibility
	if (!isset($element['style']['content']['api']) && isset($element['style']['content']) && $element['style']['content']) {
		$old_content = $element['style']['content'];
		$element['style']['content'] = [];
		$element['style']['content']['id'] = $old_content;
		$element['style']['content']['api'] = 'fapi';
	}
	// end temporary

	if (isset($element['style']['content']) && $element['style']['content'] && isset($element['style']['content']['id']) && MwSellingApi()->isApiRegistered($element['style']['content']['api']) && $element['style']['content']['id']) {
		$content .= MwSellingApi()->printForm($element['style']['content']['api'], $element, $css_id, $post_id, $edit_mode, $added);
	} else {
		$vePage->display->add_element_info(__('Není vybrán žádný prodejní formulář.', 'cms_ve'), 'info');
	}

	return $content;
}


function ve_element_variable_content($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;

	$content = '';
	if (isset($element['style']['content']) && $element['style']['content'] && get_post($element['style']['content'])) {
		$layer = $vePage->display->get_layer($element['style']['content'], 've_elvar');
		$var = $layer[0]['content'][0]['content'] ?? [];
		$i = 0;
		if (empty($var)) {
			$vePage->display->add_element_info(__('Vybraný předdefinovaný obsah je prázdný', 'cms_ve'));
		} else {
			foreach ($var as $content_key => $code) {
				if ($code['type'] != 'variable_content') {
					$new_css_id = str_replace('#element_', '', $css_id) . '_' . $i;
					$content .= $vePage->display->generate_element($code, str_replace('#', '', $new_css_id), '', false, 'var' . $element['style']['content'] . '_', $added);
					$i++;
				}
			}
		}
	} else {
		$vePage->display->add_element_info(__('Není vybrán žádný předdefinovaný obsah.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_faq($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;
	$content = '';

	if (isset($element['style']['faqs'])) {
		$class = 'in_element_content in_faq_element in_faq_element_' . $element['style']['style'];
		$item_class = ' faq_item';

		$clickable = (isset($element['style']['clickable']) ? intval($element['style']['clickable']) : 0);

		if (isset($element['style']['background_set'])) {
			if ($element['style']['style'] === '2') {
				$item_class .= ' faq_element_bg ' . $vePage->display->create_background_set_class($element['style']['background_set'], $css_id, ' .faq_element_bg');
			} elseif ($element['style']['style'] === '5') {
				$class .= ' faq_element_bg ' . $vePage->display->create_background_set_class($element['style']['background_set'], $css_id, ' .faq_element_bg');
			}
		}

		$vePage->display->element_css->addStyles(['font' => $element['style']['question_font']], $css_id . ' .ve_faq_question');
		$vePage->display->element_css->addStyles(['font' => $element['style']['answer_font']], $css_id . ' .ve_faq_answer');

		$onclick = ($clickable ? 'onclick="faqClick(this, \'' . $css_id . '\');"' : '');

		switch ($element['style']['cols']) {
			case 'one':
				$cols = 1;

				break;
			case 'two':
				$cols = 2;

				break;
			case 'three':
				$cols = 3;

				break;
			case 'four':
				$cols = 4;

				break;
			case 'five':
				$cols = 5;

				break;
		}

		$el_rows = array_chunk($element['style']['faqs'], $cols);

		$class .= ' cols-' . $cols;

		if ($clickable) {
			$class .= ' in_faq_element_clickable';
		}
		if ($element['style']['style'] === '2' || $element['style']['style'] === '3') {
			$class .= ' smallcols';
		} elseif ($element['style']['style'] === '5') {
			$class .= ' fullcols';
		}

		$content .= '<div class="' . $class . '">';

		$i = 1;
		foreach ($el_rows as $row) {
			$content .= '<div class="mw_element_row">';

			foreach ($row as $faq) {
				$answer_class = '';
				if ($clickable && ($i != 1 || !isset($element['style']['open_first']))) {
					$answer_class = 've_nodisp';
				}

				$content .= '<div class="col col-' . $element['style']['cols'] . $item_class . '">';

				$content .= '<h3 class="' . $vePage->display->get_font_class($element['style']['question_font']) . ' ve_faq_question ' . ($clickable && ($i != 1 || !isset($element['style']['open_first'])) ? 've_faq_question_close' : 've_faq_question_open') . '" ' . $onclick . '>'
				. '<span class="ve_faq_question_text">' . stripslashes($faq['question']) . '</span>';
				if ($clickable) {
					$content .= '<span class="ve_faq_plus">' . mw_content_icon_set('plus') . '</span>';
					$content .= '<span class="ve_faq_minus">' . mw_content_icon_set('minus') . '</span>';
				}
				$content .= '</h3>';
				$content .= '<div class="ve_faq_answer ' . $answer_class . '">' . stripslashes($faq['answer']) . '</div>';
				$content .= '</div>';

				$i++;
			}

			$content .= '</div>';
		}
		$content .= '</div>';
	} elseif ($vePage->edit_mode) {
		$vePage->display->add_element_info(__('Element je prázdný. Přidejte otázky nebo element smažte.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_catalog($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$content = '';


	if ($element['style']['item_type'] == 'subpage') {
		if (isset($element['style']['page']) && $element['style']['page']) {
			$post_id = $element['style']['page'];
		}

		$args = [
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'parent' => $post_id,
		];

		$items = mwPage::getAll($args, false);
	} else {
		$items = $element['style']['items'] ?? [];
	}

	if (count($items)) {
		$element['style']['cols'] = isset($element['style']['cols']) && $element['style']['cols'] ? $element['style']['cols'] : 0;
		$cols = $vePage->display->getAutoCols($element['style']['cols'], count($items), 3, false, $element['style']['style']);
		$hover_style = $element['style']['hover'] ?? '';
		$image_ratio = $element['style']['image_ratio'] ?? '32';
		$text_align = $element['style']['text_align'] ?? 'left';
		$img_col_size = $element['style']['image_size'] ?? 2;

		$items_set = [];
		foreach ($items as $item) {
			if ($element['style']['item_type'] == 'subpage') {
				\assert($item instanceof mwPage);

				$args = [
					'link' => $item->getUrl(),
					'image' => $item->getThumbnail(),
					'title' => $item->getName(),
					'description' => $item->getExcerpt(),
				];
			} else {
				$args = [
					'link' => Link::create_link($item['link']),
					'target' => isset($item['link']['target']) ? true : false,
					'image' => new Image($item['image']),
					'title' => $item['title'],
					'subtitle' => $item['subtitle'] ?? '',
					'description' => $item['description'],
					'price' => $item['price'],
				];
			}

			if (isset($element['style']['show_button'])) {
				$args['button_style'] = $element['style']['button'];
				$args['button_text'] = $element['style']['item_type'] !== 'subpage' ? $item['button_text'] : $element['style']['button_text'] ?? __('Více informací', 'cms_ve');
				$args['show_button'] = true;
			}

			if ($element['style']['style'] == '1') {
				$args['image_hover_link'] = $args['link'] ? true : false;
				$args['image_hover_content'] = '<h3 class="' . $vePage->display->get_font_class($element['style']['font_title']) . '">' . $args['title'] . '</h3>';
				$args['image_hover_content'] .= $vePage->display->printContentContainer($args['subtitle'], 'mw_element_item_subtitle', 'span');
				$args['image_hover_content'] .= $vePage->display->printContentContainer($args['price'], 'mw_element_item_price ' . $vePage->display->get_font_class($element['style']['font_price']));
			}

			$items_set[] = $args;
		}

		$items_args = [
			'style' => $element['style']['style'],
			'cols' => $cols,
			'inside_col_type' => $col_type,
			'autocols' => !$element['style']['cols'] ? true : false,
			'cols_type' => $element['style']['cols_type'] ?? 'cols',
			'hover_style' => $hover_style,
			'image_ratio' => $image_ratio,
			'align' => $text_align,
			'hide_image' => isset($element['style']['hide_img']) ? true : false,
			'img_col_size' => $img_col_size,
			'styles' => [
				'hover_color' => isset($element['style']['hover_color']) ? $element['style']['hover_color']['rgba'] : 'rgba(0,0,0,0,5)',
				'font_title' => $element['style']['font_title'],
				'font_subtitle' => $element['style']['font_subtitle'] ?? '',
				'font_description' => $element['style']['font_description'],
				'font_price' => $element['style']['font_price'],
			],
			'cssid' => $css_id,
			'added' => $added,
			'slider' => isset($element['style']['use_slider']) ? true : false,
			'slider_setting' => $element['style']['miocarousel_setting'] ?? '',
			'show_price' => true,
		];

		if (isset($element['style']['background_set'])) {
			$items_args['background_set'] = $element['style']['background_set'];
		}

		if ($element['style']['style'] == '1') {
			$items_args['image_hover'] = true;
			$items_args['hover_content'] = isset($element['style']['hide_content']) ? true : false;
		}

		$content = '<div class="in_element_content in_catalog_element">';
		$content .= $vePage->display->generate_element_items($items_args, $items_set, $added, $row_set);
		$content .= '</div>';
	} else {
		$vePage->display->add_element_info(__('Katalog je prázdný. Přidejte do něj nějaké položky.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_event_calendar($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;
	$content = '';

	$num = isset($element['style']['num']) && $element['style']['num'] ? $element['style']['num'] : -1;

	$category = $element['style']['category'] ?? null;
	$category = isset($_GET['event_calendar_cat']) && $_GET['event_calendar_cat'] ? $_GET['event_calendar_cat'] : $category;

	$show = $element['style']['show'] ?? '';

	$items = mwEvent::getAll([
		'posts_per_page' => $num,
		'paged' => 1,
	], true, $category, $show);

	if (empty($items['items']) && (!isset($element['style']['category']) || !$element['style']['category'])) {
		$content = '<div class="mw_element_items_info_box">' . __('Momentálně nejsou k dispozici žádné akce.', 'cms_ve') . '</div>';
	} else {
		$vePage->display->element_css->addStyles(['background-color' => $element['style']['color']], $css_id . ' .mw_event_date_container');

		$cols_set = isset($element['style']['cols']) && $element['style']['cols'] ? $element['style']['cols'] : 0;
		$cols = $vePage->display->getAutoCols($cols_set, count($items['items']), 3, false, $element['style']['style']);

		$hover_style = $element['style']['hover'] ?? '';
		$image_ratio = $element['style']['image_ratio'] ?? '32';
		$text_align = $element['style']['text_align'] ?? 'left';
		$img_col_size = $element['style']['image_size'] ?? 2;

		$hide_image = false;
		if (isset($element['style']['hide_image']) && ($element['style']['style'] == '3' || $element['style']['style'] == '7' || $element['style']['style'] == '4' || $element['style']['style'] == '6')) {
			$hide_image = true;
		}
		$show_description = true;
		if (isset($element['style']['hide_description']) && ($element['style']['style'] == '3' || $element['style']['style'] == '7' || $element['style']['style'] == '4' || $element['style']['style'] == '6')) {
			$show_description = false;
		}

		$items_set = [];
		foreach ($items['items'] as $event) {
			$subtitle = '';
			if ($event->getStartDate()) {
				$subtitle .= '<span class="mw_event_subtitle_date">' . mw_content_icon_set('calendar') . $event->getFromToDate() . '</span>';
			}
			if ($event->getPlace()) {
				$subtitle .= '<span class="mw_event_subtitle_where">' . mw_content_icon_set('map-pin') . $event->getPlace() . '</span>';
			}

			$header = '';
			if (!isset($element['style']['hide_date'])) {
				$header = '<div class="mw_event_date_container"><span>' . date('d', $event->getStartDateTime()) . '</span>' . date('m', $event->getStartDateTime()) . '</div>';
			}

			$word_count = (int) ($element['style']['word_count'] ?? 0);

			$args = [
				'link' => Link::create_link($event->getLink()),
				'target' => $event->getLinkTarget(),
				'image' => $event->getThumbnail(),
				'title' => $event->getName(),
				'description' => $event->getExcerpt($word_count),
				'subtitle' => $subtitle,
				'custom_header' => $header,
				'edit_button' => $event->getEditButton(),
			];

			if (isset($element['style']['show_button'])) {
				$args['button_style'] = $element['style']['button'];
				$args['button_text'] = $element['style']['button_text'];
				$args['show_button'] = true;
			}

			$items_set[] = $args;
		}

		$items_args = [
			'style' => $element['style']['style'],
			'cols' => $cols,
			'inside_col_type' => $col_type,
			'autocols' => !$cols_set ? true : false,
			'cols_type' => $element['style']['cols_type'] ?? 'cols',
			'hover_style' => $hover_style,
			'image_ratio' => $image_ratio,
			'align' => $text_align,
			'img_col_size' => $img_col_size,
			'styles' => [
				'font_title' => $element['style']['font_title'],
				'font_subtitle' => $element['style']['font_subtitle'] ?? '',
				'font_description' => $element['style']['font_description'],
			],
			'hide_image' => $hide_image,
			'show_description' => $show_description,
			'cssid' => $css_id,
			'added' => $added,
			'post_id' => $post_id,
			'empty' => __('V této kategorii nebyly nalezeny žádné akce.', 'cms_ve'),
		];

		if (isset($element['style']['show_cats']) && (!isset($element['style']['category']) || !$element['style']['category'])) {
			$items_args['categories'] = MW_EVENT_CAT_SLUG;
		}

		if (isset($element['style']['background_set'])) {
			$items_args['background_set'] = $element['style']['background_set'];
		}

		$content = '<div class="in_element_content in_event_calendar_element ' . (isset($element['style']['hide_date']) ? 'hidden_calendar_date' : '') . '">';
		$content .= $vePage->display->generate_element_items($items_args, $items_set, $added, $row_set);
		$content .= '</div>';
	}

	return $content;
}

function ve_element_table($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;

	$content = '';

	$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .mw_table');
	$vePage->display->element_css->addStyles(['width' => $element['style']['width']['size'] . $element['style']['width']['unit']], $css_id . ' .mw_table th');

	if (!empty($element['style']['lines'])) {
		$content .= '<table class="mw_table mw_table_style_' . $element['style']['style'] . ' mw_table_' . $element['style']['text_align'] . '">';
		$i = 0;
		foreach ($element['style']['lines'] as $row) {
			$content .= '<tr ' . ($i == 0 ? 'class="even"' : '') . '><th>' . stripslashes($row['title']) . '</th><td>' . stripslashes($row['text']) . '</td></tr>';
			$i = $i == 0 ? 1 : 0;
		}

		$content .= '</table>';
	} else {
		$vePage->display->add_element_info(__('Tabulka je prázdná.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_google_map($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;

	$vePage->display->add_enqueue_script('ve_google_maps');

	$id = 'mw_gmap_' . str_replace('#', '', $css_id);

	$content = '<div class="mw_google_map_container" id="' . $id . '" data-setting=\'{';
	if ($element['style']['map_setting']['address']) {
		$content .= '"address":"' . $element['style']['map_setting']['address'] . '",';
	}
	$content .= '"zoom":' . ($element['style']['map_setting']['zoom'] ?: '10') . ',';
	$content .= '"scrollwheel":' . (!isset($element['style']['setting']['scrollwheel']) ? 'false' : 'true');
	$content .= '}\'';
	$content .= 'style="width:100%; height:' . $element['style']['height'] . 'px;"></div>';

	if ($added) {
		$content .= '
        <script>
        var setting = {
          address : "' . $element['style']['map_setting']['address'] . '",
          zoom : ' . $element['style']['map_setting']['zoom'] . ',
          scrollwheel : ' . (!isset($element['style']['setting']['scrollwheel']) ? 'false' : 'true') . ',
        };
        mwGetIframeContent().initialize_google_map("' . $id . '", setting);
        </script>';

		$vePage->google_map_api = mwApiConnect()->getApi('google_maps')->getOption();
	}

	if ($vePage->edit_mode) {
		$content .= '<div class="mw_error_box admin_feature ve_nodisp" id="' . $id . '_error">' . __('Adresa nenalezena. Zadejte platnou adresu.', 'cms_ve') . '</div>';

		if (!$vePage->google_map_api || !isset($vePage->google_map_api['api_key']) || !$vePage->google_map_api['api_key']) {
			$vePage->display->add_element_info(__('Pro správné fungování google mapy je potřeba v nastavení elementu propojit Mioweb s Google API.', 'cms_ve'));
		}
	}

	return $content;
}

function ve_element_member_download($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;
	$content = '';
	if (isset($element['style']['content']) && is_array($element['style']['content'])) {
		if (isset($element['style']['font'])) {
			$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .in_element_content');
		}

		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .mw_icon' => ['color'],
			],
			'--icon-color-' . $css_id,
			$element['style']['color']
		);

		$bg_class = '';
		if (isset($element['style']['background_set']) && ($element['style']['style'] == '1' || $element['style']['style'] == '4')) {
			$bg_class = $vePage->display->create_background_set_class($element['style']['background_set'], $css_id, ' .mw_download_element_background');
		}

		$ul_class = '';
		$li_class = '';
		if ($element['style']['style'] == '1') {
			$ul_class = $bg_class . ' mw_download_element_background';
		} elseif ($element['style']['style'] == '4') {
			$li_class = $bg_class . ' mw_download_element_background';
		}

		$icon_class = $element['style']['color'] && Colors::isLightColor($element['style']['color']) ? ' light_color' : ' dark_color';

		$content .= '<ul class="in_element_content mw_download_element mw_download_element_style_' . $element['style']['style'] . ' ' . $ul_class . '">';
		$missing = false;
		foreach ($element['style']['content'] as $file) {
			$desc = $file['desc'] == '' ? false : true;

			$content .= '<li class="mw_icon_text ' . $li_class . '">
                <a href="' . esc_url($file['file']) . '" download rel="nofollow" class="mw_icon_text-icon mw_icon_style_' . $element['style']['icon_style'] . ' ' . $icon_class . '">
                    <div class="mw_icon">
                        <i>' . mw_content_icon_set($file['icon']['icon'], $file['icon']['icon_set']) . '</i>
                    </div>
                </a>
                <div class="mw_icon_text-text">
                    <a rel="nofollow" href="' . esc_url($file['file']) . '" download>' . $file['name'] . '</a>
                    ' . $vePage->display->printContentContainer(stripslashes($file['desc']), '', 'p') . '
                </div>
            </li>';

			if (!$file['file']) {
				$missing = true;
			}
		}
		$content .= '</ul>';

		if ($missing) {
			$vePage->display->add_element_info(__('Nejsou vyplněny všechny soubory ke stažení.', 'cms_ve'));
		}
	} else {
		$vePage->display->add_element_info(__('Nejsou zadány žádné soubory ke stažení.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_icon($element, $css_id, $post_id, $edit_mode, $added)
{
	global $vePage;
	$content = '';

	$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .mw_icon_text-text');
	if (isset($element['style']['title_font'])) {
		$vePage->display->element_css->addStyles(['font' => $element['style']['title_font']], $css_id . ' .mw_icon_text-title');
	}
	if ($element['style']['size']) {
		$vePage->display->element_css->addStyles(['font-size' => $element['style']['size'] . 'px'], $css_id . ' .mw_icon_text-icon');
	}

	$vePage->display->element_css->addVariableStyles(
		[
			$css_id . ' .mw_icon' => ['color'],
		],
		'--icon-color-' . $css_id,
		$element['style']['color']
	);

	$link = '';
	if (isset($element['style']['link'])) {
		$link = Link::create_link($element['style']['link']);
	}

	if ($link) {
		$tag = 'a';
		$attr = ' href="' . Link::create_link($element['style']['link']) . '" ' . (isset($element['style']['link']['target']) ? 'target="_blank"' : '');
	} else {
		$tag = 'div';
		$attr = '';
	}

	$icon_class = $element['style']['color'] && Colors::isLightColor($element['style']['color']) ? ' light_color' : ' dark_color';

	$title = $element['style']['title'] ?? '';
	$text = $element['style']['text'] ?? '';

	$valign = $element['style']['vertical_align'] ?? 'center';
	$content .= '<div class="in_element_content mw_icon_text mw_icon_text_style_' . $element['style']['style'] . ' mw_icon_text_align_' . $element['style']['align'] . ' mw_icon_text_vertical_align_' . $valign . '">'
	. '<' . $tag . $attr . ' class="mw_icon_text-icon">'
	. '<div class="mw_icon mw_icon_style_' . $element['style']['icon_style'] . ' ' . $icon_class . '">'
	. '<i>' . mw_content_icon_set($element['style']['icon']['icon'], $element['style']['icon']['icon_set']) . '</i>'
	. '</div>'
	. '</' . $tag . '>'
	. '<div class="mw_icon_text_content">'
	. $vePage->display->printContentContainer($title, 'mw_icon_text-title ' . $vePage->display->get_font_class($element['style']['title_font'], 'title'), $tag, '', '', $attr)
	. $vePage->display->printContentContainer($text, 'mw_icon_text-text ' . $vePage->display->get_font_class($element['style']['font'], 'text'), $tag, '', '', $attr)
	. '</div>'
	. '</div>';

	return $content;
}


function ve_element_social_icons($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$content = '';

	if (isset($element['style']['socials']) && !empty($element['style']['socials'])) {
		$id = str_replace('#', '', $css_id);

		if (!$element['style']['hover_color'] && $element['style']['color']) {
			$element['style']['hover_color'] = Colors::shiftColor($element['style']['color'], 0.8);
		}

		$vePage->display->element_css->addStyles(
			[
				'margin-right' => $element['style']['space'] . 'px',
			],
			$css_id . ' .mw_social_icon_bg'
		);

		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .mw_social_icon_bg svg' => ['width', 'height'],
				$css_id . ' .mw_social_icon_bg' => ['width', 'height', 'font-size'],
			],
			'--social-icon-size-' . $id,
			$element['style']['size'] . 'px'
		);

		if ($element['style']['style'] == '1' || $element['style']['style'] == '2') {
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .mw_social_icon_bg' => 'background-color',
				],
				'--social-icon-color-' . $id,
				$element['style']['color']
			);
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .mw_social_icon_bg:hover' => 'background-color',
				],
				'--social-icon-hover-' . $id,
				$element['style']['hover_color']
			);
			/*
			if($element['style']['hover_color'] && $element['style']['color']=="#ffffff") {
			$styles[]=array(
			'styles'=>array(
			'fill'=>'#fff',
			),
			'element'=>$css_id." .mw_social_icon_bg:hover svg",
			);
			}*/
		} elseif ($element['style']['style'] == '3' || $element['style']['style'] == '4') {
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .mw_social_icon_bg svg' => 'fill',
					$css_id . ' .mw_social_icon_bg' => 'border-color',
				],
				'--social-icon-color-' . $id,
				$element['style']['color']
			);
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .mw_social_icon_bg:hover svg' => 'fill',
					$css_id . ' .mw_social_icon_bg:hover' => 'border-color',
				],
				'--social-icon-hover-' . $id,
				$element['style']['hover_color']
			);
		}

		$content = '';

		$class = 'mw_social_icons_container mw_social_icons_container_' . $element['style']['style'];
		$class .= Colors::isLightColor($element['style']['color']) ? ' light_color' : ' dark_color';
		$class .= Colors::isLightColor($element['style']['hover_color']) ? ' light_hover_color' : ' dark_hover_color';
		$class .= ' ve_' . $element['style']['align'];

		$content .= '<div class="' . $class . '">';

		foreach ($element['style']['socials'] as $item) {
			$link = $item['link'] ?: '';

			$content .= '<a href="' . $link . '" target="_blank" class="mw_social_icon_bg">' . mw_content_icon('icon-' . $item['icon']['icon'], 'social-icons.svg') . '</a>';
		}
		$content .= '</div>';
	} else {
		$vePage->display->add_element_info(__('Nejsou vybrané žádné sociální sítě.', 'cms_ve'), 'info');
	}

	return $content;
}

// @TODO remove element social sprinters
function ve_element_social_sprinters($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;
	$content = '';

	if (isset($element['style']['code']) && $element['style']['code']) {
		$code = str_replace('sprinte.rs/', '', $element['style']['code']);
		$code = str_replace('http://sprinte.rs/', '', $code);
		$err_message = '';

		$url = 'https://socialsprinters.com/aa/api_ss/getAppUrl.php?short_code=' . $code . '&access_token=f5hg4k5e4a545h4fs5a';
		$response = wp_remote_post($url, [
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => [],
		]);

		$return = wp_remote_retrieve_body($response);
		if ($return == 'bad_access_token') {
			$err_message = __('Zadaný zkrácený odkaz sprinte.rs je neplatný.', 'cms_ve');
		} elseif ($return == 'not_found') {
			$err_message = __('Nebyla nalezena žádná aplikace Social Sprinters.', 'cms_ve');
		} else {
			$content = '<div class="in_element_content in_element_content_social_sprinters">';
			$vePage->display->add_enqueue_script('ve_social_sprinters');
			$content .= '<iframe id="ss_iframe_' . $code . '" src="' . $return . '" width="100%" scrolling="no"></iframe>';
			if ($added) {
				$content .= '<script>
              jQuery(function() {
                mwGetIframeContent().iFrameResize();
              });
            </script>';
			} else {
				$content .= '<script type="text/javascript">'
				. 'jQuery(document).ready(function($) { iFrameResize(); });'
				. '</script>';
			}

			$content .= '</div>';
		}

		if ($err_message) {
			$vePage->display->add_element_info($err_message);
		}
	} else {
		$vePage->display->add_element_info(__('Není zadán žádný zkrácený odkaz sprinte.rs.', 'cms_ve'), 'info');
	}

	return $content;
}

function ve_element_breadcrumbs($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	$content = '';

	if ($post_id) {
		$post_type = get_post_type($post_id);
		if ($post_type == 've_elvar') {
			$vePage->display->add_element_info(__('Drobečkové menu se vypíše až po umístění předdefinovaného obsahu na konkrétní stránku.', 'cms_ve'));
		} else {
			if ($added && function_exists('mwMemberModule')) {
				mwMemberModule()->displayMemberInit($post_id);
			}

			$vePage->display->element_css->addStyles(
				['font' => $element['style']['font']],
				$css_id . ' .mw_breadcrumbs'
			);

			$content = $vePage->display->mw_breadcrumbs($post_id);
		}
	} elseif (is_home() || $added) {
		$vePage->display->element_css->addStyles(
			['font' => $element['style']['font']],
			$css_id . ' .mw_breadcrumbs'
		);

		$content = $vePage->display->mw_breadcrumbs(null);
	} else {
		$vePage->display->add_element_info(__('Drobečkové menu nebylo možné vypsat, protože není známé ID stránky. Zkuste uložit a obnovit stránku.', 'cms_ve'));
	}

	return $content;
}

function ve_element_cookie_management($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	if (isset(MwCookies()->setting()['button_color']) && MwCookies()->setting()['button_color']) {
		$color = MwCookies()->setting()['button_color'];
		$vePage->display->element_css->addStyles([
			'color' => $color . ' !important',
			'border-color' => $color,
		], $css_id . ' .mw_cookie_button_secondary');
		$vePage->display->element_css->addStyles([
			'color' => '#fff !important',
		], $css_id . ' .mw_cookie_button_secondary:hover');
		$vePage->display->element_css->addStyles([
			'background-color' => $color,
		], $css_id . ' .mw_cookie_button_primary, ' . $css_id . ' .mw_cookie_button_secondary:hover');
		$vePage->display->element_css->addStyles([
			'box-shadow' => '0 0 2px ' . $color,
		], $css_id . ' .mw_cookie_allow_all_button:hover');
	}

	$content = '<div class="in_element_cookie_management mw_cookie_management_container">';

	$but_set = [
		'style' => $element['style']['button'] ?? [],
		'show' => '',
		'popup' => '',
		'link' => ['link' => '#'],
		'text' => $element['style']['button_text'],
		'align' => $element['style']['align'] ?? 'center',
	];
	$content .= Button::createButton(
		$but_set,
		$vePage->display->element_css,
		'mw_cookie_element_set_but mw_cookie_open_setting',
		$css_id . ' .mw_cookie_element_set_but',
		$added,
		$edit_mode
	);
	$content .= '<div class="cms_clear"></div>';
	$content .= MwCookies()->printCookieSettingPopup();
	$content .= '</div>';

	return $content;
}
