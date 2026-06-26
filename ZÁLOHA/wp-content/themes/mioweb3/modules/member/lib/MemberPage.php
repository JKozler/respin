<?php

namespace Mioweb\Member;
use WP_Post;
use mwPage;
use MwObjectCache;
use mwMember;

class MemberPage extends mwPage
{

	private $_mpId;

	private $_memberSectionId;

	private $_postId;

	private $_accessType;

	private $_accessInfo;

	private $_memberSectionLevels = null;

	private $_checklist = null;

	private $_month;

	private $_buyMonthPageId;

	private $_hideInList;

	public function __construct($page)
	{
		parent::__construct(get_post($page));

		$this->_mpId = intval($page->mp_id);
		$this->_memberSectionId = $page->member_section_id ? intval($page->member_section_id) : null;

		$this->_postId = intval($page->post_id);
		$this->_accessType = $page->access_type;
		$this->_accessInfo = $page->access_info;
		$this->_month = $page->month;
		$this->_buyMonthPageId = $page->month_page_id;
		$this->_hideInList = intval($page->hide_in_list) ? true : false;
	}

	public function getMemberPageId(): ?int
	{
		return $this->_mpId;
	}

	public function getPostId(): ?int
	{
		return $this->_postId;
	}

	public function getMemberSectionId(): ?int
	{
		return $this->_memberSectionId;
	}

	public function getAccessType(): ?string
	{
		return $this->_accessType;
	}

	public function getAccessInfo(): ?string
	{
		return $this->_accessInfo;
	}

	public function isMonth(): bool
	{
		return $this->getAccessType() === 'month' && $this->getMonth();
	}

	public function getMonth(): ?MonthMembership
	{
		return $this->_month ? new MonthMembership($this->_month) : null;
	}

	public function getMonthPageId(): ?int
	{
		return $this->_buyMonthPageId;
	}

	public function getMonthPageUrl(): string
	{
		return $this->getMonthPageId() ? get_permalink($this->getMonthPageId()) : '';
	}

	public function hideInList(): bool
	{
		return $this->_hideInList;
	}

	public function getEvergreenDays(): int
	{
		return $this->getAccessType() === 'evergreen' ? (int) $this->getAccessInfo() : 0;
	}

	public function getEvergreenDate(): string
	{
		return $this->getAccessType() === 'date' ? $this->getAccessInfo() : '';
	}

	public function getCheckListPageId(): ?int
	{
		return $this->getAccessType() === 'checklist' ? (int) $this->getAccessInfo() : null;
	}

	public function getForCheckListPageId(): ?int
	{
		return $this->getCheckListPageId() ?: $this->previousPageID($this->getPostId());
	}

	public function isPreviousPageCompleted(): bool
	{
		$checklistPageId = $this->getForCheckListPageId();

		if ($checklistPageId) {
			$member = mwMemberModule()->currentMember();
			$progress = $member->getProgress($checklistPageId, 'parent');
			if ($progress < 100) {
				return false;
			}
		}

		return true;
	}

	// @TODO refactor with $this->getMemberSubpages
	function previousPageID($id): int
	{
		// Get all pages under this section
		$post = get_post($id);
		$post_parent = $post->post_parent;
		$get_pages_query = [
			'orderby' => [
				'menu_order' => 'ASC',
				'date' => 'DESC',
			],
			'child_of' => $post_parent,
			'parent' => $post_parent,
		];
		$get_pages = get_pages($get_pages_query);
		$prev_page_id = '';
		// Count results
		$page_count = count($get_pages);

		for ($p = 0; $p < $page_count; $p++) {
			// get the array key for our entry
			if ($id == $get_pages[$p]->ID) {
				break;
			}
		}

		// assign our next & previous keys
		$prev_key = $p - 1;
		$last_key = $page_count - 1;

		// if there isn't a value assigned for the previous key, go all the way to the end
		if (isset($get_pages[$prev_key])) {
			$prev_page_id = $get_pages[$prev_key]->ID;
		}

		return $prev_page_id;
	}

