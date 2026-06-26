<?php

namespace Mioweb\Member;
use mwMember;

class MemberChecklist
{

	/** @var int */
	private $_memberPageId = null;

	/** @var int */
	private $_elementId = null;

	/** @var array */
	private $_tasks = [];

	/** @var int */
	private ?int $_userId = null;

	public function __construct(?int $memberPageId, ?string $elementId = null, array $tasks = [], ?int $userId = null)
	{
		$this->_memberPageId = $memberPageId;
		$this->_elementId = $elementId;
		$this->_userId = $userId;
		$this->_tasks = $tasks;
	}

	public static function createByMemberPage(int $memberPageId, int $userId = null)
	{
		$tasks = MemberTask::getAllByMemberPageId($memberPageId, $userId);

		return new self($memberPageId, null, $tasks, $userId);
	}

	public static function createFromElement(string $elementId, array $elementTasks, ?mwMember $user = null)
	{
		$tasks = MemberTask::getAllFromElement($elementId, $elementTasks, $user);

		return new self(null, $elementId, $tasks, $user ? $user->getId() : null);
	}

	public function getMemberPageId(): ?int
	{
		return $this->_memberPageId;
	}

	public function getElementId(): ?string
	{
		return $this->_elementId;
	}

	// return array of MemberTask
	public function getTasks(): array
	{
		return $this->_tasks;
	}

	public function isEmpty(): bool
	{
		return count($this->getTasks()) ? false : true;
	}

	public function update(array $tasks): void
	{
		$order = 0;
		$doNotDelete = [];
		foreach ($tasks as $task) {
			if ($task['task']) {
				$taskData = [
					'task' => $task['task'],
					'member_page_id' => $this->getMemberPageId(),
					'task_order' => $order,
				];

				if ($task['mpt_id']) {
					$taskData['mpt_id'] = $task['mpt_id'];
					$doNotDelete[] = $task['mpt_id'];
				}

				MWDB()->replace('mw_member_page_tasks', $taskData);

				$order++;
			}
		}

		// delete
		if (!$this->isEmpty()) {
			foreach ($this->getTasks() as $task) {
				if (!in_array($task->getId(), $doNotDelete)) {
					MWDB()->delete('mw_member_page_tasks', [
						'mpt_id' => $task->getId(),
					]);
				}
			}
		}
	}

	public function createCopyFor(int $memberPageId): void
	{
		$order = 0;
		foreach ($this->getTasks() as $task) {
			$taskData = [
				'task' => $task->getTask(),
				'member_page_id' => $memberPageId,
				'task_order' => $order,
			];
			MWDB()->insert('mw_member_page_tasks', $taskData);
			$order++;
		}
	}

	public static function delete(int $memberPageId)
	{
		return MWDB()->delete('mw_member_page_tasks', [
			'mw_member_page_id' => $memberPageId,
		]);
	}

	public function toArraySetting(): array
	{
		$tasks = [];
		foreach ($this->getTasks() as $task) {
			$tasks[] = [
				'mpt_id' => $task->getId(),
				'task' => $task->getTask(),
			];
		}

		return $tasks;
	}

}
