<?php

class MwsAddress
{

	private $_country;

	private $_city;

	private $_zip;

	private $_street;

	public function __construct(string $country, string $city, string $zip, string $street)
	{
		$this->_country = $country;
		$this->_city = $city;
		$this->_zip = $zip;
		$this->_street = $street;
	}

	public function getCountry(): string
	{
		return $this->_country;
	}

	public function getCity(): string
	{
		return $this->_city;
	}

	public function getZip(): string
	{
		return $this->_zip;
	}

	// @TODO add split street fce
	public function getStreet(): string
	{
		return $this->_street;
	}

	public function format(bool $toHtml = false): string
	{
		$quote = function (string $str) use ($toHtml) {
			$str = trim($str);
			if (!$str) {
				return '';
			}

			if ($toHtml) {
				return '<div>' . esc_html($str) . '</div>';
			}

			return $str . "\n";
		};

		$result = $toHtml ? '<div class="mws-address">' : '';

		$result .= $quote($this->getStreet());
		$result .= $quote($this->getZip() . ' ' . $this->getCity());
		$result .= $quote(MwsCountry::getCaption($this->getCountry()));

		return $result . ($toHtml ? '</div>' : '');
	}

	public function toArray(): array
	{
		return [
			'country' => $this->getCountry(),
			'city' => $this->getCity(),
			'zip' => $this->getZip(),
			'street' => $this->getStreet(),
		];
	}

	public static function createFromArray(array $values): self
	{
		return new self(
			$values['country'],
			$values['city'],
			$values['zip'],
			$values['street']
		);
	}

}
