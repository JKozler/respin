<?php

namespace Mioweb\Member;

use mwMember;
use Mioweb\Lib\LockFactory;
use Tracy\Debugger;
use Tracy\ILogger;

class Notifications
{
	// add membership
	public static function addMembershipNotify()
	{
		$sectionId = (int) $_GET['add_new_member'] ?? null;
		$memberApiKey = mwMemberModule()->getApiKey();
		$client = null;

		// simple shop notification
		if (isset($_GET['simpleshop_hash'])) {
			$client = NotificationsSimpleShop::authorization($sectionId, $memberApiKey);

		// GET notification
		} elseif (!isset($_POST['voucher']) && isset($_GET['token'], $_GET['email']) && $_GET['token'] === $memberApiKey) {
			$client = [
				'user_email' => $_GET['email'],
				'first_name' => $_GET['first_name'] ?? '',
				'last_name' => $_GET['last_name'] ?? '',
			];

		// fapi notification
		} else {
			$client = NotificationsFapi::authorization($sectionId, $memberApiKey);
		}

		if ($client !== null) {
			// create notify

			if (isset($_GET['level'])) {
				$levels = explode('-', $_GET['level']);
			} elseif (isset($_GET['addlevel'])) {
				$levels = explode('-', $_GET['addlevel']);
			} else {
				$levels = [];
			}

			$add_levels = isset($_GET['addlevel']) && (!empty($_GET['addlevel']) || $_GET['addlevel'] === '0') ? true : false;

			// months member
			$months = [];
			if (isset($_GET['month'])) {
				$add_months = explode('-', $_GET['month']);

				foreach ($add_months as $month) {
					$months[] = $month;
				}
			}

			$start = null;
			if ((isset($_GET['date']) && $_GET['date']) || (isset($_GET['time']) && $_GET['time'])) {
				$reg_date = isset($_GET['date']) && $_GET['date'] && $_GET['date'] !== 'NOW' ? $_GET['date'] : date('Y-m-d', current_time('timestamp'));
				$reg_time = isset($_GET['time']) && $_GET['time'] && $_GET['time'] !== 'NOW' ? $_GET['time'] : date('H:i:s', current_time('timestamp'));
				$start = $reg_date . ' ' . $reg_time;
			}

			// section and levels id back compatibility
			if (mwMemberModule()->memberSectionIdExist($sectionId) === false) {
				$sectionConvert = mwMemberModule()->getConvertTableSection($sectionId);
				if ($sectionConvert) {
					$sectionId = $sectionConvert['id'];
					$convertedLevels = [];
					foreach ($levels as $level) {
						$convertedLevels[] = $sectionConvert['levels'][$level] ?? $level;
					}
					$levels = $convertedLevels;
				}
			}

			$params = [
				'send_email' => isset($_GET['send_email']) ? (bool) $_GET['send_email'] : true,
				'start' => $start,
				'days' => isset($_GET['days']) && $_GET['days'] ? (int) $_GET['days'] : 0,
				'levels' => $levels,
				'add_levels' => $add_levels,
				'months' => $months,
				'month_num' => isset($_GET['month_num']) && $_GET['month_num'] ? (int) $_GET['month_num'] : 0,
				'mail_list' => $_GET['se_list'] ?? null,
				'setexp' => $_GET['setexp'] ?? null,
			];

			// return json?
			$returnJson = isset($_GET['return_json']) ? (bool) $_GET['return_json'] : false;

			$return = self::addMembership($client, $sectionId, $params, $returnJson, 'by_notify');

			if ($return['status'] === 1) {
				Notifications::updateDebug($sectionId, __('Notifikace proběhla správně.', 'cms_member') . ' ' . $return['message'], 1);

				// @TODO remove?
				do_action('cms_new_user_fapi_notification', $return['user_id'], $client);
			} else {
				Notifications::updateDebug($sectionId, __('Notifikace skončila chybou.', 'cms_member') . ' ' . $return['message'], 1);
			}

			echo json_encode($return);
		}
		die();
	}