	public function isAvailableInFuture($userRegistered): string
	{
		$evergreenTime = 0;
		if ($this->isMonth()) {
			$evergreenTime = strtotime($this->getMonth()->getStartDate());
		} elseif ($this->getAccessType() === 'date' && $this->getAccessInfo()) {
			$evergreenTime = strtotime($this->getAccessInfo());
		} elseif ($this->getAccessType() === 'evergreen' && (int) $this->getAccessInfo() > 0) {
			$evergreenTime = $userRegistered + ((int) $this->getAccessInfo() * 86400);
		}

		if ($evergreenTime && $evergreenTime > current_time('timestamp')) {
			return $evergreenTime;
		}

		return 0;
	}

	public function getLevels(): array
	{
		if ($this->_memberSectionLevels === null) {
			$rows = MWDB()->getRows('mw_member_page_levels', 'member_page_id = ' . $this->getMemberPageId());

			$this->_memberSectionLevels = [];
			foreach ($rows as $row) {
				$this->_memberSectionLevels[$row->member_level_id] = $row->member_level_id;
			}
		}

		return $this->_memberSectionLevels;
	}

	public function getFirstLevel(): ?int
	{
		$levels = $this->getLevels();

		return count($levels) ? reset($levels) : null;
	}

	public function getChecklist(): ?MemberChecklist
	{
		if ($this->_checklist === null) {
			$this->_checklist = MemberChecklist::createByMemberPage($this->getMemberPageId());
		}

		return $this->_checklist;
	}

	public function getChecklistForUser(int $userId): ?MemberChecklist
	{
		return MemberChecklist::createByMemberPage($this->getMemberPageId(), $userId);
	}

	public static function getOneById(int $id, bool $forceRecache = false): ?self
	{
		global $wpdb;
		// member page columns
		$columns = 'mp_id, post_id, member_section_id, access_type, access_info, month, month_page_id, hide_in_list';
		// post columns
		$columns .= ', ID, post_author, post_date, post_date_gmt, post_excerpt, post_title, post_status, comment_status, ping_status, post_password, post_name, post_modified, post_modified_gmt, post_parent, menu_order, post_type, comment_count';

		$query = "SELECT $columns FROM " . $wpdb->prefix . "mw_member_pages, $wpdb->posts WHERE post_id = ID AND post_id = " . $id;
		$row = MWDB()->getRow($query);

		return $row ? static::createOne($row, $forceRecache) : null;
	}

	public static function createOne(object $page, bool $forceUpdateCache = false): ?self
	{
		if ($forceUpdateCache || !($obj = MwObjectCache::get(static::class, $page->ID))) {
			$obj = new self($page);
			MwObjectCache::add($obj, $obj->getId());
		}

		return $obj;
	}

	/** @return self[] */
	public static function getMemberSubPages($args = [])
	{
		return self::getMemberPages(null, $args, true);
	}

