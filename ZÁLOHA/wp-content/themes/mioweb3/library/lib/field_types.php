<?php
// title
use Nette\Utils\Strings;
use Mioweb\Lib\Email;
use Mioweb\VisualEditor\Lib\Image;
use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\Lib\License;

function field_type_title($field, $meta)
{
	echo '<h4>' . $field['name'] . '</h4>';
}

// text
function field_type_text($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');

	$args = [
		'name' => getTagName($group_name, $field['id']),
		'id' => $group_id . '_' . $field['id'],
	];
	if (isset($field['required'])) {
		$args['required'] = 1;
	}
	if (isset($field['maxlength'])) {
		$args['maxlength'] = $field['maxlength'];
	}
	if (isset($field['placeholder'])) {
		$args['placeholder'] = $field['placeholder'];
	}
	if (isset($field['save']) && $field['save'] === 'option') {
		$args['no_special_chars'] = true;
	}

	echo mwAdminComponents::input($args, $content);
}

//textarea
function field_type_textarea($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	cms_generate_field_textarea(getTagName($group_name, $field['id']), $group_id . '_' . $field['id'], $content, '', $field);
}
function field_type_invoice_note($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	echo '<div class="mws_invoice_field_footer">';
	cms_generate_field_textarea(getTagName($group_name, $field['id']), $group_id . '_' . $field['id'], $content, '', $field);
	echo '</div>';
	echo MwVariables::variableListPop('invoice', __('Následující proměnné budou ve faktuře nahrazeny skutečnými daty konkrétní objednávky. Můžete tak do poznámek vložit veškeré potřebné informace o konkrétní objednávce.', 'mwshop'));
}
function cms_generate_field_textarea($name, $id, $content, $class = '', $field = [])
{
	$rows = '4';
	if (isset($field['rows'])) {
		$rows = $field['rows'];
	}
	echo '<textarea class="cms_text_textarea ' . $class . '" name="' . $name . '" id="' . $id . '" rows="' . $rows . '">' . htmlspecialchars(stripslashes($content)) . '</textarea>';
}



/*
function cms_generate_field_text($name, $id, $value, $class = '', $field = [])
{
	$autribut = '';
	if (isset($field['autocomplete'])) {
		$autribut = 'autocomplete="' . $field['autocomplete'] . '"';
	}
	if (isset($field['required'])) {
		$class .= ' required';
	}
	if (isset($field['disabled'])) {
		$autribut .= ' disabled="disabled"';
	}

	return '<input class="mw_input ' . $class . '" type="text" name="' . $name . '" id="' . $id . '" '
	. (isset($field['maxlength']) ? 'maxlength="' . htmlspecialchars(stripslashes($field['maxlength'])) . '"' : '')
	. (isset($field['placeholder']) ? 'placeholder="' . htmlspecialchars(stripslashes($field['placeholder'])) . '"' : '')
	. ' value="' . $value . '" ' . $autribut . ' />';
} */

// file
function field_type_file($field, $meta, $group_name, $group_id)
{
	$id = $group_id . '_' . $field['id'];
	echo tus()->initInput($id, $field['id'], ['application/zip', 'application/x-zip-compressed']);
}

// hidden input
function field_type_hidden($field, $meta, $group_name, $group_id)
{
	echo '<input type="hidden" autocomplete="off" id="' . $group_id . '_' . $field['id'] . '" name="' . $field['id'] . '" value="' . ($field['content'] ?? '') . '" />';
}

function field_type_hidden_input($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	echo '<input type="hidden" autocomplete="off" id="hidden_' . $field['id'] . '" name="' . $group_name . '[' . $field['id'] . ']" value="' . $content . '" />';
}

// info
function field_type_info($field, $meta, $group_id)
{
	if (!isset($field['color'])) {
		$field['color'] = 'gray';
	}
	$type = $field['color'] == 'gray' ? 'info_gray' : 'info';
	echo mwAdminComponents::messageBox($field['content'], [
		'type' => $type,
	]);
}

// static, non-editable
function field_type_static($field, $meta, $group_id)
{
	$content = $field['content'] ?? '';
	echo '<div class="cms_static">' . $content . '</div>';
}

/**
 * Generate NUMBER editor.
 *
 * @param array $field Supported options: id, content, unit, min, step, placeholder
 * @param array|int|null $meta Value of number
 * @param $group_id
 */
function field_type_number($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	$content = '<input class="mw_input mw_input_size_auto mw_input_size" autocomplete="off" type="number" name="' . $group_name . '[' . $field['id'] . ']"'
	. ' id="' . $group_id . '_' . $field['id'] . '"'
	. ' value="' . $content . '"'
	. (isset($field['placeholder']) ? ' placeholder="' . esc_attr($field['placeholder']) . '"' : '')
	. (isset($field['min']) ? ' min="' . esc_attr((float) $field['min']) . '"' : '')
	. (isset($field['step']) ? ' step="' . esc_attr((string) $field['step']) . '"' : '')
	. ' />';
	if (isset($field['unit'])) {
		$content .= ' ' . $field['unit'];
	}

	echo $content;
}

// id generator for elements savign and writing user data
function field_type_id_generator($field, $meta, $group_name, $group_id)
{
	$content = isset($meta) && $meta ? $meta : $field['id'] . '_' . md5(microtime());
	echo '<input type="hidden" autocomplete="off" name="' . $group_name . '[' . $field['id'] . ']" id="' . $group_id . '_' . $field['id'] . '" value="' . $content . '" />';
}

// size
function field_type_size($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? ['size' => '', 'unit' => 'px']);
	if (is_array($content)) {
		echo cms_generate_field_size($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $field);
	} else {
		echo cms_generate_field_simple_size($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $field);
	}
}

function cms_generate_field_size($name, $id, $value, $field, $units = ['px', '%'])
{
	if ($field['data_type'] ?? '' === 'float') {
		$value['size'] = str_replace('.', ',', $value['size']);
	}

	$content = '<div class="field_size_container mw_flex_field">';
	$content .= '<input class="mw_input mw_input_size" autocomplete="off" type="text" name="' . $name . '[size]" id="' . $id . '_size" value="' . $value['size'] . '" />';
	$content .= '<div class="field_size_unit">';
	if (!isset($field['unit'])) {
		$content .= '<select name="' . $name . '[unit]" id="' . $id . '_unit">';
		foreach ($units as $unit) {
			$content .= '<option value="' . $unit . '" ' . ($value['unit'] == $unit ? 'selected="selected"' : '') . '>' . $unit . '</option>';
		}
		$content .= '</select>';
	} else {
		$content .= '<span>' . $field['unit'] . '</span>';
	}
	$content .= '</div>';
	$content .= '</div>';

	return $content;
}

function cms_generate_field_simple_size($name, $id, $value, $field)
{
	if ($field['data_type'] ?? '' === 'float') {
		$value = str_replace('.', ',', $value);
	}

	$placeholder = isset($field['placeholder']) ? 'placeholder="' . htmlspecialchars(stripslashes($field['placeholder'])) . '"' : '';
	$content = '<div class="field_size_container mw_flex_field">';
	$content .= '<input class="mw_input mw_input_size"  autocomplete="off" type="text" name="' . $name . '" id="' . $id . '" ' .
	'value="' . $value . '" ' . $placeholder . ' /> ';
	$content .= '<div class="field_size_unit">';
	$content .= '<span>' . $field['unit'] . '</span>';
	$content .= '</div>';
	$content .= '</div>';

	return $content;
}

//password
function field_type_password($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	echo cms_generate_field_password($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], stripslashes($content));
}

function cms_generate_field_password($name, $id, $value, $class = '')
{
	return '<input class="mw_input ' . $class . '" type="password" autocomplete="new-" name="' . $name . '" id="' . $id . '" value="' . $value . '" />';
}

//date
function field_type_date($field, $meta, $name_id, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	if (isset($field['convert']) && $content) {
		$content = date('d.m.Y', $content);
	}

	echo mwAdminComponents::input([
		'autocomplete' => 'off',
		'name' => $name_id . '[' . $field['id'] . ']',
		'id' => $group_id . '_' . $field['id'],
	], stripslashes($content), 'cms_datepicker');

	//echo cms_generate_field_text($name_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], stripslashes($content), 'cms_datepicker', $field);
}

//datetime
function field_type_datetime($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');

	if (isset($field['convert']) && $content) {
		$content = [
			'date' => date('d.m.Y', $content),
			'hour' => date('G', $content),
			'minute' => date('i', $content),
		];
	}

	echo mwAdminComponents::dateTimeInput([
		'name' => $group_id . '[' . $field['id'] . ']',
	], $content);
}

//licence
function field_type_license($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	echo '<div class="mw_field_type_licence">';
	echo '<input class="mw_input" autocomplete="off" type="text" name="', $group_id, '[', $field['id'], ']" id="', $group_id, '_', $field['id'], '" value="', stripslashes($content), '" />';
	echo License::getStatusCode($content);
	echo '</div>';
}

//text editor
function field_type_editor($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];
	$content = $meta ?? ($field['content'] ?? '');
	//wp_editor(stripslashes($content), $group_id . '_' . $field['id'], ['textarea_name' => $group_id . '[' . $field['id'] . ']']);
	wp_editor(stripslashes($content), $id, [
		'textarea_name' => $name,
		'media_buttons' => false,
		'quicktags' => false,
		'tinymce' => [
			'plugins' => 'lists, paste, wordpress, link, wpdialogs, charmap',
			'toolbar1' => 'formatselect | bold italic strikethrough underline | alignleft aligncenter alignright | link unlink | bullist numlist | superscript subscript | outdent indent charmap',
			'toolbar2' => '',
			'init_instance_callback' => "function (editor) {
				editor.on('change', function () {
		            tinymce.triggerSave();
		        });
			}",
		],
	]);
}

//checkbox
function field_type_checkbox($field, $meta, $group_id, $tagid)
{
	$content = $meta ?? ($field['content'] ?? '');
	cms_generate_field_checkbox($group_id . '[' . $field['id'] . ']', $tagid . '_' . $field['id'], $content, $field['label']);
}

function cms_generate_field_checkbox($name, $id, $content, $label = '', $class = '')
{
	echo '<input value="1" autocomplete="off" type="checkbox" name="', $name, '" id="', $id, '"', ($content ? ' checked="checked"' : ''),
	($class ? ' class="' . $class . '"' : ''), ' />';
	if ($label) {
		echo '<label for="' . $id . '">' . $label . '</label>';
	}
}

// multiple checkbox

function field_type_multiple_checkbox($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	if (!$content) {
		$content = [];
	}

	foreach ($field['options'] as $key => $option) {
		echo '<div class="set_form_subrow">';
		$val = array_key_exists($option['value'], $content) ? 1 : 0;
		echo mwAdminComponents::switch([
			'name' => $group_name . '[' . $field['id'] . '][' . $option['value'] . ']',
			'id' => $field['id'] . '_' . $option['value'],
			'switch_label' => $option['name'],
		], $val);
		//cms_generate_field_switch($group_name . '[' . $field['id'] . '][' . $option['value'] . ']', $field['id'] . '_' . $option['value'], $val, ['label' => $option['name']]);
		echo '</div>';
	}
	echo '<input type="hidden" autocomplete="off" name="', $group_name, '[', $field['id'], '][is_saved]" value="1" checked="checked" />';
}

// * select
function field_type_select($field, $meta, $group_id, $tagid, $post_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	cms_generate_field_select($group_id . '[' . $field['id'] . ']', $tagid . '_' . $field['id'], $content, $field);
}

function cms_generate_field_select($name, $id, $content, $field)
{
	echo '<select name="' . $name . '" id="' . $id . '" ' . (isset($field['disabled']) ? 'disabled="disabled"' : '') . ' autocomplete="off">';

	$options = mw_is_lite_editor() && isset($field['options_lite']) ? $field['options_lite'] : $field['options'];

	foreach ($options as $option) {
		echo '<option value="' . $option['value'] . '" ' . ($content == $option['value'] ? ' selected="selected"' : '') . '>' . $option['name'] . '</option>';
	}
	echo '</select>';
}

//sidebar select
function field_type_sidebarselect($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	echo '<select name="', $group_id, '[', $field['id'], ']" id="', $group_id, '_', $field['id'], '">';
	foreach (MW()->sidebars as $option) {
		echo '<option value="', $option['id'], '" ', $content == $option['id'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
	}
	echo '</select>';
}

//link
function field_type_link($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	$target = ($field['target'] ?? true);
	cms_generate_field_link($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, '', $target);
}

function cms_generate_field_link($name, $id, $content, $class = '', $target = true)
{
	echo '<input class="mw_input ', $class, '" type="text" autocomplete="off" name="', $name, '[link]" id="', $id, '_link" value="', isset($content['link']) ? stripslashes($content['link']) : '', '" />';
	if ($target) {
		echo '<input value="1" type="checkbox" autocomplete="off" name="', $name, '[target]" id="', $id, '_target"', isset($content['target']) ? ' checked="checked"' : '', ' /><label for="', $id, '_target">', __('Otevřít v novém okně', 'cms'), '</label>';
	}
}

function field_type_page_link($field, $meta, $group_id, $tagid)
{
	$content = $meta ?? ($field['content'] ?? '');
	$target = ($field['target'] ?? true);

	if (!is_array($content)) {
		$old = $content;
		$content = [];
		$content['link'] = $old;
	}

	if (!isset($content['page'])) {
		$content['page'] = '';
		if (isset($content['link']) && $content['link']) {
			$content['use_url'] = 1;
		}
	}

	cms_generate_field_page_link($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, '', $target, $field);
}

function cms_generate_field_page_link($name, $id, $content, $class = '', $target = true, $field = [])
{
	?>
	<div class="field_link_container <?php if ($target) : ?>field_link_container_wt<?php endif; ?>">
		<div class="mw_flex_field">
			<div class="fl_page_selector_container <?php echo isset($content['use_url']) ? 'cms_nodisp' : ''; ?>">
				<?php
				echo mwAdminComponents::selectPage([
					'name' => $name . '[page]',
					'tag_id' => $id . '_page',
				], $content['page']);
				?>
			</div>

			<input class="mw_input fl_custom_url_container <?php echo !isset($content['use_url']) ? 'cms_nodisp' : ''; ?> <?php echo $class; ?>" type="text" autocomplete="off" name="<?php echo $name; ?>[link]"
				id="<?php echo $id; ?>_link"
				value="<?php echo isset($content['link']) ? stripslashes($content['link']) : ''; ?>"
				placeholder="<?php echo __('Url včetně http:// nebo https://', 'cms'); ?>"/>

			<?php if ($target) {
				echo '<label class="mw_switch_button" title="' . __('Otevřít v novém okně', 'cms_ve') . '">';
				echo '<input class="cms_nodisp field_link_target" autocomplete="off" type="checkbox" name="' . $name . '[target]" id="' . $id . '_target" ' . (isset($content['target']) ? 'checked="checked"' : '') . ' value="1" />';
				echo mwAdminComponents::icon([
					'icon' => 'external-link',
				], 'mw_switch_button_ico');
				echo '</label>';
			} elseif (isset($field['content']) && isset($field['content']['target'])) {
				echo '<input class="cms_nodisp field_link_target" autocomplete="off" type="checkbox" name="' . $name . '[target]" id="' . $id . '_target" checked="checked" value="1" />';
			} ?>
		</div>
		<div class="set_form_subrow">
			<?php
			$use_url = isset($content['use_url']) ? '1' : '0';
			cms_generate_field_switch($name . '[use_url]', $id . '_use_url', $use_url, ['label' => __('Zadat vlastní URL', 'cms')]);
			?>
		</div>
	</div>
	<?php
}

function field_type_permalink($field, $meta, $group_id, $tagid)
{
	$content = $meta ?? ($field['content'] ?? '');
	$placeholder = $field['placeholder'] ?? '';
	$nested_text = ($field['nested_text'] ?? '');
	$use_nested = (isset($content['use_nested']) ? true : false);
	$attrBaseUri = ' data-base-uri="' . htmlspecialchars(get_home_url()) . '"';
	$previewSpan = '<span class="field_permalink_preview" title="' . esc_attr(__('Náhled URL', 'mwshop')) . '"></span>';
	$script = '';

	$baseName = $group_id . '[' . $field['id'] . ']';
	$baseId = $group_id . '_' . $field['id'];

	echo '
    <div class="field_permalink_container field_permalink_id_' . $field['id'] . '" id="' . $baseId . '" ' . $attrBaseUri . '>
      <input type="text" name="' . $baseName . '[value]" autocomplete="off" id="' . $baseId . '_basic"
             ' . (!empty($placeholder) ? 'placeholder="' . esc_attr($placeholder) . '"' : '') . '
             value="' . ($content['value'] ?? '') . '"
             class="mw_input mw_input_third field_permalink_basic ' . ($use_nested ? 'cms_nodisp' : '') . '"
        >
      ';

	if ($nested_text) {
		$nestedParentPermalinkId = $field['nested_parent_permalink'] ?? '';
		$nested_placeholder = $nested_text && isset($field['nested_placeholder']) ? $field['nested_placeholder'] : '';
		echo '
        <input type="text" name="' . $baseName . '[value_nested]" autocomplete="off" id="' . $baseId . '_nested"
            ' . (!empty($nested_placeholder) ? 'placeholder="' . esc_attr($nested_placeholder) . '"' : '') . '
            value="' . ($content['value_nested'] ?? '') . '"
           ' . ((bool) $nestedParentPermalinkId ? 'data-parent-id="' . esc_attr($nestedParentPermalinkId) . '"' : '') . '
           class="mw_input mw_input_third field_permalink_nested ' . (!$use_nested ? 'cms_nodisp' : '') . '"
        >
        ' . $previewSpan
		. '<div class="cms_clear"></div>';
		cms_generate_field_checkbox(
			$baseName . '[use_nested]',
			$baseId . '_use_nested',
			($content['use_nested'] ?? ''),
			$nested_text,
			'field_permalink_use_nested'
		);
		$script .= 'jQuery(document).ready(function($) {
        $("#' . $baseId . '_nested").keyup();
        $("#' . $baseId . '_use_nested").change();
      });
      ';
	} else {
		echo $previewSpan;
		$script .= 'jQuery(document).ready(function($) {
        $("#' . $baseId . '_basic").keyup();
      });
      ';
	}
	echo '
    </div>
    ' . ($script ? '<script>' . $script . '</script>' : '');
}

