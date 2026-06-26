<?php

use Nette\Utils\Validators;
use Mioweb\Member\Membership;
use Mioweb\Member\MemberPage;
use Mioweb\Member\MembershipEmailing;

class mwMember extends mwUser
{

	/** @var null|array of Membership */
	private $_memberships = null;

	private $_memberFields;

	private $_customFields;

	private $_lastActivity;

	public static function getAll($args = [], $paged = false): array
	{
		global $wpdb;

		$page = $args['paged'] ?? 1;
		$perPage = $args['number'] ?? -1;
		$memberSectionId = $args['member_section_id'] ?? null;
		$levels = $args['levels'] ?? null;
		//$onlyActive = isset($args['activity']);

		$limit = '';
		$where = '1 = 1';
		$from = $wpdb->users;

		if ($memberSectionId) {
			$from = $wpdb->prefix . 'mw_membership, ' . $from;
			$where .= $wpdb->prepare(' AND member_section_id = %d AND ' . $wpdb->prefix . 'mw_membership.user_id = ' . $wpdb->users . '.ID', $memberSectionId);
		}

		if ($levels && count($levels)) {
			$from = $wpdb->prefix . 'mw_membership_levels, ' . $from;
			$where .= ' AND ' . $wpdb->prefix . 'mw_membership_levels.membership_id = ' . $wpdb->prefix . 'mw_membership.id AND (';
			$prepared = [];
			foreach ($levels as $level) {
				$prepared[] = $wpdb->prepare('member_level_id = %d', $level);
			}
			$where .= implode(' OR ', $prepared);
			$where .= ')';
		}

		$joins = "INNER JOIN {$wpdb->prefix}usermeta ON ( {$wpdb->prefix}users.ID = {$wpdb->prefix}usermeta.user_id ) ";
		if ($args['show_in_memberlist'] ?? false) {
			$where .= " AND ( {$wpdb->prefix}usermeta.meta_key = 'mw_show_inmemberlist' AND {$wpdb->prefix}usermeta.meta_value = '1' )";
		} else {
			$where .= " AND ( ( {$wpdb->prefix}usermeta.meta_key = '{$wpdb->prefix}capabilities' AND {$wpdb->prefix}usermeta.meta_value LIKE '%\"member\"%' ) OR ( {$wpdb->prefix}usermeta.meta_key = '{$wpdb->prefix}capabilities' AND {$wpdb->prefix}usermeta.meta_value LIKE '%\"subscriber\"%' ) )";
		}

		if ($args['search'] ?? '') {
			$where .= ' AND (';
			$where .= $wpdb->prepare('user_login LIKE %s', '%' . $args['search'] . '%');
			$where .= ' OR ' . $wpdb->prepare('user_url LIKE %s', '%' . $args['search'] . '%');
			$where .= ' OR ' . $wpdb->prepare('user_email LIKE %s', '%' . $args['search'] . '%');
			$where .= ' OR ' . $wpdb->prepare('user_nicename LIKE %s', '%' . $args['search'] . '%');
			$where .= ' OR ' . $wpdb->prepare('display_name LIKE %s', '%' . $args['search'] . '%');
			$where .= ')';
		}

		// fields
		$fields = $wpdb->users . '.*';

		// Limit.
		if ($perPage > 0) {
			$limit = $wpdb->prepare('LIMIT %d, %d', $perPage * ($page - 1), $perPage);
		}
		$items = $wpdb->get_results("SELECT $fields FROM $from $joins WHERE $where ORDER BY display_name ASC $limit");

		foreach ($items as $key => $user) {
			$items[$key ] = static::createNew(new WP_User($user));
		}

		if ($paged) {
			$totalUsers = (int) $wpdb->get_var("SELECT COUNT({$wpdb->users}.ID) FROM $from $joins WHERE $where");

			return [
				'items' => $items,
				'pages' => ceil($totalUsers / $perPage),
				'count' => $totalUsers,
			];
		} else {
			return $items;
		}

		return $results;
	}

	function isLogged()
	{
		return $this->getId() ? true : false;
	}