	/** @return self[] */
	public static function getMemberPages($memberSectionId, $args = [], $allSubPages = false)
	{
		global $wpdb;

		$post_status = $args['post_status'] ?? ['publish'];
		$post_type = 'page';
		$parent = $args['parent'] ?? -1;
		$hierarchical = $args['hierarchical'] ?? false;
		$page = $args['paged'] ?? 1;
		$perPage = $args['number'] ?? -1;

		if ($parent > 0) {
			$hierarchical = false;
		}

		// Make sure we have a valid post status.
		if (!is_array($post_status)) {
			$post_status = explode(',', $post_status);
		}
		if (array_diff($post_status, get_post_stati())) {
			return false;
		}

		if (count($post_status) === 1) {
			$where_post_type = $wpdb->prepare('post_type = %s AND post_status = %s', $post_type, reset($post_status));
		} else {
			$post_status = implode("', '", str_replace(' ', '', $post_status));
			$where_post_type = $wpdb->prepare("post_type = %s AND post_status IN ('$post_status')", $post_type);
		}

		$where = '';
		if (is_array($parent)) {
			$post_parent__in = implode(',', array_map('absint', (array) $parent));
			if (!empty($post_parent__in)) {
				$where .= " AND post_parent IN ($post_parent__in)";
			}
		} elseif ($parent >= 0) {
			$where .= $wpdb->prepare(' AND post_parent = %d ', $parent);
		}

		if (isset($args['where'])) {
			$where .= ' ' . $args['where'];
		}

		// member page columns
		$columns = 'mp_id, post_id, member_section_id, access_type, access_info, month, month_page_id, hide_in_list';
		// post columns
		$columns .= ', ID, post_author, post_date, post_date_gmt, post_excerpt, post_title, post_status, comment_status, ping_status, post_password, post_name, post_modified, post_modified_gmt, post_parent, menu_order, post_type, comment_count';

		if ($allSubPages) {
			// select even posts which are not member pages
			$query = "SELECT $columns FROM $wpdb->posts LEFT JOIN " . $wpdb->prefix . "mw_member_pages ON (post_id = ID) WHERE ($where_post_type) $where";
		} else {
			if ($memberSectionId) {
				$where .= ' AND member_section_id = ' . $memberSectionId;
			}
			$query = "SELECT $columns FROM " . $wpdb->prefix . "mw_member_pages, $wpdb->posts WHERE post_id = ID AND ($where_post_type) $where";
		}


		if (isset($args['exclude']) && (bool) $args['exclude']) {
			$exclude = is_array($args['exclude']) ? implode(',', $args['exclude']) : $args['exclude'];
			$query .= ' AND ID NOT IN (' . $exclude . ')';
		}

		$query .= ' ORDER BY ' . ($args['orderby'] ?? 'menu_order, post_date ASC');

		// Limit.
		if ($perPage > 0) {
			$query .= $wpdb->prepare(' LIMIT %d, %d', $perPage * ($page - 1), $perPage);
		}
		$pages = $wpdb->get_results($query);

		// Sanitize before caching so it'll only get done once.
		$num_pages = count($pages);
		for ($i = 0; $i < $num_pages; $i++) {
			$pages[$i ] = sanitize_post($pages[$i ], 'raw');
		}

		if ($hierarchical) {
			$pages = get_page_children(0, $pages);
		}

		// Convert to MemberPage instances.
		$pages = array_map([MemberPage::class, 'createOne'], $pages);

		return $pages;
	}

	public static function deleteByPostId(int $postId)
	{
		return MWDB()->delete('mw_member_pages', [
			'post_id' => $postId,
		]);
	}

	public static function saveMemberPageSetting($postId, $tosave): void
	{
		if (isset($tosave['member_page']) && $tosave['member_page'] === '1') {
			$memberPage = self::getOneById($postId);

			// save member page
			$month = null;
			$accessInfo = '';
			if ($tosave['access_type'] === 'date') {
				$accessInfo = date('Y-m-d H:i:s', strtotime($tosave['evergreen_datetime']['date'] . ' ' . ($tosave['evergreen_datetime']['hour'] ?: '0') . ':' . ($tosave['evergreen_datetime']['minute'] ?: '0')));
			} elseif ($tosave['access_type'] === 'checklist') {
				$accessInfo = $tosave['checklist_page'];
			} elseif ($tosave['access_type'] === 'month') {
				$month = $tosave['month']['year'] . $tosave['month']['month'];
			} elseif ($tosave['access_type'] === 'evergreen') {
				$accessInfo = $tosave['evergreen'];
			}

			$data = [
				'post_id' => $postId,
				'member_section_id' => $tosave['member_section']['section'],
				'access_type' => $tosave['access_type'],
				'access_info' => $accessInfo,
				'month_page_id' => $tosave['month_page_id'] ?: null,
				'month' => $month,
				'hide_in_list' => isset($tosave['hide_in_list']) ? 1 : 0,
			];

			if ($memberPage) {
				$memberPageId = $memberPage->getMemberPageId();
				MWDB()->update('mw_member_pages', $data, [
					'mp_id' => $memberPage->getMemberPageId(),
				]);
			} else {
				$memberPageId = MWDB()->insert('mw_member_pages', $data);
			}

			// save member page levels

			if ($memberPage) {
				MWDB()->delete('mw_member_page_levels', [
					'member_page_id' => $memberPage->getMemberPageId(),
				]);
			}
			if ($memberPageId && isset($tosave['member_section']['levels'][$tosave['member_section']['section']])) {
				$levels = [];
				foreach ($tosave['member_section']['levels'][$tosave['member_section']['section']] as $level) {
					$levels[] = [
						$memberPageId,
						$level,
					];
				}

				MWDB()->insertRows('mw_member_page_levels', $levels);
			}

			// save member page tasks
			if ($memberPageId) {
				$checklist = MemberChecklist::createByMemberPage($memberPageId);
				$checklist->update($tosave['checklist'] ?? []);
			}
		} else {
			// delete member page
			self::deleteByPostId($postId);
		}
	}