//image select
function field_type_imageselect($field, $meta, $group_id, $tagid, $page_id)
{
	global $mwContainer;
	$content = $meta ?? ($field['content'] ?? '');
	$options = isset($field['list']) ? $mwContainer->list[$field['list']] : $field['options'];
	if (isset($field['empty'])) {
		$options = $field['empty'] + $options;
	}
	cms_generate_field_imageselect(getTagName($group_id, $field['id']), $tagid . '_' . $field['id'], $options, $content, $group_id);
}

function cms_generate_field_imageselect($name, $id, $fields, $content, $group_id = '')
{
	?>
	<div id="cms_image_select_<?php echo $id; ?>" class="cms_image_selector_container cms_image_select">
		<div class="cms_image_selected cms_open_image_selector">
			<?php
			$current = isset($fields[$content]) && is_array($fields[$content]) ? $fields[$content]['thumb'] : $fields[$content] ?? '';

			?>
			<div class="cms_image_select_container"><img src="<?php echo $current; ?>" alt=""/></div>
			<?php echo '<input type="hidden" autocomplete="off" class="cms_image_select_val" id="' . $id . '" name="' . $name . '" value="' . $content . '" />'; ?>
			</div>
			<div class="cms_image_selector_bg cms_close_image_selector"></div>
			<div class="cms_image_selector_items">
			<div class="cms_image_selector mw_scroll">
				<?php
				foreach ($fields as $key => $val) {
					echo '<div class="cms_is_item cms_is_item_' . $key . ' ' . ($content === $key ? 'cms_is_item_active' : '') . '">';
					$value = is_array($val) ? $val['thumb'] : $val;
					echo '<a class="" href="#" data-value="' . $key . '"><img src="' . $value . '" alt=""></a>';
					echo '</div>';
				}
				?>
				<div class="cms_clear"></div>
				<a href="#" class="mw_close_icon cms_close_image_selector"><?php echo mw_icon('icon-x'); ?></a>
			</div>
		</div>
	</div>
	<?php
}

//image option
function field_type_imageoption($field, $meta, $group_id, $tagid, $page_id)
{
	global $mwContainer;
	$content = $meta ?? ($field['content'] ?? '');
	$options = isset($field['list']) ? $mwContainer->list[$field['list']] : $field['options'];
	if (isset($field['custom'])) {
		cms_generate_field_imageoption_custom($group_id . '[' . $field['id'] . ']', $tagid . '_' . $field['id'], $options, $content);
	}
	cms_generate_field_imageoption(getTagName($group_id, $field['id']), $tagid . '_' . $field['id'], $options, $content, $field);
}

function cms_generate_field_imageoption($name, $id, $options, $content, $field = [], $echo = true)
{
	$class = '';
	if (isset($field['size'])) {
		$class .= 'imageoption_icon_size_' . $field['size'];
	}

	$output = '<div id="cms_image_options_' . $id . '" class="cms_style_options_container ' . $class . '">';
	$output .= '<div class="cms_style_options">';

	foreach ($options as $key => $val) {
		$output .= '<a class="cms_image_option_item ' . ($content == $key ? 'cms_current_image_option_item' : '') . '" href="#" >';
		if (isset($val['image'])) {
			$output .= '<img src="' . $val['image'] . '" alt="">';
		} elseif (isset($val['icon'])) {
			$output .= '<span>' . mw_icon('icon-' . $val['icon']) . '</span>';
		} elseif (isset($val['icon_set'])) {
			$output .= '<span><svg role="img"><use xlink:href="' . $val['icon_set'] . '"></use></svg></span>';
		}
		$output .= '<input type="radio" autocomplete="off" name="' . $name . '" value="' . $key . '" ' . ($content == $key ? 'checked="checked"' : '') . ' />';
		$output .= '</a>';
	}

	$output .= '</div>';
	$output .= '</div>';

	if ($echo) {
		echo $output;
	} else {
		return $output;
	}
}

function cms_generate_field_imageoption_custom($name, $id, $options, $content)
{
	?>
	<div id="cms_image_options_<?php echo $id; ?>" class="cms_style_options_container">
		<div class="cms_style_options">
		<?php
		$is_custom = true;

		foreach ($options as $key => $val) {
			if ($content == $key) {
				$is_custom = false;
			}
			echo '<a class="cms_image_option_item ' . ($content == $key ? 'cms_current_image_option_item' : '') . '" href="#" >';
			if (isset($val['image'])) {
				echo '<img src="' . $val['image'] . '" alt="">';
			} elseif (isset($val['icon'])) {
				echo '<span>' . mw_icon('icon-' . $val['icon']) . '</span>';
			} elseif (isset($val['icon_set'])) {
				echo '<span><svg role="img"><use xlink:href="' . $val['icon_set'] . '"></use></svg></span>';
			}
			echo '<input type="radio" autocomplete="off" name="' . $name . '" value="' . $key . '" ' . ($content == $key ? 'checked="checked"' : '') . ' />
	                </a>';
		}

		$custom_content = $is_custom ? $content : 0;

		echo '<a class="cms_image_option_item ' . ($is_custom ? 'cms_current_image_option_item' : '') . '" href="#" >';
		echo '<span><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-more-horizontal"></use></svg></span>';
		echo '<input class="cms_image_option_custom_val" autocomplete="off" type="radio" name="' . $name . '" value="' . $custom_content . '" ' . ($is_custom ? 'checked="checked"' : '') . ' />';
		echo '</a>';
		?>
		</div>
	<?php

	echo '<div class="cms_image_option_custom_container set_form_subrow ' . (!$is_custom ? 'cms_nodisp' : '') . '">';

	$setting = ['min' => '0', 'max' => '100', 'unit' => 'px'];
	if (isset($field['setting'])) {
		$setting = $field['setting'];
	}

	cms_generate_field_slider('', $id . '_size', $custom_content, ['setting' => $setting]);

	echo '</div>';

	?>
	</div>
	<?php
}

//icon select
function field_type_iconselect($field, $meta, $group_id, $tagid)
{
	$content = $meta ?? ($field['content'] ?? []);
	cms_generate_field_iconselect(getTagName($group_id, $field['id']), $tagid . '_' . $field['id'], $field, $content);
}

function cms_generate_field_iconselect($name, $id, $field, $content)
{
	if (isset($field['icons'])) {
		$simple_icons = true;
		$icons = $field['icons'];
		$ico = mw_content_icon_file($content['icon'], $icons[$content['icon']]);
		//file_get_contents($icons[$content['icon']].$content['icon'].".svg", true);
	} else {
		global $mwContainer;

		$simple_icons = false;
		$icon_set = $content['icon_set'] ?? 'awesome';
		$ico = '<svg role="img"><use xlink:href="' . MW_ICONS_URL . $icon_set . '/symbol-defs.svg#icon-' . $content['icon'] . '"></use></svg>';
		$icons = $mwContainer->list['iconsets'];
	}

	if (!isset($content['tab'])) {
		$content['tab'] = 'icon';
	}
	if (!isset($content['image'])) {
		$content['image'] = '';
	}
	if (!isset($content['icon_set'])) {
		$content['icon_set'] = '';
	}


	?>
	<div class="cms_simple_icon_selected_setting cms_icon_selector_container cms_icon_selector_container_<?php echo $content['tab']; ?>">

		<a class="cms_svg_icon_selector cms_open_style_selector">
			<div class="cms_icon_select_container"><?php echo $ico; ?></div>
			<?php echo '<input type="hidden" autocomplete="off" class="cms_icon_select_icon" name="' . $name . '[icon]" value="' . $content['icon'] . '" />'; ?>
			<input type="hidden" class="cms_icon_select_code" autocomplete="off" name="<?php echo $name . '[code]'; ?>" value='<?php echo $ico; ?>'/>
			<?php if (!$simple_icons) { ?>
						<input type="hidden" autocomplete="off" class="cms_icon_select_icon_set" name="<?php echo $name . '[icon_set]'; ?>"
							   value="<?php echo $icon_set; ?>"/>
			<?php } ?>
		</a>
		<div class="cms_style_selector_bg cms_close_style_selector"></div>
		<div class="cms_style_selector">
			<a href="#" class="mw_close_icon cms_close_style_selector"><?php echo mw_icon('icon-x'); ?></a>
			<div class="cms_style_selector_inner mw_scroll">
				<?php
				if ($simple_icons) {
					foreach ($icons as $key => $val) {
						echo '<div data-value="' . $key . '" class="cms_close_style_selector cms_icon_item ' . ($content['icon'] == $key ? 'cms_icon_item_active' : '') . '">';
						echo mw_content_icon_file($key, $val);
						echo '</div>';
					}
				} else {
					foreach ($icons as $key => $iconset) {
						foreach ($iconset['icons'] as $icon) {
							echo '<div class="cms_close_style_selector cms_icon_item ' . ($content['icon'] == $icon && $content['icon_set'] == $key ? 'cms_icon_item_active' : '') . '" data-set="' . $key . '" data-value="' . $icon . '">';
							echo '<svg role="img"><use xlink:href="' . MW_ICONS_URL . $key . '/symbol-defs.svg#icon-' . $icon . '"></use></svg>';
							echo '</div>';
						}
					}
				}
				?>
				<div class="cms_clear"></div>
			</div>
		</div>
	<?php if (isset($field['content']['color'])) {
		// color
		echo '<div class="set_form_subrow cms_icon_select_color">';
		echo '<div class="sublabel">' . __('Barva icony', 'cms') . '</div>';
		echo cms_generate_field_color($name . '[color]', $id . '_color', $content['color'], []);
		echo '</div>';
	} ?>
	<?php if (isset($field['content']['size'])) { ?>
			<div class="ve_half_set set_form_subrow cms_icon_select_size">
				<div class="sublabel"><?php echo __('Velikost ikony', 'cms'); ?></div>
		<?php
		$size = $content['size'] ?? '0';
		cms_generate_field_slider($name . '[size]', $id . '_size', $size, ['setting' => ['min' => '15', 'max' => '100', 'unit' => 'px']]);
		?>
			</div>
	<?php }

	if (isset($field['content']['image'])) {
		echo '<div class="cms_icon_select_image mw_image_uploader">';

		if (isset($content['image']['image']) && $content['image']['image']) {
			$image = substr($content['image']['image'], 0, 4) == 'http' ? $content['image']['image'] : site_url() . $content['image']['image'];
		} else {
			$image = '';
		}

		if (!is_array($content['image'])) {
			$content['image'] = ['image' => $content['image']]; // temporary
		}

		echo image_uploader($name . '[image]', $content['image'], $image, $field);

		echo '</div>';

		echo '<div class="cms_icon_select_switch">'
		. '<a class="cms_icon_select_switch_image" data-tab="image" href="#">' . __('Nahrát obrázek', 'cms') . '</a>'
		. '<a class="cms_icon_select_switch_icon" data-tab="icon" href="#">' . __('Použít iconu', 'cms') . '</a>'
		. '</div>';
	}
	echo '<input class="cms_icon_select_tab_input" autocomplete="off" type="hidden" id="' . $id . '_tab" name="' . $name . '[tab]" value="' . $content['tab'] . '" />';

	?>

	</div>


	<?php
}

//radio
function field_type_radio($field, $meta, $group_id, $tagid)
{
	$content = $meta ?? ($field['content'] ?? '');
	foreach ($field['options'] as $key => $option) {
		echo '<div class="cms_radio_container cms_radio_container_', $tagid, '_', $field['id'], '"><input type="radio" id="', $tagid, '_', $field['id'], '_', $key, '" name="', getTagName($group_id, $field['id']) , '" value="', $key, '"', $key == $content ? ' checked="checked"' : '', ' />';
		echo '<label for="', $tagid, '_', $field['id'], '_', $key, '"> ', $option, '</label></div>';
	}
	echo '<div class="cms_clear"></div>';
}

function image_uploader($name, $value, $image, $field, $noid = false)
{
	if ($image) {
		$status = 'image';
	} elseif (isset($value['pattern']) && $value['pattern']) {
		$status = 'pattern';
	} else {
		$status = 'empty';
	}

	$content = '<div class="mw_image_uploader_container ' . ($status == 'empty' ? 'mw_image_uploader_empty' : $status . '_used') . '" data-respect_size="' . (isset($field['respect_size']) && $field['respect_size'] ? '1' : '0') . '">';
	$content .= '<div class="mw_image_uploader_image_container">';
	$content .= '<div style="width: 100%">';
	$content .= '<img src="' . $image . '" alt=""/>';
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

	if (isset($field['type']) && $field['type'] == 'bgimage' || isset($field['content']['position'])) {
		$pos_style = '';

		if (!isset($value['position']) || !$value['position']) {
			$value['position'] = '50% 50%';
		}

		$pos = explode(' ', $value['position']);
		$pos_style = 'style="top: ' . $pos[1] . '; left: ' . $pos[0] . '"';

		$content .= '<div class="mw_image_uploader_position_drag" ' . $pos_style . '></div>';
	}

	$content .= '</div>';
	if ($noid) {
		$content .= '<input class="mw_image_uploader_image" autocomplete="off" type="hidden" value="' . ($value ?? '') . '" name="' . $name . '"/>';
	} else {
		$content .= '<input class="mw_image_uploader_position" autocomplete="off" type="hidden" value="' . ($value['position'] ?? '') . '" name="' . $name . '[position]' . '"/>';
		$content .= '<input class="mw_image_uploader_image" autocomplete="off" type="hidden" value="' . ($value['image'] ?? '') . '" name="' . $name . '[image]' . '"/>';
		$content .= '<input class="mw_image_uploader_imageid" autocomplete="off" type="hidden" value="' . ($value['imageid'] ?? '') . '" name="' . $name . '[imageid]' . '"/>';
		$content .= '<input class="mw_image_uploader_selected_size" autocomplete="off" type="hidden" value="' . ($value['selected_size'] ?? '') . '" name="' . $name . '[selected_size]' . '"/>';
		$content .= '<input class="mw_image_uploader_full_image" autocomplete="off" type="hidden" value="' . ($value['full_image'] ?? (isset($value['imageid']) && $value['imageid'] ? wp_get_attachment_image_url($value['imageid'], 'full') : '')) . '" name="' . $name . '[full_image]"/>';
	}
	$content .= '</div>';
	$content .= '</div>';

	if (isset($field['content']['pattern'])) {
		global $mwContainer;

		$content .= '<div class="mw_image_uploader_pattern_container">';

		$content .= '<div class="cms_image_selector_container cms_image_select">';
		$content .= '<div class="cms_image_selected cms_open_image_selector">';
		$content .= '<div class="cms_image_select_container mw_image_uploader_pattern" ' . (isset($value['pattern']) && $value['pattern'] ? 'style="background:url(' . $mwContainer->list['patterns'][$value['pattern']] . $value['pattern'] . '.jpg);"' : '') . '>';
		$content .= '<a class="mw_image_uploader_pattern_open cms_open_image_selector" href="#">' . __('Použít vzorek', 'cms') . '</a>';
		$content .= '<div class="mw_image_uploader_control">';
		$content .= mwAdminComponents::iconLink([
			'icon' => 'trash-2',
			'title' => __('Smazat obrázek', 'cms'),
			'link' => '#',
		], 'mw_image_uploader_clear');
		$content .= '</div>';
		$content .= '</div>';

		$content .= '<input type="hidden" autocomplete="off" class="cms_image_select_val mw_image_uploader_pattern_val" name="' . $name . '[pattern]" value="' . ($value['pattern'] ?? '') . '" />';

		$content .= '</div>';
		$content .= '<div class="cms_image_selector_bg cms_close_image_selector"></div>';
		$content .= '<div class="cms_image_selector_items">';
		$content .= '<div class="cms_image_selector mw_scroll">';

		foreach ($mwContainer->list['patterns'] as $key => $url) {
			$content .= '<div class="cms_is_item ' . (isset($value['pattern']) && $value['pattern'] === $key ? 'cms_is_item_active' : '') . '">';
			$content .= '<a class="" href="#" data-value="' . $key . '" data-pattern="' . $url . $key . '_p.jpg"><img src="' . $url . $key . '.jpg" alt=""></a>';
			$content .= '</div>';
		}

		$content .= '<div class="cms_clear"></div>';
		$content .= '<a href="#" class="mw_close_icon cms_close_image_selector">' . mw_icon('icon-x') . '</a>';
		$content .= '</div>';
		$content .= '</div>';
		$content .= '</div>';

		$content .= '</div>';
	}

	$content .= '</div>';

	return $content;
}

