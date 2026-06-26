<?php declare(strict_types=1);

use Mioweb\Shop\Order\Order;
use Mioweb\Shop\Order\OrderItem;
use Mioweb\Shop\Order\OrderRepository;
use Mioweb\Shop\QrPlatba;
use Nette\Utils\Json;

/**
 * Všechny proměnné se do konverzních lódů zadávají ve formátu `%%nazev_promenne%%`.
 * Nezáleží na velikosti písmen.
 * Všechny proměnné mají CZ a EN variantu.
 *
 * Dostupné proménné
 * V závorce je vždy uveden příklad hodnoty
 *
 * `currency` / `mena` ("czk")
 * `currency_symbol` / `mena_symbol` ("Kč")
 * `payment_url` / `platba_url` ("https://form.fapi.cz/gateway?id=abcd")
 * `online_pay` / `platba_online` ("<a href=\"https://form.fapi.cz/gateway?id=abcd\" target=\"_blank\">Zaplatit online</a>")
 * `if_transfer_info` / `podmineny_prevod_info` ("<br>999321<br><span class="num">246</span>&nbsp;Kč")
 * `transfer_info` / `prevod_info` ("<br>999321<br><span class="num">246</span>&nbsp;Kč")
 * `variable_symbol` / `variabilni_symbol` ("123456789")
 * `bank_account` / `cislo_uctu` ("123456789/0123")
 * `eshop_email` ("info@eshop.cz")
 * `eshop_phone` / `eshop_telefon` ("+420777777777")
 * `website_url` / `url_webu` ("https://eshop.cz")
 * `website_name` / `nazev_webu` (E-shop.cz)
 * `note` / `poznamka` ("Poznámka")
 * `billing_address` / `fakturacni_adresa` ("Ulice\n123 02 Město\nČeská republika\n")
 * `shipping_address` / `dorucovaci_adresa` stejný formát jako `billing_address` nebo prázdné, pokud je vyplněna jen fakturační adresa
 * `shipping_price` / `cena_doruceni` / `shipping_price_vat` / `cena_doruceni_s_dph` (99.90)
 * `shipping_price_novat` / `cena_doruceni_bez_dph` (82.56)
 * `payment_price` / `cena_platby` / `payment_price_vat` / `cena_platby_s_dph` (24)
 * `payment_price_novat` / `cena_platby_bez_dph` (20)
 * `shipping_method` / `zpusob_doruceni` ("Poštou")
 * `payment_method` / `zpusob_platby` ("Dobírka")
 * `price_novat` / `cena_bez_dph` (1200.0)
 * `price` / `price_vat` / `cena` / `cena_s_dph` (1452.0)
 * `order_content` / `obsah_objednavky` ('[{"name":"Plyšák","price":{"priceVatIncluded":726.0,"priceVatExcluded":600.0,"vatPercentage":21,"currency":"czk"},"count":2,"productId":null}]')
 * `order_date` / `datum_objednavky` ("2021-05-06 11:24:18")
 * `order_code` / `cislo_objenavky` ("123456789")
 * `ga_items` ('[{"id":123,"name":"Plyšák","quantity":2,"price":600}]')
 * `discount_code` / `voucher` / `slevovy_kod` ("ABCD")
 *
 * Staré proměnné pro Affilbox zůstaly pro zpětnou kompatibilitu
 * Zadávají se ve formátu `NAZEV_PROMENNE` (bez znaků "%%") a pouze velkými písmeny
 *
 * - `CENA` (1200.0)
 * - `OZNACENI_MENY` ("Kč")
 * - `ID_TRANSAKCE` ("4060d33e40d74ef839a777e6b3fae6cc") nebo ("123456789")
 */
class MwsOrderVariables
{

	/** @var string|null */
	private $currency;

	/** @var string|null */
	private $nativeCurrency;

	/** @var string */
	private $currencySymbol;

	/** @var string */
	private $nativeCurrencySymbol;

	/** @var string|NULL */
	private $paymentUrl;

	/** @var string */
	private $onlinePay;

	/** @var string|null */
	private $ifTransferInfo;

	/** @var string */
	private $transferInfo;

	/** @var string */
	private $variableSymbol;

	/** @var string|null */
	private $bankAccount;

