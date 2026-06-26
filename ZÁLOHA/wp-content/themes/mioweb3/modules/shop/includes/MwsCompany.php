<?php

class MwsCompany
{

	private $_name;

	private $_id;

	private $_taxId;

	private $_vatId;

	public function __construct(string $name, ?string $id = null, ?string $taxId = null, ?string $vatId = null)
	{
		$this->_name = $name;
		$this->_id = $id;
		$this->_taxId = $taxId;
		$this->_vatId = $vatId;
	}

	public function getName(): string
	{
		return $this->_name;
	}

	public function getId(): ?string
	{
		return $this->_id;
	}

	public function getTaxId(): ?string
	{
		return $this->_taxId;
	}

	public function getVatId(): ?string
	{
		return $this->_vatId;
	}

	public function isVATPayer(): bool
	{
		return (bool) $this->getTaxId() || (bool) $this->getVatId();
	}

	public function format(bool $toHtml = false): string
	{
		$quote = function (?string $value, ?string $name = null) use ($toHtml) {
			$value = trim($value);
			if (!$value) {
				return '';
			}

			if ($toHtml) {
				return '<div>' . esc_html(($name ? $name . ': ' : '') . $value) . '</div>';
			}

			return ($name ? $name . ': ' : '') . $value . "\n";
		};

		$result = $toHtml ? '<div class="mws-company">' : '';

		$result .= $quote($this->getName());
		$result .= $quote($this->getId(), __('IČ', 'mwshop'));
		$result .= $quote($this->getTaxId(), __('DIČ', 'mwshop'));
		$result .= $quote($this->getVatId(), __('IČ DPH', 'mwshop'));

		return $result . ($toHtml ? '</div>' : '');
	}

	public function toArray(): array
	{
		return [
			'name' => $this->getName(),
			'id' => $this->getId(),
			'taxId' => $this->getTaxId(),
			'vatId' => $this->getVatId(),
		];
	}

	public static function createFromArray(array $values): self
	{
		return new self(
			$values['name'],
			$values['id'] ?? null,
			$values['taxId'] ?? null,
			$values['vatId'] ?? null
		);
	}

}
