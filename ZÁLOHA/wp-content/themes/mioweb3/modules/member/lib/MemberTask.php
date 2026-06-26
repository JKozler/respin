<?php

namespace Mioweb\Member;

use mwMember;

class MemberTask
{

	/** @var int */
	private $_id;

	/** @var int */
	private int $_order;

	/** @var string */
	private $_task;

	/** @var bool */
	private $_completed;

	public function __construct($task)
	{
		$this->_id = $task->mpt_id;
		$this->_order = $task->task_order;
		$this->_task = $task->task;
		$this->_completed = isset($task->user_id) && $task->user_id ? true : false;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getTask(): string
	{
		return $this->_task;
	}

	public function isCompleted(): string
	{
		return $this->_completed;
	}

	public static function getAllByMemberPageId(int $memberPageId, ?int $userId): array
	{
		$tasks = [];
		if ($userId) {
			global $wpdb;
			$rows = MWDB()->getResults('SELECT * FROM ' . $wpdb->prefix . 'mw_member_page_tasks LEFT JOIN ' . $wpdb->prefix . 'mw_user_tasks ON (mpt_id = task_id AND user_id = ' . $userId . ') WHERE member_page_id = ' . $memberPageId . ' ORDER BY task_order');
		} else {
			$rows = MWDB()->getRows('mw_member_page_tasks', 'member_page_id = ' . $memberPageId, 'task_order');
		}

		foreach ($rows as $row) {
			$tasks[] = new MemberTask($row);
		}

		return $tasks;
	}

	public static function getAllFromElement(string $elementId, array $elementTasks, ?mwMember $user): array
	{
		$tasks = [];
		$userChecklist = [];

		if ($user) {
			$userChecklist = mwMember::getCustomChecklist($user->getId(), $elementId);
		}

		foreach ($elementTasks as $id => $elementTask) {
			$task = [
				'mpt_id' => $id,
				'task_order' => $id,
				'task' => $elementTask['text'],
				'user_id' => isset($userChecklist[$id]) ? 1 : null,
			];
			$tasks[] = new MemberTask((object) $task);
		}

		return $tasks;
	}

}
