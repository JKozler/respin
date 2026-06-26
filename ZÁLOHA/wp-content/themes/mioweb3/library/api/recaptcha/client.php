<?php declare(strict_types=1);

class mwAPIConnectItemClient_recaptcha extends mwAPIConnectItemClient
{

	public function __construct(mwAPIConnectItem $mwAPIConnectItem)
	{
		parent::__construct($mwAPIConnectItem);
	}

	public function checkSavedSetting(&$tosave): bool
	{
		$siteKey = $tosave['site_key'] ?? null;

		if (!(bool) $siteKey) {
			mwMessages()->error(__('Musíte vyplnit Site key.', 'cms'));

			return false;
		}

		$secretKey = $tosave['secret_key'] ?? null;

		if (!(bool) $secretKey) {
			mwMessages()->error(__('Musíte vyplnit Secret key.', 'cms'));

			return false;
		}

		return true;
	}

}
