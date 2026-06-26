<?php declare(strict_types=1);

namespace Mioweb\Mailing;

use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\EmailSendingIsDisabledException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\ExternalEmailRequirementsException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\HostingIsDeletedException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\HostingIsNotBoundToThisHostingLicense;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\InvalidDnsSettingsException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\LifetimeLicenseIsNotBoundToAnyLicenseException;
use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\SenderEmailNotMatchWebsiteException;
use Mioweb\MiowebAdminClient\IMiowebAdminPublicClient;

class TransactionalMailRequirementChecker
{

	private const DAY_IN_SECONDS = 60 * 60 * 24;

	private IMiowebAdminPublicClient $mwaPublicClient;

	public function __construct()
	{
		$this->mwaPublicClient = core()->getMwaPublicClient();
	}

	public function getRequirementStatus(bool $recache = false): TransactionalMailRequirementStatus
	{
		$status = $this->getCachedRequirementStatus();

		if ($recache || $status === null) {
			$senderEmail = MWS()->getSenderEmail();
			if (!(bool) $senderEmail) {
				throw new \Exception('Sender email must be set.');
			}

			try {
				$this->mwaPublicClient->checkRequirementsForSendingEmail([
					'url' => get_home_url(),
					'serial_number' => MW()->getLicense()->getNumber(),
					'sender' => [
						'from' => $senderEmail,
					],
				]);

				$status = $this->saveSuccess();
			} catch (ExternalEmailRequirementsException $e) {
				$status = $this->catchException($e);
			}
		}

		return $status;
	}

	public function catchException(ExternalEmailRequirementsException $e): TransactionalMailRequirementStatus
	{
		$status = new TransactionalMailRequirementStatus(false, $e);

		$expiration = $this->getTransientExpiration($e);
		set_transient(TransactionalMailRequirementStatus::TRANSIENT, $status->toArray(), $expiration);

		return $status;
	}

	public function getCachedRequirementStatus(): ?TransactionalMailRequirementStatus
	{
		$transient = get_transient(TransactionalMailRequirementStatus::TRANSIENT);

		return is_array($transient) ? TransactionalMailRequirementStatus::fromTransient($transient) : null;
	}

	public function saveSuccess(): TransactionalMailRequirementStatus
	{
		$status = new TransactionalMailRequirementStatus(true);

		set_transient(TransactionalMailRequirementStatus::TRANSIENT, $status->toArray(), self::DAY_IN_SECONDS);

		return $status;
	}

	/** @TODO */
	private function getTransientExpiration(ExternalEmailRequirementsException $e): int
	{
//		if ($e instanceof EmailSendingIsDisabledException) {
//			$expiration = self::DAY_IN_SECONDS;
//		} elseif ($e instanceof InvalidDnsSettingsException) {
//			$expiration = self::DAY_IN_SECONDS;
//		} elseif ($e instanceof SenderEmailNotMatchWebsiteException) {
//			$expiration = self::DAY_IN_SECONDS;
//		} elseif ($e instanceof HostingIsNotBoundToThisHostingLicense) {
//			$expiration = self::DAY_IN_SECONDS;
//		} elseif ($e instanceof HostingIsDeletedException) {
//			$expiration = self::DAY_IN_SECONDS;
//		} elseif ($e instanceof LifetimeLicenseIsNotBoundToAnyLicenseException) {
//			$expiration = self::DAY_IN_SECONDS;
//		} else {
		$expiration = self::DAY_IN_SECONDS;
//		}

		return $expiration;
	}

}
