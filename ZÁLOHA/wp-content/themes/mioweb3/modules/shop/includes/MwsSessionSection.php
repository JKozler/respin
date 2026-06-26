<?php declare(strict_types=1);

namespace Mioweb\Shop;

use ArrayAccess;

class MwsSessionSection implements ArrayAccess
{

	const EXPIRATION_KEY = '@@expiration@@';
	const EXPIRATION_SETTING_KEY = '@@expiration_setting@@';

	private string $identifier;

	public function __construct(string $identifier)
	{
		$this->identifier = $identifier;

		$this->init();

		if ($this->isExpired()) {
			$this->destroy();
		}

		$this->refreshExpiration();
	}

	public function offsetSet($offset, $value)
	{
		$_SESSION[$this->identifier][$offset] = $value;
	}

	/** @param $value mixed Your data */
	public function __set($name, $value): void
	{
		$this->offsetSet($name, $value);
	}

	public function offsetGet($offset)
	{
		return $_SESSION[$this->identifier][$offset] ?? null;
	}

	/** @return mixed|null Data stored in session */
	public function __get(string $name)
	{
		return $this->offsetGet($name);
	}

	public function offsetExists($offset): bool
	{
		return isset($_SESSION[$this->identifier][$offset]);
	}

	public function __isset($name): bool
	{
		return $this->offsetExists($name);
	}

	public function offsetUnset($offset)
	{
		unset($_SESSION[$this->identifier][$offset]);
	}

	public function __unset($name): void
	{
		$this->offsetUnset($name);
	}

	public function destroy(): void
	{
		unset($_SESSION[$this->identifier]);
	}

	/** @param string|int|null $time */
	public function setExpiration($time): self
	{
		$this->init();

		$currentTime = time();

		if ($time) {
			$time = \Nette\Utils\DateTime::from($time)->format('U');
			$max = (int) ini_get('session.gc_maxlifetime');

			if (
				$max !== 0 // 0 - unlimited in memcache handler
				&& ($time - $currentTime > $max + 3) // 3 - bulgarian constant
			) {
				trigger_error("The expiration time is greater than the session expiration $max seconds");
			}
		}

		$_SESSION[$this->identifier][self::EXPIRATION_KEY] = $time ?: null;
		$_SESSION[$this->identifier][self::EXPIRATION_SETTING_KEY] = $time !== null ? $time - $currentTime : null;

		return $this;
	}

	public function refreshExpiration(): void
	{
		$this->setExpiration($_SESSION[$this->identifier][self::EXPIRATION_SETTING_KEY] ?? null);
	}

	public function isExpired(): bool
	{
		$expiration = $_SESSION[$this->identifier][self::EXPIRATION_KEY] ?? null;

		if ($expiration === null) {
			return false;
		}

		$currentTime = time();

		return $currentTime > $expiration;
	}

	private function init(): void
	{
		if (!isset($_SESSION[$this->identifier])) {
			$_SESSION[$this->identifier] = [];
		}
	}

}
