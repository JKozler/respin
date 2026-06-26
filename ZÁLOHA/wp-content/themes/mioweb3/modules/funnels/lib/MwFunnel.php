<?php
class MwFunnel
{

	public $funnel;

	public $id;

	public $name;

	public $code;

	public $statistics;

	public $redirect;

	public $cookie_time;

	public $evergreen;

	public $upsell;

	public $bump;

	public $show_sell;

	public $show_contacts;

	public $currency;

	public $statistics_reset_at;

	public $sale_platform;

	protected $_items = [];

	protected $_sources = [];

	protected $_currencies = [
		'CZK' => 'Kč',
		'EUR' => '€',
		'PLN' => 'Zł',
		'USD' => '$',
	];

	function __construct($funnel)
	{
		$this->funnel = $funnel;
		$this->id = $funnel->funnel_id;
		$this->name = $funnel->funnel_title;
		$this->code = $funnel->funnel_code;
		$this->redirect = $funnel->funnel_redirect;
		$this->cookie_time = $funnel->funnel_cookie_time;
		$this->evergreen = $funnel->funnel_evergreen ? 1 : 0;
		$this->sale_platform = $funnel->funnel_sale_platform ?? 'mioweb';
		$this->upsell = $funnel->funnel_upsell;
		$this->bump = $funnel->funnel_bump;
		$this->show_sell = $funnel->funnel_show_sell ? 1 : 0;
		$this->show_contacts = $funnel->funnel_show_contacts ? 1 : 0;
		$this->currency = 'CZK';
		$this->statistics_reset_at = ($funnel->funnel_statistics_reset_at ?? null) !== null
			? new \DateTimeImmutable($funnel->funnel_statistics_reset_at)
			: null;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	function getHomeUrl()
	{
		$this->getItems();
		$url = '';
		foreach ($this->_items as $item) {
			if ($item['type'] == 'squeeze') {
				$url = get_permalink($item['page_id']);

				break;
			}
		}

		return $url;
	}

	function getNextItem($page_id)
	{
		$this->getItems();
		$next = false;
		foreach ($this->_items as $item) {
			if ($next && $item['type'] != 'squeeze' && $item['type'] != 'source') {
				return $item;
			}
			if ($item['page_id'] == $page_id) {
				$next = true;
			}
		}

		return null;
	}

	function hasItems()
	{
		return $this->_items && count($this->_items);
	}

	function getItems()
	{
		if (!$this->_items) {
			global $wpdb;
			$result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'mw_funnel_pages WHERE fp_funnel_id = ' . $this->id . ' ORDER BY fp_order ');
			foreach ($result as $row) {
				$this->_items[] = $this->formatItem($row);
			}
		}

		return $this->_items;
	}

	function getContentItems()
	{
		$return_items = [];
		foreach ($this->getItems() as $item) {
			if ($item['type'] != 'squeeze' && $item['type'] != 'source') {
				$return_items[] = $item;
			}
		}

		return $return_items;
	}
	function getNavItems()
	{
		$return_items = [];
		foreach ($this->getItems() as $item) {
			if ($item['type'] != 'squeeze' && $item['type'] != 'source' && !$item['nav_hide'] && $item['page_id']) {
				$return_items[] = $item;
			}
		}

		return $return_items;
	}

	function getItemsByType($type = '')
	{
		if ($type) {
			$return_items = [];
			foreach ($this->getItems() as $item) {
				if ($item['type'] == $type) {
					$return_items[] = $item;
				}
			}
		} else {
			$return_items = $this->getItems();
		}

		return $return_items;
	}

	function getItemsById($id)
	{
		foreach ($this->getItems() as $item) {
			if ($item['id'] == $id) {
				return $item;
			}
		}

		return null;
	}

	function getStats($query, $from = null, $to = null, $utm = [])
	{
		if ($from) {
			$query->from($from); }
		if ($to) {
			$query->to($to); }
		foreach ($utm as $tag => $val) {
			if ($val && $tag != 'source') {
				$query->filterByTag($tag, [$val]);
			}
		}
		$query->unique();

		return $query->fetchAll();
	}

