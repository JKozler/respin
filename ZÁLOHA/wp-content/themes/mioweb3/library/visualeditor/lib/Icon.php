<?php
namespace Mioweb\VisualEditor\Lib;

final class Icon
{

	private ?string $_icon;

	private ?string $_iconSet;

	private ?string $_code;

	private ?float $_size;

	private string $_sizeUnit;

	public function __construct(array $icon, ?float $size = null, string $sizeUnit = 'px')
	{
		$this->_icon = $icon['icon'] ?? null;
		$this->_code = $icon['code'] ?? null;
		$this->_iconSet = $icon['icon_set'] ?? null;
		$this->_size = $size;
		$this->_sizeUnit = $sizeUnit;
	}

	public function getIcon(): ?string
	{
		return $this->_icon;
	}

	public function getCode(): ?string
	{
		return stripslashes($this->_code);
	}

	public function getIconSet(): ?string
	{
		return $this->_iconSet;
	}

	public function getSize(bool $withUnit = false): ?string
	{
		$size = $this->_size;
		if ($this->_size && $withUnit) {
			$size .= $this->_sizeUnit;
		}

		return $size;
	}

	public function printIcon(): string
	{
		if ($this->getIcon() && $this->getIconSet()) {
			return mw_content_icon_set($this->getIcon(), $this->getIconSet());
		}

		if ($this->getCode()) {
			return $this->getCode();
		}

		return '';
	}



}
