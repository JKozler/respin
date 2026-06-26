<?php declare(strict_types=1);

namespace Mioweb\Mailing;

use Nette\Mail\FallbackMailer;

class MiowebMailerFactory
{

	private static ?WordpressMailer $wordpressMailer = null;

	private static ?FallbackMailer $transactionalMailer = null;

	private static ?Mailer $mailer = null;

	public static function getTransactional(): FallbackMailer
	{
		if (self::$transactionalMailer === null) {
			self::$transactionalMailer = new FallbackMailer([new TransactionalMailer(), self::getWordpress()], 1, 100);
		}

		return self::$transactionalMailer;
	}

	public static function getWordpress(): WordpressMailer
	{
		if (self::$wordpressMailer === null) {
			self::$wordpressMailer = new WordpressMailer();
		}

		return self::$wordpressMailer;
	}

	public static function getMailer(): Mailer
	{
		if (self::$mailer === null) {
			self::$mailer = new Mailer();
		}

		return self::$mailer;
	}

}
