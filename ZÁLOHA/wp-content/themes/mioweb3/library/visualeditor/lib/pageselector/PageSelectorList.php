<?php
class mwPageSelectorList
{

	private $_title;

	private $_tabId;

	private $_items = null;

	private $_childs = [];

	private $_usePageChilds;

	private bool $_excludeSearch;

	public function __construct(array $list, string $tabId)
	{
		$this->_title = $list['title'];
		$this->_items = $list['items'];
		$this->_childs = $list['childs'] ?? [];
		$this->_usePageChilds = $list['usePageChilds'] ?? false;
		$this->_excludeSearch = $list['excludeSearch'] ?? false;
		$this->_tabId = $tabId;
	}

	public function getTitle(): string
	{
		return $this->_title;
	}

	public function getTabId(): string
	{
		return $this->_tabId;
	}

	public function getItems(): ?array
	{
		return $this->_items;
	}

	public function getChilds(): array
	{
		return $this->_usePageChilds ? mwPageSelector()->getPages() : $this->_childs;
	}

	public function print(string $currentTabId, int $currentItemId, array $icons): void
	{
		echo '<div class="ve_page_selector_list ' . ($this->_excludeSearch ? 'exclude_search' : '') . ' ve_psl_all ve_psl_' . $this->getTabId() . ' ' . ($currentTabId != $this->getTabId() ? 've_nodisp' : '') . '">';
		if ($this->getTitle()) {
			echo '<a class="ve_page_list_name ve_pln_close" href="#">';
			echo $this->getTitle();
			echo '<span class="pln_close_icon">' . mw_icon('icon-minus') . '</span>';
			echo '<span class="pln_open_icon">' . mw_icon('icon-plus') . '</span>';
			echo '</a>';
		}
		echo '<ul class="ve_page_list">';
		foreach ($this->getItems() as $item) {
			echo $item->print($currentItemId, $this->getChilds(), $icons);
		}
		echo '</ul>';
		echo '</div>';
	}

}
