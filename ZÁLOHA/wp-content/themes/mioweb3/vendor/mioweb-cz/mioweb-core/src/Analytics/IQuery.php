<?php declare(strict_types = 1);

namespace Mioweb\Core\Analytics;

interface IQuery
{

	public const TAG_APPLY_MODE_FIRST = 'first';
	public const TAG_APPLY_MODE_LAST = 'last';

	public const TARGET_MATCH_MODE_DIRECT = 'direct';
	public const TARGET_MATCH_MODE_INDIRECT = 'indirect';

	public const GROUP_MODE_EVENT_AND_TAGS = 'event-and-tags';
	public const GROUP_MODE_TAGS = 'tags';

	public function filterByEvent(string $name, ?array $externIds = null): IQuery;

	public function filterByTag(string $name, ?array $values = null, ?int $expirationHours = null, $applyMode = IQuery::TAG_APPLY_MODE_LAST): IQuery;

	public function filterByUser(string $externId): IQuery;

	public function from(?\DateTimeInterface $from): IQuery;

	public function to(?\DateTimeInterface $to): IQuery;

	public function target(string $targetMatchMode): IQuery;

	public function group(string $groupMode): IQuery;

	public function unique(bool $unique = true): IQuery;

	public function fetchAll(): array;

}
