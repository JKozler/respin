<?php
use Mioweb\VisualEditor\Lib\Link;
use Mioweb\VisualEditor\Lib\Image;
use Mioweb\VisualEditor\Lib\Colors;

class mwFrontComponents
{

	public static function countField($args, $class = '')
	{
		$maxCount = $args['max_count'] ?? 999999999;

		$content = '<div class="mws_product_count_field ' . $class . '">';
		$content .= '<a class="remove" href="#">' . mw_content_icon_set('minus') . '</a>';
		$content .= '<input autocomplete="off" data-max-count="' . $maxCount . '" type="text" name="count" value="1"/>';
		$content .= '<a class="add" href="#">' . mw_content_icon_set('plus') . '</a>';
		$content .= '</div>';

		return $content;
	}

	public static function textLabel($args, $class = '')
	{
		if (Colors::isLightColor($args['color'])) {
			$class .= ' mw_text_tag_light';
		}

		return '<div class="mw_text_tag ' . $class . '" style="background-color:' . $args['color'] . '">' . $args['text'] . '</div>';
	}

	public static function switch($args, $checked = 0, $class = '')
	{
		$content = '';

		$id = isset($args['id']) && $args['id'] ? 'id="' . $args['id'] . '"' : '';

		$value = '1';
		if (isset($args['value'])) {
			$value = $args['value'];
		}
		$content .= '<div class="mw_switch_container ' . $class . '" ' . $id . '>';
		$content .= '<label class="mw_switch">';
		$content .= '<input class="cms_nodisp" autocomplete="off" type="checkbox" name="' . $args['name'] . '" ' . ($checked ? 'checked="checked"' : '') . ' ' . (isset($args['disabled']) ? 'disabled="disabled"' : '') . ' value="' . $value . '" />';
		$content .= '<span class="mw_switch_slider"></span>';
		$content .= '</label>';
		$content .= '<div class="mw_switch_label">' . $args['switch_label'] . '</div>';
		$content .= '</div>';

		return $content;
	}

	public static function image(array $args, string $class = ''): string
	{
		$defaultArgs = [
			'src' => '',
			'alt' => '',
			'sizes' => '',
			'lazy_loading' => 'lazy', // possibel vals: lazy, eager, false
			'srcset' => '',
			'empty_image' => true,
			'empty_image_url' => '',
		];

		$args = array_merge($defaultArgs, $args);
		$attrs = '';

		if ($args['src'] === '' && $args['empty_image']) {
			$args['src'] = $args['empty_image_url'] ?: Image::getEmptyImageUrl();
		}
		if ($args['sizes']) {
			$attrs .= ' sizes="' . $args['sizes'] . '"';
		}
		if ($args['lazy_loading']) {
			$attrs .= ' loading="' . $args['lazy_loading'] . '"';
		}
		if ($args['srcset']) {
			$attrs .= ' srcset="' . $args['srcset'] . '"';
		}

		$class = $class ? 'class="' . $class . '"' : '';

		if ($args['src']) {
			return '<img ' . $class . ' src="' . $args['src'] . '" alt="' . $args['alt'] . '" ' . $attrs . ' />';
		}

		return '';
	}

	public static function link(array $args, string $class = ''): string
	{
		$defaultArgs = [
			'link' => '#',
			'target' => '',
			'text' => '',
			'attrs' => '',
		];

		$args = array_merge($defaultArgs, $args);

		$target = $args['target'] !== '' ? 'target="' . $args['target'] . '"' : '';
		$class = $class !== '' ? 'class="' . $class . '"' : '';

		return '<a ' . $class . ' href="' . $args['link'] . '" ' . $target . ' ' . $args['attrs'] . '>' . $args['text'] . '</a>';
	}

	public static function button($args, $selector = '', $class = '', $editMode = false, $added = false): string
	{
		$content = '';

		$defaultArgs = [
			'link' => null,
			'text' => '',
			'align' => 'center',
			'subtext' => '',
			'icon' => null,
			'icon_align' => 'left',
			'attrs' => '',
			'tag' => 'a',
			'text_after' => '',
			'show' => '',
			'popup' => null,
			'loading' => false,
		];

		$args = array_merge($defaultArgs, $args);

		$class .= ' ve_content_button';
		$class .= ' ve_content_button_' . $args['align'];

		$target = '';
		$link = '';
		$iconHtml = '';

		if ($args['show'] === 'popup' && $args['popup']) {
			global $vePage; //@TODO remove global $vePage / popups to separate instance class
			$content .= $vePage->display->popups->get_popup_to_content($args['popup'], $added, $selector, $editMode);
			$args['attrs'] .= ' data-id="' . $args['popup'] . '"';
			$class .= ' open_mw_popup';
			$link = '#';
		} elseif ($args['link'] !== null && is_array($args['link'])) {
			$linkObject = new Link($args['link']);
			$link = $linkObject->getLink();
			$target = $linkObject->getTarget();
		} elseif ($args['link'] !== null) {
			$link = $args['link'];
		}

		if ($args['icon'] !== null) {
			$icon = $args['icon'];
			$iconHtml = '<span class="ve_but_icon">' . $icon->printIcon() . '</span>';
		}

		if ($args['loading']) {
			//@TODO create component for loading icon
			//$iconHtml .= '<span class="ve_but_loading_icon"><svg role="img"><use xlink:href="' . MW_UI_ICONS_URL . 'loading.svg#icon-loading-w"></use></svg></span>';
			$iconHtml .= '<span class="ve_but_loading_icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
 width="40px" height="40px" viewBox="0 0 40 40" xml:space="preserve">
<path opacity="0.2" fill="currentColor" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946
  s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634
  c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"/>
<path fill="currentColor" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0 C22.32,8.481,24.301,9.057,26.013,10.047z">
  <animateTransform attributeType="xml"
    attributeName="transform"
    type="rotate"
    from="0 20 20"
    to="360 20 20"
    dur="0.5s"
    repeatCount="indefinite"/>
  </path>
</svg></span>';
		}

		if ($iconHtml) {
			$class .= ' ve_content_button_icon ve_content_button_icon_' . $args['icon_align'];
		}

		$text = '<span class="ve_but_text">' . stripslashes($args['text']) . '</span>';

		$subtext = '';
		if ($args['subtext'] || $editMode) {
			//@TODO remove global $vePage
			global $vePage;
			$subtext = $vePage->display->printContentContainer(stripslashes($args['subtext']), 've_button_subtext');
		}

		$but_content = $args['icon_align'] === 'left' ? $iconHtml . '<div>' . $text . $subtext . '</div>' : '<div>' . $text . $subtext . '</div>' . $iconHtml;

		$content .= '<' . $args['tag'] . ' class="' . $class . '" ' . $target . ($link ? ' href="' . $link . '"' : '') . ' ' . $args['attrs'] . '>' . $but_content . $args['text_after'] . '</' . $args['tag'] . '>';

		return $content;
	}


}
