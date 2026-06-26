<?php declare(strict_types = 1);

namespace Mioweb\Core\Analytics;

use Mioweb\Core\Utils\Options;
use Nette\Database\Context;
use Nette\Utils\Json;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Analytics implements IAnalytics
{
	private const COOKIE_NAME = 'uuid';

	private const UTM_SOURCE = 'utm_source';
	private const UTM_MEDIUM = 'utm_medium';
	private const UTM_CAMPAIGN = 'utm_campaign';
	private const UTM_TERM = 'utm_term';
	private const UTM_CONTENT = 'utm_content';

	/** @var bool */
	private $isCrawler;

	/** @var Options */
	private $options;

	/** @var Context */
	private $database;

	/** @var UuidInterface|null */
	private $cookie = null;

	public function __construct(
		bool $isCrawler,
		Options $options,
		Context $database
	)
	{
		$this->isCrawler = $isCrawler;
		$this->options = $options;
		$this->database = $database;
	}

	public function logEvent(
		string $name,
		?string $externId = null,
		?string $uniqueId = null,
		array $tags = [],
		?array $data = null,
		?string $targetEventName = null,
		?string $targetEventExternId = null,
		bool $checkCrawler = true
	): void {
		if ($checkCrawler && $this->isCrawler) {
			return;
		}
		if ($uniqueId !== null) {
			$event = $this->database->table('core_events')->where([
				'name' => $name,
				'extern_id' => $externId,
				'unique_id' => $uniqueId,
			])->fetch();
			if ($event) {
				return;
			}
		}

		$eventId = Uuid::uuid4();
		$cookie =  $this->getCookieId();
		$user = $this->getUserId($cookie);
		$groupId = $user ? $user : $cookie;
		$timestamp = new \DateTimeImmutable;

		if (!$data) {
			// check if event with same date already exist
			$event = $this->database->table('core_events')->where([
				'cookie_id' => $cookie->getBytes(),
				'name' => $name,
				'extern_id' => $externId,
				'target_event_name' => $targetEventName,
				'target_event_extern_id' => $targetEventExternId,
				'DATE(timestamp) = DATE(?)' => $timestamp,
			])->fetch();
			if ($event) {
				return;
			}
		}

		$this->database->table('core_events')->insert([
			'id' => $eventId->getBytes(),
			'cookie_id' => $cookie->getBytes(),
			'user_id' => $user ? $user->getBytes() : null,
			'group_id' => $groupId->getBytes(),
			'name' => $name,
			'extern_id' => $externId,
			'unique_id' => $uniqueId,
			'target_event_name' => $targetEventName,
			'target_event_extern_id' => $targetEventExternId,
			'data' => $data ? Json::encode($data) : null,
			'timestamp' => $timestamp,
		]);

		$tags = array_replace($this->getTagsFromRequest(), $tags);
		foreach ($tags as $tagName => $tagValue) {
			$this->database->table('core_event_tags')->insert([
				'id' => Uuid::uuid4()->getBytes(),
				'event_id' => $eventId->getBytes(),
				'group_id' => $groupId->getBytes(),
				'name' => $tagName,
				'value' => $tagValue, // @TODO what if to long? make hash by default? ... or other column for index and other text for value?
				'timestamp' => $timestamp,
			]);
		}

		$this->updateTargets($groupId);
	}

	public function getEventData(
		string $name,
		?string $externId,
		string $uniqueId
	): ?array {
		$event = $this->database->table('core_events')->where([
			'name' => $name,
			'extern_id' => $externId,
			'unique_id' => $uniqueId,
		])->fetch();

		return $event && $event['data'] ? Json::decode($event['data'], Json::FORCE_ARRAY) : null;
	}

	public function setUser(string $externId, array $endEventNames = []): void
	{
		$usersTable = $this->database->table('core_users');
		$dbUser = $usersTable->where([
			'extern_id' => $externId,
		])->fetch();
		if (!$dbUser) {
			$userId = Uuid::uuid4();
			$usersTable->insert([
				'id' => $userId->getBytes(),
				'extern_id' => $externId,
			]);
		} else {
			$userId = Uuid::fromBytes($dbUser['id']);
		}

		$cookieId = $this->getCookieId();

		$eventsTable = $this->database->table('core_events');
		$eventsTable->where([
			'cookie_id' => $cookieId->getBytes(),
		])->order('timestamp DESC');

		// check last end event if exist
		$lastEndEvent = (clone $eventsTable)->where($endEventNames ? [
			'name' => $endEventNames,
			'user_id IS NOT NULL',
		] : 'user_id IS NOT NULL')->fetch();
		if ($lastEndEvent) {
			$eventsTable->where([
				'timestamp > ?' => $lastEndEvent->timestamp,
			]);
		}

		// update events
		$eventsTable->update([
			'user_id' => $userId->getBytes(),
			'group_id' => $userId->getBytes(),
		]);
		// update event tags group id by events
		$this->database->table('core_event_tags')->where([
			'event_id' => $eventsTable,
		])->update([
			'group_id' => $userId->getBytes(),
		]);
		// set current user to cookie
		$this->database->table('core_cookies')->where([
			'id' => $cookieId->getBytes(),
		])->update([
			'user_id' => $userId->getBytes(),
		]);

		$this->updateTargets($userId, true);
	}

	public function getUser(): ?string
	{
		$userId = $this->getUserId($this->getCookieId());
		if (!$userId) {
			return null;
		}
		$dbUser = $this->database->table('core_users')->where([
			'id' => $userId->getBytes(),
		])->fetch();
		return $dbUser['extern_id'];
	}

	public function getStats(): IQuery
	{
		return new Query($this->database);
	}

	private function getUserId(UuidInterface $cookie): ?UuidInterface
	{
		$dbCookie = $this->database->table('core_cookies')->where([
			'id' => $cookie->getBytes(),
		])->fetch();

		if ($dbCookie && $dbCookie['user_id']) {
			return Uuid::fromBytes($dbCookie['user_id']);
		}
		return null;
	}

	private function getCookieId(): UuidInterface
	{
		if ($this->cookie) {
			return $this->cookie;
		}

		try {
			$this->cookie = Uuid::fromString($_COOKIE[self::COOKIE_NAME] ?? '');
		} catch (InvalidUuidStringException $exception) {
			$this->cookie = Uuid::uuid4();
		}

		// check db cookie
		$dbCookie = $this->database->table('core_cookies')->where([
			'id' => $this->cookie->getBytes(),
		])->fetch();
		if (!$dbCookie) {
			$this->database->table('core_cookies')->insert([
				'id' => $this->cookie->getBytes(),
			]);
		}

		setcookie(self::COOKIE_NAME, $this->cookie->toString(), (int)(new \DateTimeImmutable('1 year'))->format('U'), '/'); // @TODO domain

		return $this->cookie;
	}

	public function cleanCookie(): void
	{
		if (isset($_COOKIE[self::COOKIE_NAME])) {
			setcookie(self::COOKIE_NAME, '', time() - 3600, '/');
			unset($_COOKIE[self::COOKIE_NAME]);
		}
		$this->cookie = null;
	}

	private function getTagsFromRequest(): array
	{
		$tags = [];
		foreach ([self::UTM_SOURCE, self::UTM_MEDIUM, self::UTM_CAMPAIGN, self::UTM_TERM, self::UTM_CONTENT] as $name) {
			if ($value = $_GET[$name] ?? null) {
				$tags[$name] = $value;
			}
		}
		return $tags;
	}

	private function updateTargets(UuidInterface $groupId, bool $recount = false): void
	{
		// set next events
		$query = 'UPDATE core_events e';
		$query .= ' JOIN core_events e2 ON e.group_id = ? AND e2.group_id = e.group_id AND e2.timestamp > e.timestamp';
		if (!$recount) {
			$query .= ' AND e.next_event_id IS NULL';
		}
		$query .= ' LEFT JOIN core_events e3 ON e3.group_id = e.group_id AND e3.timestamp > e.timestamp AND e3.timestamp < e2.timestamp';
		$query .= ' SET e.next_event_id = e2.id';
		$query .= ' WHERE e3.id IS NULL';
		$this->database->query($query, $groupId->getBytes());
		// set targets
		$query = 'UPDATE core_events e';
		$query .= ' JOIN core_events e2 ON e.group_id = ? AND e2.group_id = e.group_id AND e2.timestamp > e.timestamp AND e2.name = e.target_event_name AND (e.target_event_extern_id IS NULL OR e2.extern_id = e.target_event_extern_id)';
		if (!$recount) {
			$query .= ' AND e.target_event_id IS NULL';
		}
		$query .= ' LEFT JOIN core_events e3 ON e3.group_id = e.group_id AND e3.timestamp > e.timestamp AND e3.timestamp < e2.timestamp AND e3.name = e.target_event_name AND (e.target_event_extern_id IS NULL OR e3.extern_id = e.target_event_extern_id)';
		$query .= ' SET e.target_event_id = e2.id';
		$query .= ' WHERE e3.id IS NULL';
		$this->database->query($query, $groupId->getBytes());
	}

}
