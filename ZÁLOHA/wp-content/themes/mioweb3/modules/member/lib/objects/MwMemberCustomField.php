<?php

//define('MW_MEMBER_CUSTOM_FIELDS_SLUG', 'mw_member_user_custom_fields');

class MwMemberCustomField extends mwPost
{

	private $_meta = null;

	public function getType()
	{
		if ($this->_meta === null) {
			$this->_meta = MWDB()->getPostMeta($this->getId(), 'mw_custom_field', true);
		}

		return $this->_meta['type'] ?? 'text';
	}

	public static function getAll($search = ''): array
	{
		$query_args = [
			'post_type' => MW_MEMBER_CUSTOM_FIELDS_SLUG,
			'post_status' => ['publish','draft','private'],
		];

		if ($search) {
			$query_args['s'] = $search;
		}

		return self::getQuery($query_args, true);
	}

	public static function registerMemberCustomFields()
	{
		$wp_args = [
			'labels' => [],
			'public' => false,
			'publicly_queryable' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'rewrite' => [ 'slug' => MW_MEMBER_CUSTOM_FIELDS_SLUG ],
			'capability_type' => 'page',
			'has_archive' => false,
			'hierarchical' => false,
			'supports' => [ 'title' ],
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_MemberCustomField',
			'class' => 'MwMemberCustomField',
			'allow_add' => true,
			'public' => false,
			'labels' => [
				'title' => __('Vlastní pole členů', 'cms_member'),
				'add_item' => __('Přidat vlastní pole', 'cms_member'),
				'edit_item' => __('Upravit vlastní pole', 'cms_member'),
				'new_item' => __('Nové vlastní pole', 'cms_member'),
				'delete' => __('Smazat vlastní pole', 'cms_member'),
				'empty' => __('Nebylo nalezeno žádná vlastní pole', 'cms_member'),
				'notfound' => __('Vlastní pole sekce nebylo nalezeno', 'cms_member'),
			],

		];

		mwSetting()->registerPostType(MW_MEMBER_CUSTOM_FIELDS_SLUG, $mw_args, $wp_args);
	}

}

class mwSettingObjectService_MemberCustomField extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Název', 'cms_member'),
				],
				[
					'content' => __('Akce', 'cms_member'),
					'align' => 'right',
				],
			],
		];

		$query = MwMemberCustomField::getAll();

		foreach ($query['items'] as $item) {
			$args['rows'][] = [
				'cols' => [
					[
						'content' => '<a class="mw_link" href="' . $this->object()->getEditUrl($item->getId()) . '">' . $item->getName() . '</a>',
					],
					[
						'content' => mwSetting::printSettingActions(['edit', 'delete'], $item->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function getInfoList($item): string
	{
		return '';
	}

}
