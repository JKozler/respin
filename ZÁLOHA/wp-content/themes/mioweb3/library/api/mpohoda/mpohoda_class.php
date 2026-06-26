<?php declare(strict_types=1);

use Mioweb\MPohodaClient\MPohodaClient;
use Mioweb\MPohodaClient\MPohodaClientFactory;

function MWMPohoda(): MWMPohoda
{
	return MWMPohoda::instance();
}

class MWMPohoda
{

	private static ?self $instance = null;

	private mwAPIConnectItem $mPohodaApi;

	private ?MPohodaClient $client = null;

	private function __construct()
	{
		// TODO uncomment this when mPOHODA is released
//		$this->mPohodaApi = mwApiConnect()->getApi('mpohoda');

		if ($this->isActive()) {
			$option = $this->mPohodaApi->getOption();

			$apiKey = htmlspecialchars($option['api_key'] ?? '', ENT_QUOTES);

			$betaApiUrl = 'https://betaapi.mpohoda.cz/v1'; // TODO Remove this when mPOHODA API is released
			$clientFactory = new MPohodaClientFactory($betaApiUrl);
			$this->client = $clientFactory->createMPohodaClient($apiKey);
		}
	}

	public function isActive(): bool
	{
		return false; // TODO remove this when mPOHODA is released

		return $this->mPohodaApi->isConnected();
	}

	public function getClient(): MPohodaClient
	{
		if ($this->client === null) {
			throw new \Exception('mPOHODA is not connected.');
		}

		return $this->client;
	}

	public static function instance(): self
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