	function isActive(): bool
	{
		$lastActivity = $this->getLastActivity();

		return self::processLastActivity($lastActivity);
	}

	function isActiveInMemberSection(int $memberSection_id): bool
	{
		$lastActivity = $this->getLastActivityInMemberSection($memberSection_id);

		return self::processLastActivity($lastActivity);
	}

	public static function processLastActivity(?string $lastActivity): bool
	{
		return $lastActivity && $lastActivity > current_time('timestamp') - 2592000 ? true : false;
	}

	function saveActivity(int $memberSection_id): void
	{
		if ($this->hasAccess($memberSection_id)) {
			$this->getMembership($memberSection_id)->saveActivity();
		}
	}

	function getLastActivity(): ?string
	{
		if (!$this->_lastActivity) {
			$last = 0;
			foreach ($this->getAllMemberships() as $membership) {
				if ($last < $membership->getLastActivity()) {
					$last = $membership->getLastActivity();
				}
			}
			$this->_lastActivity = $last;
		}

		return $this->_lastActivity;
	}

	function getLastActivityDate($type = 'date', $empty = '-'): string
	{
		$time = $this->getLastActivity();

		if (!$time) {
			return $empty;
		}

		if ($type == 'diff') {
			return __('před', 'cms') . ' ' . human_time_diff(current_time('timestamp'), $time);
		}

		return mwPrintDate($time, $type);
	}

	function getLastActivityInMemberSection(int $memberSection_id): ?string
	{
		$membership = $this->getMembership($memberSection_id);
		if ($membership) {
			return $membership->getLastActivity();
		}

		return null;
	}

	function showInMemberList(): bool
	{
		return $this->getUserMeta('mw_show_inmemberlist') ? true : false;
	}

	function getDomain(): string
	{
		$this->getMemberFields();

		return $this->_memberFields['domain'] ?? '';
	}

	function hideEmailInMemberList(): bool
	{
		$this->getMemberFields();

		return isset($this->_memberFields['hide_email']) && $this->_memberFields['hide_email'];
	}

	function getMemberFields()
	{
		if (!$this->_memberFields) {
			$this->_memberFields = $this->getUserMeta('member_fields');
		}

		return $this->_memberFields;
	}

	function getCustomField($key): string
	{
		$this->getCustomFields();

		return $this->_customFields[$key] ?? '';
	}

	function getCustomFields()
	{
		if (!$this->_customFields) {
			$this->_customFields = $this->getUserMeta('member_custom_field');
		}

		return $this->_customFields;
	}

	function hasAccess(int $memberSectionId): bool
	{
		return $this->getMembership($memberSectionId) !== null;
	}

	function hasExpiredAccess(int $memberSectionId): bool
	{
		$membership = $this->getMembership($memberSectionId);
		if ($membership === null) {
			return false;
		}

		return $membership->isExpired();
	}

	function getMembership(int $memberSectionId, bool $forceReload = false): ?Membership
	{
		return $this->getAllMemberships($forceReload)[$memberSectionId] ?? null;
	}

	function getAllMemberships(bool $forceReload = false): array
	{
		if ($this->_memberships === null || $forceReload) {
			$this->_memberships = Membership::getAll($this->getId());
		}

		return $this->_memberships;
	}

	public static function getCustomChecklist(int $userId, string $id): array
	{
		$checklistsData = get_user_meta($userId, 'checklist', true);

		return isset($checklistsData[$id]) && is_array($checklistsData[$id]) ? $checklistsData[$id] : [];
	}

	public static function saveCustomChecklist(int $userId, string $id, array $checklist): void
	{
		$checklistsData = get_user_meta($userId, 'checklist', true);

		if ($checklistsData === '') {
			$checklistsData = [];
		}

		if (!empty($checklist)) {
			$checklistsData[$id] = $checklist;
		} else {
			if (isset($checklistsData[$id])) {
				unset($checklistsData[$id]);
			}
		}
		update_user_meta($userId, 'checklist', $checklistsData);
	}

