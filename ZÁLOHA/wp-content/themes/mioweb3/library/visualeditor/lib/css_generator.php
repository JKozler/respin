<?php

use Mioweb\VisualEditor\Lib\Image;

class mwCssManager
{

	public $styles = [];

	public $mobile_styles = [];

	public $tablet_styles = [];

	public $cached_styles = '';

	public function __construct()
	{
	}

	public function loadCachedStyles($pageId, $edit_mode)
	{
		if (!$edit_mode) {
			$this->cached_styles = get_post_meta($pageId, 'mw_cached_styles', true);
		} else {
			delete_post_meta($pageId, 'mw_cached_styles');
		}
	}

	function createCssContainer(): mwCssContainer
	{
		return new mwCssContainer();
	}

	function printCss($instance, $id, $edit_mode = false)
	{
		$content = '';
		if ($edit_mode) {
			$content = '<style ' . ($id ? 'id="' . $id . '"' : '') . '>';
			$content .= $this->printStyles($instance->styles);
			$content .= '</style>';

			$content .= '<style ' . ($id ? 'id="' . $id . '_tablet"' : '') . '>';
			if (!empty($instance->tablet_styles)) {
				$content .= $this->printStyles($instance->tablet_styles, 'tablet');
			}
			$content .= '</style>';

			$content .= '<style ' . ($id ? 'id="' . $id . '_mobile"' : '') . '>';
			if (!empty($instance->mobile_styles)) {
				$content .= $this->printStyles($instance->mobile_styles, 'mobile');
			}
			$content .= '</style>';
		} else {
			$this->mergeCssContainer($instance);
		}

		return $content;
	}

	function printGlobalCss($edit_mode = false)
	{
		$content = '';

		if ($edit_mode) {
			$content = '<style>' . $this->getGlobalCss() . '</style>';
		} elseif (!$this->cached_styles /*&& $pageId*/) {
			$css = $this->getGlobalCss();
			$content = '<style>' . $css . '</style>';
			//update_post_meta($pageId,'mw_cached_styles',$css);
		}/*
		else if($this->cached_styles)
		{

			$content = '<style>'.$this->cached_styles.'</style>';

		}*/
		$this->clearGlobalCss();

		return $content;
	}
	function getGlobalCss()
	{
		$css = $this->printStyles($this->styles);
		$css .= $this->printStyles($this->tablet_styles, 'tablet');
		$css .= $this->printStyles($this->mobile_styles, 'mobile');

		return $css;
	}

	function mergeCssContainer($container)
	{
		$this->styles = array_merge($this->styles, $container->styles);
		$this->tablet_styles = array_merge($this->tablet_styles, $container->tablet_styles);
		$this->mobile_styles = array_merge($this->mobile_styles, $container->mobile_styles);
	}

	function addGlobalStyles($styles, $device = '')
	{
		if ($device == 'mobile') {
			$this->mobile_styles = array_merge($this->mobile_styles, $styles);
		} elseif ($device == 'tablet') {
			$this->tablet_styles = array_merge($this->tablet_styles, $styles);
		} else {
			$this->styles = array_merge($this->styles, $styles);
		}
	}

	function addGlobalStyle($selector, $styles, $device = '')
	{
		if ($device == 'mobile') {
			$this->mobile_styles[$selector] = isset($this->mobile_styles[$selector]) ? array_merge($this->mobile_styles[$selector], $styles) : $styles;
		} elseif ($device == 'tablet') {
			$this->tablet_styles[$selector] = isset($this->tablet_styles[$selector]) ? array_merge($this->tablet_styles[$selector], $styles) : $styles;
		} else {
			$this->styles[$selector] = isset($this->styles[$selector]) ? array_merge($this->styles[$selector], $styles) : $styles;
		}
	}

	function clearGlobalCss()
	{
		$this->styles = [];
		$this->tablet_styles = [];
		$this->mobile_styles = [];
	}

	function printStyles($styles, $res = '')
	{
		$content = '';
		if (!empty($styles)) {
			if ($res == 'mobile') {
				$content .= '@media screen and (max-width: 767px) {';
			} elseif ($res == 'tablet') {
				$content .= '@media screen and (max-width: 969px) {';
			}

			foreach ($styles as $selector => $style) {
				$content .= $selector . '{';
				foreach ($style as $property => $value) {
					$content .= $this->printProperty($property, $value);
				}
				$content .= '}';
			}

			if ($res) {
				$content .= '}';
			}
		}

		return $content;
	}

