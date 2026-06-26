<?php

class MwsPerson
{

	private $_fistName;

	private $_lastName;

	public function __construct(string $firstName, string $lastName)
	{
		$this->_fistName = $firstName;
		$this->_lastName = $lastName;
	}

	public function getFirstName(): string
	{
		return $this->_fistName;
	}

	public function getLastName(): string
	{
		return $this->_lastName;
	}

	public function getFullName(): string
	{
		return trim($this->getFirstName() . ' ' . $this->getLastName());
	}


	public function format(bool $toHtml = false): string
	{
		$value = trim($this->getFirstName() . ' ' . $this->getLastName());

		return $toHtml ? ('<div class="mws-person">' . esc_html($value) . '</div>') : $value . "\n";
	}

	public function toArray(): array
	{
		return [
			'firstName' => $this->getFirstName(),
			'lastName' => $this->getLastName(),
		];
	}

	public static function createFromArray(array $values): self
	{
		return new self(
			$values['firstName'],
			$values['lastName']
		);
	}

}
