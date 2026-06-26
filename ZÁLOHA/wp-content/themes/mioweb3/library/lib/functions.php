<?php

use JetBrains\PhpStorm\Deprecated;
use Mioweb\Shop\Order\OrderRepository;
use Nette\Http\Url;

if (!function_exists('load_child_theme')) {

	function load_child_theme()
	{
	}
}

function mw_is_lite_editor()
{
	$modules = get_option('cms_license_modules');

	if (is_array($modules)) {
		return in_array('lite', $modules) ? true : false;
	}

	return false;

	//return true;
}

function mw_sp($count, $single, $plurar1, $plurar2)
{
	if ($count === 1) {
		return $count . ' ' . $single;
	}

	if ($count > 1 && $count < 5) {
		return $count . ' ' . $plurar1;
	}

	return $count . ' ' . $plurar2;
}

function mw_get_php_version_main_part(): string
{
	$phpversion = (string) phpversion();
	preg_match('#^\d+(\.\d+)*#', $phpversion, $match); // Get rid of suffixes like "-1+0~20211119.91+debian10~1"

	return $match[0] ?? $phpversion;
}

function mwPrintDate($timestamp, $type = 'datetime', $convertFromUTC = false) // types date, time, datetime
{
	if ($convertFromUTC) {
		$timestamp = mwConvTimestampUTC2TimestampLocal($timestamp);
	}

	$format = '';

	if ($type == 'datetime') {
		$format = get_option('date_format') . ' ' . get_option('time_format');
	} elseif ($type == 'date') {
		$format = get_option('date_format');
	} elseif ($type == 'time') {
		$format = get_option('time_format');
	}

	return date_i18n($format, $timestamp);
}

/**
 * Convert Unix timestamp in UTC into Unix timestamp in WP local timezone.
 *
 * @param $timestampUTC
 * @return mixed
 * @throws Exception
 */
function mwConvTimestampUTC2TimestampLocal($timestampUTC)
{
	try {
		// get datetime object from unix timestamp
		$datetime = new DateTime("@{$timestampUTC}", new DateTimeZone('UTC'));
		// set the timezone to the site timezone
		$datetime->setTimezone(new DateTimeZone(wp_get_timezone_string()));

		// return the unix timestamp adjusted to reflect the site's timezone
		return $timestampUTC + $datetime->getOffset();
	} catch (Exception $e) {
		// something broke
		throw $e;
	}
}


/**
 * Returns the timezone string for a site, even if it's set to a UTC offset
 *
 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
 * Credit to https://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
 *
 * @return string valid PHP timezone string
 */
function wp_get_timezone_string()
{
	// if site timezone string exists, return it
	if ($timezone = get_option('timezone_string')) {
		return $timezone;
	}

	// get UTC offset, if it isn't set then return UTC
	if (($utc_offset = get_option('gmt_offset', 0)) === 0) {
		return 'UTC';
	}

	// adjust UTC offset from hours to seconds
	$utc_offset *= 3600;

	// attempt to guess the timezone string from the UTC offset
	if ($timezone = timezone_name_from_abbr('', $utc_offset, 0)) {
		return $timezone;
	}

	// last try, guess timezone string manually
	$is_dst = date('I');

	foreach (timezone_abbreviations_list() as $abbr) {
		foreach ($abbr as $city) {
			if ($city['dst'] == $is_dst && $city['offset'] == $utc_offset) {
				return $city['timezone_id'];
			}
		}
	}

	// fallback to UTC
	return 'UTC';
}



