<?php declare(strict_types=1);

namespace Mioweb\Shop;

use Mioweb\Shop\Exceptions\UpsellProcessException;
use MwsCart;
use MwsForm;
use MwsProduct;
use MwsUserException;
use Nette\Utils\Random;

abstract class FormCart extends MwsCart
{

	protected MwsForm $form;

	/** @var array<int, Upsell> */
	protected array $processedUpsellIds = [];

	protected bool $formProcessed = false;

	protected ?string $securityCode = null;

	public function __construct(MwsForm $form, ?array $data = null)
	{
		$this->form = $form;

		parent::__construct($data);
	}

	abstract public function clear(bool $reload = true): void;

	/** @inheritDoc*/
	protected function loadFromArray(array $data, bool $reload = false): void
	{
		if ($this->_loaded && !$reload) {
			return;
		}

		$this->formProcessed = (bool) $data['formProcessed'];
		$this->securityCode = $data['securityCode'];
		$this->processedUpsellIds = $data['processedUpsellIds'];

		parent::loadFromArray($data, $reload);
	}

	public function toArray(): array
	{
		$result = parent::toArray();

		$result['formProcessed'] = $this->formProcessed;
		$result['securityCode'] = $this->securityCode;
		$result['processedUpsellIds'] = $this->processedUpsellIds;
		$result['formId'] = $this->form->getId();

		return $result;
	}

	public function getForm(): MwsForm
	{
		return $this->form;
	}

	public function isFormProcessed(): bool
	{
		return $this->formProcessed;
	}

	public function setFormProcessed(bool $processed = true): void
	{
		$this->formProcessed = $processed;
		$this->refreshSecurityCode();
	}

	public function securityCode(): ?string
	{
		return $this->securityCode;
	}

	/** @throws UpsellProcessException */
	public function processUpsell(Upsell $upsell, bool $addToCart): void
	{
		$nextUnprocessed = $this->getNextValidUnprocessedUpsell();
		if ($nextUnprocessed === null || $upsell->getId() !== $nextUnprocessed->getId()) {
			throw new UpsellProcessException('This upsell cannot be processed currently.');
		}

		$product = $upsell->getProduct();
		\assert($product instanceof MwsProduct || $product === null);
		if ($product === null) {
			throw new UpsellProcessException('Product not found.');
		}

		if ($addToCart) {
			$this->addItem($product, 1);

			$item = $this->getItems()->getOneById($product->getId());
			if ($item === null) {
				throw new UpsellProcessException(__('Chyba při vkládání do košíku.', 'mwshop'));
			}
			$price = $product->getPrice();
			$item->setStoredPrice($price);
			$item->setStoredShopPrice($price);
			$item->setStoredProductPrice($price);
		}

		$this->processedUpsellIds[] = $upsell->getId();
		$this->refreshSecurityCode();
	}

	/** @return array<int, Upsell> */
	public function getValidUnprocessedUpsells(): array
	{
		$processedUpsellIds = $this->getProcessedUpsellIds();
		$validUpsells = $this->getForm()->getValidUpsells();

		return array_filter($validUpsells, function (Upsell $upsell) use ($processedUpsellIds): bool {
			return !in_array($upsell->getId(), $processedUpsellIds, true);
		});
	}

	public function getNextValidUnprocessedUpsell(): ?Upsell
	{
		$unprocessedUpsells = $this->getValidUnprocessedUpsells();

		return array_shift($unprocessedUpsells) ?? null;
	}

	public function isUpsellProcessed(Upsell $upsell): bool
	{
		return in_array($upsell->getId(), $this->getProcessedUpsellIds(), true);
	}

	public function clearProcessedUpsellIds(): void
	{
		$this->processedUpsellIds = [];
	}

	/** @return array<int, Upsell> */
	private function getProcessedUpsellIds(): array
	{
		return $this->processedUpsellIds;
	}

	private function refreshSecurityCode(): void
	{
		$this->securityCode = Random::generate(32);
	}

}
