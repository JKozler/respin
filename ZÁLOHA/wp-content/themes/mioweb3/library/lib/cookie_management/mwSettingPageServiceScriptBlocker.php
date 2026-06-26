<?php
class mwSettingPageService_scriptBlocker extends mwSettingPageService
{

	public function saveSetting($tosave)
	{
		MWDB()->setOption($this->settingPage()->getId(), $tosave);

		// create mu-plugin for blocking $plugins
		$mu_plugin_file_source_path = __DIR__ . '/mioweb_gdpr_plugin_blocker.php';
		$mu_plugins = get_mu_plugins();

		$mu_plugin_file = 'mioweb_gdpr_plugin_blocker.php';
		$mu_plugins_path = WP_CONTENT_DIR . '/' . 'mu-plugins';

		$mu_plugin_file_path = $mu_plugins_path . '/mioweb_gdpr_plugin_blocker.php';

		// add mu file
		if (file_exists($mu_plugins_path) && !array_key_exists($mu_plugin_file, $mu_plugins)) {
			copy($mu_plugin_file_source_path, $mu_plugin_file_path);
		} else {
			// create mu-plugins folder
			if (!file_exists($mu_plugins_path)) {
				$create_mu_folder = mkdir($mu_plugins_path, 0755, true);
				if ($create_mu_folder) {
					copy($mu_plugin_file_source_path, $mu_plugin_file_path);
				}
			}
		}
		if (!file_exists($mu_plugin_file_path)) {
			mwMessages()->error(__('Nepodařilo se vytvořit soubor <i>' . $mu_plugin_file_path . '</i>. Pravděpodobně z důvodů nedostatečného oprávnění pro vytvoření souboru na vašem hostingu. Kontaktujte svého poskytovatele hostingu nebo upravte oprávnění na vašem hostingu, tak aby mohl být soubor vytvořen a nastavení znovu uložte. Bez tohoto souboru nebude blokování pluginů fungovat.', 'cms'));
		}
	}

}
