<?php

class mwAPIConnectItemClient_packeta extends mwAPIConnectItemClient
{

	function checkSavedSetting(&$connection): bool
	{
		$newPassword = $connection['api_pas'] ?? '';
		$newKey = $connection['api_key'] ?? '';
		$newSender = $connection['sender'] ?? '';

		$newStatus = false;

		if ($newPassword && $newKey && $newSender) {
			$connection['api_pas'] = $newPassword = trim($newPassword);
			$connection['api_key'] = $newKey = trim($newKey);
			$connection['sender'] = $newSender = trim($newSender);

			if ($newKey !== substr($newPassword, 0, 16)) {
				mwMessages()->error(__('Nesprávný API klíč. API klíč musí být roven prvním 16 znakům API hesla.', 'cms_ve'));
			} else {
				//For oAuth and similar method first authorization requires a special care.
				$client = new SoapClient(PACKETA_SOAP_URL);
				$attributes = [
					'number' => '1',
					'name' => 'John',
					'surname' => 'Doe',
					'email' => 'test@test.cz',
					'phone' => '123456789',
					'addressId' => 106,
					'value' => 1000,
					'eshop' => $newSender,
					'street' => 'Street',
					'houseNumber' => '10',
					'zip' => '11111',
					'city' => 'City',
					'weight' => 1.5,
				];

				try {
					$packet = $client->packetAttributesValid($newPassword, $attributes);
					$newStatus = true;
				} catch (SoapFault $e) {
					$error = MwsPacketa::getError($e);
					mwMessages()->error($error['errorMessage'], 'packeta_log');
				}
			}
		} else {
			mwMessages()->error(__('Je potřeba vyplnit všechny údaje.', 'cms_ve'));
		}

		return $newStatus;
	}

}