function write_meta($fields, $meta, $tagname, $tagid, $post_id = '', $type = 'setting', $is_multielement = false)
{
	foreach ($fields as $field) {
		if (isset($field['hide_field']) && $field['hide_field']) {
			continue;
		}

		$title = '';
		if (isset($field['name']) && $field['name']) {
			$title = $field['name'];
		}
		if (isset($field['title']) && $field['title']) {
			$title = $field['title'];
		}

		$show_class = (isset($field['show_group'])
			? ' cms_show_group_' . $tagid . '_' . $field['show_group'] . ' '
			. (isset($field['show_val'])
				? ' cms_show_group_' . $tagid . '_' . $field['show_group'] . '_'
				. implode(
					' cms_show_group_' . $tagid . '_' . $field['show_group'] . '_',
					explode(',', $field['show_val'])
				)
				: ''
			)
			: ''
		);

		if (isset($field['class'])) {
			$show_class .= ' ' . $field['class'];
		}

		if ($field['type'] === 'group') {
			// GROUP
			echo '<div class="' . $show_class . '">';
			write_meta($field['setting'], $meta, $tagname, $tagid, $post_id, $type, $is_multielement);
			echo '</div>';
		} elseif ($field['type'] == 'box') {
			// BOX
			echo '<div class="mw_setting_box ' . $show_class . '">';
			if (isset($field['title'])) {
				echo '<div class="mw_setting_box_head">' . $field['title'] . '</div>';
			}
			echo '<div class="mw_setting_box_content_s ' . ($field['content_class'] ?? '') . '">';
			write_meta($field['setting'], $meta, $tagname, $tagid, $post_id, $type, $is_multielement);
			echo '</div>';
			echo '</div>';
		} elseif ($field['type'] == 'toggle_group') {
			// TOGGLE GROUP
			$switchVal = '';

			if (isset($field['checkbox'])) {
				$show_class .= ' mw_toggle_group_checkbox';
				$open = isset($meta[$field['id']]) && $meta[$field['id']] ? 1 : 0;
				if (isset($field['invert'])) {
					$open = !$open;
				}
			} elseif (isset($field['status_switch'])) {
				$switchVal = isset($field['id']) ? $meta[$field['id']] ?? '' : '';
				cms_load_customized_field_value($post_id, $field, $switchVal);
				$show_class .= ' mw_toggle_group_checkbox mw_toggle_group_status_switch';
				$open = $switchVal === $field['true_val'] ? 1 : 0;
			} else {
				$open = 0;
			}

			if (!isset($field['open']) && !$open) {
				$show_class .= ' mw_toggle_group_close';
			}

			$attrs = 'data-type="toggle_group"';
			if (isset($field['action'])) {
				$attrs .= ' data-action="' . $field['action'] . '"';
			}
			if (isset($field['target'])) {
				$attrs .= ' data-target="' . $field['target'] . '"';
			}
			if (isset($field['show'])) {
				$attrs .= 'data-show="' . $tagid . '_' . $field['show'] . '" ';

				?>
				<style>
					.cms_show_group_<?php echo $tagid . '_' . $field['show']; ?>:not(.cms_show_group_<?php echo $tagid . '_' . $field['show']; ?>_<?php echo $open ?>) {
						display: none;
					}
				</style>
				<?php
			}

			?>
			<div class="mw_setting_box mw_toggle_group mw_onedit_action <?php echo $show_class ?>" <?php echo $attrs; ?>>
				<a class="mw_setting_box_head mw_toggle_group_head" href="#">
				<?php

				if (isset($field['checkbox'])) {
					echo '<div class="mw_switch_container">';
					echo '<div class="mw_switch ' . (isset($field['invert']) ? 'mw_switch_invert' : '') . '">';
					echo '<input class="cms_nodisp" autocomplete="off" type="checkbox" name="' . getTagName($tagname, $field['id']) . '" ' . (isset($meta[$field['id']]) && $meta[$field['id']] ? 'checked="checked"' : '') . ' value="1" />';
					echo '<span class="mw_switch_slider"></span>';
					echo '</div>';
					echo '<div class="cms_clear"></div>';
					echo '</div>';
				} elseif (isset($field['status_switch'])) {
					echo mwAdminComponents::statusSwitch([
						'true_val' => $field['true_val'],
						'false_val' => $field['false_val'],
						'name' => getTagName($tagname, $field['id']),
					], $switchVal);
				} else {
					echo mwAdminComponents::icon(['icon' => 'chevron-up']);
				}
				echo $field['title'];

				if (isset($field['tooltip']) && $field['tooltip']) {
					echo mwAdminComponents::tooltip([
							'text' => $field['tooltip'],
							'tooltip_align' => $field['tooltip_align'] ?? 'right',
					]);
				}

				?>
				</a>
				<div class="mw_setting_box_content_s mw_toggle_group_content <?php echo !isset($field['open']) && !$open ? 'cms_nodisp' : ''; ?>">
					<?php write_meta($field['setting'], $meta, $tagname, $tagid, $post_id, $type, $is_multielement); ?>
				</div>
			</div>
			<?php

			// TABS
		} elseif ($field['type'] == 'tabs') {
			if (count($field['tabs']) > 1) {
				$atributes = '';

				// info for page builder

				$f_css = $field['onedit']['css'] ?? '';
				$f_action = $field['onedit']['action'] ?? '';
				$f_setting = $field['onedit']['setting'] ?? '';
				$f_class = $field['onedit']['class'] ?? '';
				$f_target = $field['onedit']['target'] ?? '';

				$atributes .= 'data-type="' . $field['type'] . '" data-css="' . $f_css . '" data-action="' . $f_action . '" data-setting="' . $f_setting . '" data-target="' . $f_target . '" data-setname="' . $field['id'] . '" data-class="' . $f_class . '"';
				$show_class .= ' mw_onedit_action';

				echo '<div class="mw_setting_tabs_container ' . $show_class . '" ' . $atributes . '>';

				$tabs = [];
				foreach ($field['tabs'] as $id => $set_tab) {
					$tabs[] = [
						'id' => $id,
						'icon' => $set_tab['icon'] ?? '',
						'name' => $set_tab['name'],
					];
				}

				$group = 'mw_setting_tab_' . $field['id'];
				$current = isset($field['content']) && isset($meta[$field['id']]) ? $meta[$field['id']] : '';

				echo mwAdminComponents::tabs([
					'tabs' => $tabs,
					'group' => $group,
					'member' => isset($field['content']) ? getTagName($tagname, $field['id']) : '',
				], $current, 'mw_setting_tabs');

				$i = 1;
				foreach ($field['tabs'] as $id => $set_tab) {
					$active = $current == $id || ($current == '' && $i == 1) ? true : false;

					echo '<div id="' . $group . '_' . $id . '" class="mw_tab ' . $group . '_container ' . ($active ? 'active' : '') . '">';
					write_meta($set_tab['setting'], $meta, $tagname, $tagid, $post_id, $type, $is_multielement);
					echo '</div>';
					$i++;
				}
				echo '</div>';
			} else {
				foreach ($field['tabs'] as $id => $set_tab) {
					write_meta($set_tab['setting'], $meta, $tagname, $tagid, $post_id, $type, $is_multielement);
				}
			}
		} elseif ($field['type'] == 'multielement') {
			$content = null;
			if ($type == 'setting') {
				$content = $meta[$field['id']] ?? [];
			} elseif (isset($meta['style'][$field['id']])) {
				$content = $meta['style'][$field['id']];
			}
			// Load field value with custom storage of value
			cms_load_customized_field_value($post_id, $field, $content);

			$attrs = 'data-type="multielement"';
			if (isset($field['onedit'])) {
				$attrs .= ' data-action="' . $field['onedit']['action'] . '"';
			}


			?>
			<div id="<?php echo $tagid . '_' . $field['id']; ?>" class="set_form_row mw_onedit_action <?php echo $show_class ?>" <?php echo $attrs; ?>>
			<?php

			if ($title) {
				echo '<div class="label">';
				echo $title;
				if (isset($field['tooltip'])) {
					echo mwAdminComponents::tooltip([
						'text' => $field['tooltip'],
						'tooltip_align' => $field['tooltip_align'] ?? 'right',
					]);
				}
				echo '</div>';
			}

			$field['tagid'] = $tagid . '_' . $field['id'];
			$field['tagname'] = getTagName($tagname, $field['id']);
			echo MwFields::multiElement($field, $content);
			?>

			</div>
			<?php
		} elseif ($field['type'] == 'title') {
			echo '<div class="set_form_row ' . $show_class . '"><h4>' . $field['name'] . '</h4></div>';
		} elseif (!isset($field['inline'])) {
			if ($field['type'] != 'hidden_input') {
				echo '<div class="set_form_row ' . $show_class . '">';
			}

			if ($title /*&& !isset($field['hidden_setting']) && $field['type'] != 'font'*/) {
				echo '<div class="label"><span>' . $title . '</span> ';
				if (isset($field['tooltip'])) {
					echo mwAdminComponents::tooltip([
						'text' => $field['tooltip'],
						'tooltip_align' => $field['tooltip_align'] ?? 'right',
					]);
				}
				if (isset($field['formobile'])) {
					echo MW()->mobile_device_switcher();
				}
				echo '<div class="cms_clear"></div></div>';
			}

			if ($field['type'] == 'row_set') {
				foreach ($field['setting'] as $subfield) {
					echo '<div class="mw_flex_field_col">';
					echo '<div class="sublabel">' . $subfield['title'] . '</div>';
					write_field($subfield, $meta, $post_id, $tagid, $tagname, $type, $is_multielement, $fields);
					echo '</div>';
				}
			} elseif ($field['type'] == 'hidden_input') {
				if ($type == 'setting') {
					$content = isset($field['id']) && isset($meta[$field['id']]) ? $meta[$field['id']] : null;
				} else {
					$content = isset($field['id']) && isset($meta['style'][$field['id']]) ? $meta['style'][$field['id']] : null;
				}
				call_user_func_array('field_type_' . $field['type'], [$field, $content, $tagname, $tagid, $post_id, '', $meta]);
			} else {
				write_field($field, $meta, $post_id, $tagid, $tagname, $type, $is_multielement, $meta);
			}

			if (isset($field['desc'])) {
				echo '<span class="mw_description">' . $field['desc'] . '</span>';
			}

			if ($field['type'] != 'hidden_input') {
				echo '</div>';
			}
		} elseif (isset($field['inline'])) { // for inline text editor
			echo '<input type="hidden" name="' . getTagName($tagname, $field['id']) . '" value="%%get%%" />';
		}
	}
}