	public static function addMembership(array $client, int $sectionId, array $args, bool $returnJson = false, string $source = '', ?string $accept = null)
	{
		$debug_status = 0;
		$user_changed = false;
		$userId = null;
		$user = null;

		$defaults = [
			'send_email' => true,
			'start' => null,
			'days' => 0,
			'levels' => [],
			'add_levels' => true,
			'months' => [],
			'month_num' => 0,
			'mail_list' => null,
			'setexp' => null,
		];

		$params = wp_parse_args($args, $defaults);

		// LOCK
		$lockFactory = new LockFactory();
		$lock = $lockFactory->createLock(__METHOD__ . '.' . $client['user_email'], 30);

		if (!$lock->acquire(true)) {
			return [];
		}

		try {
			$months = [];

			/* FIND EXISTING USER */
			$user = mwMember::getOneByEmail($client['user_email']);

			$send_email = $params['send_email'];

			// months
			if (isset($params['months']) && count($params['months'])) {
				foreach ($params['months'] as $month) {
					if ($month < 1900) { // wrong
						// do nothing
					} elseif ($month < 9999) { // year
						$months += MonthMembership::getAllMonthsOfYear($month);
					} else { // month
						$months[$month] = $month;
					}
				}
			}

			// add months count
			if ($params['month_num']) {
				$months += MonthMembership::getMonthsByNum($params['month_num']);
			}

			$memberSection = mwMemberModule()->getMemberSection($sectionId);

			// member section do not exist
			if ($memberSection === null) {
				$debug_status = -1;
				$debug_text = sprintf(__('Sekce s ID=[%s] neexistuje. ', 'cms_member'), $sectionId);

			// member section exist
			} else {
				/* NEW USER ******************** */
				if ($user === null) {
					$end = null;
					if (isset($params['setexp']) && $params['setexp'] !== null) {
						$end = $params['setexp'] !== '0' ? $params['setexp'] : null;
					} elseif (isset($params['days']) && $params['days']) {
						$startDate = $params['start'] === null ? current_time('timestamp') : strtotime($params['start']);
						$end = date('Y-m-d H:i:s', $startDate + ($params['days'] * 86400));
					}

					$membership = [
						'member_section_id' => $sectionId,
						'start' => $params['start'],
						'end' => $end,
						'levels' => $params['levels'],
						'months' => $months,
					];

					$newMember = mwMember::addMember($client, [$membership], $send_email, null, null, $source, $accept);

					if ($newMember) {
						if ($returnJson) {
							echo json_encode([
								'loginUrl' => $memberSection->getUrl(),
								'username' => $newMember->member->getLogin(),
								'password' => $newMember->password,
							]);
						}

						$user_changed = true;
						$debug_status = 1;
						$debug_text = sprintf(__('Nový uživatel %s byl vytvořen.', 'cms_member'), $newMember->member->getLogin());
						$userId = $newMember->member->getId();
					} else {
						$debug_text = __('Při vytváření člena nastala chyba.', 'cms_member');
					}
				} else {
					/* UPDATE EXISTING USER ******************** */
					$membership = [
						'member_section_id' => $sectionId,
						'levels' => $params['levels'],
						'months' => $months,
						'start' => $params['start'],
						'add_levels' => $params['add_levels'],
						'days' => $params['days'],
					];

					if (isset($params['setexp']) && $params['setexp'] !== null) {
						$membership['end'] = $params['setexp'] !== '0' ? $params['setexp'] : null;
					}

					$saved = $user->saveMemberships([$membership], $send_email);

					if ($returnJson) {
						echo json_encode([
							'loginUrl' => $memberSection->getUrl(),
							'username' => $client['user_email'],
						]);
					}

					$debug_status = 1;
					$userId = $user->getId();

					if (count($saved['added']) || count($saved['updated'])) {
						$debug_text = sprintf(__('Uživatel %s byl aktualizován.', 'cms_member'), $client['user_email']);
						$user_changed = true;
					} else {
						$debug_text = sprintf(__('U uživatele %s nebyly provedeny žádné změny.', 'cms_member'), $client['user_email']);
					}
				}

				// add to SE list
				if (isset($params['mail_list']) && $params['mail_list'] !== '' && isset($client['user_email'])) {
					$fields = [
						'name' => $client['first_name'] ?? '',
						'surname' => $client['last_name'] ?? '',
					];

					$apiItem = mwApiConnect()->getApi('se');
					$apiItem->client()->save_to_list_details($params['mail_list'], $client['user_email'], null, $fields);
				}
			}
		} catch (\Throwable $e) {
			Debugger::log($e, ILogger::EXCEPTION);

			return [
				'message' => $e->getMessage(),
				'status' => 0,
				'user_changed' => $user_changed,
				'user_existed' => $user !== null,
				'user_id' => $userId,
			];
		} finally {
			$lock->release();
		}

		return [
			'message' => $debug_text,
			'status' => $debug_status,
			'user_changed' => $user_changed,
			'user_existed' => $user !== null,
			'user_id' => $userId,
		];
	}

