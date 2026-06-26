<?php

use Mioweb\Shop\Order\Order;
use Nette\Http\UrlScript;
use Nette\Utils\Validators;
use Mioweb\Lib\Email;

class mwSettingObject
{

	private $_id;

	private $_class;

	private $_labels;

	private $_setting;

	private $_weditor;

	private $_settingCategories;

	private $_allowAdd;

	private $_supports;

	private $_taxonomies;

	private $_bulkActions;

	private $_service = null;

	private $_serviceClass;

	private $_fastAdd;

	private $_filter;

	private $_fastSetting;

	private $_public;

	private $_detail;

	private $_objectType;

	// used for taxonomy
	private $_hierarchical;

	private $_hierarchicalAdmin;

	public function __construct(string $id, array $args)
	{
		$this->_id = $id;
		$this->_serviceClass = $args['service_class'] ?? 'mwSettingObjectService';
		$this->_class = $args['class'];
		$this->_labels = $args['labels'] ?? [];
		$this->_allowAdd = $args['allow_add'] ?? false;
		$this->_fastAdd = $args['fast_add'] ?? false;
		$this->_weditor = $args['weditor'] ?? false;
		$this->_supports = $args['supports'] ?? [];
		$this->_filter = $args['filter'] ?? [];
		$this->_setting = $args['setting'] ?? [];
		$this->_settingCategories = $args['setting_categories'] ?? [];
		$this->_fastSetting = $args['fast_add_setting'] ?? [];
		$this->_bulkActions = $args['bulk_actions'] ?? [];
		$this->_taxonomies = $args['taxonomies'] ?? [];
		$this->_hierarchical = $args['hierarchical'] ?? false;
		$this->_hierarchicalAdmin = $args['admin_hierarchical'] ?? $this->_hierarchical;
		$this->_public = $args['public'] ?? true;
		$this->_detail = $args['detail'] ?? true;
		$this->_objectType = $args['object_type'] ?? '';
	}

	public function getId(): string
	{
		return $this->_id;
	}

	public function getObjectType(): string
	{
		return $this->_objectType;
	}

	public function getLabel(string $label): string
	{
		$defaultLabels = [
			'title' => '',
			'trash_title' => __('Koš', 'cms'),
			'singular' => '',
			'add_copy' => __('Vytvořit kopii', 'cms'),
			'add_item' => __('Přidat', 'cms'),
			'archives' => __('Archiv', 'mwshop'),
			'category_of' => __('Článků', 'cms'),
			'edit_item' => __('Upravit', 'cms'),
			'new_item' => __('Vytvořit nový', 'cms'),
			'add_more' => __('Přidat další', 'cms'),
			'delete' => __('Smazat', 'cms'),
			'empty' => __('Seznam je prázdný', 'cms'),
			'export_title' => __('Exportovat', 'cms'),
			'notfound' => __('Výsledek vyhledávání je prázdný', 'cms'),
		];

		return $this->_labels[$label] ?? $defaultLabels[$label] ?? '';
	}

	public function getSetting(?string $setId = null): array
	{
		if ($setId) {
			foreach ($this->_setting as $set) {
				if ($set['id'] == $setId) {
					return $set;
				}
			}

			return []; // TODO throw error?
		}

		return $this->_setting;
	}

	public function getSettingCategories(): array
	{
		return $this->_settingCategories;
	}

	public function getSettingForCategory(string $cat = ''): array
	{
		$sets = [];

		foreach ($this->_setting as $set) {
			if (($cat && isset($set['category']) && $set['category'] == $cat) || ($cat == '' && !isset($set['category']))) {
				$sets[] = $set;
			}
		}

		return $sets;
	}

	public function getFastSetting(): array
	{
		return $this->_fastSetting ?: $this->_setting[0];
	}

	public function getFilterSetting(): array
	{
		return $this->_filter;
	}

	public function isAllowFilter(): bool
	{
		return empty($this->_filter) ? false : true;
	}