function getTagName($tagname, $field_id)
{
	if ($tagname && $field_id) {
		return $tagname . '[' . $field_id . ']';
	}

	if ($field_id) {
		return $field_id;
	}

	return $tagname;
}

function write_field($field, $meta, $post_id, $tagid, $tagname, $type = 'setting', $is_multielement = false, $all_meta = [])
{
	if ($type == 'setting') {
		$content = isset($field['id']) && isset($meta[$field['id']]) ? $meta[$field['id']] : null;
	} else {
		$content = isset($field['id']) && isset($meta['style'][$field['id']]) ? $meta['style'][$field['id']] : null;
	}

	$atributes = '';

	// Load field value with custom storage of value
	cms_load_customized_field_value($post_id, $field, $content);

	// show after action
	if (isset($field['show'])) {
		$atributes .= 'data-show="' . $tagid . '_' . $field['show'] . '" ';

		//$val = empty($content)? 0 : $content;
		$val = $content ?? ($field['content'] ?? 0);
		if ($field['type'] == 'slider') {
			if ($val) {
				$val = 1;
			}
		}

		$show_val = $val;
		if ($field['type'] == 'sale_form_select' && isset($val['api'])) {
			$show_val = $val['api'];
		}
		?>
		<style>
			.cms_show_group_<?php echo $tagid . '_' . $field['show']; ?>:not(.cms_show_group_<?php echo $tagid . '_' . $field['show']; ?>_<?php echo $show_val ?>) {
				display: none;
			}
		</style>
		<?php
	}

	$devices = isset($field['formobile']) ? MW()->devices : ['desktop' => ''];

	// info for page builder
	$atributes .= 'data-type="' . $field['type'] . '"';
	if (isset($field['onedit'])) {
		$f_css = $field['onedit']['css'] ?? '';
		$f_action = $field['onedit']['action'] ?? '';
		$f_setting = $field['onedit']['setting'] ?? '';
		$f_class = $field['onedit']['class'] ?? '';
		$f_target = $field['onedit']['target'] ?? '';

		$atributes .= ' data-css="' . $f_css . '" data-action="' . $f_action . '" data-setting="' . $f_setting . '" data-target="' . $f_target . '" data-setname="' . $field['id'] . '" data-class="' . $f_class . '"';
		if ($is_multielement) {
			$atributes .= ' data-multielement="1"';
		}
	}

	foreach ($devices as $device => $val) {
		$container_class = '';
		$container_class .= 'mw_onedit_action';
		if (isset($field['formobile'])) {
			$container_class .= ' ' . $device . '_device_set_container';
		}

		if ($container_class) {
			echo '<div class="' . $container_class . '" ' . $atributes . ' data-device="' . $device . '">';
		}

		if ($device != 'desktop') {
			$content = $type == 'setting' ? $meta[$device][$field['id']] ?? null : $meta['style'][$device][$field['id']] ?? null;
			$field['content'] = '';

			$new_tagname = $tagname . '[' . $device . ']';
			$new_tagid = $tagid . '_' . $device;
		} else {
			$new_tagname = $tagname;
			$new_tagid = $tagid;
		}

		call_user_func_array('field_type_' . $field['type'], [$field, $content, $new_tagname, $new_tagid, $post_id, $all_meta]);

		if ($container_class) {
			echo '</div>';
		}
	}
}