	function printProperty($key, $style)
	{
		$css = '';

		if ($key == 'bg') {
			if (isset($style['background_image']['image']) && $style['background_image']['image']) {
				$image = new Image($style['background_image']);
				$cover = isset($style['background_image']['cover']) && $style['background_image']['cover'];
				$repeat = $style['background_image']['repeat'] ?? 'no-repeat';

				// background color
				if (isset($style['background_color']['rgba1']) && $style['background_color']['rgba1']) {
					$css .= 'background-color: ' . $style['background_color']['rgba1'] . ';';
				} elseif (isset($style['background_color']['rgba2']) && $style['background_color']['rgba2']) {
					$css .= 'background-color: ' . $style['background_color']['rgba2'] . ';';
				}

				// background image
				if ($cover) {
					$size = isset($style['max_size']) && $style['max_size'] ? $style['max_size'] : 'full';
					$css .= 'background-image: url(' . $image->getUrl($size) . ');';
				} else {
					$css .= 'background-image: url(' . $image->getUrl() . ');';
				}

				// position
				if (isset($style['background_image']['position'])) {
					$css .= 'background-position: ' . $style['background_image']['position'] . ';';
				}

				// background repeat
				$css .= 'background-repeat: ' . ($cover ? 'no-repeat' : $repeat) . ';';

				// size
				if (!$cover && isset($style['background_image']['size']) && $style['background_image']['size'] != '') {
					$css .= 'background-size: ' . $style['background_image']['size'] . 'px;';
				}
			} elseif (isset($style['background_image']['pattern']) && $style['background_image']['pattern']) {
				global $mwContainer;
				$css .= 'background-image: url(' . $mwContainer->list['patterns'][$style['background_image']['pattern']] . $style['background_image']['pattern'] . '_p.jpg);';
			} elseif (isset($style['background_color']['rgba1']) && $style['background_color']['rgba1'] && isset($style['background_color']['rgba2']) && $style['background_color']['rgba2']) {
				$color1 = $style['background_color']['rgba1'];
				$color2 = $style['background_color']['rgba2'];
				$css .= 'background: linear-gradient(to bottom, ' . $color1 . ' 0%, ' . $color2 . ' 100%) no-repeat border-box;';
				//$css .= "background: -moz-linear-gradient(top,  " . $color1 . ",  " . $color2 . ") no-repeat border-box;";
				//$css .= "background: -webkit-gradient(linear, left top, left bottom, from(" . $color1 . "), to(" . $color2 . ")) no-repeat border-box;";
				//$css .= "filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $ie_color1 . "', endColorstr='" . $ie_color2 . "');";
			} elseif (isset($style['background_color']['rgba1']) && $style['background_color']['rgba1']) {
				$css .= 'background: ' . $style['background_color']['rgba1'] . ';';
			} elseif (isset($style['background_color']['rgba2']) && $style['background_color']['rgba2']) {
				$css .= 'background: ' . $style['background_color']['rgba2'] . ';';
			}
		} elseif ($key == 'font') {
			if (isset($style['font-size']) && $style['font-size'] != '') {
				$css .= 'font-size: ' . $style['font-size'] . 'px;';
			}
			if (isset($style['font-family']) && $style['font-family']) {
				$css .= "font-family: '" . $style['font-family'] . "';";
			}
			if (isset($style['color']) && $style['color']) {
				$css .= 'color: ' . $style['color'] . ';';
			}
			if (isset($style['weight']) && $style['weight']) {
				$css .= 'font-weight: ' . $style['weight'] . ';';
			}
			if (isset($style['align']) && $style['align']) {
				$css .= 'text-align: ' . $style['align'] . ';';
			}
			if (isset($style['line-height']) && $style['line-height']) {
				$css .= 'line-height: ' . $style['line-height'] . ';';
			}
			if (isset($style['letter-spacing']) && $style['letter-spacing']) {
				$css .= 'letter-spacing: ' . $style['letter-spacing'] . 'px;';
			}
			if (isset($style['capitals']) && $style['capitals']) {
				$css .= 'text-transform: uppercase';
			}
		} elseif ($key == 'text-shadow') {
			if ($style == 'dark') {
				$css .= 'text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5); ';
			} elseif ($style == 'light') {
				$css .= 'text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.5); ';
			}
		} elseif ($key == 'box-shadow') {
			if (isset($style['size']) && $style['size']) {
				$css .= '-webkit-box-shadow: ' . $style['horizontal'] . 'px ' . $style['vertical'] . 'px ' . $style['size'] . 'px 0 rgba(0, 0, 0, ' . ($style['transparency'] / 100) . ');
					-moz-box-shadow: ' . $style['horizontal'] . 'px ' . $style['vertical'] . 'px ' . $style['size'] . 'px 0 rgba(0, 0, 0, ' . ($style['transparency'] / 100) . ');
					box-shadow: ' . $style['horizontal'] . 'px ' . $style['vertical'] . 'px ' . $style['size'] . 'px 0 rgba(0, 0, 0, ' . ($style['transparency'] / 100) . '); ';
			} else {
				$css .= 'box-shadow:' . $style . ';';
			}
		} elseif ($key == 'corner') {
			if ($style) {
				$css .= '-moz-border-radius: ' . $style . ';'
				. '-webkit-border-radius: ' . $style . ';'
				. '-khtml-border-radius: ' . $style . ';'
				. 'border-radius: ' . $style . ';';
			}
		} elseif ($key == '_padding') {
			if ($style) {
				$css .= 'padding: ' . $style['top'] . 'px ' . $style['right'] . 'px ' . $style['bottom'] . 'px ' . $style['left'] . 'px;';
			}
		} elseif ($key == 'paddingem') {
			if ($style) {
				$css .= 'padding: ' . $style['top'] . 'em ' . $style['right'] . 'em ' . $style['bottom'] . 'em ' . $style['left'] . 'em;';
			}
		} elseif ($key == 'paddingc') {
			if ($style) {
				$css .= 'padding: ' . $style['top'] . ' ' . $style['right'] . ' ' . $style['bottom'] . ' ' . $style['left'] . ';';
			}
		} elseif (($key == 'border_top' || $key == 'border_bottom' || $key == '_border') && !empty($style)) {
			$newkey = $key == '_border' ? 'border' : str_replace('_', '-', $key);
			$css .= $newkey . ': ' . $style['size'] . 'px ' . ($style['style'] ?? 'solid') . ' ' . $style['color'] . ';';
		} elseif ($key == 'opacity') {
			if (!empty($style)) {
				$css .= 'zoom: 1;'
				. 'filter: alpha(opacity=' . $style . ');'
				. 'opacity: ' . ($style / 100) . ';';
			}
		} elseif (!empty($style)) {
			if (is_array($style)) {
				//echo $key;
				//print_r($style);
			}
			$css = $key . ':' . $style . ';';
		}

		return $css;
	}

}

