<?php

/**
 * Class and routines for shipping methods.
 * User: kuba
 * Date: 04.04.16
 * Time: 12:49
 */


/** Name of the meta key of product. */
define('MWS_SHIPPING_META_KEY', 'shipping');
/** Pseudo identifier for electronic shipping method. */
define('MWS_SHIPPING_ID_ELECTRONIC', -1);

/**
 * Class MwsShipping is used to wrap shipping methods. To get a shipping method, use the {@link MwsShipping:createNew}
 * constructor, which uses caching. Or create new instance directly if caching is not necessary.
 */
class MwsShipping extends mwPost
{

	/** @var null|array Settings of instance. */
	protected $_meta = null;

	/** @var MwsPrice Internal storage of the price. */
	private $_price;

	private $_type;

	private $_country;

	private $_carrier;

	private $_isCodSupported;

	private $_codPrice;

	/** @var MwsPrice|null Limit for free shoping */
	private $_freeFrom;

	private ?string $_trackingUrl;

	private ?array $_weightPrices = null;

	protected function __construct(WP_Post $post)
	{
		parent::__construct($post);
		$this->loadMeta();

		$this->_price = new MwsPrice(
			(float) ($this->_meta['price'] ?? 0),
			isset($this->_meta['vat_id']) ? MWS()->getVATs()->getValueById($this->_meta['vat_id'], false) : null // @TODO have shipping own vat?
		);

		$this->_type = $this->_meta['type'] ?? MwsShippingType::Custom;
		$this->_country = $this->_meta['country'] ?? null;
		$this->_carrier = $this->_meta['carrier'] ?? null;
		$this->_trackingUrl = $this->_meta['tracking_url'] ?? null;
		$this->_freeFrom = isset($this->_meta['free_from']) && $this->_meta['free_from'] ? new MwsPrice((float) $this->_meta['free_from']) : null;
		$this->_isCodSupported = (bool) ($this->_meta['cod_enabled'] ?? false);
		if ($this->_isCodSupported) {
			$this->_codPrice = new MwsPrice(
				(float) ($this->_meta['cod_price'] ?? 0),
				isset($this->_meta['vat_id']) ? MWS()->getVATs()->getValueById($this->_meta['vat_id'], false) : null //@TODO have shipping own vat?
			);
		} else {
			$this->_codPrice = new MwsPrice(0);
		}

		if (($this->_meta['use_weight_prices'] ?? null) === '1') {
			$this->_weightPrices = is_array($this->_meta['weight_prices']) ? $this->_meta['weight_prices'] : [];
		}
	}

	public function getPrice(): MwsPrice
	{
		return $this->_price;
	}

	public function getCodPrice(): MwsPrice
	{
		return $this->_codPrice;
	}

	public function isPriceByWeight(): bool
	{
		return $this->_weightPrices !== null;
	}

	public function isCodSupported(): bool
	{
		return $this->_isCodSupported;
	}

	public function getType(): string
	{
		return $this->_type;
	}

	public function getCountry(): ?string
	{
		return $this->_country;
	}

	public function getCarrier(): ?string
	{
		return $this->_carrier;
	}

	public function getFreeFrom(): ?MwsPrice
	{
		return $this->_freeFrom;
	}

	public function getTrackingUrl(): ?string
	{
		return $this->_trackingUrl;
	}

	/**
	 * Count total price according to selected $payType and optionally to the total order weight.
	 */
	public function getTotalPrice(?MwsPaymentMethod $paymentMethod, MwsPrice $cartPrice, float $cartWeight = 0): MwsPrice
	{
		$price = $this->getPrice();
		$currency = $price->getCurrency();
		$priceForCart = $this->getPriceForCart($cartPrice, $cartWeight);
		if (!$paymentMethod) {
			return new MwsPrice(
				$priceForCart !== null ?
					$priceForCart->getPriceVatIncluded() :
					0.0,
				$price->getVatPercentage(),
				$currency
			);
		}

		return new MwsPrice(
			$priceForCart !== null ?
				$priceForCart->getPriceVatIncluded() + ($paymentMethod->isCod() ? $this->getCodPrice()->getPriceVatIncluded() : 0) :
				0.0,
			$price->getVatPercentage(),
			$currency
		);
	}

