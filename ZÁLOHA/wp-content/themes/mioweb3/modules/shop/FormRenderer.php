<?php declare(strict_types=1);

namespace Mioweb\Shop;

use MioShop;
use Mioweb\HttpClient\Utils\Json;
use MwHeureka;
use MwsCart;
use MwsForm;
use MwsPaymentMethod;
use MwsProduct;
use MwsShipping;
use MwsCurrencyEnum;
use mwFrontComponents;
use MwsPayType;
use MwsShippingType;
use MwsShippingElectronic;
use ReverseChargeApplicationException;
use function htmlspecialchars;

final class FormRenderer
{

	private $_formId = '';

	private $_product;

	private $_miniupsellProduct = null;

	private $_isShippingRequired = true;

	private $_isShippingVisible = true;

	private $_allowSimplified = false;

	private $_allowDiscountCodes = false;

	private $_showProductCount = false;

	private $_allowMiniupsell = false;

	private $_basicSettings = [];

	private $_showPhone = true;

	private $_showCountry = true;

	private $_showName = true;

	private $_showNote = false;

	private $_paymentMethods = [];

	private $_shippingMethods = null;

	private $_thanksPage = null;

	private MwsCart $_cart;

	public function init(
		MwsProduct $product,
		bool $allowSimplified = false,
		bool $allowDiscountCodes = false,
		?string $thanksPage = null
		//?array $paymentMethods = null,
		//?array $shippingMethods = null,
	)
	{
		$this->_product = $product;
		$this->_isShippingRequired = $this->_product->isShippingRequired();
		$this->_thanksPage = $thanksPage;
		$this->initCart($this->_product, $this->_product->getPrice(), []);
		$this->_allowDiscountCodes = $allowDiscountCodes;
		$this->initPaymentMethods();

		$this->_allowSimplified = $allowSimplified /*&& !$this->_isShippingRequired*/ && $this->_product->getPrice()->getPriceVatIncluded() <= 10000;
	}

	public function initByForm(MwsForm $mwsForm)
	{
		$this->_basicSettings = $mwsForm->getBasicSettings();
		$this->_formId = $mwsForm->getId();
		$this->_product = $mwsForm->getProduct();

		$this->_allowMiniupsell = $mwsForm->isMiniupsellAllowed();
		$this->_miniupsellProduct = $mwsForm->getMiniupsell();

		$this->_allowDiscountCodes = (bool) ($this->_basicSettings['allow_discount_codes'] ?? false);
		$this->_isShippingRequired = $this->_product->isShippingRequired() || ($this->_miniupsellProduct && $this->_miniupsellProduct->isShippingRequired());
		$this->_isShippingVisible = $this->_product->isShippingRequired();

		//$isElectronicContained = MwsProductType::isElectronic($product->getType());

		/** Can be simplified invoice used? */
		$this->_allowSimplified = $mwsForm->isSimplifiedAllowed() /*&& !$this->_isShippingRequired*/ && $this->_product->getPrice()->getPriceVatIncluded() <= 10000;

		$visibilitySettings = $mwsForm->getVisibilitySettings();
		$this->_showName = (bool) ($visibilitySettings['show_field_name'] ?? false);
		$this->_showPhone = (bool) ($visibilitySettings['show_field_phone'] ?? false);
		$this->_showCountry = (bool) ($visibilitySettings['show_field_country'] ?? false);
		$this->_showNote = (bool) ($visibilitySettings['show_field_note'] ?? false);
		$this->_showProductCount = (bool) ($visibilitySettings['show_product_count'] ?? false);

		$excludedPaymentTypes = $this->_product->isShippingRequired() ? [] : [MwsPayType::Cod];
		$this->_paymentMethods = $mwsForm->getPaymentMethods($excludedPaymentTypes);

		$excludedShippingTypes = $this->_product->isShippingRequired() ? [] : [MwsShippingType::Personal];
		$this->_shippingMethods = $mwsForm->getShippingMethods($excludedShippingTypes);

		$this->initCart($this->_product, $this->_product->getPrice());
	}

	public function initCart($product, $price, $formData = [])
	{
		MWS()->current()->setProduct($product);
		$this->_cart = MWS()->getFormProcessor()->prepareCart($formData, $product, $price, true);
		$this->_cart->recount(true, false);
	}

