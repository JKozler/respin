<?php declare(strict_types=1);

namespace Mioweb\Mailing;

use Mioweb\MiowebAdminClient\Exceptions\ExternalEmails\ExternalEmailRequirementsException;
use Mioweb\MiowebAdminClient\IMiowebAdminPublicClient;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Utils\Strings;

class TransactionalMailer implements Mailer
{

	private IMiowebAdminPublicClient $mwaPublicClient;

	private TransactionalMailRequirementChecker $requirementChecker;

	public function __construct()
	{
		$this->mwaPublicClient = core()->getMwaPublicClient();
		$this->requirementChecker = new TransactionalMailRequirementChecker();
	}

	/** @throws SendException */
	public function send(Message $mail): void
	{
		$status = $this->requirementChecker->getCachedRequirementStatus();

		if ($status !== null && !$status->isAvailable()) {
			throw new SendException('Transactional e-mails are unavailable for this domain.', 0, $status->getException());
		}

		$from = $mail->getFrom();
		if (!(bool) $from) {
			throw new SendException('From header is empty.');
		}

		$to = $mail->getHeader('To');
		if (!(bool) $to) {
			throw new SendException('To header is empty.');
		}

		$recipients = [];
		foreach ($to as $email => $name) {
			$recipients[] = [
				'email' => $email,
				'name' => $name,
			];
		}

		$attachments = $this->parseAttachments($mail);

		$mailBody = $mail->getHtmlBody() ?? '';
		$contentType = $mail->getHeader('Content-Type');

		if (!is_string($contentType) || !Strings::startsWith($contentType, \Mioweb\Mailing\Mailer::CONTENT_TYPE_HTML)) {
			$mailBody = $this->sanitizeHtml($mailBody);
		}

		try {
			$response = $this->mwaPublicClient->sendEmail([
				'url' => get_home_url(),
				'serial_number' => MW()->getLicense()->getNumber(),
				'sender' => [
					'from' => key($from),
					'name' => reset($from),
				],
				'recipients' => $recipients,
				'message_contents' => [
					'subject' => $mail->getSubject() ?? '',
					'html_body' => $mailBody,
				],
				'attachments' => $attachments,
			]);

			$this->requirementChecker->saveSuccess();
		} catch (ExternalEmailRequirementsException $e) {
			$this->requirementChecker->catchException($e);

			throw new SendException('Transactional e-mail error: ' . $e->getMessage(), 0, $e);
		} catch (\Throwable $e) {
			throw new SendException('Error while sending transactional e-mail', 0, $e);
		}

		$allFailed = true;
		foreach ($response['recipients'] as $recipient) {
			if (($recipient['status'] ?? null) === EmailStatus::CREATED) {
				$allFailed = false;

				break;

//			} else {
// 				TODO log
			}
		}

		if ($allFailed) {
			throw new SendException('Transactional e-mail error: All recipients skipped or failed.');
		}
	}

	private function sanitizeHtml(string $content): string
	{
		$content = htmlspecialchars(strip_tags($content), ENT_QUOTES, 'UTF-8');

		return str_replace("\n", '<br>', $content);
	}

	/** @return string[] */
	private function parseAttachments(Message $mail): array
	{
		$result = [];

		foreach ($mail->getAttachments() as $attachment) {
			$contentDisposition = $attachment->getHeader('Content-Disposition');
			if ($contentDisposition === null) {
				continue;
			}

			$parts = explode('; ', $contentDisposition);
			if (count($parts) < 2) {
				continue;
			}

			$fileName = Strings::trim(Strings::after($parts[1], 'filename='), '"');

			$result[] = [
				'file_name' => $fileName,
				'content_type' => $attachment->getHeader('Content-Type'),
				'data_base64' => base64_encode($attachment->getBody()),
			];
		}

		return $result;
	}

}
