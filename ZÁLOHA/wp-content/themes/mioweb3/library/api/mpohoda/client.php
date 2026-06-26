<?php declare(strict_types=1);

use Mioweb\MPohodaClient\AuthorizationException;
use Mioweb\MPohodaClient\IMPohodaClient;
use Mioweb\MPohodaClient\MPohodaClientFactory;

class mwAPIConnectItemClient_mpohoda extends mwAPIConnectItemClient
{

	public function __construct(mwAPIConnectItem $mwAPIConnectItem)
	{
		parent::__construct($mwAPIConnectItem);
	}

	public function checkSavedSetting(&$tosave): bool
	{
		$apiKey = $tosave['api_key'] ?? null;

		if (!(bool) $apiKey) {
			mwMessages()->error(__('Musíte vyplnit API klíč.', 'cms'));

			return false;
		}

		$client = $this->createClient($apiKey);

		try {
			$client->getVatRates()->findAll([
				'PageNumber' => 1,
				'PageSize' => 1,
			]);
		} catch (AuthorizationException $e) {
			mwMessages()->error(__('Chyba autentizace, zadaný API klíč je pravděpodobně neplatný.', 'cms'));

			return false;
		}

		return true;
	}

	private function createClient(string $apiKey): IMPohodaClient
	{
		$betaApiUrl = 'https://betaapi.mpohoda.cz/v1'; // TODO #3401 Remove this when stable version of mPOHODA API is released

		$clientFactory = new MPohodaClientFactory($betaApiUrl);

		return $clientFactory->createMPohodaClient($apiKey);
	}

}
