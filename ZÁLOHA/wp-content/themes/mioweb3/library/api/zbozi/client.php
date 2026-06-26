<?php

class mwAPIConnectItemClient_zbozi extends mwAPIConnectItemClient
{

	function checkSavedSetting(&$tosave): bool
	{
		$shopId = $tosave['shop_id'] ?? '';
		$privateKey = $tosave['private_key'] ?? '';

		if ($shopId && $privateKey) {
			$tosave['shop_id'] = trim($tosave['shop_id']);
			$tosave['private_key'] = trim($tosave['private_key']);

			return true;
		}

		mwMessages()->error(__('Pro aktivování standartního měření konverzí na Zboží.cz, je potřeba zadat ID provozovny i tajný klíč', 'cms'));

		return false;
	}

	public function isDebugMode(): bool
	{
		$option = $this->_mwAPIConnectItem->getOption();

		return ($this->_mwAPIConnectItem->isConnected() && isset($option['sandbox']));
	}

}
