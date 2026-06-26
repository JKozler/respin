<?php

namespace Mioweb\Member;

use mwAdminComponents;
use mwSetting;

class Dashboard
{

	public static function getDashboard()
	{
		$content = mwAdminComponents::title([
			'text' => __('Celkové statistiky', 'cms_member'),
			//'onright' => mwAdminComponents::rangeSelect([]),
		], 'h2');

		// statistics
		$content .= '<div class="mw_dashboard_statistics">';
		$content .= self::dashboardStatistics();
		$content .= '</div>';

		$object = mwSetting()->getObject('member_sections');

		$content .= mwAdminComponents::title([
			'text' => __('Statistiky členských sekcí', 'cms_member'),
			'onright' => mwAdminComponents::button([
					'button_text' => __('Přidat členskou sekci', 'cms_member'),
					'icon' => 'plus',
					'attrs' => 'data-object="' . $object->getId() . '" data-title="' . __('Přidat členskou sekci', 'cms_member') . '"',
			], 'mw_member_fast_add'),
		], 'h2');

		$listArgs = [
			'rows' => [],
			'empty_content' => $object->getLabel('empty'),
			'head' => [
				[
					'content' => __('Členská sekce', 'cms_member'),
				],
				[
					'content' => __('Členů', 'cms_member'),
				],
				[
					'content' => __('Aktivních', 'cms_member'),
				],
				[
					'content' => __('Akce', 'cms_member'),
					'align' => 'right',
				],
			],
		];

		$members = mwMemberModule()->getMemberSections();

		foreach ($members as $item) {
			$listArgs['rows'][] = [
				'cols' => [
					[
						'content' => '<a class="mw_link" href="' . $object->getEditUrl($item->getId()) . '">' . $item->getName() . '</a>',
					],
					[
						'content' => mwAdminComponents::icon(['icon' => 'users', 'text' => number_format(mwMemberStatistics()->memberUsersCount($item->getId()), 0, '.', ' ')], 'mw_table_statistics'),
					],
					[
						'content' => mwAdminComponents::icon(['icon' => 'user-check', 'text' => number_format(mwMemberStatistics()->activeMemberUsersCount($item->getId()), 0, '.', ' ')], 'mw_table_statistics'),
					],
					[
						'content' => mwSetting::printSettingActions(['edit', 'delete'], $item->getId(), $object),
						'align' => 'right',
					],
				],
			];
		}

		$content .= '<div class="mw_dashboard_list_container">';
		$content .= mwAdminComponents::table($listArgs, 'mw_table_list');
		$content .= '</div>';
		echo $content;
	}

	public static function dashboardStatistics(): string
	{
		$content = mwAdminComponents::statisticsMainBox([
			'value' => number_format(mwMemberStatistics()->memberUsersCount(), 0, '.', ' '),
			'text' => __('Členů', 'cms_member'),
			'icon' => 'users',
		]);
		$content .= mwAdminComponents::statisticsBox([
			'value' => number_format(mwMemberStatistics()->activeMemberUsersCount(), 0, '.', ' '),
			'text' => __('Aktivních členů', 'cms_member'),
			'icon' => 'user-check',
		]);
		$content .= mwAdminComponents::statisticsBox([
			'value' => mwMemberStatistics()->memberSectionsCount(),
			'text' => __('Členských sekcí', 'cms_member'),
			'icon' => 'lock',
		]);

		return $content;
	}

	public static function dashboardStatistics_ajax()
	{
		echo self::dashboardStatistics();
		die();
	}
}
