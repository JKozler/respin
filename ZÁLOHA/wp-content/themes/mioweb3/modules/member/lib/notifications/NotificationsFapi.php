<?php

namespace Mioweb\Member;
use Fapi\FapiClient\AuthorizationException;
use Fapi\FapiClient\FapiClientFactory;
use Fapi\FapiClient\Tools\SecurityChecker;

class NotificationsFapi
{

	public static function authorization(int $sectionId, string $memberApiKey, bool $ignore_repayment = false): ?array
	{
		$fapi_option = mwApiConnect()->getApi('fapi')->getOption();
		$fapi_option = apply_filters('mw_notifi_fapi_login', $fapi_option);

		if (isset($fapi_option['login']) && $fapi_option['login'] && isset($fapi_option['password']) && $fapi_option['password']) {
			require_once FAPI_API;

			$fapi = (new FapiClientFactory())->createFapiClient($fapi_option['login'], $fapi_option['password']);

			try {
				$fapi->checkConnection();
			} catch (AuthorizationException $e) {
				Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Přihlašovací údaje do FAPI nejsou správné.', 'cms_member') . '<br><small>' . get_class($e) . ': ' . $e->getMessage() . '</small>');

				return null;
			}

			if (!empty($_POST['voucher'])) {
				if (empty($_POST['time']) || empty($_POST['security']) || !is_numeric($_POST['voucher']) || !is_numeric($_POST['time'])) {
					Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Chybná data.', 'cms_member'));

					return null;
				}
				$voucherId = (int) $_POST['voucher'];
				$time = (int) $_POST['time'];
				$security = $_POST['security'];

				try {
					$voucher = $fapi->getVouchers()->find($voucherId);
				} catch (\Exception $exception) {
					Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Nepodařilo se získat voucher s tímto ID: ' . $_POST['voucher'], 'cms_member'));

					return null;
				}

				if (empty($voucher)) {
					Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Voucher je prázdný.', 'cms_member'));

					return null;
				}

				if (!isset($voucher['item_template_code'])) {
					Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Voucher neobsahuje kód šablony.', 'cms_member'));

					return null;
				}

				$itemTemplates = $fapi->getItemTemplates()->findAll([
					'code' => $voucher['item_template_code'],
					'limit' => 1,
				]);
				$itemTemplate = $itemTemplates[0] ?? [];
				$isOk = SecurityChecker::isVoucherSecurityValid($voucher, $itemTemplate, $time, $security);

				if (!$isOk) {
					Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Stažený voucher neodpovídá jeho bezpečnostnímu otisku.', 'cms_member'));

					return null;
				}

				if (!isset($voucher['status']) || $voucher['status'] !== 'applied') {
					Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Voucher není uplatněný.', 'cms_member'));

					return null;
				}

				if (!isset($voucher['applicant']['email'])) {
					Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Stažený voucher neobsahuje e-mail klienta, který ho použil.', 'cms_member'));

					return null;
				}

				if (!isset($voucher['applicant']['email'])) {
					Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Stažený voucher neobsahuje e-mail klienta, který ho použil.', 'cms_member'));

					return null;
				}

				$email = $voucher['applicant']['email'];
				$clients = $fapi->getClients()->findAll(['email' => $email]);

				if ((bool) $clients) {
					$client = array_shift($clients);

					return [
						'user_email' => $client['email'],
						'first_name' => $client['first_name'] ?? '',
						'last_name' => $client['last_name'] ?? '',
					];
				} else {
					return ['user_email' => $email];
				}
			}

			if (empty($_POST['id']) || empty($_POST['time']) || empty($_POST['security']) || !is_numeric($_POST['id']) || !is_numeric($_POST['time'])) {
				Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Chybná data.', 'cms_member'));

				return null;
			}

			try {
				$invoice = $fapi->getInvoices()->find($_POST['id']);
			} catch (\Exception $exception) {
				Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Nepodařilo se získat fakturu s tímto ID: ' . $_POST['id'], 'cms_member'));

				return null;
			}

			if (empty($invoice)) {
				Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Faktura je prázdná.', 'cms_member'));

				return null;
			}

			if (!empty($invoice['parent'])) {
				// This is not the first invoice in the order, so we can skip it.
				return null;
			}
			if (!empty($invoice['repayment_number']) && $invoice['repayment_number'] > 1 && !$ignore_repayment) {
				return null;
			}
			$itemsSecurityHash = '';
			foreach ($invoice['items'] as $item) {
				$itemsSecurityHash .= md5($item['id'] . $item['name']);
			}
			$security = sha1($_POST['time'] . $invoice['id'] . $invoice['number'] . $itemsSecurityHash);

			if ($security != $_POST['security']) {
				Notifications::updateDebug($sectionId, __('Notifikace skončila chybou. Stažená faktura neodpovídá jejímu bezpečnostnímu otisku.', 'cms_member'));

				return null;
			}

			$client = $fapi->getClients()->find($invoice['client']);

			return [
				'user_email' => $client['email'],
				'first_name' => $client['first_name'] ?? '',
				'last_name' => $client['last_name'] ?? '',
			];
		}

		return null;
	}

}
