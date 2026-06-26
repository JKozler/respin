<?php

use Mioweb\Shop\Upsell;
use Mioweb\VisualEditor\Lib\Image;

class MwShopFields
{

	function __construct()
	{
		// variants ajax
		add_action('wp_ajax_mwsChangeVariantParams', [$this, 'changeVariantParams_ajax']);

		// vat rates ajax
		add_action('wp_ajax_mwsGetVatRatesForm', [$this, 'getVatRatesForm_ajax']);
		add_action('wp_ajax_mwsSaveVatRatesForm', [$this, 'saveVatRatesForm_ajax']);
	}

	public static function installEshop(): string
	{
		$content = '<div class="mws_create_shop_container">';
		$content .= '<p>' . __('Eshop není aktivní. Je potřeba nejdříve eshop vytvořit.', 'mwshop') . '</p>';
		$content .= mwAdminComponents::button([
			'button_text' => __('Vytvořit eshop', 'mwshop'),
			'style' => 'secondary',
		], 'mws_create_eshop');
		$content .= '</div>';

		return $content;
	}

	// itemSelect
	public static function productSelect($args, $val, $fieldName, $fieldId = ''): string
	{
		$object = mwSetting()->getObject(MWS_PRODUCT_SLUG);
		$getAllArgs = $args['args'] ?? [
			'post_status' => isset($args['only_published']) && $args['only_published'] ? 'publish' : 'any',
		];
		$items = MwsProduct::getAll($getAllArgs, false);

		$options = [];
		if (isset($args['empty_text'])) {
			$options[] = [
				'name' => $args['empty_text'],
				'value' => 0,
			];
		}

		foreach ($items as $item) {
			if (!isset($args['hide_variants']) || !$args['hide_variants'] || !$item->hasVariants()) {
				$options[] = [
					'value' => $item->getID(),
					'name' => $item->getName(),
					'attrs' => 'data-url="' . $object->getEditUrl($item->getID()) . '"',
				];
			}
		}

		$class = '';
		$whisperer = $args['whisperer'] ?? true;
		$class .= $whisperer ? ' mw_whisperer' : '';

		$content = '<div class="mw_item_selector mw_flex_field ' . ($val ? 'selected' : '') . '">';

		$content .= mwAdminComponents::select([
			'name' => $fieldName,
			'tag_id' => $fieldId,
			'options' => $options,
		], $val, $class);

		if (isset($args['edit_button']) && $args['edit_button']) {
			$content .= mwAdminComponents::iconLink([
				'icon' => 'edit-2',
				'title' => __('Upravit', 'cms_ve'),
				'target' => '_blank',
				'link' => $val ? $object->getEditUrl($val) : '',
			], 'mw_icon_button mw_icon_button_edit');
		}
		if (isset($args['add_button']) && $args['add_button']) {
			$content .= mwAdminComponents::iconLink([
				'icon' => 'plus',
				'attrs' => 'data-object="' . $object->getId() . '"',
				'title' => __('Přidat', 'cms_ve'),
			], 'mw_icon_button mw_icon_button_add');
		}

		$content .= '</div>';

		return $content;
	}

