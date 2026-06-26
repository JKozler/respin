<?php

class MwsBankAccount
{

	private $_number;

	private $_iban;

	private $_bic;

	public function __construct(string $number, ?string $iban = null, ?string $bic = null)
	{
		$this->_number = $number;
		$this->_iban = $iban;
		$this->_bic = $bic;
	}

	public function getNumber(): string
	{
		return $this->_number;
	}

	public function getIban(): ?string
	{
		return $this->_iban;
	}

	public function getBic(): ?string
	{
		return $this->_bic;
	}

	public function toArray(): array
	{
		return [
			'number' => $this->getNumber(),
			'iban' => $this->getIban(),
			'bic' => $this->getBic(),
		];
	}

	public static function createFromArray(array $values): self
	{
		return new self(
			$values['number'],
			$values['iban'] ?? null,
			$values['bic'] ?? null
		);
	}

}
