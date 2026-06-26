<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient\Rest;

use Mioweb\HttpClient\Exceptions\HttpClientException;
use Mioweb\HttpClient\HttpMethod;
use Mioweb\HttpClient\HttpRequest;
use Mioweb\HttpClient\HttpResponse;
use Mioweb\HttpClient\HttpStatusCode;
use Mioweb\HttpClient\IHttpClient;
use Mioweb\HttpClient\Utils\Json;
use Mioweb\MiowebAdminClient\Exceptions\ActiveHostingOrderException;
use Mioweb\MiowebAdminClient\Exceptions\AuthKeyRequestsPerHourExceeded;
use Mioweb\MiowebAdminClient\Exceptions\AuthenticationKeyExpired;
use Mioweb\MiowebAdminClient\Exceptions\CannotCreateAliasForExistingMailbox;
use Mioweb\MiowebAdminClient\Exceptions\CloudMailUnavailableException;
use Mioweb\MiowebAdminClient\Exceptions\CustomerAlreadyExistsException;
use Mioweb\MiowebAdminClient\Exceptions\CustomerNotExistsException;
use Mioweb\MiowebAdminClient\Exceptions\DiskQuotaPricesException;
use Mioweb\MiowebAdminClient\Exceptions\DistributionCodeHasAlreadyBeenUsed;
use Mioweb\MiowebAdminClient\Exceptions\DomainOccupiedOrBlockedException;
use Mioweb\MiowebAdminClient\Exceptions\DomainPrepaidCountEmptyException;
use Mioweb\MiowebAdminClient\Exceptions\DomainPricesException;
use Mioweb\MiowebAdminClient\Exceptions\EmailAlreadyTakenException;
use Mioweb\MiowebAdminClient\Exceptions\FapiErrorException;
use Mioweb\MiowebAdminClient\Exceptions\FullHostingPricesException;
use Mioweb\MiowebAdminClient\Exceptions\HostingNotExistsException;
use Mioweb\MiowebAdminClient\Exceptions\HostingOnlyPricesException;
use Mioweb\MiowebAdminClient\Exceptions\HostingShareAlreadyExistsException;
use Mioweb\MiowebAdminClient\Exceptions\HostingShareInvalidAgencyStatusException;
use Mioweb\MiowebAdminClient\Exceptions\HostingStatusNotCreatedException;
use Mioweb\MiowebAdminClient\Exceptions\HttpsAlreadyEnabledException;
use Mioweb\MiowebAdminClient\Exceptions\HttpsCertificateGenerationException;
use Mioweb\MiowebAdminClient\Exceptions\HttpsCustomNginxConfigurationException;
use Mioweb\MiowebAdminClient\Exceptions\HttpsDomainTestUnsuccessfulException;
use Mioweb\MiowebAdminClient\Exceptions\HttpsFailedRequest;
use Mioweb\MiowebAdminClient\Exceptions\InsufficientContactInfoException;
use Mioweb\MiowebAdminClient\Exceptions\InvalidDomainManagedException;
use Mioweb\MiowebAdminClient\Exceptions\InvalidPhoneNumber;
use Mioweb\MiowebAdminClient\Exceptions\InvalidRdataException;
use Mioweb\MiowebAdminClient\Exceptions\InvalidTTLException;
use Mioweb\MiowebAdminClient\Exceptions\LicensePackageNotFoundException;
use Mioweb\MiowebAdminClient\Exceptions\LowServerCapacityException;
use Mioweb\MiowebAdminClient\Exceptions\MigrationAlreadyRunningException;
use Mioweb\MiowebAdminClient\Exceptions\MigrationNotAllowedNowException;
use Mioweb\MiowebAdminClient\Exceptions\MultiPlusPricesException;
use Mioweb\MiowebAdminClient\Exceptions\NotAuthorizedException;
use Mioweb\MiowebAdminClient\Exceptions\NotEnoughCapacityException;
use Mioweb\MiowebAdminClient\Exceptions\NotFoundException;
use Mioweb\MiowebAdminClient\Exceptions\OrderAlreadyActiveException;
use Mioweb\MiowebAdminClient\Exceptions\OrderAlreadyExistsException;
use Mioweb\MiowebAdminClient\Exceptions\OrderCreationException;
use Mioweb\MiowebAdminClient\Exceptions\PendingAlreadyPresent;
use Mioweb\MiowebAdminClient\Exceptions\PendingCannotCreateForDeleted;
use Mioweb\MiowebAdminClient\Exceptions\PriceListNotExistsException;
use Mioweb\MiowebAdminClient\Exceptions\TooWeakPassword;
use Mioweb\MiowebAdminClient\Exceptions\UserCannotEditDomain;
use Mioweb\MiowebAdminClient\Rest\Exceptions\InvalidResponseBodyException;
use Mioweb\MiowebAdminClient\Rest\Exceptions\InvalidStatusCodeException;
use Mioweb\MiowebAdminClient\Rest\Exceptions\RestClientException;

