<?php
use Mioweb\VisualEditor\Lib\Colors;

class mwAdminComponents
{

	function __construct()
	{
	}
	const WEIGHT_UNIT = 'kg';

	public static function title($args, $tag = 'h4')
	{
		$content = '';
		$content .= '<div class="mw_title_container mw_title_container_' . $tag . '">';
		$content .= '<div class="mw_title_container_inner">';
		$content .= '<' . $tag . ' class="mw_title">' . $args['text'] . '</' . $tag . '>';

		if (isset($args['onright'])) {
			$content .= '<div class="mw_title_right_content">';
			$content .= $args['onright'];
			$content .= '</div>';
		}
		$content .= '</div>';
		if (isset($args['description']) && $args['description']) {
			$content .= '<div class="mw_title_description">' . $args['description'] . '</div>';
		}
		$content .= '</div>';

		return $content;
	}

	public static function table($args, $class = '')
	{
		if (!isset($args['rows']) || !count($args['rows'])) {
			$class .= ' empty';
		}

		$bulk = (isset($args['bulk']) && $args['bulk']);

		$content = '<table class="mw_table ' . $class . '">';
		if (isset($args['head'])) {
			$content .= '<thead>';
			$content .= '<tr>';

			if ($bulk) {
				$content .= '<th class="mw_table_bulk_col">';
				$content .= mwAdminComponents::checkbox([
					'style' => 'blue',
				]);
				$content .= '</th>';
			}

			foreach ($args['head'] as $col) {
				$col_class = $col['class'] ?? '';
				if (isset($col['align']) && $col['align']) {
					$col_class .= ' mw_align_' . $col['align'];
				}
				$content .= '<th ' . ($col_class ? 'class="' . $col_class . '"' : '') . '>' . $col['content'] . '</th>';
			}
			$content .= '</tr>';
			$content .= '</thead>';
		}
		$content .= '<tbody>';
		if (isset($args['rows'])) {
			foreach ($args['rows'] as $row) {
				$content .= self::tabletr($row, $bulk);
			}
		}

		$content .= '<tr class="mw_table_empty_info">';
		$content .= '<td colspan="' . count($args['head']) . '">' . ($args['empty_content'] ?? __('Seznam je prázdný', 'cms')) . '</td>';
		$content .= '</tr>';

		$content .= '</tbody>';
		$content .= '</table>';

		if (isset($args['html_after'])) {
			$content .= $args['html_after'];
		}

		return $content;
	}
	public static function tabletr($row, $bulk = false)
	{
		$content = '<tr ' . (isset($row['class']) && $row['class'] ? 'class="' . $row['class'] . '"' : '') . '>';
		if ($bulk) {
			$content .= '<td class="mw_table_bulk_col">';
			$content .= mwAdminComponents::checkbox([
				'name' => 'bulk[]',
				'value' => $row['bulk_id'],
				'style' => 'blue',
			]);
			$content .= '</td>';
		}
		foreach ($row['cols'] as $col) {
			$col_class = $col['class'] ?? '';
			if (isset($col['align']) && $col['align']) {
				$col_class .= ' mw_align_' . $col['align'];
			}
			$content .= '<td ' . ($col_class ? 'class="' . $col_class . '"' : '') . '>' . $col['content'] . '</td>';
		}
		$content .= '</tr>';

		return $content;
	}

	// modal header
	public static function modalHead($args, $class = '')
	{
		if (isset($args['style'])) {
			$class .= ' mw_modal_head_style_' . $args['style'];
		}

		$content = '<div class="mw_modal_head ' . $class . '">';

		$content .= '<div class="mw_modal_title">';
		if ($args['back']) {
			$content .= '<a href="' . ($args['close_link'] ?? '#') . '" class="mw_icon ' . ($args['close_class'] ?? '') . '">' . mw_icon('icon-menu') . '</a>';
		}
		$content .= '<span>' . $args['title'] . '</span>';
		$content .= '</div>';

		if (isset($args['menu'])) {
			$content .= '<ul>';
			$i = 0;
			foreach ($args['menu'] as $menu) {
				$class = $i == 0 ? 'active' : '';
				if (isset($menu['class'])) {
					$class .= $menu['class'];
				}
				$content .= '<li>';
				$content .= '<a class="' . $class . '" href="' . $menu['link'] . '">' . $menu['text'] . '</a>';
				$content .= '</li>';
				$i++;
			}
			$content .= '</ul>';
		}

		if (!isset($args['hide_close'])) {
			$content .= '<a href="' . ($args['close_link'] ?? '#') . '" class="mw_close_icon ' . ($args['close_class'] ?? '') . '">' . mw_icon('icon-x') . '</a>';
		}

		$content .= '</div>';

		return $content;
	}

	// modal footer
	public static function saveBar($args, $class = '')
	{
		$content = '<div class="mw_setting_footer_bar ' . $class . '">';
		$content .= self::button([
			'button_text' => $args['save_button_text'] ?? __('Uložit', 'cms'),
			'icon' => 'save',
		], $args['save_button_class'] ?? 'mw_setting_save_but');
		if (!isset($args['hide_storno'])) {
			$content .= self::button([
				'button_text' => __('Storno', 'cms'),
				'style' => 'secondary_gray',
				'icon' => 'x',
			], 'mw_setting_storno_but');
		}
		$content .= '</div>';

		return $content;
	}

	public static function inputLabel($args, $class = '')
	{
		$tooltip = isset($args['tooltip']) && $args['tooltip'] ? self::tooltip(['text' => $args['tooltip']]) : '';
		$content = '<div class="label ' . $class . '">' . $args['label'] . $tooltip . '</div>';

		return $content;
	}
	public static function inputSublabel($args, $class = '')
	{
		$content = '<div class="sublabel ' . $class . '">' . $args['label'] . '</div>';

		return $content;
	}

