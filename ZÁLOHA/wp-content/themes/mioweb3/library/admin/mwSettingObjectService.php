<?php

use Mioweb\Admin\mwObjectExport;
use Mioweb\Database\BaseEntity;
use Mioweb\Database\Entity;
use Mioweb\VisualEditor\Lib\Image;
use Nette\NotImplementedException;

class mwSettingObjectService
{

	private mwSettingObject $_object;

	public function __construct(mwSettingObject $object)
	{
		$this->_object = $object;
	}

	public function object(): mwSettingObject
	{
		return $this->_object;
	}

	public function getListArgs($page, $per_page = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		return [];
	}

	public function getHierarchicalListArgs(): array
	{
		return [];
	}

	public function getItemUrl($id): string
	{
		return get_permalink($id);
	}

	public function printTitle($item = null): string
	{
		if (isset($_GET['edit'])) {
			$args = [
				'text' => $this->object()->getLabel('edit_item'),
			];
			if (isset($_GET['added']) && $this->_object->isAllowAdd()) {
				$args['onright'] = $this->titleButton($this->object()->getLabel('add_more'));
			}
		} elseif (isset($_GET['trash'])) {
			$args = [
				'text' => $this->object()->getLabel('trash_title'),
			];
		} elseif (isset($_GET['copy'])) {
			$args = [
				'text' => $this->object()->getLabel('add_copy'),
			];
		} elseif (isset($_GET['add'])) {
			$args = [
				'text' => $this->object()->getLabel('add_item'),
			];
		} elseif (isset($_GET['archives'])) {
			$args = [
				'text' => $this->object()->getLabel('archives'),
			];
		} else {
			$args = [
				'text' => $this->object()->getLabel('title') . mwSetting()->getHelpLink($this->object()->getId()),
			];
			if ($this->_object->isAllowAdd()) {
				$args['onright'] = $this->titleButton($this->object()->getLabel('add_item'));
			}
			$args['description'] = $this->_object->printFilterTags();
		}

		return mwAdminComponents::title($args, 'h2');
	}

	public function titleButton($text): string
	{
		return $this->_object->isFastAdd() ? mwAdminComponents::button([
				'button_text' => $text,
				'attrs' => 'data-object="' . $this->object()->getId() . '" data-return="redirect"',
				'icon' => 'plus',
		], 'mw_setting_fast_add') : mwAdminComponents::button([
				'button_text' => $text,
				'link' => $this->object()->getAddUrl(),
				'icon' => 'plus',
		]);
	}

	public function printListContent(): string
	{
		$page = $_GET['mwpage'] ?? 1;

		$content = '<div class="mw_setting_list_container">';
		$content .= '<form id="mw_setting_list_form" method="post" action="">';

		$content .= $this->printFilter();
		$content .= $this->printBulkActions();

		$content .= '<div class="mw_setting_list_table_container">';

		if ($this->object()->isHierarchical()) {
			$listArgs = $this->getHierarchicalListArgs();
			$content .= mwAdminComponents::hierarchicalList($listArgs);
		} else {
			$trash = isset($_GET['trash']);
			$listArgs = $this->getListArgs($page, MW_DEFAULT_PER_PAGE, $trash);
			$content .= $this->printList($listArgs, $page);
		}

		$content .= '</div>';

		$content .= '<input type="hidden" name="object_id" value="' . $this->object()->getId() . '"/>';

		$content .= '</form>';
		$content .= '</div>';

		return $content;
	}

	public function printList($listArgs, $page = 1): string
	{
		$listArgs['bulk'] = $this->object()->hasBulkActions();

		$content = mwAdminComponents::table($listArgs, 'mw_table_list2');

		// pagination
		if (isset($listArgs['pagination']) && $listArgs['pagination']['pages'] > 1) {
			$content .= $this->printPagination($listArgs['pagination'], $page);
		}

		return $content;
	}

