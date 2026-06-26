<?php

namespace Mioweb\Member;

use mwMember;
use MwVariables;

class MembershipEmailing
{

	public static function sendEmails(mwMember $member, array $saved, ?string $password = null, ?string $message = null, ?string $subject = null): void
	{
		if ($message !== null) {
			$memberSectionId = null;
			if (count($saved['updated']) === 1 && !isset($saved['added'])) {
				$memberSectionId = array_key_first($saved['updated']);
			} elseif (count($saved['added']) === 1 && !isset($saved['updated'])) {
				$memberSectionId = array_key_first($saved['added']);
			}
			self::sendMembershipEmail($member, $subject ?: '', $message, $password, $saved, $memberSectionId);
		} else {
			// new memberships
			foreach ($saved['added'] as $memberSectionId => $addedData) {
				self::sendMemberSectionEmail($member, $memberSectionId, $saved, $password, 'addMembership');
			}

			// updated memberships
			if (isset($saved['updated']) && count($saved['updated'])) {
				// if is updated only one membership
				if (count($saved['updated']) === 1) {
					$memberSectionId = array_key_first($saved['updated']);
					$updated = $saved['updated'][$memberSectionId];

					// only one aspect is updated
					if (count($updated) === 1) {
						// levels are updated
						if (isset($updated['levels'])) {
							self::sendMemberSectionEmail($member, $memberSectionId, $saved, $password, 'addLevel');
						} elseif (isset($updated['months'])) {
							// months are updated
							self::sendMemberSectionEmail($member, $memberSectionId, $saved, $password, 'addMonths');
						} elseif (array_key_exists('expiration', $updated)) {
							// membership expiration is extend
							self::sendMemberSectionEmail($member, $memberSectionId, $saved, $password, 'extendMembership');
						}
					} else {
						// more aspects are updated
						self::sendDefaultEmail($member, $memberSectionId, $saved, $password, 'updateMembership');
					}
				} else {
					// if is updated more memberships
					self::sendDefaultEmail($member, null, $saved, $password, 'updateMemberships');
				}
			}
		}
	}

	private static function sendMemberSectionEmail(mwMember $member, int $memberSectionId, array $saved, ?string $password, string $email = 'addMembership')
	{
		$memberSection = mwMemberModule()->getMemberSection($memberSectionId);

		// get member section email
		$message = $memberSection->getEmail($email)->getText();
		$subject = $memberSection->getEmail($email)->getSubject();

		// default email
		$message = $message ?: self::getDefaultMessage($email);
		$subject = $subject ?: self::getDefaultSubject($email);

		self::sendMembershipEmail($member, $subject, $message, $password, $saved, $memberSectionId);
	}

	private static function sendDefaultEmail(mwMember $member, ?int $memberSectionId, array $saved, ?string $password, string $email = 'addMembership')
	{
		// default email
		$message = self::getDefaultMessage($email);
		$subject = self::getDefaultSubject($email);

		self::sendMembershipEmail($member, $subject, $message, $password, $saved, $memberSectionId);
	}

	public static function sendMembershipEmail(mwMember $member, string $subject, string $message, ?string $password = null, array $saved = [], ?int $memberSectionId = null): bool
	{
		//replace variables
		$variables = MembershipVariables::getMembershipVariables($member, $password, $saved, $memberSectionId);
		$message = MembershipVariables::replaceVariables($message, $variables);
		$subject = MembershipVariables::replaceVariables($subject, $variables);
		$message = nl2br($message);

		return self::sendEmail($member->getEmail(), $subject, $message);
	}

	public static function sendEmail(string $email, string $subject, string $message): bool
	{
		$webName = str_replace('&amp;', '&', get_bloginfo('name'));

		return miowebMailer()->send($email, get_bloginfo('admin_email'), $webName, $subject, $message);
		/*
		$headers = ['Content-Type: text/html; charset=UTF-8', 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'];
		$sended = wp_mail($email, $subject, $message, $headers);

		if (!$sended) {
			global $phpmailer;

			$mail_error = ' (Nefunkční funkce mail.)';

			if (isset($phpmailer)) {
				$mail_error = ' (' . $phpmailer->ErrorInfo . ')';
			}

			mwlog(MWLS_GENERAL, __('Zpráva se nepodařila odeslat. Email:', 'cms') . $mail_error, MWLL_ERROR);
		}

		return $sended;*/
	}