	/**
	 * Count total price according to the total order weight and price.
	 */
	public function getPriceForCart(MwsPrice $cartPrice, float $cartWeight = 0): ?MwsPrice
	{
		$price = $this->getPriceByWeight($cartWeight);

		if ($price === null) {
			return null;
		}

		$freeFrom = $this->getFreeFrom();

		return $freeFrom && $cartPrice->asCurrency($freeFrom->getCurrency())->getPriceVatIncluded() >= $freeFrom->getPriceVatIncluded() ? new MwsPrice(0, $price->getVatPercentage(), $price->getCurrency()) : $price;
	}

	public function getPriceByWeight(float $weight = 0): ?MwsPrice
	{
		$price = $this->getPrice();

		if ($this->_weightPrices !== null) {
			$weightPrice = 0;
			$intervalsCnt = count($this->_weightPrices);
			foreach ($this->_weightPrices as $key => $interval) {
				$max_val = $interval['max_val'] === '∞' ? INF : ($interval['max_val'] ?? 0);
				if ($weight <= $max_val) {
					$weightPrice = (float) $interval['int_val'] ?? 0.0;

					break;
				}
				if ($intervalsCnt <= $key + 1) {
					return null;
				}
			}
			$price = new MwsPrice($weightPrice, $price->getVatPercentage(), $price->getCurrency());
		}

		return $price;
	}

	public function isPersonalShipping(): bool
	{
		return $this->_type == MwsShippingType::Personal;
	}

	public function isPacketaShipping(): bool
	{
		return $this->_type == MwsShippingType::Packeta;
	}

	public function isPacketaShippingType(): bool
	{
		return $this->_type == MwsShippingType::Packeta || $this->_type == MwsShippingType::PacketaCarriers;
	}

	private function loadMeta(): array
	{
		if ($this->_meta === null) {
			$this->_meta = get_post_meta($this->getId(), MWS_SHIPPING_META_KEY)[0] ?? [];
		}

		return $this->_meta;
	}