	//paygate
	public static function paygateSelect($value, $name, $id, $field): string
	{
		$content = '<div class="mws_paygate_select">';

		$content .= '<div class="mw_selectbox">';

		foreach ($field['options'] as $key => $val) {
			if ($key === 'mioweb' || MWS()->isCreated()) {
				$class = '';

				if ($key == 'fapi') {
					$mwAPIConnectItem = mwApiConnect()->getApi('fapi');
					if (!$mwAPIConnectItem->isConnected()) {
						$class = 'mw_fapi_disconnected';
					}
				}

				$content .= '<a class="mw_selectbox_item mw_rounded mw_shadow_b ' . $class . ' ' . ($value == $key ? 'selected' : '') . '" href="#" data-api="' . $key . '">';
				$content .= '<div class="mw_selectbox_image_container">';
				if (isset($val['image'])) {
					$content .= '<img src="' . $val['image'] . '">';
				} elseif (isset($val['icon'])) {
					$content .= mwAdminComponents::icon([
						'icon' => $val['icon'],
					]);
				} elseif (isset($val['icon_set'])) {
					$content .= '<span class="mw_icon"><svg role="img"><use xlink:href="' . $val['icon_set'] . '"></use></svg></span>';
				}
				$content .= '</div>';
				$content .= '<span>' . $val['text'] . '</span>';
				$content .= '<input type="radio" name="' . $name . '" value="' . $key . '" ' . ($value == $key ? 'checked="checked"' : '') . ' />';
				$content .= mwAdminComponents::icon([
					'icon' => 'check',
				], 'mw_selectbox_selected_icon');
				$content .= '</a>';
			}
		}

		$content .= '</div>';

		$content .= '<div class="mws_paygate_selectbox_fapi_info ' . ($value == 'fapi' ? '' : 'cms_nodisp') . '">';
		if ($value == 'fapi') {
			if (!isset($mwAPIConnectItem) || !$mwAPIConnectItem->isConnected()) {
				$content .= mwAdminComponents::messageBox(__('Mioweb není propojen s FAPI, proto není prodej a fakturace funkční. Je potřeba <a class="mws_paygate_open_fapi_connect" data-api="fapi" href="#">propojit FAPI</a>.', 'mwshop'), [
					'type' => 'error',
				]);
			} else {
				try {
					$formLink = MWS()->gateways()->getById('fapi')->sharedInstance()->getFormLink();

					$content .= mwAdminComponents::messageBox(__('Pro účely eshopu se používá FAPI formulář: ', 'mwshop') . $formLink, [
						'type' => 'info_gray',
					]);
				} catch (MwsException $e) {
					$content .= mwAdminComponents::messageBox($e->getMessage(), [
						'type' => 'error',
					]);
				}
			}
			$content .= mwAdminComponents::messageBox(__('Samotný prodej je realizován pomocí speciálního prodejního formuláře ve FAPI. FAPI tedy vytváří objednávky a faktury, zajišťuje napojení na platební brány a emailovou komunikaci s klientem.', 'mwshop'), [
				'type' => 'info_gray',
			]);
		}
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	//currency
	public static function currencySelect($val, $name, $id, $field): string
	{
		$val = MwsCurrencyEnum::checkedValue($val, MwsCurrencyEnum::czk);
		$content = '';

		$options = [];
		foreach (MwsCurrencyEnum::getAll() as $cur) {
			if (!isset($field['exclude']) || !in_array($cur, $field['exclude'])) {
				$options[] = [
					'name' => esc_html(MwsCurrencyEnum::getCaption($cur)),
					'value' => esc_attr($cur),
				];
			}
		}

		if (count($options)) {
			$content .= mwAdminComponents::select([
				'name' => $name,
				'tag_id' => $id,
				'options' => $options,
			], $val, 'mws_currency_select');
		}

		return $content;
	}

	public static function currency($val, $name, $tagId, $itemId)
	{
		$item = $itemId ? MwsCurrency::getOneById($itemId) : null;

		if ($item) {
			$content = esc_html(MwsCurrencyEnum::getCaption($val));
			$content .= mwAdminComponents::input([
				'type' => 'hidden',
				'name' => $name,
			], $val);
		} else {
			$usedCurrencies = array_map(function ($currency) {
				return $currency->getCurrency();
			}, MwsCurrency::getAll());
			$excludedCurrencies = array_unique(array_merge($usedCurrencies, [MwsCurrencyEnum::rub]));
			$content = self::currencySelect('', $name, '', ['exclude' => $excludedCurrencies]);
		}

		return $content;
	}

	//country select
	public static function countrySelect($val, $name, $id = '', $field = [])
	{
		$content = '';
		$whisperer = false;

		$options = [];
		if (isset($field['allow_all']) && $field['allow_all']) {
			$options[] = [
				'value' => '',
				'name' => __('- Všechny země -', 'mwshop'),
			];
		} elseif (isset($field['allow_empty']) && $field['allow_empty']) {
			$options[] = [
				'value' => '',
				'name' => __('- Vyberte zemi -', 'mwshop'),
			];
		}

		if (isset($field['only_shipping_countries'])) {
			$countries = MWS()->getShippingCountries();
		} else {
			$countries = MWS()->getSupportedCountries();
			$whisperer = true;
		}

		foreach ($countries as $country) {
			if (!isset($field['exclude']) || !in_array($country, $field['exclude'])) {
				$options[] = [
					'value' => $country,
					'name' => MwsCountry::getCaption($country),
				];
			}
		}

		$content .= mwAdminComponents::select([
			'name' => $name,
			'tag_id' => $id,
			'options' => $options,
			'whisperer' => $whisperer,
		], $val);

		return $content;
	}

	public static function shippingCountry($val, $name, $tagId, $itemId)
	{
		$usedCountries = array_map(function ($currency) {
			return $currency->getCountry();
		}, MwsShippingCountry::getAll());
		$content = self::countrySelect('', $name, '', ['exclude' => $usedCountries]);

		return $content;
	}

	public function getVatRatesForm_ajax()
	{
		$vats = MWS()->getVATs();
		$shippingCountries = array_filter(MWS()->getShippingCountries(), function (string $countryCode): bool {
			return MwsCountry::isEUCountry($countryCode);
		});

		$eshopCountry = MwsCountry::checkedValue(MWS()->getSupplierContact()?->getAddress()?->getCountry(), MwsCountry::CZ);

		$foreignShippingCountries = array_filter($shippingCountries, function (string $countryCode) use ($eshopCountry): bool {
			return $countryCode !== $eshopCountry;
		});

		echo '<table>';
		if ($foreignShippingCountries) {
			echo '<thead>';
			echo '<tr>';
			echo '<th>Stát</th>';
			echo '<th>Sazba DPH</th>';
			echo '<th>E-publikace</th>';
			echo '<th>Tištěná publikace</th>';
			echo '</tr>';
			echo '</thead>';
		}

		echo '<tbody>';
		foreach ($shippingCountries as $countryCode) {
			// Ignore default shipping country
			$rowAttrs = $countryCode === $eshopCountry ? ' class="cms_nodisp"' : '';

			$country = MwsCountry::getCaption($countryCode);

			echo '<tr' . $rowAttrs . '>';
			echo '<td>' . mwAdminComponents::inputLabel(['label' => $country]) . '</td>';
			echo '<td>' . mwAdminComponents::input([
					'name' => MwsVatRateType::Standard . '[' . $countryCode . ']',
					'type' => 'number',
					'id' => 'mws_vat_rate_standard_' . $countryCode,
					'attrs' => 'step="0.1" max="100" min="0"',
			], $vats->getVatRate($countryCode, MwsVatRateType::Standard)) . '</td>';
			echo '<td>' . mwAdminComponents::input([
					'name' => MwsVatRateType::ElectronicPublication . '[' . $countryCode . ']',
					'type' => 'number',
					'id' => 'mws_vat_rate_epub_' . $countryCode,
					'attrs' => 'step="0.1" max="100" min="0"',
			], $vats->getVatRate($countryCode, MwsVatRateType::ElectronicPublication)) . '</td>';
			echo '<td>' . mwAdminComponents::input([
					'name' => MwsVatRateType::PrintedPublication . '[' . $countryCode . ']',
					'type' => 'number',
					'id' => 'mws_vat_rate_pub_' . $countryCode,
					'attrs' => 'step="0.1" max="100" min="0"',
			], $vats->getVatRate($countryCode, MwsVatRateType::PrintedPublication)) . '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';

		if (!$foreignShippingCountries) {
			echo __('Nemáte nastavenou žádnou zahraniční doručovací zemi v EU.', 'mwshop');
		}

		die();
	}

	public function saveVatRatesForm_ajax()
	{
		$success = true;

		try {
			parse_str($_POST['form'], $rates);
			if (!$rates) {
				throw new \Exception('Form is empty');
			}

			$rates = array_map(function (array $type) {
				return array_map(function (string $value) {
					$vat = (float) $value;

					return max(min($vat, 100.0), 0.0);
				}, $type);
			}, $rates);

			update_option(MWS_OPTION_SHOP_SETTING_VAT_RATES, $rates);
		} catch (\Throwable $e) {
			$success = false;
		}

		echo $success;
		die();
	}

	public static function numberSeries($value, $name, $id, $field)
	{
		$content = '<div class="mws_numb_series_field">';
		$content .= '<div class="set_form_subrow">';
		$content .= mwAdminComponents::input([
			'sublabel' => __('Předpona', 'cms'),
			'maxlength' => 5,
			'name' => $name . '[prefix]',
		], $value['prefix'], 'mws_num_series_prefix ' . $id);
		$content .= '</div>';
		$content .= '<div class="set_form_subrow">';

		$options = [];
		for ($i = 1; $i < 11; $i++) {
			$options[] = [
				'name' => $i,
				'value' => $i,
			];
		}

		$content .= mwAdminComponents::select([
			'sublabel' => __('Počet znaků', 'cms'),
			'name' => $name . '[characters]',
			'options' => $options,
		], $value['characters'], 'mws_num_series_characters');
		$content .= '</div>';
		$content .= '<div class="set_form_subrow">';
		$content .= mwAdminComponents::input([
			'sublabel' => __('Začít od čísla', 'cms'),
			'maxlength' => $value['characters'],
			'name' => $name . '[start]',
		], $value['start'], 'mws_num_series_start ');
		$content .= '</div>';

		$content .= '</div>';

		$content .= '<div class="mws_num_prefix_error_message ' . $id . ' cms_nodisp"><p class="cms_error_message">' . __('Povolené proměnné jsou: RRRR, RR a MM.', 'mwshop') . '</p></div>';

		$content .= '<div class="set_form_subrow mws_num_series_format">';
		$content .= 'Výsledné číslo: <strong></strong>';
		$content .= '</div>';

		return $content;
	}

	public static function eshopAutomations($value, $name, $id, $field): string
	{
		$args = [
			'tagid' => $id,
			'tagname' => $name,
			'texts' => [
				'add' => __('Přidat akci', 'mwshop'),
			],
			'open' => 'under',
			'title_function' => 'MwShopFields::automationItemHead',
			'content_function' => 'MwShopFields::automationItemContent',
		];
		$content = '<input type="hidden" autocomplete="off" name="' . $name . '" value="">';
		$content .= MwFields::multiElement($args, $value);

		return $content;
	}

	public static function automationItemHead($args, $item, $i): string
	{
		$content = '<div class="mws_automation_item_head_event">';
		$content .= isset($item['event']) && $item['event'] ? MwsAutomationEvent::getCaption($item['event']) : MwsAutomationEvent::getCaption(MwsAutomationEvent::OnOrder);
		$content .= '</div>';
		$content .= mwAdminComponents::icon([
			'icon' => 'arrow-right',
		]);
		$content .= '<div class="mws_automation_item_head_action">';
		$content .= isset($item['action']) && $item['action'] ? MwsAutomationAction::getCaption($item['action']) : MwsAutomationAction::getCaption(MwsAutomationAction::AddContact);
		if (isset($item['action']) && class_exists('mwMemberModule') && ($item['action'] == MwsAutomationAction::AddMembership || $item['action'] == MwsAutomationAction::RemoveMembership)) {
			$itemSet = $item['action'] == MwsAutomationAction::AddMembership ? $item['member_section'] : $item['remove_member_section'];
			if (isset($itemSet['section']) && $itemSet['section'] !== '') {
				$ms = MwMemberSection::getOneById($itemSet['section']);

				if ($ms) {
					$content .= ' <strong>';
					$content .= $ms->getName();
					$content .= '</strong>';
				}
			}
		}
		$content .= '</div>';

		return $content;
	}

	public static function automationItemContent($args, $value, $i): string
	{
		$name = $args['tagname'] . '[' . $i . ']';
		$id = $args['tagid'] . '_' . $i;

		$content = '<div class="mws_automation_event_container set_form_row">';

		// select event
		$content .= '<div class="mws_automation_event_left">';

		$content .= MwsAutomationEvent::getSelect([
			'name' => $name . '[event]',
			'tag_id' => $id . '_event',
		], $value['event'] ?? MwsAutomationEvent::OnOrder, 'mws_automation_select_event');

		$content .= '</div>';
		// icon
		$content .= mwAdminComponents::icon([
			'icon' => 'arrow-right',
		]);
		// select action
		$content .= '<div class="mws_automation_event_right">';

		$exclude = [MwsAutomationAction::SendFile];
		if (!class_exists('mwMemberModule')) {
			$exclude[] = MwsAutomationAction::AddMembership;
			$exclude[] = MwsAutomationAction::RemoveMembership;
		}

		$action = $value['action'] ?? MwsAutomationAction::AddContact;
		$content .= MwsAutomationAction::getSelect([
			'name' => $name . '[action]',
			'tag_id' => $id . '_action',
			'exclude' => $exclude,
		], $action, 'mws_automation_select_action');

		// add / remove contact
		$show = $action == MwsAutomationAction::AddContact || $action == MwsAutomationAction::RemoveContact ? 'show' : '';
		$content .= '<div class="set_form_subrow mws_automation_action_setting mws_aas_' . MwsAutomationAction::AddContact . '  mws_aas_' . MwsAutomationAction::RemoveContact . ' ' . $show . '">';
		$content .= mwAdminComponents::inputLabel([
			'label' => __('Vyberte seznam', 'mwshop'),
		]);
		$content .= mwEmailingApi()->generate_api_select($name . '[contact_list]', $id . '_contact_list', $value['contact_list'] ?? '', 'lists');
		$content .= '</div>';

		if (class_exists('mwMemberModule')) {
			// add / remove member
			$show = $action == MwsAutomationAction::AddMembership ? 'show' : '';
			$content .= '<div class="mws_automation_action_setting mws_aas_' . MwsAutomationAction::AddMembership . ' ' . $show . '">';
			$content .= '<div class="set_form_subrow">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Vytvořit přístup do členské sekce', 'mwshop'),
			]);
			$content .= MwMemberFields::membershipCreator($value['member_section'] ?? [], $name . '[member_section]', $id . '_member_section');
			$content .= '</div>';
			$content .= '</div>';

			// add / remove member
			$show = $action == MwsAutomationAction::RemoveMembership ? 'show' : '';
			$content .= '<div class="mws_automation_action_setting mws_aas_' . MwsAutomationAction::RemoveMembership . ' ' . $show . '">';
			$content .= '<div class="set_form_subrow">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Zrušit přístup do členské sekce', 'mwshop'),
			]);
			$content .= MwMemberFields::memberSectionSelect([
				'show_levels' => true,
				'sublabel' => __('Zrušit přístup do členských úrovní', 'cms_member'),
			], $value['remove_member_section'] ?? '', $name . '[remove_member_section]', $id . '_remove_member_section');
			$content .= '</div>';
			$content .= '</div>';
		}

		// url script
		$show = $action == MwsAutomationAction::RunScript ? 'show' : '';
		$content .= '<div class="set_form_subrow mws_automation_action_setting mws_aas_' . MwsAutomationAction::RunScript . ' ' . $show . '">';
		$content .= mwAdminComponents::inputLabel([
			'label' => __('URL adresa skriptu', 'mwshop'),
		]);
		$content .= mwAdminComponents::input([
			'name' => $name . '[script_url]',
		], $value['script_url'] ?? '');
		$content .= '</div>';

		// send email
		$show = $action == MwsAutomationAction::SendEmail ? 'show' : '';
		$content .= '<div class="set_form_subrow mws_automation_action_setting mws_aas_' . MwsAutomationAction::SendEmail . ' ' . $show . '">';
		$content .= mwAdminComponents::inputLabel([
			'label' => __('Vyberte email', 'mwshop'),
		]);
		$emails = MWS()->getEmailSetting()['custom_emails'] ?? [];
		$options = array_map(
			function ($key, $email) {
				return ['name' => $email['name'], 'value' => $key];
			},
			array_keys($emails),
			$emails
			);

		$content .= mwAdminComponents::select(
			[
				'name' => $name . '[email_index]',
				'options' => $options,
			],
			isset($value['email_index']) ? (int) $value['email_index'] : ''
		);

		$content .= '</div>';

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	public static function upsells($value, $name, $id, int $formId): string
	{
		$args = [
			'tagid' => $id,
			'tagname' => $name,
			'texts' => [
				'add' => __('Přidat upsell', 'mwshop'),
			],
			'open' => 'under',
			'style' => 'shadow',
			'title_function' => 'MwShopFields::upsellItemHead',
			'content_function' => 'MwShopFields::upsellItemContent',
			'valid_function' => 'MwShopFields::upsellValidation',
			'button_class' => 'mws_upsell_field_add',
		];
		$content = '<input type="hidden" autocomplete="off" name="' . $name . '" value="">';

		if (isset($_GET['copy'])) {
			foreach ($value as $key => $val) {
				$oldUpsell = Upsell::getOneById($val['id']);
				$newUpsell = $oldUpsell->duplicate();
				if ($newUpsell !== null) {
					$value[$key] = [
						'id' => $newUpsell->getId(),
					];
				} else {
					unset($value[$key]);
				}
			}
		}

		$content .= MwFields::multiElement($args, $value);

		return $content;
	}

	public static function upsellItemHead($args, $value, $i): string
	{
		$content = '';

		$upsell = Upsell::getOneById($value['id']);

		if ($upsell !== null && $upsell->isValid()) {
			$product = $upsell->getProduct();

			$content .= '<div class="mws_upsells_field_head">';
			$content .= '<div class="mws_upsells_field_title_container">';
			$content .= $product->getThumbnail()->getImg();
			$content .= '<div class="mws_upsells_field_title">';
			$content .= '<span>' . $product->getName() . '</span>';
			$editUrl = mwSetting()->getObject(MWS_UPSELL_SLUG)->service()->getItemUrl($upsell->getId());
			$content .= mwAdminComponents::iconLink([
				'text' => __('Upravit stránku upsellu', 'mwshop'),
				'icon' => 'edit',
				'target' => '_blank',
				'link' => $editUrl,
			], 've_item_head_link');
			$content .= '</div>';
			$content .= '</div>';
			$content .= '<div class="mws_upsells_field_price">';
			$fullPriceClass = $upsell->isDiscounted() ? '' : 'cms_nodisp';
			$content .= $upsell->getEndPriceFull()->htmlPriceVatIncluded(1, true, 'mws_upsells_field_price_full ' . $fullPriceClass);
			$content .= $upsell->getEndPrice()->htmlPriceVatIncluded(1, true, 'mws_upsells_field_price_end');
			$content .= '</div>';
			$content .= '</div>';
		}

		return $content;
	}

	public static function upsellItemContent($args, $value, $i): string
	{
		$name = $args['tagname'] . '[' . $i . ']';

		$upsell = Upsell::getOneById($value['id']);

		$content = '';

		if ($upsell !== null && $upsell->isValid()) {
			// ID
			$content .= '<input type="hidden" autocomplete="off" name="' . $name . '[id]" value="' . $upsell->getId() . '">';


			$content .= '<div class="set_form_row">';

			$editUrl = mwSetting()->getObject(MWS_PRODUCT_SLUG)->getEditUrl($upsell->getProduct()->getId());

			$content .= '<table class="mw_table mws_setting_form_table mws_order_popup_form_table_bottom_margin">';

			// Product
			$content .= '<tr>';
			$content .= '<td>' . __('Produkt', 'mwshop') . '</td>';
			/*
			$content .= '<td>';
			$content .= self::productSelect([
				'hide_variants' => true,
				'whisperer' => true,
				'edit_button' => true,
				'add_button' => true,
			], $upsell->getProduct()->getId(), $name . '[product_id]');
			$content .= '</td>';
			*/
			$content .= '<td><a class="mw_setting_action_link" target="_blank" href="' . $editUrl . '"> ' . $upsell->getProduct()->getName() . '</a></td>';
			$content .= '</tr>';

			// Prices
			$content .= '<tr>';
			$content .= '<td>' . __('Vlastní cena', 'mwshop') . '</td>';
			$content .= '<td>';
			$content .= mwAdminComponents::switch([
				'name' => $name . '[custom_price]',
				'switch_label' => '&nbsp;',
			], $upsell->isCustomPrice() ? 1 : 0, 'mws_upsell_custom_price_switch');
			$content .= '</td>';
			$content .= '</tr>';

			$content .= '<tr class="mws_upsell_price_container ' . ($upsell->isCustomPrice() ? '' : 'cms_nodisp') . '">';
			$content .= '<td>' . __('Cena', 'mwshop') . '</td>';
			$content .= '<td>';
			$content .= mwAdminComponents::inputNumber([
				'name' => $name . '[price]',
				'attrs' => 'data-original="' . $upsell->getOriginalPriceFull() . '"',
				'unit' => MWS()->getDefaultCurrency(),
			], $upsell->getPrice() ?? '', 'mws_upsell_price_input');
			$content .= '</td>';
			$content .= '</tr>';

			$content .= '<tr class="mws_upsell_price_container ' . ($upsell->isCustomPrice() ? '' : 'cms_nodisp') . '">';
			$content .= '<td>' . __('Cena po slevě', 'mwshop') . '</td>';
			$content .= '<td>';
			$content .= mwAdminComponents::inputNumber([
				'name' => $name . '[price_sale]',
				'attrs' => 'data-original="' . ($upsell->getOriginalPriceSale() ?? '') . '"',
				'unit' => MWS()->getDefaultCurrency(),
			], $upsell->getPriceSale() ?? '', 'mws_upsell_price_sale_input');
			$content .= '</td>';
			$content .= '</tr>';

			$content .= '</table>';

			$content .= '</div>';
		}

		return $content;
	}

	public static function addUpsellItem_ajax()
	{
		$upsell = null;
		if ($_POST['itemId']) {
			$product = MwsProduct::getOneById((int) $_POST['itemId']);
			if ($product === null) {
				throw new MwsException(sprintf('Product with ID "%s" not found', (string) $_POST['itemId']));
			}

			$upsell = Upsell::create($product, $_POST['template']);
		}

		$args = unserialize(base64_decode($_POST['settings']));
		//$args['upsell'] = $upsell;
		$item = [
			'id' => $upsell->getId(),
		];

		echo MwFields::multiElementItem($args, $item, $_POST['id'], true);
		die();
	}

	public static function upsellValidation($args, $value): bool
	{
		$upsell = Upsell::getOneById($value['id']);

		return ($upsell !== null && $upsell->isValid());
	}

	// variants
	public static function variantList($value, $name, $id, $field): string
	{
		$parameters = MwsProperty::getAll();
		$savedParams = $value['parametres'] ?? [];
		$isParametres = isset($value['parametres']);

		$content = '<div class="mws_variants_manager ' . ($isParametres ? 'mws_variants_parametres_seted' : '') . '">';

		// parametres
		$content .= '<div class="mws_variants_params">';
		$content .= '<h3>' . __('Vyberte ze kterých parametrů chcete vytvářet varianty', 'mws_shop') . '</h3>';

		$content .= '<ul>';
		foreach ($parameters as $param) {
			$val = isset($savedParams[$param->getId()]) ? 1 : 0;
			$content .= '<li>';
			$content .= mwAdminComponents::checkbox([
				'name' => $name . '[parametres][' . $param->getId() . ']',
				'value' => $param->getId(),
				'label' => $param->getName(),
			], $val, 'mws_variants_param_' . $param->getId());
			$content .= '</li>';
		}
		$content .= '</ul>';

		//$content .= mwAdminComponents::messageBox(__('Nejsou vytvořené žádné parametry produktu ze kterých lze vytvářet varianty. Nejdříve vytvořte parametry, ze kterých chcete varianty vytvářet.', 'mwshop'), ['type' => 'info_gray']);
		$object = mwSetting()->getObject(MWS_PROPERTY_SLUG);
		$content .= mwAdminComponents::iconLink([
			'text' => $object->getLabel('add_item'),
			'icon' => 'plus',
			'attrs' => 'data-object="' . $object->getId() . '" data-title="' . $object->getLabel('add_item') . '" data-return="list" data-name="' . $name . '[parametres][%id%]' . '" data-target=".mws_variants_params > ul"',
		], 'mw_setting_action_link mw_setting_fast_add');

		$content .= '<div class="mw_variants_parametres_buttons">';
		$content .= mwAdminComponents::button([
			'button_text' => __('Uložit výběr', 'mwshop'),
			'style' => 'secondary',
		], 'mws_save_params_list');
		$content .= mwAdminComponents::button([
			'button_text' => __('Zrušit', 'mwshop'),
			'style' => 'secondary_gray',
		], 'mws_close_params_list');
		$content .= '</div>';
		$content .= '</div>';

		// variants container
		$content .= '<div class="mws_variants_container">';

		// choose or change parametres for variants
		$content .= '<div class="mws_params_info_box">';
		$content .= '<div class="mws_params_info_text">' . __('Parametry variant', 'mwshop') . ': ';
		$chosenParams = [];
		foreach ($savedParams as $paramId) {
			$param = MwsProperty::getOneById($paramId);
			if ($param) {
				$chosenParams[] = $param->getName();
			}
		}
		$content .= '<strong>' . implode(', ', $chosenParams) . '</strong>';
		$content .= '</div>';
		$content .= mwAdminComponents::link([
			'text' => __('Změnit parametry', 'mwshop'),
		], 'mws_change_params_list mws_open_params_list');
		$content .= '</div>';

		$content .= mwAdminComponents::button([
			'button_text' => __('Vybrat parametry pro varianty', 'mwshop'),
			'style' => 'secondary',
		], 'mws_choose_params_list mws_open_params_list');

		// variants list
		$gw = MWS()->gateways()->getDefault();

		try {
			$enabledCodes = $gw ? $gw->getEnabledCodes(MWS()->canEdit()) : [];
		} catch (FapiGatewayCommunicationException $e) {
			$enabledCodes = MwsProductCode::getAll();
		}
		$args = [
			'tagid' => $id,
			'tagname' => $name,
			'texts' => [
				'add' => __('Přidat variantu', 'mwshop'),
			],
			'open' => 'under',
			'title_function' => 'MwShopFields::variantItemHead',
			'content_function' => 'MwShopFields::variantItemContent',
			'parametres' => $savedParams,
			'enable_codes' => $enabledCodes,

		];

		$variants = $value['variants'] ?? [];

		$content .= '<div class="mws_variants_list">';
		$content .= MwFields::multiElement($args, $variants);
		$content .= '</div>';

		$content .= '</div>'; // end mws_variants_container

		$content .= '</div>'; // end mws_variants_manager

		return $content;
	}

	public static function variantItemHead($args, $item, $i): string
	{
		$content = '';
		if (empty($item) || !isset($item['variant_id']) || empty($item['variant_id'])) {
			$content .= '<div class="mws_variant_item_head_name">';
			$content .= '</div>';

				$content .= '<div class="mws_variant_item_head_count mws_variant_stock_info"><span>0</span> ' . __('ks', 'mwshop') . '</div>';

			$content .= '<span class="mws_price_vatincluded"><span class="num">100</span> ' . MWS()->getDefaultCurrency() . '</span>';
		} else {
			$variant = MwsProductVariant::getOneById($item['variant_id']);
			if ($variant) {
				$content .= '<div class="mws_variant_item_head_name">';
				foreach ($variant->getVariantValues() as $variant_value) {
					$content .= '<span>' . $variant_value->getName() . '</span>';
				}
				$content .= '</div>';

				$stockCnt = $variant->getStockCount();
				$content .= '<div class="mws_variant_item_head_count mws_variant_stock_info"><span>' . $stockCnt . '</span> ' . __('ks', 'mwshop') . '</div>';

				$content .= $variant->getPrice()->htmlPriceVatIncluded(1, false);
			}
		}

		return $content;
	}

	public static function variantItemContent($args, $item, $i): string
	{
		$name = $args['tagname'] . '[variants][' . $i . ']';
		$id = $args['tagid'] . '_variants_' . $i;

		// get real stock count
		$variantId = isset($item['variant_id']) && (int) $item['variant_id'] !== 0 ? (int) $item['variant_id'] : false;

		$content = '<div class="mws_variant_definition_container">';
		if (!isset($_GET['copy'])) {
			if ($variantId) {
				$variant = MwsProductVariant::getOneById($variantId);
				if ($variant !== null) {
					$item['stock_count'] = $variant->getStockCount();
				}
			}
			$content .= '<input name="' . $name . '[variant_id]" type="hidden" ' . ($variantId ? ' value="' . $variantId . '"' : '') . ' >';
		}


		$content .= '<div class="mws_variant_definition">';

		// image
		$content .= '<div class="mws_variant_col mws_variant_col_image">';
		$content .= mwAdminComponents::inputSublabel(['label' => __('Obrázek', 'mwshop')]);
		// back compatibility 2.0 -> 3.0
		if (isset($item['image_id']) && $item['image_id']) {
			$item['imageid'] = $item['image_id'];
		}
		$image = new Image([
			'imageid' => $item['imageid'] ?? '',
			'image' => $item['image'] ?? '',
			'position' => $item['position'] ?? '',
		]);
		$content .= mwAdminComponents::imageUploader([
			'name' => $name,
		], $image);
		$content .= '</div>';

		// properties
		$content .= '<div class="mws_variant_col mws_variant_col_property_values">';
		foreach ($args['parametres'] as $param) {
			$parameter = MwsProperty::getOneById($param);
			if ($parameter) {
				$content .= '<div class="mws_variant_parameter_input_container">';
				$content .= mwAdminComponents::inputSublabel(['label' => $parameter->getName()]);
				$content .= $parameter->htmlEditor($name . '[property][' . $parameter->getId() . ']', '', $item['property'][$parameter->getId()] ?? '');
				$content .= '</div>';
			}
		}
		$content .= '</div>';


		$content .= '<div class="mws_variant_col mws_variant_col_setting">';
		// price
		$content .= '<div class="mws_variant_subcol mws_variant_subcol_price">';
		$content .= mwAdminComponents::inputSublabel(['label' => __('Cena', 'mwshop')]);
		$content .= mwAdminComponents::inputNumber([
			'name' => $name . '[price]',
			'step' => MwsCurrencyEnum::getHtmlInputStepAttribute(),
			'placeholder' => '0',
			'unit' => MWS()->getDefaultCurrency('html'),
		], (isset($item['price']) ? esc_attr($item['price']) : '100'));
		$content .= '</div>';
		// price sale
		$content .= '<div class="mws_variant_subcol mws_variant_subcol_sale">';
		$content .= mwAdminComponents::inputSublabel(['label' => __('Cena po slevě', 'mwshop')]);
		$content .= mwAdminComponents::inputNumber([
			'name' => $name . '[price_sale]',
			'step' => MwsCurrencyEnum::getHtmlInputStepAttribute(),
			'placeholder' => '0',
			'unit' => MWS()->getDefaultCurrency('html'),
		], (isset($item['price_sale']) ? esc_attr($item['price_sale']) : ''));
		$content .= '</div>';
		// stock count
		$content .= '<div class="mws_variant_subcol mws_variant_subcol_stock mws_variant_stock_info">';
		$content .= mwAdminComponents::inputSublabel(['label' => __('Sklad', 'mwshop')]);
		$content .= mwAdminComponents::inputNumber([
			'name' => $name . '[stock_count]',
			'step' => MwsCurrencyEnum::getHtmlInputStepAttribute(),
			'placeholder' => '0',
		], (isset($item['stock_count']) ? esc_attr($item['stock_count']) : ''));
		$content .= '</div>';
		// weight
		$content .= '<div class="mws_variant_subcol mws_variant_subcol_weight">';
		$content .= mwAdminComponents::inputSublabel(['label' => __('Hmotnost', 'mwshop')]);
		$content .= mwAdminComponents::inputNumber([
			'name' => $name . '[weight_variant]',
			'min' => true,
			'placeholder' => '0',
			'unit' => mwAdminComponents::WEIGHT_UNIT,
		], (isset($item['weight_variant']) ? esc_attr($item['weight_variant']) : ''));

		$content .= '</div>';

		$content .= '</div>'; // end mws_variant_col_setting

		$content .= '</div>'; // end mws_variant_definition


		$codes = '';
		foreach ($args['enable_codes'] as $enabledCode) {
			$caption = MwsProductCode::getCaption($enabledCode);
			$codes .= '
				<div class="mws_variant_col_code" >
	                <div class="sublabel" > ' . esc_html($caption) . ' </div >
	                <input type="text" autocomplete="off" name="' . $name . '[codes][' . $enabledCode . ']" class="mw_input" ' . (isset($item['codes'][$enabledCode]) ? ' value="' . esc_attr($item['codes'][$enabledCode]) . '"' : '') . ' title="' . esc_attr($caption) . '" >
				</div >';
		}
		$content .= $codes ? '<div class="mws_variant_codes_container">' . $codes . '</div>' : '';

		$content .= '</div>';

		if (isset($item['error']) && !empty($item['error'])) {
			$content .= mwAdminComponents::messageBox($item['error'], ['type' => 'error']);
		}

		return $content;
	}

	function changeVariantParams_ajax()
	{
		$params = [];
		foreach ($_POST['params'] ?? [] as $param) {
			$params[$param] = $param;
		}

		$setting = unserialize(base64_decode($_POST['setting']));
		$setting['parametres'] = $params;
		echo base64_encode(serialize($setting));
	}

}

$MwShopFields = new MwShopFields();
