<?php
class mwPageSelectorTabService
{

	private $_tab;

	public function __construct(mwPageSelectorTab $tab)
	{
		$this->_tab = $tab;
	}

	public function tab(): mwPageSelectorTab
	{
		return $this->_tab;
	}

	public function getLists(array &$pages, array &$icons, int &$order): array
	{
		return [];
	}

}

class mwPageSelectorTabService_web extends mwPageSelectorTabService
{

	public function getLists(array &$pages, array &$icons, int &$order): array
	{
		$lists = [
			0 => new mwPageSelectorList([
				'title' => __('Webové stránky', 'cms_ve'),
				'items' => $pages[0],
				'usePageChilds' => true,
			], $this->tab()->getId()),
		];
		$order++;

		return $lists;
	}

}

class mwPageSelectorTabService_blog extends mwPageSelectorTabService
{

	public function getLists(array &$pages, array &$icons, int &$order): array
	{
		$lists = [];

		// blog categories
		$items = [];
		$childs = [];
		$categories = mwTerm::getAll('category');
		foreach ($categories as $cat) {
			if ($cat->getParentId()) {
				$childs[$cat->getParentId()][] = $cat->toPageSelectorItem();
			} else {
				$items[$cat->getId()] = $cat->toPageSelectorItem();
			}
		}

		$lists[$order] = new mwPageSelectorList([
			'title' => __('Kategorie blogu', 'cms_ve'),
			'items' => $items,
			'childs' => $childs,
		], $this->tab()->getId());
		$order++;

		$items = [];
		$posts = mwBlogPost::getAll(['post_status' => 'publish,private,draft,future,pending'], false);
		foreach ($posts as $post) {
			$item = $post->toPageSelectorItem();
			$item->setIcons(mwPageSelector::getPostIcons($post));
			$items[] = $item;
		}

		// posts
		$lists[$order] = new mwPageSelectorList([
			'title' => __('Články', 'cms_ve'),
			'items' => $items,
		], $this->tab()->getId());
		$order++;

		return $lists;
	}
}

class mwPageSelectorTabService_all extends mwPageSelectorTabService
{

}

class mwPageSelectorTabService_member extends mwPageSelectorTabService
{

	public function getLists(array &$pages, array &$icons, int &$order): array
	{
		$lists = [];
		foreach (mwMemberModule()->getMemberSections() as $memberSection) {
			$items = [];
			if ($memberSection->getLoginId()) {
				// add login page only if not member page
				$page = \Mioweb\Member\MemberPage::getOneById($memberSection->getLoginId());
				if (!$page) {
					$page = mwPage::getOneById($memberSection->getLoginId());
					if ($page && $page->getStatus() !== 'trash') {
						$items[] = $page->toPageSelectorItem();
						if (isset($pages[0][$page->getId()])) {
							unset($pages[0][$page->getId()]);
						}
					}
				}
			}
			foreach ($memberSection->getPages(['post_status' => 'publish,private,draft,future,pending,trash']) as $page) {
				$icons[$page->getId()] = array_merge($icons[$page->getId()] ?? [], self::getIcons($page));
				if ($page->getParentId() === 0) {
					$items[] = $page->toPageSelectorItem();
					unset($pages[0][$page->getId()]);
				}
			}

			$lists[$order] = new mwPageSelectorList([
				'title' => $memberSection->getName(),
				'items' => $items,
				'usePageChilds' => true,
			], $this->tab()->getId());
			$order++;
		}

		return $lists;
	}

	private static function getIcons($page): array
	{
		$icons = [];
		if ($page->getMemberPageId()) {
			$icons[] = [
				'icon' => 'lock',
				'text' => __('Členská stránka', 'cms_ve'),
			];
		}
		if ($page->getAccessType() === 'date') {
			$icons[] = [
				'icon' => 'clock',
				'text' => __('Datum', 'cms_ve') . ': ' . $page->getEvergreenDate(),
			];
		} elseif ($page->getAccessType() === 'evergreen') {
			$icons[] = [
				'icon' => 'clock',
				'text' => __('Evergreen', 'cms_ve') . ': ' . $page->getEvergreenDays() . ' ' . __('dní', 'cms_ve'),
			];
		} elseif ($page->getAccessType() === 'month') {
			$month = $page->getMonth();
			if ($month) {
				$icons[] = [
					'icon' => 'calendar',
					'text' => __('Měsíc', 'cms_ve') . ': ' . $month->getName(),
				];
			}
		} elseif ($page->getAccessType() === 'checklist') {
			$icons[] = [
				'icon' => 'check-circle',
				'text' => __('Po splnění úkolů předešlé stránky', 'cms_ve'),
			];
		}

		return $icons;
	}
}

