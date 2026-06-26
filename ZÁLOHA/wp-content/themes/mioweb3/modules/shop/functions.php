<?php

/**
 * MioShop debugging routine. Pass a string or mixed item to ouput it into Wordpress's default error log
 * (typically wp-content/debug.log).
 *
 * @param mixed $x Mixed item to be print out into log.
 * @param string $name Optional name of the $x. If present then it will be prepended in front of the output text as
 *                     "$name=<output>".
 */
function mwdbg($x, $name = '')
{
	if (defined('WP_DEBUG') || WP_DEBUG == true) {
		if (!is_array($x) && !is_object($x)) {
			error_log(($name ? $name . '=' : '') . $x);
		} else {
			error_log(($name ? $name . '=' : '') . print_r($x, true));
		}
	}
	mwshoplog(
		'DEPRACATED mwdbg() call. ' . ($name ? $name . '=' : '')
		. (!is_array($x) && !is_object($x) ? $x : print_r($x, true)),
		MWLL_WARNING,
		'deprecated'
	);
}

/** Log a shop message, optionally prefixed by shop part/module/... */
function mwshoplog($message, $level = MWLL_INFO, $ctg = '')
{
	mwlog(MWLS_SHOP, $message, $level, $ctg);
}

/**
 * Returns <code>true</code> when function is called during autosave or when post is not published. This is useful
 * for hooks to detect premature calls form WP API.
 *
 * @param int|WP_Post $postId
 * @return bool
 */
function mwsIsPostAutosaveOrUnpublished($postId)
{
	if (wp_is_post_autosave($postId)) {
		return true;
	} else {
		$postStatus = get_post_status($postId);
		if (!$postStatus || $postStatus != 'publish') {
			return true;
		}
	}

	return false;
}

/**
 * Format price of the product with currency unit. If price equals zero then result is "for free".
 *
 * @param float $price Value of price to print.
 * @param null|string $unit Unit appended to price. Empty string means "no unit". Default null value means "use global currency unit".
 * @param bool $use0text When true and $price==0 then "free" text will be printed.
 * @param null|string $divCSS If not null then result will be wrapped within SPAN element and value of this parameter
 *                            will be used as value of element's CSS "class" attribute.
 * @param string $afterText
 * @param string $beforeText
 * @param bool $numContainer Should number be inside <span class="num"> container?
 * @return string
 */
function htmlPriceSimple(float $price, ?string $unit = null, bool $use0text = false, ?string $divCSS = null, string $afterText = '', string $beforeText = '', bool $numContainer = true): string
{
	$unit ??= MWS()->getDefaultCurrency();
	$dec = (float) $price != floor($price) ? 2 : 0;
	$print_price = number_format(round((float) $price, 2), $dec, ',', ' ');
	$print_price = str_replace(' ', '&nbsp;', $print_price);

	$res = $price == 0 && $use0text
	? ($numContainer ? '<span class="num">' : '') . __('zdarma', 'mwshop') . ($numContainer ? '</span>' : '')
	: ($numContainer ? '<span class="num">' : '') . $print_price . ($numContainer ? '</span>' : '') . (!empty($unit) ? '&nbsp;' . $unit : '');
	if ($divCSS !== null && !empty($res)) {
		$before = $beforeText ? '<span class="mw_before_price">' . $beforeText . '</span>' : '';
		$res = '<span class="' . $divCSS . '">' . $before . $res . $afterText . '</span>';
	}

	return $res;
}

function htmlPriceSimpleIncluded($price, $unit = null, $use0text = true, $divCSS = null, $beforeText = '')
{
	return htmlPriceSimple(
		$price,
		$unit,
		$use0text,
		'mws_price_vatincluded' . (!empty($divCSS) ? ' ' . $divCSS : ''),
		'',
		$beforeText
	);
}