	public function initPaymentMethods(): void
	{
		$this->_paymentMethods = $this->_cart->getAllowedPaymentMethods();
	}

	public function getTotalPrice()
	{
		return $this->_cart->getStoredTotalPrice() ?? $this->_product->getPrice();
	}

	public function getCart(): MwsCart
	{
		return $this->_cart;
	}

	/**
	 * @param string $htmlId
	 * @param int|null $pageId
	 * @param string|null $htmlClass
	 * @param int $count
	 * @return string
	 */
	public function render(
		string $htmlId,
		?int $pageId,
		string $htmlClass = null,
		int $count = 1,
		string $but_class = '',
		string $but_text = ''
	): string
	{
		global $wp;
		$productPrice = $this->_product->getPrice();
		$url = home_url(add_query_arg([$_GET], $wp->request));

		$form = '<input type="hidden" name="product" value="' . $this->_product->getId() . '" />
			<input type="hidden" name="price" value="' . esc_html($productPrice->toJson()) . '" />
			<input type="hidden" name="source[formId]" value="' . $this->_formId . '" />
			<input type="hidden" name="source[pageId]" value="' . $pageId . '" />
			<input type="hidden" name="source[url]" value="' . $url . '" />
			<input type="hidden" name="source[thanksPage]" value="' . $this->_thanksPage . '" />';

		if ($this->_product->canBuyCount($count)) {
				$form .= '<input type="hidden" name="order_contact[totalPrice]" value="' . $this->getTotalPrice()->getPriceVatIncluded() . '">';
				$form .= '
					<script>
						var textError_AjaxError="' . __('Komunikace se serverem se nezdařila. Prosím opakujte požadavek později.', 'mwshop') . '";
					</script>
					';

				// contact info
				$form .= '<div class="mws_order_form_contact_container">';
				$form .= $this->contact($htmlId);
				$form .= '</div>';

				$form .= $this->miniupsell();

				if ($this->_isShippingRequired) {
				$form .= '<div class="mws_order_form_shipping_container ' . (!$this->_isShippingVisible ? 'mws_shipping_needed_field' : '') . '">';
				$form .= '<div class="mws_order_form_title">' . __('Způsob doručení', 'mwshop') . ' </div>';
				$form .= $this->shippingSelect();
				$form .= '</div>';
				}

				$form .= '<div class="mws_order_form_payment_container">';
				$form .= '<div class="mws_order_form_title">' . __('Způsob platby', 'mwshop') . ' </div>';
				$form .= '<div class="mws_order_form_payment_inner_container">';
				$form .= $this->paymentSelect($htmlId);
				$form .= '</div>';
				$form .= '</div>';

				$form .= '<div class="mws_order_form_sumarize_container">';

				$form .= $this->discountCode();

				// Summary
				$form .= $this->summary();

				$form .= '</div>';

				$form .= '<ul class="mws_order_purposes">';
				//terms and conditions
				$termsUrl = MWS()->getUrl_TermsAndConditions();
				if (MWS()->isTermsAllowed() && empty($termsUrl)) {
				$form .= '<li>' . MWS()->printMissingTermsError() . '</li>';
				} elseif (MWS()->isTermsAllowed()) {
				$form .= '<li>' . MWS()->renderTerms() . '</li>';
				}

				if (!$this->_formId) {
				$heureka = new MwHeureka();
				$form .= '<li>' . $heureka->writeDisagree() . '</li>';
				}

				// Purposes
				$form .= MWS()->renderPurposes();
				$form .= '</ul>';
		} else {
			// Product is not available
			$form = '<div class="mws_order_form_info_box">' . __('Omlouváme se, tento produkt momentálně nelze koupit.', 'mwshop') . '</div>';
		}

		MWS()->current()->setShowAvailabilityInAdded(true);
		$availability = $this->_product->getAvailabilityStatus();
		$result = '<form id="' . $htmlId . '" class="mws_order_form ' . ($htmlClass ?? '') . '" '
			. 'data-is-simplified="' . ($this->_allowSimplified ? 1 : 0) . '"'
			. 'data-is-product-shipping-required="' . ($this->_product->isShippingRequired() ? 1 : 0) . '"'
			. 'data-nonce="' . wp_create_nonce(MioShop::MWS_FORM_NONCE) . '">';

			$result .= '<div class="mws_order_form_product ' . $this->_product->getAvailabilityCSS($availability) . '">
							' . $this->product() . '
						</div>
						<div class="mws_order_form_container">
							' . $form . '
						</div>';
		if ($this->_product->canBuyCount($count)) {
			$result .= '<div class="mws_order_form_footer">
							<div class="mws_flash_messages"></div>
      						<a class="' . $but_class . ' ve_content_button_icon mws_order_form_send_button" data-target="' . (MWS()->edit_mode ? 'parent' : '') . '" href="#">
								<span class="ve_but_loading_icon"><svg role="img"><use xlink:href="' . MW_UI_ICONS_URL . 'loading.svg#icon-loading-w"></use></svg></span>
								<span class="ve_but_text">' . ($but_text ?: __('Objednat s povinností platby', 'mwshop')) . '</span>
							</a>
						</div>';
		}
		$result .= '</form>';

		return $result;
	}

