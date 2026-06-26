<?php

function mwPageSelector(): mwPageSelector
{
   return mwPageSelector::instance();
}

class mwPageSelector
{

	protected static ?self $_instance = null;

	private array $_tabs = [];

	private array $_lists = [];

	private array $_pages = [];

	private array $_pageIcons = [];

	public function addTab(array $tab, int $order): void
	{
		$this->_tabs[$order] = new mwPageSelectorTab($tab);
	}

	public function getPages(): array
	{
		return $this->_pages;
	}

	public function opener(): void
	{
		echo '<a id="ve_change_page" class="ve_open_page_selector" href="#">';
		   if (is_404()) {
		  echo __('Stránka neexistuje', 'cms_ve');
		   } elseif (is_front_page()) {
			echo __('Úvodní stránka', 'cms_ve');
		   } elseif (is_home()) {
			echo __('Úvodní stránka blogu', 'cms_ve');
		   } else {
			the_title();
		   }
			echo '<span>' . mw_icon('icon-chevron-right') . '</span>';
		echo '</a>';
	}

	public function body(string $modulType): void
	{
		?>
		<div id="ve_page_selector_bg"></div>
		<div id="ve_page_selector">
			<a class="ve_close_page_selector" href="#">
				<?php echo __('Seznam stránek', 'cms_ve'); ?>
				<span><?php echo mw_icon('icon-x'); ?></span>
			</a>
			<div id="ve_page_selector_content" data-modul="<?php echo $modulType; ?>" data-loaded=''>
				<div class="miocms_loading"></div>
			</div>
		</div>
		<?php
	}

	private function loadPages(): void
	{
		$pages = mwPage::getPages([
				'post_status' => 'publish,private,draft,future,pending,trash',
		]);
		foreach ($pages as $page) {
			$this->_pages[$page->getParentId()][$page->getId()] = $page->toPageSelectorItem();
			$this->_pageIcons[$page->getId()] = self::getPageIcons($page);
		}
	}

	public function content(): void
	{
		$currentTabId = $_POST['modul_type'];
		$currentItemId = $_POST['post_id'];

		$this->loadPages();

		// Write tabs
		echo '<ul class="ve_page_selector_tabs">';
		ksort($this->_tabs);
		foreach ($this->_tabs as $tab) {
			echo '<li><a ' . ($tab->getId() === $currentTabId ? 'class="active"' : '') . ' data-target="' . $tab->getId() . '" href="#">' . $tab->getTitle() . '</a></li>';
		}
		echo '<li class="ve_page_search_container">';
		echo '<input id="ve_page_search" autocomplete="off" type="text" name="page_search" />';
		echo '<span>' . mw_icon('icon-search') . '</span>';
		echo '</li>';
		echo '</ul>';

		// Write page select
		echo '<div class="cms_clear"></div>';
		echo '<div id="ve_page_list" class="mw_scroll">';
		echo '<a class="ve_page_list_home" href="' . get_home_url() . '">' . __('Úvodní stránka', 'cms_ve') . '</a>';

		$order = 1;
		$webTab = null;
		foreach ($this->_tabs as $tab) {
			if ($tab->getId() === 'web') {
				$webTab = $tab;
			} else {
				$this->_lists += $tab->service()->getLists($this->_pages, $this->_pageIcons, $order);
			}
		}
		if ($webTab !== null) {
			$this->_lists += $webTab->service()->getLists($this->_pages, $this->_pageIcons, $order);
		}

		ksort($this->_lists);
		foreach ($this->_lists as $list) {
			$list->print($currentTabId, $currentItemId, $this->_pageIcons);
		}

		echo '<div id="ve_pagelist_empty_search">' . __('Nebyla nalezena žádná stránka', 'cms_ve') . '</div>';
		echo '</div>';
		die();
	}

	public static function getPostIcons(mwPost $post): array
	{
		$icons = [];

		// redirect
		if ($post->isRedirected()) {
			$icons[] = [
					'icon' => 'arrow-right-circle',
					'text' => __('Přesměrování', 'cms_ve') . ': ' . $post->getRedirectUrl(),
			];
		}

		// private status
		if (($post->getStatus() === 'private')) {
			$icons[] = [
					'icon' => 'eye-off',
					'text' => __('Soukromé', 'cms_ve'),
			];
		} elseif ($post->isPasswordProtected()) {
			// password protected
			$icons[] = [
					'icon' => 'shield',
					'text' => __('Chráněné heslem', 'cms_ve'),
			];
		}

		return $icons;
	}

	public static function getPageIcons(mwPage $page): array
	{
		$icons = [];

		// front page
		if ($page->isFrontPage()) {
			$icons[] = [
					'icon' => 'home',
					'text' => __('Domovská stránka', 'cms_ve'),
			];
		}

		// blog home page
		if ($page->isBlogFrontPage()) {
			$icons[] = [
					'icon' => 'feather',
					'text' => __('Úvodní stránka blogu', 'cms_ve'),
			];
		}

		return array_merge($icons, self::getPostIcons($page));
	}

	/** @return mwPageSelector */
	public static function instance(): self
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

}