	/** @var string|null */
	private $eshopEmail;

	/** @var string|null */
	private $eshopPhone;

	/** @var string */
	private $websiteUrl;

	/** @var string */
	private $websiteName;

	/** @var string|null */
	private $note;

	/** @var MwsContact|null */
	private $shippingContact;

	/** @var MwsContact|null */
	private $invoiceContact;

	/** @var float|null */
	private $shippingPriceNovat;

	/** @var float|null */
	private $shippingPrice;

	/** @var float|null */
	private $shippingNativePriceNovat;

	/** @var float|null */
	private $shippingNativePrice;

	/** @var string|null */
	private $shippingMethod;

	/** @var string|null */
	private $paymentMethod;

	/** @var float|null */
	private $priceNovat;

	/** @var float|null */
	private $price;

	/** @var float|null */
	private $priceNativeNovat;

	/** @var float|null */
	private $priceNative;

	/** @var OrderItem[]|mixed[] */
	private $items;

	/** @var \DateTimeInterface|null */
	private $orderDate;

	/** @var string */
	private $orderCode;

	/** @var string|null */
	private $discountCode;

	/** @var string */
	private $orderInfo;

	/** @var string */
	private $shippingId;

	/** @var string|null */
	private $trackingLink;

	/** @var string|null */
	private $clientPhone;

	/** @var string|null */
	private $clientEmail;

	/** @var string|null */
	private $phone;

	public function __construct(
		?string $currency,
		string $nativeCurrency,
		?string $paymentUrl,
		string $onlinePay,
		?string $ifTransferInfo,
		string $transferInfo,
		string $variableSymbol,
		?string $bankAccount,
		?string $eshopEmail,
		?string $eshopPhone,
		?string $note,
		?MwsContact $shippingContact,
		?MwsContact $invoiceContact,
		?float $shippingPriceNovat,
		?float $shippingPrice,
		?float $shippingNativePriceNovat,
		?float $shippingNativePrice,
		?string $shippingMethod,
		?string $paymentMethod,
		?float $priceNovat,
		?float $price,
		?float $priceNativeNovat,
		?float $priceNative,
		array $orderItems,
		?\DateTimeInterface $orderDate,
		string $orderCode,
		?string $discountCode,
		string $orderInfo,
		?string $shippingId,
		?string $trackingLink,
		?string $clientPhone,
		?string $clientEmail,
		?string $phone
	)
	{
		$this->currency = strtolower($currency);
		$this->nativeCurrency = strtolower($nativeCurrency);
		$this->currencySymbol = $currency !== null ? MwsCurrencyEnum::getSymbol($currency) : null;
		$this->nativeCurrencySymbol = $currency !== null ? MwsCurrencyEnum::getSymbol($nativeCurrency) : null;
		$this->paymentUrl = $paymentUrl;
		$this->onlinePay = $onlinePay;
		$this->ifTransferInfo = $ifTransferInfo;
		$this->transferInfo = $transferInfo;
		$this->variableSymbol = $variableSymbol;
		$this->bankAccount = $bankAccount;
		$this->eshopEmail = $eshopEmail;
		$this->eshopPhone = $eshopPhone;
		$this->websiteUrl = MWS()->getUrl_Home();
		$this->websiteName = str_replace('&amp;', '&', get_bloginfo('name'));
		$this->note = $note;
		$this->shippingContact = $shippingContact;
		$this->invoiceContact = $invoiceContact;
		$this->shippingPriceNovat = $shippingPriceNovat;
		$this->shippingPrice = $shippingPrice;
		$this->shippingNativePriceNovat = $shippingNativePriceNovat;
		$this->shippingNativePrice = $shippingNativePrice;
		$this->shippingMethod = $shippingMethod;
		$this->paymentMethod = $paymentMethod;
		$this->priceNovat = $priceNovat;
		$this->price = $price;
		$this->priceNativeNovat = $priceNativeNovat;
		$this->priceNative = $priceNative;
		$this->items = $orderItems;
		$this->orderDate = $orderDate;
		$this->orderCode = $orderCode;
		$this->discountCode = $discountCode;
		$this->orderInfo = $orderInfo;
		$this->shippingId = $shippingId;
		$this->trackingLink = $trackingLink;
		$this->clientPhone = $clientPhone;
		$this->clientEmail = $clientEmail;
		$this->phone = $phone;
	}