	public function contact($htmlId)
	{
		$content = '';

		// email
		$content .= '<div class="ve_form_row">
			<label for="' . $htmlId . '-order_contact_email">' . __('E-mail', 'mwshop') . '<span>*</span></label>
			<input class="ve_form_text" type="text" name="order_contact[email]" value="" id="' . $htmlId . '-order_contact_email" />
		</div>';

		// name
		$content .= '<div class="mws_order_form_name_field ' . ($this->_allowSimplified && !$this->_showName ? 'mws_invoice_needed_field' : '') . '">
		<div class="ve_form_row ve_form_row_half">
			<label for="' . $htmlId . '-order_contact_firstname">' . __('Jméno', 'mwshop') . '<span>*</span></label>
			<input class="ve_form_text" type="text" name="order_contact[address][firstname]" value="" id="' . $htmlId . '-order_contact_firstname" />
		</div>
		<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
			<label for="' . $htmlId . '-order_contact_surname">' . __('Příjmení', 'mwshop') . '<span>*</span></label>
			<input class="ve_form_text" type="text" name="order_contact[address][surname]" value="" id="' . $htmlId . '-order_contact_surname" />
		</div>
		<div class="cms_clear"></div>
		</div>';

		// phone
		if ($this->_showPhone) {
			$content .= '<div class="ve_form_row">
				<label for="' . $htmlId . '-order_contact_phone">' . __('Telefon', 'mwshop') . '<span>*</span></label>
				<input class="ve_form_text" type="text" name="order_contact[address][phone]" value="" id="' . $htmlId . '-order_contact_phone" />
			</div>';
		}

		// address
		$content .= '<div class="mws_order_form_address_container ' . ($this->_allowSimplified ? 'mws_invoice_needed_field' : '') . '">';
			$content .= '<div class="ve_form_row">
				<label for="' . $htmlId . '-order_contact_street">' . __('Ulice a číslo popisné', 'mwshop') . '<span>*</span></label>
				<input class="ve_form_text" type="text" name="order_contact[address][street]" value="" id="' . $htmlId . '-order_contact_street" />
			</div>
			<div class="ve_form_row ve_form_row_half">
				<label for="' . $htmlId . '-order_contact_city">' . __('Město', 'mwshop') . '<span>*</span></label>
				<input class="ve_form_text" type="text" name="order_contact[address][city]" value="" id="' . $htmlId . '-order_contact_city" />
			</div>
			<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
				<label for="' . $htmlId . '-order_contact_zip">' . __('PSČ', 'mwshop') . '<span>*</span></label>
				<input class="mws_order_form_contact_zip ve_form_text" type="text" name="order_contact[address][zip]" value="" id="' . $htmlId . '-order_contact_zip" />
			</div>
			<div class="cms_clear"></div>';

			if ($this->_showCountry) {
			$content .= '<div class="ve_form_row">
						<label for="' . $htmlId . '-order_contact_country">' . __('Země', 'mwshop') . '<span>*</span></label>
						' . mws_generate_country_select('order_contact[address][country]', $htmlId . '-order_contact_country', 'mws_order_form_contact_country ve_form_text', '', false) . '
					</div>';
			} else {
			$defCountry = MWS()->getDefaultShippingCountry();
			$content .= '<input type="hidden" class="mws_order_form_contact_country" name="order_contact[address][country]" value="' . $defCountry . '" data-currency="' . strtoupper(MwsCurrencyEnum::getSupportedByCountry($defCountry)) . '">';
			}

		$content .= '</div>';

		if ($this->_allowSimplified) {
			$content .= '<div class="mws_order_form_more_checkbox mws_order_form_want_invoice_check_container">
					<label class="mws_order_form_want_invoice_check mw_checkbox_label">
						<input class="mw_checkbox" autocomplete="off" type="checkbox" name="order_contact[want_invoice]">'
						. __('Potřebuji vystavit fakturu', 'mwshop') . '
					</label>
				</div>';
		}

		// firm
		$content .= '<div class="mws_order_form_more_checkbox ' . ($this->_allowSimplified ? 'mws_invoice_needed_field' : '') . '">'
			. '<label class="mws_order_form_company_check mw_checkbox_label">'
				. '<input class="mw_checkbox" autocomplete="off" type="checkbox" name="order_contact[is_company]">'
				. __('Nakupuji na firmu', 'mwshop')
			. '</label>'
		. '</div>

		<div class="mws_order_form_company_container cms_nodisp">
			<div class="ve_form_row">
				<label for="' . $htmlId . '-order_contact_company_name">' . __('Název společnosti', 'mwshop') . '<span>*</span></label>
				<input class="ve_form_text" type="text" name="order_contact[company_info][company_name]" value="" id="' . $htmlId . '-order_contact_company_name" />
			</div>
			<div class="ve_form_row ve_form_half">
				<label for="' . $htmlId . '-order_contact_company_id">' . __('IČ', 'mwshop') . '<span>*</span></label>
				<input class="ve_form_text" type="text" name="order_contact[company_info][company_id]" value="" id="' . $htmlId . '-order_contact_company_id" />
			</div>
			<div class="ve_form_row ve_form_half ve_form_half_r">
				<label for="' . $htmlId . '-order_contact_company_vat_id">' . __('DIČ', 'mwshop') . '</label>
				<input class="ve_form_text" type="text" name="order_contact[company_info][company_vat_id]" value="" id="' . $htmlId . '-order_contact_company_vat_id" />
			</div>
			<div class="ve_form_row ve_form_half ve_form_half_r order_contact_company_sk_vat_id ' . (MWS()->getDefaultShippingCountry() == 'SK' ?: 'cms_nodisp') . '">
				<label for="' . $htmlId . '-order_contact_company_sk_vat_id">' . __('IČ DPH', 'mwshop') . '</label>
				<input class="ve_form_text" type="text" name="order_contact[company_info][company_sk_vat_id]" value="" id="' . $htmlId . '-order_contact_company_sk_vat_id" />
			</div>
		</div>';

		// shipping address
		if ($this->_isShippingRequired) {
			$showShippingAddress = ($this->_allowSimplified && $this->_product->isShippingRequired());

			$content .= '<div class="' . (!$this->_isShippingVisible ? 'mws_shipping_needed_field' : '') . '">'
				. '<div class="mws_order_form_more_checkbox mws_order_form_shipping_address_check_container">'
					. '<label class="mws_order_form_shipping_address_check mw_checkbox_label ' . ($showShippingAddress ? 'disabled' : '') . '">'
						. '<input class="mw_checkbox" autocomplete="off" type="checkbox" name="order_contact[has_shipping_addr]" ' . ($showShippingAddress ? 'checked="checked"' : '') . '>'
						. __('Doručit na jinou adresu', 'mwshop')
					. '</label>'
				. '</div>'
				. '<div class="mws_order_form_shipping_address_container ' . ($showShippingAddress ? '' : 'cms_nodisp') . '">'
					. '<div class="ve_form_row ve_form_row_half">'
						. '<label for="' . $htmlId . '-order_contact_shipping_firstname">' . __('Jméno', 'mwshop') . '<span>*</span></label>'
						. '<input class="ve_form_text" type="text" name="order_contact[shipping_address][firstname]" value="" id="' . $htmlId . '-order_contact_shipping_firstname"/>'
					. '</div>'
					. '<div class="ve_form_row ve_form_row_half ve_form_row_half_r">'
						. '<label for="' . $htmlId . '-order_contact_shipping_surname">' . __('Příjmení', 'mwshop') . '<span>*</span></label>'
						. '<input class="ve_form_text" type="text" name="order_contact[shipping_address][surname]" value="" id="' . $htmlId . '-order_contact_shipping_surname"/>'
					. '</div>'
					. '<div class="cms_clear"></div>'
					. '<div class="ve_form_row">'
						. '<label for="' . $htmlId . '-order_contact_shipping_street">' . __('Ulice', 'mwshop') . '<span>*</span></label>'
						. '<input class="ve_form_text" type="text" name="order_contact[shipping_address][street]" value="" id="' . $htmlId . '-order_contact_shipping_street"/>'
					. '</div>'
					. '<div class="ve_form_row ve_form_row_half">'
						. '<label for="' . $htmlId . '-order_contact_shipping_city">' . __('Město', 'mwshop') . '<span>*</span></label>'
						. '<input class="ve_form_text" type="text" name="order_contact[shipping_address][city]" value="" id="' . $htmlId . '-order_contact_shipping_city"/>'
					. '</div>'
					. '<div class="ve_form_row ve_form_row_half ve_form_row_half_r">'
						. '<label for="' . $htmlId . '-order_contact_shipping_zip">' . __('PSČ', 'mwshop') . '<span>*</span></label>'
						. '<input class="mws_order_form_shipping_zip ve_form_text" type="text" name="order_contact[shipping_address][zip]" value="" id="' . $htmlId . '-order_contact_shipping_zip"/>'
					. '</div>';
					if ($this->_showCountry) {
				$content .= '<div class="ve_form_row">'
			. '<label for="' . $htmlId . '-order_contact_shipping_country">' . __('Země', 'mwshop') . '<span>*</span></label>'
			. mws_generate_country_select('order_contact[shipping_address][country]', $htmlId . '-order_contact_shipping_country', 'mws_order_form_shipping_country ve_form_text', null, false)
				. '</div>';
					} else {
				$defCountry = MWS()->getDefaultShippingCountry();
				$content .= '<input type="hidden" class="mws_order_form_shipping_country" name="order_contact[shipping_address][country]" value="' . $defCountry . '" data-currency="' . strtoupper(MwsCurrencyEnum::getSupportedByCountry($defCountry)) . '">';
					}
				$content .= '<div class="cms_clear"></div></div>'
			. '</div>';
		}

		// note
		if ($this->_showNote) {
			$content .= '<div class="ve_form_row">'
				. '<label for="order_contact_note">' . __('Poznámka', 'mwshop') . ' </label>'
				. '<textarea class="ve_form_text" name="order_contact[note]" rows="4"></textarea>'
			. '</div>';
		}

		return $content;
	}