// background image
function field_type_bgimage($field, $meta, $name_id, $group_id, $post_id)
{
	global $vePage;

	$value = $meta ?? ($field['content'] ?? []);

	if (isset($value['image']) && $value['image']) {
		$image = substr($value['image'], 0, 4) == 'http' ? $value['image'] : site_url() . $value['image'];
	} else {
		$image = '';
	}

	if (isset($value['mobile']['image']) && $value['mobile']['image']) {
		$mobile_image = substr($value['mobile']['image'], 0, 4) == 'http' ? $value['mobile']['image'] : site_url() . $value['mobile']['image'];
	} else {
		$mobile_image = '';
	}
	if (isset($value['tablet']['image']) && $value['tablet']['image']) {
		$tablet_image = substr($value['tablet']['image'], 0, 4) == 'http' ? $value['tablet']['image'] : site_url() . $value['tablet']['image'];
	} else {
		$tablet_image = '';
	}

	$pat = isset($value['pattern']) && $value['pattern'] ? $value['pattern'] : '';

	if ($image || $mobile_image || $tablet_image) {
		$status = 'image';
	} elseif ($pat) {
		$status = 'pattern';
	} else {
		$status = 'empty';
	}

	if (!isset($value['overlay_color'])) {
		$value['overlay_color'] = '#158ebf';
	}

	$id = $group_id . '_' . $field['id'];
	$name = getTagName($name_id, $field['id']);

	$field['respect_size'] ??= true;

	?>
	<div class="mw_image_uploader <?php echo $status; ?>_used" id="mw_image_uploader_<?php echo $id; ?>">

	<?php
	$mobile = isset($field['mobile']) ? true : false;

	if ($mobile) {
		$mobile_field = $field;
		unset($mobile_field['content']['pattern']);

		echo MW()->create_sublabel(__('Obrázek', 'cms'), true);

		echo '<div class="desktop_setting desktop_device_set_container">';
		echo image_uploader($name, $value, $image, $field);
		echo '</div>';
		echo '<div class="tablet_setting tablet_device_set_container">';

		if (!isset($value['tablet'])) {
			$value['tablet'] = [];
		}
		echo image_uploader($name . '[tablet]', $value['tablet'], $tablet_image, $mobile_field);

		echo '</div>';
		echo '<div class="mobile_setting mobile_device_set_container">';
		if (!isset($value['mobile'])) {
			$value['mobile'] = [];
		}
		echo image_uploader($name . '[mobile]', $value['mobile'], $mobile_image, $mobile_field);
		echo '</div>';
		echo '<div class="mobile_setting mobile_device_set_container">';
		echo '<div class="set_form_subrow mw_bgimage_mobile_hide_container">';
		$mobile_hide = isset($value['mobile_hide']) ? 1 : 0;
		cms_generate_field_switch($name . '[mobile_hide]', $id . '_mobile_hide', $mobile_hide, ['label' => __('Skrýt na mobilu', 'cms_ve')]);
		echo '</div>';
		echo '</div>';
	} else {
		echo '<div class="desktop_setting">';
		echo image_uploader($name, $value, $image, $field);
		echo '</div>';
	}
	?>

		<div class="mw_image_uploader_setting <?php if (!$image) {
			echo 'mw_image_uploader_empty';
											  } ?>">

	<?php if (!isset($field['hide']) || !in_array('cover', $field['hide'])) { ?>
				<div class="desktop_setting desktop_device_set_container set_form_subrow mw_bgimage_cover_container">
		<?php
		$cover = isset($value['cover']) ? 1 : 0;
		cms_generate_field_switch($name . '[cover]', $id . '_cover', $cover, ['label' => __('Přizpůsobit obrazovce', 'cms_ve')]);
		?>
				</div>
	<?php } elseif (isset($field['content']['cover']) && $field['content']['cover']) { ?>
				<div class="mw_bgimage_cover_container cms_nodisp"><input name="<?php echo $name; ?>[cover]"
																		  type="checkbox" autocomplete="off" checked="checked" value="1">
				</div>
	<?php } ?>

	<?php if (!isset($field['hide']) || !in_array('color_filter', $field['hide'])) { ?>
				<div class="mw_bgimage_color_filter desktop_setting desktop_device_set_container">
					<div class="set_form_subrow">
		<?php
		$color_filter = isset($value['color_filter']) ? 1 : 0;
		cms_generate_field_switch($name . '[color_filter]', $id . '_color_filter', $color_filter, ['label' => __('Použít barevný filtr', 'cms_ve')]);
		?>
					</div>
					<div
						class="mw_bgimage_color_filter_val set_form_subrow <?php echo !$color_filter ? 'cms_nodisp' : ''; ?>">
		<?php
		cms_generate_field_transparent_color($name_id . '[' . $field['id'] . '][overlay_color]', $group_id . '_' . $field['id'] . '_overlay_color', $value['overlay_color']);
		//cms_generate_field_slider($name_id.'['.$field['id'].'][overlay_transparency]',$group_id.'_'.$field['id'].'_overlay_transparency',$value['overlay_transparency'], array('setting'=>array('min'=>'0','max'=>'100','unit'=>'%')));
		?>
					</div>
				</div>
	<?php } ?>
	<?php if (!isset($field['hide']) || !in_array('efect', $field['hide'])) { ?>
				<div class="set_form_subrow mw_bgimage_efect_container desktop_setting desktop_device_set_container">
					<select name="<?php echo $name_id . '[' . $field['id'] . ']'; ?>[efect]">
						<option <?php if (isset($value['efect']) && $value['efect'] == '') {
							echo 'selected="selected"';
								} ?>
							value=""><?php echo __('Efekt: žádný', 'cms'); ?></option>
						<option <?php if (isset($value['efect']) && $value['efect'] == 'fixed') {
							echo 'selected="selected"';
								} ?>
							value="fixed"><?php echo __('Efekt: fixní', 'cms'); ?></option>
		<?php if (!isset($field['hide']) || !in_array('paralax', $field['hide'])) { ?>
							<option <?php if (isset($value['efect']) && $value['efect'] == 'parallax') {
								echo 'selected="selected"';
									} ?>
								value="parallax"><?php echo __('Efekt: parallax', 'cms'); ?></option>
		<?php } ?>
					</select>
				</div>
	<?php } ?>
	<?php /*
	<div class="mw_bgimage_position_container set_form_subrow">
	<select name="<?php echo $name_id.'['.$field['id'].']'; ?>[position]">
	<option <?php if(isset($value['position']) && $value['position']=="center center") echo 'selected="selected"'; ?> value="center center"><?php echo __('Zarovnání: doprostřed', 'cms'); ?></option>
	<option <?php if(isset($value['position']) && $value['position']=="left top") echo 'selected="selected"'; ?> value="left top"><?php echo __('Zarovnání: nahoru doleva', 'cms'); ?></option>
	<option <?php if(isset($value['position']) && $value['position']=="center top") echo 'selected="selected"'; ?> value="center top"><?php echo __('Zarovnání: nahoru doprostřed', 'cms'); ?></option>
	<option <?php if(isset($value['position']) && $value['position']=="right top") echo 'selected="selected"'; ?> value="right top"><?php echo __('Zarovnání: nahoru doprava', 'cms'); ?></option>
	<option <?php if(isset($value['position']) && $value['position']=="left center") echo 'selected="selected"'; ?> value="left center"><?php echo __('Zarovnání: doprostřed doleva', 'cms'); ?></option>
	<option <?php if(isset($value['position']) && $value['position']=="right center") echo 'selected="selected"'; ?> value="right center"><?php echo __('Zarovnání: doprostřed doprava', 'cms'); ?></option>
	<option <?php if(isset($value['position']) && $value['position']=="left bottom") echo 'selected="selected"'; ?> value="left bottom"><?php echo __('Zarovnání:  dolů doleva', 'cms'); ?></option>
	<option <?php if(isset($value['position']) && $value['position']=="center bottom") echo 'selected="selected"'; ?> value="center bottom"><?php echo __('Zarovnání:  dolů doprostřed', 'cms'); ?></option>
	<option <?php if(isset($value['position']) && $value['position']=="right bottom") echo 'selected="selected"'; ?> value="right bottom"><?php echo __('Zarovnání: dolů doprava', 'cms'); ?></option>
	</select>
	</div>
	*/
	if (!isset($field['hide']) || !in_array('repeat', $field['hide'])) { ?>
				<div class="desktop_setting desktop_device_set_container">
					<div
						class="mw_bgimage_repeat_container set_form_subrow <?php if (isset($value['cover'])) {
							echo 'cms_nodisp';
																		   } ?>">
						<select name="<?php echo $name_id . '[' . $field['id'] . ']'; ?>[repeat]">
							<option <?php if (isset($value['repeat']) && $value['repeat'] == 'no-repeat') {
								echo 'selected="selected"';
									} ?>
								value="no-repeat"><?php echo __('Neopakovat', 'cms'); ?></option>
							<option <?php if (isset($value['repeat']) && $value['repeat'] == 'repeat') {
								echo 'selected="selected"';
									} ?>
								value="repeat"><?php echo __('Opakovat všemi směry', 'cms'); ?></option>
							<option <?php if (isset($value['repeat']) && $value['repeat'] == 'repeat-x') {
								echo 'selected="selected"';
									} ?>
								value="repeat-x"><?php echo __('Opakovat po ose X', 'cms'); ?></option>
							<option <?php if (isset($value['repeat']) && $value['repeat'] == 'repeat-y') {
								echo 'selected="selected"';
									} ?>
								value="repeat-y"><?php echo __('Opakovat po ose Y', 'cms'); ?></option>
						</select>
					</div>
				</div>
		<?php
	}
	// hide on mobile
	/*
	if((!isset($field['mobile_hide']) || !in_array('mobile_hide',$field['hide'])) && !$mobile) {
	echo '<div class="set_form_subrow mw_bgimage_mobile_hide_container">';
	$mobile_hide=(isset($value['mobile_hide']) && $value['mobile_hide']=='1')? 1:0;
	cms_generate_field_switch($name.'[mobile_hide]',$id.'_mobile_hide',$mobile_hide, array('label'=>__('Skrýt na mobilu','cms_ve')));
	echo '</div>';
	}*/
	// hide on mobile
	if (!isset($field['hide']) || !in_array('size', $field['hide'])) {
		echo '<div class="mw_bgimage_size_container ' . (isset($value['cover']) ? 'cms_nodisp' : '') . '">';

		echo '<div class="desktop_setting desktop_device_set_container set_form_subrow mw_bgimage_desktop_size_container">';
		$size = $value['size'] ?? '';
		cms_generate_field_slider($name . '[size]', $id . '_size', $size, ['setting' => ['min' => '0', 'max' => '2000', 'unit' => 'px', 'title' => __('Velikost pozadí', 'cms'), 'placeholder' => __('Auto', 'cms')]]);
		echo '</div>';

		if ($mobile) {
			echo '<div class="tablet_setting tablet_device_set_container set_form_subrow mw_bgimage_tablet_size_container">';
			$tablet_size = $value['tablet']['size'] ?? '';
			cms_generate_field_slider($name . '[tablet][size]', $id . '_tablet_size', $tablet_size, ['setting' => ['min' => '0', 'max' => '2000', 'unit' => 'px', 'title' => __('Velikost pozadí na tabletu', 'cms'), 'placeholder' => __('Auto', 'cms')]]);
			echo '</div>';

			echo '<div class="mobile_setting mobile_device_set_container set_form_subrow mw_bgimage_mobile_size_container">';
			$mobile_size = $value['mobile']['size'] ?? '';
			cms_generate_field_slider($name . '[mobile][size]', $id . '_mobile_size', $mobile_size, ['setting' => ['min' => '0', 'max' => '2000', 'unit' => 'px', 'title' => __('Velikost pozadí na mobilu', 'cms'), 'placeholder' => __('Auto', 'cms')]]);
			echo '</div>';
		}

		echo '</div>';
	}
	/*<div><input name="<?php echo $name_id.'['.$field['id'].']'; ?>[mobile_hide]" id="mobile_hide_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>" type="checkbox" value="mobile_hide" <?php if(isset($value['mobile_hide'])) echo 'checked="checked"'; ?>> <label for="mobile_hide_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Skrýt na mobilních zařízeních','cms'); ?></label></div> */ ?>
		</div>
		<div class="cms_clear"></div>

	</div>
	<?php
}

// new upload image
function field_type_image($field, $meta, $group_name, $tagid, $post_id)
{
	$content = $meta ?? ($field['content'] ?? []);
	echo cms_generate_field_image(getTagName($group_name, $field['id']), $tagid . '_' . $field['id'], $content, $field);
}

function cms_generate_field_image($name, $id, $value, $field = [])
{
	if (!is_array($value)) {
		$value = ['image' => $value];
	}
	if (isset($value['imageid']) && $value['imageid']) {
		$image = '';
		if (Image::existImage((int) $value['imageid'])) {
			$img = wp_get_attachment_image_src($value['imageid'], $value['selected_size'] ?? 'full');
			$image = $img[0] ?? '';
		}
	} elseif (isset($value['image']) && $value['image']) {
		$image = substr($value['image'], 0, 4) == 'http' ? $value['image'] : site_url() . $value['image'];
	} else {
		$image = '';
	}

	$content = '<div class="mw_image_uploader" id="mw_image_uploader_' . $id . '">';
	$content .= image_uploader($name, $value, $image, $field);
	$content .= '</div>';

	return $content;
}

function field_type_image_url($field, $meta, $group_name, $tagid, $post_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	echo cms_generate_field_image_url(getTagName($group_name, $field['id']), $tagid . '_' . $field['id'], $content, $field);
}

function cms_generate_field_image_url($name, $id, $value, $field)
{
	if (isset($value) && $value) {
		$image = substr($value, 0, 4) == 'http' ? $value : site_url() . $value;
	} else {
		$image = '';
	}
	$content = '<div class="mw_image_uploader" id="mw_image_uploader_' . $id . '">';
	$content .= image_uploader($name, $value, $image, $field, true);
	$content .= '</div>';

	return $content;
}

//Upload gallery
function field_type_image_gallery($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	cms_generate_field_upload_gallery(getTagName($group_name, $field['id']), $group_id . '_' . $field['id'], $content, $field);
}

function cms_generate_field_upload_gallery($name, $id, $value, $field)
{
	$editable = !isset($field['editable']) || $field['editable'];

	?>
	<div class="cms_image_gallery_container">
		<?php
		if (isset($field['empty_input'])) { // for saving empty gallery in shop after deleting all images
			echo '<input type="hidden" autocomplete="off" name="' . $name . '" value="">';
		}
		?>
		<div id="image_<?php echo $id; ?>" class="cms_uploaded_image cms_image_gallery <?php if (!$value) { echo 'cms_nodisp'; } ?>">
			<div class="cms_image_gallery__wrap">
			<?php
			$i = 0;
			if (!empty($value)) :
				foreach ($value as $image) :
					if (substr($image, 0, 4) == 'http') {
						$src = $image;
						$editable = false;
					} else {
						if (Image::existImage((int) $image)) {
							$image_src = wp_get_attachment_image_src($image, 'thumbnail');
							$src = $image_src[0];
						} else {
							$src = '';
						}
					}
					if ($src) {
						?>
						<div class="cms_image_gallery__item">
							<img src="<?php echo $src; ?>">
							<div class="mw_image_uploader_control">
								<?php
							if ($editable) {
									echo mwAdminComponents::iconLink([
										'icon' => 'edit-2',
										'title' => __('Upravit obrázek', 'cms'),
										'link' => '#',
									], 'cms_image_gallery__item__edit_button');
							}
								echo mwAdminComponents::iconLink([
									'icon' => 'trash-2',
									'title' => __('Smazat obrázek', 'cms'),
									'link' => '#',
								], 'cms_image_gallery__item__close_button');
								?>
							</div>
							<input type="hidden" autocomplete="off" name="<?php echo $name . '[' . $i . ']'; ?>" value="<?php echo $image; ?>">
						</div>
						<?php
						$i++;
					}
				endforeach;
			endif; ?>
			</div>
			<div class="cms_image_gallery__spinner"></div>
		</div>
		<?php
		echo mwAdminComponents::button([
			'icon' => 'plus',
			'button_text' => __('Přidat obrázky', 'cms'),
			'style' => 'secondary',
			'link' => '#',
			'attrs' => 'target="' . $id . '" data-name="' . $name . '" data-editable="' . ($editable ? '1' : '0') . '"',
		], 'cms_upload_gallery_button');
		?>
	</div>
	<?php
}