	function loadStatistics($from = null, $to = null, $utm = [])
	{
		if (!$this->statistics) {
			$this->statistics = [
				'items' => [],
				'conversions' => [],
				'abtest' => [],
				'sources' => [],
				'visits' => 0,
				'sums' => [],
				'contacts' => 0,

				'orders' => 0,
				'upsells' => 0,
				'bumps' => 0,
				'average_order' => 0,
				'sum_orders' => 0,

				'orders_per_visit' => 0,
				'orders_per_contact' => 0,
			];

			if ($this->statistics_reset_at !== null && ($from === null || $from < $this->statistics_reset_at)) {
				$from = $this->statistics_reset_at;
			}

			$ids = [];


			foreach ($this->getItems() as $item) {
				// pages
				if ($item['page_id']) {
					$ids[] = $item['page_id'];
				}
			}
			if (count($ids)) {
				$raw_statistics = $this->getStats(core()->getAnalytics()->getStats()->filterByEvent('page', $ids), $from, $to, $utm);

				// items visits
				foreach ($raw_statistics as $stat) {
					$this->statistics['items'][$stat['externId']] = $stat['count'];
					$this->statistics['conversions'][$stat['externId']] = $stat['targetsCount'];
				}
			}

			$first_content = $this->getFirstContentItem();
			$this->statistics['sums']['squeeze'] = 0;

			// sums and visits and a/b test
			foreach ($this->getItems() as $item) {
				if ($item['page_id'] && isset($this->statistics['items'][$item['page_id']])) {
					if ($item['type'] == 'squeeze') {
						$this->statistics['sums']['squeeze'] += $this->statistics['items'][$item['page_id']];
						$sum = $this->statistics['sums']['squeeze'];
					} else {
						$sum = $this->statistics['items'][$item['page_id']];
					}
					if ($sum > $this->statistics['visits']) {
						$this->statistics['visits'] = $sum;
					}
				}
				if (isset($item['page_id']) && $item['page_id'] && $item['ab_page'] && get_post_status($item['ab_page']) == 'publish') {
					$ab_1 = $this->getStats(core()->getAnalytics()->getStats()->filterByEvent('page', [$item['page_id']])->filterByTag('ab_test_' . $item['page_id'] . '_' . $item['ab_page'], [$item['page_id']]), $from, $to, $utm);
					$ab_2 = $this->getStats(core()->getAnalytics()->getStats()->filterByEvent('page', [$item['page_id']])->filterByTag('ab_test_' . $item['page_id'] . '_' . $item['ab_page'], [$item['ab_page']]), $from, $to, $utm);

					$a_visits = isset($ab_1[0]) ? $ab_1[0]['count'] : 0;
					$a_conversions = $a_visits > 0 && isset($ab_1[0]) ? round($ab_1[0]['targetsCount'] / $a_visits * 100) . '%' : '0%';

					$b_visits = isset($ab_2[0]) ? $ab_2[0]['count'] : 0;
					$b_conversions = $b_visits > 0 && isset($ab_2[0]) ? round($ab_2[0]['targetsCount'] / $b_visits * 100) . '%' : '0%';

					$this->statistics['abtest'][$item['page_id']] = [
						'original' => [
							'visits' => $a_visits,
							'conversions' => $a_conversions,
						],
						'variant' => [
							'visits' => $b_visits,
							'conversions' => $b_conversions,
						],
					];
				}
			}

			// sources visits
			$this->loadSourceVisits($ids, $from, $to, $utm);

			// contacts
			$this->loadContactCount($from, $to, $utm);

			// ordereds
			$this->loadOrderNums($from, $to, $utm);
		}

		return $this->statistics;
	}