	public static function loadMemberPageSetting($postId): array
	{
		$memberPage = self::getOneById($postId);
		if ($memberPage !== null) {
			return [
				'member_page' => '1',
				'member_section' => [
					'section' => $memberPage->getMemberSectionId(),
					'levels' => [
						$memberPage->getMemberSectionId() => $memberPage->getLevels(),
					],
				],
				'access_type' => $memberPage->getAccessType(),
				'evergreen' => $memberPage->getEvergreenDays(),
				'evergreen_datetime' => strtotime($memberPage->getEvergreenDate()),
				'month' => $memberPage->_month,
				'hide_in_list' => $memberPage->hideInList() ? 1 : 0,
				'month_page_id' => $memberPage->getMonthPageId(),
				'checklist_page' => $memberPage->getChecklistPageId(),
				'checklist' => $memberPage->getChecklist()->toArraySetting(),
			];
		}

		return [];
	}

	public function createCopy($tosave)
	{
		$item = parent::createCopy($tosave);

		$data = [
			'post_id' => $item->getId(),
			'member_section_id' => $tosave['member_page']['member_section']['section'],
			'access_type' => $this->getAccessType(),
			'access_info' => $this->getAccessInfo(),
			'month_page_id' => $this->getMonthPageId(),
			'month' => $this->getMonth(),
			'hide_in_list' => $this->hideInList() ? 1 : 0,
		];

		$memberPageId = MWDB()->insert('mw_member_pages', $data);

		if ($memberPageId && isset($tosave['member_page']['member_section']['levels'][$tosave['member_page']['member_section']['section']])) {
			$levels = [];
			foreach ($tosave['member_page']['member_section']['levels'][$tosave['member_page']['member_section']['section']] as $level) {
				$levels[] = [
					$memberPageId,
					$level,
				];
			}

			MWDB()->insertRows('mw_member_page_levels', $levels);
		}

		// save member page tasks
		if ($memberPageId) {
			$checklist = $this->getChecklist();
			$checklist->createCopyFor($memberPageId);
		}

		return $item;
	}

	public static function registerMemberPageObject(): void
	{
		$mwArgs = [
			'class' => 'Mioweb\Member\MemberPage',
			'service_class' => 'Mioweb\Member\mwSettingObjectService_MemberPage',
		];

		mwSetting()->registerObjectCopy('member_page', 'page', $mwArgs);
	}

}

class mwSettingObjectService_MemberPage extends \mwSettingObjectService
{

	public function printFastCopyForm($itemId): void
	{
		$item = MemberPage::getOneById($itemId);
		$memberSectionId = $item->getMemberSectionId();

		$content = [
			'member_section' => [
				'section' => $memberSectionId,
				'levels' => [
					$memberSectionId => $item->getLevels(),
				],
			],
		];

		echo '<div class="mw_fast_object_form">';

		$meta_set = $this->object()->getFastSetting();
		write_meta($meta_set['fields'], $content, 'member_page', 'member_page', $itemId);

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="object_id" value="' . $this->object()->getId() . '"/>';
		echo '<input type="hidden" name="item_id" value="' . $itemId . '"/>';

		echo '</div>';
	}

}
