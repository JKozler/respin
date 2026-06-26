<?php
class mwPageSelectorItem
{

	private string $_title;

	private int $_id;

	private string $_type;

	private string $_status;

	private string $_url;

	private array $_actions;

	private array $_icons;

	public function __construct(array $item)
	{
		$this->_title = $item['title'];
		$this->_id = $item['id'];
		$this->_type = $item['type'];
		$this->_status = $item['status'];
		$this->_url = $item['url'];
		$this->_actions = $item['actions'] ?? [];
		$this->_icons = $item['icons'] ?? [];
	}

	public function getTitle(): string
	{
		return $this->_title;
	}

	public function getFinallTitle(): string
	{
		$title = $this->_title ?: __('(bez názvu)', 'cms_ve');
		$title .= $this->_status === 'draft' ? ' (' . __('draft') . ')' : '';

		return $title;
	}

	public function getId(): int
	{
		return $this->_id;
	}

	public function getType(): string
	{
		return $this->_type;
	}

	public function getStatus(): string
	{
		return $this->_status;
	}

	public function getUrl(): string
	{
		return $this->_url;
	}

	public function getActions(): array
	{
		return $this->_actions;
	}

	public function addIcon(array $icon): array
	{
		return $this->_icons[] = $icon;
	}

	public function setIcons(array $icons): array
	{
		return $this->_icons = $icons;
	}

	public function getIcons(): array
	{
		return $this->_icons;
	}

	public function print(int $currentItemId, array $childs, array $icons, bool $parentTrashed = false): string
	{
		$subitems = $this->printSubitems($childs, $currentItemId, $icons, $parentTrashed);

		// Do not render pages in trash
		if ($this->getStatus() === 'trash') {
			return $subitems;
		}

		if ($this->_type === 'page') {
		$this->setIcons($icons[$this->getId()] ?? []);
		}

		$current = $currentItemId === $this->getId() ? true : false;
		$option = '<li><div class="ve_page_item_container"><a class="ve_page_item ' . ($current ? 've_page_item_current' : '') . '" data-slug="' . $this->getTitle() . '" title="' . $this->getUrl() . '" href="' . $this->getUrl() . '"  ' . ($current ? 'class="selected"' : '') . '>';
		$option .= '<span class="ve_page_item_title"> ' . $this->getFinallTitle() . '</span>';
		$option .= $this->getStatus() == 'future' ? $this->printIcon('clock', __('Naplánované', 'cms_ve')) : '';
		$option .= $parentTrashed ? $this->printIcon('alert-triangle', __('Rodičovská stránka je v koši', 'cms_ve'), 'icon-warning') : '';
		$option .= $this->printIcons();
		$option .= '</a>';
		$option .= $this->printActions($current);
		$option .= '</div>';
		$option .= $subitems;
		$option .= '</li>';

		return $option;
	}

	public function printActions(bool $current): string
	{
		$actions = '<div class="ve_ps_icons_container">';
		foreach ($this->getActions() as $action) {
			if ($action === 'delete') {
				$actions .= '<a class="mw_icon ve_ps_icon ve_delete_page" href="#" data-current="' . ($current ? 1 : 0) . '" data-id="' . $this->getId() . '" data-objectid="' . $this->getType() . '" title="' . __('Smazat', 'cms_ve') . '">' . mw_icon('icon-trash-2') . '</a>';
			} elseif ($action === 'copy') {
				$actions .= '<a class="mw_icon ve_ps_icon mw_duplicate_page" data-id="' . $this->getId() . '"  data-objectid="' . $this->getType() . '" href="#" title="' . __('Duplikovat stránku', 'cms_ve') . '" data-title="' . __('Duplikovat', 'cms_ve') . '">' . mw_icon('icon-copy') . '</a>';
			}
		}
		$actions .= '</div>';

		return $actions;
	}

	public function printSubitems($childs, $currentItemId, $icons, $parentTrashed): string
	{
		$content = '';
		$itemChilds = $childs[$this->getId()] ?? [];
		if (count($itemChilds)) {
			$closestParentTrashed = $this->getStatus() === 'trash';
			$parentTrashed = $parentTrashed ?: $closestParentTrashed;
			$content .= (!$closestParentTrashed ? '<ul class="ve_ps_subpages">' : '');

			foreach ($itemChilds as $item) {
				$content .= $item->print($currentItemId, $childs, $icons, $parentTrashed);
			}

			$content .= (!$closestParentTrashed ? '</ul>' : '');
		}

		return $content;
	}

	public function printIcons(): string
	{
		$icons = '';
		foreach ($this->getIcons() as $icon) {
			$icons .= $this->printIcon($icon['icon'], $icon['text'], $icon['class'] ?? '');
		}

		return $icons;
	}

	public function printIcon($icon, $text = '', $class = '')
	{
		$icons = '<div class="ve_page_list_item_icon' . ($class ? ' ' . $class : '') . '">';
		$icons .= mw_icon('icon-' . $icon);
		if ($text) {
			$icons .= '<span>' . $text . '</span>';
		}
		$icons .= '</div>';

		return $icons;
	}

}