// upload file
function field_type_upload_file($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	$hideDelete = $field['hide_delete'] ?? false;
	cms_generate_field_upload_file(getTagName($group_name, $field['id']), $group_id . '_' . $field['id'], $content, $hideDelete);
}

function cms_generate_field_upload_file($name, $id, $value, $hideDelete = false)
{
	?>
	<div class="cms_upload_file_container mw_flex_field <?php if ($value) { echo 'cms_upload_file_uploaded'; } ?>">
		<input class="cms_text_upload mw_input" autocomplete="off" id="<?php echo $id; ?>" type="text" value="<?php if ($value) { echo $value; } ?>" name="<?php echo $name; ?>"/>
		<?php
		echo mwAdminComponents::iconLink([
			'icon' => 'download',
			'title' => __('Nahrát soubor', 'cms'),
		], 'mw_icon_button cms_upload_file');
		if (!isset($hideDelete)) {
		echo mwAdminComponents::iconLink([
			'icon' => 'trash-2',
			'title' => __('Smazat', 'cms'),
		], 'mw_icon_button cms_delete_uploaded_file');
		}
		?>
	</div>
	<?php
}

// select menu
function field_type_selectmenu($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	$menus = get_terms('nav_menu', ['hide_empty' => false]);
	cms_generate_field_selectmenu(getTagName($group_name, $field['id']), $group_id . '_' . $field['id'], $menus, $content);
}

function cms_generate_field_selectmenu($name, $id, $menus, $value)
{
	echo '<div class="ve_menuselect_container ' . ($value && wp_get_nav_menu_object($value) ? 'selected' : '') . '">';

	echo '<div class="mw_flex_field">';

	echo '<select class="ve_menuselect_selector" name="' . $name . '" id="' . $id . '">';
	echo '<option value="" ' . (!$value ? ' selected="selected"' : '') . '>' . __('Bez menu', 'cms') . '</option>';
	foreach ($menus as $menu) {
		echo '<option value="' . $menu->term_id . '" ' . ($value == $menu->term_id ? ' selected="selected"' : '') . '>' . $menu->name . '</option>';
	}
	echo '</select>';

	echo mwAdminComponents::iconLink([
		'icon' => 'edit-2',
		'title' => __('Upravit menu', 'cms'),
		'attrs' => 'data-title="' . __('Upravit menu', 'cms') . '"',
	], 'mw_icon_button open_menuselect_editor');

	echo mwAdminComponents::iconLink([
		'icon' => 'trash-2',
		'title' => __('Smazat menu', 'cms'),
	], 'mw_icon_button delete_menuselect_editor');

	echo '</div>';

	echo mwAdminComponents::button([
		'icon' => 'plus',
		'button_text' => __('Vytvořit nové menu', 'cms'),
		'style' => 'secondary',
		'attrs' => 'data-title="' . __('Vytvořit nové menu', 'cms') . '"',
	], 'create_menuselect_editor');

	echo '</div>';
}

// select page
function field_type_selectpage($field, $meta, $group_name, $group_id)
{
//	$pages = mw_get_pages();

	echo mwAdminComponents::selectPage([
		'name' => getTagName($group_name, $field['id']),
		'tag_id' => $group_id . '_' . $field['id'],
		'add_button' => $field['add_button'] ?? false,
		'edit_button' => $field['edit_button'] ?? false,
		'whisperer' => $field['whisperer'] ?? false,
		'lazy_loading' => $field['lazy_loading'] ?? true,
	], $meta);
}

// background

function field_type_background($field, $meta, $name_id, $group_id, $post_id)
{
	$content = $meta ?? ($field['content'] ?? ['color1' => '', 'color2' => '']);
	cms_generate_field_background($name_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $field);
}

function cms_generate_field_background($name, $id, $content, $field = [])
{
	if ($content === '') {
		$content = [];
	}

	$class = '';
	if (isset($field['content']['gradient']) && isset($content['gradient']) && $content['gradient']) {
		$class = 'background_color_field_container_wg';
	}

	$show_transparency = (isset($field['hide_transparency']) ? 0 : 1);

	if ($show_transparency) {
		if (!isset($content['transparency1']) || $content['transparency1'] === '') {
			$content['transparency1'] = 1;
		}
	} else {
		$content['transparency1'] = 1;
	}

	if (!isset($content['rgba1'])) {
		$content['rgba1'] = '';
	}

	echo '<div class="background_color_field_container ' . $class . '" data-opacity="' . $show_transparency . '">';
	echo '<div class="cms_background_color cms_background_start_color">'
	. '<input class="mw_input cms_background_color_input" autocomplete="off" type="text" name="' . $name . '[color1]" id="' . $id . '_color1" value="' . $content['color1'] . '" data-opacity="' . $content['transparency1'] . '" />';

	echo '<input class="cms_color_transparency" autocomplete="off" type="hidden" name="' . $name . '[transparency1]" id="' . $id . '_transparency1" value="' . $content['transparency1'] . '" />'
	. '<input class="cms_color_rgba" type="hidden" autocomplete="off" name="' . $name . '[rgba1]" id="' . $id . '_rgba1" value="' . $content['rgba1'] . '" />';

	if ($show_transparency) {
		echo '<span class="cms_color_transparency_view">' . (100 * (float) $content['transparency1']) . '%</span>';
	}

	echo '</div>';
	if (isset($field['content']['gradient'])) {
		$gradient = isset($content['gradient']) && $content['gradient'] == '1' ? '1' : '0';

		if (!isset($content['color2'])) {
			$content['color2'] = '';
		}
		if (!isset($content['rgba2'])) {
			$content['rgba2'] = '';
		}

		if ($show_transparency) {
			if (!isset($content['transparency2']) || $content['transparency2'] === '') {
				$content['transparency2'] = 1;
			}
		} else {
			$content['transparency2'] = 1;
		}

		echo '<div class="cms_background_color cms_background_end_color  set_form_subrow ' . ($gradient ? '' : 'cms_nodisp') . '">'
		. '<input class="mw_input cms_background_color_input" autocomplete="off" type="text" name="' . $name . '[color2]" id="' . $id . '_color2" value="' . $content['color2'] . '" data-opacity="' . $content['transparency2'] . '" />';

		echo '<input class="cms_color_transparency" autocomplete="off" type="hidden" name="' . $name . '[transparency2]" id="' . $id . '_transparency2" value="' . $content['transparency2'] . '" />'
		. '<input class="cms_color_rgba" autocomplete="off" type="hidden" name="' . $name . '[rgba2]" id="' . $id . '_rgba2" value="' . $content['rgba2'] . '" />';

		if ($show_transparency) {
			echo '<span class="cms_color_transparency_view">' . (100.0 * (float) $content['transparency2']) . '%</span>';
		}

		echo '</div>';
		echo '<div class="cms_clear"></div>';

		echo '<div class="set_form_subrow">';
		cms_generate_field_switch($name . '[gradient]', $id . '_gradient', $gradient, ['label' => __('Barevný přechod', 'cms_ve')]);
		echo '</div>';
	}
	echo '</div>';
}

// switch
function field_type_switch($field, $meta, $name_id, $group_id, $post_id)
{
	$content = $meta ?? (isset($field['content']) ? 1 : 0);
	$name = getTagName($name_id, $field['id']);
	cms_generate_field_switch($name, $group_id . '_' . $field['id'], $content, $field);
}

function cms_generate_field_switch($name, $id, $content, $field = [], $echo = true)
{
	$val = '1';
	if (isset($field['value'])) {
		$val = $field['value'];
	}

	$output = mwAdminComponents::switch([
		'name' => $name,
		'id' => $id,
		'value' => $val,
		'switch_label' => $field['label'],
	], $content);

	if ($echo) {
		echo $output;
	} else {
		return $output;
	}
}

// switch
function field_type_status_switch($field, $meta, $name_id, $group_id, $post_id)
{
	$content = $meta ?? ($field['content'] ?? '0');
	$name = getTagName($name_id, $field['id']);
	cms_generate_field_status_switch($name, $group_id . '_' . $field['id'], $content, $field);
}

function cms_generate_field_status_switch($name, $id, $content, $field = [], $echo = true)
{
	$output = mwAdminComponents::statusSwitch([
		'name' => $name,
		'id' => $id,
		'true_val' => $field['true_val'] ?? '1',
		'false_val' => $field['false_val'] ?? '0',
		'switch_label' => $field['label'],
	], $content);

	if ($echo) {
		echo $output;
	} else {
		return $output;
	}
}

// color
function field_type_color($field, $meta, $name_id, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	echo cms_generate_field_color(getTagName($name_id, $field['id']), $group_id . '_' . $field['id'], $content, $field);
}

function cms_generate_field_color($name, $id, $content, $field = [])
{
	$attrs = 'data-position="' . ($field['position'] ?? 'bottom left') . '"';
	if (isset($field['hide_swatches']) && $field['hide_swatches']) {
		$attrs .= ' data-hide_swatches="1"';
	}

	return '<input class="mw_input cms_color_input" autocomplete="off" type="text" name="' . $name . '" id="' . $id . '" value="' . $content . '" ' . $attrs . ' />';
}

// transparent color
function field_type_transparent_color($field, $meta, $name_id, $group_id)
{
	$content = $meta ?? ($field['content'] ?? []);
	cms_generate_field_transparent_color($name_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $field);
}

function cms_generate_field_transparent_color($name, $id, $content, $field = [])
{
	if (!isset($content['color'])) {
		$old = $content;
		$content = [];
		$content['color'] = $old;
	}

	if (!isset($content['transparency'])) {
		$content['transparency'] = isset($field['content']) && isset($field['content']['transparency']) ? $field['content']['transparency'] : 1;
	}
	if (!isset($content['rgba'])) {
		$content['rgba'] = '';
	}

	echo '<div class="cms_transparent_color_container">';
	echo '<input class="mw_input cms_color_input cms_transparent_color" autocomplete="off" type="text" name="' . $name . '[color]" id="' . $id . '_color" value="' . $content['color'] . '" data-opacity="' . $content['transparency'] . '" />';
	echo '<input class="cms_transparent_color_transparency" autocomplete="off" type="hidden" name="' . $name . '[transparency]" id="' . $id . '_transparency" value="' . $content['transparency'] . '" />';
	echo '<input class="cms_transparent_color_rgba" autocomplete="off" type="hidden" name="' . $name . '[rgba]" id="' . $id . '_rgba" value="' . $content['rgba'] . '" />';
	echo '<span class="cms_color_transparency_view">' . (100 * floatval($content['transparency'])) . '%</span>';
	echo '</div>';
}

// padding
function field_type_padding($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	cms_generate_field_padding($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content);
}

function cms_generate_field_padding($name, $id, $content)
{
	echo '<div class="mw_flex_field_col"><div class="sublabel">' . __('Nahoře', 'cms') . '</div><input class="mw_input" type="text" name="' . $name . '[top]" id="' . $id . '_top" value="' . $content['top'] . '" /></div>';
	echo '<div class="mw_flex_field_col"><div class="sublabel">' . __('Dole', 'cms') . '</div><input class="mw_input" type="text" name="' . $name . '[bottom]" id="' . $id . '_bottom" value="' . $content['bottom'] . '" /></div>';
	echo '<div class="mw_flex_field_col"><div class="sublabel">' . __('Vlevo', 'cms') . '</div><input class="mw_input" type="text" name="' . $name . '[left]" id="' . $id . '_top" value="' . $content['left'] . '" /></div>';
	echo '<div class="mw_flex_field_col"><div class="sublabel">' . __('Vpravo', 'cms') . '</div><input class="mw_input" type="text" name="' . $name . '[right]" id="' . $id . '_bottom" value="' . $content['right'] . '" /></div>';
	echo '<div class="cms_clear"></div>';
}

// shadow
function field_type_shadow($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	cms_generate_field_shadow($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $field['content']);
}

function cms_generate_field_shadow($name, $id, $content)
{
	echo '<div class="mw_flex_field_col"><div class="sublabel">' . __('Horizontální posunutí', 'cms') . '</div><input class="mw_input" autocomplete="off" type="text" name="' . $name . '[horizontal]" id="' . $id . '_horizontal" value="' . $content['horizontal'] . '" /></div>';
	echo '<div class="mw_flex_field_col"><div class="sublabel">' . __('Vertikální posunutí', 'cms') . '</div><input class="mw_input" autocomplete="off" type="text" name="' . $name . '[vertical]" id="' . $id . '_vertical" value="' . $content['vertical'] . '" /></div>';
	echo '<div class="mw_flex_field_col"><div class="sublabel">' . __('Velikost stínu', 'cms') . '</div><input class="mw_input" autocomplete="off" type="text" name="' . $name . '[size]" id="' . $id . '_size" value="' . $content['size'] . '" /></div>';
	?>
	<div class="mw_flex_field_col" style="width: 180px;">
		<div class="sublabel"><?php echo __('Průhlednost stínu', 'cms'); ?></div>
	<?php
	$transparency = $content['transparency'] ?? '10';
	cms_generate_field_slider($name . '[transparency]', $id . '_transparency', $transparency, ['setting' => ['min' => '0', 'max' => '100', 'unit' => '%']]);
	?>
	</div>

	<?php
	echo '<div class="cms_clear"></div>';
}

// slider
function field_type_slider($field, $meta, $name_id, $group_id, $post_id)
{
	$content = isset($meta) && empty($meta) !== '' ? $meta : ($field['content'] ?? $field['setting']['min']);

	if ((is_array($content) && (!isset($field['content']) || is_array($field['content']))) || (isset($field['setting']['unit']) && is_array($field['setting']['unit']))) {
		cms_generate_field_slider_m($name_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $field);
	} else {
		/* back compatibility */
		if (is_array($content)) {
			$content = $content['size'] ?? '';
		}
		/* end of back compatibility */
		cms_generate_field_slider($name_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $field);
	}
}