function htmlPriceSimpleExcluded($price, $unit = null, $use0text = false, $divCSS = null)
{
	if (MWS()->getVATs()->isUsingVatAccounting()) {
		return htmlPriceSimple(
			$price,
			$unit,
			$use0text,
			'mws_price_vatexcluded' . (!empty($divCSS) ? ' ' . $divCSS : ''),
			' ' . __('bez&nbsp;DPH', 'mwshop')
		);
	}

	return '';
}

function htmlPriceSimpleSale($priceSale, $priceNormal, $unit = null, $divCSS = null)
{
	$discount = $priceNormal > 0 ? round($priceSale / $priceNormal * 100, 0) : 0;
	if ($discount == 0) {
		return '';
	}

	$unit ??= MWS()->getDefaultCurrency();
	$res = '<span>' . $discount . '%</span>';
	$res .= ' <span><span class="num">' . $priceNormal . '</span>' . (!empty($unit) ? '&nbsp;' . $unit : '') . '</span>';

	$res = '<span class="mws_price_sale' . (!empty($divCSS) ? ' ' . $divCSS : '') . '">' . $res . '</span>';

	return $res;
}

/**
 * Strip decimal places up to count of $decimals.
 *
 * @param $float
 * @param int $decimals
 * @return float
 */
function floordec($float, $decimals = 2)
{
	return floor($float * pow(10, $decimals)) / pow(10, $decimals);
}

/**
 * Convert datetimestring in WP local timezone into UTC timestamp.
 *
 * @param string $dateTimeLocalString Datetime string in WP timezone, e.g. as set in admin interace.
 * @return string
 * @throws Exception conversion error, mostly incorrect format of datetimestring
 */
function mwConvDateTimeLocal2TimestampUTC($dateTimeLocalString)
{
	try {
		// get datetime object from site timezone
		$datetime = new DateTime($dateTimeLocalString, new DateTimeZone(wp_get_timezone_string()));
		// get the unix timestamp (adjusted for the site's timezone already)
		$timestamp = $datetime->format('U');

		return $timestamp;
	} catch (Exception $e) {
		// you'll get an exception most commonly when the date/time string passed isn't a valid date/time
		throw $e;
	}
}

/**
 * Convert datetimestring in UTC timezone into UTC timestamp.
 *
 * @param string $dateTimeUTCString Datetime string in UTC, e.g. $post->post_date_gmt
 * @return string
 * @throws Exception conversion error, mostly incorrect format of datetimestring
 */
function mwConvDateTimeUTC2TimestampUTC($dateTimeUTCString)
{
	try {
		// get datetime object from site timezone
		$datetime = new DateTime($dateTimeUTCString, new DateTimeZone('UTC'));
		// get the unix timestamp (adjusted for the site's timezone already)
		$timestamp = $datetime->format('U');

		return $timestamp;
	} catch (Exception $e) {
		// you'll get an exception most commonly when the date/time string passed isn't a valid date/time
		throw $e;
	}
}

/**
 * Convert timestamp into string representation of date according to WP settings. Before it is printed it is converted into local
 * timezone.
 *
 * @param int $timestamp Unix timestamp, in UTC defaultly.
 * @param bool $convertFromUTC Determines, whether $timestamp is in UTC (default) or not, meaning already converted to local
 *                      timezone.
 * @return string
 */
function mwFormatAsDate($timestamp, $convertFromUTC = true)
{
	if ($convertFromUTC) {
		$timestamp = mwConvTimestampUTC2TimestampLocal($timestamp);
	}

	return date_i18n(get_option('date_format'), $timestamp);
}

/**
 * Convert timestamp into string representation of time according to WP settings. Before it is printed it is converted into local
 * timezone.
 *
 * @param int $timestamp Unix timestamp, in UTC defaultly.
 * @param bool $convertFromUTC Determines, whether $timestamp is in UTC (default) or not, meaning already converted to local
 *                      timezone.
 * @return string
 */
function mwFormatAsTime($timestamp, $convertFromUTC = true)
{
	if ($convertFromUTC) {
		$timestamp = mwConvTimestampUTC2TimestampLocal($timestamp);
	}

	return date_i18n(get_option('time_format'), $timestamp);
}