/** Update preloaded meta of a field by a custom loader or use the value from a customized storage location.
 *
 * @param int $post_id ID of post where the value relies to.
 * @param array $field Field definition
 * @param mixed $meta Value that is preloaded. If custom loading should be performed, this value will be updated inplace.
 */
function cms_load_customized_field_value($post_id, $field, &$meta)
{
	if (isset($field['save']) && isset($field['id']) && !empty($field['id'])) {
		$fieldId = $field['id'];
		if ($field['save'] == 'post' && $post_id) {
			$post = get_post($post_id);
			$meta = $post->$fieldId;
		} elseif ($field['save'] == 'option') {
			$meta = get_option($fieldId, true);
		} elseif ($field['save'] == 'post_meta' && $post_id) {
			$meta = get_post_meta($post_id, $fieldId, true);
		} elseif ($field['save'] == 'term' && $post_id) {
			$term = get_term($post_id);
			$meta = $term->$fieldId;
		}
	}
	// Custom value loader
	if (isset($field['loadhook']) && is_callable($field['loadhook'])) {
		$fnc = $field['loadhook'];
		$fnc($post_id, $field, $meta);
	}
}

// COMMENTS
// **********************************************************************

function approve_comments()
{
	$comment_status = 'approve';
	$comment_id = intval($_POST['comment_approve_id']);
	wp_set_comment_status($comment_id, $comment_status);
	echo true;
	die();
}

