<?php

use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\Link;

function ve_element_mioweb_nav($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;
	global $post;
	$current = $post_id;
	$campaigns = get_option('campaign_basic');
	$campaign = isset($campaigns['campaigns']) && isset($campaigns['campaigns'][$element['style']['campaign']]) ? $campaigns['campaigns'][$element['style']['campaign']] : null;
	$accessed_pages = [];

	if (isset($campaign['evergreen']) && isset($_COOKIE['mioweb_campaign_access']) && $_COOKIE['mioweb_campaign_access']) {
		$access = unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
		$accessed_pages = explode(',', $access[$element['style']['campaign']]);
	}

	$content = '';
	$ul_class = '';

	if (isset($element['style']['campaign']) && $element['style']['campaign'] != '' && isset($campaigns['campaigns']) && isset($campaigns['campaigns'][$element['style']['campaign']])) {
		$content .= $vePage->print_styles(['color' => $element['style']['color-active']], $css_id . ' li.current-menu-item .mioweb_campaign_menu_item,' . $css_id . ' ul li a.mioweb_campaign_menu_item:hover', 'online');

		$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .mioweb_campaign_menu li');

		if ($element['style']['style'] == '6') {
			if (!isset($element['style']['background-color']) || !$element['style']['background-color']) {
				$element['style']['background-color'] = '#ffffff';
			}
			if (!isset($element['style']['color-active']) || !$element['style']['color-active']) {
				$element['style']['color-active'] = '#eeeeee';
			}

			$ul_class .= Colors::isLightColor($element['style']['background-color']) ? ' light_color' : ' dark_color';
			$ul_class .= Colors::isLightColor($element['style']['color-active']) ? ' light_hover_color' : ' dark_hover_color';

			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .mioweb_campaign_menu li.current-menu-item' => ['background-color'],
					$css_id . ' .mioweb_campaign_menu li.mioweb_campaign_access:hover' => ['background-color'],
					$css_id . ' .mioweb_campaign_menu li.mioweb_campaign_access:hover::after' => ['border-color'],
					$css_id . ' .mioweb_campaign_menu li.current-menu-item::after' => ['border-color'],
				],
				'--campaign-nav-color-' . $css_id,
				$element['style']['color-active']
			);
			$vePage->display->element_css->addStyles(['background-color' => $element['style']['background-color']], $css_id . ' ul');
		} else {
			$vePage->display->element_css->addVariableStyles(
				[
					$css_id . ' .mioweb_campaign_menu li.current-menu-item' => ['color'],
					$css_id . ' .mioweb_campaign_menu li.mioweb_campaign_access:hover' => ['color'],
				],
				'--campaign-nav-color-' . $css_id,
				$element['style']['color-active']
			);
		}

		$content .= '<div class="mioweb_campaign_menu mioweb_campaign_menu_' . $element['style']['style'] . '"><ul class="' . $ul_class . '">';
		$i = 0;
		if (is_array($campaign['page'])) {
			$count = count($campaign['page']);
			foreach ($campaign['page'] as $page) {
				if ($page['page'] && !isset($page['exclude'])) {
					$wpage = get_post($page['page']);
					$date = strtotime($page['publishdate']['date'] . ' ' . $page['publishdate']['hour'] . ':' . $page['publishdate']['minute'] . ':0');

					$item_class = '';

					if (current_user_can('administrator') || (!isset($campaign['evergreen']) && $date < current_time('timestamp')) || (isset($campaign['evergreen']) && isset($access) && ($access[$element['style']['campaign']] == 'all' || in_array($page['page'], $accessed_pages)))) {
						$name = $page['name'];
						$thumb = $page['thumb'];
						$tag = 'a';
						$item_class .= ' mioweb_campaign_access';
						$access = true;
					} else {
						$name = $page['csname'] ?: $page['name'];
						$thumb = $page['csthumb'] ?: $page['thumb'];
						$tag = 'div';
						$item_class .= ' mioweb_campaign_no_access';
						$access = false;
					}
					$name = $name ?: $wpage->post_title;

					if ($current == $page['page']) {
						$item_class .= ' current-menu-item';
					}

					$content .= '<li class="' . $item_class . '"><' . $tag . ' class="mioweb_campaign_menu_item ' . ($i == $count - 1 ? 'mioweb_campaign_menu_item_last' : '') . '" href="' . get_permalink($page['page']) . '">';

					$img_class = '';
					if ($thumb) {
						$img = '<img src="' . home_url() . $thumb . '" />';
					} else {
						$img = '';
						$img_class = ' mioweb_campaign_menu_img_empty';
					}

					$content .= '<div class="mioweb_campaign_menu_img ' . $img_class . '">'
						. $img
						. '<div class="mioweb_campaign_img_overlay">'
						. (isset($element['style']['show_play']) ? mw_content_icon('icon-play1', 'content-icons.svg') : '')
						. '</div>'
						. '</div>';

					$content .= '<div class="mioweb_campaign_menu_text">';
					$content .= '<span>' . $name . '</span>';
					if (!$access && isset($element['style']['show_info'])) {
						if (isset($campaign['evergreen'])) {
							$content .= '<small>' . __('Zatím nedostupné', 'cms_mioweb') . '</small>';
						} elseif ($page['publishdate']['date']) {
							$content .= '<small>' . __('Dostupné od', 'cms_mioweb') . ' ' . date('j.n.', strtotime($page['publishdate']['date'])) . '</small>';
						}
					}
					$content .= '</div>';
					$content .= '</' . $tag . '></li>';

					$i++;
				}
			}
		}

		$content .= '</ul></div>';
		if ($i == 0) {
			$content = '';
			$vePage->display->add_element_info(__('Vybraná kampaň neobsahuje žádné stránky s obsahem zdarma. Nastavte je ve správě kampaní, jinak se nebude menu zobrazovat.', 'cms_mioweb'));
		}
	} else {
		$vePage->display->add_element_info(__('Není vybrána žádná kampaň, pro kterou se má navigace vypsat.', 'cms_mioweb'), 'info');
	}

	return $content;
}

