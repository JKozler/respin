<?php declare(strict_types = 1);

namespace Mioweb\Core\Analytics;

use Nette\Database\Context;
use Nette\Utils\Json;

final class Query implements IQuery
{

	/**
	 * @var Context
	 */
	private $database;

	/**
	 * @var array
	 */
	private $events = [];

	/**
	 * @var array
	 */
	private $tags = [];

	/**
	 * @var array
	 */
	private $users = [];

	/**
	 * @var \DateTimeInterface|null
	 */
	private $from = null;

	/**
	 * @var \DateTimeInterface|null
	 */
	private $to = null;

	/**
	 * @var bool
	 */
	private $unique = false;

	private $targetMatchMode = self::TARGET_MATCH_MODE_INDIRECT;

	private $groupMode = self::GROUP_MODE_EVENT_AND_TAGS;

	public function __construct(Context $database)
	{
		$this->database = $database;
	}

	public function filterByEvent(string $name, ?array $externIds = null): IQuery
	{
		$this->events[] = [
			$name,
			$externIds,
		];
		return $this;
	}

	public function filterByTag(string $name, ?array $values = null, ?int $expirationHours = null, $applyMode = self::TAG_APPLY_MODE_LAST): IQuery
	{
		$this->tags[] = [
			$name,
			$values,
			$applyMode,
			$expirationHours,
		];
		return $this;
	}

	public function filterByUser(string $externId): IQuery
	{
		$this->users[] = $externId;
		return $this;
	}

	public function from(?\DateTimeInterface $from): IQuery
	{
		$this->from = $from;
		return $this;
	}

	public function to(?\DateTimeInterface $to): IQuery
	{
		$this->to = $to;
		return $this;
	}

	public function target(string $targetMatchMode): IQuery
	{
		$this->targetMatchMode = $targetMatchMode;
		return $this;
	}

	public function group(string $groupMode): IQuery
	{
		$this->groupMode = $groupMode;
		return $this;
	}

	public function unique(bool $unique = true): IQuery
	{
		$this->unique = $unique;
		return $this;
	}