class MiowebAdminRestClient extends MiowebAdminBaseRestClient
{

	private string $username;

	private string $password;

	private string $apiUrl;

	private IHttpClient $httpClient;

	public function __construct(string $username, string $password, string $apiUrl, IHttpClient $httpClient)
	{
		$this->username = $username;
		$this->password = $password;
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
			'auth' => [$this->username, $this->password],
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
			throw new RestClientException('Failed to send an HTTP request.', $e->getCode(), $e);
		}
	}

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function suspendHosting(int $hostingId): array
	{
		$this->validateId($hostingId);
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/hostings/' . $hostingId . '/suspend', []);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function unSuspendHosting(int $hostingId): array
	{
		$this->validateId($hostingId);
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/hostings/' . $hostingId . '/unsuspend', []);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function fireCreationOnHosting(int $hostingId, array $parameters): array
	{
		$this->validateId($hostingId);
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/hostings/' . $hostingId . '/fire-creation', $parameters);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function extendDomain(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/domains/extend', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * Change domain of hosting. Buy domain if necessary and possible. All failures are converted to exceptions.
	 *
	 * @param int $hostingId
	 * @param string $managed
	 * @param string|null $domainName
	 * @param string|null $nameGenerator
	 * @param mixed[] $parameters
	 * @return mixed[] Response of success from APIv3 "change domain of hosting".
	 * @throws \Throwable on unexpected errors (multiple types for specific cases)
	 */
	public function changeDomain(
		int $hostingId,
		string $managed,
		?string $domainName = null,
		?string $nameGenerator = null,
		array $parameters = []
	): array
	{
		$httpResponse = $this->sendHttpRequest(
			HttpMethod::PUT,
			\sprintf('/v3/hostings/%d/domain', $hostingId),
			[
				'name' => $domainName,
				'managed' => $managed,
				'name_generator' => $nameGenerator,
			] + $parameters,
		);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S400_BAD_REQUEST) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			if (\strpos($errorMessage, 'Invalid hosting domain managed') !== false) {
				throw new InvalidDomainManagedException();
			}

			if (\strpos($errorMessage, 'Hosting must be created') !== false) {
				throw new HostingStatusNotCreatedException();
			}

			if (\strpos($errorMessage, 'Hosting have 0 domain prepaid count') !== false) {
				throw new DomainPrepaidCountEmptyException();
			}

			if (\strpos($errorMessage, 'Domain name has already been taken') !== false) {
				throw new DomainOccupiedOrBlockedException();
			}

			if (\strpos($errorMessage, 'Lack of contact information') !== false) {
				throw new InsufficientContactInfoException();
			}

			if (\strpos($errorMessage, 'Problem while creating contact') !== false) {
				throw new InsufficientContactInfoException();
			}

			throw new InvalidResponseBodyException($errorMessage);
		}

		throw new InvalidStatusCodeException();
	}

	/**
	 * Enable HTTPS for hosting. All failures are converted to exceptions.
	 *
	 * @param int $hostingId
	 * @return mixed[] Response of success from APIv3 "enable HTTPS".
	 * @throws \Exception on unexpected errors (multiple types for specific cases)
	 */
	public function enableHttps(int $hostingId): array
	{
		$httpResponse = $this->sendHttpRequest(
			HttpMethod::POST,
			\sprintf('/v3/hostings/%d/https', $hostingId),
			[],
		);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S404_NOT_FOUND) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			throw new NotFoundException($errorMessage . '[' . $hostingId . ']');
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Https is already recorded to be') !== false) {
			throw new HttpsAlreadyEnabledException();
		}

		if (\strpos($errorMessage, 'Hosting is already secured.') !== false) {
			throw new HttpsAlreadyEnabledException();
		}

		if (\strpos($errorMessage, 'Let\'s Encrypt certificate generating failed.') !== false) {
			throw new HttpsCertificateGenerationException();
		}

		if (\strpos($errorMessage, 'Non-standard nginx config detected.') !== false) {
			throw new HttpsCustomNginxConfigurationException();
		}

		if (\strpos($errorMessage, 'Will not generate certificate, domain test unsuccessful.') !== false) {
			throw new HttpsDomainTestUnsuccessfulException();
		}

		if (\strpos($errorMessage, 'Failed to send an HTTP request') !== false) {
			throw new HttpsFailedRequest();
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * Queue migration of hosting.
	 *
	 * @param int $hostingId
	 *
	 * @param int|null $serverId Optional target server id.
	 * @param \DateTimeInterface|null $processAt Optional datetime when to start with migration.
	 * @return mixed[] Response of success from APIv3 "migrateHosting()".
	 */
	public function migrate(int $hostingId, ?int $serverId = null, ?\DateTimeInterface $processAt = null): array
	{
		$parameters = [
			'server_id' => $serverId,
		];

		if ($processAt !== null) {
			$parameters['process_at'] = $processAt->format('Y-m-d H:i:s');
		}

		$httpResponse = $this->sendHttpRequest(
			HttpMethod::POST,
			\sprintf('/v3/hostings/%d/migrate', $hostingId),
			$parameters,
		);

		if (\in_array($httpResponse->getStatusCode(), [HttpStatusCode::S200_OK, HttpStatusCode::S201_CREATED], true)) {
			return $this->getResourceResponseData($httpResponse);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S404_NOT_FOUND) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			throw new NotFoundException($errorMessage . '[' . $hostingId . ']');
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Another migration already running.') !== false) {
			throw new MigrationAlreadyRunningException();
		}

		if (\strpos($errorMessage, 'Hosting must be created for migration.') !== false) {
			throw new MigrationNotAllowedNowException();
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * Creates PaA order. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data billing information
	 * [first_name, last_name, email, phone, company, ic, dic, address, address.street, address.city, address.zip, address.country]
	 * @return mixed[] Success response from APIv3 "create support".
	 * @throws CustomerNotExistsException
	 * @throws LicensePackageNotFoundException
	 * @throws OrderCreationException
	 * @throws OrderAlreadyActiveException
	 * @throws FapiErrorException
	 */
	public function createSupportOrder(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/orders/support', $data);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S201_CREATED || $statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'License package not found') !== false) {
			throw new LicensePackageNotFoundException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer already has an unresolved support and update order id') !== false) {
			throw new OrderAlreadyActiveException($errorMessage);
		}

		if (\strpos($errorMessage, 'FAPI error') !== false) {
			throw new FapiErrorException($errorMessage);
		}

		if (\strpos($errorMessage, 'Phone number format is invalid') !== false) {
			throw new InvalidPhoneNumber($errorMessage);
		}

		throw new OrderCreationException($errorMessage);
	}

	/**
	 * Gets Full hosting prices. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data priceListCode, [customerId]
	 * @return mixed[] Success response.
	 * @throws PriceListNotExistsException
	 * @throws CustomerNotExistsException
	 * @throws FullHostingPricesException
	 */
	public function getFullHostingPrices(array $data): array
	{
		$path = '/v3/prices/hosting-full';

		if ((bool) $data) {
			$path .= '?' . $this->formatUrlParameters($data);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Price list not exist') !== false) {
			throw new PriceListNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		throw new FullHostingPricesException($errorMessage);
	}

	/**
	 * Gets hosting only prices. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data priceListCode, [customerId]
	 * @return mixed[] Success response.
	 * @throws PriceListNotExistsException
	 * @throws CustomerNotExistsException
	 * @throws HostingOnlyPricesException
	 */
	public function getHostingOnlyPrices(array $data): array
	{
		$path = '/v3/prices/hosting-only';

		if ((bool) $data) {
			$path .= '?' . $this->formatUrlParameters($data);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Price list not exist') !== false) {
			throw new PriceListNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		throw new HostingOnlyPricesException($errorMessage);
	}

	/**
	 * Get prices for "Multi +1" tariffs. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data priceListCode, customerId
	 * @return mixed[] Success response.
	 * @throws PriceListNotExistsException
	 * @throws CustomerNotExistsException
	 * @throws FullHostingPricesException
	 */
	public function getMultiPlusPrices(array $data): array
	{
		$path = '/v3/prices/multi-plus';

		if ((bool) $data) {
			$path .= '?' . $this->formatUrlParameters($data);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Price list not exist') !== false) {
			throw new PriceListNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		throw new MultiPlusPricesException($errorMessage);
	}

	/**
	 * Get prices for "Disk Quota" tariffs. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data priceListCode, customerId
	 * @return mixed[] Success response.
	 * @throws PriceListNotExistsException
	 * @throws CustomerNotExistsException
	 * @throws FullHostingPricesException
	 */
	public function getDiskQuotaPrices(array $data): array
	{
		$path = '/v3/prices/disk-quota';

		if ((bool) $data) {
			$path .= '?' . $this->formatUrlParameters($data);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Price list not exists') !== false) {
			throw new PriceListNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer not exists') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		throw new DiskQuotaPricesException($errorMessage);
	}

	/**
	 * Gets Domain prices. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data priceListCode, [customerId]
	 * @return mixed[] Success response.
	 * @throws PriceListNotExistsException
	 * @throws CustomerNotExistsException
	 * @throws DomainPricesException
	 */
	public function getDomainPrices(array $data): array
	{
		$path = '/v3/prices/domain';

		if ((bool) $data) {
			$path .= '?' . $this->formatUrlParameters($data);
		}

		$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Price list not exist') !== false) {
			throw new PriceListNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		throw new DomainPricesException($errorMessage);
	}

	/**
	 * Creates Full hosting order. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data
	 * @return mixed[] Success response from APIv3.
	 * @throws CustomerNotExistsException
	 * @throws LicensePackageNotFoundException
	 * @throws OrderCreationException
	 * @throws OrderAlreadyActiveException
	 * @throws FapiErrorException
	 */
	public function createFullHostingOrder(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/orders/hosting-full', $data);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S201_CREATED || $statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer already has an unresolved hosting-full order id') !== false) {
			throw new OrderAlreadyActiveException($errorMessage);
		}

		if (\strpos($errorMessage, 'Distribution code has already been used') !== false) {
			throw new DistributionCodeHasAlreadyBeenUsed($errorMessage);
		}

		if (\strpos($errorMessage, 'FAPI error') !== false) {
			throw new FapiErrorException($errorMessage);
		}

		throw new OrderCreationException($errorMessage);
	}

	/**
	 * Creates Multi plus order. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data
	 * @return mixed[] Success response from APIv3.
	 * @throws CustomerNotExistsException
	 * @throws LicensePackageNotFoundException
	 * @throws OrderCreationException
	 * @throws OrderAlreadyActiveException
	 * @throws FapiErrorException
	 */
	public function createMultiPlusOrder(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/orders/multi-plus', $data);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S201_CREATED || $statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer already has an active order') !== false) {
			throw new OrderAlreadyActiveException($errorMessage);
		}

		if (\strpos($errorMessage, 'Distribution code has already been used') !== false) {
			throw new DistributionCodeHasAlreadyBeenUsed($errorMessage);
		}

		if (\strpos($errorMessage, 'FAPI error') !== false) {
			throw new FapiErrorException($errorMessage);
		}

		throw new OrderCreationException($errorMessage);
	}

	/**
	 * Creates domain order. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data
	 * @return mixed[] Success response from APIv3.
	 * @throws CustomerNotExistsException
	 * @throws OrderCreationException
	 * @throws OrderAlreadyActiveException
	 * @throws FapiErrorException
	 */
	public function createDomainOrder(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/orders/domain', $data);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S201_CREATED || $statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer already has an active domain order') !== false) {
			throw new OrderAlreadyActiveException($errorMessage);
		}

		if (\strpos($errorMessage, 'FAPI error') !== false) {
			throw new FapiErrorException($errorMessage);
		}

		if (
			\strpos($errorMessage, 'Parameter address.') !== false ||
			\strpos($errorMessage, 'Parameter first_name must be a string') !== false ||
			\strpos($errorMessage, 'Parameter last_name must be a string') !== false ||
			\strpos($errorMessage, 'Parameter email must be a string') !== false
		) {
			throw new InsufficientContactInfoException($errorMessage);
		}

		throw new OrderCreationException($errorMessage);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createHosting(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/hostings', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S400_BAD_REQUEST) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			// TODO: handle errors
			throw new InvalidResponseBodyException($errorMessage);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		throw new InvalidStatusCodeException($errorMessage);
	}

	/**
	 * Create new pending operation.
	 *
	 * @param mixed[] $data Data of pending operation
	 * @param int|null $hostingId ID of related hosting. Optional.
	 * @param int|null $licenseId ID of related license. Optional.
	 * @return mixed[]
	 */
	public function createPending(array $data, ?int $hostingId = null, ?int $licenseId = null): array
	{
		$this->validateId($hostingId, 'hostingId');

		if ($licenseId !== null) {
			$this->validateId($licenseId, 'licenseId');
		}

		$httpResponse = $this->sendHttpRequest(
			HttpMethod::POST,
			\sprintf('/v3/hostings/%s/pendings', $hostingId),
			$data,
		);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		if ($httpResponse->getStatusCode() === 208) {
			throw new PendingAlreadyPresent();
		}

		$msg = $this->getErrorMessageResponseData($httpResponse);

		if ($msg === 'Can not set pending operation for deleted hosting.') {
			throw new PendingCannotCreateForDeleted($msg);
		}

		if ($msg === 'Pending operation with this kind exist for this hosting.') {
			throw new PendingAlreadyPresent($msg);
		}

		throw new InvalidStatusCodeException($msg);
	}

	public function deletePending(int $hostingId, string $kind): void
	{
		$this->validateId($hostingId, 'hostingId');
		$query = $this->formatUrlParameters([
			'kind' => $kind,
		]);

		$httpResponse = $this->sendHttpRequest(
			HttpMethod::DELETE,
			\sprintf('/v3/hostings/%s/pendings?%s', $hostingId, $query),
		);

		if (!\in_array($httpResponse->getStatusCode(), [HttpStatusCode::S200_OK, HttpStatusCode::S204_NO_CONTENT], true)) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			throw new InvalidStatusCodeException($errorMessage);
		}
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getEmails(array $parameters): array
	{
		$path = '/email?' . $this->formatUrlParameters($parameters);

		try {
			$httpResponse = $this->sendHttpRequest(HttpMethod::GET, $path);
		} catch (RestClientException $e) {
			if ($e->getCode() === HttpStatusCode::S503_SERVICE_UNAVAILABLE) {
				throw new CloudMailUnavailableException();
			}

			throw $e;
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S503_SERVICE_UNAVAILABLE) {
			throw new CloudMailUnavailableException();
		}

		if ($httpResponse->getStatusCode() !== HttpStatusCode::S200_OK) {
			throw new InvalidStatusCodeException();
		}

		$responseData = Json::decode($httpResponse->getBody(), Json::FORCE_ARRAY);

		if (!\is_array($responseData)) {
			throw new InvalidResponseBodyException();
		}

		return $responseData;
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 * @throws EmailAlreadyTakenException
	 * @throws InvalidResponseBodyException
	 * @throws TooWeakPassword
	 * @throws CannotCreateAliasForExistingMailbox
	 */
	public function createEmail(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/email', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S409_CONFLICT) {
			throw new EmailAlreadyTakenException();
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S400_BAD_REQUEST) {
			$msg = $this->getErrorMessageResponseData($httpResponse);

			if ($msg === 'Cannot create an alias for existing mailbox.') {
				throw new CannotCreateAliasForExistingMailbox('Cannot create an alias for existing mailbox.');
			}
		}

		if ($httpResponse->getStatusCode() === HttpStatusCode::S403_FORBIDDEN) {
			$msg = $this->getErrorMessageResponseData($httpResponse);

			if ($msg === 'User cannot edit this domain') {
				throw new UserCannotEditDomain($msg);
			}
		}

		if ($httpResponse->getStatusCode() !== HttpStatusCode::S200_OK) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			if (\strpos($errorMessage, 'Given password is too weak') !== false) {
				throw new TooWeakPassword($errorMessage);
			}

			if (\strpos($errorMessage, ' already exist') !== false) {
				throw new EmailAlreadyTakenException($errorMessage);
			}

			throw new InvalidStatusCodeException($errorMessage);
		}

		$responseData = Json::decode($httpResponse->getBody(), Json::FORCE_ARRAY);

		if (!\is_array($responseData)) {
			throw new InvalidResponseBodyException();
		}

		return $responseData;
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateEmailPassword(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::PUT, '/email', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S403_FORBIDDEN) {
			$msg = $this->getErrorMessageResponseData($httpResponse);

			if ($msg === 'User cannot edit this domain') {
				throw new UserCannotEditDomain($msg);
			}
		} elseif ($httpResponse->getStatusCode() !== HttpStatusCode::S200_OK) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			if (\strpos($errorMessage, 'Given password is too weak') !== false) {
				throw new TooWeakPassword($errorMessage);
			}

			throw new InvalidStatusCodeException($errorMessage);
		}

		$responseData = Json::decode($httpResponse->getBody(), Json::FORCE_ARRAY);

		if (!\is_array($responseData)) {
			throw new InvalidResponseBodyException();
		}

		return $responseData;
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 * @throws UserCannotEditDomain
	 * @throws InvalidStatusCodeException
	 */
	public function deleteEmail(array $parameters): array
	{
		$path = '/email?' . $this->formatUrlParameters($parameters);
		$httpResponse = $this->sendHttpRequest(HttpMethod::DELETE, $path);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S403_FORBIDDEN) {
			$msg = $this->getErrorMessageResponseData($httpResponse);

			if ($msg === 'User cannot edit this domain') {
				throw new UserCannotEditDomain($msg);
			}
		} elseif ($httpResponse->getStatusCode() !== HttpStatusCode::S200_OK) {
			throw new InvalidStatusCodeException($this->getErrorMessageResponseData($httpResponse));
		}

		$responseData = Json::decode($httpResponse->getBody(), Json::FORCE_ARRAY);

		if (!\is_array($responseData)) {
			throw new InvalidResponseBodyException();
		}

		return $responseData;
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createHostingShare(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/hosting-shares', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Hosting not found') !== false) {
			throw new HostingNotExistsException();
		}

		if (\strpos($errorMessage, 'Hosting share already exist') !== false) {
			throw new HostingShareAlreadyExistsException();
		}

		if (\strpos($errorMessage, 'Hosting can be shared only to customers with valid agency status') !== false) {
			throw new HostingShareInvalidAgencyStatusException();
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createAuthKey(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/auth/keys', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Maximum number of requests per hour exceeded') !== false) {
			throw new AuthKeyRequestsPerHourExceeded();
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * Change password using password-reset access key. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data
	 * @return mixed[] Response of success from APIv3 "password reset".
	 * @throws \Exception on unexpected errors (multiple types for specific cases)
	 */
	public function changePassword(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/auth/keys/password-reset', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Given password is too weak') !== false) {
			throw new TooWeakPassword();
		}

		if (\strpos($errorMessage, 'Authentication key has expired') !== false) {
			throw new AuthenticationKeyExpired();
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * Change mailbox password using mailbox-password-reset access key. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data
	 * @return mixed[] Response of success from APIv3 "mailbox password reset".
	 * @throws \Exception on unexpected errors (multiple types for specific cases)
	 */
	public function changeMailboxPassword(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/auth/keys/mailbox-password-reset', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S204_NO_CONTENT) {
			return [];
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Given password is too weak') !== false) {
			throw new TooWeakPassword();
		}

		if (\strpos($errorMessage, 'Authentication key has expired') !== false) {
			throw new AuthenticationKeyExpired();
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * Phone verification using phone-verify access key. All failures are converted to exceptions.
	 *
	 * @param int $customerId
	 * @param string $key
	 * @return mixed[] Response of success from APIv3 "phone verify".
	 * @throws \Exception on unexpected errors (multiple types for specific cases)
	 */
	public function phoneVerify(int $customerId, string $key): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/auth/keys/phone-verify', [
			'customer_id' => $customerId,
			'key' => $key,
		]);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Authentication key not found') !== false) {
			throw new NotFoundException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer is not an owner of phone verify key') !== false) {
			throw new NotAuthorizedException($errorMessage);
		}

		if (\strpos($errorMessage, 'Authentication key has expired') !== false) {
			throw new AuthenticationKeyExpired();
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createCustomer(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/customers', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'User with same email or identification exist') !== false) {
			throw new CustomerAlreadyExistsException();
		}

		if (\strpos($errorMessage, 'Phone number format is invalid') !== false) {
			throw new InvalidPhoneNumber();
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * @param int $customerId
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateCustomer(int $customerId, array $data): array
	{
		$path = $this->addQueryParameters('/v3/customers/' . $customerId, []);

		$httpResponse = $this->sendHttpRequest(HttpMethod::PUT, $path, $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Phone number format is invalid') !== false) {
			throw new InvalidPhoneNumber($errorMessage);
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	public function cancelOrder(int $id): void
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::PUT, '/v3/orders/' . $id . '/cancel');

		if ($httpResponse->getStatusCode() !== HttpStatusCode::S200_OK) {
			$errorMessage = $this->getErrorMessageResponseData($httpResponse);

			throw new InvalidStatusCodeException($errorMessage);
		}
	}

	/**
	 * Creates disk quota order. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data
	 * @return mixed[] Success response from APIv3.
	 * @throws CustomerNotExistsException
	 * @throws OrderCreationException
	 * @throws OrderAlreadyActiveException
	 * @throws FapiErrorException
	 * @throws InsufficientContactInfoException
	 */
	public function createDiskQuotaOrder(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/orders/disk-quota', $data);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S201_CREATED || $statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Hosting capacity can not be increased, because server is almost full. Please contact our support team to handle transfer to another server.') !== false) {
			throw new LowServerCapacityException($errorMessage);
		}

		if (\strpos($errorMessage, 'Hosting capacity can not be decreased, because data occupy more disk space than would left.') !== false) {
			throw new NotEnoughCapacityException($errorMessage);
		}

		if (\strpos($errorMessage, 'FAPI error') !== false) {
			throw new FapiErrorException($errorMessage);
		}

		if (\strpos($errorMessage, 'There already is disk quota order pending for this hosting.') !== false) {
			throw new OrderAlreadyExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Order could not be processed. Please pay all unpaid invoices.') !== false) {
			throw new ActiveHostingOrderException($errorMessage);
		}

		if (
			\strpos($errorMessage, 'Customer_id and billing_data can not be both empty.') !== false ||
			\strpos($errorMessage, 'Parameter address') !== false ||
			\strpos($errorMessage, 'Parameter first_name must be a string') !== false ||
			\strpos($errorMessage, 'Parameter last_name must be a string') !== false ||
			\strpos($errorMessage, 'Parameter email must be a string') !== false
		) {
			throw new InsufficientContactInfoException($errorMessage);
		}

		throw new OrderCreationException($errorMessage);
	}

	/** @param mixed[] $parameters */
	public function deleteImprovementVote(array $parameters): void
	{
		$path = '/v3/improvement-votes?' . $this->formatUrlParameters($parameters);
		$httpResponse = $this->sendHttpRequest(HttpMethod::DELETE, $path);

		if ($httpResponse->getStatusCode() !== HttpStatusCode::S204_NO_CONTENT) {
			throw new InvalidStatusCodeException($this->getErrorMessageResponseData($httpResponse));
		}
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createDnsRecord(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/dns/', $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Invalid rdata format for this rdtype.') !== false) {
			throw new InvalidRdataException($errorMessage);
		}

		if (\strpos($errorMessage, 'There is already another DNS row with the same name and type but different TTL') !== false) {
			throw new InvalidTTLException($errorMessage);
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateDnsRecord(int $id, array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::PUT, '/v3/dns/' . $id, $data);

		if ($httpResponse->getStatusCode() === HttpStatusCode::S201_CREATED) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Invalid rdata format for this rdtype.') !== false) {
			throw new InvalidRdataException($errorMessage);
		}

		if (\strpos($errorMessage, 'There is already another DNS row with the same name and type but different TTL') !== false) {
			throw new InvalidTTLException($errorMessage);
		}

		throw new InvalidResponseBodyException($errorMessage);
	}

	/**
	 * Creates hosting only order. All failures are converted to exceptions.
	 *
	 * @param mixed[] $data
	 * @return mixed[] Success response from APIv3.
	 * @throws CustomerNotExistsException
	 * @throws LicensePackageNotFoundException
	 * @throws OrderCreationException
	 * @throws OrderAlreadyActiveException
	 * @throws FapiErrorException
	 */
	public function createHostingOnlyOrder(array $data): array
	{
		$httpResponse = $this->sendHttpRequest(HttpMethod::POST, '/v3/orders/hosting-only', $data);

		$statusCode = $httpResponse->getStatusCode();

		if ($statusCode === HttpStatusCode::S201_CREATED || $statusCode === HttpStatusCode::S200_OK) {
			return $this->getResourceResponseData($httpResponse);
		}

		$errorMessage = $this->getErrorMessageResponseData($httpResponse);

		if (\strpos($errorMessage, 'Customer not exist') !== false) {
			throw new CustomerNotExistsException($errorMessage);
		}

		if (\strpos($errorMessage, 'Customer already has an unresolved hosting-full order id') !== false) {
			throw new OrderAlreadyActiveException($errorMessage);
		}

		if (\strpos($errorMessage, 'Distribution code has already been used') !== false) {
			throw new DistributionCodeHasAlreadyBeenUsed($errorMessage);
		}

		if (\strpos($errorMessage, 'FAPI error') !== false) {
			throw new FapiErrorException($errorMessage);
		}

		throw new OrderCreationException($errorMessage);
	}

}
