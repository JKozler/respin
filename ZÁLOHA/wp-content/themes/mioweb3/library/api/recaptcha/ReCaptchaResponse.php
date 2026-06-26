<?php declare(strict_types=1);

namespace Mioweb\Api\Recaptcha;

final class ReCaptchaResponse
{

	private bool $success;

	private float $score;

	/** @var string[]|string|null */
	private $error;

	/** @param string[]|string|null $error */
	public function __construct(bool $success, float $score, $error = null)
	{
		$this->success = $success;
		$this->score = $score;
		$this->error = $error;
	}

	public function isSuccess(): bool
	{
		return $this->success;
	}

	public function getScore(): float
	{
		return $this->score;
	}

	/** @return string[]|string|null */
	public function getError()
	{
		return $this->error;
	}

	public function __toString(): string
	{
		return (string) $this->isSuccess();
	}

}
