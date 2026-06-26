<?php declare(strict_types=1);

namespace Mioweb\Shop\Listeners;

use Mioweb\Library\Api\MPohoda\MPohodaIssuer;
use Mioweb\Shop\Order\OrderGateDocument;

class MPohodaListener
{

	private static ?self $instance = null;

	private function __construct()
	{
		if (\MWMPohoda()->isActive()) {
			$this->registerHooks();
		}
	}

	public function invoiceCreated(OrderGateDocument $document): void
	{
		MPohodaIssuer::getInstance()->issue($document);
	}

	private function registerHooks()
	{
		add_action('mw_invoice_created', [$this, 'invoiceCreated']);
	}

	public static function getInstance(): MPohodaListener
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