function cms_generate_field_slider($name, $id, $content, $field, $echo = true)
{
	$class = '';

	if (isset($field['class'])) {
		$class = $field['class'];
	}
	if (isset($field['setting']['title'])) {
		$class .= ' mw_slider_with_title';
	}

	$slider_position = $content;
	if (isset($field['setting']['default']) && $content == '') {
		$slider_position = $field['setting']['default'];
	}
	if ($content > $field['setting']['max']) {
		$field['setting']['max'] = $content;
	}
	$placeholder = $field['setting']['placeholder'] ?? '';

	$out = '<div class="mw_slider ' . $class . '"'
	. 'data-min="' . $field['setting']['min'] . '"'
	. 'data-max="' . $field['setting']['max'] . '"'
	. 'data-step="' . ($field['setting']['step'] ?? 1) . '"'
	. 'data-default="' . ($field['setting']['default'] ?? '') . '"'
	. 'data-val="' . $slider_position . '">'
	. '<div id="' . $id . '_slider" class="mw_slider_container"></div>'
	. (isset($field['setting']['title']) ? '<div class="mw_slider_title">' . $field['setting']['title'] . '</div>' : '')
	. '<input id="' . $id . '" class="cms_slider_val" type="text" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $content . '" />'
	. '<a href="#" class="mw_slider_value_changer mw_slider_value_up">' . mw_icon('icon-chevron-up') . '</a>'
	. '<a href="#" class="mw_slider_value_changer mw_slider_value_down">' . mw_icon('icon-chevron-down') . '</a>'
	. '<div class="cms_clear"></div>'
	. '</div>';

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

function cms_generate_field_slider_m($name, $id, $content, $field, $echo = true)
{
	$class = '';

	if (isset($field['class'])) {
		$class = $field['class'];
	}

	if (!isset($content['size'])) {
		$content = ['size' => ''];
	}
	$slider_position = $content['size'];
	if (isset($field['setting']['default']) && $content['size'] == '') {
		$slider_position = $field['setting']['default'];
	}
	if ($content['size'] > $field['setting']['max']) {
		$field['setting']['max'] = $content['size'];
	}
	$placeholder = $field['setting']['placeholder'] ?? '';

	$unit_c = '';
	if (is_array($field['setting']['unit'])) {
		if (!isset($content['unit'])) {
			$content['unit'] = $field['setting']['unit'][0];
		}

		foreach ($field['setting']['unit'] as $unit) {
			$unit_c .= '<label class="mw_slider_unit ' . ($content['unit'] == $unit ? 'mw_slider_unit_a' : '') . '">'
			. '<input class="cms_slider_unit" autocomplete="off" type="radio" value="' . $unit . '" name="' . $name . '[unit]" ' . ($content['unit'] == $unit ? 'checked="checked"' : '') . ' />'
			. $unit
			. '</label>';
		}
	} else {
		//$unit_c=$field['setting']['unit'];
		if (!isset($content['unit']) || !$content['unit']) {
			$content['unit'] = 'px';
		}
		$unit_c = '<input class="cms_slider_unit" autocomplete="off" type="radio" name="' . $name . '[unit]" value="' . $content['unit'] . '" checked="checked" />';
	}

	$out = '<div class="mw_slider ' . $class . '"'
	. 'data-min="' . $field['setting']['min'] . '"'
	. 'data-max="' . $field['setting']['max'] . '"'
	. 'data-step="' . ($field['setting']['step'] ?? 1) . '"'
	. 'data-unit="' . $content['unit'] . '"'
	. 'data-default="' . ($field['setting']['default'] ?? '') . '"'
	. 'data-val="' . $slider_position . '">'
	. '<div id="' . $id . '_slider" class="mw_slider_container">'
	. '</div>'
	. '<input id="' . $id . '" class="cms_slider_val" autocomplete="off" type="text" name="' . $name . '[size]" placeholder="' . $placeholder . '" value="' . $content['size'] . '" />'
	. '<a href="#" class="mw_slider_value_changer mw_slider_value_up">' . mw_icon('icon-chevron-up') . '</a>'
	. '<a href="#" class="mw_slider_value_changer mw_slider_value_down">' . mw_icon('icon-chevron-down') . '</a>'
	. '<div class="mw_slider_unit_container">' . $unit_c . '</div>'
	. '<div class="cms_clear"></div>'
	. '</div>';

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

// font
function field_type_font($field, $meta, $name_id, $group_id, $post_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	$mobile = isset($field['mobile']) ? true : false;
	cms_generate_field_font($name_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $field, $mobile, $group_id);
}

function cms_generate_field_font($name, $id, $value, $field, $mobile = false, $group_id = '')
{
	if ($value === '') {
		$value = [];
	}

	$setting = $field['content'] ?? [];

	$basic_weights = ['normal' => __('Normal', 'cms'), 'bold' => __('Bold', 'cms')];

	$content = '';
	$font_family_content = '';
	$weight_content = '';
	$size_content = '';
	$color_content = '';
	$line_height_content = '';
	$letter_spacing_content = '';
	$align_content = '';
	$text_shadow_content = '';
	$font_content = '';
	$capitals = '';

	$rowClass = isset($field['setting']['visible']) ? 'row' : 'subrow';

	if (isset($setting['font-family'])) {
		$font_family_content .= '<div class="set_form_' . $rowClass . '"><div class="sublabel">' . __('Font', 'cms') . '</div>';
		$font_family_content .= '<div class="cms_font_family_container ' . ($value['font-family'] != '' ? 'cms_font_family_selected' : '') . '">';
		//echo '<div class="sublabel">'.__('Písmo','cms').'</div>';

		$font_family_content .= '<div id="' . $id . '_font" class="font_select_container">'
		. '<a class="font_selected cms_open_font_selector" href="#">' . ($value['font-family'] == '' ? __('Defaultní', 'cms') : $value['font-family']) . '</a>'
		. '<input type="hidden" autocomplete="off" class="font_selected_input" name="' . $name . '[font-family]" value="' . $value['font-family'] . '">'
		. '<div class="cms_style_selector_bg cms_close_style_selector"></div>'
		. '<div class="cms_style_selector">'
		. '<a href="#" class="mw_close_icon cms_close_style_selector">' . mw_icon('icon-x') . '</a>'
		. '<div class="cms_style_selector_inner font_select mw_scroll">'
		. '<a class="cms_close_style_selector" href="#" data-font="" data-text="' . __('Defaultní', 'cms') . '" data-weights="{\'id\':\'\',\'name\':\'-\'}" >' . __('Defaultní', 'cms') . '</a>';
		// used fonts
		if (isset($_SESSION['ve_used_fonts']) && count($_SESSION['ve_used_fonts'])) {
			$font_family_content .= '<div class="cms_clear"></div>';
			$font_family_content .= '<div class="cms_style_selector_title">' . __('Naposledy používané fonty', 'cms') . '</div>';

			foreach ($_SESSION['ve_used_fonts'] as $used_font) {
				if (in_array($used_font, MW()->fonts)) {
					$font_family_content .= '<a class="cms_close_style_selector" href="#" data-font="' . $used_font . '"  data-weights="{\'id\':\'normal\',\'name\':\'' . __('Normal', 'cms') . '\'},{\'id\':\'bold\',\'name\':\'' . __('Bold', 'cms') . '\'}" style="font-family:' . $used_font . ';">' . $used_font . '</a>';
				} elseif (is_string($used_font) && isset(MW()->google_fonts[$used_font])) {
					$font = MW()->google_fonts[$used_font];
					$weights = [];
					foreach ($font['weights'] as $wkey => $wval) {
						$weights[] = "{'id':'" . $wkey . "','name':'" . $wval . "'}";
					}
					$font_family_content .= '<a class="cms_close_style_selector" href="#" data-font="' . $used_font . '" data-weights="' . implode(',', $weights) . '">';
					if (isset($font['img'])) {
						$font_family_content .= '<img src="' . $font['img'] . '" alt="' . $used_font . '" />';
					} else {
						$font_family_content .= $used_font;
					}
					$font_family_content .= '</a>';
				}
			}
		}

		$font_family_content .= '<div class="cms_clear"></div>';
		$font_family_content .= '<div class="cms_style_selector_title">' . __('Základní fonty', 'cms') . '</div>';

		// basic fonts
		foreach (MW()->fonts as $font) {
			$font_family_content .= '<a class="cms_close_style_selector" href="#" data-font="' . $font . '"  data-weights="{\'id\':\'normal\',\'name\':\'' . __('Normal', 'cms') . '\'},{\'id\':\'bold\',\'name\':\'' . __('Bold', 'cms') . '\'}" style="font-family:' . $font . ';">' . $font . '</a>';
		}

		$font_family_content .= '<div class="cms_clear"></div>';

		// custom fonts
		//google
		$custom_google_fonts = [];
		foreach (MW()->google_fonts as $key => $font) {
			if (isset($font['custom_font'])) {
				$custom_google_fonts[$key] = $font;
			}
		}
		if (count($custom_google_fonts)) {
			$font_family_content .= '<div class="cms_style_selector_title">' . __('Vlastní google fonty', 'cms') . '</div>';

			foreach ($custom_google_fonts as $key => $font) {
				$weights = [];
				foreach ($font['weights'] as $wkey => $wval) {
					$weights[] = "{'id':'" . $wkey . "','name':'" . $wval . "'}";
				}
				$font_family_content .= '<a class="cms_close_style_selector" href="#" data-font="' . $key . '" data-weights="' . implode(',', $weights) . '">' . $key . '</a>';
			}

			$font_family_content .= '<div class="cms_clear"></div>';
		}

		//file
		$custom_file_fonts = MW()->file_fonts;
		if (count($custom_file_fonts)) {
			$font_family_content .= '<div class="cms_style_selector_title">' . __('Vlastní fonty ze souboru', 'cms') . '</div>';

			foreach ($custom_file_fonts as $key => $font) {
				$weights = [];
				foreach ($font as $wkey => $wval) {
					$weights[] = "{'id':'" . $wkey . "','name':'" . $wval['name'] . "','file':'" . $wval['file'] . "'}";
				}
				$font_family_content .= '<a class="cms_close_style_selector" href="#" data-font="' . $key . '" data-weights="' . implode(',', $weights) . '" style="font-family:' . $key . ';">' . $key . '</a>';
			}

			$font_family_content .= '<div class="cms_clear"></div>';
		}


		$font_family_content .= '<div class="cms_style_selector_title">' . __('Google fonty', 'cms') . '</div>';

		// google fonts
		foreach (MW()->google_fonts as $key => $font) {
			if (!isset($font['custom_font'])) {
				$weights = [];
				foreach ($font['weights'] as $wkey => $wval) {
					$weights[] = "{'id':'" . $wkey . "','name':'" . $wval . "'}";
				}
				$font_family_content .= '<a class="cms_close_style_selector" href="#" data-font="' . $key . '" data-weights="' . implode(',', $weights) . '"><img src="' . $font['img'] . '" alt="' . $key . '" /></a>';
			}
		}

		$font_family_content .= '</div>';
		$font_family_content .= '</div>';
		$font_family_content .= '</div>';

		if (isset($setting['weight'])) {
			$font_family_content .= '<div class="font_weight_select_container">';
			$font_family_content .= '<select id="' . $id . '_weight" class="font_weight_select" name="' . $name . '[weight]">';
			if (isset(MW()->google_fonts[$value['font-family']])) {
				$weights = MW()->google_fonts[$value['font-family']]['weights'];
			} elseif (isset(MW()->file_fonts[$value['font-family']])) {
				$weights = MW()->file_fonts[$value['font-family']];
				array_walk($weights, function (&$val) {
					$val = $val['name'];
				});
			} else {
				$weights = $basic_weights;
			}
			if (!$value['font-family']) {
				$weights = ['' => '-'];
			}
			foreach ($weights as $key => $weight) {
				$font_family_content .= '<option ' . ($value['weight'] == $key ? 'selected="selected"' : '') . ' value="' . $key . '">' . $weight . '</option>';
			}
			$font_family_content .= '</select>';
			$font_family_content .= '</div>';
		}

		$font_family_content .= '<div class="cms_clear"></div>';
		$font_family_content .= '</div>';
		$font_family_content .= '</div>';

		$content .= $font_family_content;
	}
	if (isset($setting['use-font'])) {
		if (!isset($value['use-font']) && isset($field['content']['use-font'])) {
			$value['use-font'] = $field['content']['use-font'];
		}

		$font_content .= '<div class="set_form_' . $rowClass . '">';
		$font_content .= MW()->create_sublabel(__('Font', 'cms'), false);
		$font_content .= '<select id="' . $id . '_use_font" class="cms_font_use" name="' . $name . '[use-font]">';
		$font_content .= '<option ' . ($value['use-font'] == 'title' ? 'selected="selected"' : '') . ' title="' . __('Použít font nadpisu', 'cms') . '" value="title">' . __('Použít font nadpisu', 'cms') . '</option>';
		$font_content .= '<option ' . ($value['use-font'] == 'subtitle' ? 'selected="selected"' : '') . ' title="' . __('Použít font podnadpisu', 'cms') . '" value="subtitle">' . __('Použít font podnadpisu', 'cms') . '</option>';
		$font_content .= '<option ' . ($value['use-font'] == 'text' ? 'selected="selected"' : '') . ' title="' . __('Použít font textu', 'cms') . '" value="text">' . __('Použít font textu', 'cms') . '</option>';
		$font_content .= '</select>';
		$font_content .= '</div>';

		$content .= $font_content;
	}

	if (isset($setting['font-size'])) {
		$devices = $mobile ? MW()->devices : ['desktop' => ''];
		$size_content .= '<div class="set_form_' . $rowClass . ' font_size_slider">';
		$size_content .= MW()->create_sublabel(__('Velikost', 'cms'), $mobile);
		foreach ($devices as $d_key => $d_val) {
			if ($d_key != 'desktop') {
				$dname = $name . '[' . $d_key . ']';
				$did = $id . '_' . $d_key;
				$dval = $value[$d_key] ?? '';
			} else {
				$dname = $name;
				$did = $id;
				$dval = $value;
			}

			$dclass = $d_key . '_setting ';
			if ($mobile) {
				$dclass .= $d_key . '_device_set_container';
			}

			$max_size = $field['setting']['max_font_size'] ?? '30';

			$size_placeholder = $field['setting']['font_size_placeholder'] ?? '';

			$size_content .= '<div class="' . $dclass . '">';
			$size_content .= cms_generate_field_slider($dname . '[font-size]', $did . '_fontsize', ($dval['font-size'] ?? ''), ['setting' => ['min' => '12', 'max' => $max_size, 'unit' => 'px', 'placeholder' => $size_placeholder, 'default' => $size_placeholder]], false);
			$size_content .= '</div>';
		}
		$size_content .= '</div>';

		$content .= $size_content;
	}
	if (isset($setting['color'])) {
		$show_class = (isset($field['setting']['show_group'])
			? ' cms_show_group_' . $group_id . '_' . $field['setting']['show_group'] . ' '
			. (isset($field['setting']['show_color'])
				? ' cms_show_group_' . $group_id . '_' . $field['setting']['show_group'] . '_'
				. implode(
					' cms_show_group_' . $group_id . '_' . $field['setting']['show_group'] . '_',
					explode(',', $field['setting']['show_color'])
				)
				: ''
			)
			: ''
		);

		$color_content .= '<div class="set_form_' . $rowClass . ' ' . $show_class . '"><div class="sublabel">' . __('Barva', 'cms') . '</div>';
		$color_content .= '<input id="' . $id . '_color" class="mw_input cms_color_input cms_font_color" autocomplete="off" type="text" name="' . $name . '[color]" value="' . ($value['color'] ?? '') . '" />';
		$color_content .= '</div>';

		$content .= $color_content;
	}
	if (isset($setting['line-height'])) {
		$line_height = $value['line-height'] ?? '';

		$line_height_content .= '<div class="set_form_' . $rowClass . ' font_line_height_slider"><div class="sublabel">' . __('Výška řádků', 'cms') . '</div>';
		$line_height_content .= cms_generate_field_slider($name . '[line-height]', $id . '_line_height', $line_height, ['setting' => ['min' => '0.8', 'max' => '3', 'step' => '0.1', 'unit' => 'em']], false);
		$line_height_content .= '</div>';

		$content .= $line_height_content;
	}
	if (isset($setting['letter-spacing'])) {
		$letter_spacing_content .= '<div class="set_form_' . $rowClass . ' font_letter_spacing_slider"><div class="sublabel">' . __('Mezery', 'cms') . '</div>';
		$letter_spacing_content .= cms_generate_field_slider($name . '[letter-spacing]', $id . '_letter_spacing', $value['letter-spacing'] ?? 0, ['setting' => ['min' => '-3', 'max' => '20', 'unit' => 'px']], false);
		$letter_spacing_content .= '</div>';

		$content .= $letter_spacing_content;
	}
	if (isset($setting['align'])) {
		$align_content .= '<div class="set_form_' . $rowClass . ' font_align_select"><div class="sublabel">' . __('Zarovnání', 'cms') . '</div>';
		$align_content .= '<select id="' . $id . '_align" name="' . $name . '[align]">';
		$align_content .= '<option ' . ($value['align'] == 'center' ? 'selected="selected"' : '') . ' value="center">' . __('Na střed', 'cms') . '</option>';
		$align_content .= '<option ' . ($value['align'] == 'left' ? 'selected="selected"' : '') . ' value="left">' . __('Vlevo', 'cms') . '</option>';
		$align_content .= '<option ' . ($value['align'] == 'right' ? 'selected="selected"' : '') . ' value="right">' . __('Vpravo', 'cms') . '</option>';
		$align_content .= '</select>';
		$align_content .= '</div>';

		$content .= $align_content;
	}
	if (isset($setting['text-shadow'])) {
		$text_shadow_content .= '<div class="set_form_' . $rowClass . '"><div class="sublabel">' . __('Stín', 'cms') . '</div>';
		$text_shadow_content .= '<select id="' . $id . '_shadow" class="cms_font_shadow" name="' . $name . '[text-shadow]">';
		$text_shadow_content .= '<option ' . ($value['text-shadow'] == '' ? 'selected="selected"' : '') . ' value="none">' . __('Žádný', 'cms') . '</option>';
		$text_shadow_content .= '<option ' . ($value['text-shadow'] == 'dark' ? 'selected="selected"' : '') . ' value="dark">' . __('Tmavý', 'cms') . '</option>';
		$text_shadow_content .= '<option ' . ($value['text-shadow'] == 'light' ? 'selected="selected"' : '') . ' value="light">' . __('Světlý', 'cms') . '</option>';
		$text_shadow_content .= '</select>';
		$text_shadow_content .= '</div>';

		$content .= $text_shadow_content;
	}
	if (isset($setting['capitals'])) {
		$capital_c = isset($value['capitals']) && $value['capitals'] ? 1 : 0;
		$capitals .= '<div class="set_form_' . $rowClass . ' set_form_row_b cms_font_capitals">';
		$capitals .= cms_generate_field_switch($name . '[capitals]', $id . '_capitals', $capital_c, ['label' => __('Velká písmena', 'cms')], false);
		$capitals .= '</div>';

		$content .= $capitals;
	}

	if (isset($field['setting']['visible'])) {
		echo $content;
	} else {
		$title = '';
		$target = 'font';
		$title_value = '';
		if (isset($setting['font-family'])) {
			$target = 'font';
			$title_value = isset($value['font-family']) && $value['font-family'] ? $value['font-family'] : __('Defaultní', 'cms');
		} elseif (isset($setting['use-font'])) {
			$target = 'usefont';
			$usefont = $value['use-font'] ?? '';
			switch ($usefont) {
				case 'title':
					$title_value = __('Použít font nadpisu', 'cms');

					break;
				case 'subtitle':
					$title_value = __('Použít font podnadpisu', 'cms');

					break;
				case 'text':
					$title_value = __('Použít font textu', 'cms');

					break;
				default:
					$title_value = __('Defaultní', 'cms');

					break;
			}
		} elseif (isset($setting['font-size'])) {
			$target = 'size';
			$title_value = (isset($value['font-size']) && $value['font-size'] != '' ? ' ' . $value['font-size'] . 'px' : __('Defaultní', 'cms'));
		}

		$title .= '<span class="mw_hidden_setting_label_main mw_font_label_target_' . $target . '">' . $title_value . '</span>';

		if (isset($setting['font-size']) && $target != 'size') {
			$title .= '<span class="mw_hidden_setting_label_size mw_font_label_target_size">' . (isset($value['font-size']) && $value['font-size'] ? ' ' . $value['font-size'] . 'px' : '') . '</span>';
		}
		if (isset($setting['color']) && $target != 'color') {
			$title .= mwAdminComponents::colorShow([
				'color' => $value['color'] ?? '',
			], 'mw_hidden_setting_label_color');
		}

		echo mwAdminComponents::hiddenSetting([
			'label' => $title,
			'content' => $content,
		]);
	}
}

function field_type_miocarousel($field, $meta, $name_id, $group_id, $post_id)
{
	$content = $meta ?? ($field['content'] ?? '');

	$name = $name_id . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	echo '<div class="cms_carousel_setting_container">';

	// animation
	echo '<div class="carousel_setting_animation">';
	echo '<div class="sublabel">' . __('Typ animace', 'cms') . '</div>';
	$select_arr = [
		'options' => [
			['name' => __('Prolínání', 'cms_ve'), 'value' => 'fade'],
			['name' => __('Zprava doleva', 'cms_ve'), 'value' => 'slide'],
		],
	];
	echo cms_generate_field_select($name . '[animation]', $id . '_animation', $content['animation'] ?? 'fade', $select_arr);
	echo '</div>';

	// setting
	echo '<div class="set_form_row carousel_setting_setting">';
	echo '<div class="set_form_subrow carousel_setting_autoplay">';
	$autoplay = isset($content['autoplay']) ? 1 : 0;
	cms_generate_field_switch($name . '[autoplay]', $id . '_autoplay', $autoplay, ['label' => __('Zapnout autoplay', 'cms_ve')]);
	echo '</div>';
	echo '<div class="set_form_subrow carousel_setting_hide_navigation">';
	$hide_navigation = isset($content['hide_navigation']) ? 1 : 0;
	cms_generate_field_switch($name . '[hide_navigation]', $id . '_hide_navigation', $hide_navigation, ['label' => __('Skrýt navigaci', 'cms_ve')]);
	echo '</div>';
	echo '</div>';

	// color scheme
	echo '<div class="set_form_row carousel_setting_color_scheme">';
	echo '<div class="sublabel">' . __('Barevné schéma', 'cms') . '</div>';
	$select_arr = [
		'options' => [
			['name' => __('Automaticky', 'cms_ve'), 'value' => 'auto'],
			['name' => __('Světlé', 'cms_ve'), 'value' => 'light'],
			['name' => __('Tmavé', 'cms_ve'), 'value' => ''],
		],
	];
	echo cms_generate_field_select($name . '[color_scheme]', $id . '_color_scheme', $content['color_scheme'] ?? '', $select_arr);
	echo '</div>';

	// delay
	echo '<div class="set_form_row carousel_setting_delay">';
	echo '<div class="sublabel">' . __('Zpoždění slidů', 'cms') . '</div>';
	echo cms_generate_field_simple_size($name . '[delay]', $id . '_delay', $content['delay'], ['unit' => 'ms']);
	echo '</div>';

	// speed
	echo '<div class="set_form_row carousel_setting_speed">';
	echo '<div class="sublabel">' . __('Délka animace', 'cms') . '</div>';
	echo cms_generate_field_simple_size($name . '[speed]', $id . '_speed', $content['speed'], ['unit' => 'ms']);
	echo '</div>';

	echo '</div>';
}

function field_type_shape_divider($field, $meta, $name_id, $group_id, $post_id)
{
	$content = $meta ?? ($field['content'] ?? '');

	$name = $name_id . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	global $mwContainer;

	if (!isset($content['shape']) || !$content['shape']) {
		$content['shape'] = 'tilt';
	}

	$ico = $mwContainer->list['shape_dividers'][$content['shape']];

	echo '<div class="cms_shape_divider_container ' . (isset($field['bottom']) ? 'cms_bottom_shape_dividers' : '') . '">';

	echo '<div class="cms_shape_divider_show set_form_subrow">';
	$show_val = isset($content['show']) ? 1 : 0;
	echo cms_generate_field_switch($name . '[show]', $id . '_show', $show_val, ['label' => $field['label']], false);
	echo '</div>';

	echo '<div class="cms_shape_divider_setting ' . (!$show_val ? 'cms_nodisp' : '') . '">';

	?>
	<div class="cms_shape_divider_shape set_form_subrow set_form_subrow_b">
		<div class="cms_image_selector_container cms_image_select">
			<div class="cms_image_selected cms_open_image_selector">
				<div class="cms_image_select_container"> <?php echo $ico; ?></div>
				<?php echo '<input type="hidden" autocomplete="off" class="cms_image_select_val" name="' . $name . '[shape]" value="' . $content['shape'] . '" />'; ?>
				<input type="hidden" autocomplete="off" class="cms_icon_select_code" name="<?php echo $name . '[code]'; ?>"
					   value='<?php echo $ico; ?>'/>
			</div>
			<a class="mw_shape_divider_setting_open" href="#">
			<?php
			echo mwAdminComponents::icon([
				'icon' => 'settings',
			], 'edit');
			echo mwAdminComponents::icon([
				'icon' => 'x',
			], 'close');
			?>
			</a>
			<div class="cms_image_selector_bg cms_close_image_selector"></div>
			<div class="cms_image_selector_items">
				<div class="cms_image_selector mw_scroll">
					<?php
					foreach ($mwContainer->list['shape_dividers'] as $key => $val) {
						echo '<div class="cms_is_item cms_is_item_' . $key . ' ' . ($content == $key ? 'cms_is_item_active' : '') . '">';
						echo '<a class="" href="#" data-value="' . $key . '">';
						echo $val;
						echo '</a>';
						echo '</div>';
					}
					?>
					<div class="cms_clear"></div>
					<a href="#" class="mw_close_icon cms_close_image_selector"><?php echo mw_icon('icon-x'); ?></a>
				</div>
			</div>
		</div>
	</div>

	<?php

	echo '<div class="cms_shape_divider_shape_setting">';

	// size
	echo '<div class="set_form_subrow cms_shape_divider_height">';
	echo MW()->create_sublabel(__('Velikost', 'cms'), true);
	foreach (MW()->devices as $d_key => $d_val) {
		if ($d_key != 'desktop') {
			$dname = $name . '[' . $d_key . ']';
			$did = $id . '_' . $d_key;
			$dval = $content[$d_key] ?? [];
		} else {
			$dname = $name;
			$did = $id;
			$dval = $content;
		}

		echo '<div class="' . $d_key . '_setting ' . $d_key . '_device_set_container">';
		echo cms_generate_field_slider($dname . '[size]', $did . '_size', $dval['size'] ?? '', ['setting' => ['min' => '0', 'max' => '300', 'unit' => 'px']], false);
		echo '</div>';
	}
	echo '</div>';

	// color
	echo '<div class="set_form_subrow cms_shape_divider_color">';
	echo '<div class="sublabel">' . __('Barva', 'cms') . '</div>';
	echo cms_generate_field_color($name . '[color]', $id . '_color', $content['color'] ?? '', $field);
	echo '</div>';

	// flip
	echo '<div class="cms_shape_divider_flip set_form_subrow set_form_subrow_b">';
	$flip_val = isset($content['flip']) && $content['flip'] ? 1 : 0;
	echo cms_generate_field_switch($name . '[flip]', $id . '_flip', $flip_val, ['label' => __('Převrátit', 'cms')], false);
	echo '</div>';

	echo '</div>'; // shape setting

	echo '</div>'; // setting container

	echo '</div>';
}

function field_type_border($field, $meta, $name_id, $group_id, $post_id)
{
	$content = $meta ?? ($field['content'] ?? ['size' => '0', 'style' => 'solid', 'color' => '']);

	$name = $name_id . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	cms_generate_field_border($name, $id, $content, $field);
}

function cms_generate_field_border($name, $id, $c, $field)
{
	$max = 10;
	if (isset($field['setting'])) {
		if (isset($field['setting']['max_size'])) {
			$max = $field['setting']['max_size'];
		}
	}

	$size = isset($c['size']) && $c['size'] ? $c['size'] : 0;
	$style = $c['style'] ?? 'solid';
	$color = $c['color'] ?? '';
	$transparency = $c['transparency'] ?? 1;
	$haveTransparency = isset($field['content']['transparency']) ? true : false;

	$content = '';
	if (isset($field['content']['size'])) {
		$content .= '<div class="cms_border_set_size set_form_subrow">';
		$content .= '<div class="sublabel">' . __('Tloušťka čáry', 'cms') . '</div>';
		$content .= cms_generate_field_slider($name . '[size]', $id . '_size', $size, ['setting' => ['min' => '0', 'max' => $max, 'unit' => 'px']], false);
		$content .= '</div>';
	}
	if (isset($field['content']['style'])) {
		$content .= '<div class="cms_border_set_style set_form_subrow">';
		$content .= '<div class="sublabel">' . __('Styl čáry', 'cms') . '</div>';
		$content .= '<select class="cms_border_set_style_val" name="' . $name . '[style]">';
		$content .= '<option ' . ($style == 'solid' ? 'selected="selected"' : '') . ' value="solid">' . __('Plná', 'cms') . '</option>';
		$content .= '<option ' . ($style == 'dashed' ? 'selected="selected"' : '') . ' value="dashed">' . __('Čárkovaná', 'cms') . '</option>';
		$content .= '<option ' . ($style == 'dotted' ? 'selected="selected"' : '') . ' value="dotted">' . __('Tečkovaná', 'cms') . '</option>';
		$content .= '</select>';
		$content .= '</div>';
	} else {
		$content .= '<input class="cms_border_set_style_val" type="hidden" autocomplete="off" name="' . $name . '[style]" value="solid" />';
	}
	if (isset($field['content']['color'])) {
		$color_input_class = $haveTransparency ? 'cms_color_input_transparent' : 'cms_color_input_notransparent';

		$content .= '<div class="cms_border_set_color set_form_subrow">';
		$content .= '<div class="sublabel">' . __('Barva', 'cms') . '</div>';
		$content .= '<input class="mw_input cms_color_input ' . $color_input_class . '" autocomplete="off" type="text" name="' . $name . '[color]" value="' . $color . '" ' . ($haveTransparency ? 'data-opacity="' . $transparency . '"' : '') . ' />';

		if ($haveTransparency) {
			$rgba = $c['rgba'] ?? '';
			$content .= '<input class="cms_color_transparency" autocomplete="off" type="hidden" name="' . $name . '[transparency]" value="' . $transparency . '" />';
			$content .= '<input class="cms_color_rgba" autocomplete="off" type="hidden" name="' . $name . '[rgba]" id="' . $id . '_rgba" value="' . $rgba . '" />';
		}

		$content .= '</div>';
	}

	$title = '<span class="mw_hidden_setting_label_border ' . ($size > 0 ? 'seted' : '') . '"><hr style="border-style:' . $style . ';"></span>';
	$title .= '<span class="mw_hidden_setting_label_size">' . $size . 'px</span>';
	$title .= mwAdminComponents::colorShow([
		'color' => $color,
	], 'mw_hidden_setting_label_color');

	echo '<div class="cms_border_set_container">';
	echo mwAdminComponents::hiddenSetting([
		'label' => $title,
		'content' => $content,
	]);
	echo '</div>';
}

function field_type_background_set($field, $meta, $name_id, $group_id, $post_id)
{
	$content = $meta ?? ($field['content'] ?? ['size' => '0', 'style' => 'solid', 'color' => '']);

	$name = $name_id . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	cms_generate_field_background_set($name, $id, $content, $field);
}

function cms_generate_field_background_set($name, $id, $c, $field)
{
	$content = '';

	if (isset($field['content']['corner'])) {
		$content .= '<div class="cms_background_set_corners set_form_subrow">';
		$content .= '<div class="sublabel">' . __('Zakulacení rohů', 'cms') . '</div>';

		$corners_options = [
			'' => [
				'icon' => 'sharp_corner',
				'text' => __('Ostré', 'cms_ve'),
			],
			'1' => [
				'icon' => 'rounded_corner',
				'text' => __('Zakulacené', 'cms_ve'),
			],
			'2' => [
				'icon' => 'round_corner',
				'text' => __('Kulaté', 'cms_ve'),
			],
		];
		$corner = $c['corner'] ?? '1';
		$content .= cms_generate_field_imageoption($name . '[corner]', $id . '_corner', $corners_options, $corner, [], false);

		$content .= '</div>';
	}

	if (isset($field['content']['shadow'])) {
		$content .= '<div class="cms_border_set_shadow set_form_subrow">';
		$content .= '<div class="sublabel">' . __('Stín', 'cms') . '</div>';
		$content .= '<select class="cms_border_set_shadow_val" name="' . $name . '[shadow]">';
		$content .= '<option ' . ($c['shadow'] == '' ? 'selected="selected"' : '') . ' value="">' . __('Bez stínu', 'cms') . '</option>';
		$content .= '<option ' . ($c['shadow'] == '5' ? 'selected="selected"' : '') . ' value="5">' . __('Malý stín', 'cms') . '</option>';
		$content .= '<option ' . ($c['shadow'] == '1' ? 'selected="selected"' : '') . ' value="1">' . __('Základní stín', 'cms') . '</option>';
		$content .= '<option ' . ($c['shadow'] == '3' ? 'selected="selected"' : '') . ' value="3">' . __('Větší stín', 'cms') . '</option>';
		$content .= '<option ' . ($c['shadow'] == '4' ? 'selected="selected"' : '') . ' value="4">' . __('Spodní stín', 'cms') . '</option>';
		$content .= '<option ' . ($c['shadow'] == '2' ? 'selected="selected"' : '') . ' value="2">' . __('Stín vpravo dole', 'cms') . '</option>';
		$content .= '</select>';
		$content .= '</div>';
	}

	if (isset($field['content']['color'])) {
		$transparency = isset($field['content']['transparency']) ? true : false;
		$color_input_class = $transparency ? 'cms_color_input_transparent' : 'cms_color_input_notransparent';
		if (!isset($c['transparency'])) {
			$c['transparency'] = 1;
		}

		$content .= '<div class="cms_background_set_color set_form_subrow">';
		$content .= '<div class="sublabel">' . __('Barva pozadí', 'cms') . '</div>';
		$content .= '<input class="mw_input cms_color_input ' . $color_input_class . '" autocomplete="off" type="text" name="' . $name . '[color]" value="' . $c['color'] . '" ' . ($transparency ? 'data-opacity="' . $c['transparency'] . '"' : '') . ' />';

		if ($transparency) {
			if (!isset($c['rgba'])) {
				$c['rgba'] = '';
			}
			$content .= '<input class="cms_color_transparency" autocomplete="off" type="hidden" name="' . $name . '[transparency]" value="' . $c['transparency'] . '" />';
			$content .= '<input class="cms_color_rgba" autocomplete="off" type="hidden" name="' . $name . '[rgba]" id="' . $id . '_rgba" value="' . $c['rgba'] . '" />';
		}

		$content .= '</div>';
	}

	if (isset($field['content']['border'])) {
		$content .= '<div class="cms_background_set_border set_form_subrow set_form_subrow_b">';
		$val = isset($c['border']) && $c['border'] ? 1 : 0;
		$content .= cms_generate_field_switch($name . '[border]', $id . '_border', $val, ['label' => __('Ohraničení', 'cms')], false);
		$content .= '</div>';
	}


	echo '<div class="cms_background_set_container">';

	$style = isset($c['color']) && $c['color'] ? 'background: ' . $c['color'] . ';' : '';
	$style .= 'border-radius: ' . (intval($corner) * 5) . 'px;';
	$title = '<div class="cms_background_set_preview" style="' . $style . '"></div>';

	echo mwAdminComponents::hiddenSetting([
		'label' => $title,
		'content' => $content,
	]);
	echo '</div>';
}

/*
function field_type_border($field, $meta, $group_id) {

$class="";
if(!isset($field['content']['style'])) $class='cms_border_set_no_style';

?>
<div class="cms_border_set_container <?php echo $class; ?>">
<?php if(isset($field['content']['size'])) { ?>
<div class="cms_border_set_size">
<?php
echo '<select name="'.$group_id.'['.$field['id'].'][size]">';
for($i=0;$i<11;$i++) {
echo '<option '.(($meta['size']==$i)? 'selected="selected"':'').' value="'.$i.'">'.$i.'px</option>';
}
echo '</select>';
?>
</div>
<?php }
if(isset($field['content']['style'])) { ?>
<div class="cms_border_set_style">
<select class="cms_border_set_style_val" name="<?php echo $group_id.'['.$field['id'].']'; ?>[style]">
<option <?php if($meta['style']=="solid") echo 'selected="selected"'; ?> value="solid"><?php echo __('Plná', 'cms'); ?></option>
<option <?php if($meta['style']=="dashed") echo 'selected="selected"'; ?> value="dashed"><?php echo __('Čárkovaná', 'cms'); ?></option>
<option <?php if($meta['style']=="dotted") echo 'selected="selected"'; ?> value="dotted"><?php echo __('Tečkovaná', 'cms'); ?></option>
</select>
</div>
<?php }  else {
echo '<input class="cms_border_set_style_val" type="hidden" name="'.$group_id.'['.$field['id'].']'.'[style]" value="solid" />';
}
if(isset($field['content']['color'])) { ?>
<div class="cms_border_set_color">
<input class="mw_input cms_color_input" type="text" name="<?php echo $group_id.'['.$field['id'].']'; ?>[color]" value="<?php echo $meta['color']; ?>" />
</div>
<?php } ?>
<div class="cms_clear"></div>
</div>
<?php

}*/
function field_type_button($field, $meta, $name_id, $tag_id)
{
	global $vePage;
	$content = $meta ?? ($field['content'] ?? []);

	$name = $name_id . '[' . $field['id'] . ']';
	$id = $tag_id . '_' . $field['id'];

	if (!isset($content['style'])) {
		$content['style'] = 'basic';
	}
	if (!isset($content['button_size'])) {
		$content['button_size'] = 'medium';
	}
	if (!isset($content['custom_size'])) {
		$content['custom_size'] = '18';
	}

	$buttons = get_option('ve_buttons');
	if ($content['style'] != 'custom_button' && !isset($buttons['buttons'][$content['style']])) {
		$content['style'] = 'basic';
	}

	if (!isset($content['custom_setting'])) {
		$content['custom_setting'] = $buttons['buttons']['basic'];
	}

	echo '<div class="cms_button_selector_container">';

	$class = $content['style'] == 'custom_button' ? 'cms_button_custom_selected' : '';
	$type = $content['style'] == 'custom_button' ? $content['custom_setting']['style'] : $buttons['buttons'][$content['style']]['style'];

	$bgtype = isset($buttons['buttons'][$content['style']]) && is_array($buttons['buttons'][$content['style']]) ? getButtonBgType($buttons['buttons'][$content['style']]) : 'normal';
	$class .= ' cms_button_selected_bg_' . $bgtype;

	echo '<div href="#" class="cms_button_selected ' . $class . '">';
	echo '<div class="ve_content_button ve_content_button_type_' . $type . ' ve_content_button_style_' . $content['style'] . '">' . __('Text tlačítka', 'cms_ve') . '</div>';
	echo '<a href="#" class="mw_open_custom_button_edit" title="' . __('Upravit', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-edit-2"></use></svg></a>';
	echo '<span><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-chevron-down"></use></svg></span>';
	echo '</div>';

	echo '<div class="cms_button_selector">';

	foreach ($buttons['buttons'] as $key => $button) {
		echo print_button_selector_item($key, $button, $content, $name);
	}
	echo '<label class="cms_button_selector_item cms_button_selector_custom_item ' . ($content['style'] == 'custom_button' ? ' cms_button_selector_item_selected' : '') . '">';
	echo '<input type="radio" class="cms_button_selector_item_radio" autocomplete="off" name="' . $name . '[style]" data-type="' . $content['custom_setting']['style'] . '" data-hover="' . $content['custom_setting']['hover_effect'] . '" value="custom_button" ' . ($content['style'] == 'custom_button' ? 'checked="checked"' : '') . ' />';
	echo '<div class="ve_content_button ve_content_button_type_1 ve_content_button_style_custom_button">' . __('Vlastní tlačítko', 'cms_ve') . '</div>';
	echo '<span><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-check"></use></svg></span>';
	echo '</label>';

	echo '</div>';

	echo '<div class="mw_custom_button_setting_window_container">';
	echo '<div class="mw_custom_button_setting_window_overlay"></div>';
	echo '<div class="mw_custom_button_setting_window mw_admin_setting_container mwb_fade_animation">';
	ftb_button_item($content['custom_setting'], $id . '_custom_button', $name . '[custom_setting]', $id . '_custom_setting', false, 'single', $field['id']);
	echo '<div class="mw_custom_button_setting_window_footer">';
	echo '<a class="mw_custom_button_setting_close" href="#">' . __('HOTOVO', 'cms_ve') . '</a>';
	echo '</div>';
	echo '</div>';

	echo '</div>';

	?>
	<div class="set_form_subrow cms_button_selector_size">
		<div class="label"><?php echo __('Velikost tlačítka', 'cms'); ?></div>
	<?php
	$select_arr = [
		'options' => [
			['name' => __('Malé', 'cms'), 'value' => 'small'],
			['name' => __('Střední', 'cms'), 'value' => 'medium'],
			['name' => __('Velké', 'cms'), 'value' => 'big'],
		],
	];
	if (!isset($field['hide']) || !in_array('custom_size', $field['hide'])) {
		$select_arr['options'][] = ['name' => __('Vlastní', 'cms'), 'value' => 'custom'];
	}
	echo cms_generate_field_select($name . '[button_size]', $id . '_button_size', $content['button_size'] ?? '', $select_arr);

	echo '<div class="set_form_subrow mw_button_field_size_container ' . ($content['button_size'] != 'custom' ? 'cms_nodisp' : '') . '">';
	$custom_size = $content['custom_size'] ?? 18;
	cms_generate_field_slider($name . '[custom_size]', $id . '_custom_size', $custom_size, ['setting' => ['min' => '10', 'max' => '50', 'unit' => 'px']]);
	echo '</div>';
	?>
	</div>
	<?php

	echo '</div>';
}

function getButtonBgType(array $button): string
{
	$isLight = false;
	$isDark = false;
	if ($button['background_color']['color1'] && $button['style'] != '12' && $button['style'] != '13') {
		$contrast = Colors::getColorContrast($button['background_color']['color1']);
		if ($contrast > 200) {
			$isLight = true;
		}
		if ($contrast < 100) {
			$isDark = true;
		}
	} else {
		if ($button['border-color'] && $button['style'] != '13') {
			$contrast = Colors::getColorContrast($button['border-color']);

			if ($contrast > 200) {
				$isLight = true;
			}

			if ($contrast < 100) {
				$isDark = true;
			}
		} elseif ($button['font-color']) {
			$contrast = Colors::getColorContrast($button['font-color']);

			if ($contrast > 200) {
				$isLight = true;
			}
			if ($contrast < 100) {
				$isDark = true;
			}
		}
	}

	return $isLight ? 'invers' : ($isDark ? 'dark' : 'normal');
}

function print_button_selector_item($key, $button, $val, $name)
{
	global $vePage;
	$content = '';

	if ($key == 'basic') {
		$text = __('Základní vzhled', 'cms_ve');
	} elseif ($key == 'inverse') {
		$text = __('Inverzní vzhled', 'cms_ve');
	} else {
		$text = __('Vlastní vzhled', 'cms_ve') . ' ' . $key;
	}

	$bgtype = getButtonBgType($button);

	$content .= '<label class="cms_button_selector_item cms_button_selector_item_bg_' . $bgtype . ' ' . ($key === $val['style'] ? ' cms_button_selector_item_selected' : '') . '" data-bgtype="' . $bgtype . '">';
	$content .= '<input class="cms_button_selector_item_radio" autocomplete="off" type="radio" name="' . $name . '[style]" data-type="' . $button['style'] . '" data-hover="' . $button['hover_effect'] . '" value="' . $key . '" ' . ($val['style'] == $key ? 'checked="checked"' : '') . ' />';
	$content .= '<div class="ve_content_button ve_content_button_type_' . $button['style'] . ' ve_content_button_style_' . $key . '">' . $text . '</div>';
	$content .= '<span><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-check"></use></svg></span>';
	$content .= '</label>';

	$buttons_css = $vePage->builder->css->createCssContainer();
	$buttons_css->addStyles(
		[
			'padding-top' => $button['height_padding'] . 'em',
			'padding-bottom' => $button['height_padding'] . 'em',
			'padding-left' => $button['width_padding'] . 'em',
			'padding-right' => $button['width_padding'] . 'em',
			'font' => $button['font'],
			'color' => $button['font-color'],
			'border-width' => $button['style'] == '12' || $button['style'] == '4' ? $button['border_width'] . 'px' : '',
			'border-color' => $button['style'] == '12' || $button['style'] == '4' ? $button['border-color'] : '',
			'corner' => $button['corner'] . 'px',
			'bg' => ['background_color' => $button['background_color']],
		],
		'.cms_button_selector_container .ve_content_button_style_' . $key
	);
	$content .= $vePage->builder->css->printCss($buttons_css, '', true);

	return $content;
}

function field_type_google_map($field, $meta, $group_id, $tag_id)
{
	$id = $group_id . '_' . $field['id'];
	$name = $tag_id . '[' . $field['id'] . ']';

	$content = $meta ?: $field['content'];
	$zoom = $content['zoom'] ?: 10;

	$gmap_api = mwApiConnect()->getApi('google_maps');
	$gmap_api_connected = $gmap_api->isConnected();

	echo '<div class="mw_google_map_container">';

	// if is no api key saved
	if (!$gmap_api_connected) {
		echo $gmap_api->printConnectionButton('data-tagid="' . $id . '" data-name="' . $name . '"');
		?>

		<style>
			.cms_show_group_ve_style_google_map {
				display: none;
			}
		</style>

		<?php
	}

	echo '<div class="mw_google_map_setting_container ' . (!$gmap_api_connected ? 've_nodisp' : '') . '">';

	?>
	<div class="set_form_row_nb mw_gm_setting_address">
		<div class="label"><?php echo __('Adresa', 'cms'); ?></div>
		<input id="mw_gm_autocomplete" class="mw_input" type="text" name="<?php echo $name . '[address]'; ?>"
			   id="<?php echo $id . '_address'; ?>" value="<?php echo $content['address']; ?>"/>
		<span class="mw_description"><?php __('Zadejte adresu, kterou chcete na mapě vyznačit.', 'cms'); ?></span>
	</div>
	<div class="set_form_row mw_gm_setting_zoom">
		<div class="label"><?php echo __('Zoom mapy', 'cms'); ?></div>
		<?php
		cms_generate_field_slider($name . '[zoom]', $id . '_zoom', $zoom, ['setting' => ['min' => '0', 'max' => '20', 'unit' => '']]);
	echo '</div>';

	echo '</div>';

	echo '</div>'; //mw_google_map_container
}

function field_type_events_list($field, $meta, $group_name, $group_id)
{
	$args = [
		'posts_per_page' => -1,
		'post_type' => MW_EVENT_SLUG,
		'orderby' => 'meta_value_num',
		'meta_key' => 'mw_event_date_start',
		'order' => 'ASC',
	];
	$items = get_posts($args);

	$old = [];

	echo '<a href="' . mwSetting()->getObject('mw_event')->getUrl() . '" class="cms_button_secondary" target="_blank">' . __('Administrace akci', 'cms') . '</a>';

	echo '<div class="mw_events_setting_list">';
	if (!empty($items)) {
		foreach ($items as $item) {
			$event_date = get_post_meta($item->ID, 'mw_event_date_start', true);

			$event_setting = get_post_meta($item->ID, 've_event', true);

			$date_end = isset($event_setting['date_end']) && $event_setting['date_end'] && $event_date <= strtotime($event_setting['date_end']) ? ' - ' . date('j.n.', strtotime($event_setting['date_end'])) : '';

			if ($event_date && $event_date > current_time('timestamp')) {
				echo '<a class="mw_events_setting_list_item" href="' . mwSetting()->getObject('mw_event')->getEditUrl($item->ID) . '" target="_blank">';
				echo '<div class="mw_events_setting_list_item_title"><span>' . date('d.m.', $event_date) . $date_end . '</span> ' . $item->post_title . '</div>';
				echo '<div class="mw_events_setting_list_item_hover">' . mw_icon('icon-edit-2') . '</div>';
				echo '<div class="cms_clear"></div></a>';
			} else {
				$old[] = $item;
			}
		}

		if (!empty($old)) {
			echo '<div class="label">' . __('Již proběhlé akce', 'cms') . '</div>';
			foreach ($old as $item) {
				$event_setting = get_post_meta($item->ID, 've_event', true);

				$date_end = isset($event_setting['date_end']) && $event_setting['date_end'] && $event_date <= strtotime($event_setting['date_end']) ? ' - ' . date('j.n.', strtotime($event_setting['date_end'])) : '';

				$event_date = get_post_meta($item->ID, 'mw_event_date_start', true);
				echo '<a class="mw_events_setting_list_item" href="' . mwSetting()->getObject('mw_event')->getEditUrl($item->ID) . '" target="_blank">';
				echo '<div class="mw_events_setting_list_item_title"><span>' . date('d.m.', $event_date) . $date_end . '</span> ' . $item->post_title . '</div>';
				echo '<div class="mw_events_setting_list_item_hover">' . mw_icon('icon-edit-2') . '</div>';
				echo '<div class="cms_clear"></div></a>';
			}
		}
	} else {
		echo '<div>' . __('Nejsou vytvořené žádné akce. Správa seznamu akcí se nachází v administraci wordpressu.', 'cms') . '</div>';
	}
	echo '</div>';
}

function field_type_smtp_test($field, $meta, $group_name, $group_id)
{
	echo '<div class="mw_smtp_test_container">';

	echo '<div class="mw_smtp_test_info cms_nodisp"></div>';

	echo '<div class="mw_smtp_test_form mw_flex_field">';
	echo '<input class="mw_input mw_smtp_test_email" autocomplete="off" type="text" name="mw_smtp_test_email" value="" placeholder="' . __('Zadejte email', 'cms') . '" />';
	echo mwAdminComponents::button([
		'button_text' => mw_content_icon_file('loading-t', MW_UI_ICONS_URL . 'loading.svg') . '<span>' . __('Odeslat testovací email', 'cms_ve') . '</span>',
		'style' => 'secondary',
	], 'mw_smtp_test_send');
	echo '</div>';

	echo '</div>';
}

function field_type_api_login($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	//print_r($content);
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	foreach ($field['fields'] as $f) {
		echo '<div class="set_form_subrow">';
		echo '<div class="sublabel">' . $f['name'] . '</div>';
		echo mwAdminComponents::input([
			'autocomplete' => 'off',
			'name' => $name . '[' . $f['id'] . ']',
			'id' => $id . '_' . $f['id'],
		], $content[$f['id']]);
		echo '<input type="hidden" autocomplete="off" name="' . $name . '[old_' . $f['id'] . ']" value="' . $content[$f['id']] . '" />';
		echo '</div>';
	}

	$connected = 0;
	if (isset($content['status']) && $content['status']) {
		$connected = 1;
	}

	echo '<div class="set_form_subrow connection_status_container connection_status_' . $connected . '">';
	echo '<div class="sublabel">' . __('Stav spojení', 'cms') . '</div>';
	echo '<div class="connection_status_valid">' . __('Připojeno', 'cms') . '</div>';
	echo '<div class="connection_status_invalid">'
	. '<span>' . __('Nepřipojeno', 'cms') . '</span>'
	. (isset($content['error']) && $content['error'] ? '<div class="connection_status_invalid_error">' . __('Chyba', 'cms') . ': ' . $content['error'] . '</div>' : '')
	. '</div>';
	echo '</div>';
}

function field_type_funnel_page($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	global $wpdb;

	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$val = $meta ?? ($field['content'] ?? '');

	$funnel_pages = get_pages([
		'post_status' => 'publish',
		'meta_key' => FUNNEL_POST_META,
	]);
	$expages = [];
	foreach ($funnel_pages as $expage) {
		if ($expage->ID != $meta) {
			$expages[] = $expage->ID;
		}
	}

	$class = '';
	$abTest = false;

	if (isset($all_meta['ab_page']) && $all_meta['ab_page']) {
		if (get_post_status($all_meta['ab_page']) == 'publish') {
			$abTest = true;
			$class = 'mw_fps_with_ab';
		} else {
			$all_meta['ab_page'] = '';
		}
	}

	$content = '<div class="mw_funnel_page_setting_container ' . $class . '">';

	$content .= '<div class="mw_fps_select ' . ($meta ? 'mw_fps_selected' : '') . '">';
	$content .= mwAdminComponents::selectPage([
		'name' => $name,
		'tag_id' => $id,
		'add_button' => true,
		'edit_button' => true,
		'whisperer' => true,
	], $meta);

	$button_class = !$meta ? 'cms_nodisp' : '';
	$content .= mwAdminComponents::button([
			'button_text' => __('Vytvořit A/B test', 'mw_funnels'),
			'icon' => 'plus',
	], 'mwf_create_ab_test');
	$content .= '</div>';

	$content .= '<div class="mw_fps_ab">';
	if ($abTest) {
		$content .= MWF()->abTestSetting($meta, $all_meta['ab_page']);
	}
	$content .= '</div>';

	$content .= mwAdminComponents::input([
			'name' => $group_name . '[ab_page]',
			'tag_id' => '',
			'type' => 'hidden',
	], $all_meta['ab_page'] ?? '', 'mw_fps_ab_page');

	$content .= '</div>';

	echo $content;
}

function field_type_simple_table_list($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$val = $meta ?? ($field['content'] ?? []);

	$table = [];
	$table['head'] = [];
	foreach ($field['cols'] as $col) {
		$align = '';
		if ($col['type'] == 'radio_check') {
			$align = 'center';
		} elseif ($col['type'] == 'actions') {
			$align = 'right';
		}
		$table['head'][] = [
			'content' => $col['title'],
			'align' => $align,
		];
	}
	$item = 0;
	foreach ($val as $row) {
		$table['rows'][] = MwFields::generateSimpleTableRow($row, $field, $name . '[' . $item . ']', $id . '_' . $item);
		$item++;
	}

	$content = '<div class="mw_simple_table_list">';
	$content .= mwAdminComponents::table($table, 'mw_table_list2');
	$content .= mwAdminComponents::button([
		'button_text' => $field['texts']['add'] ?? __('Přidat', 'cms'),
		'attrs' => 'data-title="' . $field['texts']['add'] . '" data-id="' . $item . '" data-tagid="' . $id . '" data-name="' . $name . '" data-set="' . base64_encode(serialize($field)) . '"',
		'icon' => 'plus',
	], 'mw_add_simple_table_list_item');
	$content .= '</div>';
	echo $content;
}

function field_type_term_select($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];
	$val = $meta ?? ($field['content'] ?? []);

	echo MwFields::termSelect($field, $val, $name, $id);
}

function field_type_item_select($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];
	$val = $meta ?? ($field['content'] ?? 0);

	echo MwFields::itemSelect($field, $val, $name, $id);
}

function field_type_item_multi_select($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];
	$val = $meta ?? ($field['content'] ?? []);

	echo MwFields::itemMultiSelect($field, $val, $name, $id);
}

