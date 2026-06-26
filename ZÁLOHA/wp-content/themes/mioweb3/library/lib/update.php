<?php

add_filter('pre_set_site_transient_update_themes', 'check_for_update');

function check_for_update($checked_data)
{
	global $wp_version;
	$theme_base = basename(dirname(dirname(dirname(__FILE__))));

	if (MW()->getLicense() && !MW()->getLicense()->isHosting() && isset($checked_data->checked[$theme_base])) {
		$api_url = LICENSE_SERVER . 'license/check-update';

		$license = get_transient('cms_license');

		if (isset($license['code']) && $license['code'] == 'success' && !defined('MW_NO_UPDATE_CHECK')) {
			$raw_response = wp_remote_post($api_url, [
				'method' => 'GET',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => [],
				'body' => [
					'serial_number' => $license['license'],
					'current_version' => $checked_data->checked[$theme_base],
					'url' => get_home_url(),
					'php_version' => urlencode(mw_get_php_version_main_part()),
				],
			]);

			//print_r($raw_response);
			//die();

			if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)) {
				$response = json_decode($raw_response['body']);

				if (isset($response->status) && $response->status == 'success' && $response->last_version != null) {
					$checked_data->response[$theme_base] = [
						'package' => $response->last_version->download_url,
						'new_version' => $response->last_version->version,
						'url' => $response->last_version->info_url ?? 'https://www.mioweb.cz/aktualizace/',
						'theme' => 'mioweb3',
					];

					$update_info['current_version'] = [
						'id' => $response->last_version->id,
						'released_at' => $response->last_version->released_at,
						'version' => $response->last_version->version,
						'note' => $response->last_version->note,
						'download_url' => $response->last_version->download_url,
					];
					$update_info['previous_versions'] = $response->previous_versions;

					update_option('mioweb_update_info', $update_info);
				} elseif (isset($checked_data->response[$theme_base])) {
					unset($checked_data->response[$theme_base]);
				}
			}
		}
	} elseif (isset($checked_data->response[$theme_base])) {
		unset($checked_data->response[$theme_base]);
	}

	return $checked_data;
}


$info = get_option('_site_transient_update_themes');
$theme = basename(dirname(__FILE__, 2));
if (isset($info->response[$theme])) {
	add_action('admin_notices', 'cms_new_version_notification');
}

function cms_new_version_notification()
{
	$info = get_option('_site_transient_update_themes');
	$theme = basename(dirname(__FILE__, 2));

	if (defined('MW_NO_UPDATE_CHECK') || !isset($info->response[$theme])) {
		return;
	}

	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');

	$url = wp_nonce_url('update.php?action=upgrade-theme&theme=' . $theme, 'upgrade-theme_' . $theme, '_wpnonce');
	$info = get_option('_site_transient_update_themes');

	?>
	<div id="message" class="update-nag">
	<?php printf(__('K dispozici je nová verze <strong>Mioweb šablony %s</strong>.', 'cms'), $info->response[$theme]['new_version']); ?>
	<?php echo __('Doporučujeme', 'cms'); ?> <a
			href="<?php echo $url; ?>"><?php echo __('Provést aktualizaci', 'cms'); ?></a>.
	</div>
	<?php if (isset($info->response[$theme]['news'])) { ?>
	<div id="cms_changelog" style="display: none;">
		<div><br/>
		<?php print_r($info->response[$theme]['news']); ?>
		</div>
	</div>
		<?php
	}
}

?>