	public function hasBulkActions(): bool
	{
		return empty($this->_bulkActions) ? false : true;
	}

	public function getBulkActions(): array
	{
		if (isset($_GET['trash'])) {
			return [
				[
					'action' => 'restore',
					'title' => __('Obnovit', 'cms'),
				],
				[
					'action' => 'delete',
					'title' => __('Trvale smazat', 'cms'),
				],
			];
		}

		if (isset($_GET['archives'])) {
			return [
				[
					'action' => 'renew',
					'title' => __('Obnovit', 'cms'),
				],
			];
		}

		return $this->_bulkActions;
	}

	public function isAllowAdd(): bool
	{
		return $this->_allowAdd;
	}

	public function isFastAdd(): bool
	{
		return $this->_fastAdd;
	}

	public function isHierarchical(): bool
	{
		return $this->_hierarchical;
	}

	public function isAdminHierarchical(): bool
	{
		return $this->_hierarchicalAdmin;
	}

	public function isPublic(): bool
	{
		return $this->_public;
	}

	public function hasDetail(): bool
	{
		return $this->_detail;
	}

	public function isSupported($feature): bool
	{
		return in_array($feature, $this->_supports);
	}


	public function hasTaxonomies(): bool
	{
		return !empty($this->_taxonomies);
	}

	public function getTaxonomies(): array
	{
		return $this->_taxonomies;
	}

	public function getUrl($attrs = ''): string
	{
		 $url = get_mw_admin_url($this->_id);
		 if ($attrs) {
			$url .= '&' . $attrs;
		 }

		 return $url;
	}

	public function getAddUrl(): string
	{
		return get_mw_admin_url($this->_id) . '&add=1';
	}

	public function getEditUrl($id): string
	{
		if (!$this->isPublic() && !$this->hasDetail()) {
			return $this->getUrl();
		}

		if ($this->_weditor) {
			return home_url() . '/?window_editor=' . $this->_id . '&id=' . $id;
		}

		return $this->_id == 'post' ? $this->getEditWPUrl($id) : get_mw_admin_url($this->_id) . '&edit=' . $id;
	}

	public function getTrashUrl(): string
	{
		return get_mw_admin_url($this->_id) . '&trash=1';
	}
	public function getArchiveUrl(): string
	{
		return get_mw_admin_url($this->_id) . '&archives=1';
	}

	public function getEditWPUrl($id): string
	{
		return get_edit_post_link($id);
	}

	public function getAddWPUrl()
	{
		return admin_url('post-new.php') . '?post_type=' . $this->_id;
	}

	public function getDuplicateUrl($id): string
	{
		return get_mw_admin_url($this->_id) . '&add=1&copy=' . $id;
	}

	public function getItemUrl($id): string
	{
		return $this->service()->getItemUrl($id);
	}

	public function service(): mwSettingObjectService
	{
		if (!$this->_service) {
			$this->_service = $this->newService();
		}

		return $this->_service;
	}

	public function getClass(): string
	{
		return $this->_class;
	}

	private function newService(): mwSettingObjectService
	{
		return new $this->_serviceClass($this);
	}

