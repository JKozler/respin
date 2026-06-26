<?php

namespace Mioweb\Member;

use mwMember;

class MembersImport
{

	public static function importSet()
	{
		$currentPage = mwSetting()->page();

		$currentPage->printTitle();

		echo '<form action="" class="mw_setting_form">';

		write_meta($currentPage->getSetting(), [], '', '');

		echo '<input type="hidden" name="setting_id" value="' . $currentPage->getId() . '"/>';
		wp_nonce_field('import_new_member_user', 'import_new_member_user');

		echo mwSetting()->saveBar(__('Importovat členy', 'cms_member'), 'mw_member_import_but');

		echo '</form>';
	}

	public static function importMembers_ajax()
	{
		$members = false;
		foreach ($_POST['member'] as $val) {
			if (isset($val['section'])) {
				$members = true;
			}
		}

		if (!mwSetting()->verifyNonce('import_new_member_user')) {
			mwMessages()->error(__('Nastavení se nepodařilo uložit.', 'cms'));
		} elseif (empty($_POST['emails'])) {
			mwMessages()->error(__('Musíte zadat seznam e-mailů, pro které chcete vytvořit členské účty.', 'cms_member'));
		} elseif (!$members) {
			mwMessages()->error(__('Musíte vybrat alespoň jednu členskou sekci, do které chcete nové členy zařadit.', 'cms_member'));
		} elseif (isset($_POST['send_mail']) && isset($_POST['send_custom_mail']) && !$_POST['email']['content']) {
			mwMessages()->error(__('Musíte vyplnit text e-mailu.', 'cms_member'));
		} elseif (isset($_POST['send_mail']) && isset($_POST['send_custom_mail']) && strpos($_POST['email']['content'], '%%LOGIN%%') === false && strpos($_POST['email']['content'], '%%PRIHLASOVACI_UDAJE%%') === false) {
			mwMessages()->error(__('Text e-mailové zprávy musí obsahovat proměnnou %%PRIHLASOVACI_UDAJE%%.', 'cms_member'));
		}

		$added = 0;
		$updated = 0;
		$notUpdated = 0;

		if (mwMessages()->success) {
			$sendMail = isset($_POST['send_mail']);
			$emailText = isset($_POST['send_custom_mail']) ? $_POST['email']['content'] : null;
			$emailSubject = isset($_POST['send_custom_mail']) ? $_POST['email']['subject'] ?? null : null;

			foreach (explode("\n", $_POST['emails']) as $data) {
				$email = trim($data);
				$user = mwMember::getOneByEmail($email);

				if ($user === null) {
					$membershipsData = mwMember::createMembershipsDataFromMembersField($_POST['member'] ?? []);

					$newUserData['user_email'] = $email;
					mwMember::addMember($newUserData, $membershipsData, $sendMail, $emailText, $emailSubject, 'by_import');

					$added++;
				} else {
					$membershipsData = $user->updateMembershipsFromMembersField($_POST['member']);
					$saved = $user->saveMemberships($membershipsData, $sendMail, $emailText, $emailSubject);

					if (count($saved['added']) || count($saved['updated'])) {
						$updated++;
					} else {
						$notUpdated++;
					}
				}
			}

			$confirm = sprintf(__('Bylo vytvořeno %s nových členů.', 'cms_member'), $added);
			if ($updated) {
				$confirm .= '<br>' . sprintf(__('Bylo upraveno %s starých členů.', 'cms_member'), $updated);
			}
			if ($notUpdated) {
				$confirm .= '<br>' . sprintf(__('%s starých členů nebylo upraveno.', 'cms_member'), $notUpdated);
			}
			mwMessages()->success($confirm);
		}

		wp_send_json([
			'success' => mwMessages()->success,
			'errors' => mwMessages()->errors,
			'html' => mwMessages()->writeHtml(),
		]);

		die();
	}
}