function field_type_code_list($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];
	$val = $meta ?? ($field['content'] ?? []);
	echo MwFields::codeList($val, $name, $id, $field);
}

function field_type_language_select($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$val = $meta ?? ($field['content'] ?? []);
	wp_dropdown_languages([
		'show_available_translations' => false,
		'languages' => get_available_languages(),
		'selected' => $val,
		'id' => $id,
		'name' => $name,
	]);
}


function field_type_user_roles_select($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$val = $meta ?? ($field['content'] ?? '');

	$all_roles = wp_roles()->roles;

	$options = [];
	foreach ($all_roles as $role => $details) {
		$options[] = [
			'value' => $role,
			'name' => translate_user_role($details['name']),
		];
	}

	echo mwAdminComponents::select([
		'name' => $name,
		'tag_id' => $id,
		'options' => $options,
	]);
}

function field_type_transaction_email($field, $meta, $group_name, $group_id, $post_id, $all_meta)
{
	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	$val = isset($meta) && $meta ? $meta : ($field['content'] ?? []);

	if (isset($field['content']['enabled'])) {
		$enabled = (bool) ($val['enabled'] ?? $field['content']['enabled']);

		echo mwAdminComponents::statusSwitch([
			'true_val' => '1',
			'false_val' => '0',
			'name' => $name . '[enabled]',
			'switch_label' => __('Povolit odesílání tohoto e-mailu z Miowebu', 'cms'),
			'tooltip' => __('E-mail s oznámením o přijetí objednávky je standardně odesílán z FAPI, ale v některých případech nemusí obsahovat kompletní informace, které doplňuje e-mail z Miowebu (například informace o doručení pomocí Zásilkovny). V případě, že povolíte odesílání e-mailu z Miowebu, obdrží zákazník dva e-maily. Pokud odesílání zakážete, obdrží zákazník pouze jeden e-mail z FAPI.', 'cms'),
		], $enabled ? '1' : '0');
	}

	$email = new Email(
		null,
		$val['subject'] ?? '',
		$val['content'] ?? '',
		$val['attachment'] ?? null,
	);

	Email::emailField($email, $field, $name, $field['content'] ?? null);
}