function ve_element_se_count($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	// back compatibility (temporary)
	if (isset($element['style']['list'])) {
		$element['style']['list'] = mwEmailingApi()->repair_content_val($element['style']['list']);
	}
	// back compatibility end

	$content = '';

	if (isset($element['style']['list']['id']) && $element['style']['list']['id']) {
		$vePage->display->add_enqueue_script('ve_waypoints_script');
		$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .in_element_content');

		$apiItem = mwApiConnect()->getApi($element['style']['list']['api']);

		$show_deleted = isset($element['style']['show_deleted']) ? true : false;

		if (isset($element['style']['limit']) && $element['style']['limit']) {
			$count = $apiItem->client()->get_list_count($element['style']['list']['id'], false, false, $show_deleted);
			$count = $element['style']['limit'] - $count;

			$url = isset($element['style']['limit_redirect']) ? Link::create_link($element['style']['limit_redirect']) : '';

			if ($count <= 0 && !$vePage->display->edit_mode && $url) {
				$content .= "<script>window.location='" . $url . "'</script>";
			}
		} else {
			$count = $apiItem->client()->get_list_count($element['style']['list']['id'], true, $edit_mode, $show_deleted);
		}
		if ($count < 0) {
			$count = 0;
		}

//		// Remove white spaces from numeric string
		$count = preg_replace('/\s+/', '', $count);

//		// Replace "," with "."
		$count = str_replace(',', '.', $count);

		$content .= '<div class="in_element_content ve_download_count_element ve_center ' . $vePage->display->get_font_class($element['style']['font'], 'text') . '">'
		. $vePage->display->printContentContainer(stripslashes($element['style']['text1']), 've_download_count_text_before', 'span')
		. ' <strong class="ve_number_count" data-number="' . $count . '"><span>0</span></strong> '
		. $vePage->display->printContentContainer(stripslashes($element['style']['text2']), 've_download_count_text_after', 'span')
		. '</div>';

		if ($added) {
			$content .= "<script>
            jQuery(function() {
              mwGetIframeContent().numberAnimationIncrease('" . $css_id . "');
            });
          </script>";
		}
	} else {
		$vePage->display->add_element_info(__('Není vybrán žádný seznam, ze kterého se má načíst počet stažení/koupení', 'cms_mioweb'), 'info');
	}

	return $content;
}

function ve_element_campaign_date($element, $css_id, $post_id, $edit_mode)
{
	global $vePage;

	$content = '';

	$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .in_element_content');

	$campaign_id = get_post_meta($post_id, 'mioweb_campaign', true);

	if (isset($_COOKIE['mioweb_campaign_access']) && $campaign_id) {
		$access = unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
		$time = $access['time'][$campaign_id['campaign']];
	} else {
		$time = current_time('timestamp');
	}

	if ($element['style']['days'] == 0 && $element['style']['time']) {
		$time = strtotime(date('d.m.Y', $time) . ' ' . $element['style']['time']);
		if (current_time('timestamp') > $time) {
			$time += 24 * 3600;
		}
	} else {
		$time += (int) $element['style']['days'] * 3600 * 24;
	}

	$content .= '<div class="in_element_content ve_center ' . $vePage->display->get_font_class($element['style']['font'], 'text') . '">';

	$format = 'd. m. Y';
	if (isset($element['style']['hide_year'])) {
		$format = 'd. m.';
	}

	$content .= date($format, $time);
	if ($element['style']['time']) {
		$content .= ' ' . __('v', 'cms_mioweb') . ' ' . stripslashes($element['style']['time']);
	}
	$content .= '</div>';

	if (!$campaign_id) {
		$vePage->display->add_element_info(__('Pokud není datum umístěno na stránce zařazené do kampaně, nebude se odvíjet od vstupu do kampaně ale od vstupu na stránku.', 'cms_mioweb'));
	}

	return $content;
}
