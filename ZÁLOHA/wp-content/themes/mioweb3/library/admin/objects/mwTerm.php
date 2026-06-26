<?php

use Mioweb\VisualEditor\Lib\Image;

class mwTerm
{

	private $_term;

	private $_name;

	private $_id;

	private $_parent;

	private $_slug;

	private $_count;

	private $_description;

	private $_visibility = null;

	private $_thumbnailUrl;

	private $_taxonomy;

	public function __construct($term)
	{
		$this->_term = $term;
		$this->_id = $term->term_id;
		$this->_name = $term->name ?? __('(bez názvu)', 'cms');
		$this->_parent = $term->parent;
		$this->_slug = $term->slug;
		$this->_count = $term->count;
		$this->_description = $term->description ?? '';
		$this->_taxonomy = $term->taxonomy;
	}

	public function getTerm(): WP_Term
	{
		return $this->_term;
	}

	public function getId(): int
	{
		return $this->_id;
	}
	public function getTaxonomy(): string
	{
		return $this->_taxonomy;
	}
	public function getObjectId(): string
	{
		return $this->_taxonomy;
	}
	public function getName(): string
	{
		return $this->_name;
	}
	public function getUrl(): string
	{
		return get_term_link($this->_id, $this->_taxonomy);
	}

	public function getCount(): string
	{
		return $this->_count;
	}
	public function getParentId(): int
	{
		return $this->_parent;
	}
	public function getSlug(): string
	{
		return $this->_slug;
	}
	public function getDescription(): string
	{
		return $this->_description;
	}
	public function getThumbnail(): Image
	{
		$thumbnail = $this->getMeta('mw_thumbnail');
		$thumbnail = $thumbnail ?: [];

		return new Image($thumbnail);
	}
	public function getOrder(): string
	{
		return $this->getMeta('mw_order');
	}
	public function isVisible(): int
	{
		return $this->getVisibilityStatus() !== 'private';
	}

	public function getVisibilityStatus()
	{
		if ($this->_visibility === null) {
			$this->_visibility = $this->getMeta('mw_visibility');
		}

		return $this->_visibility === 'private' ? 'private' : 'publish';
	}

	public function getMeta($metaName)
	{
		return MWDB()->getTermMeta($this->_id, $metaName, true);
	}

	public function saveMeta($metaName, $value)
	{
		return MWDB()->updateTermMeta($this->_id, $metaName, $value);
	}

	/**
	 * Get term instance by ID.
	 */
	public static function getOneById(int $termId, string $taxonomy): ?self
	{
		$term = get_term($termId, $taxonomy);
		if ($term) {
			try {
				return static::createNew($term);
			} catch (MwsException $e) {
				mwlog(MWLS_GENERAL, sprintf(__('Nepodařilo se vytvořit instanci termu: %s', 'cms'), $termId, $e->getMessage()), MWLL_ERROR);
			}
		}

		return null;
	}

	public static function createNew(WP_Term $term): self
	{
		return new static($term);
	}

	public static function createArgs(string $id, array $args = [])
	{
		$args['taxonomy'] = $id;
		$args['hide_empty'] ??= false;
		$args['orderby'] = 'mw_order';
		$args['order'] = 'ASC';

		$args['meta_query'] = [
			'relation' => 'OR',
			'mw_order' => [
				'key' => 'mw_order',
				'compare' => 'EXISTS',
			],
			[
				'key' => 'mw_order',
				'compare' => 'NOT EXISTS',
			],
		];

		if (isset($args['published'])) {
			$args['meta_query'] = [
				'relation' => 'AND',
				0 => $args['meta_query'],
				1 => [
					'relation' => 'OR',
					[
						'key' => 'mw_visibility',
						'value' => 'publish',
						'compare' => '=',
					],
					[
						'key' => 'mw_visibility',
						'compare' => 'NOT EXISTS',
					],
				],
			];
		}

		return $args;
	}

	public static function getAll($taxonomy, $args = [], $paged = false)
	{
		$args = self::createArgs($taxonomy, $args);

		$terms = get_terms($args);
		$ret = [];
		if (!is_wp_error($terms)) {
			foreach ($terms as $term) {
				$ret[] = self::createNew($term);
			}
		}

		return $ret;
	}

	public static function getPostTerms($postId, $taxonomy, $args = []): array
	{
		$args = self::createArgs($taxonomy, $args);

		$terms = wp_get_post_terms($postId, $taxonomy, $args);
		get_terms($args);
		$ret = [];
		if (!is_wp_error($terms)) {
			foreach ($terms as $term) {
				$ret[] = self::createNew($term);
			}
		}

		return $ret;
	}

	public static function sortHierarchical($terms, $parent = 0)
	{
		$ret = [];
		foreach ($terms as $key => $term) {
			if ($term->getParentId() == $parent) {
				unset($terms[$key]);
				$childs = self::sortHierarchical($terms, $term->getId());
				$ret[] = [
					'item' => $term,
					'childs' => $childs,
				];
			}
		}

		return $ret;
	}