/**
 * Convert timestamp into string representation of date and time according to WP settings. Before it is printed it is converted into local
 * timezone.
 *
 * @param int $timestamp Unix timestamp, in UTC defaultly.
 * @param bool $convertFromUTC Determines, whether $timestamp is in UTC (default) or not, meaning already converted to local
 *                      timezone.
 * @return string
 */
function mwFormatAsDateTime($timestamp, $convertFromUTC = true)
{
	if ($convertFromUTC) {
		$timestamp = mwConvTimestampUTC2TimestampLocal($timestamp);
	}

	return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
}

/**
 * Extract datetime value as timestamp from MioWeb "datetime field". If settings are empty or invalid, value of
 * $defaultTimestamp is use for date part, 0 is used for hours and minutes.
 *
 * @param array $array Array with field values from the field type "datetime" of Mioweb. Value is expected to be
 *                              in local timezone.
 * @return int Unix epoch timestamp UTC.
 * @throws Exception
 */
function mwExtractDateTimeFromField(array $array): int
{
	$date = isset($array['date']) ? new DateTimeImmutable($array['date']) : (new DateTimeImmutable())->setTime(0, 0);
	$timestampLocal = $date->getTimestamp();
	$timestampLocal += isset($array['hour']) && $array['hour'] ? ((int) $array['hour']) * 3600 : 0;
	$timestampLocal += isset($array['minute']) && $array['minute'] ? ((int) $array['minute']) * 60 : 0;

	return $timestampLocal;
}

/**
 * Returns true whe viewing single product.
 *
 * @return bool
 */
function mwsIsProduct()
{
	return is_singular([MWS_PRODUCT_SLUG]);
}

/**
 * Returns true when viewing products or shop page.
 *
 * @return bool
 */
function mwsIsShop()
{
	return is_post_type_archive(MWS_PRODUCT_SLUG); //TODO doplnit podmínku že aktuální stránka je home eshopu a nebo objednávky
}

function mwsRenderParts($slug, $name = '', $toString = false, array $args = [])
{
	$str = MWS()->renderTplParts($slug, $name, $toString, $args);

	if ($toString) {
		return $str;
	}
}

function field_type_product_select($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];
	$val = $meta ?? ($field['content'] ?? 0);

	echo mwShopFields::productSelect($field, $val, $name, $id);
}

function field_type_install_shop($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	echo MwShopFields::installEshop();
}

// select page
function field_type_select_product_type($field, $meta, $group_name, $group_id)
{
	$value = $meta ?? MwsProductType::Physical;

	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$exclude = [];
	if (!class_exists('mwMemberModule')) {
		$exclude[] = MwsProductType::Membership;
	}
	echo MwsProductType::getSelect([
		'name' => $name,
		'tag_id' => $id,
		'exclude' => $exclude,
	], $value);
}

function field_type_eshop_automations($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$value = $meta ?? ($field['content'] ?? null);

	echo MwShopFields::eshopAutomations($value, $name, $id, $field);
}

function field_type_upsells($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$value = $meta ?? ($field['content'] ?? null);

	echo MwShopFields::upsells($value, $name, $id, intval($post_id));
}

function field_type_paygate_select($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$value = $meta ?? ($field['content'] ?? '');

	echo MwShopFields::paygateSelect($value, $name, $id, $field);
}

function field_type_number_series($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$value = $meta ?? ($field['content'] ?? ['prefix' => 'RRRR', 'characters' => '6', 'start' => '1']);

	echo MwShopFields::numberSeries($value, $name, $id, $field);
}

/** Select box to select currency from the list of supported currencies. */
function field_type_currency_select($field, $meta, $group_name, $group_id)
{
	$id = $group_id . '_' . $field['id'];
	$name = getTagName($group_name, $field['id']);
	$content = $meta ?? ($field['content'] ?? '');

	echo MwShopFields::currencySelect($content, $name, $id, $field);
}