	public static function getDefaultSubject(string $type): string
	{
		$messages = [
			'addMembership' => __('Nové členství', 'cms_member'),
			'addLevel' => __('Přidány přístupy do úrovně', 'cms_member'),
			'addMonths' => __('Prodloužení členství', 'cms_member'),
			'extendMembership' => __('Prodloužení členství', 'cms_member'),
			'updateMembership' => __('Změna členství', 'cms_member'),
			'updateMemberships' => __('Změna členství', 'cms_member'),
		];

		return $messages[$type] ?? '';
	}

	public static function getDefaultMessage(string $type): string
	{
		$messages = [

			// add new membership
			'addMembership' => __('Dobrý den,', 'cms_member')
				. "\r\n\r\n"
				. __('byl Vám vytvořen přístup do členské sekce <strong>%%NAZEV_CLENSKE_SEKCE%%</strong>.', 'cms_member')
				. "\r\n\r\n"
				. '<strong>' . __('Informace o Vašem členství', 'cms_member') . '</strong>'
				. "\r\n"
				. '%%INFORMACE_O_CLENSTVI%%'
				. "\r\n"
				. '<strong>' . __('Přihlašovací údaje', 'cms_member') . '</strong>'
				. "\r\n"
				. '%%PRIHLASOVACI_UDAJE%%',

			// add new level(s)
			'addLevel' => __('Dobrý den,', 'cms_member')
				. "\r\n\r\n"
				. __('byl Vám povolen přístup do úrovně <strong>%%NAZEV_UROVNE%%</strong>.', 'cms_member')
				. "\r\n\r\n"
				. __('Přihlásit se můžete na: %%PRIHLASOVACI_STRANKA%%', 'cms_member'),

			// add new month(s)
			'addMonths' => __('Dobrý den,', 'cms_member')
				. "\r\n\r\n"
				. __('Vaše měsíční členství bylo rozšířeno. Nově máte přístup do: %%MESICNI_CLENSTVI%%', 'cms_member')
				. "\r\n\r\n"
				. __('Přihlásit se můžete na: %%PRIHLASOVACI_STRANKA%%', 'cms_member'),

			// extend membership
			'extendMembership' => __('Dobrý den,', 'cms_member')
				. "\r\n\r\n"
				. __('bylo Vám prodlouženo členství v členské sekci <strong>%%NAZEV_CLENSKE_SEKCE%%</strong>.', 'cms_member')
				. "\r\n"
				. __('Vaše členství nově vyprší: <strong>%%DATUM_EXPIRACE%%</strong>.', 'cms_member')
				. "\r\n\r\n"
				. __('Přihlásit se můžete na: %%PRIHLASOVACI_STRANKA%%', 'cms_member'),

			// update section
			'updateMembership' => __('Dobrý den,', 'cms_member')
				. "\r\n\r\n"
				. __('Vaše členství v členské sekci <strong>%%NAZEV_CLENSKE_SEKCE%%</strong> bylo změněno.', 'cms_member')
				. "\r\n"
				. '%%UPDATE_INFO%%'
				. "\r\n"
				. __('Přihlásit se můžete na: %%PRIHLASOVACI_STRANKA%%', 'cms_member'),

			// update multiple membership
			'updateMemberships' => __('Dobrý den,', 'cms_member')
				. "\r\n\r\n"
				. '%%UPDATE_INFO%%',

			// import
			'import' => __('Dobrý den,', 'cms_member')
				. "\r\n\r\n"
				. __('byl Vám vytvořen přístup do členské sekce.', 'cms_member')
				. "\r\n\r\n"
				. __('Zde můžete napsat informace, které chcete klientovi předat.', 'cms_member')
				. "\r\n\r\n"
				. '<strong>' . __('Přihlašovací údaje', 'cms_member') . '</strong>'
				. "\r\n"
				. '%%PRIHLASOVACI_UDAJE%%',

		];

		return $messages[$type] ?? '';
	}

}