	public static function saveMemberChecklist_ajax()
	{
		if (isset($_POST['taskId']) && isset($_POST['userId'])) {
			if ($_POST['type'] === 'page') {
				// save user task for page
				if ($_POST['checked'] === '1') {
					MWDB()->insert('mw_user_tasks', [
						'task_id' => $_POST['taskId'],
						'user_id' => $_POST['userId'],
					]);
				} else {
					MWDB()->delete('mw_user_tasks', [
						'task_id' => $_POST['taskId'],
						'user_id' => $_POST['userId'],
					]);
				}
			} else {
				// save user task for custom checklist
				$checklist = self::getCustomChecklist($_POST['userId'], $_POST['checklistId']);
				if ($_POST['checked'] === '1') {
					$checklist[$_POST['taskId']] = 'on';
				} else {
					unset($checklist[$_POST['taskId']]);
				}
				print_r($checklist);
				self::saveCustomChecklist($_POST['userId'], $_POST['checklistId'], $checklist);
			}
		}
		die();
	}

	function getAllProgress()
	{
		$progress = [];

		$sumSections = 0;
		$sumPercent = 0;

		foreach (mwMemberModule()->getMemberSections() as $section) {
			$percent = $this->getProgress($section->getId(), 'member_section', true);
			if ($percent !== null) {
				$sumSections++;
				$sumPercent += $percent;

				$progress[$section->getId()] = [
					'percent' => $percent,
					'name' => $section->getName(),
				];
			}
		}

		return [
			'percent' => $sumSections ? round($sumPercent / $sumSections) . '%' : '-',
			'progress' => $progress,
		];
	}

	function getProgress(int $id, string $for = 'page', bool $empty = false): ?int
	{
		$percentage = null;

		if ($for === 'page') {
			global $wpdb;
			$query = 'SELECT count(1) as total, count(user_id) as completed, count(1) - count(user_id) as not_completed, 100.0 * count(user_id) / count(1) as percentage '
				. 'FROM ' . $wpdb->posts . ' as posts, ' . $wpdb->prefix . 'mw_member_pages as member_pages, ' . $wpdb->prefix . 'mw_member_page_tasks as tasks '
				. 'LEFT JOIN ' . $wpdb->prefix . 'mw_user_tasks ON user_id = ' . $this->getId() . ' AND task_id = mpt_id '
				. 'WHERE tasks.member_page_id = member_pages.mp_id AND member_pages.post_id = ' . $id . ' AND member_pages.post_id = posts.ID AND posts.post_status != "trash"';
			$progress = MWDB()->getRow($query);
			$percentage = $progress ? $progress->percentage : null;
		} elseif ($for === 'parent') {
			$tasks = $this->getChildsTasksOf($id);
			if ($tasks) {
				$completed = 0;
				foreach ($tasks as $task) {
					if ($task->user_id) {
						$completed++;
					}
				}

				$percentage = 100.0 * $completed / count($tasks);
			}
		} elseif ($for === 'member_section') {
			global $wpdb;
			$query = 'SELECT count(1) as total, count(user_id) as completed, count(1) - count(user_id) as not_completed, 100.0 * count(user_id) / count(1) as percentage '
				. 'FROM ' . $wpdb->posts . ' as posts, ' . $wpdb->prefix . 'mw_member_pages as member_pages, ' . $wpdb->prefix . 'mw_member_page_tasks as tasks '
				. 'LEFT JOIN ' . $wpdb->prefix . 'mw_user_tasks ON user_id = ' . $this->getId() . ' AND task_id = mpt_id '
				. 'WHERE tasks.member_page_id = member_pages.mp_id AND member_pages.member_section_id = ' . $id . ' AND member_pages.post_id = posts.ID AND posts.post_status != "trash"';
			$progress = MWDB()->getRow($query);
			$percentage = $progress ? $progress->percentage : null;
		}

		if ($percentage !== null) {
			return round($percentage);
		}

		return $empty ? null : 100;
	}

