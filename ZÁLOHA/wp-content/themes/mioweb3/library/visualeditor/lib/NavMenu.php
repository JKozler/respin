<?php

namespace Mioweb\VisualEditor\Lib;

class NavMenu
{

	private $wp_menu_id;

	private $flat_menu_items;

	private $nested_menu_items;

	/**
	 * Construct object with fetching WP menu items
	 *
	 * @param int|string $wp_menu_id ID of menu in Wordpress
	 */
	public function __construct($wp_menu_id)
	{
		$this->wp_menu_id = (int) $wp_menu_id;
		$this->flat_menu_items = wp_get_nav_menu_items($this->wp_menu_id);
	}

	/**
	 * Get all items in nested structure - parents have array with children
	 *
	 * @return array
	 */
	public function getNestedMenuItems()
	{
		if ($this->nested_menu_items === null) {
			$this->createNestedTreeFromFlat();
		}

		return $this->nested_menu_items;
	}

	/**
	 * Loop through list of flat items and move children one by one under parents
	 * This function assumes, that flat list is sorted as in tree structure!
	 */
	private function createNestedTreeFromFlat()
	{
		//Prepare every item for possible children
		$tree = $this->flat_menu_items;
		foreach ($tree as $key => $item) {
			$tree[$key]->children = [];
		}

		for ($i = count($tree) - 1; $i >= 0; $i--) {
			//Loop from the bottom of array

			$current_item = $tree[$i];
			$current_parent = $current_item->menu_item_parent;

			if ($current_parent !== '0') {
 //'0' -> root item
				//Look through tree and find the parent, add item to it
				foreach ($tree as $key => $item) {
					if ($item->ID == $current_parent) {
						array_unshift($tree[$key]->children, $current_item);

						break;
					}
				}

				//Delete current child from root of tree
				unset($tree[$i]);
			}
		}

		$this->nested_menu_items = $tree;
	}

}
