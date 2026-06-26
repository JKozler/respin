<?php declare(strict_types=1);

use Nette\Utils\Json;

class mwAPIConnectItemClient_gtm extends mwAPIConnectItemClient
{

	public function __construct(mwAPIConnectItem $mwAPIConnectItem)
	{
		parent::__construct($mwAPIConnectItem);
	}

	public function checkSavedSetting(&$tosave): bool
	{
		$gtmId = $tosave['container_id'] ?? null;

		if (!(bool) $gtmId) {
			mwMessages()->error(__('Musíte vyplnit ID kontejneru.', 'cms'));

			return false;
		}

		if (!preg_match('/^GTM-[A-Z0-9]{1,9}$/', $gtmId)) {
			mwMessages()->error(__('ID konteineru není ve správném tvaru. Prosím zkontrolujte jej.', 'cms'));

			return false;
		}

		return true;
	}

}
