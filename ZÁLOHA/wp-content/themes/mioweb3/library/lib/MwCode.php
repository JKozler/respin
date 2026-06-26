<?php

class MwCode
{

	/** @var string */
	private $code;

	/** @var string */
	private $title;

	/** @var string */
	private $position;

	/** @var string */
	private $type;

	private bool $disabled;

	function __construct(array $codeData)
	{
		$this->title = $codeData['title'] ?? '';
		$this->code = $codeData['code'] ?? '';
		$this->position = $codeData['position'] ?? 'footer';
		$this->type = $codeData['type'] ?? 'necessary';
		$this->disabled = (bool) ($codeData['disabled'] ?? false);
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getPosition(): string
	{
		return $this->position;
	}

	public function isDisabled(): bool
	{
		return $this->disabled;
	}

	public function isEnabled(): bool
	{
		return !$this->isDisabled();
	}

	public function getCode(bool $ignorePermission = false): string
	{
		if ($ignorePermission || MwCookies()->isPermitted($this->type)) {
			return stripslashes($this->code);
		}

		return '';
	}

}
