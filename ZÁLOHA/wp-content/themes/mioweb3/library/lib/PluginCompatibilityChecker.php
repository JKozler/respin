<?php declare(strict_types=1);

class PluginCompatibilityChecker
{

	private const OPTION_NAME = 'mw_tracked_plugins';
	private const TRANSIENT_NAME = 'mw_tracked_plugins_t';
	private const REFRESH_INTERVAL = WEEK_IN_SECONDS;

	public static function init()
	{
		$self = new self();

		add_action('admin_notices', [$self, 'onAdminNotices']);
		add_action('activate_plugin', [$self, 'onBeforeActivatePlugin']);
		add_action('deactivate_plugin', [$self, 'refreshTrackedPlugins']);
	}

	public function onAdminNotices()
	{
		if (isset($_GET['mw_plugin_forbidden']) && $_GET['mw_plugin_forbidden']) {
			$plugin = $_GET['mw_plugin_forbidden'];
			echo '<div class="error"><p>'
				. sprintf(__('Plugin <strong>%s</strong> je nekompatibilní s Miowebem a nemůže být aktivován.', 'cms'), $plugin)
				. '</p></div>';
		}

		$activatedPlugins = get_option('active_plugins');
		if (!$activatedPlugins) {
			return;
		}

		$activatedPlugins = array_map([$this, 'shortenPluginName'], $activatedPlugins);

		$trackedPlugins = $this->getTrackedPlugins();
		$forbidden = $problematic = [];
		foreach ((array) $trackedPlugins as $trackedPlugin) {
			if (in_array($trackedPlugin->name, $activatedPlugins, true)) {
				if ($trackedPlugin->type === PluginType::FORBIDDEN) {
					$forbidden[] = $trackedPlugin->name;
				} elseif ($trackedPlugin->type === PluginType::PROBLEMATIC) {
					$problematic[] = $trackedPlugin->name;
				}
			}
		}

		// Render administration flash messages
		if ($forbidden) {
			echo '<div class="error"><p>';

			echo sprintf(
				_n(
					'Plugin <strong>%s</strong> je nekompatibilní s Miowebem. Prosím deaktivujte tento plugin.',
					'Pluginy <strong>%s</strong> jsou nekompatibilní s Miowebem. Prosím deaktivujte tyto pluginy.',
					count($forbidden),
					'cms'
				),
				implode(', ', $forbidden)
			);

			echo '</p></div>';
		}

		if ($problematic) {
			echo '<div class="notice notice-warning"><p>';

			echo sprintf(
				_n(
					'Plugin <strong>%s</strong> může způsobovat problémy a nedoporučujeme jej spolu s Miowebem používat.',
					'Pluginy <strong>%s</strong> můžou způsobovat problémy a nedoporučujeme je spolu s Miowebem používat.',
					count($problematic),
					'cms'
				),
				implode(', ', $problematic)
			);

			echo '</p></div>';
		}
	}

	/**
	 * Check the plugin immediately before activation and possibly interrupt activation
	 *
	 * @param string $plugin
	 * @param bool $network_hide
	 */
	public function onBeforeActivatePlugin(string $plugin, bool $network_hide = false)
	{
		$plugin = $this->shortenPluginName($plugin);

		if ($this->isPluginForbidden($plugin)) {
			wp_redirect(self_admin_url('plugins.php?mw_plugin_forbidden=' . $plugin));
			exit();
		}
	}

	public function getTrackedPlugins(bool $forceRefresh = false)
	{
		$synced = get_transient(self::TRANSIENT_NAME);

		if (!$synced || $forceRefresh) {
			$this->refreshTrackedPlugins();
		}

		return get_option(self::OPTION_NAME) ?: [];
	}

	/**
	 * Refresh info about plugin categories from MWA
	 */
	public function refreshTrackedPlugins()
	{
		$query = ['types' => [PluginType::FORBIDDEN, PluginType::PROBLEMATIC]];
		$url = LICENSE_SERVER . 'wp-plugins?' . http_build_query($query);

		$httpResponse = wp_remote_get($url);
		$body = json_decode(wp_remote_retrieve_body($httpResponse));

		update_option(self::OPTION_NAME, $body);
		set_transient(self::TRANSIENT_NAME, '1', self::REFRESH_INTERVAL);
	}

	private function isPluginForbidden(string $pluginName): bool
	{
		$trackedPlugins = $this->getTrackedPlugins(true);

		foreach ($trackedPlugins as $trackedPlugin) {
			if ($trackedPlugin->name === $pluginName) {
				return $trackedPlugin->type === PluginType::FORBIDDEN;
			}
		}

		return false;
	}

	/**
	 * Get only directory names from full plugin names (E.g. "wp-tracy/index.php" -> "wp-tracy")
	 *
	 * @param string $pluginName
	 * @return string
	 */
	private function shortenPluginName(string $pluginName): string
	{
		$parts = explode('/', $pluginName);

		return count($parts) >= 2 ? $parts[0] : $pluginName;
	}

}
