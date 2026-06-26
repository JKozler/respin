<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Rest;

use Mioweb\HttpClient\Exceptions\HttpClientException;
use Mioweb\HttpClient\HttpMethod;
use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\HttpStatusCode;
use Mioweb\HttpClient\IHttpClient;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\EmailSendingIsDisabledException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\HostingIsDeletedException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\HostingIsNotBoundToThisHostingLicense;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\InvalidDnsSettingsException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\LifetimeLicenseIsNotBoundToAnyLicenseException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\SenderEmailNotMatchWebsiteException;
use Mioweb\MiowebAdminClient\Exceptions\LicenseDeactivationException;
use Mioweb\MiowebAdminClient\Exceptions\NotAuthorizedException;
use Mioweb\MiowebAdminClient\Rest\Exceptions\InvalidResponseBodyException;
use Mioweb\MiowebAdminClient\Rest\Exceptions\InvalidStatusCodeException;
use Mioweb\MiowebAdminClient\Rest\Exceptions\RestClientException;
use Nette\Utils\Strings;

class MiowebAdminPublicRestClient extends MiowebAdminBaseRestClient
{

	private string $apiUrl;

	private IHttpClient $httpClient;

	public function __construct(string $apiUrl, IHttpClient $httpClient)
	{
		$this->apiUrl = \rtrim($apiUrl, '/');
		$this->httpClient = $httpClient;
	}

	/**
	 * @param string $method
	 * @param string $path
	 * @param mixed[]|null $data
	 * @return HttpResponse
	 */
	public function sendHttpRequest(string $method, string $path, ?array $data = null): HttpResponse
	{
		$url = $this->apiUrl . $path;

		$options = [
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			],
		];

		if ($data !== null) {
			$options['json'] = $data;
		}

		try {
			$httpRequest = new HttpRequest($url, $method, $options);

			return $this->httpClient->sendHttpRequest($httpRequest);
		} catch (HttpClientException $e) {
			throw new RestClientException('Failed to send an HTTP request.', 0, $e);
		}
	}

	/**
	 * @param mixed[] $data "serial_number", "url"
	 * @return mixed[] Success response from public api "deactivate license".
	 * @throws LicenseDeactivationException
	 */
	public function deactivateLicense(array $data): array
	{
		$path = '/license/deactivate?' . $this->formatUrlParameters($data);
		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		$statusCode = $httpResponse->getStatusCode();
		$response = $this->getResourceResponseData($httpResponse);

		if ($statusCode === HttpStatusCode::S200_OK) {
			if ($response['status'] === 'success') {
				return $response;
			}

			$errorMessage = $response['status'];
		} else {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);
		}

		throw new LicenseDeactivationException($errorMessage);
	}

	/**
	 * @param mixed[] $data "email", "password"
	 * @return mixed[] Success response from public api "login user".
	 * @throws NotAuthorizedException
	 */
	public function loginUser(array $data): array
	{
		$path = '/auth/login/user?' . $this->formatUrlParameters($data);
		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S403_FORBIDDEN) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			if (\strpos($errorMessage, 'Not Authorized') !== false) {
				throw new NotAuthorizedException();
			}

			throw new InvalidResponseBodyException($errorMessage);
		}

		throw new InvalidStatusCodeException();
	}

	/**
	 * Check requirements for sending (transactional) e-mails. Failures are converted to exceptions.
	 *
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
	public function checkEmailRequirements(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/external-emails/check-requirements', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S202_ACCEPTED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);
		$this->throwEmailException($errorMessage);
	}

	/**
	 * Send (transactional) e-mails. Failures are converted to exceptions.
	 *
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
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/external-emails/send', $data);

		if (in_array($httpResponse->getStatusCode(), [HttpStatusCode::S202_ACCEPTED, HttpStatusCode::S207_MULTI_STATUS], true)) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);
		$this->throwEmailException($errorMessage);
	}

	/**
	 * @throws EmailSendingIsDisabledException
	 * @throws HostingIsDeletedException
	 * @throws HostingIsNotBoundToThisHostingLicense
	 * @throws InvalidDnsSettingsException
	 * @throws LifetimeLicenseIsNotBoundToAnyLicenseException
	 * @throws SenderEmailNotMatchWebsiteException
	 * @phpstan-return never
	 */
	private function throwEmailException(string $errorMessage): void
	{
		if (Strings::contains($errorMessage, 'E-mail sending is disabled for this license')) {
			throw new EmailSendingIsDisabledException($errorMessage);
		}

		if (Strings::contains($errorMessage, 'Invalid DNS settings')) {
			throw new InvalidDnsSettingsException($errorMessage);
		}

		if (
			Strings::contains($errorMessage, 'Sender e-mail address domain (')
			&& Strings::contains($errorMessage, 'must match website domain (')
		) {
			throw new SenderEmailNotMatchWebsiteException($errorMessage);
		}

		if (Strings::contains($errorMessage, 'Hosting is not bound to this hosting license')) {
			throw new HostingIsNotBoundToThisHostingLicense($errorMessage);
		}

		if (Strings::contains($errorMessage, 'Hosting is deleted')) {
			throw new HostingIsDeletedException($errorMessage);
		}

		if (Strings::contains($errorMessage, 'Lifetime license is not bound to any license')) {
			throw new LifetimeLicenseIsNotBoundToAnyLicenseException($errorMessage);
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

}