	public function toPageSelectorItem(): mwPageSelectorItem
	{
		return new mwPageSelectorItem([
			'title' => $this->getName(),
			'url' => $this->getUrl(),
			'id' => $this->getId(),
			'parent' => $this->getParentId(),
			'status' => $this->getVisibilityStatus(),
			'type' => $this->getTaxonomy(),
			'actions' => ['delete'],
		]);
	}

	public static function getHiearchicalOptions($terms, &$options, $pre = '')
	{
		foreach ($terms as $term) {
			$options[] = [
				'name' => $pre . ' ' . $term['item']->getName(),
				'value' => $term['item']->getId(),
			];
			if (count($term['childs'])) {
				self::getHiearchicalOptions($term['childs'], $options, $pre . '—');
			}
		}
	}

	public function getEditButton()
	{
		global $vePage;

		return $vePage->display->itemEditButton($this->getTaxonomy(), $this->getId());
	}

}

class mwSettingObjectService_Taxonomy extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => $this->object()->getLabel('singular'),
				],
				[
					'content' => $this->object()->getLabel('category_of'),
					'align' => 'center',
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$terms = mwTerm::getAll($this->object()->getId(), [
			'name__like' => $filter['s'] ?? '',
		]);
		$actions = $this->object()->isPublic() ? ['edit', 'show_page', 'delete'] : ['edit', 'delete'];

		foreach ($terms as $term) {
			$args['rows'][] = [
				'bulk_id' => $term->getId(),
				'cols' => [
					[
						'content' => mwAdminComponents::link([
							'text' => $term->getName(),
							'link' => $this->object()->getEditUrl($term->getId()),
						], 'mw_link mw_user_list_detail_link'),
					],
					[
						'content' => $term->getCount(),
						'align' => 'center',
					],
					[
						'content' => mwSetting()->printSettingActions($actions, $term->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function getHierarchicalListArgs(): array
	{
		$actions = $this->object()->isPublic() ? ['edit', 'show_page', 'delete'] : ['edit', 'delete'];
		$args = [
			'list' => $this->sortHierarchicalList(mwTerm::getAll($this->object()->getId()), 0, $actions),
			'empty_content' => $this->object()->getLabel('empty'),
			'actions' => $actions,
			'object_id' => $this->object()->getId(),
		];

		return $args;
	}

	public function sortHierarchicalList($terms, $parent = 0, $actions = [])
	{
		$ret = [];
		foreach ($terms as $key => $term) {
			if ($term->getParentId() == $parent) {
				unset($terms[$key]);
				$childs = self::sortHierarchicalList($terms, $term->getId(), $actions);

				$itemActions = $actions;
				if ($term->getId() === 1) {
					$key = array_search('delete', $itemActions);
					if ($key !== false) {
						unset($itemActions[$key]);
					}
				}

				$ret[] = [
					'id' => $term->getId(),
					'text' => mwAdminComponents::link([
						'text' => $term->getName(),
						'link' => $this->object()->getEditUrl($term->getId()),
					], 'mw_link mw_user_list_detail_link'),
					'childs' => $childs,
					'actions' => $itemActions,
				];
			}
		}

		return $ret;
	}

	public function getDetailActionList($item): string
	{
		$content = '<ul class="mw_setting_detail_action_list">';

		if ($this->object()->isPublic()) {
			$content .= '<li>';
			$content .= mwAdminComponents::iconLink([
				'icon' => 'file',
				'text' => __('Zobrazit stránku', 'cms'),
				'target' => '_blank',
				'link' => $this->getItemUrl($item->getId()),
			], 'mw_setting_action_link');
			$content .= '</li>';
		}

		if ($this->object()->isSupported('duplicate')) {
			$content .= '<li>';
			$content .= mwAdminComponents::iconLink([
				'icon' => 'copy',
				'text' => __('Duplikovat', 'cms'),
				'link' => $this->object()->getDuplicateUrl($item->getId()),
			], 'mw_setting_action_link');
			$content .= '</li>';
		}

		if ($item->getId() !== 1) {
			$content .= '<li>';
			$content .= mwAdminComponents::iconLink([
				'icon' => 'trash-2',
				'text' => __('Smazat', 'cms'),
				'attrs' => 'data-id="' . $item->getId() . '" data-objectid="' . $this->object()->getId() . '"',
			], 'mw_setting_action_link mw_setting_detail_delete_item');
			$content .= '</li>';
		}

		$content .= '</ul>';

		return $content;
	}

	public function getInfoList($item): string
	{
		return '';
	}

	public function add($tosave, $fast = false): ?int
	{
		$name = $tosave['term']['name'];
		$slug = sanitize_title(trim(wp_unslash($name)));
		//$slug = $this->createUniqueSlug($sanitized_name);
		$term = MWDB()->insertTerm($name, $this->object()->getId(), [
			'slug' => $slug,
		]);

		if (is_wp_error($term)) {
			if ($term->get_error_code() === 'term_exists') {
				mwMessages()->error(__('Položka se zvoleným názvem již existuje.', 'cms'));
			} else {
				mwMessages()->error($term->get_error_message());
			}
		} elseif (isset($term['term_id'])) {
			$this->save($term['term_id'], $tosave);

			return $term['term_id'];
		}

		return null;
	}

	public function save($itemId, $tosave)
	{
		$tosave = $this->beforeSaveActions($itemId, $tosave);

		$args = [
			'name' => $tosave['term']['name'],
			'description' => $tosave['term']['description'] ?? '',
			'parent' => $tosave['term']['parent'] ?? -1,
		];

		if (isset($tosave['term']['slug'])) {
			$args['slug'] = $tosave['term']['slug'];
		}
		MWDB()->updateTerm($itemId, $this->object()->getId(), $args);

		$tosave = $this->beforeSaveMeta($itemId, $tosave);

		// save meta fields
		foreach ($this->object()->getSetting() as $set) {
			if (isset($tosave[$set['id']])) {
				$this->saveMeta($itemId, $set['id'], $tosave[$set['id']] ?? []);
			}
		}

		if (isset($tosave['visibility'])) {
			$this->setVisibility($itemId, $tosave['visibility']);
		}

		if ($this->object()->isSupported('thumbnail')) {
			if (isset($tosave['thumbnail']) && intval($tosave['thumbnail']['imageid']) > 0) {
				$this->setThumbnail($itemId, $tosave['thumbnail']);
			} else {
				$this->deleteThumbnail($itemId);
			}
		}

		$this->afterSaveActions($itemId, $tosave);
	}

	public function saveMeta($itemId, $setId, $tosave)
	{
		return MWDB()->updateTermMeta($itemId, $setId, $tosave);
	}

	public function getMeta($itemId, $setId)
	{
		return MWDB()->getTermMeta($itemId, $setId, true);
	}

	public function fastAddReturn($item, $type, $name = ''): string
	{
		// for return html after fast add action
		$content = '';

		if ($type == 'list') {
			$content .= '<li><label>';
			$content .= mwAdminComponents::checkbox([
				'type' => 'checkbox',
				'name' => 'taxonomy[' . $this->object()->getId() . '][]',
				'value' => $item->getTaxonomy() === 'post_tag' ? $item->getName() : $item->getId(),
			], true);
			$content .= $item->getName();
			$content .= '</label></li>';
		}

		return $content;
	}

	public function setVisibility($itemId, $visibility = 'publish')
	{
		MWDB()->updateTermMeta($itemId, 'mw_visibility', $visibility);
	}

	public function setThumbnail($itemId, $image)
	{
		MWDB()->updateTermMeta($itemId, 'mw_thumbnail', $image);
	}

	public function deleteThumbnail($itemId)
	{
		MWDB()->deleteTermMeta($itemId, 'mw_thumbnail');
	}

	public function createUniqueSlug($slug, $itemId = null)
	{
		$term = get_term_by('slug', $slug, $this->object()->getId());
		if ($term && $term->term_id !== intval($itemId)) {
			$num = 2;
			do {
				$alt_slug = $slug . "-$num";
				$num++;
				$slug_check = get_term_by('slug', $alt_slug, $this->object()->getId());
			} while ($slug_check);
			$slug = $alt_slug;
		}

		return $slug;
	}

	public function checkSlug($slug, $itemId): string
	{
		return $this->createUniqueSlug($slug, $itemId);
	}

	public function getItem($itemId)
	{
		return $itemId ? mwTerm::getOneById($itemId, $this->object()->getId()) : null;
	}

	public function getItemUrl($id): string
	{
		$link = get_term_link($id, $this->object()->getId());

		return !is_wp_error($link) ? $link : '';
	}

	function updateOrder($itemId, $order, $parentId = 0)
	{
		MWDB()->updateTerm($itemId, $this->object()->getId(), [
			'parent' => $parentId,
		]);

		$terms = mwTerm::getAll($this->object()->getId(), [
			'parent' => $parentId,
		]);

		$i = 0;
		foreach ($terms as $term) {
			$new_order = $i;

			if ($term->getId() == $itemId) {
				$new_order = $order;
				$i--;
			} elseif ($i >= intval($order)) {
				$new_order = $i + 1;
			}

			MWDB()->updateTermMeta($term->getId(), 'mw_order', $new_order);
			$i++;
		}
	}

	public function delete($id, $force_delete = false)
	{
		return MWDB()->deleteTerm($id, $this->object()->getId());
	}

	public function getAll($args = [], $paged = false): array
	{
		return $this->object()->getClass()::getAll($this->object()->getId(), $args, $paged);
	}

}
