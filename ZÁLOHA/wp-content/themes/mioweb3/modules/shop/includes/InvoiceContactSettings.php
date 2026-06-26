<?php declare(strict_types=1);

namespace Mioweb\Shop;

class InvoiceContactSettings
{

	private bool $showPhone;

	private bool $showEmail;

	public function __construct(bool $showPhone, bool $showEmail)
	{
		$this->showPhone = $showPhone;
		$this->showEmail = $showEmail;
	}

	public function showPhone(): bool
	{
		return $this->showPhone;
	}

	public function showEmail(): bool
	{
		return $this->showEmail;
	}
}
