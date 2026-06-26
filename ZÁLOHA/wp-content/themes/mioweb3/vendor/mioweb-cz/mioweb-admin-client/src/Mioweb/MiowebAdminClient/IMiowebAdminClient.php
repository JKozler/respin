<?php declare(strict_types=1);

namespace Mioweb\MiowebAdminClient;

use Mioweb\MiowebAdminClient\Exceptions\EmailAlreadyTakenException;

interface IMiowebAdminClient
{

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function extendDomain(array $data): array;

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
	): array;

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function enableHttps(int $hostingId): array;

	/**
	 * Migrate hosting to different server.
	 *
	 * @param int $hostingId
	 * @param int|null $serverId
	 * @param \DateTimeInterface|null $processAt
	 * @return mixed[]
	 */
	public function migrate(int $hostingId, ?int $serverId = null, ?\DateTimeInterface $processAt = null): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostings(array $parameters = []): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]|null
	 */
	public function getBillingStatistics(array $parameters = []): ?array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function checkDomain(array $parameters): array;

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function fireCreationOnHosting(int $hostingId, array $parameters): array;

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function suspendHosting(int $hostingId): array;

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function unSuspendHosting(int $hostingId): array;

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 * @return mixed[]|null
	 */
	public function getHosting(int $hostingId, array $parameters = []): ?array;

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 * @return mixed[]|null
	 */
	public function getHostingInfo(int $hostingId, array $parameters = []): ?array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createHosting(array $data): array;

	/**
	 * @param int $hostingId
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateHosting(int $hostingId, array $data): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostingShares(array $parameters = []): array;

	/**
	 * @param int $hostingShareId
	 * @return mixed[]|null
	 */
	public function getHostingShare(int $hostingShareId): ?array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createHostingShare(array $data): array;

	/**
	 * @param int $hostingShareId
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateHostingShare(int $hostingShareId, array $data): array;

	/** @param int $hostingShareId HostingShare ID */
	public function deleteHostingShare(int $hostingShareId): void;

	/**
	 * Get customers. Filtering available.
	 *
	 * @param mixed[] $parameters Available filters, for unique values: "email", "billingUserId". Optional "limit=600", "offset".
	 * @return mixed[]
	 */
	public function getCustomers(array $parameters = []): array;

	/**
	 * Get one customer.
	 *
	 * @param int $customerId
	 * @param mixed[] $parameters
	 * @return mixed[]|null
	 */
	public function getCustomer(int $customerId, array $parameters = []): ?array;

	/**
	 * Create new customer. Billing user ID is automatically loaded from Billing API by corresponding email.
	 *
	 * @param mixed[] $data "email", "is_agency", "support_expiration_date=null", "language=null"
	 * @return mixed[]
	 */
	public function createCustomer(array $data): array;

	/**
	 * @param int $customerId Customer ID
	 * @param mixed[] $data "billing_user_id", "is_agency", "support_expiration_date=null", "language=null"
	 * @return mixed[]
	 */
	public function updateCustomer(int $customerId, array $data): array;

	/**
	 * @param int $customerId Customer ID
	 * @param mixed[] $parameters
	 */
	public function deleteCustomer(int $customerId, array $parameters = []): void;

	/**
	 * @param mixed[] $data "email"
	 * @return bool
	 */
	public function isEmailAvailableForCustomer(array $data): bool;

	/**
	 * @param int $customerId Customer ID
	 * @param mixed[] $parameters "tdl", "domain"
	 * @return mixed[]
	 */
	public function getCustomerDomainContact(int $customerId, array $parameters): ?array;

	/**
	 * @param mixed[] $data "customer_id", "domain", "birthdate", "company"
	 * @return mixed[]
	 */
	public function createCustomerDomainContact(array $data): array;

	/**
	 * @param mixed[] $parameters Available filters, for unique values: "email". Optional "limit=600", "offset".
	 * @return mixed[]
	 */
	public function getLicensePackages(array $parameters = []): array;

	/**
	 * Get one license package.
	 *
	 * @param int $licensePackageId
	 * @return mixed[]|null
	 */
	public function getLicensePackage(int $licensePackageId): ?array;

	/**
	 * @param mixed[] $data
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function createLicensePackage(array $data, array $parameters = []): array;

	/**
	 * @param int $licensePackageId
	 * @param mixed[] $data
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function updateLicensePackage(int $licensePackageId, array $data, array $parameters = []): array;

	/**
	 * @param int $hostingId
	 * @param mixed[] $data
	 * @param int|null $licenseId
	 * @return mixed[]
	 */
	public function createHostingPending(int $hostingId, array $data, ?int $licenseId = null): array;

	public function deleteHostingPending(int $hostingId, string $kind): void;

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostingPendings(int $hostingId, array $parameters = []): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getLicenses(array $parameters = []): array;

	/**
	 * @param int $licenseId
	 * @param mixed[] $parameters
	 * @return mixed[]|null
	 */
	public function getLicense(int $licenseId, array $parameters = []): ?array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createLicense(array $data): array;

	/**
	 * @param int $licenseId
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateLicense(int $licenseId, array $data): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 * @deprecated V1 API is deprecated
	 */
	public function getEmailsV1(array $parameters): array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 * @throws EmailAlreadyTakenException
	 * @deprecated V1 API is deprecated
	 */
	public function createEmailV1(array $data): array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 * @deprecated V1 API is deprecated
	 */
	public function updateEmailPasswordV1(array $data): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 * @deprecated V1 API is deprecated
	 */
	public function deleteEmailV1(array $parameters): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getTemplateLicensesV2(array $parameters): array;

	/**
	 * @param int $id
	 * @return mixed[]|null
	 */
	public function getTemplateLicenseV2(int $id): ?array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createTemplateLicenseV2(array $data): array;

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateTemplateLicenseV2(int $id, array $data): array;

	public function deleteTemplateLicenseV2(int $id): void;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getUsersV2(array $parameters): array;

	/**
	 * @param int $userId
	 * @return mixed[]|null
	 */
	public function getUserV2(int $userId): ?array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createUserV2(array $data): array;

	/**
	 * @param int $userId
	 * @param mixed[] $data
	 * @return mixed[]|null
	 */
	public function updateUserV2(int $userId, array $data): ?array;

	public function deleteUserV2(int $userId): void;

	/**
	 * Gets MW releases
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getMwReleases(array $data): array;

	/**
	 * @param int $orderId
	 * @return mixed[]|null
	 */
	public function getOrder(int $orderId, bool $includeData = false, bool $includeFapiData = false): ?array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getOrders(array $parameters = []): array;

	/**
	 * @param int $orderSourceId
	 * @return mixed[]|null
	 */
	public function getOrderSource(int $orderSourceId): ?array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getOrderSources(array $parameters = []): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostingFullOrders(array $parameters = []): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getDomainOrders(array $parameters = []): array;

	/**
	 * @param int $orderId
	 * @return mixed[]|null
	 */
	public function getHostingFullOrder(int $orderId): ?array;

	/**
	 * @param int $orderId
	 * @return mixed[]|null
	 */
	public function getSupportOrder(int $orderId): ?array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getSupportOrders(array $parameters = []): array;

	/**
	 * @param int $priceId
	 * @return mixed[]|null
	 */
	public function getPrice(int $priceId): ?array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getPrices(array $parameters = []): array;

	/**
	 * Gets prices for hosting-full
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getFullHostingPrices(array $data): array;

	/**
	 * Gets prices for multi plus
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getMultiPlusPrices(array $data): array;

	/**
	 * Gets prices for disk quota
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getDiskQuotaPrices(array $data): array;

	/**
	 * Gets mapping for multi plus tariffs
	 *
	 * @return mixed[]
	 */
	public function getMultiPlusMapping(): array;

	/**
	 * Gets prices for hosting-only
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getHostingOnlyPrices(array $data): array;

	/**
	 * Gets price for domain service
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getDomainPrices(array $data): array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getSupportPrices(array $data = []): array;

	/**
	 * Creates new PaA order
	 *
	 * @param mixed[] $data billing information
	 * @return mixed[]
	 */
	public function createSupportOrder(array $data): array;

	/**
	 * Creates new hosting-full order
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createFullHostingOrder(array $data): array;

	/**
	 * Creates new multi-plus order
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createMultiPlusOrder(array $data): array;

	/**
	 * Creates new domain order
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createDomainOrder(array $data): array;

	/**
	 * Creates new hosting-only order
	 *
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createHostingOnlyOrder(array $data): array;

	/**
	 * @param int $serviceId Service ID
	 * @return mixed[]|null
	 */
	public function getService(int $serviceId): ?array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getServices(array $parameters = []): array;

	/**
	 * @param int $serviceId
	 * @return mixed[]
	 */
	public function enableAutomaticPayments(int $serviceId): array;

	/**
	 * @param int $serviceId
	 * @return mixed[]
	 */
	public function disableAutomaticPayments(int $serviceId): array;

	/**
	 * @param int $authKeyId
	 * @return mixed[]|null
	 */
	public function getAuthKey(int $authKeyId): ?array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getAuthKeys(array $parameters = []): array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createAuthKey(array $data): array;

	/**
	 * @param int $customerId
	 * @param string $resetToken
	 * @param string $newPassword
	 * @return mixed[]
	 */
	public function changePassword(int $customerId, string $resetToken, string $newPassword): array;

	/**
	 * @param string $resetToken
	 * @param string $newPassword
	 * @return mixed[]
	 */
	public function changeMailboxPassword(string $resetToken, string $newPassword): array;

	/**
	 * @param int $customerId
	 * @param string $key
	 * @return mixed[]
	 */
	public function phoneVerify(int $customerId, string $key): array;

	/**
	 * @param int $vatRateId
	 * @return mixed[]|null
	 */
	public function getVatRate(int $vatRateId): ?array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getVatRates(array $parameters = []): array;

	/**
	 * @param int $hostingId
	 * @param mixed[] $parameters
	 */
	public function deleteHosting(int $hostingId, array $parameters = []): void;

	/**
	 * @param int $distributionCodeId
	 * @return mixed[]|null
	 */
	public function getDistributionCode(int $distributionCodeId): ?array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getDistributionCodes(array $parameters = []): array;

	/** @return mixed[] */
	public function getPriceListTypes(): array;

	/**
	 * @param string $domainName
	 * @param string[] $types
	 * @return mixed[]
	 */
	public function getPublicDnsRecords(string $domainName, array $types): array;

	/**
	 * @param int $hostingId
	 * @return mixed[]
	 */
	public function getDnsRecords(int $hostingId): array;

	/**
	 * @param int $id
	 * @param int $hostingId
	 * @return mixed[]|null
	 */
	public function getDnsRecord(int $id, int $hostingId): ?array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createDnsRecord(array $data): array;

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function updateDnsRecord(int $id, array $data): array;

	/**
	 * @param int $id
	 * @param mixed[] $data
	 */
	public function deleteDnsRecord(int $id, array $data): void;

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function resetDnsRecord(int $id, array $data = []): array;

	/** @return mixed[] */
	public function getHostingServers(): array;

	public function cancelOrder(int $id): void;

	/**
	 * Creates new order for hosting capacity
	 *
	 * @param mixed[] $data billing information
	 * @return mixed[]
	 */
	public function createDiskQuotaOrder(array $data): array;

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getImprovement(int $id, array $data): array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]|null
	 */
	public function getImprovements(array $data): ?array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]|null
	 */
	public function getImprovementVotes(array $data): ?array;

	/**
	 * @param int $id
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function getImprovementVote(int $id, array $data): array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function createImprovementVote(array $data): array;

	/** @param mixed[] $data */
	public function deleteImprovementVote(array $data): void;

	/**
	 * Gets hosting-only orders
	 *
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getHostingOnlyOrders(array $parameters = []): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function getSubdomains(array $parameters = []): array;

	/** @return mixed[] */
	public function getSubdomain(int $id): array;

	/**
	 * @param mixed[] $parameters
	 * @return mixed[]
	 */
	public function checkVatNumber(array $parameters = []): array;

	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public function verifyEmail(array $data): array;

}