	function loadSourceVisits($ids, $from = null, $to = null, $utm = [])
	{
		$sourceTags = [];
		$filtered = [];
		foreach ($this->getItemsByType('source') as $item) {
			$sourceTags[$item['id']] = [
				'utm_source' => $item['utm_source'],
				'utm_medium' => $item['utm_medium'],
				'utm_campaign' => $item['utm_campaign'],
				'utm_term' => $item['utm_term'],
				'utm_content' => $item['utm_content'],
			];
			if (count($utm)) {
				$filtered[$item['id']] = (!$utm['utm_source'] || $utm['utm_source'] == $item['utm_source'])
					&& (!$utm['utm_medium'] || $utm['utm_medium'] == $item['utm_medium'])
					&& (!$utm['utm_campaign'] || $utm['utm_campaign'] == $item['utm_campaign'])
					&& (!$utm['utm_term'] || $utm['utm_term'] == $item['utm_term'])
					&& (!$utm['utm_content'] || $utm['utm_content'] == $item['utm_content'])
				 ? 1 : 0;
			} else {
				$filtered[$item['id']] = 1;
			}
		}
		foreach ($sourceTags as $sid => $st) {
			if ($filtered[$sid]) {
				$query = core()->getAnalytics()->getStats()->filterByEvent('page', $ids)->group(\Mioweb\Core\Analytics\IQuery::GROUP_MODE_TAGS);

				$used_utms = 0;
				foreach ($st as $tag => $val) {
					if ($val) {
						$query->filterByTag($tag, [$val]);
						$used_utms++;
					}
				}

				$this->statistics['sources'][$sid] = 0;

				if ($used_utms) {
					$raw_source_stats = $this->getStats($query, $from, $to);
					foreach ($raw_source_stats as $stat) {
						$this->statistics['sources'][$sid] += $stat['count'];
					}
				}
			} else {
				$this->statistics['sources'][$sid] = 0;
			}
		}
	}
	function loadContactCount($from = null, $to = null, $utm = [])
	{
		$contacts_data = $this->getStats(core()->getAnalytics()->getStats()->filterByEvent('newContact_f' . $this->id), $from, $to, $utm);
		$this->statistics['contacts'] = !empty($contacts_data) ? $contacts_data[0]['count'] : 0;
	}
	function loadOrderNums($from = null, $to = null, $utm = [])
	{
		$orders_data = $this->getStats(core()->getAnalytics()->getStats()->filterByEvent('funnel_purchase_f' . $this->id), $from, $to, $utm);

		if (isset($orders_data[0])) {
			foreach ($orders_data[0]['data'] as $order) {
				if ($order['upsell']) {
					$this->statistics['upsells']++;
				}
				if ($order['bump']) {
					$this->statistics['bumps']++;
				}
				$this->statistics['sum_orders'] += $order['price'];
				$this->statistics['orders']++;

				$this->currency = $order['currency'];
			}
		}

		$this->statistics['average_order'] = $this->statistics['orders'] ? $this->statistics['sum_orders'] / $this->statistics['orders'] : 0;

		$this->statistics['orders_per_visit'] = $this->statistics['visits'] ? $this->statistics['sum_orders'] / $this->statistics['visits'] : 0;
		$this->statistics['orders_per_contact'] = $this->statistics['contacts'] ? $this->statistics['sum_orders'] / $this->statistics['contacts'] : 0;
	}

	function getItemVisits($page_id)
	{
		return $page_id && isset($this->statistics['items'][$page_id]) ? $this->statistics['items'][$page_id] : 0;
	}
	function getItemABVisits($page_id)
	{
		return $page_id && isset($this->statistics['abtest'][$page_id]) ? $this->statistics['abtest'][$page_id] : [
				'original' => [
					'visits' => 0,
					'conversions' => 0,
				],
				'variant' => [
					'visits' => 0,
					'conversions' => 0,
				],
		];
	}
	function getItemConversion($page_id)
	{
		if ($page_id && isset($this->statistics['conversions'][$page_id])) {
			$visits = $this->getItemVisits($page_id);
			$percent = $visits > 0 ? round($this->statistics['conversions'][$page_id] / $visits * 100) . '%' : '0%';

			return [
				'count' => $this->statistics['conversions'][$page_id],
				'percent' => $percent,
			];
		}

		return [
			'count' => null,
			'percent' => null,
		];
	}

	function getSourceVisits($item_id)
	{
		return $this->statistics['sources'][$item_id] ?? 0;
	}

