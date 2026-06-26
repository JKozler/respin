<?php

define('CAMPAIGN_OPTION', 'campaign_basic');

class MwCampaign
{

	private $_id;

	private $_setting;

	public function __construct($id, $campaign)
	{
		$this->_id = $id;
		$this->_setting = $campaign;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getName()
	{
		return $this->_setting['name'] ?? __('(Bez názvu)', 'cms');
	}

	public function getSqueezeId()
	{
		return $this->_setting['squeeze'] ?? 0;
	}

	public function getPages()
	{
		return $this->_setting['page'] ?? [];
	}

	public function getUrl()
	{
		$did = $this->_setting['squeeze'] ?? 0;

		return $did ? get_permalink($did) : '';
	}

	/**
	 * Get discount code instance by member section ID.
	 */
	public static function getOneById(int $id): ?self
	{
		$campaigns = self::getAll();
		$campaign = null;
		foreach ($campaigns as $camp) {
			if ($camp->getId() == $id) {
				$campaign = $camp;
			}
		}

		return $campaign;
	}

	public static function createNew(int $id, array $camp): self
	{
		return new self($id, $camp);
	}

	public static function getAll(): array
	{
		$camps = get_option(CAMPAIGN_OPTION);
		$ret = [];
		if (isset($camps['campaigns'])) {
			foreach ($camps['campaigns'] as $id => $camp) {
				$ret[] = self::createNew(intval($id), $camp);
			}
		}

		return $ret;
	}

	public static function registerCamapaigns()
	{
		$mwArgs = [
			'service_class' => 'mwSettingObjectService_Campaign',
			'class' => 'MwCampaign',
			'object_type' => 'campaign',
			'allow_add' => true,
			'labels' => [
				'title' => __('Kampaně', 'cms_mioweb'),
				'add_item' => __('Přidat kampaň', 'cms_mioweb'),
				'edit_item' => __('Upravit kampaň', 'cms_mioweb'),
				'new_item' => __('Nová kampaň', 'cms_mioweb'),
				'delete' => __('Smazat kampaň', 'cms_mioweb'),
				'empty' => __('Nebyla nalezena žádná kampaň', 'cms_mioweb'),
				'notfound' => __('Kampaň nebyla nalezena', 'cms_mioweb'),
			],

		];
		mwSetting()->registerObject('campaigns', $mwArgs);
	}

}

class mwSettingObjectService_Campaign extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = MW_DEFAULT_PER_PAGE, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Název', 'cms_mioweb'),
				],
				[
					'content' => __('Akce', 'cms_mioweb'),
					'align' => 'right',
				],
			],
		];

		$camps = MwCampaign::getAll();

		$campaigns_pages = [];
		foreach ($camps as $camp) {
			$campaigns_pages[$camp->getId()]['title'] = $camp->getName();
			$campaigns_pages[$camp->getId()]['pages'][] = $camp->getSqueezeId();
			foreach ($camp->getPages() as $page) {
				if ($page['page']) {
					$campaigns_pages[$camp->getId()]['pages'][] = $page['page'];
				}
			}
		}

		foreach ($camps as $item) {
			$name = '<a class="mw_link" href="' . $this->object()->getEditUrl($item->getId()) . '">' . $item->getName() . '</a>';

			$conflictData = null;
			foreach ($campaigns_pages as $cp_id => $cp) {
				if ($cp_id != $item->getId()) {
					$intersect = array_intersect($campaigns_pages[$item->getId()]['pages'], $cp['pages']);
					if (count($intersect)) {
						$conflictData = [
							'campaign' => $cp['title'],
							'page' => reset($intersect),
						];
					}
				}
			}

			if ($conflictData !== null) {
				$name .= mwAdminComponents::tooltip([
					'icon' => '!',
					'text' => sprintf(__('Tato kampaň je v konfliktu s kampaní %s. V obou kampaních je použita stránka %s. Každá stránka může být použita pouze v jedné kampani.', 'cms_mioweb'), '<strong>' . $conflictData['campaign'] . '</strong>', '<strong>' . get_the_title($conflictData['page']) . '</strong>'),
				], 'mw_tooltip_alert');
			} elseif (!$item->getSqueezeId()) {
				$name .= mwAdminComponents::tooltip([
					'icon' => '!',
					'text' => __('Tato kampaň nemá nastavenou žádnou vstupní stránku. Pro správné fungování musíte kampani nastavit vstupní stránku.', 'cms_mioweb'),
				], 'mw_tooltip_alert');
			}

			$args['rows'][] = [
				'cols' => [
					[
						'content' => $name,
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

	public function printTitle($item = null): string
	{
		if (isset($_GET['edit'])) {
			$args = [
				'text' => $this->object()->getLabel('edit_item'),
			];

			$args['onright'] = mwAdminComponents::iconLink([
				'icon' => 'arrow-left',
				'text' => __('Zpět na výpis kampaní', 'cms_mioweb'),
				'link' => $this->object()->getUrl(),
			], 'mw_setting_action_link');
		} else {
			$args = [
				'text' => $this->object()->getLabel('title'),
			];

			$args['onright'] = mwAdminComponents::button([
				'button_text' => $this->object()->getLabel('add_item'),
				'icon' => 'plus',
				'attrs' => 'data-object="' . $this->object()->getId() . '" data-title="' . $this->object()->getLabel('add_item') . '" data-return="redirect"',
			], 'mw_setting_fast_add');
		}

		return mwAdminComponents::title($args, 'h2');
	}

	public function printForm($item, $add = false)
	{
		$option = get_option(CAMPAIGN_OPTION, []);
		$meta = $option['campaigns'][$item->getId()] ?? [];

		$meta_set = $this->object()->getSetting();

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

		echo '<div class="mw_setting_object_detail_content">';
		echo '<div class="mw_setting_object_detail_form">';

		$i = 1;
		foreach ($meta_set as $set) {
			echo '<div id="mw_object_setting_tab_' . $set['id'] . '" class="mw_tab mw_object_setting_tab_container ' . ($i == 1 ? 'active' : '') . '">';
			write_meta($set['fields'], $meta, 'campaign', 'campaign', $item->getId());
			$i++;
			echo '</div>';
		}

		wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce');
		echo '<input type="hidden" name="object_id" value="' . $this->object()->getId() . '"/>';
		echo '<input type="hidden" name="item_id" value="' . $item->getId() . '"/>';
		echo '</div>';
		echo '</div>';
	}

	public function checkData($tosave, $itemId = null, $fast = false, bool $add = false): bool
	{
		if (!isset($tosave['campaign']['name']) || !$tosave['campaign']['name']) {
			mwMessages()->error(__('Název kampaně je povinná položka, prosím vyplňte jej.', 'cms_mioweb'));

			return false;
		}

		if ($itemId !== null) {
			if (!$tosave['campaign']['code']) {
				mwMessages()->error(__('Přistupový kód je povinná položka, prosím vyplňte jej.', 'cms_mioweb'));

				return false;
			}

			if (!$tosave['campaign']['squeeze']) {
				mwMessages()->error(__('Pro správné fungování kampaně je potřeba nastavit vstupní stránku.', 'cms_mioweb'));

				return false;
			}

			$pages = new WP_Query([
				'post_type' => 'page',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'fields' => 'ids',
				'meta_key' => 'mioweb_campaign',
			]);
			foreach ($pages->posts as $page_id) {
				$page_meta = get_post_meta($page_id, 'mioweb_campaign', true);
				if (isset($page_meta['campaign']) && $page_meta['campaign'] != $itemId) {
					if ($tosave['campaign']['squeeze'] == $page_id) {
						mwMessages()->error(sprintf(__('Vstupní stránka %s byla použita již v jiné kampani. Prosím použijte jinou stránku.', 'cms_mioweb'), '<strong>' . get_the_title($page_id) . '</strong>'));

						return false;
					}
					foreach ($tosave['campaign']['page'] as $cPage) {
						if ($cPage['page'] == $page_id) {
							mwMessages()->error(sprintf(__('Stránka %s byla použita již v jiné kampani. Prosím použijte jinou stránku.', 'cms_mioweb'), '<strong>' . get_the_title($page_id) . '</strong>'));

							return false;
						}
					}
				}
			}
		}

		return true;
	}

	function add($tosave, $fast = false): int
	{
		$camps = get_option(CAMPAIGN_OPTION);

		if ($camps === false || !isset($camps['campaigns']) || count($camps['campaigns']) === 0) {
			$new_id = 1;
		} else {
			$max = max(array_keys($camps['campaigns']));
			$new_id = $max + 1;
		}

		$campaign = [
			'name' => $tosave['campaign']['name'],
			'code' => '1199',
			'squeeze' => 0,
		];


		if ($camps === '') {
			$camps = [];
		}

		$camps['campaigns'][$new_id] = $campaign;
		update_option(CAMPAIGN_OPTION, $camps);

		return $new_id;
	}

	public function save($itemId, $tosave)
	{
		$itemId = intval($itemId);
		$camps = get_option(CAMPAIGN_OPTION);
		$camps['campaigns'][$itemId] = $tosave['campaign'];
		update_option(CAMPAIGN_OPTION, $camps);

		// delete pages from campaign
		$pages = new WP_Query([
			'post_type' => 'page',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_key' => 'mioweb_campaign',
		]);
		foreach ($pages->posts as $page_id) {
			$page_meta = get_post_meta($page_id, 'mioweb_campaign', true);
			if (isset($page_meta['campaign']) && $page_meta['campaign'] == $itemId) {
				delete_post_meta($page_id, 'mioweb_campaign');
			}
		}

		// add pages to campaign

		foreach ($camps['campaigns'][$itemId]['page'] as $page) {
			update_post_meta($page['page'], 'mioweb_campaign', ['campaign' => $itemId, 'type' => 'page']);
		}
		update_post_meta($camps['campaigns'][$itemId]['squeeze'], 'mioweb_campaign', ['campaign' => $itemId, 'type' => 'squeeze']);
	}

	function delete($id, $force_delete = false)
	{
		$option = get_option(CAMPAIGN_OPTION);
		if (isset($option['campaigns'][$id])) {
			unset($option['campaigns'][$id]);
		}
		update_option(CAMPAIGN_OPTION, $option);

		$pages = new WP_Query([
			'post_type' => 'page',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_key' => 'mioweb_campaign',
		]);
		foreach ($pages->posts as $page_id) {
			$page_meta = get_post_meta($page_id, 'mioweb_campaign', true);
			if (isset($page_meta['campaign']) && $page_meta['campaign'] == $id) {
				delete_post_meta($page_id, 'mioweb_campaign');
			}
		}
	}

	public function getItem($itemId)
	{
		return $this->object()->getClass()::getOneById($itemId);
	}


}
