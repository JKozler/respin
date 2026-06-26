<?php

function mwMemberStatistics()
{
   return mwMemberStatistics::instance();
}

class mwMemberStatistics
{

	protected static $_instance = null;

	private $_memberUsers = null;

	private $_activeMemberUsers = null;

	public function memberUsersCount($member_id = -1)
	{
		if ($this->_memberUsers === null) {
			$this->getMemberUsers();
		}

		return $this->_memberUsers[$member_id] ?? 0;
	}

	public function activeMemberUsersCount($member_id = -1)
	{
		if ($this->_activeMemberUsers === null) {
			$this->getMemberUsers();
		}

		return $this->_activeMemberUsers[$member_id] ?? 0;
	}

	public function memberSectionsCount(): int
	{
		return count(mwMemberModule()->getMemberSections());
	}

	public function getMemberUsers()
	{
		global $wpdb;
		$this->_memberUsers[-1] = $wpdb->get_var('SELECT count(DISTINCT user_id) FROM ' . $wpdb->prefix . 'mw_membership, ' . $wpdb->users . ' WHERE user_id = ' . $wpdb->users . '.ID');
		$this->_activeMemberUsers[-1] = $wpdb->get_var('SELECT count(DISTINCT user_id) FROM ' . $wpdb->prefix . 'mw_membership, ' . $wpdb->users . ' WHERE user_id = ' . $wpdb->users . '.ID AND last_activity >= NOW() - INTERVAL 30 DAY');

		$userCounts = MWDB()->getResults('SELECT count(1) as members, member_section_id FROM ' . $wpdb->prefix . 'mw_membership, ' . $wpdb->users . ' WHERE user_id = ' . $wpdb->users . '.ID GROUP BY member_section_id');
		foreach ($userCounts as $count) {
			$this->_memberUsers[$count->member_section_id] = $count->members;
		}

		$activeUserCounts = MWDB()->getResults('SELECT count(1) as members, member_section_id FROM ' . $wpdb->prefix . 'mw_membership, ' . $wpdb->users . ' WHERE user_id = ' . $wpdb->users . '.ID AND last_activity >= NOW() - INTERVAL 30 DAY GROUP BY member_section_id');
		foreach ($activeUserCounts as $count) {
			$this->_activeMemberUsers[$count->member_section_id] = $count->members;
		}
	}

	/** @return mwMemberStatistics Returns singleton instance of member statistics. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

}
