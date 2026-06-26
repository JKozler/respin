<?php

use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\Link;
use Mioweb\VisualEditor\Lib\Image;

function ve_element_funnel_nav($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	if ($added) {
		MWF()->init($post_id);
	}

	$funnel = MWF()->current_funnel;
	$current = MWF()->currentPageData;


	$content = '';
	if (MWF()->is_funnel_page && $funnel) {
		$navPages = $funnel->getNavItems();
		if (count($navPages)) {
			$content .= $vePage->print_styles(['color' => $element['style']['color-active']], $css_id . ' li.current-menu-item .mioweb_funnel_menu_item,' . $css_id . ' ul li a.mioweb_funnel_menu_item:hover', 'online');

			$vePage->display->element_css->addStyles([
				'font' => $element['style']['font'],
			], $css_id . ' .mioweb_funnel_menu li');

			$ul_class = '';
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
						$css_id . ' .mioweb_funnel_menu li.current-menu-item' => ['background-color'],
						$css_id . ' .mioweb_funnel_menu li.mioweb_funnel_access:hover' => ['background-color'],
						$css_id . ' .mioweb_funnel_menu li.mioweb_funnel_access:hover::after' => ['border-color'],
						$css_id . ' .mioweb_funnel_menu li.current-menu-item::after' => ['border-color'],
					],
					'--funnel-nav-color-' . $css_id,
					$element['style']['color-active']
				);
				$vePage->display->element_css->addStyles(['background-color' => $element['style']['background-color']], $css_id . ' ul');
			} else {
				$vePage->display->element_css->addVariableStyles(
					[
						$css_id . ' .mioweb_funnel_menu li.current-menu-item' => ['color'],
						$css_id . ' .mioweb_funnel_menu li.mioweb_funnel_access:hover' => ['color'],
					],
					'--funnel-nav-color-' . $css_id,
					$element['style']['color-active']
				);
			}

			$content .= '<div class="mioweb_funnel_menu mioweb_funnel_menu_' . $element['style']['style'] . '"><ul class="' . $ul_class . '">';
			foreach ($navPages as $page) {
				$wpage = get_post($page['page_id']);

				$item_class = '';
				$thumb = null;
				if (!$vePage->edit_mode && (!MWF()->hasAccess($page) || (!$funnel->evergreen && !MWF()->isPagePublished($page)))) {
					$name = $page['nav_ba_title'] ?: $page['nav_title'];
					if (isset($page['nav_ba_image']['image']) && $page['nav_ba_image']['image']) {
						$thumb = new Image($page['nav_ba_image']);
					}
					if (!$thumb && isset($page['nav_image']['image']) && $page['nav_image']['image']) {
						$thumb = new Image($page['nav_image']);
					}

					$tag = 'div';
					$item_class .= ' mioweb_funnel_no_access';
					$access = false;
				} else {
					$name = $page['nav_title'];
					if (isset($page['nav_image']['image']) && $page['nav_image']['image']) {
						$thumb = new Image($page['nav_image']);
					}
					$tag = 'a';
					$item_class .= ' mioweb_funnel_access';
					$access = true;
				}
				$name = $name ?: $wpage->post_title;

				if ($current['page_id'] == $page['page_id']) {
					$item_class .= ' current-menu-item';
				}

				$content .= '<li class="' . $item_class . '"><' . $tag . ' class="mioweb_funnel_menu_item" href="' . get_permalink($page['page_id']) . '">';

				$img_class = '';
				if ($thumb) {
					$imgArgs = [
						'col_divisor' => Image::getColDivisor($col_type),
						'full_row' => MWPageDisplay::isFullWidthRow($row_set),
					];
					if ($element['style']['style'] === '2') {
						$imgArgs['max_width'] = 100;
					} elseif ($element['style']['style'] === '3') {
						$imgArgs['max_width'] = 66;
					}
					$img = $thumb->printImg($imgArgs, '', $edit_mode);
				} else {
					$img = '';
					$img_class = ' mioweb_funnel_menu_img_empty';
				}

				$content .= '<div class="mioweb_funnel_menu_img ' . $img_class . '">'
					. $img
					. '<div class="mioweb_funnel_img_overlay">'
					. (isset($element['style']['show_play']) ? mw_content_icon('icon-play1', 'content-icons.svg') : '')
					. '</div>'
					. '</div>';

				$content .= '<div class="mioweb_funnel_menu_text">';
				$content .= '<span>' . $name . '</span>';
				if (!$access && isset($element['style']['show_info'])) {
					if ($funnel->evergreen) {
						$content .= '<small>' . __('Zatím nedostupné', 'mw_funnels') . '</small>';
					} elseif ($page['publishtimestamp']) {
						$content .= '<small>' . __('Dostupné od', 'mw_funnels') . ' ' . date('j. n.', $page['publishtimestamp']) . '</small>';
					}
				}
				$content .= '</div>';
				$content .= '</' . $tag . '></li>';
			}

			$content .= '</ul></div>';
		} else {
			$vePage->display->add_element_info(__('Vybraná kampaň neobsahuje žádné stránky s obsahem zdarma. Nastavte je ve správě kampaní, jinak se nebude menu zobrazovat.', 'mw_funnels'));
		}
	} else {
		$vePage->display->add_element_info(__('Tato stránka není zařazena do žádné cesty zákazníka. Tento element může být umístěn jen na stránku zařazenou do cesty zákazníka.', 'mw_funnels'), 'info');
	}

	return $content;
}

