<?php
use Mioweb\VisualEditor\Lib\Link;
use Mioweb\Shop\Upsell;

class MwsForm extends mwPost
{

	/** @var array|null */
	private $_basicSettings = null;

	/** @var array|null */
	private $_visibilitySettings = null;

	/** @var array|null */
	private $_automationSettings = null;

	/** @var mixed[]|null */
	private ?array $_upsellSettings = null;

	public static function getAll($args = [], $paged = true): array
	{
		$default_args = [
			'post_type' => MWS_FORM_SLUG,
			'post_status' => 'any',
			'posts_per_page' => -1,
		];

		$query_args = array_merge($default_args, $args);

		return self::getQuery($query_args, $paged);
	}

	/** @throws MwsException */
	public function getBasicSettings(bool $refresh = false): array
	{
		if ($refresh || $this->_basicSettings === null) {
			$this->_basicSettings = $this->getSettings('mws_sale_form', true);
		}

		return $this->_basicSettings;
	}

	/** @throws MwsException */
	public function getVisibilitySettings(bool $refresh = false): array
	{
		if ($refresh || $this->_visibilitySettings === null) {
			$this->_visibilitySettings = $this->getSettings('mws_sale_form_visibility');
		}

		return $this->_visibilitySettings;
	}

	/** @throws MwsException */
	public function getAutomationSettings(bool $refresh = false): array
	{
		if ($refresh || $this->_automationSettings === null) {
			$this->_automationSettings = $this->getSettings('mws_sale_form_automation');
		}

		return $this->_automationSettings;
	}

	public function getAutomations(bool $refresh = false): array
	{
		$a = $this->getAutomationSettings($refresh);

		return isset($a['actions']) && is_array($a['actions']) ? $a['actions'] : [];
	}

	/** @throws MwsException */
	public function getUpsellSettings(bool $refresh = false): array
	{
		if ($refresh || $this->_upsellSettings === null) {
			$settings = $this->getSettings('mws_sale_form_upsell');

			if (!isset($settings['upsells']) || $settings['upsells'] === '') {
				$settings['upsells'] = [];
			}

			$this->_upsellSettings = $settings;
		}

		return $this->_upsellSettings;
	}

	/** @return Upsell[] */
	public function getUpsells(bool $refresh = false): array
	{
		$settings = $this->getUpsellSettings($refresh)['upsells'] ?? [];

		$result = [];

		foreach ($settings as $upsellArr) {
			$id = $upsellArr['id'];
			\assert(is_numeric($id));
			$upsell = Upsell::getOneById((int) $id);
			\assert($upsell === null || $upsell instanceof Upsell);

			if ($upsell !== null) {
				$result[(int) $id] = $upsell;
			}
		}

		return $result;
	}

	/** @return Upsell[] */
	public function getValidUpsells(bool $refresh = false): array
	{
		return array_filter($this->getUpsells(), function (Upsell $upsell): bool {
			return $upsell->isValid();
		});
	}

	public function isTest(): bool
	{
		return $this->getStatus() === 'test';
	}

	public function isSimplifiedAllowed(): bool
	{
		if (MWS()->getEshopCountry() === MwsCountry::CZ) {
			return MWS()->gateways()->getDefault()->isSimplifiedInvoiceAllowedForForm($this);
			//return (bool) ($form->getVisibilitySettings()['allow_simply_form'] ?? false);
		}

		return false;
	}

	/** @throws MwsException */
	public function getProductId(): int
	{
		return (int) $this->getBasicSettings()['product'];
	}

	public function getProduct(): ?MwsProduct
	{
		return MwsProduct::getOneById($this->getProductId());
	}

	public function isMiniupsellAllowed(): bool
	{
		return (bool) isset($this->getBasicSettings()['sell_miniupsell']);
	}

	public function getMiniupsellId(): int
	{
		return (int) $this->getBasicSettings()['miniupsell'] ?? 0;
	}

	public function getMiniupsell(): ?MwsProduct
	{
		if ($this->isMiniupsellAllowed() && $this->getMiniupsellId()) {
			return MwsProduct::getOneById($this->getMiniupsellId());
		}

		return null;
	}