	public function checkSaveHooks($tosave, $field, $itemId)
	{
		if (isset($field['setting'])) {
			foreach ($field['setting'] as $settingField) {
				$tosave = $this->checkSaveHooks($tosave, $settingField, $itemId);
			}
		} elseif (isset($field['tabs']) && is_array($field['tabs']) && $field['type'] == 'tabs') {
			foreach ($field['tabs'] as $field_tab) {
				if (isset($field_tab['setting']) && is_array($field_tab['setting'])) {
					foreach ($field_tab['setting'] as $settingKey => $settingField) {
						$this->checkSaveHooks($tosave, $settingField, $itemId);
					}
				}
			}
		}

		if (isset($field['save']) && isset($field['id']) && !empty($field['id'])) {
			$fieldName = $field['id'];
			$fieldValue = @$tosave[$fieldName]; // TODO fix
			$fieldSaved = false;

			if (isset($field['savehook']) && is_callable($field['savehook'])) {
				$func = $field['savehook'];
				$func($itemId, $field, $fieldValue, $fieldSaved, $tosave);
			}

			if ($fieldSaved) {
				// Nothing to do
			} elseif ($field['save'] == 'post') {
				// Save into POST data field.
				$new_post = [
					'ID' => $itemId,
					$fieldName => $fieldValue,
				];

				cms_save_disable();
				wp_update_post($new_post);
				cms_save_enable();
				$fieldSaved = true;
			} elseif ($field['save'] == 'post_meta') {
				update_post_meta($itemId, $fieldName, $fieldValue);
				$fieldSaved = true;
			} else {
				// Coding error.
				echo "Incorrect 'field save value' for field [{$fieldName}], value [{$field['save']}].";
			}
			if ($fieldSaved) {
				unset($tosave[$fieldName]);
			}
		}

		return $tosave;
	}

	public function saveListFilter($filter)
	{
			$filter['object'] = $this->getId();
			$_SESSION['mwObjectListFilter'] = $filter;
	}


	// add $_GET values to filter
	public function addGetToFilter()
	{
		if ($this->isAllowFilter()) {
			$filter = $this->getSavedListFilter();

			foreach ($this->getFilterSetting() as $filterItem) {
				if (isset($_GET[$filterItem['id']])) {
				$filter[$filterItem['id']] = $_GET[$filterItem['id']];
				} elseif (isset($filter[$filterItem['id']])) {
				unset($filter[$filterItem['id']]);
				}
			}

			$this->saveListFilter($filter);
		}
	}

	public function getSavedListFilter(): array
	{
		if (isset($_SESSION['mwObjectListFilter']) && isset($_SESSION['mwObjectListFilter']['object']) && $_SESSION['mwObjectListFilter']['object'] == $this->getId()) {
			if (isset($_GET['archives'])) {
				$_SESSION['mwObjectListFilter']['archives'] = $_GET['archives'];
			} elseif (isset($_POST['archives'])) {
				$_SESSION['mwObjectListFilter']['archives'] = $_POST['archives'];
			} else {
				$_SESSION['mwObjectListFilter']['archives'] = 0;
			}

			return $_SESSION['mwObjectListFilter'];
		} else {
			return [];
		}
	}
	public function printFilterTags(): string
	{
		$content = '';
		$tags = '';
		$this->isAllowFilter();
		{
			$filter = $this->getSavedListFilter();

			foreach ($this->getFilterSetting() as $filterItem) {
			if (isset($filterItem['type']) && $filterItem['type'] == 'hidden' && isset($filter[$filterItem['id']]) && $filter[$filterItem['id']]) {
				$object = mwSetting()->getObject($filterItem['object_id']);
				$item = $object->service()->getItem($filter[$filterItem['id']]);
				$tags .= '<span>' . $filterItem['title'] . ':</span> <a href="' . $item->getUrl() . '" target="_blank">' . $item->getName() . '</a>';
				$tags .= mwAdminComponents::iconLink([
					'icon' => 'x',
					'title' => __('Zrušit filtr', 'cms'),
					'link' => $this->getUrl(),
				], 'mw_comments_close_page_filter');
			}
			}
		}

		return $tags ? '<div class="mw_title_description_comments">' . $tags . '</div>' : '';
	}

	public function message404($text = '')
	{
		if (!$text) {
			$text = $this->getLabel('notfound');
		}

		echo mwSetting::message404($text, '<a href="' . $this->getUrl() . '">' . __('Zpět na seznam', 'cms') . '</a>');
	}