	function printPagination($pagination, $page = 1)
	{
		$url = $this->object()->getUrl();

		$nextPage = $page < $pagination['pages'] ? $page + 1 : $page;
		$prevPage = $page > 1 ? $page - 1 : 1;

		$content = '<div class="mw_pagination">';
		if ($page == 1) {
			$content .= mwAdminComponents::icon([
				'icon' => 'chevron-left',
			]);
		} else {
			$content .= mwAdminComponents::iconLink([
				'icon' => 'chevron-left',
				'link' => $url . '&mwpage=' . ($page - 1),
				'attrs' => 'data-page="' . ($page - 1) . '"',
			]);
		}
		$content .= '<select name="page" autocomplete="off">';
		for ($i = 1; $i <= $pagination['pages']; $i++) {
			$content .= '<option data-url="' . $url . '&mwpage=' . $i . '" value="' . $i . '" ' . ($i == $page ? 'selected="selected"' : '') . '>' . $i . '</option>';
		}
		$content .= '</select>';
		$content .= '<span class="mw_pagination_from">/ ' . $pagination['pages'] . '</span>';
		if ($page == $pagination['pages']) {
			$content .= mwAdminComponents::icon([
				'icon' => 'chevron-right',
			]);
		} else {
			$content .= mwAdminComponents::iconLink([
				'icon' => 'chevron-right',
				'link' => $url . '&mwpage=' . ($page + 1),
				'attrs' => 'data-page="' . ($page + 1) . '"',
			]);
		}
		if (isset($pagination['count']) && $pagination['count']) {
			$content .= '<div class="mw_pagination_count">' . __('Celkem', 'cms') . ': ' . $pagination['count'] . '</div>';
		}
		$content .= '</div>';

		return $content;
	}

	public function printFilter(): string
	{
		$content = '';

		if ($this->_object->isSupported('search') || $this->_object->isSupported('trash')
			|| $this->_object->isAllowFilter() || $this->_object->isSupported('archives')
		) {
			$content .= '<div class="mw_setting_filter">';

			$content .= '<div class="mw_setting_filter_selects">';
			if (isset($_GET['trash'])) {
				$content .= '<input type="hidden" name="trash" value="1"/>';
				$content .= mwAdminComponents::iconLink([
					'text' => __('Zpět na seznam', 'cms'),
					'icon' => 'arrow-left',
					'link' => $this->object()->getUrl(),
				], 'mw_setting_filter_item');
				$content .= mwAdminComponents::link([
					'text' => __('Vysypat koš', 'cms'),
					'attrs' => 'data-objectid="' . $this->object()->getId() . '"',
				], 'mw_setting_empty_trash mw_setting_filter_item');
			} elseif (isset($_GET['archives'])) {
				{
					$content .= '<input type="hidden" name="archives" value="1"/>';
					$content .= mwAdminComponents::iconLink([
						'text' => __('Zpět na seznam', 'cms'),
						'icon' => 'arrow-left',
						'link' => $this->object()->getUrl(),
					], 'mw_setting_filter_item');

				}
			} else {
				if ($this->_object->isAllowFilter()) {
					$content .= $this->printFilterItems();
				}
				if ($this->_object->isSupported('trash')) {
					$content .= mwAdminComponents::link([
						'text' => __('Koš', 'cms') . '<span class="count"> (' . $this->getInTrashCount() . ')</span>',
						'link' => $this->_object->getTrashUrl(),
					], 'mw_setting_filter_item');
				}
			}

			if ($this->_object->isSupported('archives') && !isset($_GET['archives'])) {
				$content .= mwAdminComponents::link([
					'text' => __('Archiv', 'mwshop'),
					'link' => $this->_object->getArchiveUrl(),
					'attrs' => 'data-objectid="' . $this->_object->getId() . '" data-title="' . $this->_object->getLabel('archives_item') . '"',
				], 'mw_setting_filter_item');
			}

			$content .= '</div>';

			$content .= '<div class="mw_setting_filter_actions">';

			if ($this->_object->isSupported('export')) {
				$content .= mwAdminComponents::iconLink([
					'icon' => 'download',
					'text' => __('Export', 'cms'),
					'attrs' => 'data-objectid="' . $this->_object->getId() . '" data-title="' . $this->_object->getLabel('export_title') . '"',
				], 'mw_setting_filter_item mw_setting_open_export');
			}

			if ($this->_object->isSupported('search')) {
				$savedFilter = $this->_object->getSavedListFilter();
				$searchVal = $savedFilter['s'] ?? '';
				$content .= '<div class="mw_setting_filter_search">';
				$content .= mwAdminComponents::clickSearch([
					'name' => 'filter[s]',
				], $searchVal);
				$content .= '</div>';
			}
			$content .= '</div>';

			$content .= '</div>';
		}

		return $content;
	}

