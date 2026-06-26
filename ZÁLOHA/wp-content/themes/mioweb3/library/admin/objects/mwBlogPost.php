<?php

class mwBlogPost extends mwPost
{

	protected $_visitsCount = null;

	public function getVisitsCount()
	{
		if ($this->_visitsCount === null) {
			$visits = MWDB()->getPostMeta($this->getId(), 'mioweb_post_visited', true);
			$this->_visitsCount = $visits ? : 0;
		}

		return $this->_visitsCount;
	}

	public function getArticleDate(bool $only_modify = false): string
	{
		$date = $this->getDateCreated('date');
		$modif_date = $this->getDateUpdated('date');

		$content = $date;
		if ($date != $modif_date && isset(mwBlog()->setting['show']['updated'])) {
			if ($only_modify) {
				$content = $modif_date;
			} else {
				$content .= ' (' . __('Aktualizováno', 'cms_blog') . ': ' . $modif_date . ')';
			}
		}

		return $content;
	}

	public static function getAll($args = [], $paged = true): array
	{
		$default_args = [
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		];

		$query_args = array_merge($default_args, $args);

		return self::getQuery($query_args, $paged);
	}

	public function printInfoLabels()
	{
		$content = '';
		if ($this->isPasswordProtected()) {
			$content .= mwAdminComponents::textLabel([
				'text' => __('Chráněno heslem', 'cms'),
				'predefined_color' => 'red',
			]);
		}
		if ($this->isDraft()) {
			$content .= mwAdminComponents::textLabel([
				'text' => __('Koncept', 'cms'),
				'predefined_color' => 'yellow',
			]);
		}
		if ($this->isPrivate()) {
			$content .= mwAdminComponents::textLabel([
				'text' => __('Soukromý', 'cms'),
				'predefined_color' => 'yellow',
			]);
		}
		if ($this->isFuture()) {
			$content .= mwAdminComponents::textLabel([
				'text' => __('Plánováno', 'cms'),
				'predefined_color' => 'green',
			]);
		}
		if ($this->isSticky()) {
			$content .= mwAdminComponents::textLabel([
				'text' => __('Sticky', 'cms'),
			]);
		}
		if ($content) {
			return '<div class="mw_table_list_info_labels">' . $content . '</div>';
		}

		return '';
	}

	public function getEditButton()
	{
		global $vePage;

		return $vePage->display->itemEditButton($this->getPostType(), $this->getId());
	}

	public function getSettingActions()
	{
		return $this->isTrashed() ? ['restore', 'delete'] : ['wp_edit', 'show_page', 'delete'];
	}

	public static function registerBlogObjects()
	{
		$mwArgs = [
			'service_class' => 'mwSettingObjectService_BlogPost',
			'class' => 'mwBlogPost',
			'object_type' => 'post',
			'allow_add' => true,
			'public' => true,
			'supports' => ['thumbnail','visibility','search','trash','comments','password_protected'],
			'taxonomies' => ['category','post_tag'],
			'filter' => [
				[
					'id' => 'post_category',
					'content' => '',
					'title' => __('Kategorie', 'cms'),
					'object_items' => 'category',
				],
				[
					'id' => 'post_author',
					'object_id' => 'users',
					'title' => __('Od autora', 'cms'),
					'type' => 'hidden',
				],
			],
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Články', 'cms'),
				'add_item' => __('Přidat článek', 'cms'),
				'edit_item' => __('Upravit článek', 'cms'),
				'new_item' => __('Nový článek', 'cms'),
				'delete' => __('Smazat článek', 'cms'),
				'empty' => __('Nebyl nalezen žádný článek', 'cms'),
				'notfound' => __('Článek s tímto ID nebyl nalezen', 'cms'),
				'trash_title' => __('Koš článků', 'cms'),
			],

		];

		mwSetting()->registerObject('post', $mwArgs);

		$mwArgs = [
			'service_class' => 'mwSettingObjectService_Taxonomy',
			'class' => 'mwTerm',
			'object_type' => 'term',
			'allow_add' => true,
			'hierarchical' => true,
			'sortable' => true,
			'public' => true,
			'supports' => ['thumbnail','visualeditor'],
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Kategorie', 'cms'),
				'singular' => __('Kategorie', 'cms'),
				'add_item' => __('Přidat kategorii', 'cms'),
				'edit_item' => __('Upravit kategorii', 'cms'),
				'new_item' => __('Nová kategorie', 'cms'),
				'empty' => __('Nebyla nalezena žádná kategorie', 'cms'),
				'notfound' => __('Kategorie nebyla nalezena', 'cms'),
			],
		];

		mwSetting()->registerObject('category', $mwArgs);

		$mwArgs = [
			'service_class' => 'mwSettingObjectService_Taxonomy',
			'class' => 'mwTerm',
			'object_type' => 'term',
			'allow_add' => true,
			'supports' => ['thumbnail','visualeditor'],
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Štítky', 'cms'),
				'singular' => __('Štítek', 'cms'),
				'add_item' => __('Přidat štítek', 'cms'),
				'edit_item' => __('Upravit štítek', 'cms'),
				'new_item' => __('Nový štítek', 'cms'),
				'empty' => __('Nebyly nalezeny žádné štítky', 'cms'),
				'notfound' => __('Štítek nebyl nalezen', 'cms'),
			],
		];

		mwSetting()->registerObject('post_tag', $mwArgs);
	}

}