function field_type_emails($field, $meta, $group_name, $group_id, $postId, $all_meta)
{
	$emails = Email::getAll($field['module'], $postId);
	if (empty($emails) && isset($field['content'])) {
		foreach ($field['content'] as $type => $email) {
			$emails[$type] = new Email(null, $email['subject'], $email['content'], $email['attachment'] ?? null, $field['module'], $type, $postId);
		}
	}
	$tagName = isset($field['ignore_id_in_field_names']) ? $group_name : getTagName($group_name, $field['id']);

	Email::emailsField($emails, $field, $tagName);
}

function field_type_item_set($field, $meta, $group_name, $group_id, $postId, $all_meta)
{
	$val = $meta ?? ($field['content'] ?? '');

	echo MwFields::itemSet($field, $val, $group_name, $group_id, $postId);
}
function field_type_post_title($field, $meta, $group_name, $group_id, $postId, $all_meta)
{
	$item = null;
	$post = get_post($postId);
	if ($post) {
		$object = mwSetting()->getObject($post->post_type);
		$item = $object->service()->getItem($postId);
	}

	echo MwFields::itemTitle($item, $field);
}

function field_type_user_contact_info($field, $meta, $group_name, $group_id, $userId, $all_meta)
{
	$val = $meta ?? ($field['content'] ?? '');
	echo MwFields::userContactInfo($field, $val, getTagName($group_name, $field['id']), '', $userId);
}