	function getChildsTasksOf(int $page_id): array
	{
		$page = MemberPage::getOneById($page_id);
		$tasks = [];
		if ($page) {
			// get all tasks of member section
			global $wpdb;
			$query = 'SELECT ID, user_id '
				. 'FROM ' . $wpdb->prefix . 'mw_member_pages, ' . $wpdb->posts . ', ' . $wpdb->prefix . 'mw_member_page_tasks '
				. 'LEFT JOIN ' . $wpdb->prefix . 'mw_user_tasks ON user_id = ' . $this->getId() . ' AND task_id = mpt_id '
				. 'WHERE post_id = ID AND member_page_id = mp_id AND member_section_id = ' . $page->getMemberSectionId() . ' AND post_status != "trash"';

			$allTasks = MWDB()->getResults($query);

			if ($allTasks) {
				// get all pages
				$query = 'SELECT ID, post_parent FROM ' . $wpdb->posts . ' WHERE post_status = "publish"';
				$pages = MWDB()->getResults($query);

				// get all subpages of page with $id
				$children = [];
				$subpages = [];
				foreach ((array) $pages as $page) {
					$children[(int) $page->post_parent ][] = $page;
					//include parent page
					if ($page_id === (int) $page->ID) {
						$subpages[] = $page->ID;
					}
				}

				// Start the search by looking at immediate children.
				if (isset($children[$page_id ])) {
					// Always start at the end of the stack in order to preserve original `$pages` order.
					$to_look = array_reverse($children[$page_id ]);

					while ($to_look) {
						$p = array_pop($to_look);
						$subpages[] = $p->ID;
						if (isset($children[$p->ID ])) {
							foreach (array_reverse($children[$p->ID ]) as $child) {
								// Append to the `$to_look` stack to descend the tree.
								$to_look[] = $child;
							}
						}
					}
				}

				// get tasks of subpages
				foreach ($allTasks as $task) {
					if (in_array($task->ID, $subpages)) {
						$tasks[] = $task;
					}
				}
			}
		}

		return $tasks;
	}

	function getSource(): string
	{
		return $this->getUserMeta('mw_member_source');
	}

	function getSourceText(): string
	{
		$source = $this->getSource();
		$source_val = __('Neznámý', 'cms_member');

		if ($source == 'by_notify') {
			$source_val = __('Notifikace', 'cms_member');
		} elseif ($source == 'by_admin') {
			$source_val = __('Vytvořen ručně', 'cms_member');
		} elseif ($source == 'by_import') {
			$source_val = __('Import členů', 'cms_member');
		} elseif ($source == 'by_automation') {
			$source_val = __('Automatizace po prodeji', 'cms_member');
		} elseif ($source == 'free_registration') {
			$source_val = __('Formulář pro registraci zdarma', 'cms_member');
		} elseif ($source == 'by_api') {
			$source_val = __('API', 'cms_member');
		} elseif ($source == 'by_mw_login') {
			$source_val = __('Mioweb podpora', 'cms_member');
		}

		return $source_val;
	}

	public static function onAddUser_hook($userId, $password, $tosave)
	{
		// source
		update_user_meta($userId, 'mw_member_source', 'by_admin');

		// email
		$sendEmail = isset($tosave['send_user_notification']);

		//  save member data
		self::saveMemberData($userId, $tosave, $sendEmail, $password);
	}

	public static function onSaveUser_hook($userId, $tosave)
	{
		$sendEmail = (bool) ($tosave['send_user_notification'] ?? false);
		self::saveMemberData($userId, $tosave, $sendEmail);
	}

