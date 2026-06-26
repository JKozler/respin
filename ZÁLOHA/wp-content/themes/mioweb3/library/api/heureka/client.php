<?php

class mwAPIConnectItemClient_heureka extends mwAPIConnectItemClient
{

	function checkSavedSetting(&$tosave): bool
	{
		$secretKey = $tosave['secret_key'] ?? '';
		$apiKey = $tosave['api_key'] ?? '';

		if (!$apiKey && !$secretKey) {
			mwMessages()->error(__('Pro aktivaci služby "Ověřeno zákazníky" nebo měření konverzí musíte zadat tajný nebo veřejný klíč', 'cms'));

			return false;
		}

		$tosave['secret_key'] = trim($secretKey);
		$tosave['api_key'] = trim($apiKey);

		return true;
	}

}
