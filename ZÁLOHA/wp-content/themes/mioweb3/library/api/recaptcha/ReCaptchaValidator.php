<?php declare(strict_types=1);

namespace Mioweb\Api\Recaptcha;

/**
 * Based on https://github.com/contributte/reCAPTCHA/blob/master/src/ReCaptchaProvider.php
 */
class ReCaptchaValidator
{

	public const FORM_TOKEN_PARAMETER = 'g-recaptcha-response';
	public const VERIFICATION_URL = 'https://www.recaptcha.net/recaptcha/api/siteverify';
	public const DEFAULT_SCORE_THRESHOLD = 0.5;

	private string $secretKey;

	public function __construct(string $secretKey)
	{
		$this->secretKey = $secretKey;
	}

	public function validate(?string $token): ?ReCaptchaResponse
	{
		$apiResponse = $this->makeRequest($token);

		if (empty($apiResponse)) {
			return null;
		}

		$answer = json_decode($apiResponse, true);

		return new ReCaptchaResponse($answer['success'] === true, $answer['score'] ?? 0.0, $answer['error-codes'] ?? null);
	}

	private function makeRequest(?string $response, ?string $remoteIp = null): ?string
	{
		if (empty($response)) {
			return null;
		}

		$params = [
			'secret' => $this->secretKey,
			'response' => $response,
		];

		if ($remoteIp !== null) {
			$params['remoteip'] = $remoteIp;
		}

		$url = $this->buildUrl($params);
		$result = @file_get_contents($url);

		return $result ?: null;
	}

	private function buildUrl(array $parameters = []): string
	{
		return self::VERIFICATION_URL . '?' . http_build_query($parameters);
	}

}
