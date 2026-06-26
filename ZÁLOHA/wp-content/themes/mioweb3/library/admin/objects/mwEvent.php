<?php

class mwEvent extends mwPost
{

	private $_startDate;

	private $_startDateTime;

	private $_endDate;

	private $_place;

	private $_link;

	public function __construct($post)
	{
		parent::__construct($post);

		$startDate = get_post_meta($post->ID, 'mw_event_date_start', true);
		$eventMeta = get_post_meta($post->ID, 've_event', true);

		$this->_startDate = $this->formatStartDate((int) $startDate);
		$this->_startDateTime = $startDate;
		$this->_endDate = isset($eventMeta['date_end']) && $eventMeta['date_end'] ? date('j.n. Y', strtotime($eventMeta['date_end'])) : null;
		$this->_place = $eventMeta['where'] ?? '';
		$this->_link = $eventMeta['event_page'] ?? '';
	}

	public function formatStartDate(?int $date): string
	{
		$formated = date('H:i', $date) == '00:00' ? date('j.n. Y', $date) : date('j.n. Y H:i', $date);

		return $formated;
	}

	public function getStartDate(): string
	{
		return $this->_startDate;
	}

	public function getStartDateTime(): string
	{
		return $this->_startDateTime;
	}

	public function getEndDate(): string
	{
		return $this->_endDate;
	}

	public function getFromToDate(): string
	{
		$date = $this->_startDate;
		if ($this->_endDate) {
			$date .= ' - ' . $this->_endDate;
		}

		return $date;
	}

	public function getPlace(): string
	{
		return $this->_place;
	}

	public function getLink(): array
	{
		return $this->_link;
	}

	public function getLinkTarget(): bool
	{
		return isset($this->_link['target']) ? true : false;
	}

	public static function getAll($args = [], $paged = true, $category = '', $show = ''): array
	{
		if ($show == '') {
			$orderby = 'DESC';
		} else {
			$orderby = $show == '<' ? 'DESC' : 'ASC';
		}

		$default_args = [
			'post_type' => MW_EVENT_SLUG,
			'post_status' => ['publish'],
			'posts_per_page' => -1,
			'meta_key' => 'mw_event_date_start',
			'orderby' => [
				'mw_event_date_start' => $orderby,
			],
		];

		if ($category) {
			$default_args['tax_query'] = [
				[
					'taxonomy' => MW_EVENT_CAT_SLUG,
					'field' => 'ID',
					'terms' => $category,
				],
			];
		}

		if ($show) {
			$default_args['meta_query'] = [
				'mw_event_date_start' => [
					'key' => 'mw_event_date_start',
					'value' => current_time('timestamp'),
					'compare' => $show,
				],
			];
		}

		$query_args = array_merge($default_args, $args);

		return self::getQuery($query_args, $paged);
	}

	public static function registerEventPostTypes()
	{
		$catlabels = [
			'name' => __('Kategorie', 'cms'),
			'singular_name' => __('Kategorie', 'cms'),
			'search_items' => __('Hledat kategorii', 'cms'),
			'all_items' => __('Kategorie', 'cms'),
			'edit_item' => __('Upravit kategorii', 'cms'),
			'update_item' => __('Upravit kategorii', 'cms'),
			'add_new_item' => __('Přidat kategorii', 'cms'),
			'new_item_name' => __('Přidat kategorii', 'cms'),
			'menu_name' => __('Kategorie', 'cms'),
		];

		$wp_args = [
			'labels' => $catlabels,
			'rewrite' => ['slug' => MW_EVENT_CAT_SLUG],
			'hierarchical' => true,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'query_var' => true,
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_Taxonomy',
			'class' => 'mwTerm',
			'hierarchical' => true,
			'admin_hierarchical' => false,
			'allow_add' => true,
			'public' => false,
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Kategorie akcí', 'cms'),
				'singular' => __('Kategorie', 'cms'),
				'category_of' => __('Akcí', 'cms'),
				'add_item' => __('Přidat kategorii', 'cms'),
				'edit_item' => __('Upravit kategorii', 'cms'),
				'new_item' => __('Nová kategorie', 'cms'),
				'empty' => __('Nebyla nalezena žádná kategorie', 'cms'),
				'notfound' => __('Kategorie s tímto ID nebyla nalezena', 'cms'),
			],

		];

