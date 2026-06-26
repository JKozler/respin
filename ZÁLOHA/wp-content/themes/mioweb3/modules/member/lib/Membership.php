<?php

namespace Mioweb\Member;

use MwObjectCache;

class Membership
{

	private ?int $_id = null;

	private int $_memberSectionId;

	private int $_userId;

	private string $_start;

	private ?string $_end;

	private ?string $_lastActivity = null;

	private ?array $_membershipLevels = null;

	private ?array $_membershipMonths = null;

	function __construct(?int $id, int $member_section_id, int $user_id, ?string $start = null, ?string $end = null, ?string $last_activity = null)
	{
		$this->_id = $id;
		$this->_memberSectionId = $member_section_id;
		$this->_userId = $user_id;
		$this->_start = $start;
		$this->_end = $end;
		$this->_lastActivity = $last_activity;
	}

	public function getId(): ?int
	{
		return $this->_id;
	}

	public function getUserId(): int
	{
		return $this->_userId;
	}

	public function getMemberSectionId(): int
	{
		return $this->_memberSectionId;
	}

	public function getMembershipLevels(): array
	{
		if ($this->_membershipLevels === null && $this->getId()) {
			$this->_membershipLevels = MembershipLevel::getAll($this->getId());
		}

		return $this->_membershipLevels ?: [];
	}

	public function setLevels(array $levels, $add = true): array
	{
		$newlevels = [];
		foreach ($levels as $levelId) {
			$newlevels[$levelId] = new MembershipLevel($levelId);
		}
		$oldLevels = $this->getMembershipLevels();
		$this->_membershipLevels = $add ? $oldLevels + $newlevels : $newlevels;

		return array_diff_key($newlevels, $oldLevels);
	}

	public function getMembershipMonths(): array
	{
		if ($this->_membershipMonths === null) {
			$this->_membershipMonths = [];

			if ($this->getId()) {
				$rows = MWDB()->getRows('mw_membership_months', 'membership_id = ' . $this->getId(), 'month');


				foreach ($rows as $row) {
					$this->_membershipMonths[$row->month] = new MonthMembership($row->month);
				}
			}
		}

		return $this->_membershipMonths;
	}

	public function setMonths(array $months, $add = true): array
	{
		$newMonths = [];
		foreach ($months as $month) {
			$newMonths[$month] = new MonthMembership($month);
		}
		$oldMonths = $this->getMembershipMonths();

		$this->_membershipMonths = $add ? $oldMonths + $newMonths : $newMonths;

		return array_diff_key($newMonths, $oldMonths);
	}

	public function getStart($format = 'timestamp'): ?string
	{
		if ($format === 'timestamp') {
			return $this->_start ? strtotime($this->_start) : null;
		}

		return $this->_start ? mwPrintDate(strtotime($this->_start), $format) : null;
	}

	public function setStart(?string $start): bool
	{
		if ($start === null) {
			return false;
		}
		$oldStart = $this->_start;
		$newStart = date('Y-m-d H:i', strtotime($start)) . ':00';
		$this->_start = $newStart;

		return strtotime($oldStart) !== strtotime($newStart); // start changed
	}

	public function getEnd($format = 'timestamp'): ?string
	{
		if ($format === 'timestamp') {
			return $this->_end ? strtotime($this->_end) : null;
		}

		return $this->_end ? mwPrintDate(strtotime($this->_end), $format) : null;
	}

	public function setEnd(?string $end): bool
	{
		$oldEnd = $this->_end;
		$newEnd = $end ? date('Y-m-d', strtotime($end)) : null;
		$this->_end = $newEnd;

		return strtotime($oldEnd) !== strtotime($newEnd); // end changed
	}

	public function getLastActivity($format = 'timestamp'): ?string
	{
		if ($format === 'timestamp') {
			return $this->_lastActivity ? strtotime($this->_lastActivity) : null;
		}

		return $this->_lastActivity ? mwPrintDate(strtotime($this->_lastActivity), $format) : null;
	}

	public function getMonthsEnd($format = 'timestamp'): ?string
	{
		$end = null;

		if (count($this->getMembershipMonths())) {
			$cur_y = date('Y', current_time('timestamp'));
			$cur_m = date('n', current_time('timestamp'));
			$cur = date('Ym', current_time('timestamp'));

			$last = $cur;
			foreach ($this->getMembershipMonths() as $month => $v) {
				if ($month > $last) {
					$last = $month;
				}

				if ($month > $cur) {
					if ($cur_m < 12) {
						$cur_m++;
					} else {
						$cur_m = 1;
						$cur_y++;
					}
				}
			}

			if ($cur_y . str_pad($cur_m, 2, '0', STR_PAD_LEFT) === $last) {
				$lastMonth = new MonthMembership($last);
				$end = $lastMonth->getEndDate();
			}
		}

		if ($format === 'timestamp') {
			return $end ? strtotime($end) : null;
		}

		return $end ? mwPrintDate(strtotime($this->_end), $format) : '';
	}

	public function isExpired(): bool
	{
		if ($this->_end !== null) {
			$now = new \DateTimeImmutable(date('Y-m-d', current_time('timestamp')));

			try {
				$end = new \DateTimeImmutable($this->_end);

				if ($end < $now) {
					return true;
				}
			} catch (\Throwable $e) {
				// ignore
			}
		}

		return false;
	}