	public function printFilterItems()
	{
		$content = '';
		$savedFilter = $this->_object->getSavedListFilter();

		foreach ($this->_object->getFilterSetting() as $filter) {
			if (isset($filter['type']) && $filter['type'] == 'hidden') {
				$filterVal = $savedFilter[$filter['id']] ?? '';
				$content .= '<input type="hidden" name="filter[' . $filter['id'] . ']" value="' . $filterVal . '"/>';
			} else {
				if (isset($filter['object_items'])) {
					$filter['items'] = [
						'' => __('Vše', 'cms'),
					];
					$object = mwSetting()->getObject($filter['object_items']);
					$getAllArgs = array_key_exists('get_all_args', $filter) ? $filter['get_all_args'] : [
						'post_status' => 'any',
					];
					$items = $object->service()->getAll($getAllArgs, false);

					foreach ($items as $item) {
						$filter['items'][$item->getId()] = $item->getName();
					}
				}
				$filter['name'] = 'filter[' . $filter['id'] . ']';
				$filterVal = $savedFilter[$filter['id']] ?? $filter['content'];
				$content .= mwAdminComponents::linkSelect($filter, $filterVal, 'mw_setting_filter_item');
			}
		}

		return $content;
	}

	public function printBulkActions(): string
	{
		$content = '';

		if ($this->_object->hasBulkActions()) {
			$content .= '<div class="mw_setting_bulk_container cms_nodisp">';
			$content .= __('Vybrané položky', 'cms') . ' (<span class="mw_setting_bulk_count"></span>):';

			foreach ($this->_object->getBulkActions() as $bulk) {
				$title = $bulk['title'] ?? '';
				if (!$title && $bulk['action'] === 'delete') {
					$title = __('Smazat', 'cms');
				}
				$content .= mwAdminComponents::link([
					'text' => $title,
					'attrs' => 'data-action="' . $bulk['action'] . '"',
				], 'mw_setting_action_link');
			}

			$content .= '</div>';
		}

		return $content;
	}

	public function printEditPage($itemId)
	{
		$item = $this->getItem($itemId);

		echo $this->printTitle($item);

		if ($item) {
			if ($this->isItemEditNotAllowed($item)) {
				$this->itemIsNotEditedMessage($item);
			} elseif ($this->object()->isSupported('trash') && $item->isTrashed()) {
				$this->object()->message404(__('Nemůžete upravovat tuto položku, protože byla přesunuta do koše. Nejdříve ji prosím obnovte a potom to zkuste znovu.', 'cms'));
			} else {
				echo '<form action="" class="mw_setting_form">';
				$this->printForm($item);
				$this->printSaveBar();
				echo '</form>';
			}
		} else {
			$this->object()->message404();
		}
	}

	public function isItemEditNotAllowed($item): bool
	{
		return false;
	}

	public function itemIsNotEditedMessage($item)
	{
		// echo content;
	}

	public function printForm($item, $add = false)
	{
		$itemId = $item ? $item->getId() : '';


		$meta_set = $this->object()->getSetting();

		if (count($meta_set) > 1) {
				$tabs = [];
				foreach ($meta_set as $set) {
				$tabs[] = [
				'id' => $set['id'],
				'name' => $set['title'],
				];
				}
				echo '<div class="mw_setting_tabs_container mw_onedit_action" data-type="tabs">';
				echo mwAdminComponents::tabs([
					'tabs' => $tabs,
					'group' => 'mw_object_setting_tab',
				], '', 'mw_setting_tabs');
				echo '</div>';
		}

		echo '<div class="mw_setting_object_detail_content">';
		echo '<div class="mw_setting_object_detail_form">';

		$i = 1;
		foreach ($meta_set as $set) {
			echo '<div id="mw_object_setting_tab_' . $set['id'] . '" class="mw_tab mw_object_setting_tab_container ' . ($i == 1 ? 'active' : '') . '">';
			$this->printSet($itemId, $set);
			$i++;
			echo '</div>';
		}

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="object_id" value="' . $this->object()->getId() . '"/>';
		if (!$add) {
			echo '<input type="hidden" name="item_id" value="' . $itemId . '"/>';
		}

		echo '</div>';

		echo $this->printFormSidebar($item, $add);

		echo '</div>';
	}

	public function printSet($itemId, $set)
	{
		// save by custom static function
		if (isset($set['load_function'])) {
			$exploded = explode('::', $set['load_function']);
			$class = $exploded[0];
			$function = $exploded[1];
			$meta = $class::$function($itemId);
		} else {
			// seve to post meta
			$meta = $itemId ? $this->getMeta($itemId, $set['id']) : [];
		}
		write_meta($set['fields'], $meta, $set['id'], $set['id'], $itemId);
	}

	public function printFastAddForm()
	{
		$meta_set = $this->object()->getFastSetting();

		echo '<div class="mw_fast_add_object_form">';

		write_meta($meta_set['fields'], [], $meta_set['id'] ?? '', $meta_set['id'] ?? '', '');

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="object_id" value="' . $this->object()->getId() . '"/>';

		echo '</div>';
	}

