<?php

class MwsShippingCountry
{

	private $_id;

	private $_country;

	public function __construct(int $id, array $countrySetting)
	{
		$this->_id = $id;
		$this->_country = $countrySetting['country'];
	}

	public function getId()
	{
		return $this->_id;
	}

	public function getCountry()
	{
		return $this->_country;
	}

	public function getName()
	{
		return MwsCountry::getCaption($this->_country);
	}

	public function getUrl()
	{
		return '';
	}

	/**
	 * Get discount code instance by member section ID.
	 */
	public static function getOneById(int $id): ?self
	{
		$countries = self::getAll();
		foreach ($countries as $country) {
			if ($country->getId() == $id) {
				return $country;
			}
		}

		return null;
	}

	public static function getOneByCountry(string $country): ?self
	{
		$countries = self::getAll();
		foreach ($countries as $c) {
			if ($c->getCountry() == $country) {
				return $c;
			}
		}

		return null;
	}

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 */
	public static function createNew(int $id, array $country): self
	{
		return new self($id, $country);
	}

	public static function getAll(): array
	{
		$countries = get_option(MWS_OPTION_SHIPPING_COUNTRIES, []);
		$ret = [];
		foreach ($countries as $cId => $country) {
			$ret[] = self::createNew($cId, $country ?? []);
		}

		return $ret;
	}

	public static function getList(): array
	{
		$countries = get_option(MWS_OPTION_SHIPPING_COUNTRIES, []);

		return array_column($countries, 'country');
	}

}

class mwSettingObjectService_ShippingCountries extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Země', 'mwshop'),
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

		$countries = MwsShippingCountry::getAll();
		$defaultCountry = MWS()->getDefaultShippingCountry(true);

		foreach ($countries as $item) {
			$checked = $defaultCountry === $item->getCountry() ? 'checked' : '';

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
				'attrs' => 'data-objectid="' . MWS_SHIPPING_COUNTRY_SLUG . '" data-itemid="' . $item->getCountry() . '"',
			], 'mwtl_default_tocheck mwtl_set_default');
			$default_select .= '</div>';

			$args['rows'][] = [
				'bulk_id' => $item->getId(),
				'cols' => [
					[
						'content' => $item->getName(),
					],
					[
						'content' => $default_select,
					],
					[
						'content' => count($countries) > 1 && $item->getCountry() !== $defaultCountry ? mwSetting::printSettingActions(['delete'], $item->getId(), $this->object()) : '',
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function titleButton($text): string
	{
		if (count(MwsCountry::getAll()) > count(MwsShippingCountry::getList())) {
			return mwAdminComponents::button([
				'button_text' => $text,
				'attrs' => 'data-object="' . $this->object()->getId() . '" data-return="redirect"',
				'icon' => 'plus',
			], 'mw_setting_fast_add');
		}

		return '';
	}

	public function getMeta($itemId, $setId): array
	{
		$option = get_option(MWS_OPTION_SHIPPING_COUNTRIES, []);

		return $option[$itemId] ?? [];
	}

	function add($tosave, $fast = false): int
	{
		if (isset($tosave['country']['country'])) {
			$countries = get_option(MWS_OPTION_SHIPPING_COUNTRIES);

			if ($countries === false) {
				$new_id = 1;
			} else {
				$max = max(array_keys($countries));
				$new_id = $max + 1;
			}

			$countries[$new_id] = $tosave['country'];
			update_option(MWS_OPTION_SHIPPING_COUNTRIES, $countries);

			return $new_id;
		}

		return -1;
	}

	public function save($itemId, $tosave)
	{
		$countries = get_option(MWS_OPTION_SHIPPING_COUNTRIES, []);
		$countries[$itemId] = $tosave['country'];
		update_option(MWS_OPTION_SHIPPING_COUNTRIES, $countries);
	}

	function delete($id, $force_delete = false)
	{
		$option = get_option(MWS_OPTION_SHIPPING_COUNTRIES, []);
		if (count($option) > 1) {
			if (isset($option[$id])) {
				if ($option[$id]['country'] !== MWS()->getDefaultShippingCountry()) {
					unset($option[$id]);
				}
			}
			update_option(MWS_OPTION_SHIPPING_COUNTRIES, $option);
		}
	}

	public function setDefaultItem($id): bool
	{
		return MwsShippingCountry::getOneByCountry($id) ? update_option(MWS_OPTION_DEFAULT_SHIPPING_COUNTRY, $id) : false;
	}

}
