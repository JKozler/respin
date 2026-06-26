<?php declare(strict_types=1);

class mwAPIConnectItemClient_google_analytics extends mwAPIConnectItemClient
{

	public function __construct(mwAPIConnectItem $mwAPIConnectItem)
	{
		parent::__construct($mwAPIConnectItem);
	}

	public function checkSavedSetting(&$tosave): bool
	{
		$measurementId = $tosave['measurement_id'] ?? '';

		if ($measurementId) {
			$tosave['measurement_id'] = trim($measurementId);

			return true;
		}

		mwMessages()->error(__('Musíte vyplnit ID měření.', 'cms'));

		return false;
	}

	public function isDebugMode(): bool
	{
		$option = $this->_mwAPIConnectItem->getOption();

		return ($this->_mwAPIConnectItem->isConnected() && isset($option['debug_mode']));
	}

}
