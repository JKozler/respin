<?php declare(strict_types=1);

use Mioweb\Shop\Order\OrderRepository;
use Mioweb\VisualEditor\Lib\Button;

class mwAPIConnectItemClient_mioweb extends mwAPIConnectItemClient
{

	const MWS_FORM_NONCE = 'nonce_mws_form';

	function checkSavedSetting(&$tosave): bool
	{
		return true;
	}

	function isConnected(): bool
	{
		return true;
	}

	public function printForm($element, $css_id, $post_id, $edit_mode, $added)
	{
		global $vePage;

		$vePage->display->add_enqueue_script('shop_front_script');
		$vePage->display->add_enqueue_style('mwsShop');

		if (MWS()->getSelectedGatewayId() !== 'mioweb') {
			$vePage->display->add_element_info(__('Miowebí formulář nelze použít když je prodej napojený na FAPI. Prosím vyberte jiný formulář.', 'cms_ve'));

			return '';
		}

		$formId = (int) $element['style']['content']['id'];

		$form = MwsForm::getOneById($formId);

		if ($form === null) {
			$vePage->display->add_element_info(__('Formulář neexistuje, pravděpodobně byl smazán. Prosím vyberte formulář, který chcete zobrazit.', 'cms_ve'));

			return '';
		}
		$product = $form->getProduct();
		if ($product === null) {
			$vePage->display->add_element_info(__('Product nastavený ve formuláři neexistuje. Pravděpodobně byl smazán. Přiřaďte formuláři jiný produkt.', 'cms_ve') . ' <a href="' . mwSetting()->getObject('mwsform')->getEditUrl($form->getId()) . '" target="_blank">' . __('Upravit formulář', 'cms_ve') . '</a>');

			return '';
		}
		if ($product->hasVariants()) {
			$vePage->display->add_element_info(__('Ve formuláři nelze prodávat produkt s více variantami.', 'cms_ve') . ' <a href="' . mwSetting()->getObject('mwsform')->getEditUrl($form->getId()) . '" target="_blank">' . __('Upravit formulář', 'cms_ve') . '</a>');

			return '';
		}
		if (!$form->getThxPage() && (!function_exists('MWS') || !MWS()->isCreated())) {
			$vePage->display->add_element_info(__('Formulář nemá nastavenou děkovací stránku.', 'cms_ve') . ' <a href="' . mwSetting()->getObject('mwsform')->getEditUrl($form->getId()) . '" target="_blank">' . __('Upravit formulář', 'cms_ve') . '</a>');
		}
		\assert($form instanceof MwsForm);

		$id = ltrim(ltrim($css_id, '#'), 'element_');

		$class = '';

		if (isset($element['style']['mw_background_set']['corner']) && $element['style']['mw_background_set']['corner']) {
			$class .= ' mw_element_item_corners' . $element['style']['mw_background_set']['corner'];
		}
		if (isset($element['style']['mw_background_set']['shadow']) && $element['style']['mw_background_set']['shadow']) {
			$class .= ' mw_element_item_shadow' . $element['style']['mw_background_set']['shadow'];
		}
		if (isset($element['style']['mw_background_set']['border']) && $element['style']['mw_background_set']['border']) {
			$class .= ' mw_element_item_borders';
		}

		// button

		$button = new Button($element['style']['mw_button'], '', $css_id . ' .ve_content_button');
		$vePage->display->element_css = $button->addButtonStyles($vePage->display->element_css, null, $edit_mode);
		$but_class = 've_content_button ' . $button->getButtonClasses();

		$but_text = $element['style']['mw_button_text'] ?? __('Objednat', 'cms_ve');

		$id = str_replace('#', '', $css_id);
		$vePage->display->element_css->addVariableStyles(
			[
				$css_id . ' .mws_order_form input:checked + .mws_radio_select_content' => 'border-color',
				$css_id . ' .mws_order_form_apply_discount_code_but' => 'background-color',
				$css_id . ' .mw_checkbox:checked' => ['background-color', 'border-color'],
				$css_id . ' .mw_radio_button:checked' => 'border-color',
				$css_id . ' .mw_radio_button:checked::after' => 'background-color',
				$css_id . ' .mws_order_purposes a' => 'color',
			],
			'--order-form-active-color-' . $id,
			$element['style']['mw_active_color']
		);

		$content = '<div class="in_element_content in_element_mws_order_form">';
		$content .= MWS()->renderForm('mws_order_form_' . $id, $form, (int) $post_id, $class, $but_class, $but_text);
		$content .= '</div>';

		if ($added) {
			$content .= '<script>' .
			'jQuery(function(){' .
			'mwGetIframeContent().mw_init_order_form("' . $css_id . ' .mws_order_form");' .
			'});' .
			'</script>';
		}

		return $content;
	}

	public function getFormsList()
	{
		$mwForms = MwsForm::getAll([], false);

		$forms = [];
		foreach ($mwForms as $form) {
			$forms[] = [
				'id' => $form->getId(),
				'name' => $form->getName(),
			];
		}

		return $forms;
	}

	public function getProductsList()
	{
		$products = [];

		return $products;
	}

	public function getPurchaseEventData($id, $funnel = null): ?array
	{
		$order = OrderRepository::getOrderByOrderNum($id);

		if (!$order) {
			return null;
		}

		$bump = 0;
		$orderLive = $order->getGateLive();

		foreach ($order->getItems()->getAll() as $item) {
			if ($item->isMiniupsell()) {
				$bump = 1;
			}
		}

		return [
			'id' => $order->getId(),
			'email' => $orderLive->getInvoiceContact()->getEmail(),
			'price' => $order->getNativePrice()->getPriceVatIncluded(),
			'currency' => strtoupper(MWS()->getDefaultCurrency('key')),
			'upsell' => 0,
			'bump' => $bump,
			'vs' => $order->getNumber(),
		];
	}

}
