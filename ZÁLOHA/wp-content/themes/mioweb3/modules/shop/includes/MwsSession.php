<?php declare(strict_types=1);

use Mioweb\Shop\MwsSessionSection;

class MwsSession
{
	const SESSION_STARTED = true;
	const SESSION_NOT_STARTED = false;
	const DEFAULT_SECTION = 'default';

	private bool $sessionActive = self::SESSION_NOT_STARTED;

	private static ?self $instance;

	/** @var MwsSessionSection[] */
	private array $sections = [];

	private function __construct()
	{
		$this->sections[] = new MwsSessionSection(self::DEFAULT_SECTION);
	}

	/**
	 * Returns THE instance of 'Session'.
	 * The session is automatically initialized if it wasn't.
	 *
	 * @return MwsSession
	 **/
	public static function getInstance(): self
	{
		if (!isset(self::$instance)) {
			self::$instance = new self();
			//          add_action( 'shutdown', array(self::$instance, 'saveSession' ), 20 );
		}

		self::$instance->startSession();

		return self::$instance;
	}

	/**
	 * (Re)starts the session.
	 *
	 * @return bool TRUE if the session has been initialized, else FALSE.
	 */
	public function startSession(): bool
	{
		$this->sessionActive = isset($_SESSION);
		if (!$this->sessionActive) {
			$this->sessionActive = session_start();
		}

		return $this->sessionActive;
	}

	public function getSection(string $sectionName): MwsSessionSection
	{
		if (!isset($this->sections[$sectionName])) {
			$this->sections[$sectionName] = new MwsSessionSection($sectionName);
		}

		return $this->sections[$sectionName];
	}

	public function __set(string $name, $value): void
	{
		$this->getSection(self::DEFAULT_SECTION)[$name] = $value;
	}

	/** @return mixed|null Data stored in session. */
	public function __get(string $name)
	{
		return $this->getSection(self::DEFAULT_SECTION)[$name] ?? null;
	}


	public function __isset(string $name): bool
	{
		return isset($this->getSection(self::DEFAULT_SECTION)[$name]);
	}


	public function __unset(string $name): void
	{
		unset($this->getSection(self::DEFAULT_SECTION)[$name]);
	}


	/** @return bool true is session has been really destroyed, else false */
	public function destroy(): bool
	{
		if ($this->sessionActive) {
			mwshoplog(__METHOD__, MWLL_DEBUG, 'session');
			$this->sessionActive = !session_destroy();
			unset($_SESSION);

			return !$this->sessionActive;
		}

		return false;
	}
}