	public function product()
	{
		$thumb = $this->_product->getThumbnail()->getImg('large', ['loading' => false]);
		$content = '<div class="mws_product_card">';
		if ($thumb) {
			$content .= '<div class="mws_product_card_thumb responsive_image">'
					. '<div class="mw_image_ratio mw_image_ratio_' . MWS()->thumb_name . '">' . $this->_product->getThumbnail()->getImg('large', ['loading' => false]) . '</div>'
				. '</div>';
		}
		$content .= '<div class="mws_product_card_content">'
				. '<div class="mws_product_card_title_container">'
					. '<div class="mws_product_card_title">' . $this->_product->getName() . '</div>'
					. (MWS()->current()->showAvailabilityInAdded() ? $this->_product->htmlAvailabilityMessage() : '') // @TODO this show always only current state
				. '</div>'
				. ($this->_showProductCount ? mwFrontComponents::countField(['max_count' => $this->_product->availableCountToSell()]) : '')
				. '<div class="mws_product_card_price">'
					. $this->_product->getPrice()->htmlPriceVatIncluded()
				. '</div>'
			. '</div>'
		. '</div>';

		return $content;
	}

	public function miniupsell()
	{
		$content = '';

		if ($this->_allowMiniupsell && $this->_miniupsellProduct) {
			if (!$this->_miniupsellProduct->canBuyCount()) {
				if (MWS()->edit_mode) {
					$content .= '<div class="mw_error_box">' . sprintf(__('Nastavený miniupsell nelze koupit. Změňte miniupsell v <a href="%s" target="_blank">' . __('nastavení formuláře', 'cms_ve') . '</a> nebo upravte omezení prodeje v <a href="%s" target="_blank">nastavení produktu</a>', 'mwshop'), mwSetting()->getObject('mwsform')->getEditUrl($this->_formId), mwSetting()->getObject('mwproduct')->getEditUrl($this->_miniupsellProduct->getId())) . '</div>';
				}

				return $content;
			}

			if ($this->_miniupsellProduct->hasVariants()) {
				if (MWS()->edit_mode) {
					$content .= '<div class="mw_error_box">' . __('Jako miniupsell nelze prodávat produkt s více variantami', 'mwshop') . ' <a href="' . mwSetting()->getObject('mwsform')->getEditUrl($this->_formId) . '" target="_blank">' . __('Upravit formulář', 'cms_ve') . '</a></div>';
				}

				return $content;
			}

			$content = '<div class="mws_order_form_miniupsell">'
				. '<label class="mws_order_form_miniupsell_head">'
					. '<input class="mw_checkbox" type="checkbox" autocomplete="off" name="miniupsell" data-shipping-required="' . ($this->_miniupsellProduct->isShippingRequired() ? 1 : 0) . '">'
					. '<div class="mws_order_form_miniupsell_title">'
						. (isset($this->_basicSettings['miniupsell_title']) && $this->_basicSettings['miniupsell_title'] ? $this->_basicSettings['miniupsell_title'] : $this->_miniupsellProduct->getName())
					. '</div>'
					. '<div class="mws_order_form_miniupsell_price">'
						. $this->_miniupsellProduct->getPrice()->htmlPriceVatIncluded()
					. '</div>'
					. '<div class="mws_order_form_miniupsell_arrow">' . mw_icon('icon-arrow-right') . '</div>'
				. '</label>';

			$description = isset($this->_basicSettings['miniupsell_description']) && $this->_basicSettings['miniupsell_description'] ? $this->_basicSettings['miniupsell_description'] : $this->_miniupsellProduct->getExcerpt();

			if ($description) {
				$thumb = $this->_miniupsellProduct->getThumbnail()->getImg('large', ['loading' => false]);
				$content .= '<div class="mws_order_form_miniupsell_content">';
				if ($thumb) {
					$content .= '<div class="mws_order_form_miniupsell_thumb responsive_image">'
							. '<div class="mw_image_ratio mw_image_ratio_' . MWS()->thumb_name . '">' . $thumb . '</div>'
						. '</div>';
				}
				$content .= '<div class="mws_order_form_miniupsell_text">'
						. (isset($this->_basicSettings['miniupsell_description']) && $this->_basicSettings['miniupsell_description'] ? $this->_basicSettings['miniupsell_description'] : $this->_miniupsellProduct->getExcerpt())
					. '</div>'
				. '</div>';
			}
			$content .= '</div>';
		}

		return $content;
	}

