<?php

namespace Mioweb\VisualEditor\Lib;

final class ButtonStyles
{

	/** @var ButtonStyles Single instance holder. */
	private static $_instance = null;

	/** @var array list of predefined button styles. */
	private $_styles;

	private function __construct()
	{
		$option = get_option('ve_buttons');
		$this->_styles = $option['buttons'] ?? [];
	}

	public function getStyles(): array
	{
		return $this->_styles;
	}

	public function getStyle(string $style, bool $primaryReplacement = true): ?array
	{
		if (!isset($this->_styles[$style]) && $primaryReplacement) {
			return $this->getPrimaryStyle();
		}

		return $this->_styles[$style] ?? null;
	}

	public function getPrimaryStyle(): ?array
	{
		return $this->_styles['basic'] ?? null;
	}

	public static function instance(): ButtonStyles
	{
		if (!static::$_instance) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}
}