	public function toArrayRaw(): array
	{
		$invoiceAddress = $this->invoiceContact !== null ? $this->invoiceContact->getAddress() : null;
		$shippingAddress = $this->shippingContact !== null ? $this->shippingContact->getAddress() : $invoiceAddress;

		$invoiceContact = $this->invoiceContact !== null ? $this->invoiceContact->format() : '';
		$shippingContact = $this->shippingContact !== null ? $this->shippingContact->format() : $invoiceContact;

		$priceNoShipping = $this->price !== null ? ($this->shippingPrice !== null ? $this->price - $this->shippingPrice : '') : '';
		$priceNoShippingNoVat = $this->priceNovat !== null ? ($this->shippingPriceNovat !== null ? $this->priceNovat - $this->shippingPriceNovat : '') : '';
		$priceNativeNoShipping = $this->priceNative !== null ? ($this->shippingNativePrice !== null ? $this->priceNative - $this->shippingNativePrice : '') : '';
		$priceNativeNoShippingNoVat = $this->priceNativeNovat !== null ? ($this->shippingNativePriceNovat !== null ? $this->priceNativeNovat - $this->shippingNativePriceNovat : '') : '';

		$variables = [
			'CURRENCY' => $this->currency ?? '',
			'NATIVE_CURRENCY' => $this->nativeCurrency ?? '',
			'CURRENCY_SYMBOL' => $this->currencySymbol,
			'NATIVE_CURRENCY_SYMBOL' => $this->nativeCurrencySymbol ?? '',
			'PAYMENT_URL' => $this->paymentUrl ?? '',
			'ONLINE_PAY' => $this->onlinePay,
			'IF_TRANSFER_INFO' => $this->ifTransferInfo ?? '',
			'TRANSFER_INFO' => $this->transferInfo,
			'VARIABLE_SYMBOL' => $this->variableSymbol,
			'BANK_ACCOUNT' => $this->bankAccount ?? '',
			'ESHOP_EMAIL' => $this->eshopEmail ?? '',
			'ESHOP_PHONE' => $this->eshopPhone ?? '',
			'WEBSITE_URL' => $this->websiteUrl,
			'WEBSITE_NAME' => $this->websiteName,
			'NOTE' => $this->note ?? '',
			'SHIPPING_CONTACT' => $shippingContact,
			'BILLING_CONTACT' => $invoiceContact,
			'SHIPPING_ADDRESS' => $shippingAddress !== null ? $shippingAddress->format() : '',
			'BILLING_ADDRESS' => $invoiceAddress !== null ? $invoiceAddress->format() : '',
			'SHIPPING_PRICE_NOVAT' => $this->shippingPriceNovat ?? '',
			'SHIPPING_PRICE' => $this->shippingPrice ?? '',
			'SHIPPING_METHOD' => $this->shippingMethod ?? '',
			'PAYMENT_METHOD' => $this->paymentMethod ?? '',
			'PRICE_NOVAT' => $this->priceNovat ?? '',
			'PRICE' => $this->price ?? '',
			'PRICE_NO_SHIPPING_NOVAT' => $priceNoShippingNoVat,
			'PRICE_NO_SHIPPING' => $priceNoShipping,
			'NATIVE_PRICE_NOVAT' => $this->priceNativeNovat ?? '',
			'NATIVE_PRICE' => $this->priceNative ?? '',
			'NATIVE_PRICE_NO_SHIPPING_NOVAT' => $priceNativeNoShippingNoVat,
			'NATIVE_PRICE_NO_SHIPPING' => $priceNativeNoShipping,
			'ORDER_CONTENT' => $this->getOrderContent(),
			'ORDER_DATE' => $this->orderDate !== null ? $this->orderDate->format('Y-m-d H:i:s') : '',
			'ORDER_CODE' => $this->orderCode,
			'GA_ITEMS' => MwVariables::generateItemsForGA($this->items, $this->currency),
			'DISCOUNT_CODE' => $this->discountCode ?? '',
			'PARCEL_POINT_ID' => $this->shippingId ?? '',
			'TRACKING_LINK' => $this->trackingLink ?? '',
			'CLIENT_PHONE' => $this->clientPhone ?? '',
			'PHONE' => $this->phone ?? '',
			'CLIENT_EMAIL' => $this->clientEmail ?? '',

			//'ORDER_INFO' => '<strong>' . __('Obsah objednávky', 'mwshop') . '</strong><br>' . $this->formatOrderContentTable() . '<br><br>' . $this->orderInfo ?? '',
		];

		return MwVariables::addAliases($variables);
	}

