<?php declare(strict_types=1);

namespace Mioweb\Mailing\Exceptions;

class TooManyRecipientsException extends \Exception
{

	private int $limit;

	public function __construct(int $limit, string $message = '', int $code = 0, ?\Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->limit = $limit;
	}

	public function getLimit(): int
	{
		return $this->limit;
	}

}