		mwSetting()->registerTaxonomy(MW_EVENT_CAT_SLUG, MW_EVENT_SLUG, $mw_args, $wp_args);


		$labels = [
			'name' => __('Kalendář akci', 'cms'),
			'singular_name' => __('Akce', 'cms'),
			'menu_name' => __('Kalendář akcí', 'cms'),
			'name_admin_bar' => __('Kalendář akcí', 'cms'),
			'add_new' => __('Přidat akci', 'cms'),
			'add_new_item' => __('Přidat novou událost', 'cms'),
			'new_item' => __('Nová událost', 'cms'),
			'edit_item' => __('Upravit událost', 'cms'),
			'view_item' => __('Zobrazit událost', 'cms'),
			'all_items' => __('Události', 'cms'),
			'search_items' => __('Hledat událost', 'cms'),
			'parent_item_colon' => ':',
			'not_found' => __('Událost nenalezena', 'cms'),
			'not_found_in_trash' => __('Událost nenalezena', 'cms'),
		];

		$wp_args = [
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'rewrite' => ['slug' => 'mw_events'],
			'capability_type' => 'post',
			'has_archive' => false,
			'supports' => ['title', 'thumbnail'],
			'taxonomies' => [MW_EVENT_CAT_SLUG],
		];

		$mw_args = [
			'service_class' => 'mwSettingObjectService_Event',
			'class' => 'mwEvent',
			'allow_add' => true,
			'supports' => ['thumbnail','visibility','search','duplicate'],
			'taxonomies' => [MW_EVENT_CAT_SLUG],
			'filter' => [
				[
					'id' => 'show',
					'content' => '',
					'title' => __('Zobrazit', 'cms'),
					'items' => [
						'>' => __('Neproběhlé akce', 'cms'),
						'<' => __('Proběhlé akce', 'cms'),
						'' => __('Všechny akce', 'cms'),
					],
				],
			],
			'bulk_actions' => [
				[
					'action' => 'delete',
				],
			],
			'labels' => [
				'title' => __('Kalendář akcí', 'cms'),
				'add_item' => __('Přidat akci', 'cms'),
				'edit_item' => __('Upravit akci', 'cms'),
				'new_item' => __('Nová akce', 'cms'),
				'delete' => __('Smazat akci', 'cms'),
				'empty' => __('Nebyla nalezena žádná akce', 'cms'),
				'notfound' => __('Akce s tímto ID nebyla nalezena', 'cms'),
			],

		];

		mwSetting()->registerPostType(MW_EVENT_SLUG, $mw_args, $wp_args);
	}

}

class mwSettingObjectService_Event extends mwSettingObjectService
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
					'content' => __('Začátek', 'cms'),
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

		$show = $filter['show'] ?? '';
		$search = $filter['s'] ?? '';

		$query = mwEvent::getAll([
			'post_status' => ['publish','draft','private'],
			'posts_per_page' => $perPage,
			'paged' => $page,
			's' => $search,
		], true, '', $show);

		$args['pagination'] = [
			'pages' => $query['pages'],
			'count' => $query['count'],
		];

		foreach ($query['items'] as $event) {
			$args['rows'][] = [
				'bulk_id' => $event->getId(),
				'cols' => [
					[
						'content' => '<a class="mw_link" href="' . $this->object()->getEditUrl($event->getId()) . '">' . $event->getName() . '</a>',
					],
					[
						'content' => $event->getStartDate(),
					],
					[
						'content' => mwAdminComponents::checker($event->isVisible(), [
							'attrs' => 'data-id="' . $event->getId() . '" data-objectid="' . $this->object()->getId() . '"',
						], 'mw_checker_visibility'),
						'align' => 'center',
					],
					[
						'content' => mwSetting::printSettingActions(['edit', 'duplicate', 'delete'], $event->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

}