function field_type_currency($field, $meta, $group_name, $group_id, $post_id)
{
	$id = $group_id . '_' . $field['id'];
	$name = getTagName($group_name, $field['id']);
	$content = $meta ?? ($field['content'] ?? '');

	echo MwShopFields::currency($content, $name, $id, $post_id);
}

function field_type_currencies($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$val = $meta ?? ($field['content'] ?? []);

	echo MwShopFields::currencies($val, $name);
}

/** select coutnries */
function field_type_country_select($field, $meta, $group_name, $group_id)
{
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];
	$content = $meta ?? '';

	echo MwShopFields::countrySelect($content, $name, $id, $field);
}

function field_type_shipping_country($field, $meta, $group_name, $group_id, $post_id)
{
	$id = $group_id . '_' . $field['id'];
	$name = getTagName($group_name, $field['id']);
	$content = $meta ?? ($field['content'] ?? '');

	echo MwShopFields::shippingCountry($content, $name, $id, $post_id);
}


function field_type_countries($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$val = $meta ?? ($field['content'] ?? []);

	echo MwShopFields::countries($val, $name);
}

function field_type_automation_event($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$value = $meta ?? ($field['content'] ?? null);

	echo MwShopFields::automationEvent($value, $name, $id, $field);
}

// company info (ID, VAT ID, name)
function field_type_company_info($field, $meta, $group_id, $group_name)
{
	$content = $meta ?? ($field['content'] ?? []);
	if (!is_array($content)) {
		$content = [];
	}

	$baseId = $group_id . '_' . $field['id'];
	$baseName = $group_name . '[' . $field['id'] . ']';

	$i = 'company_name';
	$field['placeholder'] = __('Název společnosti', 'mwshop');
	echo '<div class="">'
	. '<label class="sublabel" for="' . $baseId . "_$i" . '">' . $field['placeholder'] . '</label>';
	echo '<div>'
	. cms_generate_field_text(
		$baseName . "[$i]",
		$baseId . "_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'',
		$field
	)
	. '</div>';
	echo '</div>';

	echo '<div class="cms_clear"></div>';

	$i = 'company_id';
	$field['placeholder'] = __('IČ', 'mwshop');
	echo '<div class="mw_flex_field_col">'
	. '<label class="sublabel" for="' . $baseId . "_$i" . '">' . $field['placeholder'] . '</label>';
	echo '<div>'
	. cms_generate_field_text(
		$baseName . "[$i]",
		$baseId . "_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'mw_company_id',
		$field
	)
	. '</div>';
	echo '</div>';

	$i = 'company_vat_id';
	$field['placeholder'] = __('DIČ', 'mwshop');
	echo '<div class="mw_flex_field_col">'
	. '<label class="sublabel" for="' . $baseId . "_$i" . '">' . $field['placeholder'] . '</label>';
	echo '<div>'
	. cms_generate_field_text(
		$baseName . "[$i]",
		$baseId . "_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'mw_company_vat_id',
		$field
	)
	. '</div>';
	echo '</div>';

	echo '<div class="cms_clear"></div>';
}

