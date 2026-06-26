<?php declare(strict_types=1);

use FacebookAds\Api;
use FacebookAds\Http\Exception\AuthorizationException;
use FacebookAds\Http\RequestInterface;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\DeliveryCategory;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

class mwAPIConnectItemClient_fbconversions extends mwAPIConnectItemClient
{

	public function checkSavedSetting(&$tosave): bool
	{
		$pixel_id = $tosave['pixel_id'] ?? '';
		$access_token = $tosave['access_token'] ?? '';
		$test_id = $tosave['test_id'] ?? '';


		if ($pixel_id && $access_token) {
			$tosave['pixel_id'] = $pixel_id = trim($pixel_id);
			$tosave['access_token'] = $access_token = trim($access_token);
			$tosave['test_id'] = $test_id = trim($test_id);

			if (isset($tosave['debug_mode']) && !$test_id) {
				mwMessages()->error(__('Pro testovací režim je třeba vyplnit Test ID.', 'cms_ve'));

				return false;
			}



			$api = Api::init(null, null, $access_token);

			try {
				$api->call(
					'/me',
					RequestInterface::METHOD_GET,
					['access_token' => $access_token]
					);
			} catch (AuthorizationException $e) {
				mwMessages()->error(__('Špatně zadaný Access token.', 'cms_ve'));

				return false;
			}

			return true;
		}

		mwMessages()->error(__('Je potřeba vyplnit potřebné údaje (Pixel ID a Access token).', 'cms_ve'));

		return false;
	}

	public function isDebugMode(): bool
	{
		$option = $this->_mwAPIConnectItem->getOption();

		return ($this->_mwAPIConnectItem->isConnected() && isset($option['debug_mode']));
	}

}