class mwCssContainer
{

	public $styles = [];

	public $mobile_styles = [];

	public $tablet_styles = [];

	public bool $edit_mode = false;

	public function __construct()
	{
		$this->edit_mode = current_user_can('edit_pages') && !isset($_GET['revision']);
	}

	public function resetStyles()
	{
		$this->styles = [];
		$this->mobile_styles = [];
		$this->tablet_styles = [];
	}

	function addStyles($styles, $selector)
	{
		$this->styles = $this->addStylesTo($this->styles, $styles, $selector);
	}

	function addMobileStyles($styles, $selector)
	{
		$this->mobile_styles = $this->addStylesTo($this->mobile_styles, $styles, $selector);
	}

	function addTabletStyles($styles, $selector)
	{
		$this->tablet_styles = $this->addStylesTo($this->tablet_styles, $styles, $selector);
	}

	function addStylesTo($old_styles, $styles, $selector)
	{
		if (isset($old_styles[$selector])) {
			foreach ($styles as $property => $value) {
				$old_styles[$selector][$property] = $value;
			}
		} else {
			$old_styles[$selector] = $styles;
		}

		return $old_styles;
	}

	public function addBgStyle(array $imageArray, string $selector, bool $edit_mode = false, ?string $maxSize = null): void
	{
		$bgImage = new Image($imageArray);

		$use = 'img';
		if (!$bgImage->isEmpty()) {
			$this->addStyles([
				'bg' => [
					'background_image' => $imageArray,
					'max_size' => $edit_mode ? null : $maxSize,
				],
			], $selector);
		} elseif (isset($imageArray['pattern']) && $imageArray['pattern']) {
			global $mwContainer;
			if (isset($mwContainer->list['patterns'][$imageArray['pattern']])) {
				$this->addStyles([
					'background-image' => 'url(' . $mwContainer->list['patterns'][$imageArray['pattern']] . $imageArray['pattern'] . '_p.jpg);',
				], $selector);
			}
			$use = 'pattern';
		}

		$cover = isset($imageArray['cover']) && $imageArray['cover'];

		// tablet background
		if (isset($imageArray['tablet']) && $imageArray['tablet']['image']) {
			$tabletImage = new Image($imageArray['tablet']);
			$size = $cover ? 'large' : null;
			$this->addTabletStyles([
				'background-image' => 'url(' . $tabletImage->getUrl($size) . ')',
				'background-position' => $imageArray['tablet']['position'],
			], $selector);
		} /* automate add smaller image for mobile devices. Problem is that its not calculate with image ratio. So if is image wide and screen is mobile, image is blur.
		 elseif (!$bgImage->isEmpty() && $cover && !$edit_mode) {
			$this->addTabletStyles([
				'background-image' => 'url(' . $bgImage->getUrl('large') . ')',
			], $selector);
		}*/

		if ($use == 'img' && isset($imageArray['tablet']) && isset($imageArray['tablet']['size']) && $imageArray['tablet']['size']) {
			$this->addTabletStyles([
				'background-size' => $imageArray['tablet']['size'] . 'px',
			], $selector);
		}

		// mobile background
		if (isset($imageArray['mobile_hide'])) {
			$this->addMobileStyles([
				'background-image' => 'none',
			], $selector);

			$this->addMobileStyles(['background-color' => 'transparent'], $selector . ' .background_overlay');
		} elseif (isset($imageArray['mobile']) && $imageArray['mobile']['image']) {
			$mobileImage = new Image($imageArray['mobile']);
			$size = $cover ? 'large' : null;
			$this->addMobileStyles([
				'background-image' => 'url(' . $mobileImage->getUrl($size) . ')',
				'background-position' => $imageArray['mobile']['position'],
			], $selector);
		}

		if ($use == 'img' && isset($imageArray['mobile']) && isset($imageArray['mobile']['size']) && $imageArray['mobile']['size']) {
			$this->addMobileStyles([
				'background-size' => $imageArray['mobile']['size'] . 'px',
			], $selector);
		}
	}

