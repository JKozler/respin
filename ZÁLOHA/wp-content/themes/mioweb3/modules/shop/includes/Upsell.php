<?php declare(strict_types=1);

namespace Mioweb\Shop;

use mwPage;
use mwSettingObjectService;
use MwsException;
use MwsForm;
use MwsProduct;
use MwsPrice;
use WP_Post;
use WP_Query;
use function wp_update_post;
use const MWS_UPSELL_META_FORM_ID;
use const MWS_UPSELL_META_PRODUCT_ID;

class Upsell extends mwPage
{

	public const SECURITY_CODE_QUERY_PARAMETER = 'u_code';

	private bool $_loaded = false;

	private ?int $_productId = null;

	private ?MwsProduct $_product = null;

	private ?int $_formId = null;

	private ?float $_price = null;

	private ?float $_priceSale = null;

	private ?float $_originalPriceSale = null;

	private ?float $_originalPriceFull = null;

	private bool $_isCustomPrice = false;

	public function __construct(WP_Post $post)
	{
		parent::__construct($post);

		$this->load();
	}

	public function getId(): int
	{
		return $this->getPost()->ID;
	}

	public function load(): void
	{
		if ($this->_loaded) {
			return;
		}

		$setting = MWDB()->getPostMeta($this->getId(), MWS_UPSELL_META_PRODUCT_ID, true) ?: null;
		$this->_productId = $setting !== null ? (int) $setting : null;

		$setting = MWDB()->getPostMeta($this->getId(), MWS_UPSELL_META_FORM_ID, true);
		\assert((bool) $setting);
		$this->_formId = (int) $setting;

		$setting = MWDB()->getPostMeta($this->getId(), MWS_UPSELL_META_PRICE, true);
		$this->_isCustomPrice = $setting['custom_price'] ?? false;
		$this->_price = isset($setting['price']) ? (float) $setting['price'] : null;
		$this->_priceSale = isset($setting['price_sale']) ? (float) $setting['price_sale'] : null;

		$this->_loaded = true;
	}

	public function getProductId(): ?int
	{
//		$this->load();
		return $this->_productId;
	}

	public function getFormId(): ?int
	{
//		$this->load();
		return $this->_formId;
	}

	public function setFormId(int $formId): void
	{
		$this->_formId = $formId;
	}

	public function getProduct(): ?MwsProduct
	{
		if ($this->_product === null && $this->getProductId()) {
			$this->_product = MwsProduct::getOneById($this->getProductId());

			if ($this->_product !== null) {
				$this->_originalPriceFull = $this->_product->getPriceFull()->getPriceVatIncluded();
				$this->_originalPriceSale = $this->_product->isDiscountedNow() ? $this->_product->getPrice()->getPriceVatIncluded() : null;

				if ($this->isCustomPrice()) {
					$this->_product->setPrices($this->_price, $this->_priceSale, $this->isDiscounted());
				}
			}
		}

		return $this->_product;
	}

	public function isCustomPrice(): bool
	{
		return $this->_isCustomPrice;
	}

	public function setIsCustomPrice(bool $isCustomPrice): void
	{
		$this->_isCustomPrice = $isCustomPrice;
	}

	public function getPrice(): ?float
	{
		return $this->_price;
	}

	public function setPrice(?float $price): void
	{
		$this->_price = $price;
	}

	public function getPriceSale(): ?float
	{
		return $this->_priceSale;
	}

	public function setPriceSale(?float $price): void
	{
		$this->_priceSale = $price;
	}

	public function getEndPrice(): MwsPrice
	{
		$price = null;
		if ($this->isCustomPrice()) {
			if ($this->getPriceSale() !== null) {
				$price = $this->getPriceSale();
			} elseif ($this->getPrice() !== null) {
				$price = $this->getPrice();
			}
		} elseif ($this->getProduct() !== null) {
			return $this->getProduct()->getPrice();
		}
		$vatPercentage = $this->getProduct() !== null ? $this->getProduct()->getPrice()->getVatPercentage() : 0;

		return new MwsPrice($price ?? 0, $vatPercentage, MWS()->getDefaultCurrency('key'));
	}