	/**
	 * Creates new instance of shipping method. If shipping of the same ID is already loaded then that instance is used from
	 * cache.
	 *
	 * @param WP_Post $post Instance of post with custom-post-type {@link MWS_SHIPPING_SLUG}.
	 * @param bool $forceUpdateCache When set to true then possibly precached instance will not be used but will be
	 *                               updated by the newly created instance.
	 * @return MwsShipping
	 * @throws MwsException If passed post is not of shipping class.
	 */
	public static function createNew(WP_Post $post, bool $forceUpdateCache = false): ?self
	{
		if (get_post_type($post) != MWS_SHIPPING_SLUG) {
			throw new MwsException('Passed post is not of shipping type.');
		}

		if ($post->post_status === 'auto-draft') {
			// Newly created post
			mwshoplog('Newly created unsaved SHIPPING post: ' . json_encode($post, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG);

			return null;
		}

		//Is created already or must be updated in cache?
		if ($forceUpdateCache || !($obj = MwObjectCache::get(self::class, $post->ID))) {
			$obj = new self($post);
			MwObjectCache::add($obj, $obj->getId());
		}

		return $obj;
	}

	/**
	 * Get all defined shipping methods as an array of {@link MwsShipping} instances.
	 *
	 * @return MwsShipping[].
	 */
	public static function getAll(array $args = [], $paged = false, ?string $country = null): array
	{
		$res = [];
		$default_args = [
			'post_type' => MWS_SHIPPING_SLUG,
			'post_status' => 'publish',
			'posts_per_page' => -1,
		];

		$query_args = array_merge($default_args, $args);

		$query = new WP_Query($query_args);
		if ($query->have_posts()) {
			/** @var WP_Post $post */
			foreach ($query->posts as $post) {
				try {
					$shipping = static::createNew($post);
					if (!$country || !$shipping->getCountry() || $country == $shipping->getCountry()) {
						$res[] = $shipping;
					}
				} catch (Exception $e) {
				}
			}
		}

		return $paged ? [
				'items' => $res,
				'pages' => $query->max_num_pages,
				'count' => $query->found_posts,
		] : $res;
	}

	/**
	 * Get shipping instance by shipping ID.
	 *
	 * @param int $shippingId
	 * @param bool $forceRecache
	 * @return MwsShipping|object Existing shipping or null
	 */
	public static function getOneById(int $shippingId, bool $forceRecache = false): ?self
	{
		if ($shippingId === MwsShippingElectronic::id) {
			return MwsShippingElectronic::getInstance();
		}

		if ($shippingId) {
			$post = get_post($shippingId);
			if ($post) {
				try {
					return static::createNew($post, $forceRecache);
				} catch (MwsException $e) {
					mwshoplog(
						sprintf(__('Nepodařilo se vytvořit instanci způsobu doručení [%d] se zprávou: %s', 'mwshop'), $shippingId, $e->getMessage()),
						MWLL_ERROR
					);
				}
			}
		}

		return null;
	}

	public static function htmlRadio(
		MwsPrice $totalPrice,
		string $currency,
		string $country,
		string $showCountry,
		string $jsParentSelector,
		bool $required = true,
		?int $selected = null,
		string $uniqueId = '',
		?array $allowedMethods = null,
		?int $packetaId = null,
		?string $packetaAddress = null,
		float $cartWeight = 0.0
	): string
	{
		$prices = [];
		$prefix = 'mws_shipping';
		$uniquePrefix = $uniqueId . $prefix;
		//Hidden input for AJAX error messages.
		$content = '<input type="hidden" name="' . $prefix . '" />';

		$shippings = $required ? $allowedMethods ?? MwsShipping::getAll([], false, $country) : [MwsShippingElectronic::getInstance()];
		$countOfShippings = count($shippings);

		if ($countOfShippings) {
			$content .= '<div class="mws_radio_select_list">';

			/** @var MwsShipping $shipping */
			foreach ($shippings as $shipping) {
				//don't include the shipping, if its weight pricing doesn't cover the cart weight
				$price = $shipping->getPriceForCart($totalPrice, $cartWeight);
				if ($price === null) {
					continue;
				}

				if (!$shipping->isPacketaShippingType() || MWS()->packeta->isConnected()) {
					$id = $shipping->getId();
					$title = esc_html($shipping->getName());
					$class = 'mw_radio_button';
					$class .= ($shipping->isCodSupported() ? ' mws_cod_enabled' : '');
					$class .= ($shipping->isPersonalShipping() ? ' mws_personal_pickup' : '');
					$class = trim($class);

					$labelClass = $prefix . '_radio ' . $prefix . '_radio_' . $id;
					$labelClass .= $shipping->getCountry() && $shipping->getCountry() !== $showCountry ? ' novisible' : '';

					if ($shipping->isPacketaShipping()) {
						global $vePage;
						$vePage->display->add_enqueue_script('mw_packeta_library_script');
						$vePage->display->add_enqueue_script('mw_packeta_script');
						$class .= ' mws_open_packeta_select';
					}

					$priceHtml = ''
						. '<span class="mws_radio_select_right">'
						. $price->asCurrency($currency)->htmlPriceVatIncluded(1, true, 'mws_price_inline')
						. '</span>';

					$cod_price = $shipping->getCodPrice()->asCurrency($currency);
					$cod_price_for_print = '';
					if ($cod_price->getPriceVatIncluded()) {
						$cod_price_for_print .= $cod_price->formatPrice();
						$cod_price_for_print .= ' ' . MwsCurrencyEnum::getSymbol($currency);
					}

					$checked = $id === $selected || $countOfShippings === 1;

					$content .= '<label class="mws_radio_select ' . $labelClass . '">'
						. '<input type="radio" id="' . $uniquePrefix . '_' . $id . '" name="' . $prefix . '"'
							. 'data-country="' . ($shipping->getCountry() ?? '') . '" '
							. 'data-cod-price="' . $cod_price_for_print . '"'
							. 'value="' . $id . '"' . ($id === $selected ? ' checked="checked"' : '')
							. ' class="' . $class . '"'
							. ' data-codEnabled="' . ($shipping->isCodSupported() ? 1 : 0) . '"'
							. ($countOfShippings === 1 ? ' checked="checked"' : '')
						. '/>'
						. '<div class="mws_radio_select_content">'
						. '<div class="mws_radio_select_left">'
						. '<span>' . $title . '</span>'
						. ($shipping->getExcerpt() ? '<div class="mws_help_container">?<span>' . $shipping->getExcerpt() . '</span></div>' : '')
						. '</div>'
						. $priceHtml;
					if ($shipping->isPacketaShipping()) {
						$content .= '<div class="mws_shipping_more mws_shipping_more_' . $id . ' mws_shipping_packeta_info_container' . ($checked && $packetaId !== null ? '' : ' cms_nodisp') . '">'
							. '<div class="mws_shipping_packeta_info_address" id="' . $uniqueId . 'mws_shipping_packeta_info_address">' . ($packetaAddress ?: __('Nevybráno', 'mwshop')) . '</div>'
							. '<a class="mws_open_packeta_select" href="#">' . __('Změnit místo vyzvednutí', 'mwshop') . '</a>'
							. '<div class="mw_icon mws_shipping_packeta_icon"><i>' . mw_content_icon_set('map-pin') . '</i></div>'
							. '</div>';
					}
					$content .= '</div>';
					$content .= '</label>';

					$prices[$id]['price'] = $shipping->getPriceForCart($totalPrice, $cartWeight)->asCurrency($currency)->toArray();
					$prices[$id]['codPrice'] = $shipping->getCodPrice()->asCurrency($currency)->toArray();
				}
			}

			$content .= '</div>';

			$content .= '<input id="' . $uniqueId . 'mws_shipping_packeta_id" class="mws_shipping_packeta_id" type="hidden" name="mws_shipping_info[id]" value="' . ($packetaId ?? '') . '" />';
			$content .= '<input id="' . $uniqueId . 'mws_shipping_packeta_address" class="mws_shipping_packeta_address" type="hidden" name="mws_shipping_info[address]" value="' . ($packetaAddress ?? '') . '" />';
		} else {
			if (MWS()->edit_mode) {
				$content .= '<div class="mw_error_box">' . __('Není definován žádný způsob doručení.', 'mwshop') . ' <a target="_blank" href="' . mwSetting()->getObject(MWS_SHIPPING_SLUG)->getUrl() . '">' . __('Správa způsobů doručení', 'mwshop') . '</a></div>';
			} else {
				$content .= '<div class="mw_error_box">' . __('Není definován žádný způsob doručení, objednávku nelze dokončit.', 'mwshop') . '</div>';
			}
		}

		// TODO refactor javascripts for eshop

		if ($jsParentSelector) { // only for eshop
			$unit = MwsCurrencyEnum::getSymbol($currency);
				$content .= "<script>
					var prices = JSON.parse('" . json_encode($prices) . "');
					var priceUnit = '" . $unit . "';
					var text_zeroPrice = '" . __('zdarma', 'mwshop') . "';
					var text_makeSelection = '" . __('(proveďte výběr)', 'mwshop') . "';
					var text_InvalidPayType = '" . __('(zvolte jiný způsob platby)', 'mwshop') . "';
					var reinit = " . ($jsParentSelector === '#mws_quick_order' ? 'true' : 'false') . ";

					jQuery(document).ready(function ($) {
						initShippingAndPaymentInputs('" . $jsParentSelector . "', prices, priceUnit, text_zeroPrice, text_makeSelection, text_InvalidPayType);
					});
				</script>";
		}

		return $content;
	}

	public static function minFreeFrom(): ?MwsPrice
	{
		/** @var MwsPrice|null $minFreeFrom */
		$minFreeFrom = null;
		foreach (self::getAll() as $shipping) {
			$freeFrom = $shipping->getFreeFrom();
			if ($freeFrom && (!$minFreeFrom || $freeFrom->getPriceVatIncluded() < $minFreeFrom->getPriceVatIncluded())) {
				$minFreeFrom = $freeFrom;
			}
		}

		return $minFreeFrom;
	}

	public function isElectronic(): bool
	{
		return false;
	}

}

final class MwsShippingElectronic extends MwsShipping
{
	const id = MWS_SHIPPING_ID_ELECTRONIC;

	private static $_instance = null;

	protected function __construct()
	{
		$post = new WP_Post(new stdClass());
		$post->ID = -1;
		$post->post_title = __('Elektronicky', 'mwshop');
		$post->post_type = MWS_SHIPPING_SLUG;
		$post->post_excerpt = __('Zboží vám bude zasláno elektronicky na váš email.', 'mwshop');

		$this->_meta = [
			'price' => 0,
			'cod_price' => 0,
			'cod_enabled' => false,
			'personal_pickup' => false,
		];
		parent::__construct($post);
	}

	public static function getInstance(): self
	{
		if (!static::$_instance) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	public function isElectronic(): bool
	{
		return true;
	}

}