if (!MW()->is_module_active('mioweb')) {

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
			// Remove white spaces from numeric string
			$count = preg_replace('/\s+/', '', $count);
			// Replace "," with "."
			$count = str_replace(',', '.', $count);
			$add = isset($element['style']['add_contacts']) ? (int) $element['style']['add_contacts'] : 0;
			$count = $add + (int) $count;

			if ($count < 0) {
				$count = 0;
			}

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
			$vePage->display->add_element_info(__('Není vybrán žádný seznam, ze kterého se má načíst počet stažení/koupení', 'mw_funnels'), 'info');
		}

		return $content;
	}
}

function ve_element_funnel_date($element, $css_id, $post_id, $edit_mode, $added, $row_set)
{
	global $vePage;

	if ($added) {
		MWF()->init($post_id);
	}

	$time = MWF()->getAccessTime() && MWF()->is_funnel_page ? MWF()->getAccessTime() : current_time('timestamp');

	$content = '';

	$vePage->display->element_css->addStyles(['font' => $element['style']['font']], $css_id . ' .in_element_content');

	if ($element['style']['days'] == 0 && $element['style']['time']) {
		$time = strtotime(date('d.m.Y', $time) . ' ' . $element['style']['time']);
		if (current_time('timestamp') > $time) {
			$time += 24 * 3600;
		}
	} else {
		$time += ((int) $element['style']['days']) * 3600 * 24;
	}

	$content .= '<div class="in_element_content ve_center ' . $vePage->display->get_font_class($element['style']['font'], 'text') . '">';

	$format = 'j. n. Y';
	if (isset($element['style']['hide_year'])) {
		$format = 'j. n.';
	}

	$content .= date($format, $time);
	if ($element['style']['time']) {
		$content .= ' ' . __('v', 'mw_funnels') . ' ' . stripslashes($element['style']['time']);
	}
	$content .= '</div>';

	if (!MWF()->is_funnel_page) {
		$vePage->display->add_element_info(__('Pokud není datum umístěno na stránce zařazené do kampaně, nebude se odvíjet od vstupu do kampaně ale od času načtení stránky.', 'mw_funnels'));
	}

	return $content;
}

// back compatibility with module mioweb
/*
if (!MW()->is_module_active('mioweb'))
{
	function ve_element_mioweb_nav($element, $css_id, $post_id, $edit_mode, $added, $row_set)
	{
		ve_element_funnel_nav($element, $css_id, $post_id, $edit_mode, $added, $row_set);
	}
	function ve_element_campaign_date($element, $css_id, $post_id, $edit_mode, $added, $row_set)
	{
		ve_element_funnel_date($element, $css_id, $post_id, $edit_mode, $added, $row_set);
	}
	function ve_element_se_count($element, $css_id, $post_id, $edit_mode, $added, $row_set)
	{
		ve_element_download_count($element, $css_id, $post_id, $edit_mode, $added, $row_set);
	}
}*/
