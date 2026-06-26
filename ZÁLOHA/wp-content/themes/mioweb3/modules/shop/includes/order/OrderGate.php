<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;

use MwsBankAccount;
use MwsContact;
use MwsCustomer;
use MwsException;
use MwsGatewayMeta;
use MwsPrice;

/**
 * Live connector to the order at the gateway. Works as caching wrapper object for order, where its data is loaded
 * directly from the gateway.
 */
abstract class OrderGate implements IOrderGate
{

	/** @var IOrder */
	protected $_order;

	/** @var MwsPrice */
	private $_price = null;

	private $_nativePrice = null;

	private $_currency = null;

	private ?MwsBankAccount $_bankAccount = null;

	private $_isPaid = null;

	private $_paidOn = null;

	private $gw = null;

	public function __construct(IOrder $order)
	{
		$this->_order = $order;
	}

	public function getPrice(): MwsPrice
	{
		if (!$this->_price) {
			$this->_price = $this->doGetPrice();
		}

		return $this->_price;
	}

	public function getNativePrice(): MwsPrice
	{
		if (!$this->_nativePrice) {
			$this->_nativePrice = $this->doGetNativePrice();
		}

		return $this->_nativePrice;
	}

	public function getCurrency(): string
	{
		if (!$this->_currency) {
			$this->_currency = $this->doGetCurrency();
		}

		return $this->_currency;
	}

	public function getBankAccount(string $currency): ?MwsBankAccount
	{
		if (!$this->_bankAccount) {
			$this->_bankAccount = $this->doGetBankAccount($currency);
		}

		return $this->_bankAccount;
	}

	public function isPaid(): bool
	{
		if ($this->_isPaid === null) {
			$this->_isPaid = $this->doIsPaid();
		}

		return $this->_isPaid;
	}

	public function getPaidOn(): ?int
	{
		if ($this->_paidOn === null) {
			$this->_paidOn = $this->doGetPaidOn() ?: false;
		}

		return $this->_paidOn ?: null;
	}

	/** Get associated gateway instance. */
	protected function getGateway(): ?MwsGatewayMeta
	{
		if ($this->gw === null) {
			$this->gw = MWS()->gateways()->getById($this->_order->getGateIdentifier());
		}

		return $this->gw;
	}

	/**
	 * Get information about the ordering person.
	 *
	 * @param bool $short Set to <code>true</code> to output short version of the contact, e.g. like a title.
	 * @return string If none is present then empty string is returned.
	 */
	public function formatInvoiceContact(bool $short = false): string
	{
		return $this->getInvoiceContact()->format($short);
	}

	/**
	 * Get shipping contact.
	 */
	public function formatShippingContact(): string
	{
		return ($contact = $this->getShippingContact()) ? $contact->format(true, true) : '';
	}

	public function getCreateInvoiceLink(): string
	{
		return '';
	}

	/**
	 * Get contact edit buttons for WP administration.
	 *
	 * @return string If none is present then empty string is returned.
	 */
	abstract public function formatContactEditing(): string;

	public function setInvoiceContact(MwsContact $contact): void
	{
		throw new MwsException('Not implemented.');
	}

	public function setShippingContact(?MwsContact $contact): void
	{
		throw new MwsException('Not implemented.');
	}

	public function createInvoice(): OrderGateDocument
	{
		throw new MwsException('Not implemented.');
	}

	abstract public function sendSummary(): void;

	/**
	 * Get items of the order.
	 *
	 * @return OrderItem[]
	 */
	abstract public function getItems(): array;

	/**
	 * Get documents of the order.
	 *
	 * @return OrderGateDocument[]
	 */
	abstract public function getDocuments(): array;

	abstract public function getCustomer(): MwsCustomer;

	abstract public function getInvoiceContact(): MwsContact;

	abstract public function getSupplier(): ?MwsContact;

	abstract public function getShippingContact(): ?MwsContact;

	/**
	 * Ancestor loads real price.
	 */
	abstract protected function doGetPrice(): MwsPrice;

	abstract protected function doGetNativePrice(): MwsPrice;

	/**
	 * Ancestor loads real price.
	 */
	abstract protected function doGetCurrency(): string;

	abstract protected function doGetBankAccount(string $currency): ?MwsBankAccount;

	/**
	 * Ancestor load real status of payments.
	 */
	abstract protected function doIsPaid(): bool;

	/**
	 * Ancestor load real time of payment as Unix timestamp in UTC.
	 */
	abstract protected function doGetPaidOn(): ?int;

}