if (is_admin()) {
	add_action('wp_ajax_approve_comments', 'approve_comments');
}

/**
 * @param string $url
 * @param int|string $perpage
 * @param string $scheme Currently not working - @see https://developers.facebook.com/support/bugs/1759174414250782/
 * @param int|string $width
 * @return string
 */
function cms_facebook_comments($url, $perpage = '10', $scheme = 'light', $width = '550')
{
	return '<div class="fb-comments" data-href="' . $url . '" data-numposts="' . $perpage . '" data-colorscheme="' . $scheme . '" data-width="' . $width . '"></div>';
}

// License
// **********************************************************************

function mwSendStatisticsInit()
{
	$s_status = get_transient('mw_send_statistics');
	if (!$s_status) {
		$licence = get_option('web_option_license');
		mwSendStatistics($licence['license']);
	}
}

function mwSendStatistics($licence)
{
	$url = LICENSE_SERVER . 'hosting-statistics';

	$statistics = mw_get_statistics();
	$statistics['serial_number'] = $licence;
	$statistics['url'] = get_home_url();

	$response = wp_remote_post($url, [
		'method' => 'POST',
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.1',
		'blocking' => true,
		'headers' => [],
		'body' => $statistics,
	]);

	$return = json_decode(wp_remote_retrieve_body($response));

	if (is_wp_error($response) || !isset($return->status) || (isset($return->error))) {
		set_transient('mw_send_statistics', '1', 24 * HOUR_IN_SECONDS);
	} else {
		set_transient('mw_send_statistics', '1', 30 * 24 * HOUR_IN_SECONDS);
	}
}