// order address (firstname,...,city)
function field_type_order_address($field, $meta, $group_id, $group_name)
{
	$content = $meta ?? ($field['content'] ?? []);
	if (!is_array($content)) {
		$content = [];
	}

	$baseId = $group_id . '_' . $field['id'];
	$baseName = $group_name . '[' . $field['id'] . ']';

	$i = 'firstname';
	$field['placeholder'] = __('Jméno', 'mwshop');
	echo '<div class="mw_flex_field_col">'
	. '<label class="sublabel" for="' . $baseId . "_$i" . '">' . $field['placeholder'] . '</label>';
	echo '<div>'
	. cms_generate_field_text(
		$baseName . "[$i]",
		$baseId . "_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'',
		$field
	)
	. '</div>';
	echo '</div>';

	$i = 'surname';
	$field['placeholder'] = __('Příjmení', 'mwshop');
	echo '<div class="mw_flex_field_col">'
	. '<label class="sublabel" for="' . $baseId . "_$i" . '">' . $field['placeholder'] . '</label>';
	echo '<div>'
	. cms_generate_field_text(
		$baseName . "[$i]",
		$baseId . "_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'',
		$field
	)
	. '</div>';
	echo '</div>';

	$i = 'phone';
	$field['placeholder'] = __('Telefon', 'mwshop');
	echo '<div class="mw_flex_field_col">'
	. '<label class="sublabel" for="' . $baseId . "_$i" . '">' . $field['placeholder'] . '</label>';
	echo '<div>'
	. cms_generate_field_text(
		$baseName . "[$i]",
		$baseId . "_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'',
		$field
	)
	. '</div>';
	echo '</div>';

	$i = 'street';
	$field['placeholder'] = __('Ulice', 'mwshop');
	echo '<div class="mw_flex_field_col">'
	. '<label class="sublabel" for="' . $baseId . "_$i" . '">' . $field['placeholder'] . '</label>';
	echo '<div>'
	. cms_generate_field_text(
		$baseName . "[$i]",
		$baseId . "_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'',
		$field
	)
	. '</div>';
	echo '</div>';

	$i = 'city';
	$field['placeholder'] = __('Město', 'mwshop');
	echo '<div class="mw_flex_field_col">'
	. '<label class="sublabel" for="' . $baseId . "_$i" . '">' . $field['placeholder'] . '</label>';
	echo '<div>'
	. cms_generate_field_text(
		$baseName . "[$i]",
		$baseId . "_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'',
		$field
	)
	. '</div>';
	echo '</div>';

	$i = 'zip';
	$field['placeholder'] = __('PSČ', 'mwshop');
	echo '<div class="mw_flex_field_col">'
	. '<label class="sublabel" for="' . $baseId . "_$i" . '">' . $field['placeholder'] . '</label>';
	echo '<div>'
	. cms_generate_field_text(
		$baseName . "[$i]",
		$baseId . "_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'number number_int',
		$field
	)
	. '</div>';
	echo '</div>';

	echo '<div class="cms_clear"></div>';
}

// global VAT values
function field_type_vatvalues($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? []);
	if (!is_array($content)) {
		$content = [];
	}

	$baseId = $group_id . '_' . $field['id'];
	$baseName = $group_id . '[' . $field['id'] . ']';

	//Make sure that in case that VAT is used then the first VAT level is defined. This hack is just for better UI clarity.
	if (!isset($content[0]) && !empty($content[0])) {
		$content[0] = MWS()->getVATs()->getValueDefault(true, null);
	}

	$field['placeholder'] = __('nepoužívat', 'mwshop');
	for ($i = 0; $i < 5; $i++) {
		echo '<div class="vat-values set_form_subrow">';
		echo '<label class="label" for="' . $baseId . "_$i" . '">' . sprintf(__('Sazba %d'), $i + 1) . '</label>';
		echo '<div>';
		echo mwAdminComponents::input([
			'name' => $baseName . "[$i]",
			'id' => $baseId . "_$i",
		], isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : '');
		echo '%</div></div>';
	}
	echo '<div class="cms_clear"></div>';
}

// product VAT selection from global values
function field_type_vat_select($field, $meta, $group_id, $group_name)
{
	$content = $meta ?? ($field['content'] ?? 0);

	$vats = MWS()->getVATs();
	if (!$vats->hasValues()) {
		field_type_info(
			['content' =>
			__('Účtování s DPH není aktivní. Chcete-li účtovat s DPH, zadejte sazby DPH v globálním nastavení obchodu.', 'mwshop')],
			null,
			null
		);

		return;
	}

	$items = $vats->toArray();
	$options = [];
	foreach ($items as $vatId => $vatValue) {
		if ($vatValue !== null) {
			$options[] = [
				'value' => $vatId,
				'name' => (empty($vatValue) ? '0' : (int) $vatValue) . '%'
					//TODO Comment out following line to hide VAT level within select box.
					. ' (' . sprintf(__('sazba %d'), $vatId + 1) . ')',
			];
		}
	}
	$field['options'] = $options;

	cms_generate_field_select(
		$group_name . '[' . $field['id'] . ']',
		$group_id . '_' . $field['id'],
		$content,
		$field
	);
}