	public static function saveMemberData(int $userId, array $tosave, bool $sendEmail = true, ?string $password = null, string $type = '')
	{
		$message = null;
		$subject = null;
		if ($type) {
			$message = MembershipEmailing::getDefaultMessage($type);
			$subject = MembershipEmailing::getDefaultSubject($type);
		}

		// membership
		self::saveUserMembers($userId, $tosave['member'] ?? [], $sendEmail, $password, $message, $subject);

		// member_info
		if (isset($tosave['member_info'])) {
			// custom fields
			if (isset($tosave['member_info']['custom_fields'])) {
				update_user_meta($userId, 'member_custom_field', $tosave['member_info']['custom_fields']);
			} else {
				delete_user_meta($userId, 'member_custom_field');
			}

			// show member in catalog
			if (isset($tosave['member_info']['show_member'])) {
				update_user_meta($userId, 'mw_show_inmemberlist', $tosave['member_info']['show_member']);
			} else {
				delete_user_meta($userId, 'mw_show_inmemberlist');
			}

			// domain and show email
			if (isset($tosave['member_info']['member_fields'])) {
				update_user_meta($userId, 'member_fields', $tosave['member_info']['member_fields']);
			}
		}
	}

	public static function saveUserMembers(int $userId, array $members, bool $sendEmail = true, ?string $password = null, ?string $message = null, ?string $subject = null): array
	{
		$user = mwMember::getOneById($userId);
		$saved = [];

		if ($user) {
			$membershipsData = self::createMembershipsDataFromMembersField($members, $user);
			$saved = $user->saveMemberships($membershipsData, $sendEmail, $message, $subject, $password);
		}

		return $saved;
	}

	public static function createMembershipsDataFromMembersField(array $list, ?mwMember $member = null): array
	{
		$membershipsData = [];
		foreach ($list as $id => $membership) {
			$membershipId = null;
			if ($member && $member->getMembership($id)) {
				$membershipId = $member->getMembership($id)->getId();
			}

			if (isset($membership['section']) && mwMemberModule()->memberSectionIdExist($id)) {
				$date = $membership['start'] ?: null;
				$time = $membership['time'] ?: null;
				$end = $membership['end'] ?? null;
				$end = $end ? date('Y-m-d', strtotime($end)) : null;

				$membershipsData[] = [
					'membership_id' => $membershipId,
					'member_section_id' => $id,
					'date' => $date,
					'time' => $time,
					'end' => $end,
					'levels' => $membership['levels'] ?? [],
					'months' => $membership['months'] ?? [],
					'add_levels' => false,
					'add_months' => false,
				];
			} elseif ($membershipId) {
				Membership::delete($membershipId);
			}
		}

		return $membershipsData;
	}

	public function updateMembershipsFromMembersField(array $list): array
	{
		$membershipsData = [];

		foreach ($list as $id => $membership) {
			$membershipId = null;
			$date = null;
			$time = null;
			$end = null;
			$oldMembership = $this->getMembership($id);
			if ($oldMembership) {
				$membershipId = $oldMembership->getId();
				$start = $oldMembership->getStart();
				$date = date('Y-m-d', $start);
				$time = date('H:i', $start);

				$endRaw = $oldMembership->getEnd();
				if ((bool) $endRaw) {
					$end = date('Y-m-d', $endRaw);
				}
			}
			if (isset($membership['section']) && mwMemberModule()->memberSectionIdExist($id)) {
				$date = $membership['start'] ?: $date;
				$time = $membership['time'] ?: $time;
				$end = $membership['end'] ?: $end;
				$end = $end ? date('Y-m-d', strtotime($end)) : null;

				$membershipsData[] = [
					'membership_id' => $membershipId,
					'member_section_id' => $id,
					'date' => $date,
					'time' => $time,
					'end' => $end,
					'levels' => $membership['levels'] ?? [],
					'months' => $membership['months'] ?? [],
					'add_levels' => true,
					'add_months' => true,
				];
			}
		}

		return $membershipsData;
	}

	public static function addMember(array $userData, array $membershipsData, bool $sendEmail = true, ?string $message = null, ?string $subject = null, ?string $source = null, ?string $accept = null): ?stdClass
	{
		$userData['meta_input'] = [];

		// gdpr
		if ($accept !== null) {
			$userData['meta_input']['mw_member_accepted'] = [
				'time' => current_time('timestamp', 0),
				'text' => $accept,
			];
		}
		if ($source) {
			$userData['meta_input']['mw_member_source'] = $source;
		}

		$userData['role'] = 'member';

		//new user
		$newUser = self::addUser($userData);

		if ($newUser) {
			$member = self::getOneById($newUser->id);
			$member->saveMemberships($membershipsData, $sendEmail, $message, $subject, $newUser->password);

			return (object) ['member' => $member, 'password' => $newUser->password];
		}

		return null;
	}

