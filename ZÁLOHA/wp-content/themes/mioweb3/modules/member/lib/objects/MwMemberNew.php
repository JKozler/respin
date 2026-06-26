<?php

class MwMemberNew extends mwPost
{
/*
	public static function getAll($num = 20, $page = 1, $search = '', $private = false): array
	{
		$post_status = ['publish'];
		if ($private) {
			$post_status[] = 'draft';
			$post_status[] = 'private';
		}

		$query_args = [
			'post_type' => MW_MEMBER_NEWS_SLUG,
			'post_status' => $post_status,
			'posts_per_page' => $num,
			'paged' => $page,
		];

		if ($search) {
			$query_args['s'] = $search;
		}

		return self::getQuery($query_args, true);
	}
*/
	public static function getAll($args = [], $num = 20, $page = 1, $paged = true): array
	{
		$default_args = [
			'post_type' => MW_MEMBER_NEWS_SLUG,
			'post_status' => ['publish'],
			'posts_per_page' => $num,
			'paged' => $page,
		];

		$query_args = array_merge($default_args, $args);

		return self::getQuery($query_args, $paged);
	}

	public function getNewContent($wordCount = 0): string
	{
		return $wordCount ? wp_trim_words($this->getContent(), $wordCount) : $this->getContent();
	}

	public static function registerMemberNews()
	{
			$wp_args = [
				'public' => false,
				'publicly_queryable' => true,
				'show_ui' => false,
				'show_in_menu' => false,
				'query_var' => true,
				'rewrite' => [ 'slug' => MW_MEMBER_NEWS_SLUG ],
				'capability_type' => 'page',
				'has_archive' => false,
				'hierarchical' => false,
				'supports' => [ 'title','editor' ],
			];

			$mw_args = [
				'service_class' => 'mwSettingObjectService_MemberNew',
				'class' => 'MwMemberNew',
				'allow_add' => true,
				'public' => false,
				'supports' => ['visibility','search','duplicate'],
				'bulk_actions' => [
					[
						'action' => 'delete',
					],
				],
				'labels' => [
					'title' => __('Členské novinky', 'cms'),
					'add_item' => __('Přidat členskou novinku', 'cms'),
					'edit_item' => __('Upravit členskou novinku', 'cms'),
					'new_item' => __('Nová členská novinka', 'cms'),
					'delete' => __('Smazat členskou novinku', 'cms'),
					'empty' => __('Nebyly nalezeny žádná členské novinky', 'cms'),
					'notfound' => __('Novinka nebyla nalezena', 'cms'),
				],

			];

			mwSetting()->registerPostType(MW_MEMBER_NEWS_SLUG, $mw_args, $wp_args);
	}

}

class mwSettingObjectService_MemberNew extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Novinka', 'cms'),
				],
				[
					'content' => __('Datum', 'cms'),
				],
				[
					'content' => __('Viditelnost', 'cms'),
					'align' => 'center',
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();

		//$show = $filter['show']?? '';

		$query = MwMemberNew::getAll([
			'post_status' => ['publish', 'private', 'draft'],
			's' => $filter['s'] ?? '',
		], $perPage, $page, true);

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		foreach ($query['items'] as $item) {
			$args['rows'][] = [
				'bulk_id' => $item->getId(),
				'cols' => [
					[
						'content' => '<a class="mw_link" href="' . $this->object()->getEditUrl($item->getId()) . '">' . $item->getName() . '</a>',
					],
					[
						'content' => $item->getDateCreated(),
					],
					[
						'content' => mwAdminComponents::checker($item->isVisible(), [
							'attrs' => 'data-id="' . $item->getId() . '" data-objectid="' . $this->object()->getId() . '"',
						], 'mw_checker_visibility'),
						'align' => 'center',
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

}
