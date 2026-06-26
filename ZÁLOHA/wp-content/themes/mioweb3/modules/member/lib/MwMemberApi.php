<?php
use Mioweb\Member\Notifications;

class MwMemberApi
{

	function __construct()
	{
		add_action('rest_api_init', function () {
			$namespace = 'mioweb/v1';

			register_rest_route($namespace, '/get-member-sections', [
				'methods' => 'GET',
				'callback' => [$this, 'get_member_sections'],
				'permission_callback' => [$this, 'get_permission'],
			]);
			register_rest_route($namespace, '/stop-membership', [
				'methods' => 'POST',
				'callback' => [$this, 'stopMembership'],
				'permission_callback' => [$this, 'get_permission'],
			]);

			register_rest_route($namespace, '/add-membership', [
				'methods' => 'POST',
				'callback' => [$this, 'add_member'],
				'permission_callback' => [$this, 'get_permission'],
			]);

			register_rest_route($namespace, '/get-conversion-table', [
				'methods' => 'POST',
				'callback' => [$this, 'getConversionTable'],
				'permission_callback' => [$this, 'get_permission'],
			]);
		});
	}

	function get_permission(WP_REST_Request $request)
	{
		$api_key = mwMemberModule()->getApiKey();

		if (!isset($_SERVER['PHP_AUTH_USER']) && (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))) {
			$header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];

			[$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']] = explode(':', base64_decode(substr($header, 6)));
		}

		// Validate
		return isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] === 'mioweb' && $_SERVER['PHP_AUTH_PW'] === $api_key ? true : new WP_Error('access_forbidden', esc_html__('Neoprávněný přístup', 'my-text-domain'), ['status' => 401]);
	}

	function get_member_sections(WP_REST_Request $request)
	{
		//$pokus = $request->get_param( 'pokus' );

		$member_sections = mwMemberModule()->getMemberSections();

		if (count($member_sections) === 0) {
			return null;
		}

		$member_list = [];
		foreach ($member_sections as $memberSection) {
			$levels = [];

			foreach ($memberSection->getLevels() as $level) {
				$levels[] = [
					'id' => $level->getId(),
					'name' => $level->getName(),
				];
			}

			$member_list[] = [
				'id' => $memberSection->getId(),
				'name' => $memberSection->getName(),
				'dashboard' => $memberSection->getUrl(),
				'login' => $memberSection->getLoginUrl(),
				'levels' => $levels,
			];
		}

		$theme_data = wp_get_theme();
		$version = 'Mioweb ' . $theme_data->version . ' (' . get_bloginfo('version') . ')';

		$return = [
			'sections' => $member_list,
			'version' => $version,
		];

		return $return;
	}

	function stopMembership(WP_REST_Request $request)
	{
		$email = $request->get_param('email');
		$member_section = $request->get_param('member_section') ?: null;
		$level = $request->get_param('level') ?: null;

		// missing required data
		if (!$email || $member_section === null) {
			return new WP_Error('missing_data', esc_html__('Chybí povinné data', 'cms_member'), ['status' => 400]);
		}

		if (mwMemberModule()->getMemberSection($member_section) === null) {
			if (mwMemberModule()->memberSectionIdExist($member_section) === false) {
				$sectionConvert = mwMemberModule()->getConvertTableSection($member_section);
				if ($sectionConvert) {
					$member_section = $sectionConvert['id'];
					if (isset($sectionConvert['levels'][$level])) {
						$level = $sectionConvert['levels'][$level];
					}
				}
			}
		}

		$return = $level !== null ? Notifications::stopMembershipByEmail($email, $member_section, $level) : Notifications::stopMembershipByEmail($email, $member_section);

		$theme_data = wp_get_theme();
		$return['version'] = 'Mioweb ' . $theme_data->version . ' (' . get_bloginfo('version') . ')';

		return $return;
	}

	function add_member(WP_REST_Request $request)
	{
		$email = $request->get_param('email');
		$login = $request->get_param('login');

		$client = [
			'user_email' => $email,
			'user_login' => $login ?: $email,
			'first_name' => $request->get_param('name'),
			'last_name' => $request->get_param('surname'),
		];

		$member_section = $request->get_param('member_section');

		$months = $request->get_param('months') ?? [];
		$years = $request->get_param('years');

		if ($years && count($years)) {
			foreach ($years as $year) {
				$months[] = $year;
			}
		}

		$start = null;
		$reg_date = $request->get_param('reg_date');
		$reg_time = $request->get_param('reg_time');
		if ($reg_date || $reg_time) {
			$date = $reg_date ?: date('Y-m-d', current_time('timestamp'));
			$time = $reg_time ?: date('H:i:s', current_time('timestamp'));
			$start = $date . ' ' . $time;
		}

		$params = [
			'send_email' => $request->get_param('send_email') ?? true,
			'start' => $start,
			'days' => $request->get_param('days') ?? 0,
			'levels' => $request->get_param('levels') ?? [],
			'months' => $months,
			'month_num' => $request->get_param('month_num') ?? 0,
			'setexp' => $request->get_param('setexp'),
		];

		// missing required data
		if (empty($email) || $member_section === null) {
			return new WP_Error('missing_data', esc_html__('Chybí povinné data', 'cms_member'), ['status' => 400]);
		}

		if (mwMemberModule()->getMemberSection($member_section) === null) {
			$sectionConvert = mwMemberModule()->getConvertTableSection($member_section);
			if ($sectionConvert) {
				$member_section = $sectionConvert['id'];
				$convertedLevels = [];
				foreach ($params['levels'] as $level) {
					$convertedLevels[] = $sectionConvert['levels'][$level] ?? $level;
				}
				$params['levels'] = $convertedLevels;
			} else {
				return new WP_Error('missing_member_section', esc_html__('Členská sekce nenalezena', 'cms_member'), ['status' => 403]);
			}
		}

		$return = Notifications::addMembership($client, $member_section, $params, false, 'by_api');

		$theme_data = wp_get_theme();
		$return['version'] = 'Mioweb ' . $theme_data->version . ' (' . get_bloginfo('version') . ')';

		return $return;
	}

	function getConversionTable(): array
	{
		$theme_data = wp_get_theme();

		$return = [
			'table' => mwMemberModule()->getConvertTable(),
			'version' => 'Mioweb ' . $theme_data->version . ' (' . get_bloginfo('version') . ')',
		];

		return $return;
	}
}

$mwMemberApi = new MwMemberApi();
