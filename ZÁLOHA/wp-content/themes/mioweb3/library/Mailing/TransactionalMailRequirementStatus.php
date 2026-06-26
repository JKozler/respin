<?php declare(strict_types=1);

namespace Mioweb\Mailing;

use Exception;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\ExternalEmailRequirementsException;
use Nette\Utils\Json;
use function get_class;
use function is_subclass_of;

class TransactionalMailRequirementStatus implements \Serializable
{

	public const TRANSIENT = 'mw_transactional_emails_requirements';

	private bool $available;

	private ?ExternalEmailRequirementsException $exception;

	public function __construct(bool $available, ?ExternalEmailRequirementsException $exception = null)
	{
		$this->available = $available;
		$this->exception = $exception;
	}

	/** @param mixed[] $data */
	public static function fromTransient(array $data): self
	{
		$self = new self((bool) $data['available']);
		$self->initFromTransient($data);

		return $self;
	}

	public function isAvailable(): bool
	{
		return $this->available;
	}

	public function getException(): ?ExternalEmailRequirementsException
	{
		return $this->exception;
	}

	/** @param mixed[] $data */
	public function initFromTransient(array $data): void
	{
		if ($data['exception'] !== null) {
			/** @var class-string<ExternalEmailRequirementsException> $exceptionClass */
			$exceptionClass = $data['exception']['class'];
			$exception = new $exceptionClass($data['exception']['message']);
			\assert($exception instanceof ExternalEmailRequirementsException);
		} else {
			$exception = null;
		}

		$this->available = (bool) $data['available'];
		$this->exception = $exception;
	}

	/** @return mixed[] */
	public function toArray(): array
	{
		$exception = $this->getException();
		$exceptionArr = $exception !== null ? [
			'class' => get_class($exception),
			'message' => $exception->getMessage(),
		] : null;

		return [
			'available' => $this->isAvailable(),
			'exception' => $exceptionArr,
		];
	}

	public function serialize(): string
	{
		return serialize($this->__serialize());
	}

	/** @return mixed[] */
	public function __serialize(): array
	{
		return $this->toArray();
	}

	public function unserialize(string $data)
	{
		$this->__unserialize(unserialize($data));
	}

	public function __unserialize(array $data): void
	{
		$this->initFromTransient($data);
	}

}