function field_type_user_password($field, $meta, $group_name, $group_id, $postId, $all_meta)
{
	wp_enqueue_script('password-strength-meter');

	echo '<div class="mw_user_password_fieldtype ' . ($postId ? 'mw_user_password_fieldtype_setted hide_setting' : '') . '">';

	if ($postId) {
		echo mwAdminComponents::button([
			'button_text' => __('Nastavit nové heslo', 'cms'),
			'style' => 'secondary',
		], 'mw_user_password_fieldtype_setnew');
	}

	echo '<div class="mw_flex_field mw_user_password_fieldtype_setting">';

	$val = $postId ? '' : mwSetting()->generatePassword();
	echo mwAdminComponents::input([
		'name' => getTagName($group_name, $field['id']),
		'id' => $group_id . '_' . $field['id'],
		'attrs' => 'data-password="' . ($val ?: mwSetting()->generatePassword()) . '"',
	], $val, 'mw_user_password_fieldtype_input');

	echo mwAdminComponents::iconLink([
		'icon' => 'eye-off',
		'title' => __('Skrýt heslo', 'cms'),
	], 'mw_icon_button mw_user_password_fieldtype_hide');

	echo mwAdminComponents::button([
		'button_text' => __('Generovat heslo', 'cms'),
		'style' => 'secondary',
	], 'mw_user_password_fieldtype_generate');

	echo mwAdminComponents::button([
		'button_text' => __('Zrušit', 'cms'),
		'style' => 'secondary',
	], 'mw_user_password_fieldtype_cancel');

	echo '</div>';

	echo '<div class="mw_user_password_fieldtype_strength strong">' . __('Bezpečné', 'cms') . '</div>';

	echo '</div>';
}

function field_type_timezone_select($field, $meta, $group_name, $group_id, $postId, $all_meta)
{
	$val = $meta ?? ($field['content'] ?? '');

	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	echo MwFields::timeZoneSelect($field, $val, $name, $id);
}

function field_type_date_time_format_select($field, $meta, $group_name, $group_id, $postId, $all_meta)
{
	$val = $meta ?? ($field['content'] ?? '');

	$name = getTagName($group_name, $field['id']);
	$id = $group_id . '_' . $field['id'];

	echo MwFields::dateTimeFormatSelect($field, $val, $name, $id);
}

function field_type_conversion_code($field, $meta, $group_name, $group_id)
{
   $name = getTagName($group_name, $field['id']);
   $id = $group_id . '_' . $field['id'];

   $value = $meta ?? ($field['content'] ?? '');

   echo MwFields::conversionCode($value, $name, $id, $field);
}

function field_type_plugin_blocker($field, $meta, $group_name, $group_id)
{
   $name = getTagName($group_name, $field['id']);
   $id = $group_id . '_' . $field['id'];
   $value = $meta ?? ($field['content'] ?? []);

   echo MwFields::pluginBlocker($field, $value, $name, $id);
}

function field_type_gdpr_purpose_select($field, $meta, $group_name, $group_id)
{
	$name = getTagName($group_name, $field['id']);
	$val = $meta ?? ($field['content'] ?? '');
	echo mwAdminComponents::gdprPurposeSelect([
		'name' => $name,
	], $val);
}

function field_type_interval_table($field, $meta, $group_name, $group_id)
{
	$max_name = $field['fields']['max_name'] ?? __('Maximální hodnota', 'cms_ve');
	$max_unit = $field['fields']['max_unit'] ?? '';
	$decimals = $field['fields']['decimals'] ?? 3;
	$int_val_name = $field['fields']['int_val_name'] ?? __('Hodnota', 'cms_ve');
	$int_val_unit = $field['fields']['int_val_unit'] ?? '';
	$int_name = $field['fields']['int_name'] ?? __('Rozmezí', 'cms_ve');
	$content = $meta ?? ($field['content'] ?? '');
	$field_id = $field['id'] ?? 'intervals';
	$name = $group_id . '[' . $field_id . ']';

	$data_attrs
		= 'data-max-name="' . $max_name .
		'" data-int-name="' . $int_name .
		'" data-int-val-name="' . $int_val_name .
		'" data-decimals="' . $decimals .
		'" data-max-unit="' . $max_unit .
		'" data-int-val-unit="' . $int_val_unit .
		'" data-confirm="' . __('Přejete si smazat tuto položku?', 'cms_ve') .
		'" data-name="' . $name . '"';
	?>
	<div class="mw_admin_setting_table_container">
		<table class="mw_table mw_table_style_3 mw_interval_table" <?php echo $data_attrs; ?>>
			<thead>
			<tr>
				<th class="mw_align_left"><?php echo $max_name; ?></th>
				<th class=" mw_align_right"><?php echo $int_val_name; ?></th>
				<th class=" mw_align_right"><?php echo __('Akce', 'cms_ve'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
				cms_generate_intervals($content, $max_name, $decimals, $max_unit, $int_val_unit, $name);
			?>
			</tbody>
		</table>
		<a href="#" class="mw_setting_action_link mw_interval_add">+ <?php echo __('Přidat', 'cms_ve') . ' ' . lcfirst($int_name) ?></a>
	</div>
	<?php
}

function cms_generate_intervals($content, $max_name, $decimals, $max_unit, $int_val_unit, $name)
{
	if (!empty($content)) {
		usort($content, function ($a, $b) {
			return ($a['max_val'] ?? INF) <=> ($b['max_val'] ?? INF);
		});

		$min_value = 0.0;
		$granularity = pow(10, -1 * $decimals);

		foreach ($content as $key => $interval) {
			$min = number_format($min_value, $decimals);
			$max = isset($interval['max_val']) && $interval['max_val'] && $interval['max_val'] !== '∞' ?
					number_format((float) $interval['max_val'], $decimals) :
					'∞';
			?>
		<tr>
			<td><?php echo str_replace('.', ',', $min) . ' - ' . str_replace('.', ',', $max) . ($max_unit ? ' ' . $max_unit : ''); ?></td>
			<td class="mw_align_right"><?php echo str_replace('.', ',', ($interval['int_val'] ?? 0)) . ($int_val_unit ? ' ' . $int_val_unit : ''); ?></td>
			<td class="mw_align_right">
				<input class="cms_nodisp max_val" autocomplete="off" type="text" name="<?php echo $name . '[' . $key . '][max_val]" value="' . $max ?>">
				<input class="cms_nodisp int_val" autocomplete="off" type="text" name="<?php echo $name . '[' . $key . '][int_val]" value="' . ($interval['int_val'] ?? 0) ?>">
				<div class="mw_table_actions">
					<?php echo mwAdminComponents::dropIcon(
							['items' => [
									0 => [
										'class' => 'mw_interval_edit',
										'text' => __('Upravit', 'cms_ve'),
										'attrs' => 'data-id="' . $key . '"',
									],
									1 => [
											'class' => 'mw_interval_delete',
											'text' => __('Smazat', 'cms_ve'),
											'attrs' => 'data-id="' . $key . '"',
									],
							],
							]
					);
					?>
				</div>
			</td>
		</tr>
		<?php
			if ($interval['max_val'] !== '∞') {
				$min_value = ((float) $interval['max_val'] ?? 0) + $granularity;
			}
		}
	} else {
		echo '<tr><td><span class="mw_description">' .
				__('Přidejte', 'cms_ve') . ' ' . lcfirst($max_name) . '.' .
				'<input class="cms_nodisp" autocomplete="off" type="text" name="' . $name . '" value="">' .
				'</span></td><td></td><td></td></tr>';
	}
}

function cms_generate_intervals_ajax()
{
	$content = $_POST['content'] ?? [];
	$success = true;

	foreach ($content as $key => &$interval) {
		if ($interval['max_val'] === '') {
			$interval['max_val'] = '∞';
		}
		if ($interval['int_val'] === '') {
			mwMessages()->error($_POST['int_val_name'] . ' ' . __('je povinný údaj.', 'cms_ve'));
			$success = false;
		} elseif ((!is_numeric($interval['max_val']) && $interval['max_val'] !== '∞') || !is_numeric($interval['int_val'])) {
			mwMessages()->error(__('Hodnoty musí být číselné.', 'cms_ve'));
			$success = false;
		}
	}
	// check if there are duplicate max values
	$max_values = array_map('floatval', array_column($content, 'max_val'));
	if (count($max_values) !== count(array_unique($max_values))) {
		mwMessages()->error(__('Tato', 'cms_ve') . ' ' . lcfirst($_POST['int_name']) . ' ' . __('je již zadána.', 'cms_ve'));
		$success = false;
	}

	$result = [];

	if ($success) {
		ob_start();
		cms_generate_intervals($content, $_POST['max_name'], $_POST['decimals'], $_POST['max_unit'], $_POST['int_val_unit'], $_POST['name']);
		$result['content'] = ob_get_contents();
		ob_end_clean();
	}

	wp_send_json([
					'success' => mwMessages()->success,
					'errors' => mwMessages()->errors,
					'html' => mwMessages()->writeHtml(),
	] + $result);
	die();
}

add_action('wp_ajax_cms_generate_intervals', 'cms_generate_intervals_ajax');

function cms_add_or_change_interval_form_ajax()
{
	$action_header = $_POST['edit'] === 'true' ? __('Upravit', 'cms_ve') : __('Přidat', 'cms_ve');
	$max_val = $_POST['max_val'];
	$int_val = $_POST['int_val'];

	echo '<h3>' . $action_header . ' ' . lcfirst($_POST['int_name']) . '</h3>';
	echo '<table class="mw_table">';
	echo '<tr>
				<td>' .
					$_POST['max_name'] . ' ' . __('do (včetně)', 'cms_ve') .
					mwAdminComponents::tooltip(['text' => __('Pokud váhu nenastavíte, vytvoří se rozmezí do nekonečna.', 'cms_ve')]) .
				'</td>
				<td>' . mwAdminComponents::inputNumber([
							'name' => 'max_val',
							'step' => pow(10, -1 * $_POST['decimals']),
							'unit' => $_POST['max_unit'],
							'min' => 0,
							'placeholder' => '∞',
], $max_val) .
				'</td>
			</tr>
			<tr>
				<td>' . $_POST['int_val_name'] . '</td>
				<td>' . mwAdminComponents::inputNumber([
							'name' => 'int_val',
							'unit' => $_POST['int_val_unit'],
							'min' => 0,
], $int_val) .
				'</td>
			</tr>';
	echo '</table>';

	die();
}

add_action('wp_ajax_cms_add_or_change_interval_form', 'cms_add_or_change_interval_form_ajax');