	public function saveMemberships(array $membershipsData, bool $sendEmail = true, ?string $message = null, ?string $subject = null, ?string $password = null): array
	{
		$saved = [
			'added' => [],
			'updated' => [],
		];

		foreach ($membershipsData as $membershipData) {
			$memberSectionId = $membershipData['member_section_id'];
			if (!$this->hasAccess($memberSectionId)) {
				$membership = Membership::createNewByArray($this->getId(), $membershipData);
				$saved['added'][$memberSectionId]['levels'] = $membership->setLevels($membershipData['levels'] ?? [], false);
				$saved['added'][$memberSectionId]['months'] = $membership->setMonths($membershipData['months'] ?? [], false);
				$membership->save();

				// list of added
				$saved['added'][$memberSectionId]['section'] = $memberSectionId;
				$saved['added'][$memberSectionId]['expiration'] = $membership->getEnd();
			} else {
				$membership = $this->getMembership($memberSectionId);
				$newLevels = $membership->setLevels($membershipData['levels'] ?? null, $membershipData['add_levels'] ?? true);
				$newMonths = $membership->setMonths($membershipData['months'] ?? null, $membershipData['add_months'] ?? true);

				$newExpiration = false;
				if (array_key_exists('end', $membershipData)) {
					$newExpiration = $membership->setEnd($membershipData['end']);
				}

				$newStart = false;
				// start is used in notifications and automations
				if (array_key_exists('start', $membershipData)) {
					$newStart = $membership->setStart($membershipData['start']);
				// date and time is used in member field
				} elseif (array_key_exists('date', $membershipData) || array_key_exists('time', $membershipData)) {
					$date = isset($membershipData['date']) && $membershipData['date'] ? $membershipData['date'] : date('Y-m-d', $membership->getStart());
					$time = isset($membershipData['time']) && $membershipData['time'] ? $membershipData['time'] : date('H:i:s', $membership->getStart());
					$start = Membership::createStartFromDateAndTime($date, $time);
					$newStart = $membership->setStart($start);
				}

				// end
				$days = $membershipData['days'] ?? null;
				if ($days) {
					$from = $membership->getEnd() > current_time('timestamp') ? $membership->getEnd() : current_time('timestamp');
					$end = date('Y-m-d', $from + ($days * 86400));
					$newExpiration = $membership->setEnd($end);
				}

				$membership->save();

				// new levels
				if (count($newLevels)) {
					$saved['updated'][$memberSectionId]['levels'] = $newLevels;
				}
				// new months
				if (count($newMonths)) {
					$saved['updated'][$memberSectionId]['months'] = $newMonths;
				}
				// new expiration
				if ($newExpiration) {
					$saved['updated'][$memberSectionId]['expiration'] = $membership->getEnd();
				}
				// new start date
				if ($newStart) {
					$saved['updated'][$memberSectionId]['start'] = $membership->getStart();
				}
			}
		}
		if ($sendEmail) {
			MembershipEmailing::sendEmails($this, $saved, $password, $message, $subject);
		}

		return $saved;
	}

	public function stopMembership(int $memberSectionId): void
	{
		Membership::deleteByUser($this->getId(), $memberSectionId);
	}

	public function stopMembershipLevel(int $memberSectionId, int $levelId): void
	{
		$membership = $this->getMembership($memberSectionId);
		if ($membership) {
			$membership->stopLevelMembership($levelId);
		}
	}

	public static function getOneByEmail(string $email): ?self
	{
		$user = self::getOneBy($email, 'email');
		if (!$user) {
			$user = self::getOneBy($email, 'login');
		}

		return $user;
	}