	function getSumVisits($type = null, $format = false)
	{
		$visits = 0;
		if (!$type) {
			$visits = $this->statistics['visits'];
		}
		if (isset($this->statistics['sums'][$type])) {
			$visits = $this->statistics['sums'][$type];
		}

		return $visits;
	}
	function getPercentVisits($visits, $max100 = false)
	{
		if ($this->statistics['visits'] > 0) {
			$percent = round($visits / $this->statistics['visits'] * 100);
			if ($max100 && $percent > 100) {
				$percent = 100;
			}

			return $percent . '%';
		} else {
			return '0%';
		}
	}
	function getOrdersCount()
	{
		return number_format($this->statistics['orders'], 0, '.', ' ');
	}
	function getSumOrders()
	{
		return number_format($this->statistics['sum_orders'], 2, '.', ' ') . ' ' . $this->currency();
	}
	function getAverageOrder()
	{
		return number_format($this->statistics['average_order'], 2, '.', ' ') . ' ' . $this->currency();
	}
	function getUpsellsCount()
	{
		return $this->statistics['upsells'];
	}
	function getBumpsCount()
	{
		return $this->statistics['bumps'];
	}
	function getContactsNum()
	{
		return $this->statistics['contacts'];
	}
	function getOrdersPerVisits()
	{
		return number_format($this->statistics['orders_per_visit'], 2, '.', ' ') . ' ' . $this->currency();
	}
	function getOrdersPerContacts()
	{
		return number_format($this->statistics['orders_per_contact'], 2, '.', ' ') . ' ' . $this->currency();
	}

	function getUsedUTMs()
	{
		$utms = [
			'utm_source' => [],
			'utm_medium' => [],
			'utm_term' => [],
			'utm_campaign' => [],
			'utm_content' => [],
		];
		$ids = [];
		foreach ($this->getItems() as $item) {
			// pages
			if ($item['page_id']) {
				$ids[] = $item['page_id'];
			}
		}
		if (count($ids)) {
			// source
			$utms['utm_source'] = $this->getUsedUTM('utm_source', $ids);
			// medium
			$utms['utm_medium'] = $this->getUsedUTM('utm_medium', $ids);
			// campaign
			$utms['utm_campaign'] = $this->getUsedUTM('utm_campaign', $ids);
			// term
			$utms['utm_term'] = $this->getUsedUTM('utm_term', $ids);
			// content
			$utms['utm_content'] = $this->getUsedUTM('utm_content', $ids);
		}

		return $utms;
	}
	function getUsedUTM($name, $ids)
	{
		$return = [];
		$utms = core()->getAnalytics()->getStats()->filterByEvent('page', $ids)->filterByTag($name)->group(\Mioweb\Core\Analytics\IQuery::GROUP_MODE_TAGS)->fetchAll();
		foreach ($utms as $utm) {
			foreach ($utm['tags'] as $tag) {
				$return[] = $tag['value'];
			}
		}

		return $return;
	}

	function getFirstContentItem()
	{
		foreach ($this->getItems() as $item) {
			if ($item['type'] != 'squeeze' && $item['type'] != 'source') {
				return $item;
			}
		}

		return null;
	}
	function getPermalink($item)
	{
		$url = '';
		if ($item['page_id']) {
			$url = get_permalink($item['page_id']);
			if ($item['limited_access']) {
				$url .= '?setuser=' . $this->code;
			}
		}

		return $url;
	}
	function currency()
	{
		return $this->_currencies[$this->currency];
	}

	public static function formatItem($row)
	{
		$publishtime = $row->fp_publishtime ? unserialize($row->fp_publishtime) : [];
		$publishtimestamp = 0;
		if (isset($publishtime['date']) && $publishtime['date']) {
			$publishtimestamp = strtotime($publishtime['date'] . ' ' . $publishtime['hour'] . ':' . $publishtime['minute'] . ':0');
		}
		$set = [
			'id' => $row->fp_id,
			'page_id' => $row->fp_page_id,
			'ab_page' => $row->fp_ab_page,
			'title' => $row->fp_title,
			'type' => $row->fp_type,
			'limited_access' => $row->fp_limited_access,
			'order' => $row->fp_order,
			'nav_hide' => $row->fp_nav_hide ? 1 : 0,
			'publishtime' => $publishtime,
			'publishtimestamp' => $publishtimestamp,
			'nav_title' => $row->fp_nav_title,
			'funnel_id' => $row->fp_funnel_id,
			'nav_image' => $row->fp_nav_image ? unserialize($row->fp_nav_image) : [],
			'nav_ba_title' => $row->fp_nav_ba_title,
			'nav_ba_image' => $row->fp_nav_ba_image ? unserialize($row->fp_nav_ba_image) : [],
		];
		if ($row->fp_type == 'source') {
			$set['utm_source'] = $row->fp_utm_source;
			$set['utm_medium'] = $row->fp_utm_medium;
			$set['utm_campaign'] = $row->fp_utm_campaign;
			$set['utm_term'] = $row->fp_utm_term;
			$set['utm_content'] = $row->fp_utm_content;
			$set['icon'] = unserialize($row->fp_icon);
			$set['color'] = $row->fp_color;
		}

		return $set;
	}