function mw_get_statistics()
{
	global $wpdb;

	$statistics = [];

	$cur_theme = wp_get_theme();
	$users_count = count_users();

	$statistics['mioweb_version'] = $cur_theme->version;
	$statistics['wp_version'] = get_bloginfo('version');
	$statistics['php_version'] = urlencode((string) phpversion());
	$statistics['sql_version'] = method_exists($wpdb, 'db_server_info') ? $wpdb->db_server_info() : null;
	$statistics['wp_language'] = get_bloginfo('language');

	// blog
	// **************************************************************

	$statistics['blog_posts_num'] = wp_count_posts('post')->publish;

	// web
	// **************************************************************

	$statistics['page_num'] = wp_count_posts('page')->publish;

	$installed_web = get_option('ve_installed_web');
	if ($installed_web) {
		$statistics['installed_web'] = $installed_web['web_theme'];
	}

	$statistics['se_connect'] = mwApiConnect()->getApi('se')->isConnected() ? 1 : 0;

	$statistics['fapi_connect'] = mwApiConnect()->getApi('fapi')->isConnected() ? 1 : 0;

	$apis = mwApiConnect()->getApis();
	$apiConnections = [];
	foreach ($apis as $api) {
		$apiConnections[$api->getId()] = $api->isConnected();
	}
	$statistics['api_connections'] = $apiConnections;

	// shop
	// **************************************************************

	$created = get_option('mw_eshop_created');
	if ($created) {
		$statistics['shop_created'] = 1;

		$var = new WP_Query([
			'posts_per_page' => -1,
			'post_type' => ['mwproduct'],
			'post_status' => 'publish', // explicitly setting post_status helps memory usage
			'fields' => 'ids',
		]);
		$statistics['products_num'] = $var->post_count; //wp_count_posts( 'mwproduct' )->publish;

		$var = new WP_Query([
			'posts_per_page' => -1,
			'post_type' => ['mwvariant'],
			'post_status' => 'publish', // explicitly setting post_status helps memory usage
			'fields' => 'ids',
		]);
		$statistics['variants_num'] = $var->post_count;
		$statistics['orders_num'] = class_exists(OrderRepository::class) ? OrderRepository::countBy([]) : null;
		$statistics['ordered_sum'] = 0;
	} else {
		$statistics['shop_created'] = 0;
	}

	// member
	// **************************************************************

	$statistics['members_num'] = 0;
	$statistics['member_users'] = 0;

	if (MW()->is_module_active('member')) {
		$members = mwMemberModule()->getMemberSections();
		$statistics['members_num'] = count($members);

		if (isset($users_count['avail_roles']) && isset($users_count['avail_roles']['member'])) {
			$statistics['member_users'] = $users_count['avail_roles']['member'];
		}
	}

	// campaigns
	// **************************************************************

	$campaigns = get_option('campaign_basic');
	$statistics['campaigns_num'] = $campaigns ? count($campaigns['campaigns']) : 0;

	// funnels
	// **************************************************************

	if (MW()->is_module_active('funnels')) {
		$funnelsNum = $wpdb->get_var('SELECT count(*) FROM ' . $wpdb->prefix . 'mw_funnels');
		$statistics['funnels_num'] = (int) $funnelsNum ?? 0;
	}

	// onboarding
	// **************************************************************

	$onboard = get_option('mw_tutorials');

	if ($onboard && isset($onboard['game'])) {
		$statistics['onboard_step'] = $onboard['game']['step'];
		$statistics['onboard_template'] = $onboard['game']['template'];
		$statistics['onboard_start'] = date('Y-m-d H:i:s', $onboard['game']['start'] / 1000);
		$statistics['onboard_time'] = intval($onboard['game']['time']);
	}

	// plugins
	// **************************************************************

	$installed_plugins = get_option('active_plugins') ?: [];
	$statistics['plugins_num'] = count($installed_plugins);
	if (!function_exists('get_plugins')) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if (function_exists('get_plugins')) {
		$all_plugins = get_plugins();
		//print_r($all_plugins);
		$install_names = [];
		foreach ($installed_plugins as $plug) {
			//print_r($plug);
			if (isset($all_plugins[$plug])) {
				$install_names[] = $all_plugins[$plug]['Name'];
			}
		}
		$statistics['installed_plugins'] = implode(',', $install_names);
	}

	return $statistics;
}