	// register member object
	public static function registerMembers()
	{
		$args = [
			'service_class' => 'mwSettingObjectService_Member',
			'class' => 'mwMember',
			'object_type' => 'user',
			'allow_add' => true,
			'public' => false,
			'supports' => ['search'],
			'filter' => [
				[
					'id' => 'member_section',
					'content' => '',
					'title' => __('Členská sekce', 'cms'),
					'object_items' => 'member_sections',
				],
				/* @TODO add filter by member level and activity
				[
					'id' => 'member_section_level',
					'content' => '',
					'title' => __('Členská úroveň', 'cms'),
					'object_items' => 'member_levels',
					'get_all_args' => null,
				],
				[
					'id' => 'activity',
					'content' => '',
					'title' => __('Aktivita', 'cms'),
					'items' => [
						'' => __('Vše', 'mwshop'),
						'active' => __('Aktivní', 'mwshop'),
						'nonactive' => __('Neaktivní', 'mwshop'),
					],
				],
				*/
			],
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Členové', 'cms'),
				'add_item' => __('Přidat člena', 'cms'),
				'edit_item' => __('Upravit člena', 'cms'),
				'new_item' => __('Nový člen', 'cms'),
				'empty' => __('Nebyl nalezen žádný člen', 'cms'),
				'notfound' => __('Člen nebyl nalezen', 'cms'),
			],

		];

		mwSetting()->registerObject('members', $args);
	}

}