	public function summary()
	{
		$content = '<div class="mws_order_form_sumarize">';

		if (!$this->_cart->isRecounted()) {
			$content .= '<div class="mws_error">';
			$content .= empty($this->_cart->getRecountError())
					? __('Omlouváme se, nepodařilo se spočítat cenu objednávky. Proveďte přepočítání.', 'mwshop')
					: $this->_cart->getRecountError();

			if (MWS()->edit_mode && !empty($this->_cart->getRecountAdminError())) {
				$content .= '<small>' . __('Technický popis chyby:', 'mwshop') . ' <span>' . $this->_cart->getRecountAdminError() . '</span></small>';
			}
			$content .= '</div>';
		} else {
			$content .= '<table>';

			$currency = null;
			$price = $this->_cart->getStoredTotalPrice();
			if ($this->_cart->isRecounted() && $price !== null) {
				$currency = MwsCurrencyEnum::getSymbol($price->getCurrency());

				$discountCode = $this->_cart->getDiscountCode();
				if ($discountCode !== null) {
					$content .= '<tr class="mws_order_form_discount_code_info">'
							. '<td>'
							. __('Slevový kód', 'mwshop') . ': ' . esc_html($discountCode->getCode())
							. ' (<a href="#" class="mws_order_form_remove_discount_code">' . __('zrušit', 'mwshop') . '</a>)'
							. '</td>'
							. '<td>'
							. '-' . $discountCode->printValue($price->getCurrency())
							. '</td>'
						. '</tr>';
				}
			}

			$content .= '<tr class="mws_order_form_total_price">'
					. '<td>' . __('Celková cena', 'mwshop') . '</td>'
					. '<td>';
					if ($currency !== null) {
				$content .= htmlPriceSimpleIncluded($price->getPriceVatIncluded(), $currency);
				$content .= htmlPriceSimpleExcluded($price->getPriceVatExcluded(), $currency);
					} else {
				$content .= __('(nutno přepočítat)', 'mwshop');
					}
					$content .= '</td>'
					. '</tr>';

			try {
				if ($this->_cart->shouldApplyReverseCharge()) {
					$content .= '<tr>';
					$content .= '<td class="mws_cart_summary_reverse_charge" colspan="3">' . __('Daň odvede zákazník', 'mwshop') . '</td>';
					$content .= '</tr>';
				}
			} catch (ReverseChargeApplicationException $e) {
				// ignore
			}
			$content .= '</table>';
		}

		$content .= '</div>';

		return $content;
	}

