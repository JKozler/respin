<?php

function field_type_funnel_sell_setting($field, $meta, $group_name, $group_id)
{
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	$value = isset($meta['api']) && $meta['api'] ? $meta : ['api' => 'fapi'];
	$content = '';

	$content .= '<div class="mw_funnel_sell_setting_container">';

	$content .= '<div>';
	$content .= MwSellingApi()->apiSelector($name, $id, $value);
	$content .= '</div>';

	$content .= '<div class="mw_funnel_sell_setting">';
	$content .= mw_generate_funnel_sell_setting($value, $name, $id);
	$content .= '</div>';

	$content .= '</div>';

	echo $content;
}

function mw_generate_funnel_sell_setting($value, $name, $id)
{
	$mwAPIConnectItem = mwApiConnect()->getApi($value['api']);
	$content = '';
	if ($mwAPIConnectItem->isConnected()) {
		if ($value['api'] == 'fapi') {
			$products = $mwAPIConnectItem->client()->getProductsList();

			$content .= '<div class="set_form_subrow">';
			$content .= '<div class="label">' . __('Upsell produkt', 'mw_funnels') . '</div>';
			$content .= MwSellingApi()->productSelector($name . '[upsell]', $id . '_upsell', $value['upsell'] ?? '', $value['api'], $products);
			$content .= '<span class="mw_description">' . __('Pokud nastavíte upsell produkt, bude se ve statistikách počítat kolikrát byl upsell produkt zakoupen.', 'mw_funnels') . '</span>';
			$content .= '</div>';
			$content .= '<div class="set_form_subrow">';
			$content .= '<div class="label">' . __('Miniupsell(bump) produkt', 'mw_funnels') . '</div>';
			$content .= MwSellingApi()->productSelector($name . '[bump]', $id . '_miniupsell', $value['bump'] ?? '', $value['api'], $products);
			$content .= '<span class="mw_description">' . __('Pokud nastavíte miniupsell produkt, bude se ve statistikách počítat kolikrát byl miniupsell produkt zakoupen.', 'mw_funnels') . '</span>';
			$content .= '</div>';
		}
	} else {
		$content .= $mwAPIConnectItem->printConnectionButton('data-tagid="' . $id . '" data-name="' . $name . '"');
	}

	return $content;
}

function mw_ajax_generate_funnel_sell_setting()
{
	$value = [
		'api' => $_POST['api_id'],
		'upsell' => '',
		'bump' => '',
	];
	echo mw_generate_funnel_sell_setting($value, $_POST['tag_name'], $_POST['tag_id']);
	die();
}

add_action('wp_ajax_mw_generate_funnel_sell_setting', 'mw_ajax_generate_funnel_sell_setting');

function mw_ajax_connect_funnel_sell_setting()
{
	$api = mwApiConnect()->getApi($_POST['api_id']);
	if ($api) {
		$status = $api->saveSetting($_POST['setting']);
		$content = '';

		if ($status) {
			$value = [
				'api' => $_POST['api_id'],
				'upsell' => '',
				'bump' => '',
			];
			$content = mw_generate_funnel_sell_setting($value, $_POST['tag_name'], $_POST['tag_id']);
		}

		wp_send_json([
			'status' => $status,
			'content' => $content,
			'success' => mwMessages()->success,
			'errors' => mwMessages()->errors,
			'html' => mwMessages()->writeHtml(),
		]);
	}
	die();
}

add_action('wp_ajax_mw_connect_funnel_sell_setting', 'mw_ajax_connect_funnel_sell_setting');
