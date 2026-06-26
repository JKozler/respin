<?php declare(strict_types=1);

use Mioweb\Api\Recaptcha\ReCaptchaResponse;
use Mioweb\Api\Recaptcha\ReCaptchaValidator;

function MWRecaptcha(): MwRecaptcha
{
	return MwRecaptcha::instance();
}

class MwRecaptcha
{

	private static ?self $instance = null;

	private mwAPIConnectItem $recaptchaApi;

	private string $siteKey;

	private string $secretKey;

	private ?ReCaptchaValidator $validator = null;

	private function __construct()
	{
		$this->recaptchaApi = mwApiConnect()->getApi('recaptcha');

		if ($this->isActive()) {
			$option = $this->recaptchaApi->getOption();

			$this->siteKey = htmlspecialchars($option['site_key'] ?? '', ENT_QUOTES);
			$this->secretKey = htmlspecialchars($option['secret_key'] ?? '', ENT_QUOTES);

			$this->validator = new ReCaptchaValidator($this->secretKey);
		}
	}

	public function isActive(): bool
	{
		return $this->recaptchaApi->isConnected();
	}

	public function getCode(): string
	{
		// TODO GDPR
		$ad_storage = MwCookies()->isPermitted('marketing') ? 'granted' : 'denied';
		$analytics_storage = MwCookies()->isPermitted('analytics') ? 'granted' : 'denied';

		$recaptchaV3Content = file_get_contents(__DIR__ . '/recaptchaV3.js');
		$code = '<script src="https://www.recaptcha.net/recaptcha/api.js?render=' . $this->siteKey . '"></script>';
		$code .= '<script type="application/javascript">'
			. 'const MWReCaptchaSiteKey = "' . $this->siteKey . '";'
			. $recaptchaV3Content
			. '</script>';

		return $code;
	}

	public function validate(?string $token, float $scoreThreshold = ReCaptchaValidator::DEFAULT_SCORE_THRESHOLD): bool
	{
		$response = $this->validator->validate($token);

		return $response !== null && $response->isSuccess() && $response->getScore() >= $scoreThreshold;
	}

	public function getPrivacyConsentText(): string
	{
		$content = '<div class="mw_field_recaptcha_accept">';
		$content .= sprintf(
			__('Tento formulář je chráněn službou reCAPTCHA a platí <a href="%s" %s>Zásady ochrany osobních údajů</a> a <a href="%s" %s>Smluvní podmínky</a> společnosti Google.', 'cms'),
			'https://policies.google.com/privacy',
			'target="_blank" rel="nofollow"',
			'https://policies.google.com/terms',
			'target="_blank" rel="nofollow"',
		);
		$content .= '</div>';

		return $content;
	}

	public static function instance(): self
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
