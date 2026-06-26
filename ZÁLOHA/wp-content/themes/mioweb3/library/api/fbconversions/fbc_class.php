<?php declare(strict_types=1);

use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequestAsync;
use FacebookAds\Object\ServerSide\UserData;

function MWFBC(): MwFacebookConversions
{
	return MwFacebookConversions::instance();
}

class MwFacebookConversions
{

	private static ?MwFacebookConversions $instance = null;

	private mwAPIConnectItem $fbcApi;

	private string $pixel_id;

	private ?string $test_id;

	private bool $debug_mode;

	private Api $api;

	private function __construct()
	{
		$this->fbcApi = mwApiConnect()->getApi('fbconversions');
		if ($this->isActive()) {
			$option = $this->fbcApi->getOption();
			$this->pixel_id = $option['pixel_id'];
			$this->debug_mode = (isset($option['debug_mode']) && $option['debug_mode']);
			$this->api = Api::init(null, null, $option['access_token'] ?? '');

			if ($this->debug_mode) {
				$this->test_id = $option['test_id'];
			}
		}
	}

	public function isActive(): bool
	{
		return $this->fbcApi->isConnected();
	}

	public function isDebugMode(): bool
	{
		return $this->debug_mode;
	}

	public function sendEvent(string $name, array $options)
	{
		$currency = $options['currency'] ?? null;
		$items = $options['items'] ?? [];
		$value = $options['value'] ?? 0.0;
		$num_items = 0;


		$this->api->setLogger(new CurlLogger());

		$contents = [];

		foreach ($items as $item) {
			$contents[] = (new Content())
				->setProductId($item['item_id'] ?? null)
				->setQuantity($item['quantity'] ?? 1)
				->setItemPrice($item['price'] ?? 0.0)
				->setTitle($item['item_name'] ?? null);
			$num_items += $item['quantity'] ?? 1;
		}

		$custom_data = (new CustomData())
			->setContents($contents)
			->setCurrency($currency)
			->setValue($value)
			->setNumItems($num_items);

		$user_data = (new UserData())
			->setClientUserAgent($_SERVER['HTTP_USER_AGENT'] ?? null)
			->setClientIpAddress($_SERVER['REMOTE_ADDR'] ?? null);

		$event = (new Event())
			->setEventName($name)
			->setEventTime(time())
			->setCustomData($custom_data)
			->setUserData($user_data)
			->setActionSource(ActionSource::WEBSITE)
			->setEventSourceUrl(home_url($_SERVER['REQUEST_URI'] ?? null));

		$events[] = $event;

		$request = (new EventRequestAsync($this->pixel_id))->setEvents($events);

		if ($this->debug_mode) {
			$request->setTestEventCode($this->test_id);
		}

		$request->execute();
	}

	public static function instance(): MwFacebookConversions
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
