<?php

if (!is_admin()) {
	// Deactivate plugins base on the condition meets
	add_filter('option_active_plugins', 'mwRemoveBlockedPlugins');
}

function mwRemoveBlockedPlugins($plugins)
{
	if (file_exists(get_template_directory() . '/library/lib/cookie_management/MwCookieManagement.php')) {
		require_once(get_template_directory() . '/library/lib/cookie_management/MwCookieManagement.php');

		$blockerSetting = MwCookies()->getBlockedSetting();

		if ($blockerSetting && MwCookies()->useCookieManagement()) {
			$pluginSettings = $blockerSetting['plugins'] ?? [];

			foreach ($plugins as $key => $plugin) {
				if (isset($pluginSettings[$plugin]['block']) && !MwCookies()->isPermitted($pluginSettings[$plugin]['type'])) {
					unset($plugins[$key]);
				}
			}
		}
	}

	return $plugins;
}
