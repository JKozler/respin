<?php declare(strict_types=1);

namespace Mioweb\Shop;

use DateTimeImmutable;

/** Api documentation: https://qr-platba.cz/pro-vyvojare/restful-api/ */
class QrPlatba
{

	const API_PATH = 'https://api.paylibo.com/paylibo/';
	const RESOURCE_QR = 'generator/czech/image';
	const SUCCESS_REQUEST = 200;
	const QR_CODE = 'QR';
	const SMALL_SIZE_QR = 8;
	const BANK_CZ_CODES = [
		'komercniBank' => '0100',
		'CSOB' => '0300',
		'monetaBank' => '0600',
		'cnb' => '0710',
		'ceskaSporitelna' => '0800',
		'fioBank' => '2010',
		'mufgBank' => '2020',
		'akcenta' => '2030',
		'citifin' => '2060',
		'moravskyPenezniUstav' => '2070',
		'hypotecniBank' => '2100',
		'penezniDum' => '2200',
		'artesa' => '2220',
		'axaBank' => '2230',
		'postBank' => '2240',
		'creditasBank' => '2250',
		'anoSporitelniDruzstvo' => '2260',
		'zunoBank' => '2310',
		'citiBank' => '2600',
		'unitCreditBank' => '2700',
		'airBank' => '3030',
		'BNP' => '3050',
		'PKO' => '3060',
		'ingBank' => '3500',
		'expoBank' => '4000',
		'ceskoMorovaskaBank' => '4300',
		'raiffeisenBank' => '5500',
		'J&TBank' => '5800',
		'PPFBank' => '6000',
		'equaBank' => '6100',
		'commerzBank' => '6200',
		'mBank' => '6210',
		'BNP-SA/NV' => '6300',
		'VUB' => '6700',
		'sberBank' => '6800',
		'deutscheBank' => '7910',
		'sparKasse' => '7940',
		'reiffeisenBank' => '7950',
		'ceskomoravskaStavebni' => '7960',
		'wuestenrot' => '7970',
		'wuestenrotHypo' => '7980',
		'modraPyramida' => '7990',
		'reiffeisenBankEG' => '8030',
		'oberBank' => '8040',
		'stavebniSporitelnaCS' => '8060',
		'ceskaExportniBank' => '8090',
		'HSBC' => '8150',
		'privatBankReiffeisen' => '8200',
		'paymentExecution' => '8220',
		'EEPays' => '8230',
		'bankGutmann' => '8231',
		'druzstevniZalozenaKredit' => '8240',
		'Bank of China' => '8250',

	];

	private ?string $accountPrefix;

	private string $accountNumber;

	private string $bankCode;

	private ?float $amount;

	private string $currency;

	private ?string $variableSign;

	private ?string $constantSign;

	private ?string $specialSign;

	/**
	 * Internal payment ID.
	 */
	private ?string $identifier;

	/**
	 * Maturity date in ISO 8601 format
	 * (short date format, ie YYYY-mm-dd)
	 */
	private ?DateTimeImmutable $date;

	private ?string $message;

	/**
	 * Use compact format (uppercase without diacritics).
	 * Default value: true.
	 */
	private bool $compress = true;

	/**
	 * Use QR code branding (box and inscription QR Payment).
	 * Default value: true.
	 */
	private bool $branding = true;

	/**
	 * The size of the QR code in pixels.
	 */
	private int $size;

	private string $url;

	public function __construct(string $bankAccountNumber)
	{
		$this->separateBankNumber($bankAccountNumber);
	}

	public function generateGETRequest(string $selectData): bool
	{
		$baseUrl = self::API_PATH;
		$data = '';
		if ($selectData === self::QR_CODE) {
			$data = $this->prepareQRData();
			$baseUrl .= self::RESOURCE_QR;
		}
		if ($data !== '') {
			// to build url
			$url = sprintf('%s?%s', $baseUrl, http_build_query($data));
			$request = new \Mioweb\HttpClient\HttpRequest($url);
			$response = core()->getHttpClient()->sendHttpRequest($request);
			if ($response->getStatusCode() === self::SUCCESS_REQUEST && $response->getBody()) {
					$this->url = $url;

					return true;
			}
		}

		return false;
	}

	private function prepareQRData(): array
	{
		return [
			'accountPrefix' => $this->accountPrefix,
			'accountNumber' => $this->accountNumber,
			'bankCode' => $this->bankCode,
			'identifier' => $this->identifier,
			'amount' => $this->amount,
			'ks' => $this->constantSign,
			'vs' => $this->variableSign,
			'ss' => $this->specialSign,
			'date' => $this->date,
			'message' => $this->message,
			'size' => $this->size,
		];
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	public static function isCZBankCode(string $code): bool
	{
		return in_array($code, self::BANK_CZ_CODES, true);
	}

	/**
	to prepare data for http request
	 */
	public function loadDataQrCode(
		?float $amount,
		?string $currency,
		?string $variableSign,
		?string $constantSign,
		?string $specialSign,
		?string $identifier,
		?DateTimeImmutable $date,
		?string $message,
		?int $size
	): void
	{
		$this->identifier = $identifier;
		$this->amount = $amount;
		$this->constantSign = $constantSign;
		$this->currency = $currency;
		$this->variableSign = $variableSign;
		$this->specialSign = $specialSign;
		$this->date = $date;
		$this->message = $message;
		$this->size = $size;
	}

	/**
	 * use it just for cz banking system
	 */
	private function separateBankNumber(string $bankNumber): void
	{
		//remove all white space from banknumber string
		$bankNumber = str_replace(' ', '', $bankNumber);
		// prefix is 6n length in cz banking system
		$prefix = strpos($bankNumber, '-');
		$strStartWith = false;

		if (str_starts_with($bankNumber, '-')) {
			$bankNumber = ltrim($bankNumber, '-');
			$strStartWith = true;
		}

		if ($prefix !== false && !$strStartWith) {
			$this->accountPrefix = substr($bankNumber, 0, $prefix);
			$this->accountNumber = substr($bankNumber, $prefix + 1, -5);
		} else {
			$this->accountPrefix = null;
			$this->accountNumber = substr($bankNumber, 0, -5);
		}
		// 4 code in cz banking
		$this->bankCode = substr($bankNumber, -4);
	}

	// allow qr code just on cz market
	public function canHaveQrCode(string $countryCode): bool
	{
		return $this->bankCode !== '' && $countryCode === 'CZ' && self::isCZBankCode($this->bankCode);
	}

}