class mwPageSelectorTabService_eshop extends mwPageSelectorTabService
{

	public function getLists(array &$pages, array &$icons, int &$order): array
	{
		$lists = [];
		// eshop pages
		$eshopPages = [];
		if (MWS()->getHomePageId()) {
			//eshop home
			$homePage = mwPage::getOneById(MWS()->getHomePageId());
			if ($homePage !== null) {
				$eshopPages[] = $homePage->toPageSelectorItem();
				if (isset($pages[0][MWS()->getHomePageId()])) {
					unset($pages[0][MWS()->getHomePageId()]);
				}
			}
		}
		if (MWS()->getOrderPageId()) {
			//eshop order
			$orderPage = mwPage::getOneById(MWS()->getOrderPageId());
			if ($orderPage !== null) {
				$eshopPages[] = $orderPage->toPageSelectorItem();
				if (isset($pages[0][MWS()->getOrderPageId()])) {
					unset($pages[0][MWS()->getOrderPageId()]);
				}
			}
		}

		if (count($eshopPages)) {
			$lists[$order] = new mwPageSelectorList([
				'title' => __('Eshop', 'mwshop'),
				'items' => $eshopPages,
				'usePageChilds' => true,
			], $this->tab()->getId());
			$order++;
		}

		// eshop categories
		$items = [];
		$childs = [];
		$categories = mwTerm::getAll('eshop_category');
		foreach ($categories as $cat) {
			if ($cat->getParentId()) {
				$childs[$cat->getParentId()][] = $cat->toPageSelectorItem();
			} else {
				$items[$cat->getId()] = $cat->toPageSelectorItem();
			}
		}

		$lists[$order] = new mwPageSelectorList([
			'title' => __('Kategorie eshopu', 'cms_ve'),
			'items' => $items,
			'childs' => $childs,
		], $this->tab()->getId());
		$order++;

		$items = [];
		$posts = MwsProduct::getAll(['post_status' => 'any']);
		foreach ($posts as $post) {
			$items[] = $post->toPageSelectorItem();
		}

		//eshop products
		$lists[$order] = new mwPageSelectorList([
			'title' => __('Produkty eshopu', 'cms_ve'),
			'items' => $items,
		], $this->tab()->getId());
		$order++;

		return $lists;
	}

}

class mwPageSelectorTabService_funnel extends mwPageSelectorTabService
{

	public function getLists(array &$pages, array &$icons, int &$order): array
	{
		$lists = [];
		$funnel_pages = \Mioweb\Funnel\FunnelPage::getAllForFunnel(null, [
			'post_status' => 'publish,private,draft,future,pending',
		]);

		foreach (MWF()->getAll() as $funnel) {
			$items = [];
			foreach ($funnel_pages as $page) {
				$meta = get_post_meta($page->getId(), FUNNEL_POST_META, true);
				if ($meta && $meta === $funnel->id) {
					$icons[$page->getId()][] = [
						'icon' => 'filter',
						'text' => __('Stránka cesty zákazníka', 'cms_ve'),
					];
					$items[] = $page->toPageSelectorItem();
				}
			}
			$lists[$order] = new mwPageSelectorList([
				'title' => $funnel->name,
				'items' => $items,
				'excludeSearch' => true,
			], $this->tab()->getId());
			$order++;
		}

		return $lists;
	}

}

class mwPageSelectorTabService_campaign extends mwPageSelectorTabService
{

	public function getLists(array &$pages, array &$icons, int &$order): array
	{
		$lists = [];
		$campaigns = MwCampaign::getAll();
		$campaign_pages = MwCampaignPage::getAll([
			'meta_key' => 'mioweb_campaign',
			'post_status' => 'publish,private,draft,future,pending',
		]);
		if (count($campaigns)) {
			foreach ($campaigns as $campaign) {
				$items = [];
				foreach ($campaign_pages as $page) {
					$meta = get_post_meta($page->getId(), 'mioweb_campaign', true);
					if (isset($meta['campaign']) && $meta['campaign'] === $campaign->getId()) {
						$icons[$page->getId()][] = [
							'icon' => 'target',
							'text' => __('Stránka kampaně', 'cms_ve'),
						];
						$items[] = $page->toPageSelectorItem();
					}
				}

				$lists[$order] = new mwPageSelectorList([
					'title' => $campaign->getName(),
					'items' => $items,
					'excludeSearch' => true,
				], $this->tab()->getId());
				$order++;
			}
		}

		return $lists;
	}

}
