<?php declare(strict_types=1);

namespace Mioweb\Shop\Order;

use MwMemberSection;
use MwsAutomationAction;
use MwsEmailType;

class OrderAutomationProcessor
{

	public function process(Order $order, string $event): void
	{
		$automations = $order->getAutomations();

		foreach ($order->getItems()->getProducts() as $orderItem) {
			$product = $orderItem->getProduct();

			if ($product) {
				$automations = array_merge($automations, $product->getAutomations());
			}
		}

		$gateLive = $order->getGateLive();
		$contact = $gateLive ? $gateLive->getInvoiceContact() : null;
		foreach ($automations as $automation) {
			if ($automation['event'] !== $event) {
				continue;
			}

			$done = false;

			$message = '';
			switch ($automation['action']) {
				case MwsAutomationAction::SendEmail:
					if ($contact) {
						$file = MWS()->getEmailAttachment(MwsEmailType::CustomEmails, $order, (int) $automation['email_index'] ?? '');
						if ($file && (filesize($file) > Order::MAXIMUM_FILE_SIZE_MB * pow(2, 20))) {
							$message = __(sprintf(
								'Příloha e-mailu je příliš velká. Maximální velikost souboru je %d MB.',
								Order::MAXIMUM_FILE_SIZE_MB
							), 'mwshop');

							break;
						}

						$attachments = $file ? [$file] : [];

						$contact->sendMail(
							MWS()->getEmailSubject(MwsEmailType::CustomEmails, $order, (int) $automation['email_index'] ?? ''),
							MWS()->getEmailContent(MwsEmailType::CustomEmails, $order, (int) $automation['email_index'] ?? ''),
							$attachments
						);
						$message = __('Email byl odeslán na adresu: ', 'mwshop') . $contact->getEmail();
						$done = true;
					}

					break;
				case MwsAutomationAction::AddContact:
					if ($contact && (bool) ($automation['contact_list']['id'] ?? null)) {
						$result = mwEmailingApi()->save_to_list_details(
							$automation['contact_list']['api'],
							$automation['contact_list']['id'],
							$contact->getEmail(),
							$automation['contact_list']['purpose'] ?? null,
							[
								'name' => $contact->getPerson()->getFirstName(),
								'surname' => $contact->getPerson()->getLastName(),
								'phone' => $contact->getPhone(),
							],
						);

						if ($result['status'] === true) {
							$message = __('Kontakt byl přidán do seznamu s ID: ', 'mwshop') . $automation['contact_list']['id'] . ' (' . mwApiConnect()->getApi($automation['contact_list']['api'])->getName() . ')';
						} else {
							$message = __('Kontakt se nepodařilo přidat do seznamu s ID: ', 'mwshop') . $automation['contact_list']['id'] . ' (' . mwApiConnect()->getApi($automation['contact_list']['api'])->getName() . ')';
							if ($result['message']) {
								$message .= __('Chyba:', 'mwshop') . $result['message'];
							}
						}

						$done = true;
					}

					break;
				case MwsAutomationAction::RemoveContact:
					if ($contact && isset($automation['contact_list']['id'])) {
						$result = mwEmailingApi()->remove_from_list(
							$automation['contact_list']['api'],
							$automation['contact_list']['id'],
							$contact->getEmail()
						);

						if ($result['status'] === true) {
							$message = __('Kontakt byl odebrán ze seznamu s ID: ', 'mwshop') . $automation['contact_list']['id'] . ' (' . mwApiConnect()->getApi($automation['contact_list']['api'])->getName() . ')';
						} else {
							$message = __('Kontakt se nepodařilo odebrat ze seznamu s ID.', 'mwshop') . $automation['contact_list']['id'] . ' (' . mwApiConnect()->getApi($automation['contact_list']['api'])->getName() . ')';
							if ($result['message']) {
								$message .= __('Chyba:', 'mwshop') . $result['message'];
							}
						}

						$done = true;
					}

					break;
				case MwsAutomationAction::AddMembership:
					if ($contact && class_exists('mwMemberModule')) {
						$autSet = $automation['member_section'] ?? null;

						if ($autSet !== null) {
							$client = [
								'user_email' => $contact->getEmail(),
								'user_login' => $contact->getEmail(),
								'first_name' => $contact->getPerson()->getFirstName(),
								'last_name' => $contact->getPerson()->getLastName(),
							];

							/* back compatibility @TODO repair data in new migration and remove this code */
							$lVals = $autSet['levels'][$autSet['section']] ?? ($autSet[$autSet['section']]['levels'] ?? ($autSet[$autSet['section']] ?? []));
							/* end back compatibility */

							$params = [
								'send_email' => isset($autSet['nosend_email']) ? false : true,
								'levels' => $lVals,
							];

							$membershipType = $autSet['membership_type'];
							if ($membershipType === 'nolimit') {
								$params['setexp'] = '0';
							} elseif ($membershipType === 'limit') {
								$params['days'] = $autSet['membership_days'];
							} elseif ($membershipType === 'limit_date' && $autSet['membership_limit_date']) {
								$params['setexp'] = $autSet['membership_limit_date'];
							}

							$params['start'] = null;
							if (isset($autSet['set_date'])) {
								$regDate = $autSet['date']['date'] ?: date('Y-m-d', current_time('timestamp'));
								$regTime = $autSet['date']['hour'] . ':' . $autSet['date']['minute'];
								$params['start'] = $regDate . ' ' . $regTime;
							}

							$memberId = $autSet['section'];

							if (is_string($memberId) && is_numeric($memberId)) {
								$memberId = (int) $memberId;
							}

							$memberSection = MwMemberSection::getOneById($memberId);
							if ($memberSection) {
								$return = \Mioweb\Member\Notifications::addMembership($client, $memberSection->getId(), $params, false, 'by_automation');
								if ($return['status'] === 1) {
									$info = $memberSection->getName();
									if (count($params['levels'])) {
										$addedLevels = [];
										foreach ($params['levels'] as $levelId) {
											if (is_string($levelId) && is_numeric($levelId)) {
												$levelId = (int) $levelId;
											}
											$level = \Mioweb\Member\MemberLevel::getOneById($levelId);
											if ($level !== null) {
												$addedLevels[] = $level->getName();
											}
										}
										$info .= ' (';
										$info .= count($addedLevels) > 1 ? __('úrovně', 'mwshop') : __('úroveň', 'mwshop');
										$info .= ': ' . implode(', ', $addedLevels) . ')';
									}
									if ($return['user_existed']) {
										$message .= sprintf(__('Bylo upraveno členství pro email %s v členské sekci %s', 'mwshop'), $contact->getEmail(), $info);
									} else {
										$message .= sprintf(__('Bylo vytvořeno členství pro email %s v členské sekci %s', 'mwshop'), $contact->getEmail(), $info);
									}
								} else {
									$message .= __('Nepodařilo se vytvořit přístup do členské sekce.', 'mwshop');
									$message .= __('Chyba', 'mwshop') . ': ' . $return['message'];
								}
							} else {
								$message .= __('Nepodařilo se vytvořit přístup do členské sekce. Členská sekce s tímto ID neexistuje.', 'mwshop');
							}
						}

						$done = true;
					}

					break;
				case MwsAutomationAction::RemoveMembership:
					if ($contact && class_exists('mwMemberModule')) {
						$autSet = $automation['remove_member_section'];
						$email = $contact->getEmail();

						$memberId = $autSet['section'];

						if (is_string($memberId) && is_numeric($memberId)) {
							$memberId = (int) $memberId;
						}

						$memberSection = MwMemberSection::getOneById($memberId);

						/* back compatibility @TODO repair data in new migration and remove this code */
						$lVals = $autSet['levels'][$autSet['section']] ?? ($autSet[$autSet['section']]['levels'] ?? ($autSet[$autSet['section']] ?? null));
						/* end back compatibility */

						if (isset($lVals)) {
							foreach ($lVals as $level) {
								if (is_string($level) && is_numeric($level)) {
									$level = (int) $level;
								}
								$return = \Mioweb\Member\Notifications::stopMembershipByEmail($email, $memberId, $level);

								if ($return['status'] == 201) {
									$message = sprintf(__('Pro email %s byl zrušen přístup do některých úrovní v členské sekci %s', 'mwshop'), $contact->getEmail(), $memberSection ? $memberSection->getName() : __('(smazaná)', 'mwshop'));
								}
							}
						} else {
							$return = \Mioweb\Member\Notifications::stopMembershipByEmail($email, $memberId);

							if ($return['status'] == 201) {
								$message = sprintf(__('Pro email %s byl zrušen přístup do členské sekce %s', 'mwshop'), $contact->getEmail(), $memberSection ? $memberSection->getName() : __('(smazaná)', 'mwshop'));
							}
						}

						/** @phpstan-ignore-next-line */
						if ($return['status'] != 201) {
							/** @phpstan-ignore-next-line */
							$message = __('Nepodařilo se zrušit přístup do členské sekce.', 'mwshop') . ' ' . $return['message'];
						}

						$done = true;
					}

					break;
				case MwsAutomationAction::RunScript:
					try {
						$args = [
							'method' => 'POST',
							'timeout' => 45,
							'redirection' => 5,
							'httpversion' => '1.1',
							'blocking' => true,
							'headers' => [],
							'body' => [
								'ID' => $order->getId(),
								'number' => $order->getNumber(),
								'status' => $order->getStatus(),
								'variable_symbol' => $order->getNumber(),
								'exchange_rate' => $order->getExchangeRate(),
								'paid' => $order->isPaid(),
								'paid_on' => $order->getPaidAtDateFormatted(),
								'source' => $order->getSource()->toArray(),
								'payment_type' => $order->getPayment()['type'] ?? null,
								'shipping' => $order->getShipping(),
								'heureka_disagree' => $order->getHeurekaDisagree(),
								'customer' => $order->getCustomer(),
								'customer_note' => $order->getCustomerNote(),
								'discount_code' => $order->getDiscountCode(),
								'items' => [],
								'simplified_invoice' => $order->useSimplifiedInvoice(),
								'currency' => $order->getGateLive()->getCurrency(),
								'native_currency' => $order->getNativeCurrency(),
								'total' => $order->getPrice()->getPriceVatIncluded(),
								'total_vat' => $order->getPrice()->getVatAmount(),
								'total_native' => $order->getNativePrice()->getPriceVatIncluded(),
								'total_vat_native' => $order->getNativePrice()->getVatAmount(),
							],
						];

						foreach ($order->getItems()->getProducts() as $orderItem) {
							if ($product = $orderItem->getProduct()) {
								$args['body']['items'][] = [
									'id' => $product->getId(),
									'name' => $product->getName(),

									'count' => $orderItem->getCount(),
									'type' => $product->getType(),
									//"oss" => false,
									'price' => $product->getPrice()->getPriceVatIncluded(),
									'price_novat' => $product->getPrice()->getPriceVatExcluded(),
									'price_vat' => $product->getPrice()->getVatAmount(),
									'vat' => $product->getPrice()->getVatPercentage(),
								];
							}
						}

						$response = wp_remote_post($automation['script_url'], $args);

						$return = json_decode(wp_remote_retrieve_body($response));

						$message = is_wp_error($response) || !isset($return->status) || (isset($return->error)) ? sprintf(__('Programový skript %s nebyl úspěšný: %s', 'mwshop'), $automation['script_url'], $return->error->message ?? '') : __('Byl úspěšně spuštěn programový skript: ', 'mwshop') . $automation['script_url'];

						$done = true;
					} catch (\Throwable $e) {
					}

					break;
				case MwsAutomationAction::SendFile:
					if ($contact) {
						$file = $order->processFileName($automation['file']);

						if ($file) {
							if (filesize($file) > Order::MAXIMUM_FILE_SIZE_MB * pow(2, 20)) {
								$message = __(sprintf(
									'Soubor je příliš velký. Maximální velikost souboru je %d MB.',
									Order::MAXIMUM_FILE_SIZE_MB
								), 'mwshop');
							} else {
								$success = $contact->sendMail(
									MWS()->getEmailSubject(MwsEmailType::ElectronicDelivery, $order),
									MWS()->getEmailContent(MwsEmailType::ElectronicDelivery, $order),
									[$file]
								);

								$message = $success
									? __('Soubor byl odeslán na adresu: ', 'mwshop') . $contact->getEmail()
									: __('Soubor se nepodařilo odeslat.', 'mwshop');
							}
						} else {
							$message = __('Soubor neexistuje.', 'mwshop');
						}
					}
					$done = true;

					break;
			}

			if ($done) {
				$order->addHistory($message); // TODO: event and more info
			}
		}
	}

}