	public function toArrayFormatted(): array
	{
		$invoiceAddress = $this->invoiceContact !== null ? $this->invoiceContact->getAddress() : null;
		$shippingAddress = $this->shippingContact !== null ? $this->shippingContact->getAddress() : null;

		$variables = [
			'CURRENCY' => $this->currency ?? '',
			'NATIVE_CURRENCY' => $this->nativeCurrency ?? '',
			'CURRENCY_SYMBOL' => $this->currencySymbol,
			'NATIVE_CURRENCY_SYMBOL' => $this->nativeCurrency ? MwsCurrencyEnum::getSymbol($this->nativeCurrency) : '',
			'PAYMENT_URL' => $this->paymentUrl ?? '',
			'ONLINE_PAY' => $this->onlinePay,
			'IF_TRANSFER_INFO' => $this->ifTransferInfo ?? '',
			'TRANSFER_INFO' => $this->transferInfo,
			'VARIABLE_SYMBOL' => $this->variableSymbol,
			'BANK_ACCOUNT' => $this->bankAccount ?? '',
			'ESHOP_EMAIL' => $this->eshopEmail ?? '',
			'ESHOP_PHONE' => $this->eshopPhone ?? '',
			'WEBSITE_URL' => $this->websiteUrl,
			'WEBSITE_NAME' => $this->websiteName,
			'NOTE' => $this->note ?? '',
			'SHIPPING_CONTACT' => $this->shippingContact !== null ? $this->shippingContact->format(true) : __('Stejná jako fakturační adresa', 'mwshop'),
			'BILLING_CONTACT' => $this->invoiceContact !== null ? $this->invoiceContact->format(true) : '',
			'SHIPPING_ADDRESS' => $shippingAddress !== null ? $shippingAddress->format() : __('Stejná jako fakturační adresa', 'mwshop'),
			'BILLING_ADDRESS' => $invoiceAddress !== null ? $invoiceAddress->format() : '',
			'SHIPPING_PRICE_NOVAT' => $this->shippingPriceNovat !== null ? htmlPriceSimple($this->shippingPriceNovat, $this->currencySymbol) : '',
			'SHIPPING_PRICE' => $this->shippingPrice !== null ? htmlPriceSimple($this->shippingPrice, $this->currencySymbol) : '',
			'SHIPPING_METHOD' => $this->shippingMethod ?? '',
			'PAYMENT_METHOD' => $this->paymentMethod ?? '',
			'PRICE_NOVAT' => $this->priceNovat !== null ? htmlPriceSimple($this->priceNovat, $this->currencySymbol) : '',
			'PRICE' => $this->price !== null ? htmlPriceSimple($this->price, $this->currencySymbol) : '',
			'NATIVE_PRICE_NOVAT' => $this->priceNativeNovat !== null ? htmlPriceSimple($this->priceNativeNovat, $this->nativeCurrencySymbol) : '',
			'NATIVE_PRICE' => $this->priceNative !== null ? htmlPriceSimple($this->priceNative, $this->nativeCurrencySymbol) : '',
			'ORDER_CONTENT' => $this->formatOrderContentTable(),
			'ORDER_DATE' => $this->orderDate !== null ? mwFormatAsDateTime(mwConvDateTimeUTC2TimestampUTC($this->orderDate->format('Y-m-d H:i:s')), false) : '',
			'ORDER_CODE' => $this->orderCode,
			'GA_ITEMS' => MwVariables::generateItemsForGA($this->items, $this->currency),
			'DISCOUNT_CODE' => $this->discountCode ?? '',
			'ORDER_INFO' => '<strong>' . __('Obsah objednávky', 'mwshop') . '</strong><br>' . $this->formatOrderContentTable() . '<br><br>' . $this->orderInfo ?? '',
			'PARCEL_POINT_ID' => $this->shippingId ?? '',
			'TRACKING_LINK' => $this->trackingLink ? __('Svou zásilku můžete sledovat zde:', 'mwshop') . ' <a href="' . $this->trackingLink . '">' . $this->trackingLink . '</a>' : '',
			'CLIENT_PHONE' => $this->clientPhone ?? '',
			'PHONE' => $this->phone ?? '',
			'CLIENT_EMAIL' => $this->clientEmail ?? '',
		];

		return MwVariables::addAliases($variables);
	}

