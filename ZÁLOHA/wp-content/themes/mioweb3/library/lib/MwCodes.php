<?php

function MwCodes()
{
	return MwCodes::instance();
}

class MwCodes
{

	protected static $_instance = null;

	/** @var MwCode[] */
	private $codes = [];

	/** @var MwCode[] */
	private $conversionCodes = [];

	/** @var string */
	private $css;

	function __construct()
	{
	}

	public function addCode($code, $position = 'footer', $type = 'necessary')
	{
		$this->codes[] = new MwCode([
			'position' => $position,
			'type' => $type,
			'code' => $code,
		]);
	}
	public function addConversionCode($code)
	{
		$this->conversionCodes[] = new MwCode([
			'position' => 'footer',
			'code' => $code,
		]);
	}

	public function addCodesFromOption(string $optionName, bool $conversions = true): void
	{
		$data = MWDB()->getOption($optionName);
		$this->addCodesFromData($data);
		if ($conversions) {
			$this->addConversionCodesFromData($data);
		}
	}

	public function addCodesFromMeta(string $metaName, WP_Post $post): void
	{
		$data = MWDB()->getPostMeta($post->ID, $metaName, true);
		$this->addCodesFromData($data);
		if ($post->post_type === 'page' || $post->post_type === 'post') {
			$this->addConversionCodesFromData($data);
		}
	}

	public function addCodesFromData($data): void
	{
		if (isset($data['codes']) && is_array($data['codes'])) {
			foreach ($data['codes'] as $code) {
				$this->codes[] = new MwCode($code);
			}
		}

		if (isset($data['css'])) {
			$this->css .= $data['css'];
		}
	}
	public function addConversionCodesFromData($data): void
	{
		if (isset($data['conversion_codes']) && is_array($data['conversion_codes'])) {
			foreach ($data['conversion_codes'] as $code) {
				$this->conversionCodes[] = new MwCode($code);
			}
		}
	}

	public function printHeaderCodes(): void
	{
		$isShopActive = MW()->is_module_active('shop');
		//Facebook Conversions API
		if (!MW()->edit_mode && MWFBC()->isActive() && (!$isShopActive || !is_singular(MWS_PRODUCT_SLUG))) {
			MWFBC()->sendEvent('ViewContent', []);
		}

		$codes = '';

		// Google Analytics 4
		if (MWGA()->isActive()) {
			$codes .= MWGA()->getCode();
		}

		// Google Tag Manager
		if (MWGTM()->isActive()) {
			$codes .= MWGTM()->getHeaderCode();
		}

		// header codes
		$codes .= $this->printCodes('header');

		echo $codes;
	}

	public function printBodyCodes(): void
	{
		$codes = '';

		// Google Tag Manager
		if (MWGTM()->isActive()) {
			$codes .= MWGTM()->getBodyCode();
		}

		// body codes
		$codes .= $this->printCodes('body');

		echo $codes;
	}

	public function printFooterCodes(): void
	{
		$codes = '';

		// Google reCAPTCHA v3
		if (MWRecaptcha()->isActive()) {
			$codes .= MWRecaptcha()->getCode();
		}

		$codes .= $this->printCodes('footer');

		echo $codes;
	}

	public function printCodes(string $position): string
	{
		$codes = '';
		foreach ($this->codes as $code) {
			if ($code->getPosition() === $position && $code->isEnabled()) {
				$codes .= $code->getCode();
			}
		}

		return $codes;
	}

	public function printCss(): void
	{
		if ($this->css) {
			echo '<style>';
			echo stripslashes($this->css);
			echo '</style>';
		}
	}

	public function printConversionCodes(): void
	{
		$codes = '';
		foreach ($this->conversionCodes as $code) {
			if ($code->isEnabled()) {
				$codes .= $code->getCode(true);
			}
		}
		echo self::processConversionCode($codes);
	}