	public function fetchAll(): array
	{
		$selects = [
			'e.name AS name',
			'e.extern_id AS extern_id',
			'e.data AS data',
			'e.group_id AS group_id',
		];
		$selectParameters = [];

		$joins = [];

		$whereConditions = [];
		$whereParameters = [];

		$havingConditions = [];
		$havingParameters = [];

		// filtrovanie eventov
		if ($this->events) {
			$eventConditions = [];
			foreach ($this->events as list ($eventName, $eventExternIds)) {
				$whereParameters[] = $eventName;
				if (!$eventExternIds) {
					$eventConditions[] = 'e.name = ?';
				} elseif (count($eventExternIds) == 1) {
					$eventConditions[] = 'e.name = ? AND e.extern_id = ?';
					$whereParameters[] = reset($eventExternIds);
				} else {
					$eventConditions[] = 'e.name = ? AND e.extern_id IN (?)';
					$whereParameters[] = $eventExternIds;
				}
			}
			$whereConditions[] = '(' . implode(') OR (', $eventConditions) . ')';
		}

		if ($this->users) {
			$joins[] = 'INNER JOIN core_users u ON e.user_id = u.id';
			if (count($this->users) == 1) {
				$whereConditions[] = 'u.extern_id = ?';
				$whereParameters[] = reset($this->users);
			} else {
				$whereConditions[] = 'u.extern_id IN (?)';
				$whereParameters[] = $this->users;
			}
		}

		// from timestamp
		if ($this->from) {
			$whereConditions[] = 'e.timestamp >= ?';
			$whereParameters[] = $this->from;
		}

		// to timestamp
		if ($this->to) {
			$whereConditions[] = 'e.timestamp <= ?';
			$whereParameters[] = $this->to;
		}

		$groupByTags = [];
		if ($this->tags) {
			foreach ($this->tags as $key => list ($tagName, $tagValues, $tagApplyMode, $tagExpirationHours)) {
				$tagSelect = 'SELECT et.value';
				$tagSelect .= ' FROM core_event_tags et';
				$tagSelect .= ' WHERE e.group_id = et.group_id';
				$tagSelect .= ' AND et.name = ?';
				$tagSelect .= ' AND et.timestamp <= e.timestamp';
				// tag has expiration
				if ($tagExpirationHours) {
					$tagSelect .= ' AND et.timestamp >= DATE_SUB(e.timestamp, INTERVAL ' . $tagExpirationHours . ' HOUR)';
				}
				if ($tagApplyMode == self::TAG_APPLY_MODE_LAST) {
					$tagSelect .= ' ORDER BY et.timestamp DESC';
				} else {
					$tagSelect .= ' ORDER BY et.timestamp ASC';
				}
				$tagSelect .= ' LIMIT 1';
				$selects[] = '(' . $tagSelect . ') AS tag' . $key;
				$selectParameters[] = $tagName;

				// filter by applied tag
				if (!$tagValues) {
					// if not tag value => test if any tag is applied
					$havingConditions[] = 'tag' . $key . ' IS NOT NULL';
				} elseif (count($tagValues) == 1) {
					// if only one tag value
					$havingConditions[] = 'tag' . $key . ' = ?';
					$havingParameters[] = reset($tagValues);
				} else {
					// multiple tag values
					$havingConditions[] = 'tag' . $key . ' IN (?)';
					$havingParameters[] = $tagValues;
				}

				$groupByTags['tag' . $key] = $tagName;
			}
		}

		// add target event select
		if ($this->targetMatchMode == self::TARGET_MATCH_MODE_INDIRECT) {
			$selects[] = 'IF (e.target_event_id IS NOT NULL, e.group_id, NULL) AS target';
		} else {
			$selects[] = 'IF (e.target_event_id = e.next_event_id, e.group_id, NULL) AS target';
		}

		$query = 'SELECT ' . implode(', ', $selects);
		$query .= ' FROM core_events e';
		if ($joins) {
			$query .= ' ' . implode(' ', $joins);
		}
		if ($whereConditions) {
			$query .= ' WHERE (' . implode(') AND (', $whereConditions) . ')';
		}
		if ($havingConditions) {
			$query .= ' HAVING (' . implode(') AND (', $havingConditions) . ')';
		}

		$groupQuery = 'SELECT *, GROUP_CONCAT(e.data) AS grouped_data';
		if ($this->unique) {
			$groupQuery .= ', COUNT(DISTINCT e.group_id) AS count';
			$groupQuery .= ', COUNT(DISTINCT e.target) AS targetsCount';
		} else {
			$groupQuery .= ', COUNT(e.group_id) AS count';
			$groupQuery .= ', COUNT(e.target) AS targetsCount';
		}

		$groupQuery .= ' FROM (' . $query . ') AS e';

		if ($this->groupMode === self::GROUP_MODE_EVENT_AND_TAGS) {
			$groupQuery .= ' GROUP BY e.name, e.extern_id';
			if ($groupByTags) {
				$groupQuery .= ', ' . implode(', ', array_keys($groupByTags));
			}
		} elseif ($groupByTags) {
			$groupQuery .= ' GROUP BY ' . implode(', ', array_keys($groupByTags));
		}

		$this->database->query('SET SESSION group_concat_max_len = 10000000'); // @TODO temporary
		$result = $this->database->fetchAll($groupQuery, ...array_merge($selectParameters, $whereParameters, $havingParameters));

		$stats = [];
		foreach ($result as $row) {
			$stats[$row['name']][] = $row;
		}

		return array_map(function ($row) use ($groupByTags) {
			$tags = [];
			foreach ($groupByTags as $tagKey => $tagName) {
				$tags[] = [
					'name' => $tagName,
					'value' => $row[$tagKey],
				];
			}
			$result = [
				'tags' => $tags,
				'count' => $row['count'],
				'targetsCount' => $row['targetsCount'],
				'data' => Json::decode('[' . $row['grouped_data'] . ']', Json::FORCE_ARRAY),
			];
			if ($this->groupMode === self::GROUP_MODE_EVENT_AND_TAGS) {
				$result['name'] = $row['name'];
				$result['externId'] = $row['extern_id'];
			}
			return $result;
		}, $result);
	}

}
