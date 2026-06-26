<?php

namespace Mioweb\Member;

use Mioweb\Lib\Email;
use Mioweb\Lib\LockFactory;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockInterface;
use WP_User_Query;
use function array_key_exists;
use function gettype;
use function set_time_limit;
use function update_option;
use const MWLS_MEMBER;

class Installer
{

	const OPTION_INSTALLER_STEPS = 'mw_member_installer_steps';
	//const OPTION_TASKS_CONVERT_TABLE = 'mw_member_installer_tasks_convert_table';
	const OPTION_TASKS_CONVERT_TABLE = 'mw_member_installer_tasks_convert_table_2';

	private static ?int $startedTimestamp = null;

	public static function installUpdates()
	{
		self::$startedTimestamp = time();

		$versions = get_option('cms_versions');

		if (isset($versions['member']) && $versions['member'] != MEMBER_VERSION) {
			if ($versions['member'] == '0.9') {
				$members = get_option('member_basic');

				if ($members && isset($members['members']) && is_array($members['members'])) {
					$header = get_option('member_header');
					$footer = get_option('member_footer');
					$popups = get_option('member_popups');
					$appearance = get_option('member_appearance');
					$fapi_notification = get_option('fapi_notification');

					//backup
					//if(!get_option('member_basic_backup')) update_option('member_basic_backup',$members);
					if (!get_option('member_appearance_backup')) {
						update_option('member_appearance_backup', $appearance);
					}
					if (!get_option('member_header_backup')) {
						update_option('member_header_backup', $header);
					}
					if (!get_option('member_footer_backup')) {
						update_option('member_footer_backup', $footer);
					}
					if (!get_option('member_popups_backup')) {
						update_option('member_popups_backup', $popups);
					}
					if (!get_option('fapi_notification_backup')) {
						update_option('fapi_notification_backup', $fapi_notification);
					}

					// new setting of member sections

					$new_header = ['members'];
					$new_footer = ['members'];
					$new_popups = ['members'];
					$new_appearance = ['members'];
					$new_fapi_notification = ['members'];

					foreach ($members['members'] as $mem_id => $member) {
						$new_header['members'][$mem_id] = $header;
						$new_header['members'][$mem_id]['menu'] = $header['menu'][$mem_id]; //menu
						$new_footer['members'][$mem_id] = $footer;
						$new_footer['members'][$mem_id]['menu'] = $footer['menu'][$mem_id]; //menu
						$new_popups['members'][$mem_id] = $popups;
						$new_appearance['members'][$mem_id] = $appearance;
						$new_fapi_notification['members'][$mem_id] = $fapi_notification;
					}

					update_option('member_header', $new_header);
					update_option('member_footer', $new_footer);
					update_option('member_appearance', $new_appearance);
					update_option('member_popups', $new_popups);
					update_option('fapi_notification', $new_fapi_notification);

					// notification log
					$notifications = get_option('notification_debug');
					if ($notifications && is_array($notifications)) {
						$new_notifications = [];
						foreach ($members['members'] as $mem_id => $member) {
							$new_notifications[$mem_id] = $notifications;
						}
						update_option('mem_notification_debug', $new_notifications);
					}
				}
			}
			if (version_compare($versions['member'], '3.0', '<')) {
				$pages = mw_get_pages(['post_status' => 'publish,private,draft']);
				foreach ($pages as $page) {
					$page_member = get_post_meta($page->ID, 'page_member', true);

					if ($page_member && !isset($page_member['visibility'])) {
						$page_month_member = get_post_meta($page->ID, 'mw_month_member', true);

						if (isset($page_member['evergreen_datetime']) && $page_member['evergreen_datetime']['date']) {
							$page_member['visibility'] = 'date';
						} elseif (isset($page_member['evergreen']) && $page_member['evergreen']) {
							$page_member['visibility'] = 'evergreen';
						} elseif ($page_month_member) {
							$page_member['visibility'] = 'month';
						} else {
							$page_member['visibility'] = '';
						}

						if ($page_member['description'] && !$page->post_excerpt) {
							wp_update_post(['ID' => $page->ID, 'post_excerpt' => $page_member['description']]);
						}

						if (isset($page_member['thumbnail']) && $page_member['thumbnail']) {
							global $wpdb;

							$thumb_name = preg_replace('/\-[0-9]+x[0-9]+\.[A-Za-z]+$/', '', $page_member['thumbnail']);

							$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid LIKE '%s';", '%' . $thumb_name . '%'));

							if (isset($attachment[0])) {
								set_post_thumbnail($page->ID, $attachment[0]);
							}
						}

						update_post_meta($page->ID, 'page_member', $page_member);
					}
				}

				global $wpdb;
				$wpdb->get_results("INSERT INTO $wpdb->usermeta (user_id, meta_key, meta_value)
				SELECT distinct(ID) as user_id, \"mw_show_inmemberlist\" as meta_key, \"1\" as meta_value from $wpdb->users
				WHERE ID not in (
				SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'mw_hide_member'
				) and ID not in (
				SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'mw_show_inmemberlist'
				)");

				/*
				$args = [
					'role' => 'member',
					'meta_key' => 'mw_hide_member',
					'meta_compare' => 'NOT EXISTS'
				];

				$wp_user_query = new WP_User_Query($args);
				$users = $wp_user_query->get_results();

				foreach ($users as $user) {
					add_user_meta($user->ID, 'mw_show_inmemberlist', '1');
				}*/
			}
			if (version_compare($versions['member'], '3.1', '<')) {
				update_option('mw_old_url', get_home_url());
			}
			if (version_compare($versions['member'], '3.2', '<')) {
				if (strpos(PHP_SAPI, 'cgi') !== false) {
					self::set_htaccess_for_cgi('update member module');
				}
			}

			if (version_compare($versions['member'], '3.3', '<')) {
				$custom_fields = get_option('mw_member_user_custom_fields');

				if ($custom_fields) {
					$keys = [];
					foreach ($custom_fields as $cf_key => $field) {
						$itemId = wp_insert_post([
							'post_type' => MW_MEMBER_CUSTOM_FIELDS_SLUG,
							'post_status' => 'publish',
							'post_title' => $field['title'],
							'post_excerpt' => $field['description'],
						]);

						if ($itemId) {
							MWDB()->setPostMeta($itemId, 'mw_custom_field', [
								'type' => $field['type'],
							]);
							MWDB()->setPostMeta($itemId, 'mw_back_compatibility_id', $field['id']);
							$keys[$field['id']] = $itemId;
						}
					}
					$args = [
						'meta_key' => 'member_custom_field',
					];

					$wp_user_query = new WP_User_Query($args);
					$users = $wp_user_query->get_results();

					foreach ($users as $user) {
						$meta = get_user_meta($user->ID, 'member_custom_field', true);
						foreach ($meta as $meta_key => $meta_val) {
							if (isset($keys[$meta_key])) {
								$meta[$keys[$meta_key]] = $meta_val;
							}
						}
						update_user_meta($user->ID, 'member_custom_field', $meta);
					}
				}
			}

			if (version_compare($versions['member'], '3.4', '<')) {
				$factory = new LockFactory();
				$lock = $factory->createLock('member-migration-3.4');

				try {
					if (!$lock->acquire()) {
						die();
					}
				} catch (LockConflictedException | LockAcquiringException) {
					die();
				}

				$steps = self::getInstallationSteps();
				$step = end($steps) ?: null;
				$migrationTimeout = 120; // 2 minutes
				$currentTimestamp = time();

				if ($step !== null && $step['status'] === 'running') {
					$startedTimestamp = $step['time'];
					$lock->release();

					if ($currentTimestamp - $startedTimestamp > $migrationTimeout) {
						// TODO show some error to an admin (migration failed and cannot continue)
						die();
					}

					// TODO maybe show some maintenance page? Web might not be working depending on currently running step
					return;
				}

				ini_set('max_execution_time', $migrationTimeout);
				set_time_limit($migrationTimeout);

				if (!array_key_exists('tables-installed', $steps)) {
					self::installTables($lock);
					self::addInstallationStep('tables-installed', null, $lock);
					$steps = self::markInstallationStepsAsDone();
				}

				// member sections
				$members = get_option('member_basic');

				if ($members && isset($members['members']) && is_array($members['members'])) {
					if (!array_key_exists('member-sections', $steps)) {
						self::addInstallationStep('member-sections', null, $lock);

						$convertTable = [];

						// This should be run only once
						self::migrateMemberSections($members, $convertTable);
						$steps['member-sections']['value'] = [
							'convert_table' => $convertTable,
						];
						$steps = self::markInstallationStepsAsDone($steps);
					} else {
						// Saved computed values from previous steps
						$convertTable = $steps['member-sections']['value']['convert_table'] ?? [];
					}

					// Eshop Automations
					if (function_exists('MWS')) {
						if (!array_key_exists('products_automations', $steps)) {
							self::addInstallationStep('eshop_automations', null, $lock);
							self::migrateEshopAutomations($convertTable);
							$steps = self::markInstallationStepsAsDone($steps);
						}
						// Products Automations - can be run multiple times
						if (!array_key_exists('membership_products_automations', $steps)) {
							self::migrateProductsAutomations($steps['products_automations'] ?? [], $convertTable, $lock);
						}

						// Membership products Automations
						if (!array_key_exists('forms_automations', $steps)) {
							self::migrateMembershipProductsAutomations($steps['membership_products_automations'] ?? [], $convertTable, $lock);
						}

						// Forms Automations - can be run multiple times
						if (!array_key_exists('member-pages', $steps)) {
							self::migrateFormsAutomations($steps['forms_automations'] ?? [], $convertTable, $lock);
						}
					}

					// Member pages - can be run multiple times
					if (!array_key_exists('members', $steps)) {
						self::migrateMemberPages($steps['member-pages'] ?? [], $convertTable, $lock);
					}

					// Members - can be run multiple times
					if (!array_key_exists('done_v2', $steps)) {
						self::migrateMembers($steps['members'] ?? [], $convertTable, $lock);
					}
				}

				self::addInstallationStep('done_v2', null, $lock);
				self::markInstallationStepsAsDone();
				self::cleanup();

				update_option('mw_member_migration_version', 2);
			}

			if (version_compare($versions['member'], '3.5', '<')) {
				if (!get_option('mw_member_migration_version', false)) {

					$factory = new LockFactory();
					$lock = $factory->createLock('member-migration-3.5');

					try {
						if (!$lock->acquire()) {
							die();
						}
					} catch (LockConflictedException | LockAcquiringException) {
						die();
					}

					$steps = self::getInstallationSteps();
					$step = end($steps) ?: null;
					$migrationTimeout = 120; // 2 minutes
					$currentTimestamp = time();

					if ($step !== null && $step['status'] === 'running') {
						$startedTimestamp = $step['time'];
						$lock->release();

						if ($currentTimestamp - $startedTimestamp > $migrationTimeout) {
							// TODO show some error to an admin (migration failed and cannot continue)
							die();
						}

						// TODO maybe show some maintenance page? Web might not be working depending on currently running step
						return;
					}

					ini_set('max_execution_time', $migrationTimeout);
					set_time_limit($migrationTimeout);

					// member sections
					$members = get_option('member_basic');

					if ($members && isset($members['members']) && is_array($members['members'])) {

						$convertTable = $steps['member-sections']['value']['convert_table'] ?? [];

						// Member pages - can be run multiple times
						if (!array_key_exists('member-tasks', $steps)) {
							self::migrateMemberPages($steps['member-pages-2'] ?? [], $convertTable, $lock, true);
						}

						// member tasks
						if (!array_key_exists('second-done', $steps)) {
							self::migrateMemberTasks($steps['member-tasks'] ?? [], $lock);
						}
					}

					// repair eshop automations
					$automations = get_option('eshop_actions');
					if (is_array($automations) && !isset($automations['actions'])) {
						update_option('eshop_actions', ['actions' => $automations]);
					}

					self::addInstallationStep('second-done', null, $lock);
					self::markInstallationStepsAsDone();
					self::cleanup();
				}
			}

			$versions['member'] = MEMBER_VERSION;
			update_option('cms_versions', $versions);
		} elseif (empty($versions) || !isset($versions['member'])) {
			if (strpos(PHP_SAPI, 'cgi') !== false) {
				self::set_htaccess_for_cgi();
			}

			self::installTables();

			$versions['member'] = MEMBER_VERSION;
			update_option('cms_versions', $versions);
		}
	}

	private static function migrateEshopAutomations(array $convertTable): void
	{
		$automations = MWS()->getGlobalAutomations();
		$newAutomations = self::processAutomation($automations, $convertTable);
		if ($newAutomations !== null) {
			update_option('eshop_actions', ['actions' => $newAutomations]);
		}
	}

	private static function migrateProductsAutomations(array $step, array $convertTable, ?LockInterface $lock = null): void
	{
		$limit = 500;
		$offset = (int) ($step['value'] ?? 0);

		if ($step !== []) {
			// if it is not first iteration
			$offset += $limit;
		}

		global $wpdb;

		do {
			self::addInstallationStep('products_automations', $offset, $lock);
			$rows = $wpdb->get_results("SELECT post_id, meta_value, meta_key FROM $wpdb->postmeta WHERE meta_key = 'automations' LIMIT $limit OFFSET $offset");
			$offset += $limit;
			foreach ($rows as $row) {
				$formAutomations = maybe_unserialize($row->meta_value);
				if (is_array($formAutomations) && isset($formAutomations['actions']) && is_array($formAutomations['actions'])) {
					$newAutomations = self::processAutomation($formAutomations['actions'], $convertTable);
					if ($newAutomations !== null) {
						update_post_meta($row->post_id, $row->meta_key, ['actions' => $newAutomations]);
					}
				}
			}
		} while ($rows);
	}

	private static function migrateMembershipProductsAutomations(array $step, array $convertTable, ?LockInterface $lock = null): void
	{
		$limit = 500;
		$offset = (int) ($step['value'] ?? 0);

		if ($step !== []) {
			// if it is not first iteration
			$offset += $limit;
		}

		global $wpdb;

		do {
			self::addInstallationStep('membership_products_automations', $offset, $lock);

			$rows = $wpdb->get_results("SELECT post_id, meta_value FROM $wpdb->posts, $wpdb->postmeta WHERE ID = post_id AND post_type = 'mwproduct' AND meta_key = 'product' LIMIT $limit OFFSET $offset");
			$offset += $limit;

			foreach ($rows as $row) {
				$product = maybe_unserialize($row->meta_value);
				$modified = false;
				if (is_array($product) && $product['type'] === 'membership' && isset($product['membership_setting']) && is_array($product['membership_setting'])) {
					$autSet = $product['membership_setting'];
					$oldMemberSectionId = $autSet['section'];

					if (isset($convertTable[$oldMemberSectionId])) {
						$newData = $convertTable[$oldMemberSectionId];

						// new member section id
						$autSet['section'] = $newData['id'];

						// new levels ids
						if (isset($autSet[$oldMemberSectionId]['levels'])) {
							$convertedLevels = [];
							foreach ($autSet[$oldMemberSectionId]['levels'] as $level) {
								if (isset($newData['levels'][$level])) {
									$convertedLevels[$newData['levels'][$level]] = $newData['levels'][$level];
								}
							}
							//$autSet[$newData['id']]['levels'] = $convertedLevels;
							$autSet['levels'][$newData['id']] = $convertedLevels;
						}
						$modified = true;
					}

					if ($modified) {
						$product['membership_setting'] = $autSet;
						update_post_meta($row->post_id, 'product', $product);
					}
				}
			}
		} while ($rows);
	}

	private static function migrateFormsAutomations(array $step, array $convertTable, ?LockInterface $lock = null): void
	{
		$limit = 500;
		$offset = (int) ($step['value'] ?? 0);

		if ($step !== []) {
			// if it is not first iteration
			$offset += $limit;
		}

		global $wpdb;

		do {
			self::addInstallationStep('forms_automations', $offset, $lock);
			$rows = $wpdb->get_results("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = 'mws_sale_form_automation' LIMIT $limit OFFSET $offset");
			$offset += $limit;
			foreach ($rows as $row) {
				$formAutomations = maybe_unserialize($row->meta_value);
				if (is_array($formAutomations) && isset($formAutomations['actions']) && is_array($formAutomations['actions'])) {
					$newAutomations = self::processAutomation($formAutomations['actions'], $convertTable);
					if ($newAutomations !== null) {
						update_post_meta($row->post_id, 'mws_sale_form_automation', ['actions' => $newAutomations]);
					}
				}
			}
		} while ($rows);
	}

	private static function processAutomation(array $automations, array $convertTable): ?array
	{
		$modified = false;

		foreach ($automations as $key => $automation) {
			$newSetting = self::convertAutomationSetting('member_section', $automation, $convertTable);
			if ($newSetting !== null) {
				$automations[$key]['member_section'] = $newSetting;
				$modified = true;
			}

			$newSetting = self::convertAutomationSetting('remove_member_section', $automation, $convertTable);
			if ($newSetting !== null) {
				$automations[$key]['remove_member_section'] = $newSetting;
				$modified = true;
			}
		}
		if ($modified) {
			return $automations;
		}

		return null;
	}

	private static function convertAutomationSetting(string $autKey, array $automation, array $convertTable): ?array
	{
		$autSet = $automation[$autKey] ?? null;

		if ($autSet !== null) {
			$oldMemberSectionId = $autSet['section'];

			if (isset($convertTable[$oldMemberSectionId])) {
				$newData = $convertTable[$oldMemberSectionId];

				// new member section id
				$autSet['section'] = $newData['id'];

				// new levels ids
				if (isset($autSet[$oldMemberSectionId]['levels'])) {
					$convertedLevels = [];
					foreach ($autSet[$oldMemberSectionId]['levels'] as $level) {
						if (isset($newData['levels'][$level])) {
							$convertedLevels[$newData['levels'][$level]] = $newData['levels'][$level];
						}
					}
					//$autSet[$newData['id']] = $convertedLevels;
					$autSet['levels'][$newData['id']] = $convertedLevels;
				}
			}

			return $autSet;
		}

		return null;
	}

	private static function migrateMembers(array $step, array $convertTable, ?LockInterface $lock = null): void
	{
		global $wpdb;

		$levels = new dataImporter('mw_membership_levels');
		$months = new dataImporter('mw_membership_months');
		$tasks = new dataImporter('mw_user_tasks');

		$tasksConvertTable = self::getTasksConvertTable();

		$limit = 250;
		$offset = (int) ($step['value'] ?? 0);

		if ($step !== []) {
			// if it is not first iteration
			$offset += $limit;
		}

		do {
			self::addInstallationStep('members', $offset, $lock);

			$oldMembers = $wpdb->get_results("SELECT DISTINCT ID, membershipsTable.meta_value as membership, checlistTable.meta_value as checklist FROM $wpdb->users
						LEFT JOIN $wpdb->usermeta as membershipsTable ON (membershipsTable.user_id = ID AND membershipsTable.meta_key = 'cms_member')
						LEFT JOIN $wpdb->usermeta as checlistTable ON (checlistTable.user_id = ID AND checlistTable.meta_key = 'checklist')
						LIMIT $limit OFFSET $offset");
			$offset += $limit;

			foreach ($oldMembers as $member) {

				$lastActivities = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->usermeta
						WHERE user_id = $member->ID AND meta_key LIKE 'mw_member_last_activity_%'");

				$lastActivitiesArray = [];
				foreach ($lastActivities as $activity) {
					$lastActivitiesArray[$activity->meta_key] = $activity->meta_value;
				}

				// memberships
				if ($member->membership) {
					$membershipData = unserialize($member->membership);

					foreach ($membershipData as $oldSectionId => $oldData) {
						if (isset($convertTable[$oldSectionId])) {

							$lastActivityField = 'mw_member_last_activity_' . $oldSectionId;
							$lastActivity = isset($lastActivitiesArray[$lastActivityField]) ? date('Y-m-d H:i:s', $lastActivitiesArray[$lastActivityField]) : null;

							$data = [
								'member_section_id' => $convertTable[$oldSectionId]['id'],
								'user_id' => $member->ID,
								'end' => isset($oldData['end']) && $oldData['end'] ? date('Y-m-d', strtotime($oldData['end'])) : null,
								'start' => date('Y-m-d H:i:s', strtotime($oldData['date'] . ' ' . $oldData['time'])),
								'last_activity' => $lastActivity,
							];
							$membershipId = MWDB()->insert('mw_membership', $data);

							if ($membershipId) {
								// membership levels
								if (isset($oldData['levels'])) {
									foreach ($oldData['levels'] as $level => $lVal) {
										if (isset($convertTable[$oldSectionId]['levels'][$level])) {
											$levels->add([
												$membershipId,
												$convertTable[$oldSectionId]['levels'][$level],
												'NULL',
												'NULL',
											]);
										}
									}
								}

								// membership months
								if (isset($oldData['months'])) {
									foreach ($oldData['months'] as $month => $mVal) {
										$months->add([
											$membershipId,
											$month,
										]);
									}
								}
							}
						}
					}
				}
			}

			$levels->push();
			$months->push();

			foreach ($oldMembers as $member) {
				// member tasks
				if ($member->checklist) {
					$checklists = @unserialize($member->checklist);
					if ($checklists === false || !is_array($checklists)) {
						mwlog(MWLS_MEMBER, 'Invalid value `$member->checklist`. Cannot unserialize.');
						$checklists = [];
					}

					foreach ($checklists as $checklistPageId => $checklist) {
						if (isset($tasksConvertTable[$checklistPageId])) {
							foreach ($checklist as $taskId => $task) {
								if (isset($tasksConvertTable[$checklistPageId][$taskId])) {
									$tasks->add([
										$member->ID,
										$tasksConvertTable[$checklistPageId][$taskId],
									]);
								}
							}
						}
					}
				}
			}

			$tasks->push();
		} while ($oldMembers);
	}

	private static function migrateMemberTasks(array $step, ?LockInterface $lock = null): void
	{
		global $wpdb;

		$tasks = new dataImporter('mw_user_tasks');

		$tasksConvertTable = self::getTasksConvertTable();

		if (count($tasksConvertTable)) {
			$limit = 250;
			$offset = (int) ($step['value'] ?? 0);

			if ($step !== []) {
				// if it is not first iteration
				$offset += $limit;
			}

			do {
				self::addInstallationStep('member-tasks', $offset, $lock);

				$oldMembers = $wpdb->get_results("SELECT DISTINCT ID, checlistTable.meta_value as checklist FROM $wpdb->users
						LEFT JOIN $wpdb->usermeta as checlistTable ON (checlistTable.user_id = ID AND checlistTable.meta_key = 'checklist')
						LIMIT $limit OFFSET $offset");
				$offset += $limit;

				foreach ($oldMembers as $member) {
					// member tasks
					if ($member->checklist) {
						$checklists = @unserialize($member->checklist);
						if ($checklists === false || !is_array($checklists)) {
							mwlog(MWLS_MEMBER, 'Invalid value `$member->checklist`. Cannot unserialize.');
							$checklists = [];
						}

						foreach ($checklists as $checklistPageId => $checklist) {
							if (isset($tasksConvertTable[$checklistPageId])) {
								foreach ($checklist as $taskId => $task) {
									if (isset($tasksConvertTable[$checklistPageId][$taskId])) {
										$tasks->add([
											$member->ID,
											$tasksConvertTable[$checklistPageId][$taskId],
										]);
									}
								}
							}
						}
					}
				}

				$tasks->push();
			} while ($oldMembers);
		}
	}

	private static function migrateMemberPages(array $step, array $convertTable, ?LockInterface $lock = null, bool $secondTime = false): void
	{
		global $wpdb;

		$tasksConvertTable = self::getTasksConvertTable();

		$limit = 500;
		$offset = (int) ($step['value'] ?? 0);

		if ($step !== []) {
			// if it is not first iteration
			$offset += $limit;
		}

		do {
			if ($secondTime) {
				self::addInstallationStep('member-pages-2', $offset, $lock);
				$oldPages = $wpdb->get_results("SELECT ID, meta_value FROM $wpdb->postmeta, $wpdb->posts WHERE post_id = ID AND meta_key = 'page_member' AND meta_value LIKE '%ember_page\";i:1;%' LIMIT $limit OFFSET $offset");
			} else {
				self::addInstallationStep('member-pages', $offset, $lock);
				$oldPages = $wpdb->get_results("SELECT ID, meta_value FROM $wpdb->postmeta, $wpdb->posts WHERE post_id = ID AND meta_key = 'page_member' LIMIT $limit OFFSET $offset");
			}
			$offset += $limit;

			foreach ($oldPages as $page) {
				$oldSetting = unserialize($page->meta_value);
				if (isset($oldSetting['member_page']) && ($oldSetting['member_page'] === '1' || $oldSetting['member_page'] === 1)) {
					// page
					$oldSectionId = $oldSetting['member_section']['section'];
					$month = null;
					$accessInfo = '';
					$visibility = $oldSetting['visibility'] ?? '';

					if ($visibility === 'date') {
						$accessInfo = date('Y-m-d H:i:s', strtotime($oldSetting['evergreen_datetime']['date'] . ' ' . ($oldSetting['evergreen_datetime']['hour'] ?: '0') . ':' . ($oldSetting['evergreen_datetime']['minute'] ?: '0')));
					} elseif ($visibility === 'checklist') {
						$accessInfo = $oldSetting['checklist_page'];
					} elseif ($visibility === 'month') {
						$month = $oldSetting['mw_month_member']['year'] . $oldSetting['mw_month_member']['month'];
					} elseif ($visibility === 'evergreen') {
						$accessInfo = $oldSetting['evergreen'];
					}

					if (isset($convertTable[$oldSectionId])) {
						$data = [
							'post_id' => $page->ID,
							'member_section_id' => $convertTable[$oldSectionId]['id'],
							'access_type' => $visibility,
							'access_info' => $accessInfo,
							'month_page_id' => isset($oldSetting['month_member_buy_page']) && $oldSetting['month_member_buy_page'] && get_post($oldSetting['month_member_buy_page']) !== null ? $oldSetting['month_member_buy_page'] : null,
							'month' => $month,
							'hide_in_list' => isset($oldSetting['hide_in_month_member_list']) ? 1 : 0,
						];
						$memberPageId = MWDB()->insert('mw_member_pages', $data);

						if ($memberPageId) {
							// member page levels
							if (isset($oldSetting['member_section'][$oldSectionId]['levels'])) {
								$levels = [];
								foreach ($oldSetting['member_section'][$oldSectionId]['levels'] as $level => $status) {
									if (isset($convertTable[$oldSectionId]['levels'][$level])) {
										$levels[] = [
											$memberPageId,
											$convertTable[$oldSectionId]['levels'][$level],
										];
									}
								}
								if (count($levels)) {
									MWDB()->insertRows('mw_member_page_levels', $levels);
								}
							}

							// member page tasks
							if (isset($oldSetting['checklist'])) {
								foreach ($oldSetting['checklist'] as $taskId => $task) {
									$taskData = [
										'task' => $task['text'],
										'member_page_id' => $memberPageId,
										'task_order' => $taskId,
									];

									$taskNewId = MWDB()->insert('mw_member_page_tasks', $taskData);

									$tasksConvertTable[$page->ID][$taskId] = $taskNewId;
								}
							}
						}
					}
				}
			}

			self::saveTasksConvertTable($tasksConvertTable);
		} while ($oldPages);
	}

	private static function migrateMemberSections(array $members, array &$convertTable): void
	{
		global $wpdb;

		$header = get_option('member_header');
		$footer = get_option('member_footer');
		$popups = get_option('member_popups');
		$appearance = get_option('member_appearance');
		$fapi_notification = get_option('fapi_notification');

		$lastSectionId = array_key_last($members['members']);
		MWDB()->sql('ALTER TABLE ' . $wpdb->prefix . 'mw_member_sections AUTO_INCREMENT=' . (intval($lastSectionId) + 1));

		$newSections = [];

		foreach ($members['members'] as $oldSectionId => $oldSection) {
			// section
			$newSectionArgs = [
				'name' => $oldSection['name'] ?? '',
				'dashboard_page_id' => isset($oldSection['dashboard']) && $oldSection['dashboard'] && get_post($oldSection['dashboard']) !== null ? $oldSection['dashboard'] : null,
				'login_page_id' => isset($oldSection['login']) && $oldSection['login'] && get_post($oldSection['login']) !== null ? $oldSection['login'] : null,
				'noaccess_page_id' => null,
				'extend_page_id' => isset($oldSection['extend_member_page']) && $oldSection['extend_member_page'] && get_post($oldSection['extend_member_page']) !== null ? $oldSection['extend_member_page'] : null,
				'expire_page_id' => isset($oldSection['expire_page']) && $oldSection['expire_page'] && get_post($oldSection['expire_page']) !== null ? $oldSection['expire_page'] : null,
				'hide_evergreen' => isset($oldSection['evergreen_show']) && $oldSection['evergreen_show'] ? 1 : 0,
				'send_notifications' => isset($fapi_notification['members'][$oldSectionId]['notification_onemail']) ? 1 : 0,
				'notification_email' => $fapi_notification['members'][$oldSectionId]['notification_onemail'] ?? '',
			];
			$newSectionId = MWDB()->insert('mw_member_sections', $newSectionArgs);

			$newSections[] = [
				'id' => $newSectionId,
				'name' => $oldSection['name'] ?? '',
			];

			if ($newSectionId) {
				$convertTable[$oldSectionId]['id'] = $newSectionId;

				// levels
				if (isset($oldSection['levels']) && count($oldSection['levels'])) {
					foreach ($oldSection['levels'] as $oldLevelId => $oldLevel) {
						$newLevelId = MWDB()->insert('mw_member_section_levels', [
							'member_section_id' => $newSectionId,
							'name' => $oldLevel['name'] ?? '',
							'noaccess_text' => $oldLevel['noaccess_text'] ?? '',
							'noaccess_page_id' => isset($oldLevel['noaccess_page']) && $oldLevel['noaccess_page'] && get_post($oldLevel['noaccess_page']) !== null ? $oldLevel['noaccess_page'] : null,
							'extend_page_id' => null,
							'expire_page_id' => null,
							'show_level_pages' => isset($oldLevel['show']) && $oldLevel['show'] ? 1 : 0,
						]);

						$convertTable[$oldSectionId]['levels'][$oldLevelId] = $newLevelId;
					}
				}

				// emails
				$emails = [
					'addMembership' => [
						'content' => $oldSection['email_text'] ?? '',
						'subject' => $oldSection['email_subject'] ?? '',
					],
					'addLevel' => [
						'content' => $oldSection['level_email_text'] ?? '',
						'subject' => $oldSection['level_email_subject'] ?? '',
					],
					'extendMembership' => [
						'content' => $oldSection['expiration_email_text'] ?? '',
						'subject' => $oldSection['expiration_email_subject'] ?? '',
					],
				];
				Email::saveEmailsSetting($newSectionId, 'member', $emails);

				// options
				if (isset($header['members'][$oldSectionId])) {
					MWDB()->setOption('mwms_header_' . $newSectionId, $header['members'][$oldSectionId]);
				}
				if (isset($footer['members'][$oldSectionId])) {
					MWDB()->setOption('mwms_footer_' . $newSectionId, $footer['members'][$oldSectionId]);
				}
				if (isset($popups['members'][$oldSectionId])) {
					MWDB()->setOption('mwms_popups_' . $newSectionId, $popups['members'][$oldSectionId]);
				}
				if (isset($appearance['members'][$oldSectionId])) {
					MWDB()->setOption('mwms_appearance_' . $newSectionId, $appearance['members'][$oldSectionId]);
				}
			}
		}

		MWDB()->setOption('mwms_migration_compatibility', $convertTable);
		mwlog(MWLS_MEMBER, sprintf('Sending convert table to SimpleShop: "%s"', print_r($convertTable, true)));
		$response = mwSellingApi()->sendConversionTable($convertTable, $newSections);
		mwlog(MWLS_MEMBER, sprintf('SimpleShop convert response: %s', is_array($response) ? print_r($response, true) : gettype($response)));
	}

	private static function printTime($text, $start)
	{
		$time = microtime(true);
		echo $text . ': ' . ($time - $start) . ' s<br><br>';

		return $time;
	}

	private static function getTableEngine(string $tableName): ?string
	{
		$status = (array) MWDB()->getRow("SHOW TABLE STATUS WHERE Name = '$tableName'");

		return $status['Engine'] ?? null;
	}

	private static function getInstallationSteps(): array
	{
		return get_option(self::OPTION_INSTALLER_STEPS, []) ?: [];
	}

	private static function getTasksConvertTable(): array
	{
		return get_option(self::OPTION_TASKS_CONVERT_TABLE, []) ?: [];
	}

	private static function saveTasksConvertTable(array $tasksConvertTable): void
	{
		update_option(self::OPTION_TASKS_CONVERT_TABLE, $tasksConvertTable);
	}

	private static function cleanup(): void
	{
//		delete_option(self::OPTION_TASKS_CONVERT_TABLE); /// TODO
//		delete_option(self::OPTION_INSTALLER_STEPS); /// TODO
	}

	private static function markInstallationStepsAsDone(?array $steps = null): array
	{
		$steps ??= self::getInstallationSteps();

		foreach ($steps as $key => $stepTmp) {
			$steps[$key]['status'] = 'done';
		}

		update_option(self::OPTION_INSTALLER_STEPS, $steps);

		return $steps;
	}

	private static function addInstallationStep(string $step, $value = null, ?LockInterface $lock = null): void
	{
		$steps = self::markInstallationStepsAsDone();
		self::doABreakIfNeeded($lock);

		$timestamp = time();
		$pid = getmypid();

		$steps[$step] = [
			'step' => $step,
			'time' => $timestamp,
			'pid' => $pid,
			'value' => $value,
			'status' => 'running',
		];
		$scalarValue = is_scalar($value) ? (string) $value : 'not-scalar';
		mwlog(MWLS_MEMBER, sprintf('Updating installation step to "%s", time: %d, PID: %d, value: %s', $step, $timestamp, $pid, $scalarValue));
		update_option(self::OPTION_INSTALLER_STEPS, $steps);
	}

	private static function doABreakIfNeeded(?LockInterface $lock = null)
	{
		$currentTimestamp = time();
		$maximumExecutionTimeout = 20;

		if (self::$startedTimestamp !== null && ($currentTimestamp - self::$startedTimestamp > $maximumExecutionTimeout)) {
			mwlog(MWLS_MEMBER, 'Member installation - reloading browser.');
			// Automaticaly reload the page
			echo '<script type="application/javascript">window.location.reload()</script>';
			if ($lock !== null) {
				$lock->release();
			}

			// TODO maybe show some maintenance page?
			die();
		}
	}

	private static function installTables(?LockInterface $lock = null): bool
	{
		global $wpdb;

		MWDB()->sql("SET @@sql_mode := REPLACE(REPLACE(@@sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '');");

		self::addInstallationStep('engine-posts', null, $lock);
		$engine = self::getTableEngine($wpdb->prefix . 'posts');
		if ($engine === null || strtolower($engine) !== 'innodb') {
			// TODO optimise - it kills webs with large `wp_posts` table
			MWDB()->sql("ALTER TABLE {$wpdb->prefix}posts ENGINE=InnoDB;");
		}

		self::addInstallationStep('engine-users', null, $lock);
		$engine = self::getTableEngine($wpdb->prefix . 'users');
		if ($engine === null || strtolower($engine) !== 'innodb') {
			MWDB()->sql("ALTER TABLE {$wpdb->prefix}users ENGINE=InnoDB;");
		}

		self::addInstallationStep('tables', null, $lock);
		// member sections
		MWDB()->createTable('mw_member_sections', '
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(200) NOT NULL,
			dashboard_page_id bigint(20) UNSIGNED DEFAULT NULL,
			login_page_id bigint(20) UNSIGNED DEFAULT NULL,
			noaccess_page_id bigint(20) UNSIGNED DEFAULT NULL,
			extend_page_id bigint(20) UNSIGNED DEFAULT NULL,
			expire_page_id bigint(20) UNSIGNED DEFAULT NULL,
			hide_evergreen tinyint(1) NOT NULL DEFAULT 1,
			send_notifications tinyint(1) NOT NULL DEFAULT 0,
			notification_email varchar(100) NOT NULL,
			CONSTRAINT fkmw_member_sections_1 FOREIGN KEY (dashboard_page_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE SET NULL,
			CONSTRAINT fkmw_member_sections_2 FOREIGN KEY (expire_page_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE SET NULL,
			CONSTRAINT fkmw_member_sections_3 FOREIGN KEY (extend_page_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE SET NULL,
			CONSTRAINT fkmw_member_sections_4 FOREIGN KEY (login_page_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE SET NULL,
			CONSTRAINT fkmw_member_sections_5 FOREIGN KEY (noaccess_page_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE SET NULL,
			PRIMARY KEY (id)');

		// member section levels
		MWDB()->createTable('mw_member_section_levels', '
			id int(11) NOT NULL AUTO_INCREMENT,
			member_section_id int(11) NOT NULL,
			name varchar(200) NOT NULL,
			noaccess_text text NOT NULL,
			noaccess_page_id bigint(20) UNSIGNED DEFAULT NULL,
			extend_page_id bigint(20) UNSIGNED DEFAULT NULL,
			expire_page_id bigint(20) UNSIGNED DEFAULT NULL,
			show_level_pages tinyint(1) NOT NULL DEFAULT 0,
			CONSTRAINT fkmw_member_section_levels_1 FOREIGN KEY (member_section_id) REFERENCES ' . $wpdb->prefix . 'mw_member_sections (id) ON DELETE CASCADE,
			CONSTRAINT fkmw_member_section_levels_2 FOREIGN KEY (expire_page_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE SET NULL,
			CONSTRAINT fkmw_member_section_levels_3 FOREIGN KEY (extend_page_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE SET NULL,
			CONSTRAINT fkmw_member_section_levels_4 FOREIGN KEY (noaccess_page_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE SET NULL,
			PRIMARY KEY (id)');

		// memberships
		MWDB()->createTable('mw_membership', '
			id bigint(20) NOT NULL AUTO_INCREMENT,
			member_section_id int(11) NOT NULL,
			user_id bigint(20) UNSIGNED NOT NULL,
			start datetime NOT NULL,
			end date DEFAULT NULL,
			last_activity datetime DEFAULT NULL,
			CONSTRAINT fkmw_membership_1 FOREIGN KEY (member_section_id) REFERENCES ' . $wpdb->prefix . 'mw_member_sections (id) ON DELETE CASCADE,
		 	CONSTRAINT fkmw_membership_2 FOREIGN KEY (user_id) REFERENCES ' . $wpdb->prefix . 'users (ID) ON DELETE CASCADE,
			PRIMARY KEY (id)');

		// memberships levels
		MWDB()->createTable('mw_membership_levels', '
			membership_id bigint(20) NOT NULL,
			member_level_id int(11) NOT NULL,
			start datetime DEFAULT NULL,
			end datetime DEFAULT NULL,
			CONSTRAINT fkmw_membership_levels_1 FOREIGN KEY (member_level_id) REFERENCES ' . $wpdb->prefix . 'mw_member_section_levels (id) ON DELETE CASCADE,
			CONSTRAINT fkmw_membership_levels_2 FOREIGN KEY (membership_id) REFERENCES ' . $wpdb->prefix . 'mw_membership (id) ON DELETE CASCADE,
			PRIMARY KEY (membership_id,member_level_id)');

		// memberships levels
		MWDB()->createTable('mw_membership_months', '
			membership_id bigint(20) NOT NULL,
			month char(6) NOT NULL,
			CONSTRAINT fk_membership_months_1 FOREIGN KEY (membership_id) REFERENCES ' . $wpdb->prefix . 'mw_membership (id) ON DELETE CASCADE,
			PRIMARY KEY (membership_id,month)');

		// member pages
		MWDB()->createTable('mw_member_pages', '
			mp_id bigint(20) NOT NULL AUTO_INCREMENT,
			member_section_id int(11) NOT NULL,
			post_id bigint(20) UNSIGNED NOT NULL,
			access_type varchar(100) NOT NULL,
			access_info varchar(200) NOT NULL,
			month varchar(100) DEFAULT NULL,
		 	month_page_id bigint(20) UNSIGNED DEFAULT NULL,
			hide_in_list tinyint(1) NOT NULL DEFAULT 0,
			CONSTRAINT fkmw_member_pages_1 FOREIGN KEY (member_section_id) REFERENCES ' . $wpdb->prefix . 'mw_member_sections (id) ON DELETE CASCADE,
			CONSTRAINT fkmw_member_pages_2 FOREIGN KEY (post_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE CASCADE,
			CONSTRAINT fkmw_member_pages_3 FOREIGN KEY (month_page_id) REFERENCES ' . $wpdb->prefix . 'posts (ID) ON DELETE SET NULL,
			PRIMARY KEY (mp_id),
		  	UNIQUE KEY (member_section_id,post_id),
		  	KEY (month_page_id)');

		// member page levels
		MWDB()->createTable('mw_member_page_levels', '
			member_page_id bigint(20) NOT NULL,
			member_level_id int(11) NOT NULL,
			CONSTRAINT fkmw_member_page_levels_1 FOREIGN KEY (member_page_id) REFERENCES ' . $wpdb->prefix . 'mw_member_pages (mp_id) ON DELETE CASCADE,
			CONSTRAINT fkmw_member_page_levels_2 FOREIGN KEY (member_level_id) REFERENCES ' . $wpdb->prefix . 'mw_member_section_levels (id) ON DELETE CASCADE,
			PRIMARY KEY (member_page_id,member_level_id)');

		// member page tasks
		MWDB()->createTable('mw_member_page_tasks', '
			mpt_id bigint(20) NOT NULL AUTO_INCREMENT,
			member_page_id bigint(20) NOT NULL,
			task text NOT NULL,
			task_order bigint(20) NOT NULL,
			CONSTRAINT fkmw_member_page_tasks_1 FOREIGN KEY (member_page_id) REFERENCES ' . $wpdb->prefix . 'mw_member_pages (mp_id) ON DELETE CASCADE ON UPDATE NO ACTION,
			PRIMARY KEY (mpt_id)');

		// member tasks
		MWDB()->createTable('mw_user_tasks', '
			user_id bigint(20) UNSIGNED NOT NULL,
			task_id bigint(20) NOT NULL,
			CONSTRAINT fkmw_user_tasks_1 FOREIGN KEY (task_id) REFERENCES ' . $wpdb->prefix . 'mw_member_page_tasks (mpt_id) ON DELETE CASCADE ON UPDATE NO ACTION,
			CONSTRAINT fkmw_user_tasks_2 FOREIGN KEY (user_id) REFERENCES ' . $wpdb->prefix . 'users (ID) ON DELETE CASCADE,
			PRIMARY KEY (user_id,task_id)');

		return $wpdb->last_error ? false : true;
	}

	public static function mw_modify_htaccess_cgi()
	{
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		//require_once( ABSPATH . 'wp-admin/includes/misc.php' );

		$home_path = get_home_path();
		$htaccess_file = $home_path . '.htaccess';

		if ((!file_exists($htaccess_file) && is_writable($home_path)) || is_writable($htaccess_file)) {
			/*
			$str = file_get_contents( $htaccess_file );

			if (strpos($str, '# BEGIN Mioweb') === false) {
			$str = '# BEGIN Mioweb'.PHP_EOL.'# END Mioweb'.PHP_EOL.$str;

			file_put_contents( $htaccess_file, $str );
			}*/

			$rules = ['RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]'];

			return insert_with_markers_on_top($htaccess_file, 'Mioweb', $rules);
		}

		return false;
	}

	public static function set_htaccess_for_cgi($action = 'install member module')
	{
		$return = self::mw_modify_htaccess_cgi();
		if ($return) {
			mwlog(MWLS_MEMBER, 'Htaccess modified for CGI (during ' . $action . ')', MWLL_INFO);
		} else {
			update_option('mw_error_modify_htaccess_cgi', '1');
			mwlog(MWLS_MEMBER, 'Htaccess modify for CGI failed (during ' . $action . ')', MWLL_ERROR);
		}

		return $return;
	}

}

class dataImporter
{

	private array $_data = [];

	private string $_table;

	public function __construct(string $table)
	{
		$this->_table = $table;
	}

	public function add(array $row)
	{
		$this->_data[] = $row;
		if (count($this->_data) > 30000) {
			$this->push();
		}
	}

	public function push(): void
	{
		if (count($this->_data)) {
			MWDB()->insertRows($this->_table, $this->_data);
			$this->_data = [];
		}
	}
}
