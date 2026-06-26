<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient;

use Mioweb\HttpClient\IHttpClient;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\EmailSendingIsDisabledException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\HostingIsDeletedException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\HostingIsNotBoundToThisHostingLicense;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\InvalidDnsSettingsException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\LifetimeLicenseIsNotBoundToAnyLicenseException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\SenderEmailNotMatchWebsiteException;
use Mioweb\MiowebAdminClient\Exceptions\LicenseDeactivationException;
use Mioweb\MiowebAdminClient\Rest\Exceptions\InvalidResponseBodyException;
use Mioweb\MiowebAdminClient\Rest\MiowebAdminPublicRestClient;

class MiowebAdminPublicClient implements IMiowebAdminPublicClient
{

	private MiowebAdminPublicRestClient $restClient;

	public function __construct(string $apiUrl, IHttpClient $httpClient)
	{
		$this->restClient = new MiowebAdminPublicRestClient($apiUrl, $httpClient);
	}

	/**
	 * Method to check MWA api
	 *
	 * @return mixed[]
	 */
	public function ping(): array
	{
		return $this->restClient->getSingularResource('/ping');
	}

	/**
	 * Method to deactivate license
	 *
	 * @param mixed[] $data "serial_number", "url"
	 * @return mixed[]
	 * @throws LicenseDeactivationException
	 */
	public function deactivateLicense(array $data): array
	{
		return $this->restClient->deactivateLicense($data);
	}

	/**
	 * Method to authenticate machine with access token and URL
	 *
	 * @param string $token
	 * @param string $url
	 * @return mixed[]
	 */
	public function loginMachine(string $token, string $url): array
	{
		return $this->restClient->getSingularResource('/auth/login/machine', [
			'token' => $token,
			'url' => $url,
		]);
	}

	/**
	 * Method to authenticate user with login credentials (email and password)
	 *
	 * @param string $email
	 * @param string $password
	 * @return mixed[]
	 */
	public function loginUser(string $email, string $password): array
	{
		return $this->restClient->loginUser([
			'email' => $email,
			'password' => $password,
		]);
	}

	/**
	 * Method to calculate password score
	 *
	 * @param string $password
	 * @return mixed[]
	 */
	public function passwordScore(string $password, ?int $mailServerId = null): array
	{
		return $this->restClient->getSingularResource('/password-score', [
			'password' => $password,
			'mailServerId' => $mailServerId,
		]);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getWpPlugins(array $parameters): array
	{
		return $this->restClient->getSingularResource('/wp-plugins', $parameters);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getExchangeRates(array $parameters): array
	{
		return $this->restClient->getSingularResource('/exchange-rates', $parameters);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 * @throws EmailSendingIsDisabledException
	 * @throws InvalidDnsSettingsException
	 * @throws SenderEmailNotMatchWebsiteException
	 * @throws HostingIsNotBoundToThisHostingLicense
	 * @throws HostingIsDeletedException
	 * @throws LifetimeLicenseIsNotBoundToAnyLicenseException
	 * @throws InvalidResponseBodyException
	 */
	public function checkRequirementsForSendingEmail(array $data): array
	{
		return $this->restClient->checkEmailRequirements($data);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 * @throws EmailSendingIsDisabledException
	 * @throws InvalidDnsSettingsException
	 * @throws SenderEmailNotMatchWebsiteException
	 * @throws HostingIsNotBoundToThisHostingLicense
	 * @throws HostingIsDeletedException
	 * @throws LifetimeLicenseIsNotBoundToAnyLicenseException
	 * @throws InvalidResponseBodyException
	 */
	public function sendEmail(array $data): array
	{
		return $this->restClient->sendEmail($data);
	}

}