	public function checkData(&$tosave, $itemId = 0, $fast = false, bool $add = false): bool
	{
		foreach ($this->_setting as $set) {
			if (isset($tosave[$set['id']]) || (count($this->_setting) === 1 && isset($tosave['post_title']))) {
				if (!$this->checkDataSet($tosave, $set['fields'] ?? [], $set['id'], $itemId, $add)) {
					return false;
				}
			}
		}

		if (!$this->service()->checkData($tosave, $itemId, $fast, $add)) {
			return false;
		}

		return (bool) $this->service()->checkData($tosave, $itemId, $fast);
	}

	public function checkDataSet(&$tosave, $set, $setId = '', $itemId = 0, bool $add = false): bool
	{
		$setData = $setId ? ($tosave[$setId] ?? []) : $tosave;

		foreach ($set as $field) {
			if ($field['type'] == 'group' || $field['type'] == 'box' || $field['type'] == 'toggle_group') {
				if (!$this->checkDataSet($tosave, $field['setting'], $setId, $itemId)) {
					return false;
				}
			} else {
				if (isset($field['required']) && $field['required'] && !$setData[$field['id']]) {
					mwMessages()->error(sprintf(__('Pole %s je vyžadováno, prosím vyplňte jej.', 'cms'), $field['name']));

					return false;
				}
				if ($field['type'] == 'item_set' && isset($field['fields']['post_title']) && !$tosave['post_title']) {
					mwMessages()->error(sprintf(__('Pole %s je vyžadováno, prosím vyplňte jej.', 'cms'), $field['fields']['post_title']['label']));

					return false;
				}
				if ($field['type'] == 'item_set' && isset($field['fields']['term_title']) && !$tosave['term']['name']) {
					mwMessages()->error(sprintf(__('Pole %s je vyžadováno, prosím vyplňte jej.', 'cms'), $field['fields']['term_title']['label']));

					return false;
				}

				if ($field['type'] === 'upload_file' && isset($field['max_file_size_bytes']) && $field['max_file_size_bytes'] && (bool) $setData[$field['id']]) {
					$file = $setData[$field['id']];
					if (Validators::isUrl($file)) {
						$url = new UrlScript($file);
						$file = rtrim(ABSPATH, '/') . '/' . ltrim($url->getPath(), '/');
					}

					if (file_exists($file)) {
						$actualSize = filesize($file);
						$maxFileSizeMB = Order::MAXIMUM_FILE_SIZE_MB;

						if ($actualSize > $maxFileSizeMB * pow(2, 20)) {
							mwMessages()->error(sprintf(__('Maximální povolená velikost souboru v poli "%s" je %d MB.', 'cms'), $field['name'], $maxFileSizeMB));

							return false;
						}
					}
				}

				if ($field['type'] === 'emails') {
					$errorText = '';
					$emails = (isset($field['id']) && $field['id']) && !(isset($field['ignore_id_in_field_names']) && $field['ignore_id_in_field_names']) ? $setData[$field['id']] : $setData;

					foreach ($field['content'] as $emailId => $emailSet) {
						if (isset($emails[$emailId])) {
							$errorText .= Email::checkHtmlTags(
								$emailSet['title'],
								$emails[$emailId],
							);
							/*
							Email::checkAttachment(
								MwsEmailType::getCaption($emailId) . (isset($customEmail['name']) ? ' - ' . $customEmail['name'] : ''),
								$emails[$emailId]
							);*/
						}
					}
					if ($errorText) {
						mwMessages()->error($errorText);

						return false;
					}
				}

				if (isset($field['data_type']) && $field['data_type'] === 'float') {
					$tosave[$setId][$field['id']] = str_replace(',', '.', $tosave[$setId][$field['id']]);
				}

				/*
				if($field['type']=='number' && $setData[$field['id']]!=='' && !is_numeric($setData[$field['id']]))
				{
					mwMessages()->error(sprintf(__('Pole %s musí být číslo.', 'cms'), '<i>'.$field['name'].'</i>'));
					return false;
				}*/
			}
		}

		return (bool) $this->service()->checkDataSet($setData, $set, $setId, $itemId);
	}

}