class mwSettingObjectService_Member extends mwSettingObjectService_User
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Jméno', 'cms'),
				],
				[
					'content' => __('Email', 'cms'),
				],
				[
					'content' => __('Aktivní', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Pokrok', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();
		$search = isset($filter['s']) && $filter['s'] ? $filter['s'] : '';
		$memberSection_id = isset($filter['member_section']) && $filter['member_section'] !== '' && Validators::isNumeric($filter['member_section'])
				? (int) $filter['member_section']
				: null;

		$users = mwMember::getAll([
			'paged' => $page,
			'number' => $perPage,
			'search' => $search,
			'member_section_id' => $memberSection_id,
		], true);

		$args['pagination'] = [
			'pages' => $users['pages'],
			'count' => $users['count'],
		];

		foreach ($users['items'] as $user) {
			$allProgress = $user->getAllProgress();
			$tooltipText = '';
			foreach ($allProgress['progress'] as $progress) {
				$tooltipText = '<p>' . $progress['name'] . ': ' . $progress['percent'] . '%</p>';
			}
			$progressInfo = $tooltipText ? mwAdminComponents::tooltip(['text' => $tooltipText, 'tooltip_align' => 'top', 'type' => 'text', 'icon' => $allProgress['percent']]) : $allProgress['percent'];

			$activity_icon = $user->isActive() ? mwAdminComponents::icon([
					'icon' => 'check',
			], 'mw_status_icon_check') : mwAdminComponents::icon([
					'icon' => 'x',
			], 'mw_status_icon_x');
			$activity = mwAdminComponents::tooltip(['text' => $user->getLastActivityDate('diff'), 'tooltip_align' => 'top', 'type' => 'text', 'icon' => $activity_icon]);

			$args['rows'][] = [
				'bulk_id' => $user->getId(),
				'cols' => [
					[
						'content' => mwAdminComponents::link([
							'text' => $user->getAvatar(30) . ' <span>' . $user->getName() . '</span>',
							'link' => $this->object()->getEditUrl($user->getId()),
						], 'mw_link mw_user_list_detail_link'),
					],
					[
						'content' => $user->getEmail(),
					],
					[
						'content' => $activity,
						'align' => 'center',
					],
					[
						'content' => $progressInfo,
						'align' => 'center',
					],
					[
						'content' => mwSetting()->printSettingActions(['edit', 'delete'], $user->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	function printFormSidebar($item, $add = false, $inPopup = false): string
	{
		$content = '<div class="mw_setting_object_detail_sidebar">';

		$content .= '<div class="mw_setting_sidebar_box">';
		if (!$add) {
			$content .= '<div class="mw_setting_sidebar_main_set">';
			$content .= mwAdminComponents::title([
				'text' => __('Role', 'cms'),
			]);
			$content .= mwUser::roleSelect($add ? 'member' : ($item->getRole() ?? ''), 'user[role]');
			$content .= '</div>';

			$content .= '<div class="mws_hide_in_listings_container mw_onedit_action" data-type="switch">';
			$content .= mwAdminComponents::switch([
				'switch_label' => __('Při změně členství informovat člena emailem', 'cms'),
				'name' => 'send_user_notification',
			], 1);
			$content .= '</div>';

			$content .= $this->getInfoList($item);
			if (!$inPopup) {
				$content .= $this->getDetailActionList($item);
			}
		} else {
			$content .= '<input type="hidden" name="user[role]" value="member" />';
			$content .= mwAdminComponents::title([
				'text' => __('Informovat uživatele', 'cms'),
			]);
			$content .= mwAdminComponents::switch([
				'switch_label' => __('Poslat novému členovi email s&nbsp;informací o jeho členství', 'cms'),
				'name' => 'send_user_notification',
			], 1);
		}
		$content .= '</div>';

		if ($item) {
			$content .= '<div class="mw_setting_sidebar_box">';
			$content .= mwAdminComponents::title([
				'text' => __('Profilový obrázek', 'cms'),
			]);
			$content .= '<div class="mw_setting_gravatar_sidebar">';
			$content .= $item->getAvatar(80);
			$content .= '<div>' . __('Profilový obrázek si člen může nastavit na adrese', 'cms') . ' <a target="_blank" href="https://cs.gravatar.com/">gravatar.com</a></div>';
			$content .= '</div>';
			$content .= '</div>';

			$content .= '<div class="mw_setting_sidebar_box">';
			$content .= mwAdminComponents::title([
				'text' => __('Souhlas se zpracováním osobních údajů', 'cms'),
			]);
			$accept = $item->getUserMeta('mw_member_accepted');
			$content .= !empty($accept) && $accept['time'] ? __('Souhlas udělen', 'cms_member') . ': ' . mwPrintDate($accept['time']) : __('Souhlas neudělen', 'cms_member');
			$accept_text = $accept['text'] ?? __(' Souhlas s účelem zpracování je evidován u původního zdroje.', 'cms_member');
			$content .= mwAdminComponents::tooltip([
				'icon' => 'i',
				'tooltip_align' => 'top',
				'text' => $accept_text,
			]);

			$content .= '</div>';
		}

		$content .= '</div>';

		return $content;
	}

	public function getInfoList($item): string
	{
		$content = '<div class="mw_setting_sidebar_info">';
		$content .= '<div class="mw_setting_sidebar_info_row">';
		$content .= '<span>' . __('Vytvořen', 'cms') . ':</span>';
		$content .= '<span>' . $item->getDateCreated() . '</span>';
		$content .= '</div>';
		$content .= '<div class="mw_setting_sidebar_info_row">';
		$content .= '<span>' . __('Zdroj', 'cms_member') . ':</span>';
		$content .= '<span>' . $item->getSourceText() . '</span>';
		$content .= '</div>';
		$content .= '<div class="mw_setting_sidebar_info_row">';
		$content .= '<span>' . __('Naposledy', 'cms') . ':</span>';
		$content .= '<span>' . $item->getLastActivityDate('diff') . '</span>';
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	public function checkData($tosave, $itemId = 0, $fast = false, bool $add = false): bool
	{
		$userCheck = parent::checkData($tosave, $itemId, $fast, $add);
		if (!$userCheck) {
			return false;
		}
		if ($tosave['user']['role'] === 'member' && $add) {
			$selected = false;
			if (isset($tosave['member'])) {
				foreach ($tosave['member'] as $member) {
					if (isset($member['section'])) {
						$selected = true;

						continue;
					}
				}
			}

			if (!$selected) {
				mwMessages()->error(__('Nového člena musíte zařadit alespoň do jedné členské sekce.', 'cms'));

				return false;
			}
		}

		return true;
	}
}
