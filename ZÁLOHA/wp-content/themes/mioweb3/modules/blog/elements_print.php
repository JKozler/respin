<?php

// Recent posts

function ve_element_recent_posts($element, $css_id, $post_id, $edit_mode, $added, $row_set, $col_type)
{
	global $vePage;

	$number = $element['style']['number'] ?: 3;

	$category = '';
	if (isset($element['style']['category'])) {
		$category = $element['style']['category'];
	}

	$args = isset($element['style']['type']) && $element['style']['type'] == 'most_viewed_posts' ? [
			'posts_per_page' => $number,
			'cat' => $category,
			'ignore_sticky_posts' => true,
			'meta_key' => 'mioweb_post_visited',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
	] : [
			'posts_per_page' => $number,
			'cat' => $category,
			'ignore_sticky_posts' => true,
	];

	$rposts = mwBlogPost::getAll($args, false);

	if (count($rposts)) {
		if ($element['style']['style'] == '4') {
			$content = '<ul class="in_element_content in_recent_posts_element in_recent_posts_element_4">';
			foreach ($rposts as $rpost) {
				\assert($rpost instanceof mwBlogPost);
				$content .= '<li><a class="mw_element_item_title" href="' . $rpost->getUrl() . '">' . $rpost->getName() . '</a></li>';
			}
			$content .= '</ul>';
		} else {
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
				default:
					$cols_set = 0;

					break;
			}

			switch ($element['style']['style']) {
				case '1':
					$style = '3';

					break;
				case '2':
					$style = '4';

					break;
				case '3':
					$style = '6';

					break;
				case '5':
					$style = '5';

					break;
				case '6':
					$style = '1';

					break;
				case '7':
					$style = '7';

					break;
				default:
					$style = null; // TODO throw exception instead?

					break;
			}

			$cols = $vePage->display->getAutoCols($cols_set, count($rposts), 3, false, $style);

			$hover_style = $element['style']['hover'] ?? '';
			$image_ratio = $element['style']['image_ratio'] ?? '32';
			$text_align = $element['style']['text_align'] ?? 'left';
			$img_col_size = $element['style']['image_size'] ?? 2;
			$excerpt_words = isset($element['style']['excerpt_words']) && $element['style']['excerpt_words'] ? $element['style']['excerpt_words'] : 17;
			$but_text = $element['style']['but_text'] ?? __('Celý článek', 'cms_blog');
			$show_link = isset($element['style']['show']['more']) ? false : true;

			$items = [];
			foreach ($rposts as $item) {
				\assert($item instanceof mwBlogPost);

				$excerpt = !isset($element['style']['show']['excerpt'])
					? $item->getExcerpt($excerpt_words, true)
					: '';

				$args = [
					'link' => $item->getUrl(),
					'title' => $item->getName(),
					'description' => $excerpt,
					'edit_button' => $vePage->display->itemEditButton($item->getPostType(), $item->getId()),
					'image' => $item->getThumbnail(),
				];

				if ($style == '1') {
					$args['image_hover_content'] = '<h3 class="title_element_container">' . $item->getName() . '</h3>';
					$args['image_hover_link'] = true;
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
				'show_description' => true,
				'hide_image' => isset($element['style']['show']['images']) ? true : false,
				'show_link' => $show_link,
				'link_text' => $but_text,
				'styles' => [
					'hover_color' => isset($element['style']['hover_color']) ? $element['style']['hover_color']['rgba'] : 'rgba(0,0,0,0,5)',
					'font_title' => $element['style']['font'],
					'font_description' => $element['style']['font_text'],
				],
				'cssid' => $css_id,
			];
			if (isset($element['style']['background_set'])) {
				$items_args['background_set'] = $element['style']['background_set'];
			}

			if ($style == '1') {
				$items_args['image_hover'] = true;
				$items_args['hover_content'] = false;
			}

			$content = '<div class="in_element_content in_recent_posts_element">';
			$content .= $vePage->display->generate_element_items($items_args, $items, $added, $row_set);
			$content .= '</div>';
		}
	} else {
		$content = '<div class="mw_element_items_info_box">' . __('Momentálně nejsou k dispozici žádné články.', 'cms_ve') . '</div>';
	}

	return $content;
}