	public static function fromOrder(string $vs, bool $formated = false): array
	{
		$order = OrderRepository::getOrderByOrderNum($vs);
		if ($order !== null) {
			$variables = self::fromMwOrder($order);

			return $formated ? $variables->toArrayFormatted() : $variables->toArrayRaw();
		}

		return [];
	}

	public static function fromMwOrder(Order $order): self
	{
		$gateLive = $order->getGateLive();

		$items = $gateLive->getItems();

		$currency = $gateLive->getCurrency();
		$nativeCurrency = $order->getNativeCurrency();
		$bankAccount = $gateLive->getBankAccount($currency);

		$sPrice = null;
		$sNativePrice = null;
		foreach ($items as $item) {
			if ($item->getType() === MwsOrderItemType::Shipping) {
				$sPrice = $item->getPrice($currency);
				$sNativePrice = $nativeCurrency !== null ? $item->getPrice($nativeCurrency) : null;
			}
		}

		$trackingNumber = $order->getTrackingNumber();
		$trackingUrl = $order->getTrackingUrl();
		$trackingLink = '';
		if ($trackingUrl && $trackingNumber) {
			$trackingLink = str_replace(['{CISLO_ZASILKY}', '{TRACKING_NUMBER}'], $trackingNumber, $trackingUrl, $replaceCnt);

			if ($replaceCnt === 0) { //variable to replace was not found, add the tracking number at the end of the link
				$trackingLink = $trackingUrl . $trackingNumber;
			}
		}

		$price = $order->getPrice();
		$priceNative = $order->getNativePrice();

		$invoiceContact = $order->getInvoiceContact();
		$shippingContact = $order->getShippingContact();

		$orderNumber = $order->getNumber();

		$clientPhone = $invoiceContact->getPhone();
		$clientEmail = $invoiceContact->getEmail();
		$shipping = $shippingContact !== null;
		$contact = null;

		$phone = $shipping ? $shippingContact->getPhone() : $invoiceContact->getPhone();

		if ($gateLive instanceof MwsOrderGate_Fapi) {
			$clientPhone = null;
			$contact = $invoiceContact->getPhone();
			$phone = $invoiceContact->getPhone();
		}

		$priceVatIncluded = $price !== null ? $price->getPriceVatIncluded() : null;
		$priceVatIncludedFormatted = $priceVatIncluded !== null
			? htmlPriceSimple($priceVatIncluded, MwsCurrencyEnum::getSymbol($price->getCurrency()))
			: '';
		if ($bankAccount !== null) {
			$bankAccountNumber = $bankAccount->getNumber();
			$iban = $bankAccount->getIban();
			$swift = $bankAccount->getBic();
		} else {
			$bankAccountNumber = $iban = $swift = null;
		}

		$ibanHtml = (bool) $iban ? '<strong>' . __('IBAN', 'mwshop') . '</strong>: ' . $iban . '<br>' : '';
		$swiftHtml = (bool) $swift ? '<strong>' . __('SWIFT', 'mwshop') . '</strong>: ' . $swift . '<br>' : '';

		$paymentInfo = '<strong>' . __('Bankovní účet', 'mwshop') . '</strong>: ' . $bankAccountNumber . '<br>' . $ibanHtml . $swiftHtml . '<strong>' . __('Variabilní symbol', 'mwshop') . '</strong>: ' . $orderNumber . '<br><strong>' . __('Částka', 'mwshop') . '</strong>: ' . $priceVatIncludedFormatted;
		$supplier = $order->getSupplierContact();
		$paymentType = $order->getPayment()['type'] ?? null;

		$payUrl = $order->getDirectPaymentUrl();
		$paymentUrl = $order->getDirectPaymentLink(__('Zaplatit online', 'mwshop'));
		$payment = $order->getPaymentTitle();
		$note = $order->getCustomerNote();
		if ($bankAccountNumber !== null) {
			$qrCode = new QrPlatba($bankAccountNumber);
			if ($qrCode->canHaveQrCode($order->getInvoiceContact()->getAddress()->getCountry())) {
				$qrCode->loadDataQrCode(
					$priceVatIncluded,
					$currency,
					$orderNumber,
					null,
					null,
					null,
					null,
					get_bloginfo('name'),
					$qrCode::SMALL_SIZE_QR
					);
				if ($qrCode->generateGETRequest($qrCode::QR_CODE)) {
					$paymentInfo = '<table><tr><td valign="top" style="padding-top:10px; width: 50%" >' . $paymentInfo . '</td>';
					$paymentInfo .= '<td align="right" style="width: 50%">';
					$paymentInfo .= '<img alt="QR platba" src="' . $qrCode->getUrl() . '" style="width: 50%"/></td></tr>';
					$paymentInfo .= '</table>';
				}
			}
		}

		if ($paymentType === MwsPayType::Wire) {
			$payment .= '<br><br>' . $paymentInfo;
		} elseif ($paymentUrl && !$order->isPaid()) {
			$payment .= '<br>' . $paymentUrl;
		}

		$shipping = $order->getShipping()['name'] ?? '';
		$shippingId = $order->getShipping()['externalId'] ?? null;
		$isPacketaPickup = MWS()->packeta->isOrderWithPacketa($order) && isset($order->getShipping()['pickupAddress']) && $order->getShipping()['pickupAddress'];
		if ($isPacketaPickup) {
			$shipping .= '<br><br>';
			$shipping .= '<strong>' . __('Výdejní místo Zásilkovny', 'mwshop') . ':</strong><br>';
			$shipping .= str_replace(', ', '<br>', $order->getShipping()['pickupAddress']);

			if ($shippingId) {
				$shippingId = $order->getShipping()['externalId'];
			}
		}

		$invoiceContactFormated = $invoiceContact !== null ? $invoiceContact->format(true) : '';

		$orderInfo = '';

		if ($invoiceContact !== null) {
			$orderInfo .= '<strong>' . __('Kontaktní údaje', 'mwshop') . '</strong><br>';
			$orderInfo .= __('E-mail: ', 'mwshop') . $clientEmail . '<br>';
			if (($clientPhone !== null && $clientPhone !== '') || ($contact !== null && $contact !== '')) {
			$contact = $clientPhone ?? $contact;
			$orderInfo .= __('Telefon: ', 'mwshop') . $contact . '<br>';
			}
			$orderInfo .= '<br>';
		}
		if ($invoiceContactFormated) {
			$orderInfo .= '<strong>' . __('Fakturační údaje', 'mwshop') . '</strong><br>';
			$orderInfo .= $invoiceContactFormated . '<br>';
		}
		$shippintType = $order->getShipping()['type'] ?? null;
		if (!$isPacketaPickup && $shippintType !== MwsShippingType::Personal) {
			$orderInfo .= '<strong>' . __('Doručovací údaje', 'mwshop') . '</strong><br>';
			$orderInfo .= $shippingContact !== null ? $shippingContact->format(true, true) . '<br>' : __('Stejné jako fakturační', 'mwshop') . '<br><br>';
		}

		$orderInfo .= '<strong>' . __('Způsob platby', 'mwshop') . '</strong><br>';
		$orderInfo .= $payment . '<br><br>';

		$orderInfo .= '<strong>' . __('Způsob doručení', 'mwshop') . '</strong><br>';
		$orderInfo .= $shipping;

		if ($trackingLink) {
			$orderInfo .= '<br><br><strong>' . __('Svou zásilku můžete sledovat zde:', 'mwshop') . '</strong><br>';
			$orderInfo .= '<a href="' . $trackingLink . '">' . $trackingLink . '</a>';
		}

		if ($note) {
			$orderInfo .= '<br><br><strong>' . __('Poznámka', 'mwshop') . '</strong><br>';
			$orderInfo .= $note;
		}

		return new self(
			$currency,
			$nativeCurrency,
			$payUrl,
			$paymentUrl,
			$paymentType === MwsPayType::Wire ? $paymentInfo : null,
			$paymentInfo,
			$orderNumber,
			$bankAccountNumber,
			$supplier !== null ? $supplier->getEmail() : null,
			$supplier !== null ? $supplier->getPhone() : null,
			$note,
			$shippingContact,
			$invoiceContact,
			$sPrice !== null ? (float) $sPrice->getPriceVatExcluded() : null,
			$sPrice !== null ? (float) $sPrice->getPriceVatIncluded() : null,
			$sNativePrice !== null ? (float) $sNativePrice->getPriceVatExcluded() : null,
			$sNativePrice !== null ? (float) $sNativePrice->getPriceVatIncluded() : null,
			$shipping,
			$payment,
			$price !== null ? $price->getPriceVatExcluded() : null,
			$priceVatIncluded,
			$priceNative !== null ? $priceNative->getPriceVatExcluded() : null,
			$priceNative !== null ? $priceNative->getPriceVatIncluded() : null,
			$items,
			$order->getCreatedAt(),
			$orderNumber,
			$order->getDiscountCode()['code'] ?? null,
			$orderInfo,
			$shippingId,
			$trackingLink,
			$clientPhone,
			$clientEmail,
			$phone,
		);
	}