	// select
	public static function select($args, $val = '', $class = '')
	{
		$content = '';

		if (isset($args['label']) && $args['label']) {
			$content .= self::inputLabel(['label' => $args['label']]);
		} elseif (isset($args['sublabel']) && $args['sublabel']) {
			$content .= self::inputSublabel(['label' => $args['sublabel']]);
		}

		if (isset($args['whisperer']) && $args['whisperer']) {
			$class .= ' mw_whisperer';
		}

		$content .= '<select class="mw_select ' . $class . '" name="' . $args['name'] . '" id="' . ($args['tag_id'] ?? '') . '" autocomplete="off">';
		if (isset($args['with_empty']) && $args['with_empty']) {
			$content .= '<option value=""' . ($val === '' ? ' selected="selected"' : '') . '></option>';
		}
		foreach ($args['options'] as $option) {
			$attrs = $option['attrs'] ?? '';
			$content .= '<option value="' . $option['value'] . '" ' . ($val == $option['value'] ? ' selected="selected"' : '') . ' ' . (isset($option['disabled']) && $option['disabled'] ? 'disabled="disabled"' : '') . ' ' . $attrs . '>' . $option['name'] . '</option>';
		}
		$content .= '</select>';

		return $content;
	}

	// input
	public static function input($args, $val = '', $class = '')
	{
		$content = '';

		if (isset($args['label'])) {
			$content .= self::inputLabel(['label' => $args['label']]);
		} elseif (isset($args['sublabel']) && $args['sublabel']) {
			$content .= self::inputSublabel(['label' => $args['sublabel']]);
		}

		$type = $args['type'] ?? 'text';

		$attrs = $args['attrs'] ?? '';
		$attrs .= isset($args['id']) && $args['id'] ? ' id="' . $args['id'] . '"' : '';
		$attrs .= ' autocomplete="' . ($args['autocomplete'] ?? 'off') . '"';
		$attrs .= isset($args['maxlength']) ? ' maxlength="' . $args['maxlength'] . '"' : '';
		$attrs .= isset($args['placeholder']) ? ' placeholder="' . $args['placeholder'] . '"' : '';
		$val = stripslashes($val);
		if (!($args['no_special_chars'] ?? false)) {
			$val = htmlspecialchars($val);
		}
		$content .= '<input class="mw_input ' . $class . '" type="' . $type . '" name="' . $args['name'] . '" value="' . $val . '" ' . $attrs . '/>';

		if (isset($args['desc'])) {
			$content .= '<span class="mw_description">' . $args['desc'] . '</span>';
		}

		return $content;
	}

	// input type number
	public static function inputNumber($args, $val = '', $class = '')
	{
		$args['type'] = 'number';

		$content = '<div class="mw_input_number_container ' . $class . '">';
		$args['attrs'] = isset($args['attrs']) ? $args['attrs'] . ' ' : '';
		$args['attrs'] .= 'step="' . ($args['step'] ?? '1') . '"';
		$args['attrs'] .= isset($args['min']) ? 'min="' . $args['min'] . '"' : '';
		$content .= self::input($args, $val);
		if (isset($args['unit'])) {
			$content .= '<span class="mw_input_number_unit">' . $args['unit'] . '</span>';
		}
		$content .= '</div>';

		return $content;
	}

	// checkbox
	public static function checkbox($args, $checked = false, $class = '')
	{
		$content = '';

		$id = isset($args['id']) && $args['id'] ? 'id="' . $args['id'] . '"' : '';
		$name = isset($args['name']) ? 'name="' . $args['name'] . '"' : '';
		$val = $args['value'] ?? '1';

		if (isset($args['style'])) {
			$class .= ' mw_checkbox_' . $args['style'];
		}

		$content .= '<input class="mw_checkbox ' . $class . '" type="checkbox" ' . $name . ' ' . $id . ' autocomplete="off" '
		. ' value="' . $val . '" ' . ($checked ? 'checked="checked"' : '') . ' />';

		if (isset($args['label'])) {
			$content = '<label class="mw_checkbox_label">' . $content . ' <span class="mw_checkbox_label_text">' . $args['label'] . '</span></label>';
		}

		return $content;
	}

	// textarea
	public static function textarea($args, $val = '', $class = '')
	{
		$content = '';

		if (isset($args['label'])) {
			$content .= self::inputLabel(['label' => $args['label']]);
		}

		$id = isset($args['id']) && $args['id'] ? 'id="' . $args['id'] . '"' : '';
		$content .= '<textarea class="cms_text_textarea ' . $class . '" name="' . $args['name'] . '" ' . $id . ' '
		. (isset($args['maxlength']) ? ' maxlength="' . $args['maxlength'] . '"' : '')
		. (isset($args['rows']) ? ' rows="' . $args['rows'] . '"' : '')
		. ' autocomplete="' . ($args['autocomplete'] ?? 'off') . '"'
		. '>' . htmlspecialchars(stripslashes($val)) . '</textarea>';

		if (isset($args['desc'])) {
			$content .= '<span class="mw_description">' . $args['desc'] . '</span>';
		}

		return $content;
	}

