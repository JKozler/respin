<?php

namespace Mioweb\VisualEditor\Lib;
use Mioweb\VisualEditor\Lib\Icon;
use mwCssContainer;
use mwFrontComponents;

final class Button
{

	private string $_style;

	private string $_size;

	private ?int $_customSize;

	private string $_selector;

	private string $_class;

	private ?array $_styles;

	public function __construct(array $setting, string $class = '', string $selector = '')
	{
		$this->_style = $setting['style'] ?? 'basic';
		$this->_size = $setting['button_size'] ?? 'medium';
		$this->_customSize = (isset($setting['custom_size']) && $setting['custom_size'] !== '' ? (int) $setting['custom_size'] : null) ?: null;
		$this->_class = $class;
		$this->_selector = $selector;

		if ($this->_selector === '' && $this->_class === '') {
			$this->_selector = '.ve_content_button';
		} elseif ($this->_class && $this->_selector === '') {
			$this->_selector = '.' . $this->_class;
		}

		$this->_styles = $this->_style === 'custom_button' ? $setting['custom_setting'] ?? null : mwButtonStyles()->getStyle($this->_style, false);
		if ($this->_styles === null) {
			$this->_styles = mwButtonStyles()->getPrimaryStyle();
			$this->_style = 'basic';
		}
	}

	public function printButton($args, $added = false, $edit_mode = false): string
	{
		$class = $this->_class . $this->getButtonClasses();

		return mwFrontComponents::button($args, $this->_selector, $class, $edit_mode, $added);
	}

	public static function createButton(array $args, mwCssContainer &$container, string $class = '', string $selector = '', bool $added = false, bool $edit_mode = false): string
	{
		$button = new self($args['style'] ?? [], $class, $selector);
		$container = $button->addButtonStyles($container, $args['icon'] ?? null, $edit_mode);

		return $button->printButton($args, $added, $edit_mode);
	}

	public function getButtonClasses(): string
	{
		$class = ' ve_content_button_type_' . $this->_styles['style'] . ' ve_content_button_style_' . $this->_style;

		if (isset($this->_styles['hover_effect']) && $this->_styles['hover_effect'] === 'scale') {
			$class .= ' ve_cb_hover_' . $this->_styles['hover_effect'];
		}
		$class .= ' ve_content_button_size_' . $this->_size;

		return $class;
	}

	public function addButtonStyles(mwCssContainer $container, ?Icon $icon = null, bool $editMode = false): mwCssContainer
	{
		if ($this->_customSize) {
			$container->addStyles(['font-size' => $this->_customSize . 'px'], $this->_selector . '.ve_content_button_size_custom');
		}

		if ($icon !== null && $icon->getSize()) {
			$container->addStyles(['font-size' => $icon->getSize(true)], $this->_selector . ' .ve_but_icon');
		}

		if ($this->_style === 'custom_button' || $editMode) {
			$styles = self::getButtonStyles($this->_styles, $this->_selector . '.ve_content_button_style_custom_button');
			foreach ($styles as $cbs_key => $cbs_val) {
				$container->addStyles($cbs_val, $cbs_key);
			}
		}

		return $container;
	}

	public static function getButtonStyles(array $setting, string $selector): array
	{
		$hover = [];

		if (!isset($setting['font-color']) || !$setting['font-color']) {
			$setting['font-color'] = '#ffffff';
		}

		$hover['color'] = $setting['font-color'];
		if (isset($setting['hover_effect']) && $setting['hover_effect'] == 'darker') {
			if ($setting['style'] == '13') {
				$hover['color'] = Colors::shiftColor($setting['font-color']);
			} else {
				$hover['background_color'] = ['rgba1' => Colors::shiftColor($setting['background_color']['color1']), 'rgba2' => Colors::shiftColor($setting['background_color']['color2'])];
				$hover['border-color'] = Colors::shiftColor($setting['border-color']);
			}
		} elseif (isset($setting['hover_effect']) && $setting['hover_effect'] == 'lighter') {
			if ($setting['style'] == '13') {
				$hover['color'] = Colors::shiftColor($setting['font-color'], 1.2);
			} else {
				$hover['background_color'] = ['rgba1' => Colors::shiftColor($setting['background_color']['color1'], 1.2), 'rgba2' => Colors::shiftColor($setting['background_color']['color2'], 1.2)];
				$hover['border-color'] = Colors::shiftColor($setting['border-color'], 1.2);
			}
		} elseif (!isset($setting['hover_effect']) || $setting['hover_effect'] == '') {
			if (isset($setting['hover_font_color']) && $setting['hover_font_color']) {
				$hover['color'] = $setting['hover_font_color'];
			}

			if (isset($setting['hover_color']) && $setting['style'] != '13') {
				$hover['background_color'] = $setting['hover_color'];
			}
			if (isset($setting['border_hover-color']) && $setting['style'] != '13') {
				$hover['border-color'] = $setting['border_hover-color'];
			}
		}

		if ($setting['style'] == '13') {
			$hover['border-color'] = '';
			$hover['background_color'] = '';
		}

		$but_styles = [];

		$but_styles[$selector] = [
			'font' => $setting['font'],
			'color' => $setting['font-color'],
			'bg' => ['background_color' => $setting['background_color']],
			'corner' => $setting['corner'] . 'px',
		];
		$but_styles[$selector . ' .ve_button_subtext'] = [
			'font' => isset($setting['subtext_font']) && $setting['subtext'] ? $setting['subtext_font'] : '',
		];

		if (isset($hover['background_color'])) {
			$but_styles[$selector . ':hover'] = [
				'color' => $hover['color'],
				'bg' => ['background_color' => $hover['background_color']],
				'border-color' => $hover['border-color'],
			];
		}

		/*
		if(isset($setting['font']) && isset($setting['font']['use-font'])) {
		if($setting['font']['use-font']=='title') {
		$but_styles[$selector]['font']=$this->page_setting['title_font'];
		} else if($setting['font']['use-font']=='subtitle') {
		$but_styles[$selector]['font']=$this->page_setting['subtitle_font'];
		}
		}*/

		if ($setting['style'] == '12' || $setting['style'] == '4') {
			if (isset($setting['border-color'])) {
				$but_styles[$selector]['border-color'] = $setting['border-color'];
			}
			if (isset($setting['border_width']) && $setting['border_width'] != '') {
				$but_styles[$selector]['border-width'] = $setting['border_width'] . 'px';
			}
		}
		if ($setting['style'] == '12' || $setting['style'] == '13') {
			$but_styles[$selector]['bg'] = '';
		}

		$border = 0;
		if ($setting['style'] == '12') {
			$border = isset($setting['border_width']) && $setting['border_width'] != '' ? $setting['border_width'] : 2;
		} elseif ($setting['style'] == '2' || $setting['style'] == '3' || $setting['style'] == '7' || $setting['style'] == '8') {
			$border = 1;
		} elseif ($setting['style'] == '4') {
			$border = isset($setting['border_width']) && $setting['border_width'] != '' ? $setting['border_width'] : 2;
		}

		if (isset($setting['width_padding'])) {
			$but_styles[$selector]['paddingc'] = ['top' => 'calc(' . $setting['height_padding'] . 'em - ' . $border . 'px)', 'bottom' => 'calc(' . $setting['height_padding'] . 'em - ' . $border . 'px)', 'left' => (isset($setting['icon']) ? ($setting['width_padding'] - 0.8) . 'em' : $setting['width_padding'] . 'em'), 'right' => $setting['width_padding'] . 'em'];
		}

		return $but_styles;
	}
}
