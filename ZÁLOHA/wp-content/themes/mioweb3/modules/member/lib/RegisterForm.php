<?php

namespace Mioweb\Member;

use mwMember;

class RegisterForm
{

	public static function sendRegisterForm()
	{
		if (! isset($_POST['mw_wpnonce_free_reg']) || ! wp_verify_nonce($_POST['mw_wpnonce_free_reg'], 'member_free_registration_nonce')) {
			self::returnError(__('Ověřovací kód není platný nebo jeho platnost vypršela, obnovte stránku a zkuste to znovu.', 'cms_member'));
		}

		$info = unserialize(base64_decode($_POST['member_free_registration']));

		// missing email
		if (!$_POST['user_email']) {
			self::returnError(__('Musíte zadat svůj e-mail!', 'cms_member'));
		}

		$user = mwMember::getOneByEmail($_POST['user_email']);

		// email already exist
		if (!isset($info['update']) && $user !== null) {
			self::returnError(__('Uživatel s touto e-mailovou adresou již existuje!', 'cms_member'));

		// password
		} elseif (!isset($info['generate_password'])) {
			if (!$_POST['user_password'] || !$_POST['user_password2']) {
				self::returnError(__('Musíte zadat heslo a potvrzení hesla!', 'cms_member'));
			} elseif ($_POST['user_password'] != $_POST['user_password2']) {
				self::returnError(__('Heslo a potvrzení hesla se neshoduje!', 'cms_member'));
			}
		}

		$redirectUrl = $_POST['member_registration_redirect'] ?? '';

		$memberSection = mwMemberModule()->getMemberSection($info['id']);

		// member section dont exist
		if ($memberSection === null) {
			self::returnError(__('Členská sekce neexistuje.', 'cms_member'));
		}

		// info hash name is wrong
		if ($memberSection->getName() !== $info['name']) {
			self::returnError(__('Neoprávněná registrace!', 'cms_member'));
		}

		$client = [
			'user_email' => $_POST['user_email'],
			'password' => isset($info['generate_password']) ? wp_generate_password() : $_POST['user_password'],
			'first_name' => $_POST['first_name'] ?? null,
			'last_name' => $_POST['last_name'] ?? null,
		];

		$params = [
			'send_email' => !isset($info['no_email']),
		];

		if (isset($info['level'])) {
			$params['levels'] = [];
			foreach ($info['level'] as $lkey => $lval) {
				$params['levels'][] = $lkey;
			}
		}

		if (isset($info['days'])) {
			$params['days'] = $info['days'];

			if (isset($info['update'])) {
				if ($user !== null && $user->hasAccess($memberSection->getId()) && $user->getMembership($memberSection->getId())->getEnd()) {
					$params['days'] = 0;
				}
			}
		}

		$accept = $_POST['gdpr_accept'] ?? null;

		$return = Notifications::addMembership($client, $memberSection->getId(), $params, false, 'free_registration', $accept);

		if ($return['user_changed']) {
			// add to SE
			if (isset($info['se'])) {
				// back compatibility (temporary)
				$info['se'] = mwEmailingApi()->repair_content_val($info['se']);
				// back compatibility end

				if ($info['se']['id']) {
					$apiItem = mwApiConnect()->getApi($info['se']['api']);

					$fields = ['name' => $_POST['first_name'] ?? null, 'surname' => $_POST['last_name'] ?? null];
					if (isset($_POST['field'])) {
						$fields = array_merge($fields, $_POST['field']);
					}

					$custom_fields = $_POST['custom_field'] ?? [];

					$apiItem->client()->save_to_list_details($info['se']['id'], $_POST['user_email'], $info['se']['purpose'] ?? null, $fields, $custom_fields);
				}
			}

			$mail_sended = false;
			if (isset($info['email']) && $info['email']) {
				$webName = str_replace('&amp;', '&', get_bloginfo('name'));
				$subject = __('Nová registrace zdarma na ', 'cms_member') . $webName;
				$message = __('Byla vytvořeno nebo upraveno členství skrz formulář pro registraci zdarma pro uživatele s e-mailovou adresou: ', 'cms_member') . $_POST['user_email']
					. '<br><br>'
					. '<a href="' . MwSetting()->getObject('members')->getEditUrl($return['user_id']) . '">' . __('Zobrazit detail člena', 'cms_member') . '</a>';

				$mail_sended = MembershipEmailing::sendEmail($info['email'], $subject, $message);
			}

			if ($redirectUrl) {
				$redirectUrl = add_query_arg('mid', $return['user_id'], $redirectUrl);
			}
		} else {
			self::returnError(sprintf(__('Vaše členství již existuje. Přihlásit se můžete na <a href="%s" target="_blank">přihlašovací stránce</a>.', 'cms_member'), $memberSection->getLoginUrl()));
			$redirectUrl = '';
		}

		wp_send_json([
			'sended' => 'ok',
			'message' => __('Registrace byla úspěšná. Zkontrolujte svou emailovou schránku.', 'cms_member'),
			'redirect' => $redirectUrl,
			'target' => isset($_POST['member_registration_redirect_target']) ? 'parent' : '',
			'return' => $return,
		]);

		die();
	}

	private static function returnError(string $message): void
	{
		wp_send_json([
			'sended' => 'error',
			'message' => $message,
		]);
		die();
	}

}