	// switch
	public static function switch($args, $checked = 0, $class = '')
	{
		$content = '';

		if (isset($args['label'])) {
			$content .= self::inputLabel(['label' => $args['label']]);
		}

		$id = isset($args['id']) && $args['id'] ? 'id="' . $args['id'] . '"' : '';

		$value = '1';
		if (isset($args['value'])) {
			$value = $args['value'];
		}
		if (!isset($args['switch_label'])) {
			$class .= ' mw_switch_nolabel';
		}
		$content .= '<div class="mw_switch_container ' . $class . '" ' . $id . '>';
		if (isset($args['switch_label'])) {
			$content .= '<div class="mw_switch_label">' . $args['switch_label'] . '</div>';
		}
		$content .= '<label class="mw_switch">';
		$content .= '<input class="cms_nodisp" autocomplete="off" type="checkbox" name="' . $args['name'] . '" ' . ($checked ? 'checked="checked"' : '') . ' value="' . $value . '" />';
		$content .= '<span class="mw_switch_slider"></span>';
		$content .= '</label>';
		$content .= '<div class="cms_clear"></div>';
		$content .= '</div>';

		return $content;
	}

	public static function statusSwitch($args, $val = '0', $class = '')
	{
		$content = '';
		$true = $args['true_val'] ?? '1';
		$false = $args['false_val'] ?? '0';

		if (isset($args['label'])) {
			$content .= self::inputLabel(['label' => $args['label']]);
		}

		$checked = $true === $val;

		$id = isset($args['id']) && $args['id'] ? 'id="' . $args['id'] . '"' : '';

		$content .= '<div class="mw_switch_container ' . $class . '" ' . $id . ' data-true="' . $true . '" data-false="' . $false . '">';
		if ((isset($args['switch_label']) && $args['switch_label']) || (isset($args['tooltip']) && $args['tooltip'])) {
			$tooltip = isset($args['tooltip']) && $args['tooltip'] ? self::tooltip(['text' => $args['tooltip']]) : '';
			$label = $args['switch_label'] ?? '';
			$content .= '<div class="mw_switch_label">' . $label . $tooltip . '</div>';
		}
		$content .= '<label class="mw_switch">';
		$content .= '<input class="cms_nodisp" autocomplete="off" type="checkbox" ' . ($checked ? 'checked="checked"' : '') . ' />';
		$content .= '<span class="mw_switch_slider"></span>';
		$content .= '</label>';
		$content .= '<input class="mw_status_switch_val" autocomplete="off" type="hidden" name="' . $args['name'] . '" value="' . ($checked ? $true : $false) . '" />';
		$content .= '<div class="cms_clear"></div>';
		$content .= '</div>';

		return $content;
	}