	/** @param mixed[] $invoice */
	/*
	public static function fromFapiOrder(array $invoice): self
	{
		$currencySymbol = MwsCurrencyEnum::getSymbol($invoice['currency']);
		$customerArr = $invoice['invoice']['customer'];
		$addressArr = $customerArr['address'] ?? null;
		$shippingAddressArr = $customerArr['shipping_address'] ?? null;

		$invoicePerson = isset($customerArr['first_name'], $customerArr['last_name']) ? new MwsPerson($customerArr['first_name'], $customerArr['last_name']) : null;
		$shippingPerson = isset($shippingAddressArr['name'], $shippingAddressArr['surname']) ? new MwsPerson($shippingAddressArr['name'], $shippingAddressArr['surname']) : null;
		$company = isset($customerArr['company']) ? new MwsCompany($customerArr['company'], $customerArr['ic'] ?? null, $customerArr['dic'] ?? null) : null;
		$address = $addressArr !== null ? new MwsAddress($addressArr['country'] ?? null, $addressArr['city'] ?? null, $addressArr['zip'] ?? null, $addressArr['street'] ?? null) : null;
		$shippingAddress = $shippingAddressArr !== null ? new MwsAddress($shippingAddressArr['country'] ?? null, $shippingAddressArr['city'] ?? null, $shippingAddressArr['zip'] ?? null, $shippingAddressArr['street'] ?? null) : null;
		$invoiceContact = new MwsContact($invoice['email'], $customerArr['phone'] ?? null, $invoicePerson, $company, $address);
		$shippingContact = $shippingAddress !== null ? new MwsContact($invoiceContact->getEmail(), $shippingAddressArr['phone'] ?? $invoiceContact->getPhone(), $shippingPerson ?? $invoiceContact->getPerson(), null, $shippingAddress) : null;

		$priceExcludedVat = $invoice['invoice']['total'] - $invoice['invoice']['total_vat'];

		$priceVatIncluded = htmlPriceSimple($invoice['invoice']['total'], $currencySymbol);
		$bankAccountNumber = $invoice['invoice']['supplier']['bank_account'] ?? null;
		// TODO IBAN and SWIFT
		$variableSymbol = $invoice['invoice']['variable_symbol'];
		$paymentInfo = '<strong>Bankovní učet</strong>: ' . $bankAccountNumber . '<br><strong>Variabilní symbol</strong>: ' . $variableSymbol . '<br><strong>Částka</strong>: ' . $priceVatIncluded . '<br>';
		$paymentType = $invoice['invoice']['payment_type'] ?? null;
		$supplier = MWS()->getSupplierContact(); // Not from FAPI
		$items = $invoice['invoice']['items'];
		$discountCode = null;
		foreach ($items as $item) {
			if ($item['type'] === 'discount' && isset($item['code'])) {
				$discountCode = $item['code'];
				break;
			}
		}
		return new self(
			$invoice['currency'],
			'', // TODO
			$paymentType === MwsPayType::Wire ? $paymentInfo : null,
			$paymentInfo,
			(string)$variableSymbol,
			$bankAccountNumber,
			$invoice['invoice']['supplier']['email'] ?? null,
			$supplier !== null ? $supplier->getPhone() : null, // Not from FAPI
			$invoice['invoice']['notes'] ?? null,
			$shippingContact,
			$invoiceContact,
			null, // TODO
			null, // TODO
			$invoice['invoice']['shipping_method'] ?? null,
			null, // TODO
			null, // TODO
			MwsPayType::getCaption($paymentType),
			$priceExcludedVat,
			$invoice['invoice']['total'],
			$items,
			new \DateTimeImmutable($invoice['invoice']['created_on']),
			$invoice['invoice']['number'],
			$discountCode
		);
	} */

