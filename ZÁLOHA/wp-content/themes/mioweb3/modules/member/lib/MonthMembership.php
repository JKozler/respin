<?php

namespace Mioweb\Member;

class MonthMembership
{

	/** @var string of month in format: yyyymm */
	private $_month;

	public function __construct(string $month)
	{
		$this->_month = $month;
	}

	public function getMonth(): string
	{
		return $this->_month;
	}

	public function getName(): string
	{
		$m = str_split($this->_month, 4);

		return $m[1] . '/' . $m[0];
	}

	public function getStartDate(): string
	{
		$m = str_split($this->_month, 4);

		return '01.' . $m[1] . '.' . $m[0];
	}

	public function getEndDate(): string
	{
		$date = new \DateTime($this->getStartDate());
		$date->modify('last day of this month');

		return $date->format('d. m. Y');
	}

	public static function getMonthsByNum(int $num): array
	{
		$months = [];
		$cur_month = date('n', current_time('timestamp'));
		$cur_year = date('Y', current_time('timestamp'));
		$cur_month = intval($cur_month) + 1;
		for ($i = 1; $i <= $num; $i++) {
			if ($cur_month > 12) {
				$cur_month = 1;
				$cur_year++;
			}
			$month = $cur_year . str_pad($cur_month, 2, '0', STR_PAD_LEFT);
			if (!isset($months[$month])) {
				$months[$month] = $month;
			} else {
				$i--; // skip existing months
			}
			$cur_month++;
		}

		return $months;
	}

	public static function getAllMonthsOfYear(string $year): array
	{
		$months = [];
		for ($i = 1; $i <= 12; $i++) {
			$m = $year . str_pad($i, 2, '0', STR_PAD_LEFT);
			$months[$m] = $m;
		}

		return $months;
	}

	public static function getAllMonths(int $memberSectionId)
	{
		$months = [];
		$rows = MWDB()->getRows('mw_member_pages', 'member_section_id = ' . $memberSectionId . ' AND access_type = "month"', 'month', 'month');
		foreach ($rows as $month) {
			if ($month->month) {
				$months[$month->month] = new MonthMembership($month->month);
			}
		}

		return $months;
	}

	public static function getArchiveYears(int $memberSectionId): array
	{
		$years = [];
		$curentMonth = date('Ym', current_time('timestamp'));
		$rows = MWDB()->getRows('mw_member_pages', 'member_section_id = ' . $memberSectionId . ' AND access_type = "month" AND month IS NOT NULL AND hide_in_list = 0 AND month < ' . $curentMonth, 'month', 'LEFT(month, 4) as year', 'year');
		foreach ($rows as $row) {
			$years[] = $row->year;
		}

		return $years;
	}

	public static function getAllMonthPages(int $memberSectionId, $args = [])
	{
		$get = $args['get'] ?? 'future';
		$curentMonth = date('Ym', current_time('timestamp'));
		$where = 'AND access_type = "month" AND month IS NOT NULL AND hide_in_list = 0';
		$number = -1;

		if ($get === 'future') {
			if ($args['hide_current'] ?? false) {
				$where .= ' AND month > ' . $curentMonth;
			} else {
				$where .= ' AND month >= ' . $curentMonth;
			}
		} elseif ($get === 'archive') {
			$where .= ' AND month < ' . $curentMonth;
			if (isset($args['year'])) {
				$where .= ' AND LEFT(month, 4) = ' . $args['year'];
			}
		} elseif ($get === 'current') {
			$where .= ' AND month = ' . $curentMonth;
			$number = 1;
		}

		$pages = MemberPage::getMemberPages($memberSectionId, [
			'where' => $where,
			'number' => $number,
			'orderby' => 'month ASC',
		]);

		return $pages;
	}

}