class mwSettingObjectService_BlogPost extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Název', 'cms'),
				],
				[
					'content' => __('Autor', 'cms'),
				],
				[
					'content' => __('Návštěv', 'cms'),
				],
				[
					'content' => __('Komentářů', 'cms'),
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$filter = $this->object()->getSavedListFilter();

		$query_args = [
			'post_status' => $trash ? 'trash' : 'any',
			'posts_per_page' => $perPage,
			'paged' => $page,
			's' => $filter['s'] ?? '',
		];

		if (isset($filter['post_author']) && $filter['post_author']) {
			$query_args['author'] = $filter['post_author'];
		}

		if (isset($filter['post_category']) && $filter['post_category']) {
			$query_args['tax_query'] = [
				[
					'taxonomy' => 'category',
					'field' => 'term_id',
					'terms' => $filter['post_category'],
				],
			];
		}

		$query = mwBlogPost::getAll($query_args, true);

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		/** @var mwBlogPost $item */
		foreach ($query['items'] as $item) {
			$name = $trash ? $item->getName() : '<a class="mw_link" target="_blank" href="' . $this->object()->getEditWPUrl($item->getId()) . '">' . $item->getName() . '</a>';
			$name .= $item->printInfoLabels();

			$author = $item->getAuthor();
			$authorName = $author !== null ? $author->getName() : '';

			$args['rows'][] = [
				'bulk_id' => $item->getId(),
				'cols' => [
					[
						'content' => $name,
					],
					[
						'content' => $trash
							? $authorName
							: (
								$author !== null
									? '<a class="mw_link" href="' . $this->object()->getUrl('post_author=' . $item->getAuthorId()) . '">' . $authorName . '</a>'
									: ''
							),
					],
					[
						'content' => mwAdminComponents::icon(['icon' => 'eye', 'text' => number_format($item->getVisitsCount(), 0, '.', ' ')], 'mw_table_statistics'),
					],
					[
						'content' => mwAdminComponents::iconLink([
							'icon' => 'message-square',
							'text' => number_format($item->getCommentCount(), 0, '.', ' '),
							'target' => '_blank',
							'link' => mwSetting()->getObject('comments')->getUrl() . '&source=' . $item->getId(),
						], 'mw_table_statistics'),
					],
					[
						'content' => mwSetting::printSettingActions($item->getSettingActions(), $item->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function afterSaveActions($itemId, $tosave)
	{
		// stick post
		if (isset($tosave['stick_post'])) {
			stick_post($itemId);
		} else {
			unstick_post($itemId);
		}
		// post format
		if (isset($tosave['post_format'])) {
			set_post_format($itemId, $tosave['post_format']);
		}
	}

	public function getInfoList($item): string
	{
		$content = '<div class="mw_setting_sidebar_info">';

		$content .= '<div class="mw_setting_sidebar_info_row">';
		$content .= '<span>' . __('Publikováno', 'cms') . ':</span>';
		$content .= '<span>';
		$content .= '<a href="#" class="mw_setting_open_publish_date mw_setting_action_link">' . $item->getDateCreated() . '</a>';
		$content .= '</span>';
		$content .= '</div>';

		$datetime = [
			'date' => date('d.m.Y', $item->getDateCreatedTimestamp()),
			'hour' => date('G', $item->getDateCreatedTimestamp()),
			'minute' => date('i', $item->getDateCreatedTimestamp()),
		];

		$content .= '<div class="mw_setting_publish_date_container">';
		$content .= '<div class="mw_setting_publish_date_box mw_rounded mw_shadow_b">';
		$content .= mwAdminComponents::dateTimeInput([
			'name' => 'post_date',
		], $datetime);
		$content .= '</div>';
		$content .= '</div>';

		$content .= '<div class="mw_setting_sidebar_info_row">';
		$content .= '<span>' . __('Upraveno', 'cms') . ':</span>';
		$content .= '<span>' . $item->getDateUpdated() . '</span>';
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	public function printTitle($item = null): string
	{
		$args = [
			'text' => $this->object()->getLabel('title'),
			'onright' => mwAdminComponents::button([
				'button_text' => $this->object()->getLabel('add_item'),
				'link' => $this->object()->getAddWPUrl(),
				'attrs' => 'target="_blank"',
				'icon' => 'plus',
			]),
		];

		$args['description'] = $this->object()->printFilterTags();

		return mwAdminComponents::title($args, 'h2');
	}

}
