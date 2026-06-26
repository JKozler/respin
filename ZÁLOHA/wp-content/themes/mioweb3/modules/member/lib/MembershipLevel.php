<?php

namespace Mioweb\Member;

class MembershipLevel
{

	/** @var null"int id of membership */
	private $_membershipId;

	/** @var int id of member section level */
	private $_levelId;

	/** @var string */
	private $_start;

	/** @var string */
	private $_end;

	public function __construct(int $levelId, ?int $membershipId = null, ?string $start = null, ?string $end = null)
	{
		$this->_membershipId = $membershipId;
		$this->_levelId = $levelId;
		$this->_start = $start;
		$this->_end = $end;
	}

	public function getMembershipId(): ?int
	{
		return $this->_membershipId;
	}

	public function getLevelId(): int
	{
		return $this->_levelId;
	}

	public function getStartDate(): ?string
	{
		return $this->_start;
	}

	public function getEndDate(): ?string
	{
		return $this->_end;
	}

	public function isExpired(): bool
	{
		if ($this->_end !== null) {
			$now = new \DateTimeImmutable(current_time('timestamp'));

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

	public static function getAll(int $membershipId): array
	{
		$rows = MWDB()->getRows('mw_membership_levels', 'membership_id = ' . $membershipId);
		$levels = [];
		foreach ($rows as $row) {
			$levels[$row->member_level_id] = new MembershipLevel($row->member_level_id, $row->membership_id, $row->start, $row->end);
		}

		return $levels;
	}

}
