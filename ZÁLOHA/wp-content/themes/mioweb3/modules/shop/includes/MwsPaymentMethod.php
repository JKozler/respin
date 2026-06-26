<?php

/** Name of the meta key of product. */
define('MWS_PAYMENT_METHOD_META_KEY', 'payment_method');

class MwsPaymentMethod extends mwPost
{

	private $_type;

	private $_paymentGatewayId = null;

	/** This field is currently used only for FAPI wireOnline payment methods */
	private ?string $bank = null;

	private ?MwsPaymentGateway $_gateway = null;

	public function __construct(WP_Post $post)
	{
		parent::__construct($post);
		$this->load();
	}

	public function getName(): string
	{
		return $this->getPost()->post_title ?: MwsPayType::getCaption($this->getType());
	}

	public function getExcerpt(int $wordCount = 0, bool $contentIfEmpty = false): string
	{
		return $this->getPost()->post_excerpt ?: MwsPayType::getDescription($this->getType());
	}

	public function getType(): string
	{
		return $this->_type;
	}

	public function getBank(): ?string
	{
		return $this->bank;
	}

	public function setBank(?string $bank): void
	{
		$this->bank = $bank;
	}

	public function getPaymentGatewayId(): ?string
	{
		return $this->_paymentGatewayId;
	}

	public function getGateway(): ?MwsPaymentGateway
	{
		if ($this->_gateway === null) {
			if (!$this->isGateway() || !$this->getPaymentGatewayId()) {
				return null;
			}

			$this->_gateway = MWS()->getPaymentGatewayById($this->getPaymentGatewayId());
		}

		return $this->_gateway;
	}

	public function isPaymentGatewayConnected(): bool
	{
		if (!$this->isGateway() || $this->getPaymentGatewayId() === null) {
			return false;
		}

		$api = mwApiConnect()->getApi($this->getPaymentGatewayId());
		if ($api === null) {
			return false;
		}

		return $api->isConnected();
	}

	public function isGateway(): bool
	{
		return MwsPayType::isGateway($this->getType());
	}

	public function isCod(): bool
	{
		return $this->getType() === MwsPayType::Cod;
	}

	public function isWireOnline(): bool
	{
		return $this->getType() === MwsPayType::WireOnline;
	}

	/**
	 * Returns supported currencies based on multiple parameters (country, electronic contained, gateway settings, ...)
	 *
	 * @return string[]
	 */
	public function getAllowedCurrencies(?string $country = null, bool $isElectronicContained = false): array
	{
		$result = [];

		$allCurrencies = MWS()->getCurrencies();
		$country ??= MWS()->getDefaultShippingCountry();
		foreach ($allCurrencies as $currency) {
			if (MWS()->isPaymentMethodAllowed($this, $currency, $country, $isElectronicContained)) {
				$result[] = $currency;
			}
		}

		return $result;
	}

	/**
	 * Checks enabled currencies directly at gateway. Ignores local payment methods and returns empty arrays for them.
	 *
	 * @return string[]
	 */
	public function getGatewaySupportedCurrencies(): array
	{
		$gateway = $this->getGateway();

		if ($gateway === null) {
			return [];
		}

		return $gateway->getEnabledCurrenciesForPaymentMethod($this);
	}

	private function load(): void
	{
		$meta = get_post_meta($this->getId(), MWS_PAYMENT_METHOD_META_KEY)[0] ?? [];
		$typeParts = explode(':', $meta['type']);
		$this->_type = $typeParts[0];
		$this->_paymentGatewayId = $typeParts[1] ?? null;
	}

