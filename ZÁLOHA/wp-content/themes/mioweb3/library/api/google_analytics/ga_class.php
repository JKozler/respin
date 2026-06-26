<?php
declare(strict_types=1);
use Nette\Utils\Json;

function MWGA(): MwGoogleAnalytics
{
   return MwGoogleAnalytics::instance();
}

class MwGoogleAnalytics
{

	private static $instance = null;

	/** @var */
	private $gaApi;

	/** @var string */
	private $measurementId;

	/** @var bool */
	private $debugMode = false;

	private function __construct()
	{
		$this->gaApi = mwApiConnect()->getApi('google_analytics');
		if ($this->isActive()) {
			$option = $this->gaApi->getOption();
			$this->measurementId = htmlspecialchars($option['measurement_id'] ?? '', ENT_QUOTES);
			$this->debugMode = (isset($option['debug_mode']) && $option['debug_mode']);
		}
	}

	public function isActive(): bool
	{
		return $this->gaApi->isConnected();
	}

	public function isDebugMode(): bool
	{
		return $this->debugMode;
	}



	public function getCode(): string
	{
		$ad_storage = MwCookies()->isPermitted('marketing') ? 'granted' : 'denied';
		$analytics_storage = MwCookies()->isPermitted('analytics') ? 'granted' : 'denied';

		return '<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=' . $this->measurementId . '"></script>
			<script>
				window.dataLayer = window.dataLayer || [];
				' . MWGTM()->getDataLayer() . '
				function gtag(){dataLayer.push(arguments);}
				gtag(\'js\', new Date());

				gtag(\'consent\', \'default\', {
				\'ad_storage\': \'' . $ad_storage . '\',
				\'analytics_storage\': \'' . $analytics_storage . '\'
				});

				gtag(\'config\', \'' . $this->measurementId . '\');
			</script>';
	}

	public function printEvent(string $name, array $options)
	{
		if ($this->isDebugMode()) {
			$options['debug_mode'] = true;
		}

		return "<script>gtag('event', '" . $name . "', " . Json::encode($options) . ')</script>';
	}

	/** @return MwGoogleAnalytics */
	public static function instance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