// category select
function field_type_shop_category_select($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? 0);

	$items = get_categories(['taxonomy' => MWS_PRODUCT_CAT_SLUG, 'hide_empty' => 0]);
	$options = [];
	$options[] = [
		'value' => '',
		'name' => __('- Vyberte kategorii -', 'mwshop'),
	];
	foreach ($items as $val) {
		$options[] = [
			'value' => $val->term_id,
			'name' => $val->name,
		];
	}
	$field['options'] = $options;

	cms_generate_field_select(
		$group_name . '[' . $field['id'] . ']',
		$group_id . '_' . $field['id'],
		$content,
		$field
	);
}

function field_type_product_properties($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? [];

	$id = $group_id . '_' . $field['id'];
	$name = $group_name . '[' . $field['id'] . ']';

	$propDefs = MwsProperty::getAll();
	if (count($propDefs)) {
		echo '<table class="mw_table">';
		/** @var MwsProperty $property */
		foreach ($propDefs as $property) {
			$propId = $property->getId();
			$htmlId = $id . '_' . $propId;
			echo '<tr>';
			echo '	<td class="left_label"><label for="' . $htmlId . '">' . $property->getName() . '</label></td>';
			echo '	<td>' . $property->htmlEditor(
				$name . '[' . $propId . ']',
				$htmlId,
				$content[$propId] ?? null,
				'mw_input mw_input_s',
				'',
				'',
				false,
				true
			)
			. '</td>';
			echo '	<td>' . $property->getUnit() . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	} else {
		echo mwAdminComponents::messageBox(__('Nejsou vytvořeny žádné parametry produktů.', 'mwshop'), [
			'type' => 'info_gray',
		]);
	}
}

/** List of all properties. Can be formed as list of checkboxes. */
function field_type_property_list($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? [];

	$id = $group_id . '_' . $field['id'];
	$name = $group_name . '[' . $field['id'] . ']';
	$checkboxes = isset($field['checkbox']);

	$propDefs = MwsProperty::getAll();
	if (count($propDefs)) {
		echo '<div class="mws_product_properties">';
		/** @var MwsProperty $property */
		foreach ($propDefs as $property) {
			$propId = $property->getId();
			echo '<div class="mws_property mws_property-' . $propId . '">';
			if ($checkboxes) {
				cms_generate_field_checkbox(
					$name . '[' . $propId . ']',
					$id . '_' . $propId,
					$content[$propId] ?? '',
					$property->getName()
				);
			} else {
				echo '	<div>' . $property->getName() . '</div>';
			}
			echo '</div>';
		}
		echo '</div>';
		echo '<div class="cms_clear"></div>';
	} else {
		echo mwAdminComponents::messageBox(__('Nejsou vytvořeny žádné parametry produktů.', 'mwshop'), [
			'type' => 'info_gray',
		]);
	}
}

function cms_generate_product_code($name, $id, $meta, $class = '', $field = [])
{
	return cms_generate_field_text(
		$name,
		$id,
		$meta,
		'mws_product_code' . (isset($field['class']) ? ' ' . $field['class'] : ''),
		$field
	);
}

/** Product extended code. */
function field_type_product_code($field, $meta, $group_name, $group_id)
{
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	$content = $meta ?? '';
	echo cms_generate_product_code($name, $id, $content, '', $field);
}

/** Product extended codes (accounting, storage) for single product. */
function field_type_product_codes($field, $meta, $group_name, $group_id)
{
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	$content = $meta ?? [];

	$gw = MWS()->gateways()->getDefault();
	try {
		$enabledCodes = $gw ? $gw->getEnabledCodes(MWS()->canEdit()) : [];
	} catch (FapiGatewayCommunicationException $e) {
		$enabledCodes = MwsProductCode::getAll();
	}

	if (empty($enabledCodes)) {
		$res = '<div class="label">' . __('Kódy účetní, skladové, evidenční', 'mwshop') . '</div>'
		. '<span class="mw_description">' . __('Pro využití kódů je potřeba v nastavení eshopu v sekci <i>Platby a fakturace</i> povolit, které kódy chcete používat.', 'mwshop') . '</span>';
	} else {
		$res = '';
		foreach ($enabledCodes as $enabledCode) {
			$res .= '<div class="label">' . MwsProductCode::getCaption($enabledCode) . '</div>';
			$res .= mwAdminComponents::input([
				'name' => $name . "[$enabledCode]",
//				'name' => $id . "_$enabledCode",
			], $content[$enabledCode] ?? '');
		}
	}

	echo $res;
}

/** Variants admin **/
function field_type_variantList($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$value = $meta ?? ($field['content'] ?? null);

	echo MwShopFields::variantList($value, $name, $id, $field);
}

function mws_generate_country_select($name, $id, $css, $value, $print = true)
{
	$res = '';
	$countries = MWS()->getShippingCountries();
	if (!in_array($value, $countries)) {
		$value = MWS()->getDefaultShippingCountry();
		//TODO http://stackoverflow.com/questions/12553160/getting-visitors-country-from-their-ip
	}
	$res .= '<select autocomplete="off"'
	. ($name ? ' name="' . $name . '"' : '')
	. ($id ? ' id="' . $id . '"' : '')
	. ($css ? ' class="' . $css . '"' : '')
	. '>';
	foreach ($countries as $country) {
		$currency = MwsCurrencyEnum::getByCountry($country);
		$res .= '<option value="' . $country . '" data-currency="' . strtoupper(MwsCurrencyEnum::getSupportedByCountry($country)) . '" ' . ($country == $value ? ' selected="selected"' : '') . '>' . MwsCountry::getCaption($country) . '</option>';
	}
	$res .= '</select>';
	if ($print) {
		echo $res;
	} else {
		return $res;
	}
}

function field_type_eshop_feeds($field, $content, $group_name, $group_id)
{
	$heureka_url = get_feed_link('heureka');
	$zbozi_url = get_feed_link('zbozi');
	$google_url = get_feed_link('google');
	?>
	<table class="mw_table">
		<tr>
			<td><?php echo __('Heureka.cz', 'mws_shop'); ?></td>
			<td><a href="<?php echo $heureka_url; ?>" target="_blank"><?php echo $heureka_url; ?></a></td>
		</tr>
		<tr>
			<td><?php echo __('Zbozi.cz', 'mws_shop'); ?></td>
			<td><a href="<?php echo $zbozi_url; ?>" target="_blank"><?php echo $zbozi_url; ?></a></td>
		</tr>
		<tr>
			<td><?php echo __('Google', 'mws_shop'); ?></td>
			<td><a href="<?php echo $google_url; ?>" target="_blank"><?php echo $google_url; ?></a></td>
		</tr>
	</table>
	<?php
}

function field_type_eshop_feed($field, $content, $group_name, $group_id)
{
	if (isset($field['feed'])) {
		$url = get_feed_link($field['feed']);
		echo '<a href="' . $url . '" target="_blank">' . $url . '</a>';
	}
}

function field_type_shippings_type_select($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? 13);

	$options = [
		[
			'value' => MwsShippingType::Custom,
			'name' => __('Vlastní doprava', 'mwshop'),
		],
		[
			'value' => MwsShippingType::Personal,
			'name' => __('Osobní vyzvednutí', 'mwshop'),
		],
	];

	if (MWS()->packeta->isConnected()) {
		$options[] = [
			'value' => MwsShippingType::Packeta,
			'name' => __('Zásilkovna (výdejní místa)', 'mwshop'),
		];
		$options[] = [
			'value' => MwsShippingType::PacketaCarriers,
			'name' => __('Zásilkovna (externí dopravci)', 'mwshop'),
		];
	}

	$field['options'] = $options;

	cms_generate_field_select(
		$group_name . '[' . $field['id'] . ']',
		$group_id . '_' . $field['id'],
		$content,
		$field
	);
}