	public static function dateTimeInput($args, $val = [], $class = '')
	{
		$content = '';

		$id = isset($args['id']) && $args['id'] ? 'id="' . $args['id'] . '"' : '';

		$content .= '<div class="mw_datetime_field_container mw_flex_field ' . $class . '" ' . $id . '>';
		//date
		$content .= '<div class="mw_flex_field_col"><div class="sublabel">' . __('Datum', 'cms') . '</div>';
		$content .= mwAdminComponents::input([
			'name' => $args['name'] . '[date]',
		], (isset($val['date']) ? stripslashes($val['date']) : ''), 'cms_datepicker mw_datetime_field_date');
		$content .= '</div>';
		// hours
		$content .= '<div class="mw_flex_field_col"><div class="sublabel">' . __('Hodin', 'cms') . '</div>';
		$content .= '<select name="' . $args['name'] . '[hour]" autocomplete="off" class="mw_datetime_field_hour">';
		$hour = isset($val['hour']) ? intval($val['hour']) : 0;
		for ($i = 0; $i < 25; $i++) {
			$content .= '<option ' . ($hour == $i ? 'selected="selected"' : '') . ' value="' . $i . '">' . $i . '</option>';
		}
		$content .= '</select>';
		$content .= '</div>';
		// minutes
		$content .= '<div class="mw_flex_field_col"><div class="sublabel">' . __('Minut', 'cms') . '</div>';
		$content .= '<select name="' . $args['name'] . '[minute]" autocomplete="off" class="mw_datetime_field_minute">';
		$minute = isset($val['minute']) ? intval($val['minute']) : 0;
		for ($i = 0; $i < 60; $i++) {
			$content .= '<option ' . ($minute == $i ? 'selected="selected"' : '') . ' value="' . $i . '">' . $i . '</option>';
		}
		$content .= '</select>';
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	public static function dateInput($args, $val = '', $class = '')
	{
		return mwAdminComponents::input([
			'autocomplete' => 'off',
			'name' => $args['name'],
		], stripslashes($val), 'cms_datepicker');
	}

	// tooltip
	public static function tooltip($args, $class = '')
	{
		$icon = $args['icon'] ?? '?';
		$type = $args['type'] ?? 'icon';
		$align = $args['tooltip_align'] ?? 'right';
		$content = '<div class="mw_tooltip_container mw_tooltip_type_' . $type . ' mw_tooltip_align_' . $align . ' ' . $class . '">' . $icon . '<div class="mw_tooltip_info_text">' . $args['text'] . '</div></div>';

		return $content;
	}

	// button
	public static function button($args, $class = '')
	{
		if (isset($args['icon'])) {
			$class .= ' mw_button_wicon';
		}
		if (isset($args['style'])) {
			$class .= ' mw_button_style_' . $args['style'];
		}
		$link = $args['link'] ?? '#';
		$attrs = $args['attrs'] ?? '';

		return '<a class="mw_button ' . $class . '" href="' . $link . '" ' . $attrs . '>' . (isset($args['icon']) ? mw_icon('icon-' . $args['icon']) : '') . '<span>' . $args['button_text'] . '</span></a>';
	}

	// drop menu button
	public static function dropButton($args, $class = '')
	{
		$content = '<div class="mw_dropdown_button mw_dropdown_list ' . $class . '">';
		if (isset($args['type']) && $args['type'] = 'icon') {
			$icon = $args['icon'] ?? 'more-horizontal';
			$content .= self::iconLink(['icon' => $icon]);
		} elseif (isset($args['type']) && $args['type'] = 'link') {
			$content .= self::link($args);
		} else {
			$content .= self::button($args);
		}
		$content .= '<ul class="mw_dropdown_list_list mw_rounded">';
		foreach ($args['items'] as $item) {
			$content .= '<li>';
			$content .= self::link($item, $item['class'] ?? ''); //'<a '.(isset($item['class'])? 'class="'.$item['class'].'"' : '').' '.(isset($item['attributes'])? $item['attributes'] : '').' href="'.(isset($item['link'])? $item['link'] : '#').'">'.$item['text'].'</a>';
			$content .= '</li>';
		}
		$content .= '</ul>';
		$content .= '</div>';

		return $content;
	}

	// drop menu icon
	public static function dropIcon($args, $class = '')
	{
		$args['type'] = 'icon';

		return self::dropButton($args, $class . ' mw_dropdown_icon');
	}

	// drop menu link
	public static function dropLink($args, $class = '')
	{
		$args['type'] = 'link';

		return self::dropButton($args, $class . ' mw_dropdown_link');
	}

	// link
	public static function link($args, $class = 'mw_link')
	{
		$link = $args['link'] ?? '#';
		$target = isset($args['target']) && $args['target'] ? 'target="' . $args['target'] . '"' : '';
		$attrs = $args['attrs'] ?? '';
		if (isset($args['title'])) {
			$attrs .= ' title="' . $args['title'] . '"';
		}
		$content = '<a href="' . $link . '" class="' . $class . '" ' . $target . ' ' . $attrs . '>' . $args['text'] . '</a>';

		return $content;
	}

	// icon link
	public static function iconLink($args, $class = '')
	{
		$class .= ' mw_icon';
		$iconAlign = $args['icon_align'] ?? 'left';

		if (isset($args['text'])) {
			if ($iconAlign == 'right') {
				$args['text'] .= mw_icon('icon-' . $args['icon']);
				$class .= ' mw_icon_wtext_right';
			} else {
				$args['text'] = mw_icon('icon-' . $args['icon']) . $args['text'];
				$class .= ' mw_icon_wtext';
			}
		} else {
			$args['text'] = mw_icon('icon-' . $args['icon']);
		}
		$content = self::link($args, $class);

		return $content;
	}

	// link select
	public static function linkSelect($args, $val, $class = '')
	{
		$content = '<div class="mw_link_select ' . $class . '">';

		$current = $args['items'][$val] ?? $args['items'][''];

		if (isset($args['title'])) {
			$content .= '<div class="mw_link_select_label">' . $args['title'] . ':&nbsp;</div>';
		}

		$content .= '<div class="mw_link_select_container mw_dropdown_list">';
		$content .= self::iconLink([
			'text' => '<span class="mw_link_select_current_text">' . $current . '</span>',
			'icon' => 'chevron-down',
			'icon_align' => 'right',
		], 'mw_link_select_open');

		$content .= '<ul class="mw_dropdown_list_list mw_rounded">';
		foreach ($args['items'] as $itemKey => $item) {
			$content .= '<li>';
			$content .= '<a id="' . $itemKey . '"data-val="' . $itemKey . '" href="#">' . $item . '</a>';
			$content .= '</li>';
		}
		$content .= '</ul>';

		$content .= self::input([
			'type' => 'hidden',
			'name' => $args['name'],
		], $val, 'mw_link_select_value');

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	// icon
	public static function icon($args, $class = '')
	{
		$text = '';
		if (isset($args['text'])) {
			$text = $args['text'];
			$class .= ' mw_icon_wtext';
		}
		$attrs = $args['attrs'] ?? '';
		if (isset($args['title'])) {
			$attrs .= ' title="' . $args['title'] . '"';
		}

		return '<span class="mw_icon ' . $class . '" ' . $attrs . '>' . mw_icon('icon-' . $args['icon']) . $text . '</span>';
	}

	// icon
	public static function loadingIcon($class = '')
	{
		return '<span class="mw_icon mw_icon_loading">' . mw_content_icon_file('loading-t2', MW_UI_ICONS_URL . 'loading.svg') . '</span>';
	}

	// statistics box
	public static function statisticsMainBox($args, $class = '')
	{
		$class .= ' mw_statistics_main_box';
		$content = self::statisticsBox($args, $class);

		return $content;
	}

	// statistics box
	public static function statisticsBox($args, $class = '')
	{
		$content = '<div class="mw_statistics_box mw_rounded ' . $class . '">';
		$content .= mw_icon('icon-' . $args['icon']);
		$content .= '<div class="mw_statistics_box_texts">';
		$content .= '<div class="mw_statistics_box_val">' . $args['value'] . '</div>';
		$content .= '<div class="mw_statistics_box_text">' . $args['text'] . '</div>';
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	// range select for filters
	public static function rangeSelect($args, $class = '')
	{
		$selected = $args['selected'] ?? 'all';
		$content = '<div class="mw_range_select_container ' . $class . '">';
		$content .= '<span>' . __('Období', 'cms') . '</span>';
		$content .= '<select class="mw_range_select mw_input_w mw_input mw_input_rounded" autocomplete="off">'
			. '<option value="all" ' . ($selected == 'all' ? 'selected="selected"' : '') . '>' . __('Celé období', 'cms') . '</option>'
			. '<option value="today" ' . ($selected == 'today' ? 'selected="selected"' : '') . '>' . __('Dnes', 'cms') . '</option>'
			. '<option value="yesterday" ' . ($selected == 'yesterday' ? 'selected="selected"' : '') . '>' . __('Včera', 'cms') . '</option>'
			. '<option value="last-7-days" ' . ($selected == 'last-7-days' ? 'selected="selected"' : '') . '>' . __('Posledních 7 dní', 'cms') . '</option>'
			. '<option value="last-30-days" ' . ($selected == 'last-30-days' ? 'selected="selected"' : '') . '>' . __('Posledních 30 dní', 'cms') . '</option>'
			. '<option value="this-month" ' . ($selected == 'this-month' ? 'selected="selected"' : '') . '>' . __('Tento měsíc', 'cms') . '</option>'
			. '<option value="last-month" ' . ($selected == 'last-month' ? 'selected="selected"' : '') . '>' . __('Minulý měsíc', 'cms') . '</option>'
			. '<option value="this-year" ' . ($selected == 'this-year' ? 'selected="selected"' : '') . '>' . __('Tento rok', 'cms') . '</option>'
			. '<option value="last-year" ' . ($selected == 'last-year' ? 'selected="selected"' : '') . '>' . __('Minulý rok', 'cms') . '</option>'
			. '<option value="custom">' . __('Vlastní období', 'cms') . '</option>'
			. '</select>';
		$content .= '<div class="mw_range_select_custom">';
		$content .= '<div class="mw_range_select_input_container">';
		$content .= '<span>' . __('Od', 'cms') . '</span>';
		$content .= '<input autocomplete="off" class="mw_range_select_from" type="text" value="" />';
		$content .= '</div>';
		$content .= '<div class="mw_range_select_input_container">';
		$content .= '<span>' . __('Do', 'cms') . '</span>';
		$content .= '<input autocomplete="off" class="mw_range_select_to" type="text" value="" />';
		$content .= '</div>';
		$content .= self::iconLink([
			'icon' => 'arrow-right',
		], 'mw_range_select_custom_send');
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	// filter
	public static function filter($args, $class = '')
	{
		$content = '<div class="mw_statistic_filter ' . $class . '">';

		$content .= self::iconLink([
			'icon' => 'sliders',
			'title' => __('Filtrovat statistiky', 'cms'),
		], 'mw_statistic_filter_icon mw_funnel_tooltip');

		$content .= '<div class="mw_rounded mw_statistic_filter_inputs">';
		$content .= '<h4>' . __('Filtrovat podle', 'cms') . '</h4>';
		$content .= self::iconLink([
			'icon' => 'x',
		], 'mw_statistic_filter_close');
		$content .= '<form action="" method="post">';
		foreach ($args['items'] as $item) {
			$content .= '<div class="mw_statistic_filter_input mw_statistic_filter_input_' . $item['name'] . '">';

			if ($item['type'] != 'divider') {
				$content .= '<div class="mw_statistic_filter_label">' . $item['label'] . '</div>';
			}

			if ($item['type'] == 'text') {
				$content .= '<input class="mw_input" type="text" name="' . $item['name'] . '" value="" />';
			} elseif ($item['type'] == 'select') {
				$content .= '<select class="mw_input" name="' . $item['name'] . '">';
				$content .= '<option value="" selected="selected">' . $item['empty'] . '</option>';
				foreach ($item['options'] as $key => $val) {
					$content .= '<option value="' . $key . '">' . $val . '</option>';
				}
				$content .= '</select>';
			} elseif ($item['type'] == 'divider') {
				$content .= '<div class="mw_statistic_filter_divider"><span>' . $item['label'] . '</span></div>';
			}
			$content .= '</div>';
		}
		$content .= '<div class="mw_statistic_filter_buttons">';
		$content .= self::button([
			'button_text' => __('Filtrovat', 'cms'),
		], 'mw_statistic_filter_apply');
		$content .= self::link([
			'text' => __('Zrušit filtr', 'cms'),
		], 'mw_statistic_filter_reset mw_statistic_filter_reset_but');
		$content .= '</div>';
		$content .= '</div>';
		$content .= '</form>';
		$content .= '</div>';

		return $content;
	}

	public static function installatorTypeSelectItem($args, $input_name, $class = '')
	{
		$content = '<div class="mw_install_select_type_item ' . $class . '">';
		$content .= '<div class="mw_install_select_type_container">';
		$content .= self::icon([
			'icon' => $args['icon'],
		]);
		$content .= '<h3>' . $args['title'] . '</h3>';
		$content .= '<p>' . $args['desc'] . '</p>';
		$content .= self::button([
			'button_text' => __('Vybrat', 'cms'),
		], 'mw_installator_go_next mw_installator_select_input');
		$content .= self::input([
			'type' => __('radio', 'cms'),
			'name' => $input_name,
		], $args['value']);
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	public static function tabs($args, $current = '', $class = '')
	{
		$content = '<ul class="mw_tabs ' . $class . '">';
		$i = 1;
		foreach ($args['tabs'] as $tab) {
			$active = $current == $tab['id'] || ($current == '' && $i == 1) ? true : false;

			$content .= '<li class="' . $args['group'] . '_tab">';
			$content .= '<a data-group="' . $args['group'] . '" href="#' . $args['group'] . '_' . $tab['id'] . '" class="' . ($active ? 'active' : '') . '">';
			if (isset($tab['icon']) && $tab['icon']) {
				$content .= self::icon([
					'icon' => $tab['icon'],
				]);
			}
			$content .= $tab['name'];
			$content .= '</a>';
			if (isset($args['member']) && $args['member']) {
				$content .= '<input id="' . $args['group'] . '_' . $tab['id'] . '_radio" type="radio" name="' . $args['member'] . '" value="' . $tab['id'] . '" ' . ($active ? 'checked="checked"' : '') . '>';
			}
			$content .= '</li>';
			$i++;
		}
		$content .= '</ul>';

		return $content;
	}

	public static function templateItem($args, $class = '')
	{
		$but_class = $args['button_class'] ?? '';
		$content = '<div class="mw_template_item ' . $class . ' ' . ($args['selected'] ? 'selected' : '') . '">';

			$content .= '<div class="mw_template_item_image">';
				$content .= '<img src="' . $args['thumb_url'] . '" alt="' . $args['title'] . '"/>';
				$content .= '<div class="mw_template_item_overlay">';
				$content .= self::button([
					'button_text' => __('Vybrat šablonu', 'cms'),
				], 'mw_template_item_select ' . $but_class);
				if (isset($args['demo_url']) && $args['demo_url']) {
			$content .= self::button([
		'button_text' => __('Náhled', 'cms'),
		'style' => 'secondary',
		'attrs' => 'target="_blank"',
		'link' => $args['demo_url'],
			], 'mw_template_item_review');
				}
				$content .= '</div>';
			$content .= '</div>';

			$content .= '<input type="radio" name="template" value="' . $args['value'] . '" ' . ($args['selected'] ? 'checked="checked"' : '') . '>';

			$content .= '<p>' . $args['title'] . '</p>';

			$content .= self::icon([
				'icon' => 'check',
			], 'mw_template_item_selected_icon');

		$content .= '</div>';

		return $content;
	}

	// select page
	public static function selectPage($args, $val = '', $class = '')
	{
		$whisperer = $args['whisperer'] ?? true;
		$lazyLoading = ($args['lazy_loading'] ?? false) && $whisperer && wp_count_posts('page')->publish > 200;
		$showEmpty = $args['show_empty'] ?? true;

		$args['options'] = [];

		if ($showEmpty) {
			$args['options'][] = [
				'value' => '',
				'name' => $args['empty'] ?? '-',
				'attrs' => 'data-title="" data-url="' . get_home_url() . '"',
			];
		}

		if ($lazyLoading) {
			$class .= ' mw_select_page_lazy_loading';
			if ($val) {
				$selectedPage = mwPage::getOneById($val);
				if ($selectedPage !== null) {

					$depth = '';
					$parent_id = get_post_field('post_parent', $selectedPage->getId());

					while ($parent_id > 0) {
						$depth .= '&mdash;';
						$parent_id = get_post_field('post_parent', $parent_id);
					}

					$args['options'][] = [
						'value' => $selectedPage->getId(),
						'name' => $depth . ' ' . ($selectedPage->getName() ?: __('(bez názvu)', 'cms_ve')),
						'attrs' => 'data-title="' . $selectedPage->getName() . '" data-url="' . $selectedPage->getUrl() . '"',
					];
				}
			}
		} else {
			$page_args = [];
			if (isset($args['exclude']) && count($args['exclude'])) {
				$page_args['exclude'] = $args['exclude'];
			}
			$page_args['hierarchical'] = true;
			$pages = $args['pages'] ?? mwPage::getPages($page_args);
			$parent = [];
			$parent[0] = '';

			foreach ($pages as $page) {
				$parent[$page->getId()] = $parent[$page->getParentId()] . '&mdash;';
				$args['options'][] = [
					'value' => $page->getId(),
					'name' => $parent[$page->getParentId()] . ' ' . ($page->getName() ?: __('(bez názvu)', 'cms_ve')),
					'attrs' => 'data-title="' . $page->getName() . '" data-url="' . $page->getUrl() . '"',
				];
			}
		}

		$content = '';

		if (isset($args['label'])) {
			$content .= self::inputLabel(['label' => $args['label']]);
			$args['label'] = '';
		}

		$class .= $whisperer ? ' mw_whisperer' : '';

		$content .= '<div class="mw_item_selector mw_flex_field ' . ($val ? 'selected' : '') . '">';
		$content .= self::select($args, $val, $class . ' mw_select_page');

		if (isset($args['edit_button']) && $args['edit_button']) {
			$content .= self::iconLink([
				'icon' => 'edit-2',
				'title' => __('Upravit stránku', 'cms_ve'),
				'target' => '_blank',
				'link' => $val ? get_permalink($val) : '',
			], 'mw_icon_button mw_icon_button_edit');
		}
		if (isset($args['add_button']) && $args['add_button']) {
			$content .= self::iconLink([
				'icon' => 'plus',
				'title' => __('Přidat stránku', 'cms_ve'),
			], 'mw_icon_button mw_icon_button_add');
		}

		$content .= '</div>';

		return $content;
	}

	public static function selectPageOptions(): string
	{
		$parent = [];
		$parent[0] = '';
		$content = '';

		$pages = mwPage::getPages([
			'hierarchical' => true,
		]);

		foreach ($pages as $page) {
			$parent[$page->getId()] = $parent[$page->getParentId()] . '&mdash;';
			$content .= '<option value="' . $page->getId() . '" data-title="' . strip_tags($page->getName()) . '" data-url="' . $page->getUrl() . '">' . $parent[$page->getParentId()] . ' ' . strip_tags($page->getName()) . '</option>';
		}

		return $content;
	}

	public static function messageBox($message, $args = [], $class = '')
	{
		if (!isset($args['type'])) {
			$args['type'] = 'confirm';
		}

		$class .= ' mw_message_box_' . $args['type'];

		if ($args['type'] == 'confirm') {
			$class .= ' mw_message_box_wclose';
			$icon = 'check-circle';
		} elseif ($args['type'] == 'error') {
			$class .= ' mw_message_box_wclose';
			$icon = 'alert-circle';
		} elseif ($args['type'] == 'alert') {
			$icon = 'alert-circle';
		} elseif ($args['type'] == 'info_gray') {
			$class .= ' mw_message_box_info_gray';
			$icon = 'info';
		} else {
			$icon = 'info';
		}

		if (isset($args['close']) && $args['close']) {
			$class .= ' mw_message_box_wclose';
		}

		$content = '<div class="mw_message_box ' . $class . '">';
		$content .= self::icon([
			'icon' => $icon,
		], 'mw_message_box_icon');
		$content .= '<div class="mw_message_box_text">' . $message . '</div>';
		if (isset($args['close']) && $args['close']) {
			$content .= self::iconLink([
				'icon' => 'x',
			], 'mw_message_box_close');
		}
		$content .= '</div>';

		return $content;
	}

	public static function checker($checked = false, $args = [], $class = '')
	{
		if ($checked) {
			$class .= ' checked';
		}
		$content = '<div class="mw_checker ' . $class . '" ' . ($args['attrs'] ?: '') . '>';

		$content .= mwAdminComponents::iconLink([
			'icon' => 'x',
		], 'mw_checker_unchecked');
		$content .= mwAdminComponents::iconLink([
			'icon' => 'check',
		], 'mw_checker_checked');
		$content .= '</div>';

		return $content;
	}

	public static function clickSearch($args = [], $val = '', $class = '')
	{
		if ($val) {
			$class .= ' open filled';
		}

		$content = '<div class="mw_click_search mw_animated ' . $class . '">';
		$content .= mwAdminComponents::icon([
			'icon' => 'search',
		], 'mw_icon_search');
		$content .= mwAdminComponents::icon([
			'icon' => 'x',
		], 'mw_icon_close');
		$content .= mwAdminComponents::input([
			'placeholder' => __('Hledat', 'cms') . '...',
			'name' => $args['name'],
			'autocomplete' => 'off',
		], $val, 'mw_animated mw_input_w');
		$content .= '<a href="#">' . __('Hledat', 'cms') . '</a>';
		$content .= '</div>';

		return $content;
	}

	public static function statusField($args, $class = '')
	{
		if ($args['status'] == 'ok') {
			$icon = 'check';
		} elseif ($args['status'] == 'processing') {
			$icon = 'clock';
		} else {
			$icon = 'x';
		}

		$content = self::title([
			'text' => $args['title'],
			'onright' => $args['link'] ?? '',
		]);

		$content .= '<div class="mw_status_field mw_status_field_' . $args['status'] . ' ' . $class . '">';
		$content .= self::icon([
			'icon' => $icon,
		], 'mw_status_field_icon');
		$content .= self::loadingIcon();
		$content .= '<span>' . $args['text'] . '</span>';
		if (isset($args['list'])) {
			$content .= self::dropIcon([
				'icon' => 'chevron-down',
				'items' => $args['list'],
			]);
		}
		$content .= '</div>';

		return $content;
	}

	public static function statusSelect($args, $value = '', $class = '')
	{
		$currentStatus = $args['list'][$value] ?? ['status' => 'x', 'text' => __('Neznámý', 'cms')];

		$content = '<div class="mw_status_field_container ' . $class . '">';

		$content .= self::title([
			'text' => $args['title'],
			'onright' => $args['link'] ?? '',
		]);

		$content .= '<div class="mw_status_field mw_status_field_' . $currentStatus['status'] . '">';
		$content .= self::icon([
			'icon' => $currentStatus['icon'] ?? '',
		], 'mw_status_field_icon');
		$content .= self::loadingIcon();
		$content .= '<span class="mw_status_field_text">' . $currentStatus['text'] . '</span>';
		if (isset($args['list']) && isset($args['show_list'])) {
			$list = [];
			foreach ($args['list'] as $listLey => $listVal) {
				$list[] = [
					'text' => $listVal['text'],
					'class' => 'mw_status_field_change_stat',
					'attrs' => 'data-val="' . $listLey . '" data-text="' . $listVal['text'] . '"  data-status="' . $listVal['status'] . '"   data-icon="' . MW_UI_ICONS_URL . 'symbol-defs.svg#icon-' . $listVal['icon'] . '" ' . ($listVal['attrs'] ?? ''),
				];
			}
			$content .= self::dropIcon([
				'icon' => 'chevron-down',
				'items' => $list,
			]);
		}
		$content .= '</div>';

		if (isset($args['input']) && $args['input']) {
			$content .= mwAdminComponents::input([
				'type' => 'hidden',
				'name' => $args['input'],
			], $value);
		}

		$content .= '</div>';

		return $content;
	}

	public static function colorShow($args, $class = '')
	{
		$content = '<div class="mw_color_show ' . $class . '">';
		$content .= '<span class="mw_color_show_color" style="background-color: ' . ($args['color'] ?? 'transparent') . '"></span>';

		$content .= '</div>';

		return $content;
	}

	public static function hiddenSetting($args, $class = ''): string
	{
		$content = '<div class="mw_hidden_setting ' . $class . '">';
		$content .= '<div class="mw_hidden_setting_head mw_hidden_setting_open">';
		$content .= '<div class="mw_hidden_setting_label">' . $args['label'] . '</div>';
		$content .= '<div class="mw_hidden_setting_man">'
		. self::icon([
			'icon' => 'chevron-down',
		])
		. '</div>';
		$content .= '</div>';
		$content .= '<div class="mw_hidden_setting_container">';
		$content .= $args['content'];
		$content .= '</div>';
		$content .= '<div class="mw_hidden_setting_overlay mw_hidden_setting_close"></div>';
		$content .= '</div>'; //mw_hidden_setting

		return $content;
	}

	public static function imageUploader($args, $image, $class = ''): string
	{
		$status = $image->isEmpty() ? 'empty' : 'image';

		$content = '<div class="mw_image_uploader">';
		$content .= '<div class="mw_image_uploader_container ' . ($status == 'empty' ? 'mw_image_uploader_empty' : $status . '_used') . '">';
		$content .= '<div class="mw_image_uploader_image_container">';
		$content .= '<div style="width: 100%">';
		$content .= '<img src="' . $image->getUrl() . '" alt=""/>';
		$content .= '<div class="mw_image_uploader_upload mw_image_uploader_overlay">';
		$content .= '<div class="mw_image_uploader_control">';
		$content .= mwAdminComponents::iconLink([
			'icon' => 'trash-2',
			'title' => __('Smazat obrázek', 'cms'),
			'link' => '#',
		], 'mw_image_uploader_clear');
		$content .= '</div>';

		$content .= '<div class="mw_image_uploader_info">';
		$content .= '<span>+</span>';
		$content .= '<div>' . __('Vybrat obrázek', 'cms') . '</div>';
		$content .= '</div>';

		if (isset($args['position'])) {
			$pos_style = '';

			$pos = explode(' ', $image->getPosition());
			$pos_style = 'style="top: ' . $pos[1] . '; left: ' . $pos[0] . '"';

			$content .= '<div class="mw_image_uploader_position_drag" ' . $pos_style . '></div>';
		}

		$content .= '</div>';
		if (isset($args['noid'])) {
			$content .= '<input class="mw_image_uploader_image" type="hidden" value="' . $image->getImage() . '" name="' . $args['name'] . '"/>';
		} else {
			$content .= '<input class="mw_image_uploader_position" type="hidden" value="' . $image->getPosition() . '" name="' . $args['name'] . '[position]' . '"/>';
			$content .= '<input class="mw_image_uploader_image" type="hidden" value="' . $image->getImage() . '" name="' . $args['name'] . '[image]' . '"/>';
			$content .= '<input class="mw_image_uploader_imageid" type="hidden" value="' . $image->getId() . '" name="' . $args['name'] . '[imageid]' . '"/>';
			$content .= '<input class="mw_image_uploader_selected_size" type="hidden" value="' . $image->getSelectedSize() . '" name="' . $args['name'] . '[selected_size]' . '"/>';
		}
		$content .= '</div>';
		$content .= '</div>';

		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	// hierarchical list
	public static function hierarchicalList($args, $class = '')
	{
		$object = mwSetting()->getObject($args['object_id']);

		if (!count($args['list'])) {
			$class .= ' empty';
		}

		$content = '<ol class="mw_hierarchical_list mw_nestedsortable ' . $class . '" data-objectid="' . $args['object_id'] . '">';
		foreach ($args['list'] as $item) {
			$content .= self::printHierarchicalListItem($item, $object, $args['actions'] ?? []);
		}

		$content .= '<li class="mw_hierarchical_list_empty_info">';
		$content .= $args['empty_content'] ?? __('Seznam je prázdný', 'cms');
		$content .= '</li>';

		$content .= '</ol>';

		return $content;
	}
	public static function printHierarchicalListItem($item, $object, $actions)
	{
		$content = '<li class="mw_nestedsortable_item mw_hierarchical_list_item_container">';
		$content .= '<div class="mw_nestedsortable_item_wrap mw_hierarchical_list_item" data-id="' . $item['id'] . '">';
		$content .= '<div class="mw_hierarchical_list_item_title">';
		$content .= '<span class="mw_hierarchical_list_sort_icon"></span>';//mwAdminComponents::icon(['icon'=>'move'],'mw_hierarchical_list_sort_icon');
		$content .= $item['text'];
		$content .= '</div>';
		$itemActions = isset($item['actions']) && $item['actions'] ? $item['actions'] : $actions;
		$content .= mwSetting()->printSettingActions($itemActions, $item['id'], $object);
		$content .= '</div>';

		if (!empty($item['childs'])) {
			$content .= '<ol>';
			foreach ($item['childs'] as $item) {
				$content .= self::printHierarchicalListItem($item, $object, $actions);
			}
			$content .= '</ol>';
		}
		$content .= '</li>';

		return $content;
	}

	public static function textLabel($args, $class = '')
	{
		$style = '';
		if (isset($args['predefined_color'])) {
			$class .= ' mw_text_tag_' . $args['predefined_color'];
		} elseif ((bool) ($args['color'] ?? false)) {
			if (Colors::isLightColor($args['color'])) {
				$class .= ' mw_text_tag_light';
			}
			$style = 'style="background-color:' . $args['color'] . '"';
		} else {
			$class .= ' mw_text_tag_blue';
		}

		if (isset($args['close']) && $args['close']) {
			$class .= ' mw_text_tag_wicon';
			$args['text'] .= mwAdminComponents::iconLink([
				'icon' => 'x',
				'attrs' => $args['close_attrs'] ?? '',
			], '');
		}

		$content = '<div class="mw_text_tag ' . $class . '" ' . $style . '>' . $args['text'] . '</div>';

		return $content;
	}

	public static function gdprPurposeSelect($args, $val = 'necessary', $class = '')
	{
		return mwAdminComponents::select([
			'name' => $args['name'],
			'options' => [
				['name' => __('Nezbytný pro fungování', 'cms'), 'value' => 'necessary', 'attrs' => 'data-title="' . __('Nezbytný', 'cms') . '"'],
				['name' => __('Preferenční', 'cms'), 'value' => 'preferences', 'attrs' => 'data-title="' . __('Preferenční', 'cms') . '"'],
				['name' => __('Marketingový', 'cms'), 'value' => 'marketing', 'attrs' => 'data-title="' . __('Marketingový', 'cms') . '"'],
				['name' => __('Statistický', 'cms'), 'value' => 'analytics', 'attrs' => 'data-title="' . __('Statistický', 'cms') . '"'],
			],
		], $val, $class);
	}

	public static function statusPoint($args, $class = '')
	{
		return '<div class="mw_status_point mw_status_point_s' . $args['status'] . ' ' . $class . '">' . $args['content'] . '</div>';
	}


}
