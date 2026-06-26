<?php declare(strict_types=1);

namespace Mioweb\Shop;

use MwsSession;

class FormSessionCart extends FormCart
{

	/** Is used for storing form url in order source */
	private ?MwsSessionSection $_permanentSession = null;

	public function clear(bool $reload = true): void
	{
		$this->getSession()->destroy();
		$this->getPermanentSession()->destroy();

		if ($reload) {
			$this->loadFromSession(true);
		}
	}

	protected function getSession(): MwsSessionSection
	{
		if ($this->_session === null) {
			$session = MwsSession::getInstance()->getSection('form-' . $this->getForm()->getId());
			$session->setExpiration('1 DAY');
			$this->_session = $session;
		}

		return $this->_session;
	}

	private function getPermanentSession(): MwsSessionSection
	{
		if ($this->_permanentSession === null) {
			$session = MwsSession::getInstance()->getSection('form-' . $this->getForm()->getId() . '-permanent');
			$session->setExpiration('14 DAYS');
			$this->_permanentSession = $session;
		}

		return $this->_permanentSession;
	}

	protected function loadFromSession(bool $reload = false): void
	{
		if ($this->_loaded && !$reload) {
			return;
		}

		$this->formProcessed = (bool) ($this->getSession()->formProcessed ?? false);
		$this->securityCode = $this->getSession()->securityCode ?? null;
		$this->processedUpsellIds = $this->getSession()->processedUpsellIds ?? [];

		parent::loadFromSession($reload);

		if ($this->getSource() === null) {
			$this->_source = $this->getPermanentSession()->source ?? null;
		}
	}

	public function save(): void
	{
		if ($this->_loaded) {
			parent::save();

			$this->getPermanentSession()->source = $this->_source;
		}
	}

}