	// stop membership
	public static function stopMembershipNotify()
	{
		$sectionId = $_GET['stop_membership'] ?? -1;
		$level = $_GET['level'] ?? null;

		$client = NotificationsFapi::authorization($sectionId, '', true);

		if ($client !== null) {
			// back compatibility for old member sections and level ID
			if (mwMemberModule()->memberSectionIdExist($sectionId) === false) {
				$sectionConvert = mwMemberModule()->getConvertTableSection($sectionId);
				if ($sectionConvert) {
					$sectionId = $sectionConvert['id'];
					if ($level !== 0 && isset($sectionConvert['levels'][$level])) {
						$level = $sectionConvert['levels'][$level];
					}
				}
			}

			$status = self::stopMembershipByEmail($client['user_email'], $sectionId, $level);

			if ($status['status'] == 201) {
				Notifications::updateDebug($sectionId, __('Notifikace proběhla správně. Členství bylo pozastaveno.', 'cms_member'), 1);
			} else {
				Notifications::updateDebug($sectionId, __('Notifikace neproběhla.', 'cms_member') . ' ' . $status['message']);
			}
		}
		die();
	}

	public static function stopMembership(MwMember $member, int $memberSectionId, ?int $levelId = null): array
	{
		// no valid member section
		if (mwMemberModule()->memberSectionIdExist($memberSectionId) === null) {
			return [
				'message' => __('Členská sekce nenalezena', 'cms_member'),
				'status' => 403,
				'code' => 'missing_member_section',
			];
		}

		if ($levelId !== null) {
			$level = MemberLevel::getOneById($levelId);
			if ($level === null) {
				return [
					'message' => __('Členská úroveň nenalezena', 'cms_member'),
					'status' => 403,
					'code' => 'missing_member_section_level',
				];
			}
			$member->stopMembershipLevel($memberSectionId, $levelId);
		} else {
			$member->stopMembership($memberSectionId);
		}

		return [
			'message' => __('Hotovo', 'cms_member'),
			'status' => 201,
			'code' => 'ok',
		];
	}

	public static function stopMembershipByEmail(string $email, int $memberSectionId, ?int $level = null): array
	{
		$member = mwMember::getOneByEmail($email);

		if ($member === null) {
			return [
				'message' => __('Uživatel nenalezen', 'cms_member'),
				'status' => 403,
				'code' => 'missing_member',
			];
		}

		return self::stopMembership($member, $memberSectionId, $level);
	}

	public static function updateDebug($mem, $error, $stat = 0)
	{
		$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$debug = get_option('mem_notification_debug');
		if (!$stat) {
			$debug_option = get_option('fapi_notification')['members'][$mem] ?? [];
			$emailNotificationEnabled = (bool) ($debug_option['notification_onemail'] ?? false);

			if ($emailNotificationEnabled) {
				$email = $debug_option['notifi_email'] ?? null;
				$email = $email ?: get_bloginfo('admin_email');

				$message = __(sprintf('Na webu %s byla spuštěna FAPI notifikace, která selhala: ', '"' . get_bloginfo('name') . '"'), 'cms_member');
				$message .= $error;

				$header = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
				wp_mail($email, __('Chybná FAPI notifikace', 'cms_member'), $message, $header);
			}
		}
		$debug[$mem][] = [
			'time' => current_time('timestamp'),
			'error' => $error,
			'status' => $stat,
			'url' => $url,
		];
		$debug[$mem] = array_slice($debug[$mem], -50, 50);
		update_option('mem_notification_debug', $debug);
	}
}
