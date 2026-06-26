<?php

class mwAPIConnectItemClient_google_maps extends mwAPIConnectItemClient
{

	function checkSavedSetting(&$tosave): bool
	{
		$apiKey = $tosave['api_key'] ?? '';

		if ($apiKey) {
			$tosave['api_key'] = trim($apiKey);

			return true;
		}

		mwMessages()->error(__('Musíte vyplnit API klíč.', 'cms'));

		return false;
	}

}