function field_type_payment_method_type_select($field, $meta, $group_name, $group_id)
{
	// @TODO what is this?
	$content = $meta ?? ($field['content'] ?? 13);

	$gateway = MWS()->gateways()->getDefault();

	$options = [];
	if ($gateway->processPayments()) {
		foreach ($gateway->getEnabledPayTypes() as $paymentMethodType) {
			$options[] = [
				'value' => $paymentMethodType,
				'name' => MwsPayType::getCaption($paymentMethodType),
			];
		}
	} else {
		// add all not gateway pay types
		foreach (MwsPayType::getAll() as $paymentMethodType) {
			if (!MwsPayType::isGateway($paymentMethodType)) {
				$options[] = [
					'value' => $paymentMethodType,
					'name' => MwsPayType::getCaption($paymentMethodType),
				];
			}
		}
		foreach (MWS()->getPaymentGateways() as $id => $paymentGateway) {
			$enabledPaymentMethodTypes = $paymentGateway->getEnabledPaymentMethodTypes();
			foreach ($paymentGateway->getSupportedPaymentMethodTypes() as $paymentMethodType) {
				$options[] = [
					'value' => $paymentMethodType . ':' . $id,
					'name' => MwsPayType::getCaption($paymentMethodType) . ' - ' . $paymentGateway->getName(),
					'disabled' => !in_array($paymentMethodType, $enabledPaymentMethodTypes),
				];
			}
		}
	}

	usort($options, function ($a, $b) {
		return $a['name'] <=> $b['name'];
	});
	echo mwAdminComponents::select([
		'options' => $options,
		'name' => $group_name . '[' . $field['id'] . ']',
		'id' => $group_id . '_' . $field['id'],
	], $content);
}