	private function formatOrderContentTable(): string
	{
		$hasVat = false;
		$tdStyle = 'border-bottom: 1px solid #dbdbdb; padding: 10px;';
		$thStyle = $tdStyle . ' font-weight: normal; text-align:left;';
		$tdStyleFooter = 'padding: 10px;';

		$rows = join('', array_map(function ($item) use (&$hasVat, $tdStyle): string {
			if ($item instanceof OrderItem) {
				$price = $item->getPrice($this->currency);
				$name = $item->getName();
				$count = $item->getCount();
			} elseif (is_array($item)) {
				$price = new MwsPrice($item['price'], $item['vat'], $this->currency, $item['including_vat']);
				$name = $item['name'];
				$count = (int) $item['count'];
			} else {
				return '';
			}

			$currencySymbol = MwsCurrencyEnum::getSymbol($price->getCurrency());
			$totalPriceVatIncluded = $price->getPriceVatIncluded() * $count;
			$totalPriceVatExcluded = $price->getPriceVatExcluded() * $count;
			if ($totalPriceVatExcluded !== $totalPriceVatIncluded) {
				$hasVat = true;
			}

			return '
<tr>
	<td style="' . $tdStyle . '">' . htmlspecialchars($name) . '</td>
	<td style="' . $tdStyle . ' text-align: right;">' . sprintf(
					_n('1 ks', '%s ks', $count, 'mwshop'),
					$count
				) . '</td>
	' . ($hasVat ? '<td style="' . $tdStyle . ' text-align: right;">' . htmlPriceSimple($totalPriceVatExcluded, $currencySymbol) . '</td>' : '') . '
	<td style="' . $tdStyle . ' text-align: right;">' . htmlPriceSimple($totalPriceVatIncluded, $currencySymbol) . '</td>
</tr>';
		}, $this->items));