	public function getEndPriceFull(): MwsPrice
	{
		$price = null;
		if ($this->isCustomPrice() && $this->getPrice() !== null) {
			$price = $this->getPrice();
		} elseif ($this->getProduct() !== null) {
			return $this->getProduct()->getPriceFull();
		}
		$vatPercentage = $this->getProduct() !== null ? $this->getProduct()->getPrice()->getVatPercentage() : 0;

		return new MwsPrice($price ?? 0, $vatPercentage, MWS()->getDefaultCurrency('key'));
	}

	public function isDiscounted(): bool
	{
		return ($this->getEndPrice()->getPriceVatIncluded() < $this->getEndPriceFull()->getPriceVatIncluded());
	}

	public function getOriginalPriceSale(): ?float
	{
		return $this->_originalPriceSale;
	}

	public function getOriginalPriceFull(): ?float
	{
		return $this->_originalPriceFull;
	}

	public function getUrl(): string
	{
		return get_permalink($this->getId());
	}

	public function isValid(): bool
	{
		return $this->getProduct() !== null;
	}

	public function save(): void
	{
		update_post_meta($this->getId(), MWS_UPSELL_META_PRODUCT_ID, $this->getProductId());
		update_post_meta($this->getId(), MWS_UPSELL_META_FORM_ID, $this->getFormId());
		update_post_meta($this->getId(), MWS_UPSELL_META_PRICE, [
			'custom_price' => $this->isCustomPrice(),
			'price' => $this->getPrice(),
			'price_sale' => $this->getPriceSale(),
		]);
	}

	public static function create(MwsProduct $product, string $template, ?array $prices = null): ?self
	{
		$metaInput = [
			//MWS_UPSELL_META_FORM_ID => $form->getId(),
			MWS_UPSELL_META_PRODUCT_ID => $product->getId(),
		];

		if ($prices !== null) {
			$metaInput[MWS_UPSELL_META_PRICE] = $prices;
		}

		$args = [
			'post_title' => __('Upsell', 'mwshop'),
			'post_status' => 'publish',
			'post_type' => MWS_UPSELL_SLUG,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'meta_input' => $metaInput,
		];
		$itemId = wp_insert_post($args, true);

		wp_update_post([
			'ID' => $itemId,
			'post_name' => 'upsell' . $itemId,
		]);

		if (is_wp_error($itemId)) {
			mwMessages()->error($itemId->get_error_message());
		} elseif ($itemId) {
			// set template
			mwSetting()->getObject(MWS_UPSELL_SLUG)->service()->saveTemplate($itemId, $template);

			// save setting
			return self::createNew(get_post($itemId));
		}

		return null;
	}

	public function duplicate(): ?self
	{
		$prices = null;
		if ($this->isCustomPrice()) {
			$prices = [
				'custom_price' => $this->isCustomPrice(),
				'price' => $this->getPrice(),
				'price_sale' => $this->getPriceSale(),
			];
		}
		$newUpsell = Upsell::create($this->getProduct(), $this->getTemplate(), $prices);

		if ($newUpsell !== null) {
			$layer = $this->getContent();
			$newUpsell->setContent($layer);

			return $newUpsell;
		}

		return null;
	}

	public static function getAllByFormId(?int $formId): array
	{
		$args = [
			'post_type' => MWS_UPSELL_SLUG,
			'post_status' => 'any',
			'posts_per_page' => -1,
		];

		if ($formId === null) {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'key' => MWS_UPSELL_META_FORM_ID,
					'value' => '',
					'compare' => '=',
				],
				[
					'key' => MWS_UPSELL_META_FORM_ID,
					'compare' => 'NOT EXISTS',
				],
			];
			$args['date_query'] = [
				[
					'before' => date('Y-m-d', strtotime('-3 days')),
				],
			];
		} else {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'key' => MWS_UPSELL_META_FORM_ID,
					'value' => $formId,
					'compare' => '=',
				],
			];
		}

		$q = new WP_Query($args);

		return $q->posts;
	}

}

class mwSettingObjectService_Upsell extends mwSettingObjectService
{

}
