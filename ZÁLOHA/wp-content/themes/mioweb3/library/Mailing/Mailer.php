<?php declare(strict_types=1);

namespace Mioweb\Mailing;

use Nette\Mail\Message;
use Nette\Mail\SendException;

class Mailer
{

	public const CONTENT_TYPE_HTML = 'text/html';

	public const CONTENT_TYPE_TEXT = 'text/plain';

	/** @param string[]|string $to */
	public function send(
		array|string $to,
		string $senderEmail,
		string $senderName,
		string $subject,
		string $body,
		?string $replyTo = null,
		array $attachments = [],
		string $contentType = self::CONTENT_TYPE_HTML
): bool
	{
		$mail = new Message();
		$mail->setContentType($contentType, 'UTF-8');

		$mail->setFrom($senderEmail, $senderName);
		$replyTo ??= $senderEmail;
		$mail->addReplyTo($replyTo, $replyTo);

		$mail->setSubject($subject);
		$mail->setHtmlBody($body);

		if (is_string($to)) {
			$to = [$to];
		}

		foreach ($to as $toAddress) {
			$mail->addTo($toAddress);
		}

		foreach ($attachments as $attachment) {
			$mail->addAttachment($attachment)->setHeader('file', $attachment);
		}

		try {
			transactionalMailer()->send($mail);
		} catch (SendException $_) {
			return false;
		}

		return true;
	}

}