	public static function processConversionCode(string $code): string
	{
		// 1. Variables from eshop or FAPI
		$variables = MwVariables::getVariables();
		$code = MwVariables::replaceVariables($code, $variables);

		$replacements = [];

		// 2. Affilbox (back compatibility)
		$replacements = [
			'CENA' => isset($variables['CENA_BEZ_DPH']) ? number_format(round($variables['CENA_BEZ_DPH'], 2), 2, '.', '') : '',
			'OZNACENI_MENY' => $variables['MENA'] ?? 'CZK',
			'ID_TRANSAKCE' => $variables['VARIABLE_SYMBOL'] ?? '',
		];
		if (isset($_GET['email'])) {
			$replacements['ID_TRANSAKCE'] = esc_js($_GET['email']);
		} elseif (isset($_GET['vs'])) {
			$replacements['ID_TRANSAKCE'] = esc_js($_GET['vs']);
		}
		if (isset($_GET['cena'])) {
			$replacements['CENA'] = number_format((float) esc_js($_GET['cena']));
		}
		if (isset($_GET['mena'])) {
			$replacements['OZNACENI_MENY'] = htmlspecialchars($_GET['mena']);
		}
		if (strstr($code, 'ID_TRANSAKCE') && !$replacements['ID_TRANSAKCE']) {
			return '';
		}

		// 3. URL parameters
		preg_match_all('/(%%)([a-zA-Z_]+)(%%)/', $code, $matches);
		foreach ($matches[2] as $val) {
			if (isset($_GET[$val])) {
				$replacements['%%' . $val . '%%'] = esc_js($_GET[$val]);
			}
		}

		$code = strtr($code, $replacements);

		return stripslashes($code);
	}

	public static function convertCodesFromOldData($oldCodes, $headerCodesId = null, $bodyCodesId = null, $footerCodesId = null, $cssId = null, $conversionCodeId = null): array
	{
		$newCodes = [
			'codes' => [],
		];
		if ($cssId && isset($oldCodes[$cssId])) {
			$newCodes['css'] = $oldCodes[$cssId];
		}
		if ($headerCodesId && isset($oldCodes[$headerCodesId]) && $oldCodes[$headerCodesId]) {
			$codeList = preg_split('/\r\n\r\n|\r\r|\n\n/', $oldCodes[$headerCodesId]);
			foreach ($codeList as $code) {
				if ($code) {
					$newCodes['codes'][] = [
						'title' => '',
						'position' => 'header',
						'type' => 'necessary',
						'code' => $code,
					];
				}
			}
		}

		if ($bodyCodesId && isset($oldCodes[$bodyCodesId]) && $oldCodes[$bodyCodesId]) {
			$codeList = preg_split('/\r\n\r\n|\r\r|\n\n/', $oldCodes[$bodyCodesId]);
			foreach ($codeList as $code) {
				if ($code) {
					$newCodes['codes'][] = [
						'title' => '',
						'position' => 'body',
						'type' => 'necessary',
						'code' => $code,
					];
				}
			}
		}

		if ($footerCodesId && isset($oldCodes[$footerCodesId]) && $oldCodes[$footerCodesId]) {
			$codeList = preg_split('/\r\n\r\n|\r\r|\n\n/', $oldCodes[$footerCodesId]);
			foreach ($codeList as $code) {
				if ($code) {
					$newCodes['codes'][] = [
						'title' => '',
						'position' => 'footer',
						'type' => 'necessary',
						'code' => $code,
					];
				}
			}
		}

		if ($conversionCodeId && isset($oldCodes[$conversionCodeId]) && $oldCodes[$conversionCodeId]) {
			$newCodes['conversion_codes'] = [];
			$codeList = preg_split('/\r\n\r\n|\r\r|\n\n/', $oldCodes[$conversionCodeId]);
			foreach ($codeList as $code) {
				if ($code) {
					$newCodes['conversion_codes'][] = [
						'title' => '',
						'code' => $code,
					];
				}
			}
		}

		return $newCodes;
	}

	/** @return MwCodes Returns singleton instance of MwCookieManagement. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

}