	public function hasLevelAccess(array $levelIds): bool
	{
		if (empty($levelIds)) {
			return true;
		}

		foreach ($this->getMembershipLevels() as $membershipLevel) {
			if (in_array($membershipLevel->getLevelId(), $levelIds)) {
				return true;
			}
		}

		return false;
	}

	public function isLevelAccessExpired(array $levelIds): int
	{
		if (empty($levelIds)) {
			return 0;
		}

		$expiredLevel = 0;
		foreach ($this->getMembershipLevels() as $membershipLevel) {
			if (in_array($membershipLevel->getLevelId(), $levelIds)) {
				if ($membershipLevel->isExpired()) {
					$expiredLevel = $membershipLevel->getLevelId();
				} else {
					return 0;
				}
			}
		}

		return $expiredLevel;
	}

	public function hasMonthAccess(?string $month): bool
	{
		if ($month === null) {
			return true;
		}

		return isset($this->getMembershipMonths()[$month]);
	}

	public function saveActivity(): void
	{
		MWDB()->update('mw_membership', [
			'last_activity' => date('Y-m-d H:i:s', current_time('timestamp')),
		], [
			'id' => $this->_id,
		]);
	}

	public function save()
	{
		$data = [
			'member_section_id' => $this->_memberSectionId,
			'user_id' => $this->_userId,
			'end' => $this->_end,
			'start' => $this->_start,
		];

		if ($this->_id) {
			MWDB()->update('mw_membership', $data, [
				'id' => $this->_id,
			]);
			$membershipId = $this->_id;
		} else {
			$membershipId = MWDB()->insert('mw_membership', $data);
		}

		if ($membershipId) {
			// save levels
			MWDB()->delete('mw_membership_levels', [
				'membership_id' => $membershipId,
			]);

			if ($this->_membershipLevels) {
				foreach ($this->_membershipLevels as $level) {
					MWDB()->insert('mw_membership_levels', [
						'membership_id' => $membershipId,
						'member_level_id' => $level->getLevelId(),
					]);
				}
			}

			// save months
			MWDB()->delete('mw_membership_months', [
				'membership_id' => $membershipId,
			]);
			if ($this->_membershipMonths) {
				foreach ($this->_membershipMonths as $month) {
					MWDB()->insert('mw_membership_months', [
						'membership_id' => $membershipId,
						'month' => $month->getMonth(),
					]);
				}
			}
		}
	}

	public function stopLevelMembership(int $levelId): void
	{
		MWDB()->delete('mw_membership_levels', [
			'membership_id' => $this->_id,
			'member_level_id' => $levelId,
		]);
	}

	public static function delete(int $id): void
	{
		MWDB()->delete('mw_membership', [
			'id' => $id,
		]);
	}

	public static function deleteByUser(int $userId, int $memberSectionId)
	{
		MWDB()->delete('mw_membership', [
			'user_id' => $userId,
			'member_section_id' => $memberSectionId,
		]);
	}

	public static function getOne(int $memberSectionId, int $userId, bool $forceUpdateCache = false): ?self
	{
		$row = MWDB()->getTableRow('mw_membership', 'user_id = ' . $userId . ' AND member_section_id = ' . $memberSectionId);

		return $row ? self::createNew($row, $forceUpdateCache) : null;
	}

	public static function createNew(object $membership, bool $forceUpdateCache = false): ?self
	{
		if ($forceUpdateCache || !($obj = MwObjectCache::get(static::class, $membership->id))) {
			$obj = new self(
				$membership->id,
				$membership->member_section_id,
				$membership->user_id,
				$membership->start,
				$membership->end,
				$membership->last_activity
			);
			MwObjectCache::add($obj, $obj->getId());
		}

		return $obj;
	}

	public static function createNewByArray(int $userId, array $membership): self
	{
		if (isset($membership['start']) && $membership['start']) {
			$start = strtotime($membership['start']);
		} elseif (isset($membership['date']) || isset($membership['time'])) {
			$start = strtotime(self::createStartFromDateAndTime($membership['date'] ?? null, $membership['time'] ?? null));
		} else {
			$start = current_time('timestamp');
		}

		if (isset($membership['days']) && $membership['days']) {
			$end = date('Y-m-d', $start + ($membership['days'] * 86400));
		} else {
			$end = isset($membership['end']) && $membership['end'] ? date('Y-m-d', strtotime($membership['end'])) : null;
		}

		return new self(
			$membership['membership_id'] ?? null,
			$membership['member_section_id'],
			$userId,
			date('Y-m-d H:i', $start) . ':00',
			$end,
		);
	}

	public static function createStartFromDateAndTime(?string $date, ?string $time): string
	{
		$date = $date ?: date('Y-m-d', current_time('timestamp'));
		$time = $time ?: date('H:i:s', current_time('timestamp'));

		return $date . ' ' . $time;
	}

	public static function getAll(int $userId): array
	{
		$memberships = MWDB()->getRows('mw_membership', 'user_id = ' . $userId);
		$ret = [];
		foreach ($memberships as $membership) {
			$ret[$membership->member_section_id] = self::createNew($membership);
		}

		return $ret;
	}

}