	/** @throws MwsException */
	public function getThxPage(): ?string
	{
		global $vePage;
		$thxPageId = $this->getBasicSettings()['thx_page'] ?? null;

		return $thxPageId !== null ? Link::create_link(['page' => $thxPageId]) : null;
	}

	/** @throws MwsException */
	public function hasOwnPaymentAndShipping(): bool
	{
		return isset($this->getBasicSettings()['own_payment_shipping']) ? true : false;
	}

	/**
	 * @return MwsPaymentMethod[]
	 * @throws MwsException
	 */
	public function getPaymentMethods($excludedPaymentType = []): array
	{
		$methods = [];
		$return = [];

		if ($this->hasOwnPaymentAndShipping() && isset($this->getBasicSettings()['payments'])) {
			foreach (array_keys($this->getBasicSettings()['payments'] ?? []) as $paymentMethodId) {
				$paymentMethod = MwsPaymentMethod::getOneById($paymentMethodId);

				if ($paymentMethod !== null) {
					$methods[$paymentMethod->getId()] = $paymentMethod;
				}
			}
		}

		if (!count($methods)) {
			$methods = MWS()->getPaymentMethods();
		}

		if (count($excludedPaymentType)) {
			foreach ($methods as $paymentMethod) {
				if (!in_array($paymentMethod->getType(), $excludedPaymentType)) {
					$return[$paymentMethod->getId()] = $paymentMethod;
				}
			}
		} else {
			$return = $methods;
		}

		return $return;
	}

	/**
	 * @return MwsShipping[]
	 * @throws MwsException
	 */
	public function getShippingMethods($excludedShippingType = []): array
	{
		$methods = [];
		$return = [];

		if ($this->hasOwnPaymentAndShipping() && isset($this->getBasicSettings()['shippings'])) {
			foreach (array_keys($this->getBasicSettings()['shippings'] ?? []) as $shippingMethodId) {
				$shippingMethod = MwsShipping::getOneById($shippingMethodId);

				if ($shippingMethod !== null) {
					$methods[$shippingMethod->getId()] = $shippingMethod;
				}
			}
		}

		if (!count($methods)) {
			$methods = MwsShipping::getAll([], false);
		}

		if (count($excludedShippingType)) {
			foreach ($methods as $shippingMethod) {
				if (!in_array($shippingMethod->getType(), $excludedShippingType)) {
					$return[$shippingMethod->getId()] = $shippingMethod;
				}
			}
		} else {
			$return = $methods;
		}

		return $return;
	}

	/** @throws MwsException */
	public function allowDiscountCodes(): bool
	{
		return isset($this->getBasicSettings()['allow_discount_codes']) ? true : false;
	}

	public function getFormStatus(): string
	{
		return $this->getStatus() == 'test' ? 'test' : 'publish';
	}

	/** @throws MwsException */
	private function getSettings(string $setId, bool $required = false): array
	{
		$settings = MWDB()->getPostMeta($this->getId(), $setId, true);
		if ($required && !$settings) {
			throw new MwsException('Meta for "' . $setId . '" is empty.');
		}

		return $settings ?: [];
	}

}