/** select coutnries */
function field_type_zbozi_category_list($field, $meta, $group_name, $group_id)
{
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	$content = $meta ?? '';

	$options = MwZboziCz::getCategoryList();

	$res = '<select class="mw_whisperer" name="' . $name . '" id="' . $id . '">';
	$res .= '<option value="" ' . ($content == '' ? ' selected="selected"' : '') . '> - </option>';
	foreach ($options as $id => $cat) {
		$res .= '<option value="' . $id . '" ' . ($content == $id ? ' selected="selected"' : '') . '>' . $cat . '</option>';
	}
	$res .= '</select>';

	echo $res;
}

function field_type_heureka_category_list($field, $meta, $group_name, $group_id)
{
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	$content = $meta ?? '';

	$options = MwHeureka::getCategoryList();

	$res = '<select class="mw_whisperer" name="' . $name . '" id="' . $id . '">';
	$res .= '<option value="" ' . ($content == '' ? ' selected="selected"' : '') . '> - </option>';
	foreach ($options as $id => $cat) {
		$res .= '<option value="' . $id . '" ' . ($content == $id ? ' selected="selected"' : '') . '>' . $cat . '</option>';
	}
	$res .= '</select>';

	echo $res;
}

function field_type_terms_editor($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];
	$content = $meta ?? ($field['content'] ?? '');
	wp_editor(stripslashes($content), $id, [
			'textarea_name' => $name,
			'media_buttons' => false,
			'quicktags' => false,
			'tinymce' => [
					'plugins' => 'lists, paste, wordpress, link, wpdialogs, charmap',
					'toolbar1' => 'formatselect | bold italic strikethrough underline | bullist numlist | superscript subscript | outdent indent charmap',
					'toolbar2' => '',
					'block_formats' => 'Odstavec=p; Nadpis=h3',
					'init_instance_callback' => "function (editor) {
						editor.on('change', function () {
							tinymce.triggerSave();
						});
					}",
			],
	]);
}
