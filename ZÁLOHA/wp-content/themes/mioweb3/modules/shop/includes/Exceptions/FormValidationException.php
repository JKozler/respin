<?php declare(strict_types=1);

class FormValidationException extends MwsException
{

	/** @var string[] */
	private array $errors;

	public function __construct(array $errors = [], $message = '', $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->errors = $errors;
	}

	/** @return string[] */
	public function getErrors(): array
	{
		return $this->errors;
	}

}