	public function printFastCopyForm($itemId)
	{
//		$item = $this->getItem($itemId);
		echo '<div class="mw_fast_object_form">';

		$meta_set = $this->object()->getFastSetting();
		write_meta($meta_set['fields'], [], $meta_set['id'] ?? '', $meta_set['id'] ?? '', $itemId);

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="object_id" value="' . $this->object()->getId() . '"/>';
		echo '<input type="hidden" name="item_id" value="' . $itemId . '"/>';

		echo '</div>';
	}

	function printFormSidebar($item, $add = false, $inPopup = false): string
	{
		$content = '<div class="mw_setting_object_detail_sidebar">';

		if ($this->object()->isSupported('visibility') || !$add) {
			$content .= '<div class="mw_setting_sidebar_box">';

			if ($this->object()->isSupported('visibility')) {
				$visibility = $this->getVisibility($item);
				$options = [
					'publish' => [
						'text' => __('Veřejné', 'cms'),
						'status' => 'ok',
						'icon' => 'check',
					],
					'private' => [
						'text' => __('Soukromé', 'cms'),
						'status' => 'x',
						'icon' => 'eye-off',
					],
				];

				if ($this->object()->isSupported('password_protected')) {
					$options['password_protected'] = [
						'text' => __('Chráněné heslem', 'cms'),
						'status' => 'processing',
						'icon' => 'lock',
					];
				}

				$content .= mwAdminComponents::statusSelect([
					'title' => __('Viditelnost', 'cms'),
					'show_list' => true,
					'input' => 'visibility',
					'list' => $options,
				], $visibility, 'mw_setting_sidebar_visibility');

				if ($this->object()->isSupported('password_protected')) {
					$content .= '<div class="mw_setting_password_protected_container ' . ($visibility != 'password_protected' ? 'cms_nodisp' : '') . '">';
					$content .= mwAdminComponents::input([
						'name' => 'post_password',
						'placeholder' => __('Zadejte heslo', 'cms'),
					], $item->getPassword(), 'mw_setting_password_protected_input');
					$content .= '</div>';
				}
			}

			if (!$add) {
				$content .= $this->getInfoList($item);

				if (!$inPopup) {
					$content .= $this->getDetailActionList($item);
				}
			}
			$content .= '</div>';
		}

		if ($this->object()->isSupported('thumbnail')) {
			$content .= $this->printThumbWidget($item);
		}

		if ($this->object()->hasTaxonomies()) {
			$taxonomies = $this->object()->getTaxonomies();
			foreach ($taxonomies as $taxonomy) {
				$content .= $this->printTaxWidget($taxonomy, $item);
			}
		}

		if ($this->object()->isSupported('comments')) {
			$content .= $this->printCommentsWidget($item);
		}

		$content .= '</div>';

		return $content;
	}

	public function printTaxWidget($taxonomy, $item)
	{
		$tax = mwSetting()->getObject($taxonomy);
		$content = '<div class="mw_setting_sidebar_box">';
		$content .= mwAdminComponents::title([
			'text' => $tax->getLabel('title'),
		]);

		$terms = $tax->service()->getAll();

		$itemInTerms = $item ? $item->getTermIds($taxonomy) : [];

		if ($tax->isHierarchical()) {
			$terms = mwTerm::sortHierarchical($terms);
			$content .= $this->printHierarchicalTaxList($terms, $itemInTerms, $tax->getId(), 'mw_setting_sidebar_taxonomy_select mw_setting_sidebar_taxonomy_select_' . $tax->getId() . ' ' . (!count($terms) ? 'cms_nodisp' : ''));
			$content .= mwAdminComponents::iconLink([
				'icon' => 'plus',
				'text' => $tax->getLabel('add_item'),
				'attrs' => 'data-object="' . $tax->getId() . '" data-title="' . $tax->getLabel('add_item') . '" data-return="list" data-name="taxonomy[' . $tax->getId() . '][]" data-target=".mw_setting_sidebar_taxonomy_select_' . $tax->getId() . '"',
			], 'mw_setting_action_link mw_setting_fast_add');
		} else {
			$content .= $this->printTaxList($terms, $itemInTerms, $tax, 'mw_setting_sidebar_taxonomy_select mw_setting_sidebar_taxonomy_select_' . $tax->getId() . ' ' . (!count($terms) ? 'cms_nodisp' : ''));
		}
		$content .= '</div>';

		return $content;
	}