#[Deprecated(reason: 'Use \mwPage::getPages() instead', replacement: 'mwPage::getPages(%parametersList%)')]
function mw_get_pages($args = [])
{
	global $wpdb;

	$post_status = $args['post_status'] ?? ['publish'];
	$post_type = $args['post_type'] ?? 'page';
	$parent = $args['parent'] ?? -1;
	$hierarchical = $args['hierarchical'] ?? true;

	if ($parent > 0) {
		$hierarchical = false;
	}

	// Make sure we have a valid post status.
	if (!is_array($post_status)) {
		$post_status = explode(',', $post_status);
	}
	if (array_diff($post_status, get_post_stati())) {
		return false;
	}

	if (count($post_status) === 1) {
		$where_post_type = $wpdb->prepare('post_type = %s AND post_status = %s', $post_type, reset($post_status));
	} else {
		$post_status = implode("', '", str_replace(' ', '', $post_status));
		$where_post_type = $wpdb->prepare("post_type = %s AND post_status IN ('$post_status')", $post_type);
	}

	$where = '';
	if (is_array($parent)) {
		$post_parent__in = implode(',', array_map('absint', (array) $parent));
		if (!empty($post_parent__in)) {
			$where .= " AND post_parent IN ($post_parent__in)";
		}
	} elseif ($parent >= 0) {
		$where .= $wpdb->prepare(' AND post_parent = %d ', $parent);
	}

	$query = "SELECT ID, post_parent, post_title, post_status, post_name, post_type FROM $wpdb->posts WHERE ($where_post_type) $where";

	if (isset($args['exclude']) && (bool) $args['exclude']) {
		$exclude = is_array($args['exclude']) ? implode(',', $args['exclude']) : $args['exclude'];
		$query .= ' AND ID NOT IN (' . $exclude . ')';
	}

	$query .= ' ORDER BY post_title ASC';

	$pages = $wpdb->get_results($query);

	// Sanitize before caching so it'll only get done once.
	$num_pages = count($pages);
	for ($i = 0; $i < $num_pages; $i++) {
		$pages[$i] = sanitize_post($pages[$i], 'raw');
	}

	if ($hierarchical) {
		$pages = get_page_children(0, $pages);
	}

	// Convert to WP_Post instances.
	$pages = array_map('get_post', $pages);

	//$pages = get_pages();
	return $pages;
}

function mw_get_page_by_url(Url $url): ?WP_Post
{
	return get_page_by_path($url->getPath()) ?: null;
}

/**
 * Converts UTF8MB4 string containing unicode characters to UTF8MB3 to store in database
 * WARNING: function removes most of the emojis, but not all of them. If you need 100% string in utf8mb3, you should use
 * e.g. json_encode or some other approach
 *
 * @param string $string
 */
function mw_encode_emojis($string)
{
	// Convert emojis to html entities
	$string = wp_encode_emoji($string);

	// Remove rest of unicode
	$symbols = "\x{1F100}-\x{1F1FF}" // Enclosed Alphanumeric Supplement
			. "\x{1F300}-\x{1F5FF}" // Miscellaneous Symbols and Pictographs
			. "\x{1F600}-\x{1F64F}" //Emoticons
			. "\x{1F680}-\x{1F6FF}" // Transport And Map Symbols
			. "\x{1F900}-\x{1F9FF}" // Supplemental Symbols and Pictographs
			. "\x{2600}-\x{26FF}" // Miscellaneous Symbols
			. "\x{2700}-\x{27BF}"; // Dingbats

	// Option 1
	return preg_replace('/[' . $symbols . ']+/u', '', $string);

	// Option 2
//	return preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{1F000}-\x{1FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{1F000}-\x{1FEFF}]?/u', '', $string);
}
