<?php declare(strict_types=1);

namespace Mioweb\Mailing;

use Mioweb\Mailing\Exceptions\TooManyRecipientsException;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Mail\MimePart;
use Nette\Mail\SendException;
use PHPMailer\PHPMailer\PHPMailer;

class WordpressMailer implements Mailer
{

	private const MAX_TO_ADDRESSES = 5;

	/**
	 * @throws SendException
	 * @throws TooManyRecipientsException
	 */
	public function send(Message $mail): void
	{
		$this->setDefaultHeaders($mail);
		$to = $this->setTo($mail);
		$this->setFrom($mail);
		$attachments = $this->parseAttachments($mail);
		$headers = $this->parseHeaders($mail);

		$success = $this->wpMail($to, $mail, $headers, $attachments);

		if (!$success) {
			// Try with mail() function instead if SMTP fails
			global $phpmailer;
			if ($phpmailer instanceof PHPMailer && $phpmailer->Mailer === 'smtp') {
				add_action('phpmailer_init', [$this, 'disable_smtp'], 100);
				$success = $this->wpMail($to, $mail, $headers, $attachments);

				if ($success) {
					return;
				}
			}

			throw new SendException('wp_mail returned false.');
		}
	}

	public function disable_smtp(PHPMailer $phpMailer): void
	{
		$phpMailer->Mailer = 'mail';
	}

	/** @return string[] */
	private function parseHeaders(Message $mail): array
	{
		$headers = [];
		foreach ($mail->getHeaders() as $name => $value) {
			if (is_string($value) && !in_array(strtolower($name), ['subject', 'date'], true)) {
				$headers[] = $name . ': ' . $value;
			}
		}

		return $headers;
	}

	/** @return string[] */
	private function parseAttachments(Message $mail): array
	{
		return array_map(function (MimePart $attachment): string {
			return $attachment->getHeader('file') ?? '';
		}, $mail->getAttachments());
	}

	private function setFrom(Message $mail): void
	{
		$isSmtpEnabled = MW()->is_smtp_enabled();

		$from = $mail->getFrom();
		$fromName = reset($from);
		$fromEmail = key($from);

		if (!$isSmtpEnabled) {
			$mail->setHeader('From', sprintf('%s <%s>', $fromName, $fromEmail));
		}

		$replyTos = $mail->getHeader('Reply-To') ?? [$fromEmail];
		if (is_string($replyTos)) {
			$replyTos = [$replyTos];
		}
		$mail->setHeader('Reply-To', implode(',', $replyTos));
	}

	/**
	 * @return string[]
	 * @throws TooManyRecipientsException
	 */
	private function setTo(Message $mail): array
	{
		$to = array_keys($mail->getHeader('To') ?? []);

		if (count($to) > self::MAX_TO_ADDRESSES) {
			throw new TooManyRecipientsException(self::MAX_TO_ADDRESSES);
		}

		$mail->clearHeader('To');

		return $to;
	}

	private function setDefaultHeaders(Message $mail): void
	{
		$mail->setHeader('X-Mailer', 'WordPress');
	}

	/**
	 * @param string[]|string $to
	 * @param mixed[] $headers
	 * @param mixed[] $attachments
	 */
	private function wpMail(array|string $to, Message $mail, array $headers, array $attachments): bool
	{
		return wp_mail($to, $mail->getSubject(), $mail->getHtmlBody(), $headers, $attachments);
	}

}