class mwSettingObjectService_SaleForm extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Název', 'cms'),
				],
				[
					'content' => '',
				],
				[
					'content' => __('Objednávek', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Tržby celkem', 'cms'),
					'align' => 'right',
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();

		$query = MwsForm::getAll();

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		if ($query['count']) {
			$statistics = new MwSellStatistics();
			$unit = MWS()->getDefaultCurrency();

			foreach ($query['items'] as $item) {
				$itemStats = $statistics->getBySource($item->getId());

				$args['rows'][] = [
					'cols' => [
						[
							'content' => '<a class="mw_link" href="' . $this->object()->getEditUrl($item->getId()) . '">' . $item->getName() . '</a>',
						],
						[
							'content' => $item->isTest() ? mwAdminComponents::textLabel(['text' => __('Testovací', 'cms')]) : '',
						],
						[
							'content' => $itemStats ? $itemStats['count'] : 0,
							'align' => 'center',
						],
						[
							'content' => number_format(($itemStats ? $itemStats['total']->getPriceVatIncluded() : 0), 2, '.', ' ') . ' ' . $unit,
							'align' => 'right',
						],
						[
							'content' => mwSetting::printSettingActions(['edit', 'duplicate', 'delete'], $item->getId(), $this->object()),
							'align' => 'right',
						],
					],
				];
			}
		}

		return $args;
	}

	function printFormSidebar($item, $add = false, $inPopup = false): string
	{
		$content = '<div class="mw_setting_object_detail_sidebar">';

		$content .= '<div class="mw_setting_sidebar_box">';

		$status = $item !== null ? $item->getFormStatus() : 'publish';
		$content .= mwAdminComponents::statusSelect([
			'title' => __('Režim formuláře', 'cms'),
			'show_list' => true,
			'input' => 'visibility',
			'list' => [
				'publish' => [
					'text' => __('Veřejný', 'cms'),
					'status' => 'ok',
					'icon' => 'check',
				],
				'test' => [
					'text' => __('Testovací', 'cms'),
					'status' => 'processing',
					'icon' => 'info',
				],
			],
		], $status, 'mw_setting_sidebar_visibility');

		if (!$add) {
			$content .= $this->getInfoList($item);
			if (!$inPopup) {
				$content .= $this->getDetailActionList($item);
			}
		}
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	public function setVisibility($id, $visibility = 'publish')
	{
		wp_update_post([
			'ID' => $id,
			'post_status' => $visibility,
		]);
	}

	public function beforeSaveActions($itemId, $tosave): array
	{
		$form = MwsForm::getOneById((int) $itemId);
		\assert($form instanceof MwsForm);

		$oldSettings = $form->getUpsellSettings()['upsells'] ?? null;
		$newSettings = $tosave['mws_sale_form_upsell']['upsells'] ?? [];

		if (is_array($newSettings)) {
			// Delete removed
			foreach ((array) $oldSettings as $oldSetting) {
				$used = false;
				foreach ($newSettings as $newSetting) {
					if ((int) $oldSetting['id'] === (int) $newSetting['id']) {
						$used = true;

						break;
					}
				}

				if (!$used) {
					wp_delete_post($oldSetting['id']);
				}
			}

			$tosave['mws_sale_form_upsell'] = [];
			$tosave['mws_sale_form_upsell']['upsells'] = $newSettings;

			// Save upsell objects
			foreach ($newSettings as $newSetting) {
				$upsell = Upsell::getOneById($newSetting['id']);
				\assert($upsell instanceof Upsell || $upsell === null);
				if ($upsell !== null) {
					$upsell->setFormId($form->getId());
					$price = isset($newSetting['price']) && $newSetting['price'] ? (float) $newSetting['price'] : null;
					$upsell->setPrice($price);
					$priceSale = isset($newSetting['price_sale']) && $newSetting['price_sale'] ? (float) $newSetting['price_sale'] : null;
					$upsell->setPriceSale($priceSale);
					$isCustomPrice = isset($newSetting['custom_price']) && ($priceSale !== null || $price !== null);
					$upsell->setIsCustomPrice($isCustomPrice);
					$upsell->save();
				}
			}
		} else {
			unset($tosave['mws_sale_form_upsell']);
			foreach ((array) $oldSettings as $oldSetting) {
				wp_delete_post($oldSetting['id']);
			}
		}

		$unusedUpsells = Upsell::getAllByFormId(null);
		if (count($unusedUpsells) > 0) {
			foreach ($unusedUpsells as $unusedUpsell) {
				wp_delete_post($unusedUpsell->ID);
			}
		}

		return $tosave;
	}

	public function delete($id, $force_delete = false)
	{
		$upsellsMeta = MWDB()->getPostMeta($id, 'mws_sale_form_upsell', true);
		$settings = $upsellsMeta['upsells'] ?? [];

		foreach ($settings as $upsellArr) {
			wp_delete_post($upsellArr['id'], true);
		}

		wp_delete_post($id, true);
	}

}