	public function printHierarchicalTaxList($hTerms, $itemInTerms, $taxId, $class = '')
	{
		$content = '<ul class="' . $class . '">';
		foreach ($hTerms as $term) {
			$val = in_array($term['item']->getId(), $itemInTerms) ? 1 : 0;
			$content .= '<li><label>';
			$content .= mwAdminComponents::checkbox([
				'type' => 'checkbox',
				'name' => 'taxonomy[' . $taxId . '][]',
				'value' => $term['item']->getId(),
			], $val);
			$content .= $term['item']->getName();
			$content .= '</label>';
			if (count($term['childs'])) {
				$content .= $this->printHierarchicalTaxList($term['childs'], $itemInTerms, $taxId);
			}
			$content .= '</li>';
		}
		$content .= '</ul>';

		return $content;
	}

	public function printTaxList($terms, $itemInTerms, $tax, $class = '')
	{
		$content = '<ul class="' . $class . '">';
		foreach ($terms as $term) {
			$val = in_array($term->getId(), $itemInTerms) ? 1 : 0;
			$content .= '<li><label>';
			$content .= mwAdminComponents::checkbox([
				'type' => 'checkbox',
				'name' => 'taxonomy[' . $tax->getId() . '][]',
				'value' => $term->getName(),
			], $val);
			$content .= $term->getName();
			$content .= '</label>';
			$content .= '</li>';
		}
		$content .= '</ul>';

		$content .= mwAdminComponents::iconLink([
			'icon' => 'plus',
			'text' => $tax->getLabel('add_item'),
			'attrs' => 'data-object="' . $tax->getId() . '" data-title="' . $tax->getLabel('add_item') . '" data-return="list" data-name="taxonomy[' . $tax->getId() . '][]" data-target=".mw_setting_sidebar_taxonomy_select_' . $tax->getId() . '"',
		], 'mw_setting_action_link mw_setting_fast_add');

		return $content;
	}