		$columnCount = $hasVat ? 4 : 3;

		return '
<table style="border-spacing:0;">
<thead>
	<tr style="color: #757575; font-weight: normal;">
		<th style="' . $thStyle . '">' . __('Název položky', 'mwshop') . '</th>
		<th style="' . $thStyle . '">' . __('Množství', 'mwshop') . '</th>
	' . ($hasVat ? '<th style="' . $thStyle . '">Cena bez DPH</th>' : '') . '
		<th style="' . $thStyle . '">' . ($hasVat ? __('Cena s DPH') : __('Cena celkem', 'mwshop')) . '</th>
	</tr>
</thead>
<tbody>
	' . $rows . '
</tbody>
<tfooter>' .
	(
		$hasVat
			? '<tr style="text-align: right; color: #757575; font-size: 80%">
				<td style="' . $tdStyleFooter . '; padding-bottom: 0;" colspan="' . $columnCount . '">' . __('Celkem bez DPH', 'mwshop') . ': <strong>' . htmlPriceSimple($this->priceNovat, $this->currencySymbol) . '</strong></td>
			</tr>'
			: ''
	) .
	'<tr style="text-align: right">
		<td style="' . $tdStyleFooter . '" colspan="' . $columnCount . '">' . __('Celkem', 'mwshop') . ($hasVat ? ' ' . __('s DPH', 'mwshop') : '') . ': <strong>' . htmlPriceSimple($this->price, $this->currencySymbol) . '</strong></td>
	</tr>
</tfooter>
</table>
			';
	}

	private function getOrderContent()
	{
		$arr = [];

		foreach ($this->items as $item) {
			$arr[] = is_array($item) ? $item : $item->toArray();
		}

		return Json::encode($arr);
	}

}