	function addVariableStyles($styles, $variable, $value, $important = false)
	{
		$variable = str_replace('#', '', $variable);
		$this->styles = $this->addVariableStylesTo($this->styles, $styles, $variable, $value, $important);
	}

	function addFontVariableStyles($styles, $variable, $value, $important = false)
	{
		$variable = str_replace('#', '', $variable);

		$this->styles = isset($value['font-family']) && $value['font-family'] ? $this->addVariableStylesTo($this->styles, [$styles => 'font-family'], $variable . '-family', $value['font-family'], $important) : $this->addVariableStylesTo($this->styles, [$styles => 'font-family'], $variable . '-family', '', $important);

		$this->styles = isset($value['weight']) && $value['weight'] ? $this->addVariableStylesTo($this->styles, [$styles => 'font-weight'], $variable . '-weight', $value['weight'], $important) : $this->addVariableStylesTo($this->styles, [$styles => 'font-weight'], $variable . '-weight', '', $important);

		$this->styles = isset($value['line-height']) && $value['line-height'] ? $this->addVariableStylesTo($this->styles, [$styles => 'line-height'], $variable . '-line-height', $value['line-height'], $important) : $this->addVariableStylesTo($this->styles, [$styles => 'color'], $variable . '-color', '1.2', $important);

		$this->styles = isset($value['capitals']) && $value['capitals'] ? $this->addVariableStylesTo($this->styles, [$styles => 'text-transform'], $variable . '-text-transform', 'uppercase', $important) : $this->addVariableStylesTo($this->styles, [$styles => 'text-transform'], $variable . '-text-transform', '', $important);

		$this->styles = isset($value['color']) && $value['color'] ? $this->addVariableStylesTo($this->styles, [$styles => 'color'], $variable . '-color', $value['color'], $important) : $this->addVariableStylesTo($this->styles, [$styles => 'color'], $variable . '-color', '', $important);
	}

	function addVariableStylesTo($old_styles, $styles, $variable, $value, $important = false)
	{
		if ($this->edit_mode) {
			$var = explode(',', $variable);
			$old_styles[':root'][$var[0]] = $value;
			$end_val = 'var(' . $variable . ')';
		} else {
			$end_val = $value;
		}

		if ($important) {
			$end_val .= ' !important';
		}
		foreach ($styles as $selector => $property) {
			if (is_array($property)) {
				foreach ($property as $pro) {
					$old_styles[$selector][$pro] = $end_val;
				}
			} else {
				$old_styles[$selector][$property] = $end_val;
			}
		}

		return $old_styles;
	}
}