	public function discountCode()
	{
		$content = '';

		if ($this->_allowDiscountCodes) {
			$content = '<div class="mws_order_form_discount_code">'
				. '<label class="mw_checkbox_label">'
					. '<input class="mw_checkbox mws_order_form_discount_code_check" autocomplete="off" type="checkbox" name="">'
					. __('Chci uplatnit slevový kód', 'mwshop')
				. '</label>'
				. '<div class="mws_order_form_discount_code_form">'
					. '<div class="mws_order_form_discount_code_form_in">'
						. '<input class="ve_form_text" type="text" name="discount_code" placeholder="' . __('Zde vložte váš kód', 'mwshop') . '" value="">'
						. '<a class="mws_order_form_apply_discount_code_but ve_content_button ve_content_button_type_1 ve_content_button_icon" href="#">'
							. '<span class="ve_but_loading_icon">' . mw_icon('icon-loading-w', '', MW_UI_ICONS_URL . 'loading.svg') . '</span>'
							. '<span class="ve_but_text">' . __('Uplatnit', 'mwshop') . '</span>'
						. '</a>'
					. '</div>'
				. '</div>'
			. '</div>';
		}

		return $content;
	}

	public function shippingSelect()
	{
		$currency = MWS()->getDefaultCurrency('key');

		$content = MwsShipping::htmlRadio($this->getTotalPrice(), $currency, '', MWS()->getDefaultShippingCountry(), '', $this->_isShippingRequired, null, '', $this->_shippingMethods, null, null, $this->_cart->getItems()->getTotalWeight());

		return $content;
	}

	public function getShippingPrices(): array
	{
		$currency = MWS()->getDefaultCurrency('key');
		$weight = $this->_cart->getItems()->getTotalWeight();
		$totalPrice = $this->getTotalPrice();
		$shippings = $this->_isShippingRequired ? MwsShipping::getAll([], false, '') : [MwsShippingElectronic::getInstance()];
		$prices = [];

		foreach ($shippings as $shipping) {
			$price = $shipping->getPriceForCart($totalPrice, $weight);
			$prices[$shipping->getId()] = $price === null ? null : $price->asCurrency($currency)->htmlPriceVatIncluded(1, true, 'mws_price_inline');
		}

		return $prices;
	}

	public function paymentSelect(string $formHtmlId)
	{
		$cart = $this->getCart();

		$currency = $cart->getCurrency();
		$country = $cart->getInvoiceCountry();

		return MwsPaymentMethod::htmlRadio($this->_paymentMethods, $currency, $country, null, '#' . $formHtmlId);
	}

}
