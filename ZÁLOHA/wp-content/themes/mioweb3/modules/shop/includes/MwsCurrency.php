<?php

class MwsCurrency
{

	private $_id;

	private $_currency;

	private $_accountNumber;

	private $_exchangeRate;

	private bool $_fixedExchangeRate;

	private $_iban;

	private $_bic;

	private $_roundOrders;

	private $_roundFunction;

	private $_roundPrecision;

	public function __construct(int $id, array $currencySetting)
	{
		$this->_id = $id;

		$this->_currency = $currencySetting['currency'];

		$this->_accountNumber = $currencySetting['account_number'] ?? null;

		$this->_exchangeRate = $currencySetting['exchange_rate'] ?? null;

		$this->_fixedExchangeRate = $currencySetting['fixed_exchange_rate'] ?? false;

		$this->_iban = $currencySetting['iban'] ?? null;

		$this->_bic = $currencySetting['bic'] ?? null;

		$this->_roundOrders = $currencySetting['round_orders'] ?? false;
		$this->_roundFunction = $currencySetting['round_function'] ?? null;
		$this->_roundPrecision = $currencySetting['round_precision'] ?? 2;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getCurrency(): string
	{
		return $this->_currency;
	}

	public function getName(): string
	{
		return MwsCurrencyEnum::getCaption($this->_currency);
	}

	public function getSymbol(): string
	{
		return MwsCurrencyEnum::getSymbol($this->_currency);
	}

	public function getAccountNumber(): string
	{
		return $this->_accountNumber ?: '';
	}

	public function getIban(): string
	{
		return $this->_iban ?: '';
	}

	public function getBic(): string
	{
		return $this->_bic ?: '';
	}

	public function getSwift(): string
	{
		return $this->_bic ?: '';
	}

	public function getBankAccount(): ?MwsBankAccount
	{
		if ($this->_accountNumber) {
			return new MwsBankAccount(
				$this->_accountNumber,
				$this->_iban,
				$this->_bic,
			);
		}

		return null;
	}

	public function roundingOrders(): bool
	{
		return $this->_roundOrders;
	}

	public function getRoundingFunction(): string
	{
		return $this->_roundFunction ?: 'ceil';
	}

	public function getRoundingPrecision(): int
	{
		return $this->_roundPrecision ? intval($this->_roundPrecision) : 0;
	}

	public function isFixedExchangeRate(): bool
	{
		return $this->_fixedExchangeRate;
	}

	public function getBankExchangeRate(): float
	{
		$defaultCurrency = MWS()->getDefaultCurrency('key');

		return MwsCurrencyEnum::getDefaultConversionTable($defaultCurrency, true)[$this->getCurrency()];
	}

	public function getFixedExchangeRate(): ?float
	{
		return (float) $this->_exchangeRate;
	}

	public function getExchangeRate(): float
	{
		return $this->isFixedExchangeRate() && $this->getFixedExchangeRate() ? $this->getFixedExchangeRate() : $this->getBankExchangeRate();
	}

	/**
	 * Get discount code instance by member section ID.
	 */
	public static function getOneById(int $id): ?self
	{
		$currencies = self::getAll();
		foreach ($currencies as $cur) {
			if ($cur->getId() == $id) {
				return $cur;
			}
		}

		return null;
	}

	public static function getOneByCurrency(string $currency): ?self
	{
		$currencies = self::getAll();
		foreach ($currencies as $cur) {
			if ($cur->getCurrency() == $currency) {
				return $cur;
			}
		}

		return null;
	}

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 */
	 public static function createNew(int $id, array $currency): self
	 {
		return new self($id, $currency);
	 }

	public static function getAll(): array
	{
		$currencies = get_option(MWS_OPTION_CURRENCIES, []);
		$ret = [];
		foreach ($currencies as $curId => $currency) {
			$ret[] = self::createNew($curId, $currency);
		}

		return $ret;
	}
	public static function getList(): array
	{
		$currencies = get_option(MWS_OPTION_CURRENCIES, []);

		return array_column($currencies, 'currency');
	}


}

class mwSettingObjectService_Currencies extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Měna', 'mwshop'),
				],
				[
					'content' => MWS()->getSelectedGatewayId() === 'mioweb' ? __('Číslo účtu', 'mwshop') : '',
				],
				[
					'content' => __('Výchozí', 'mwshop'),
					'align' => 'center',
				],
				[
					'content' => __('Akce', 'mwshop'),
					'align' => 'right',
				],
			],
		];

		$currencies = MwsCurrency::getAll();
		$defaultCurrency = MWS()->getDefaultCurrency('key', true);

		foreach ($currencies as $item) {
			$actions = count($currencies) > 1 && $item->getCurrency() !== $defaultCurrency ? ['edit', 'delete'] : ['edit'];

			$checked = $defaultCurrency === $item->getCurrency() ? 'checked' : '';

			$default_select = '<div class="mwtl_default_check ' . $checked . '">';
			$default_select .= mwAdminComponents::icon([
				'icon' => 'check',
				'title' => __('Výchozí', 'mwshop'),
			], 'mwtl_default_checked');
			$default_select .= mwAdminComponents::icon([
				'icon' => 'x',
				'title' => __('Nastavit jako výchozí', 'mwshop'),
			], 'mwtl_default_unchecked');
			$default_select .= mwAdminComponents::iconLink([
				'icon' => 'check',
				'title' => __('Nastavit jako výchozí', 'mwshop'),
				'attrs' => 'data-objectid="' . MWS_CURRENCY_SLUG . '" data-itemid="' . $item->getCurrency() . '"',
			], 'mwtl_default_tocheck mwtl_set_default');
			$default_select .= '</div>';

			$args['rows'][] = [
				'cols' => [
					[
						'content' => '<a class="mw_link" href="' . $this->object()->getEditUrl($item->getId()) . '">' . $item->getName() . '</a>',
					],
					[
						'content' => MWS()->getSelectedGatewayId() === 'mioweb' ? ($item->getAccountNumber() ?: '-') : '',
					],
					[
						'content' => $default_select,
					],
					[
						'content' => mwSetting::printSettingActions($actions, $item->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	function printFormSidebar($item, $add = false, $inPopup = false): string
	{
		$content = '<div class="mw_setting_object_detail_sidebar">';
		$default_currency = MWS()->getDefaultCurrency('key');

		if (!$add && !$inPopup && $default_currency !== $item->getCurrency()) {
			$content .= '<div class="mw_setting_sidebar_box">';
			$content .= $this->getDetailActionList($item);
			$content .= '</div>';
		}

		if ($item && $default_currency !== $item->getCurrency()) {
			$content .= '<div class="mw_setting_sidebar_box">';

			$tooltip = mwAdminComponents::tooltip([
				'tooltip_align' => 'left',
				'text' => __('Pokud nenastavíte fixní směnný kurz, tak se bude aktuální hodnota kurzu stahovat jednou denně z ČNB.', 'mwshop'),
			]);

			$content .= mwAdminComponents::title([
				'text' => __('Směnný kurz', 'mwshop') . $tooltip,
			]);

			$fixed = $item->isFixedExchangeRate();

			$content .= '<div class="mw_exchange_rate_field ' . ($fixed ? 'mw_exchange_rate_field_fixed' : '') . ' mw_onedit_action" data-type="currency_exchange">';

			$content .= '<div class="mw_exchange_rate_field_bank_container">'
				. '1 ' . $item->getSymbol() . ' = '
				. $item->getBankExchangeRate() . ' ' . MwsCurrencyEnum::getSymbol($default_currency)
			. '</div>';

			$content .= '<div class="mw_exchange_rate_field_fixed_container">';
			$content .= '<span>1 ' . $item->getSymbol() . ' =&nbsp;</span>';
			$content .= mwAdminComponents::input([
				'name' => 'currency[exchange_rate]',
			], $item->getExchangeRate());
			$content .= '<span>&nbsp;' . MwsCurrencyEnum::getSymbol($default_currency) . '</span>';
			$content .= '</div>';

			$content .= mwAdminComponents::switch([
				'switch_label' => __('Nastavit fixní směnný kurz', 'mwshop'),
				'name' => 'currency[fixed_exchange_rate]',
			], $fixed);

			$content .= '</div>';

			$content .= '</div>';
		}

		$content .= '</div>';

		return $content;
	}

	public function titleButton($text): string
	{
		$class = 'mws_add_currency_button';
		$maxCount = count(MwsCurrencyEnum::getAll());
		if ($maxCount <= count(MwsCurrency::getList()) && isset($_GET['added'])) {
			$class .= ' cms_nodisp';
		}

		return mwAdminComponents::button([
			'button_text' => $text,
			'link' => $this->object()->getAddUrl(),
			'icon' => 'plus',
			'attrs' => 'data-max="' . $maxCount . '" data-info="' . __('Další měnu nelze přidat. Máte již přidány všechny podporované měny.', 'mwshop') . '"',
		], $class);
	}

	public function getMeta($itemId, $setId)
	{
		$option = get_option(MWS_OPTION_CURRENCIES, []);

		return $option[$itemId] ?? [];
	}

	function add($tosave, $fast = false): int
	{
		if (isset($tosave['currency']['currency'])) {
			$currencies = get_option(MWS_OPTION_CURRENCIES);

			if (!(bool) $currencies) {
				$new_id = 1;
			} else {
				$max = max(array_keys($currencies));
				$new_id = $max + 1;
			}

			$currencies[$new_id] = $tosave['currency'];
			update_option(MWS_OPTION_CURRENCIES, $currencies);

			return $new_id;
		}

		return -1;
	}

	public function save($itemId, $tosave)
	{
		$currencies = get_option(MWS_OPTION_CURRENCIES, []);
		$currencies[$itemId] = $tosave['currency'];
		update_option(MWS_OPTION_CURRENCIES, $currencies);
	}

	function delete($id, $force_delete = false)
	{
		$option = get_option(MWS_OPTION_CURRENCIES, []);
		if (count($option) > 1) {
			if (isset($option[$id])) {
				if ($option[$id]['currency'] !== MWS()->getDefaultCurrency()) {
					unset($option[$id]);
				}
			}
			update_option(MWS_OPTION_CURRENCIES, $option);
		}
	}

	public function setDefaultItem($id): bool
	{
		return MwsCurrency::getOneByCurrency($id) ? update_option(MWS_OPTION_DEFAULT_CURRENCY, $id) : false;
	}

}
