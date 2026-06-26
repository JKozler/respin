<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient;

use Mioweb\HttpClient\HttpStatusCode;
use Mioweb\HttpClient\IHttpClient;
use Mioweb\MiowebAdminClient\Rest\MiowebAdminRestClient;

class MiowebAdminClient implements IMiowebAdminClient
{

	private MiowebAdminRestClient $restClient;

	public function __construct(string $username, string $password, string $apiUrl, IHttpClient $httpClient)
	{
		$this->restClient = new MiowebAdminRestClient($username, $password, $apiUrl, $httpClient);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function extendDomain(array $data): array
	{
		return $this->restClient->extendDomain($data);
	}

	/**
	 * @param int $hostingId
	 * @param string $managed
	 * @param string|null $domainName
	 * @param string|null $nameGenerator
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function changeDomain(
		int $hostingId,
		string $managed,
		?string $domainName = null,
		?string $nameGenerator = null,
		array $parameters = []
	): array
	{
		return $this->restClient->changeDomain($hostingId, $managed, $domainName, $nameGenerator, $parameters);
	}

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function enableHttps(int $hostingId): array
	{
		return $this->restClient->enableHttps($hostingId);
	}

	/**
	 * Migrate hosting to different server.
	 *
	 * @param int $hostingId
	 * @param int|null $serverId
	 * @param \DateTimeInterface|null $processAt
	 * @return mixed[]
	 */
	public function migrate(int $hostingId, ?int $serverId = null, ?\DateTimeInterface $processAt = null): array
	{
		return $this->restClient->migrate($hostingId, $serverId, $processAt);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function checkDomain(array $parameters): array
	{
		return $this->restClient->getSingularResource('/v3/domains/check', $parameters);
	}

	/**
	 * Get customers. Filtering available.
	 *
	 * @param mixed[] $parameters Available filters, for unique values: "email", "billingUserId". Optional "limit=600", "offset".
	 * @return mixed[]
	 */
	public function getCustomers(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/customers', $parameters);
	}

	/**
	 * Get one customer.
	 *
	 * @param int $customerId
	 * @param mixed[] $parameters
	 * @return mixed[]|null
	 */
	public function getCustomer(int $customerId, array $parameters = []): ?array
	{
		return $this->restClient->getResource('/v3/customers', $customerId, $parameters);
	}

	/**
	 * @param int $customerId
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateCustomer(int $customerId, array $data): array
	{
		return $this->restClient->updateCustomer($customerId, $data);
	}

	/**
	 * @param int $customerId
	 * @param mixed[] $parameters
	 */
	public function deleteCustomer(int $customerId, array $parameters = []): void
	{
		$this->restClient->deleteResource('/v3/customers', $customerId, [], $parameters);
	}

	/**
	 * Create new customer. Billing user ID is automatically loaded from Billing API by corresponding email.
	 *
	 * @param mixed[] $data "email", "is_agency", "support_expiration_date=null"
	 * @return mixed[]
	 */
	public function createCustomer(array $data): array
	{
		return $this->restClient->createCustomer($data);
	}

	/**
	 * Gets MW releases
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getMwReleases(array $data): array
	{
		return $this->restClient->getSingularResource('/v3/mioweb-releases', $data);
	}

	/**
	 * Get one order by ID.
	 *
	 * @param int $orderId
	 * @return mixed[]|null
	 */
	public function getOrder(int $orderId, bool $includeData = false, bool $includeFapiData = false): ?array
	{
		return $this->restClient->getResource('/v3/orders', $orderId, [
			'include_data' => $includeData,
			'include_fapi' => $includeFapiData,
		]);
	}

	/**
	 * Gets orders
	 *
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getOrders(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/orders', $parameters);
	}

	/**
	 * Get one order source by ID.
	 *
	 * @param int $orderSourceId
	 * @return mixed[]|null
	 */
	public function getOrderSource(int $orderSourceId): ?array
	{
		return $this->restClient->getResource('/v3/order-sources', $orderSourceId);
	}

	/**
	 * Gets order sources
	 *
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getOrderSources(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/order-sources', $parameters);
	}

	/**
	 * Gets hosting full order
	 *
	 * @param int $orderId
	 * @return mixed[]|null
	 */
	public function getHostingFullOrder(int $orderId): ?array
	{
		return $this->restClient->getResource('/v3/orders/hosting-full', $orderId);
	}

	/**
	 * Gets hosting-full orders
	 *
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostingFullOrders(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/orders/hosting-full', $parameters);
	}

	/**
	 * Gets domain orders
	 *
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getDomainOrders(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/orders/domain', $parameters);
	}

	/**
	 * Gets PaA order
	 *
	 * @param int $orderId
	 * @return mixed[]|null
	 */
	public function getSupportOrder(int $orderId): ?array
	{
		return $this->restClient->getResource('/v3/orders/support', $orderId);
	}

	/**
	 * Gets support orders
	 *
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getSupportOrders(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/orders/support', $parameters);
	}

	/**
	 * Gets one price
	 *
	 * @param int $priceId
	 * @return mixed[]|null
	 */
	public function getPrice(int $priceId): ?array
	{
		return $this->restClient->getResource('/v3/prices', $priceId);
	}

	/**
	 * Gets prices
	 *
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getPrices(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/prices', $parameters);
	}

	/**
	 * Creates new PaA order
	 *
	 * @param mixed[] $data billing information
	 * @return mixed[]
	 */
	public function createSupportOrder(array $data): array
	{
		return $this->restClient->createSupportOrder($data);
	}

	/**
	 * Gets prices for support and update orders
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getSupportPrices(array $data = []): array
	{
		return $this->restClient->getSingularResource('/v3/prices/support', $data);
	}

	/**
	 * Creates new hosting-full order
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createFullHostingOrder(array $data): array
	{
		return $this->restClient->createFullHostingOrder($data);
	}

	/**
	 * Creates new multi-plus order
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createMultiPlusOrder(array $data): array
	{
		return $this->restClient->createMultiPlusOrder($data);
	}

	/**
	 * Creates new domain order
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createDomainOrder(array $data): array
	{
		return $this->restClient->createDomainOrder($data);
	}

	/** @return mixed[] */
	public function getPriceListTypes(): array
	{
		return $this->restClient->getSingularResource('/v3/price-lists');
	}

	/**
	 * Gets prices for hosting-full
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getFullHostingPrices(array $data): array
	{
		return $this->restClient->getFullHostingPrices($data);
	}

	/**
	 * Gets prices for hosting-only
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getHostingOnlyPrices(array $data): array
	{
		return $this->restClient->getHostingOnlyPrices($data);
	}

	/**
	 * Gets prices for multi-plus
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getMultiPlusPrices(array $data): array
	{
		return $this->restClient->getMultiPlusPrices($data);
	}

	/**
	 * Gets prices for disk quota
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getDiskQuotaPrices(array $data): array
	{
		return $this->restClient->getDiskQuotaPrices($data);
	}

	/**
	 * Gets mapping for multi plus tariffs
	 *
	 * @return mixed[]
	 */
	public function getMultiPlusMapping(): array
	{
		return $this->restClient->getSingularResource('/v3/prices/multi-plus-mapping');
	}

	/**
	 * Gets price for domain service
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getDomainPrices(array $data): array
	{
		return $this->restClient->getDomainPrices($data);
	}

	/**
	 * @param mixed[] $data "email"
	 * @return bool
	 */
	public function isEmailAvailableForCustomer(array $data): bool
	{
		$data = $this->restClient->createResource('/v3/customers/check-availability', $data, HttpStatusCode::S200_OK);

		return $data['available'] ?? false;
	}

	/**
	 * @param int $customerId Customer ID
	 * @param mixed[] $parameters "tdl", "domain"
	 * @return mixed[]|null
	 */
	public function getCustomerDomainContact(int $customerId, array $parameters): ?array
	{
		return $this->restClient->getResource('/v3/customers/domain-contact', $customerId, $parameters);
	}

	/**
	 * @param mixed[] $data "customer_id", "domain", "birthdate", "company"
	 * @return mixed[]
	 */
	public function createCustomerDomainContact(array $data): array
	{
		return $this->restClient->createResource('/v3/customers/domain-contact', $data);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getLicensePackages(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/license-packages', $parameters);
	}

	/**
	 * @param int $licensePackageId
	 * @return mixed[]|null
	 */
	public function getLicensePackage(int $licensePackageId): ?array
	{
		return $this->restClient->getResource('/v3/license-packages', $licensePackageId);
	}

	/**
	 * @param mixed[] $data
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function createLicensePackage(array $data, array $parameters = []): array
	{
		return $this->restClient->createResource(
			'/v3/license-packages',
			$data,
			HttpStatusCode::S201_CREATED,
			$parameters
		);
	}

	/**
	 * @param int $licensePackageId
	 * @param mixed[] $data
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function updateLicensePackage(int $licensePackageId, array $data, array $parameters = []): array
	{
		return $this->restClient->updateResource('/v3/license-packages', $licensePackageId, $data, $parameters);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostings(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/hostings', $parameters);
	}

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 * @return mixed[]|null
	 */
	public function getHosting(int $hostingId, array $parameters = []): ?array
	{
		return $this->restClient->getResource('/v3/hostings', $hostingId, $parameters);
	}

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostingInfo(int $hostingId, array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/hostings/' . $hostingId . '/info', $parameters);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createHosting(array $data): array
	{
		return $this->restClient->createHosting($data);
	}

	/**
	 * @param int $hostingId
	 * @param mixed[] $data
	 * @param int|null $licenseId
	 * @return mixed[]
	 */
	public function createHostingPending(int $hostingId, array $data, ?int $licenseId = null): array
	{
		return $this->restClient->createPending($data, $hostingId, $licenseId);
	}

	public function deleteHostingPending(int $hostingId, string $kind): void
	{
		$this->restClient->deletePending($hostingId, $kind);
	}

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostingPendings(int $hostingId, array $parameters = []): array
	{
		$this->restClient->validateId($hostingId, 'hostingId');

		return $this->restClient->getSingularResource('/v3/hostings/' . $hostingId . '/pendings', $parameters);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getBillingStatistics(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/billing-statistics', $parameters);
	}

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function suspendHosting(int $hostingId): array
	{
		return $this->restClient->suspendHosting($hostingId);
	}

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function unSuspendHosting(int $hostingId): array
	{
		return $this->restClient->unSuspendHosting($hostingId);
	}

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function fireCreationOnHosting(int $hostingId, array $parameters): array
	{
		return $this->restClient->fireCreationOnHosting($hostingId, $parameters);
	}

	/**
	 * @param int $hostingId
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateHosting(int $hostingId, array $data): array
	{
		return $this->restClient->updateResource('/v3/hostings', $hostingId, $data);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostingShares(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/hosting-shares', $parameters);
	}

	/**
	 * @param int $hostingShareId
	 * @return mixed[]|null
	 */
	public function getHostingShare(int $hostingShareId): ?array
	{
		return $this->restClient->getResource('/v3/hosting-shares', $hostingShareId);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createHostingShare(array $data): array
	{
		return $this->restClient->createHostingShare($data);
	}

	/**
	 * @param int $hostingShareId
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateHostingShare(int $hostingShareId, array $data): array
	{
		return $this->restClient->updateResource('/v3/hosting-shares', $hostingShareId, $data);
	}

	public function deleteHostingShare(int $hostingShareId): void
	{
		$this->restClient->deleteResource('/v3/hosting-shares', $hostingShareId);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getLicenses(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/licenses', $parameters);
	}

	/**
	 * @param int $licenseId
	 * @param mixed[] $parameters
	 * @return mixed[]|null
	 */
	public function getLicense(int $licenseId, array $parameters = []): ?array
	{
		return $this->restClient->getResource('/v3/licenses', $licenseId, $parameters);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createLicense(array $data): array
	{
		return $this->restClient->createResource('/v3/licenses', $data);
	}

	/**
	 * @param int $licenseId
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateLicense(int $licenseId, array $data): array
	{
		return $this->restClient->updateResource('/v3/licenses', $licenseId, $data);
	}

	/**
	 * @param int $serviceId Service ID
	 * @return mixed[]|null
	 */
	public function getService(int $serviceId): ?array
	{
		return $this->restClient->getResource('/v3/services', $serviceId);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getServices(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/services', $parameters);
	}

	/**
	 * @param int $serviceId
	 * @return mixed[]
	 */
	public function enableAutomaticPayments(int $serviceId): array
	{
		return $this->restClient->getSingularResource('/v3/services/' . $serviceId . '/automatic-payments/enable');
	}

	/**
	 * @param int $serviceId
	 * @return mixed[]
	 */
	public function disableAutomaticPayments(int $serviceId): array
	{
		return $this->restClient->getSingularResource('/v3/services/' . $serviceId . '/automatic-payments/disable');
	}

	/**
	 * @param int $authKeyId
	 * @return mixed[]|null
	 */
	public function getAuthKey(int $authKeyId): ?array
	{
		return $this->restClient->getResource('/v3/auth/keys', $authKeyId);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getAuthKeys(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/auth/keys', $parameters);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createAuthKey(array $data): array
	{
		return $this->restClient->createAuthKey($data);
	}

	/**
	 * @param int $customerId
	 * @param string $resetToken
	 * @param string $newPassword
	 * @return mixed[]
	 */
	public function changePassword(int $customerId, string $resetToken, string $newPassword): array
	{
		return $this->restClient->changePassword([
			'customer_id' => $customerId,
			'token' => $resetToken,
			'new_password' => $newPassword,
		]);
	}

	/**
	 * @param string $resetToken
	 * @param string $newPassword
	 * @return mixed[]
	 */
	public function changeMailboxPassword(string $resetToken, string $newPassword): array
	{
		return $this->restClient->changeMailboxPassword([
			'token' => $resetToken,
			'new_password' => $newPassword,
		]);
	}

	/**
	 * @param int $customerId
	 * @param string $key
	 * @return mixed[]
	 */
	public function phoneVerify(int $customerId, string $key): array
	{
		return $this->restClient->phoneVerify($customerId, $key);
	}

	/**
	 * @param int $vatRateId
	 * @return mixed[]|null
	 */
	public function getVatRate(int $vatRateId): ?array
	{
		return $this->restClient->getResource('/v3/vat-rates', $vatRateId);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getVatRates(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/vat-rates', $parameters);
	}

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 */
	public function deleteHosting(int $hostingId, array $parameters = []): void
	{
		$this->restClient->deleteResource('/v3/hostings', $hostingId, $parameters);
	}

	/**
	 * @param int $distributionCodeId
	 * @return mixed[]|null
	 */
	public function getDistributionCode(int $distributionCodeId): ?array
	{
		return $this->restClient->getResource('/v3/distribution-codes', $distributionCodeId);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getDistributionCodes(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/distribution-codes', $parameters);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 * @deprecated V1 API is deprecated
	 */
	public function getEmailsV1(array $parameters): array
	{
		return $this->restClient->getEmails($parameters);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 * @deprecated V1 API is deprecated
	 */
	public function createEmailV1(array $data): array
	{
		return $this->restClient->createEmail($data);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 * @deprecated V1 API is deprecated
	 */
	public function deleteEmailV1(array $parameters): array
	{
		return $this->restClient->deleteEmail($parameters);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getTemplateLicensesV2(array $parameters): array
	{
		return $this->restClient->getResources('/v2/template-licenses/', null, $parameters);
	}

	/**
	 * @param int $id
	 * @return mixed[]|null
	 */
	public function getTemplateLicenseV2(int $id): ?array
	{
		return $this->restClient->getResource('/v2/template-licenses/', $id);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createTemplateLicenseV2(array $data): array
	{
		return $this->restClient->createResource('/v2/template-licenses/', $data);
	}

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateTemplateLicenseV2(int $id, array $data): array
	{
		return $this->restClient->updateResource('/v2/template-licenses/', $id, $data);
	}

	public function deleteTemplateLicenseV2(int $id): void
	{
		$this->restClient->deleteResource('/v2/template-licenses/', $id);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getUsersV2(array $parameters): array
	{
		return $this->restClient->getResources('/v2/users/', null, $parameters);
	}

	/**
	 * @param int $userId
	 * @return mixed[]|null
	 */
	public function getUserV2(int $userId): ?array
	{
		return $this->restClient->getResource('/v2/users/', $userId);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createUserV2(array $data): array
	{
		return $this->restClient->createResource('/v2/users/', $data);
	}

	/**
	 * @param int $userId
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateUserV2(int $userId, array $data): array
	{
		return $this->restClient->updateResource('/v2/users/', $userId, $data);
	}

	public function deleteUserV2(int $userId): void
	{
		$this->restClient->deleteResource('/v2/users/', $userId);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 * @deprecated V1 API is deprecated
	 */
	public function updateEmailPasswordV1(array $data): array
	{
		return $this->restClient->updateEmailPassword($data);
	}

	/**
	 * @param string $domainName
	 * @param string[] $types
	 * @return mixed[]
	 */
	public function getPublicDnsRecords(string $domainName, array $types): array
	{
		return $this->restClient->getSingularResource('/v3/public-dns/', ['domainName' => $domainName, 'types' => $types]);
	}

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function getDnsRecords(int $hostingId): array
	{
		return $this->restClient->getResources('/v3/dns/', null, ['hostingId' => $hostingId]);
	}

	/**
	 * @param int $id
	 * @param int $hostingId
	 * @return mixed[]|null
	 */
	public function getDnsRecord(int $id, int $hostingId): ?array
	{
		return $this->restClient->getResource('/v3/dns/', $id, ['hostingId' => $hostingId]);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createDnsRecord(array $data): array
	{
		return $this->restClient->createDnsRecord($data);
	}

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateDnsRecord(int $id, array $data): array
	{
		return $this->restClient->updateDnsRecord($id, $data);
	}

	/**
	 * @param int $id
	 * @param mixed[] $data
	 */
	public function deleteDnsRecord(int $id, array $data): void
	{
		$this->restClient->deleteResource('/v3/dns/', $id, $data);
	}

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function resetDnsRecord(int $id, array $data = []): array
	{
		return $this->restClient->createResource('/v3/hostings/' . $id . '/reset-dns', $data);
	}

	/** @return mixed[] */
	public function getHostingServers(): array
	{
		return $this->restClient->getSingularResource('/v3/hosting-servers');
	}

	public function cancelOrder(int $id): void
	{
		$this->restClient->cancelOrder($id);
	}

	/**
	 * Creates new order for hosting capacity
	 *
	 * @param mixed[] $data billing information
	 * @return mixed[]
	 */
	public function createDiskQuotaOrder(array $data): array
	{
		return $this->restClient->createDiskQuotaOrder($data);
	}

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getImprovement(int $id, array $data): array
	{
		return $this->restClient->getResource('/v3/improvements/', $id, $data);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]|null
	 */
	public function getImprovements(array $data): ?array
	{
		return $this->restClient->getResources('/v3/improvements/', null, $data);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]|null
	 */
	public function getImprovementVotes(array $data): ?array
	{
		return $this->restClient->getResources('/v3/improvement-votes/', null, $data);
	}

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getImprovementVote(int $id, array $data): array
	{
		return $this->restClient->getResource('/v3/improvement-votes/', $id, $data);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createImprovementVote(array $data): array
	{
		return $this->restClient->createResource('/v3/improvement-votes', $data);
	}

	/** @param mixed[] $data */
	public function deleteImprovementVote(array $data): void
	{
		$this->restClient->deleteImprovementVote($data);
	}

	/**
	 * Creates new hosting-only order
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createHostingOnlyOrder(array $data): array
	{
		return $this->restClient->createHostingOnlyOrder($data);
	}

	/**
	 * Gets hosting-only orders
	 *
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostingOnlyOrders(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/orders/hosting-only', $parameters);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getSubdomains(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/subdomains', $parameters);
	}

	/** @return mixed[] */
	public function getSubdomain(int $id): array
	{
		return $this->restClient->getResource('/v3/subdomains/', $id);
	}

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function checkVatNumber(array $parameters = []): array
	{
		return $this->restClient->getSingularResource('/v3/vat-numbers/check', $parameters);
	}

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function verifyEmail(array $data): array
	{
		return $this->restClient->createResource('/v3/email-verify', $data, HttpStatusCode::S200_OK);
	}

}