	public function delete()
	{
		global $wpdb;

		$wpdb->delete($wpdb->prefix . 'mw_funnels', [
			'funnel_id' => $this->id,
		]);
		$wpdb->delete($wpdb->prefix . 'mw_funnel_pages', [
			'fp_funnel_id' => $this->id,
		]);

		$pages = new WP_Query([
			'post_type' => 'page',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_key' => FUNNEL_POST_META,
			'meta_value' => $this->id,
		]);
		foreach ($pages->posts as $page_id) {
			delete_post_meta($page_id, FUNNEL_POST_META);
		}
	}

	public static function registerFunnels()
	{
		$mwArgs = [
			'service_class' => 'mwSettingObjectService_Funnel',
			'class' => 'MwFunnel',
			'object_type' => 'funnel',
			'allow_add' => true,
			'labels' => [
				'title' => __('Cesty zákazníků', 'mw_funnels'),
				'add_item' => __('Přidat cestu zákazníka', 'mw_funnels'),
				'edit_item' => __('Upravit cestu zákazníka', 'mw_funnels'),
				'new_item' => __('Nová cesta zákazníka', 'mw_funnels'),
				'delete' => __('Smazat cestu zákazníka', 'mw_funnels'),
				'empty' => __('Není vytvořena žádná cesta zákazníka.<br><a class="mw_open_funnel_installator" href="#">Vytvořit první cestu zákazníka</a>', 'mw_funnels'),
				'notfound' => __('Cesta zákazníka nebyla nalezena', 'mw_funnels'),
			],

		];
		mwSetting()->registerObject('mw_funnels', $mwArgs);
	}

}

class mwSettingObjectService_Funnel extends mwSettingObjectService
{

	public function getListArgs($page = 1, $perPage = -1, $trash = false): array
	{
		$args = [
			'rows' => [],
			'empty_content' => $this->object()->getLabel('empty'),
			'head' => [
				[
					'content' => __('Název', 'mw_funnels'),
				],
				[
					'content' => __('Návštěv', 'mw_funnels'),
				],
				[
					'content' => __('Kontaktů', 'mw_funnels'),
				],
				[
					'content' => __('Tržby', 'mw_funnels'),
				],
				[
					'content' => __('Akce', 'mw_funnels'),
					'align' => 'right',
				],
			],
		];

		$funnels = MWF()->getAll();


		foreach ($funnels as $funnel) {
			//$event = mwEvent::createNew($item);

			$args['rows'][] = [
				'cols' => [
					[
						'content' => '<a class="mw_link" href="' . $this->object()->getEditUrl($funnel->getId()) . '">' . $funnel->getName() . '</a>',
					],
					[
						'content' => mwAdminComponents::icon(['icon' => 'eye', 'text' => $funnel->getSumVisits()], 'mw_table_statistics'),
					],
					[
						'content' => mwAdminComponents::icon(['icon' => 'mail', 'text' => $funnel->getContactsNum()], 'mw_table_statistics'),
					],
					[
						'content' => mwAdminComponents::icon(['icon' => 'dollar-sign', 'text' => $funnel->getSumOrders()], 'mw_table_statistics'),
					],
					[
						'content' => mwSetting::printSettingActions(['edit', 'delete'], $funnel->getId(), $this->object()),
						'align' => 'right',
					],
				],
			];
		}

		return $args;
	}

	public function printEditPage($itemId)
	{
		$item = MWF()->getById($itemId);
		if ($item) {
			echo MWF()->open_funnel_setting($item);
		} else {
			$this->object()->message404();
		}
	}

	public function getItem($itemId)
	{
		return $itemId ? MWF()->getById($itemId) : null;
	}

	public function delete($funnelId, $force_delete = false)
	{
		if ($funnelId) {
			$funnel = MWF()->getById($funnelId);
			$funnel->delete();
		}
	}

}
