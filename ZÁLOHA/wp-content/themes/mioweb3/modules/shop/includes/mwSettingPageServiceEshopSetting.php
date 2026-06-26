<?php declare(strict_types=1);

class mwSettingPageService_eshopSetting extends mwSettingPageService
{

	public function saveSetting($tosave)
	{
		parent::saveSetting($tosave);

		if (isset($tosave['paygate']) && $tosave['paygate'] === 'fapi') {
			$phone = isset($tosave['invoice_contact']['show_phone']) && $tosave['invoice_contact']['show_phone'] === '1';
			$email = isset($tosave['invoice_contact']['show_email']) && $tosave['invoice_contact']['show_email'] === '1';
			/** @var MwsGatewayImpl_Fapi $gw */
			$gw = MWS()->gateways()->getById('fapi')->sharedInstance();

			$gw->updateForm(['print_email_on_invoice' => $email, 'print_phone_on_invoice' => $phone]);
		}
	}
}