	/** @return mixed[] */
	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'type' => $this->getType(),
			'excerpt' => $this->getExcerpt(),
			'gateway_id' => $this->getPaymentGatewayId(),
		];
	}

	/** @return MwsPaymentMethod[]. */
	public static function getAll($args = [], $paged = false): array
	{
		$default_args = [
			'post_type' => MWS_PAYMENT_METHOD_SLUG,
			'post_status' => 'publish',
			'posts_per_page' => -1,
		];

		$query_args = array_merge($default_args, $args);

		return self::getQuery($query_args, $paged);
	}

	public static function getOneById(int $paymentMethodId, bool $forceRecache = false): ?self
	{
		$post = get_post($paymentMethodId);
		if ($post) {
			try {
				return static::createNew($post, $forceRecache);
			} catch (MwsException $e) {
				mwshoplog(
					sprintf(__('Nepodařilo se vytvořit instanci paltebni metody [%d] se zprávou: %s', 'mwshop'), $paymentMethodId, $e->getMessage()),
					MWLL_ERROR
				);
			}
		}

		return null;
	}


	/** @param MwsPaymentMethod[] $paymentMethods */
	public static function htmlRadio(
		array $paymentMethods,
		string $currency,
		?string $country = null,
		?MwsPaymentMethod $selected = null,
		?string $jsParentSelector = null,
	): string
	{
		$selectedValue = $selected !== null ? $selected->getId() : null;
		$selectedBank = $selected !== null ? $selected->getBank() : null;

		$prefix = 'mws_payment';
		//Hidden input for AJAX error messages.
		$content = '<input type="hidden" name="' . $prefix . '" />';

		if (count($paymentMethods)) {
			$content .= '<div class="mws_radio_select_list">';
			foreach ($paymentMethods as $paymentMethod) {
				$value = $id = $paymentMethod->getId();
				$title = $paymentMethod->getName();
				$description = $paymentMethod->getExcerpt() ?: '';

				$allowedCurrencies = $paymentMethod->getAllowedCurrencies($country);
				$content .= '<div class="mws_payment_radio_container">' . self::getRadioSelectContent($paymentMethod, $prefix, $value, $selectedValue, $title, $allowedCurrencies, null, $description);

				if ($paymentMethod->getType() === MwsPayType::WireOnline) {
					$gw = MWS()->gateways()->getDefault();
					$gwPaymentMethods = array_filter($gw->getEnabledPayments(), static function (array $gwPaymentMethod): bool {
						return $gwPaymentMethod['payment_type'] === MwsPayType::WireOnline && isset($gwPaymentMethod['bank']);
					});

					if ((bool) $gwPaymentMethods) {
						$class = $selectedValue === $value ? '' : 'cms_nodisp';
						$content .= '<div class="mws_payment_sub_radio' . ((bool) $class ? ' ' . $class : '') . '">';

						// Check for allowed currencies and countries
						$gwPaymentMethods = array_map(function (array $gwPaymentMethod) use ($paymentMethod, $currency, $country): array {
							$bank = $gwPaymentMethod['bank'];
							$paymentMethod->setBank($bank);
							$allowedCurrencies = $paymentMethod->getAllowedCurrencies($country);
							$allowed = in_array(strtolower($currency), $allowedCurrencies, true);

							$gwPaymentMethod['allowed'] = $allowed;
							$gwPaymentMethod['allowedCurrencies'] = $allowedCurrencies;

							return $gwPaymentMethod;
						}, $gwPaymentMethods);

						// Remove selected bank if it is not allowed
						if ($selectedBank !== null) {
							foreach ($gwPaymentMethods as $gwPaymentMethodTmp) {
								if ($gwPaymentMethodTmp['bank'] === $selectedBank && !$gwPaymentMethodTmp['allowed']) {
									$selectedBank = null;

									break;
								}
							}
						}

						// Render
						foreach ($gwPaymentMethods as $gwPaymentMethod) {
							$bank = $gwPaymentMethod['bank'];
							$bankName = $gwPaymentMethod['name'] ?? $bank;
							$imageUrl = $gwPaymentMethod['image_url'] ?? null;

							$paymentMethod->setBank($bank);

							if ($selectedBank === null && $gwPaymentMethod['allowed']) {
								$selectedBank = $bank;
							}

							$bankClass = !$gwPaymentMethod['allowed'] ? 'novisible' : null;
							$content .= self::getRadioSelectContent($paymentMethod, 'mws_payment_bank_' . $id, $bank, $selectedBank, $bankName, $gwPaymentMethod['allowedCurrencies'], 'mws_payment_bank_radio ' . ($bankClass ?? ''), null, $imageUrl);
						}

						$content .= '</div>';
					}
				}

				$content .= '</div>';
			}

			$content .= '</div>';
		} else {
			if (MWS()->edit_mode) {
				$content .= '<div class="mw_error_box">' . __('Není definován žádný způsob platby. Možné způsoby platby lze povolit v horní liště v Eshop -> Nastavení eshopu -> Platební metody.', 'mwshop') . '</a></div>';
			} else {
				$content .= '<div class="mw_error_box">' . __('Není definován žádný způsob platby, objednávku nelze dokončit.', 'mwshop') . '</div>';
			}
		}

		if ($jsParentSelector !== null) {
			$content .= "<script>
					jQuery(document).ready(function ($) {
						initPaymentInputs('" . $jsParentSelector . "');
					});
				</script>";
		}

		return $content;
	}

	/**
	 * @param string $prefix
	 * @param int|string $value
	 * @param int|string|null $selected
	 */
	private static function getRadioSelectContent(MwsPaymentMethod $paymentMethod, string $prefix, $value, $selected, string $title, array $allowedCurrencies, ?string $class = null, ?string $description = null, ?string $imageUrl = null): string
	{
		$labelClass = $prefix . '_radio ' . $prefix . '_radio_' . $value;
		$labelClass .= $paymentMethod->isCod() ? ' ' . $prefix . '_radio_cod' : '';

		if ($class !== null) {
			$labelClass .= ' ' . $class;
		}

		return '<label class="mws_radio_select ' . $labelClass . '">'
			. '<input type="radio" class="mw_radio_button" name="' . $prefix . '" value="' . $value . '"' . ($value == $selected ? ' checked' : '') . ' data-is-cod="' . ($paymentMethod->getType() == MwsPayType::Cod ? 1 : 0) . '" data-currencies=\'' . json_encode($allowedCurrencies) . '\' />'
			. '<div class="mws_radio_select_content">'
			. '<div class="mws_radio_select_left">'
			. '<span>' . $title . '</span>'
			. ($imageUrl !== null ? '<div class="mws_payment_logo ' . $prefix . '_creditcards_image"><img src="' . $imageUrl . '" height="20" alt=""></div>' : '')
			. ($description ? '<div class="mws_help_container">?<span>' . $description . '</span></div>' : '')
			. '</div>'
			. '<div class="mws_radio_select_right">'
			. ($paymentMethod->isCod() ? '<span class="mws_cod_price_container"></span>' : '')
			. '</div>'
			. '</div>'
			. '</label>';
	}

	public static function createNew(WP_Post $post, bool $forceUpdateCache = false): ?self
	{
		if ($forceUpdateCache || !($obj = MwObjectCache::get(static::class, $post->ID))) {
			$obj = new self($post);
			\assert($obj instanceof self);

			// TODO refactor
			// Do not save bank parameter into cache because there is just one main payment method for many banks and it is dynamically changing
			$objClone = clone $obj;
			$objClone->setBank(null);
			MwObjectCache::add($objClone, $objClone->getId());
		}

		return $obj;
	}

}
