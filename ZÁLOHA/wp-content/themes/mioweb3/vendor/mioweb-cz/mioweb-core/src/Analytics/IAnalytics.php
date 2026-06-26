<?php declare(strict_types = 1);

namespace Mioweb\Core\Analytics;

interface IAnalytics
{

	public function logEvent(string $name, ?string $externId = null, ?string $uniqueId = null, array $tags = [], ?array $data = null, ?string $targetEventName = null, ?string $targetEventExternId = null, bool $checkCrawler = true): void;

	public function getEventData(string $name, ?string $externId, string $uniqueId): ?array;

	public function getStats(): IQuery;

	public function setUser(string $externId, array $endEventNames = []): void;

	public function getUser(): ?string;

	public function cleanCookie(): void;

}