	public function printThumbWidget($item): string
	{
		$content = '<div class="mw_setting_sidebar_box">';
		$content .= mwAdminComponents::title([
			'text' => __('Náhledový obrázek', 'cms'),
		]);
		$content .= '<div class="mw_onedit_action" data-type="image_url">';
		$mwThumbnail = $this->getThumbnail($item);
		$content .= mwAdminComponents::imageUploader([
			'name' => 'thumbnail',
		], $mwThumbnail);
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	public function printCommentsWidget($item): string
	{
		$val = $item ? ($item->isCommentsOpen() ? 1 : 0) : (MWDB()->getOption('default_comment_status') == 'open' ? 1 : 0);

		$content = '<div class="mw_setting_sidebar_box">';
		$content .= mwAdminComponents::title([
			'text' => __('Komentáře', 'cms'),
		]);
		$content .= mwAdminComponents::switch([
			'name' => 'comment_status',
			'switch_label' => __('Povolit komentáře', 'cms'),
		], $val);
		$content .= '<input type="hidden" name="set_comment_status" value="1" />';
		$content .= '</div>';

		return $content;
	}

	public function getInfoList($item): string
	{
		$content = '<div class="mw_setting_sidebar_info">';
		$content .= '<div class="mw_setting_sidebar_info_row">';
		$content .= '<span>' . __('Vytvořeno', 'cms') . ':</span>';
		$content .= '<span>' . $item->getDateCreated() . '</span>';
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	public function getDetailActionList($item): string
	{
		$content = '<ul class="mw_setting_detail_action_list">';

		if ($this->_object->isPublic()) {
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

		$content .= '<li>';
		$content .= mwAdminComponents::iconLink([
			'icon' => 'trash-2',
			'text' => __('Smazat', 'cms'),
			'attrs' => 'data-id="' . $item->getId() . '" data-objectid="' . $this->object()->getId() . '"',
		], 'mw_setting_action_link mw_setting_detail_delete_item');
		$content .= '</li>';

		$content .= '</ul>';

		return $content;
	}

	public function printSaveBar()
	{
		echo mwSetting::saveBar(__('Uložit', 'cms'), 'mw_setting_save_object_but');
	}
	public function printAddBar()
	{
		echo mwSetting::saveBar(__('Vytvořit', 'cms'), 'mw_setting_add_but');
	}

	public function getMenu($module, $pageType)
	{
		$sets = $this->object()->getSettingForCategory();

		$menu[0] = [
			'submenu' => [],
		];

		foreach ($sets as $set) {
			if ($this->showMenuItem($set, $module, $pageType)) {
				$menu[0]['submenu'][] = $this->getMenuItem($set);
			}
		}

		$i = 1;
		foreach ($this->object()->getSettingCategories() as $cat) {
			$sets = $this->object()->getSettingForCategory($cat['id']);
			$menu[$i] = [
				'title' => $cat['title'],
				'submenu' => [],
			];

			foreach ($sets as $set) {
				if ($this->showMenuItem($set, $module, $pageType)) {
					$menu[$i]['submenu'][] = $this->getMenuItem($set);
				}
			}

			$i++;
		}

		return $menu;
	}

	public function showMenuItem($set, $module, $pageType)
	{
		// exclude setting form setted modules
		if (isset($set['exclude_modules']) && in_array($module, $set['exclude_modules'])) {
			return false;
		}

		// exclude setting form setted page types
		if (isset($set['exclude_page_type']) && in_array($pageType, $set['exclude_page_type'])) {
			return false;
		}

		// @TODO replace with exclude_page_type
		// exclude setting for home page of blog
		return !is_home() || $set['id'] === 'post_setting' || isset($_GET['window_editor']);
	}

	public function getMenuItem($set)
	{
		$arrayset = [
			'name' => $set['title'],
			'attrs' => [
				'data-id' => $this->object()->getId(),
				'data-setid' => $set['id'],
				'data-help' => mwHelp::getHelpLink($set['id']),
			],
		];

		if (isset($set['action'])) {
			$arrayset['class'] = $set['action'];
		} else {
			if (isset($set['inpanel'])) {
				$arrayset['inpanel'] = true;
				$arrayset['attrs']['data-target'] = $set['inpanel']['target'];
				$arrayset['attrs']['data-reload'] = $set['inpanel']['reload'];
			} else {
				$arrayset['inpanel'] = false;
			}
		}

		return $arrayset;
	}

	public function getActions($itemId)
	{
		$actions = [];

		if ($this->object()->isSupported('duplicate')) {
			$actions['duplicate'] = [
				'icon' => 'copy',
				'class' => 'mw_duplicate_page',
				'title' => __('Duplikovat', 'cms'),
			];
		}
		if ($this->object()->isSupported('export')) {
			$actions['export'] = [
				'icon' => 'log-out',
				'link' => add_query_arg('export_mioweb_template', $itemId),
				'target' => '_blank',
				'title' => __('Exportovat', 'cms'),
			];
		}
		if ($this->object()->getObjectType() === 'page') {
			$actions['sethome'] = [
				'icon' => 'home',
				'link' => add_query_arg('ve_set_home', $itemId),
				'title' => __('Nastavit jako domovskou', 'cms'),
			];
		}
		if ($this->object()->getObjectType() === 'post') {
			$actions['edit'] = [
				'icon' => 'edit-2',
				'link' => $this->object()->getEditWPUrl($itemId),
				'target' => '_blank',
				'title' => __('Upravit článek', 'cms'),
			];
		}
		$actions['delete'] = [
			'icon' => 'trash-2',
			'class' => 've_delete_page',
			'title' => __('Smazat', 'cms'),
		];

		return $actions;
	}

	public function getMeta($itemId, $setId)
	{
		$meta = get_post_meta($itemId, $setId, true);
		$meta = mwBackCompatibility::meta_set($meta, $setId);

		return $meta;
	}

	public function add($tosave, $fast = false): ?int
	{
		$newItem = [
			'post_type' => $this->object()->getId(),
			'post_status' => 'publish',
			'comment_status' => 'open',
			'post_content' => $tosave['post_content'] ?? '',
		];

		if (isset($tosave['post_name'])) {
			$newItem['post_name'] = $tosave['post_name'];
		}

		$itemId = wp_insert_post($newItem, true);

		if (is_wp_error($itemId)) {
			mwMessages()->error($itemId->get_error_message());
		} elseif ($itemId) {
			// set template
			if (isset($tosave['template'])) {
				$this->saveTemplate($itemId, $tosave['template']);
			}

			// save setting
			$this->save($itemId, $tosave);

			return $itemId;
		}

		return null;
	}

	public function save($itemId, $tosave)
	{
		$tosave = $this->beforeSaveActions($itemId, $tosave);

		// update post
		if (isset($tosave['post_title'])) {
			$updatePost = [
				'ID' => $itemId,
				'post_title' => $tosave['post_title'] ?? '',
				'post_parent' => $tosave['post_parent'] ?? 0,
				'menu_order' => $tosave['menu_order'] ?? 0,
				'post_excerpt' => $tosave['post_excerpt'] ?? '',
			];

			if (isset($tosave['post_content'])) {
				$updatePost['post_content'] = $tosave['post_content'];
				if ($this->object()->isSupported('visualeditor')) {
					MWDB()->setLayer($itemId, $this->object()->getId(), $tosave['post_content']);
				}
			}

			if (isset($tosave['post_date'])) {
				$date = $tosave['post_date']['date'] . ' ' . $tosave['post_date']['hour'] . ':' . $tosave['post_date']['minute'];
				$mysqlDate = date('Y-m-d H:i:s', strtotime($date));
				$updatePost['post_date'] = $mysqlDate;
				$updatePost['post_date_gmt'] = get_gmt_from_date($mysqlDate);
			}

			if (isset($tosave['post_author'])) {
				$updatePost['post_author'] = $tosave['post_author'];
			}

			if (isset($tosave['post_name'])) {
				$updatePost['post_name'] = $tosave['post_name'];
			}

			if (isset($tosave['post_password'])) {
				$updatePost['post_password'] = $tosave['post_password'];
			}

			// comments status
			if (isset($tosave['set_comment_status'])) {
				$updatePost['comment_status'] = isset($tosave['comment_status']) ? 'open' : 'closed';
			}

			wp_update_post($updatePost);
		}

		$tosave = $this->beforeSaveMeta($itemId, $tosave);

		// save meta fields
		foreach ($this->object()->getSetting() as $set) {
			if (isset($tosave[$set['id']])) {
				$this->saveObjectSetting($itemId, $set, $tosave[$set['id']] ?? []);
			}
		}

		// set visibility
		if (isset($tosave['visibility'])) {
			if ($tosave['visibility'] != 'password_protected' || $tosave['post_password']) {
				$visibility = $tosave['visibility'] == 'password_protected' ? 'publish' : $tosave['visibility'];
				$this->setVisibility($itemId, $visibility);
			}
		}

		$taxonomies = $this->object()->getTaxonomies();
		// save taxonomy
			foreach ($taxonomies as $taxonomy) {
			wp_set_post_terms($itemId, $tosave['taxonomy'][$taxonomy] ?? [], $taxonomy);
			}


		// save thumbnail
		if ($this->object()->isSupported('thumbnail')) {
			if (isset($tosave['thumbnail']) && intval($tosave['thumbnail']['imageid']) > 0) {
				$this->setThumbnail($itemId, intval($tosave['thumbnail']['imageid']));
			} elseif (isset($tosave['thumbnail'])) {
				$this->deleteThumbnail($itemId);
			}
		}

		$this->afterSaveActions($itemId, $tosave);

		mwSetting::saveUsed($tosave);
	}

	function saveTemplate($itemId, $template)
	{
		$temp = explode('/', $template);
		if (!isset(MW()->p_templates[$temp[0]]) || !file_exists(MW()->get_template_dir($temp[0]) . MW()->p_templates[$temp[0]]['path'] . $temp[1] . '/config.php')) {
			$temp[0] = 'page';
			$temp[1] = '1';
		}
		require(MW()->get_template_dir($temp[0]) . MW()->p_templates[$temp[0]]['path'] . $temp[1] . '/config.php');
		if (!empty($config['setting'])) {
			foreach ($config['setting'] as $key => $val) {
				$this->saveMeta($itemId, $key, $val);
			}
		}
		if (isset($config['config'])) {
			add_post_meta($itemId, 've_page_config', $config['config']);
		}
		add_post_meta($itemId, 've_page_template', ['type' => $this->object()->getId(), 'directory' => $template]);

		// save layer
		$newlayer = $config['layer'] ?? '';

		MWDB()->updatePost(['ID' => $itemId, 'post_content' => $newlayer]);
		MWDB()->setLayer($itemId, $this->object()->getId(), $newlayer);
	}

	public function saveObjectSetting($itemId, $set, $tosave)
	{
		foreach ($set['fields'] as $field) {
			$tosave = $this->object()->checkSaveHooks($tosave, $field, $itemId);
		}

		// Save sets as separate meta fields.

		// save by custom static function
		if (isset($set['save_function'])) {
			$exploded = explode('::', $set['save_function']);
			$class = $exploded[0];
			$function = $exploded[1];
			$class::$function($itemId, $tosave);
		} else {
			// seve to post meta
			$this->saveMeta($itemId, $set['id'], $tosave);
		}
	}

	public function saveMeta($itemId, $setId, $tosave)
	{
		MWDB()->setPostMeta($itemId, $setId, $tosave);
	}

	public function afterSaveActions($itemId, $tosave)
	{
		// for special save actions in extended classes
	}

	public function beforeSaveActions($itemId, $tosave): array
	{
		// for special save actions in extended classes
		return $tosave;
	}

	public function beforeSaveMeta($itemId, $tosave): array
	{
		// for special save actions in extended classes
		return $tosave;
	}

	// defined in specific object service
	public function exportForm(): string
	{
		return '';
	}
	// defined in specific object service

	/** @param mixed[] $args */
	public function createExport(string $format, array $args): mwObjectExport
	{
		throw new NotImplementedException();
	}

	public function fastAddReturn($item, $type, $name = ''): string
	{
		// for return html after fast add action
		$content = '';

		if ($type === 'list') {
			$content .= '<li>';
			$content .= mwAdminComponents::checkbox([
				'type' => 'checkbox',
				'name' => str_replace('%id%', $item->getId(), $name),
				'value' => $item->getId(),
				'label' => $item->getName(),
			], true);
			$content .= '</li>';
		} elseif ($type === 'select') {
			$content .= '<option value="' . $item->getId() . '" data-url="' . $this->object()->getEditUrl($item->getId()) . '">' . $item->getName() . '</option>';
		} elseif ($type === 'text_label') {
			$text = mwAdminComponents::input([
				'name' => 'taxonomy[' . $item->getTaxonomy() . '][]',
				'type' => 'hidden',
			], $item->getName());
			$text .= $item->getName();
			$text .= mwAdminComponents::iconLink([
				'icon' => 'x',
				'attrs' => 'data-itemid="' . $item->getId() . '"',
			], '');
			$content = mwAdminComponents::textLabel([
				'text' => $text,
				'color' => $item->getColor(),
				'size' => '',
			]);
		}

		return $content;
	}

	public function checkData($tosave, $itemId = 0, $fast = false, bool $add = false): bool
	{
		if (isset($tosave['visibility']) && $tosave['visibility'] == 'password_protected') {
			if (!$tosave['post_password']) {
				mwMessages()->error(__('Vyplňte heslo', 'cms'));

				return false;
			}
			if (isset($tosave['stick_post'])) {
				mwMessages()->error(__('Připnutý článek nemůže být chráněn heslem', 'cms'));

				return false;
			}
		}

		return true;
	}

	public function checkDataSet($tosave, $set, $setId = '', $itemId = 0, bool $add = false): bool
	{
		return true;
	}

	public function delete($id, $force_delete = false)
	{
		wp_delete_post($id, $force_delete);
	}

	public function restore($id): bool
	{
		return (bool) wp_untrash_post($id);
	}



	public function emptyTrash()
	{
		$args = [
			'posts_per_page' => -1,
			'post_status' => 'trash',
		];

		$trash = get_posts($args);

		foreach ($trash as $post) {
			$this->delete($post->ID, true);
		}
	}

	public function getThumbnail($item): Image
	{
		return $item ? $item->getThumbnail() : new Image([]);
	}

	public function checkSlug($slug, $itemId): string
	{
		$item = $this->getItem($itemId);

		return wp_unique_post_slug($slug, $item->getId(), $item->getStatus(), $item->getPostType(), $item->getParentId());
	}

	public function setThumbnail($itemId, $imageId)
	{
		set_post_thumbnail($itemId, $imageId);
	}

	public function deleteThumbnail($itemId)
	{
		delete_post_thumbnail($itemId);
	}

	public function setVisibility($id, $visibility = 'publish')
	{
		wp_update_post([
			'ID' => $id,
			'post_status' => $visibility,
		]);
	}

	public function setDefaultItem($id): bool
	{
		return false;
	}

	public function getVisibility($item): string
	{
		return $item ? $item->getVisibilityStatus() : 'publish';
	}

	public function bulkActions($list, $action)
	{
		if ($action == 'delete') {
			foreach ($list as $id) {
				$this->delete($id);
			}
		} elseif ($action == 'restore') {
			foreach ($list as $id) {
				$this->restore($id);
			}
		} elseif ($action === 'renew' && $this instanceof mwSettingObjectService_Order) {
			foreach ($list as $id) {
				$this->deArchive($id);
			}
		} elseif ($action === 'createArchive' && $this instanceof mwSettingObjectService_Order) {
			foreach ($list as $id) {
				$this->createArchive($id);
			}
		}
	}

	public function getInTrashCount()
	{
		$counts = wp_count_posts($this->object()->getId());

		return $counts->trash;
	}

	public function getTemplate($itemId)
	{
		return get_post_meta($itemId, 've_page_template', true);
	}

	function updateOrder($itemId, $order, $parentId = 0)
	{
		MWDB()->updatePost([
			'ID' => $itemId,
			'post_parent' => $parentId,
		]);
	}



	public function getItem($itemId)
	{
		if ($itemId) {
			$itemClass = $this->object()->getClass();

			if (is_subclass_of($itemClass, BaseEntity::class)) {
				return $itemClass::getRepositoryClassName()::getOneById($itemId);
			}

			return $itemClass::getOneById($itemId);
		}

		return null;
	}

	public function getAll($args = [], $paged = false): array
	{
		return $this->object()->getClass()::getAll($args, $paged);
	}
}
