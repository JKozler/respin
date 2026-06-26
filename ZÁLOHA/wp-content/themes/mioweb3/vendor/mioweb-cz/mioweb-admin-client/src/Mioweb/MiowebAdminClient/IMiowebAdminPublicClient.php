<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient;

use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\EmailSendingIsDisabledException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\HostingIsDeletedException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\HostingIsNotBoundToThisHostingLicense;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\InvalidDnsSettingsException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\LifetimeLicenseIsNotBoundToAnyLicenseException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\SenderEmailNotMatchWebsiteException;
use Mioweb\MiowebAdminClient\Rest\Exceptions\InvalidResponseBodyException;

interface IMiowebAdminPublicClient
{

	/**
	 * Method to check MWA api
	 *
	 * @return mixed[]
	 */
	public function ping(): array;

	/**
	 * Method to deactivate license
	 *
	 * @param mixed[] $data "serial_number", "url"
	 * @return mixed[]
	 */
	public function deactivateLicense(array $data): array;

	/**
	 * Method to authenticate machine with access token and URL
	 *
	 * @param string $token
	 * @param string $url
	 * @return mixed[]
	 */
	public function loginMachine(string $token, string $url): array;

	/**
	 * Method to authenticate user with login credentials (email and password)
	 *
	 * @param string $email
	 * @param string $password
	 * @return mixed[]
	 */
	public function loginUser(string $email, string $password): array;

	/**
	 * Method to calculate password score
	 *
	 * @param string $password
	 * @return mixed[]
	 */
	public function passwordScore(string $password, ?int $mailServerId = null): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getWpPlugins(array $parameters): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getExchangeRates(array $parameters): array;

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
	public function checkRequirementsForSendingEmail(array $data): array;

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
	public function sendEmail(array $data): array;

}
