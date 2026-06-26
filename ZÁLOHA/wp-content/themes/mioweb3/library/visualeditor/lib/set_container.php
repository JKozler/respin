<?php

class mwElementsContainer
{

	// list of empty rows
	public $empty_rows = [];

	// list of predefinet rows
	public $rows = [];

	// setting of the row
	public $row_setting = [];

	// list of element groups
	public $element_groups = [];

	// list of elements and settings
	public $elements = [];

	// container for lists
	public $list = [];

	public function __construct()
	{
	}

	function add_element_groups($groups, $top = false)
	{
		$this->element_groups = $top ? array_merge($groups, $this->element_groups) : array_merge($this->element_groups, $groups);
	}


	function add_elements($elements, $group, $group_title = '')
	{
		$this->elements = array_merge($this->elements, $elements);
		if (!isset($this->element_groups[$group])) {
			$this->element_groups[$group]['elements'] = [];
			$this->element_groups[$group]['name'] = $group_title;
		}
		foreach ($elements as $key => $val) {
			$this->element_groups[$group]['elements'][] = $key;
		}
	}

	function add_element_set($element, $sets, $order = 0, $tabsetting = 0, $tab = false)
	{
		if ($order) {
			$i = 0;
			$new_set = [];
			if ($tab) {
				$oldset = $this->elements[$element]['tab_setting'];
			} else {
				$oldset = isset($this->elements[$element]['tab_setting']) ? $this->elements[$element]['tab_setting'][$tabsetting]['setting'] : $this->elements[$element]['setting'];
			}

			foreach ($oldset as $val) {
				if ($i + 1 == $order) {
					$new_set = array_merge($new_set, $sets);
					$new_set[$i + 1] = $val;
					$i++;
				} else {
					$new_set[$i] = $val;
				}
				$i++;
			}
			if ($tab) {
				$this->elements[$element]['tab_setting'] = $new_set;
			} else {
				if (isset($this->elements[$element]['tab_setting'])) {
					$this->elements[$element]['tab_setting'][$tabsetting]['setting'] = $new_set;
				} else {
					$this->elements[$element]['setting'] = $new_set;
				}
			}
		} else {
			if ($tab) {
				$this->elements[$element]['tab_setting'] = array_merge($this->elements[$element]['tab_setting'], $sets);
			} else {
				$this->elements[$element]['setting'] = array_merge($this->elements[$element]['setting'], $sets);
			}
		}
		//if($element=='image_gallery') print_r($this->elements[$element]['tab_setting']);
	}

	function add_element_set_options($element, $set, $options, $order = 0)
	{
		/*
		if($order) {
		$i=0;
		$new_set=array();
		if(isset($this->elements[$element]['setting'][$set])) {
		foreach($this->elements[$element]['setting'][$set]['options'] as $val) {
		if($i+1==$order) {
		$new_set=array_merge($new_set,$options);
		$new_set[$i+1]=$val;
		$i++;
		}
		else $new_set[$i]=$val;
		$i++;
		}
		$this->elements[$element]['setting'][$set]['options']=$new_set;
		}
		} */
		if (isset($this->elements[$element]['setting'])) {
			$setting = $this->elements[$element]['setting'];
			foreach ($setting as $key => $val) {
				if ($val['id'] == $set) {
					foreach ($options as $optkey => $opt) {
						$this->elements[$element]['setting'][$key]['options'][$optkey] = $opt;
					}
				}
			}
		} elseif (isset($this->elements[$element]['tab_setting'])) {
			foreach ($this->elements[$element]['tab_setting'] as $setkey => $setting) {
				foreach ($setting['setting'] as $key => $val) {
					if ($val['id'] == $set) {
						foreach ($options as $optkey => $opt) {
							$this->elements[$element]['tab_setting'][$setkey]['setting'][$key]['options'][$optkey] = $opt;
						}
					}
				}
			}
		}
	}

	function add_set_field($type, $array)
	{
		if (!isset($this->list[$type])) {
			$this->list[$type] = [];
		}
		$this->list[$type] = array_merge($this->list[$type], $array);
	}

	function add_rows($rows)
	{
		$this->rows[] = $rows;
	}

}
