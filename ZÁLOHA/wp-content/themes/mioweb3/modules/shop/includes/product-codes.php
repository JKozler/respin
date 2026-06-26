<?php
/**
 * Product codes.
 * User: kuba
 * Date: 01.03.17
 * Time: 9:54
 */

class MwsProductCodes
{

	private $_items;

	public function __construct($items)
	{
		$this->_items = $items ?? [];
	}

	/**
	 * Get value of a code type.
	 */
	public function getCode(string $codeType): string
	{
		return $this->_items[$codeType] ?? '';
	}

	/**
	 * Set value of a code type.
	 */
	public function setCode(string $codeType, string $value): void
	{
		$this->_items[$codeType] = $value;
	}

	public function toArray(): array
	{
		return $this->_items;
	}

}
