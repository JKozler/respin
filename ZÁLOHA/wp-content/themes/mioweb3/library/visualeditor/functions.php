<?php

use Mioweb\VisualEditor\Lib\Button;

function mw_icon($icon, $class = '', $url = '')
{
	if ($class) {
		$class = 'class="' . $class . '"';
	}
	if (!$url) {
		$url = MW_UI_ICONS_URL . 'symbol-defs.svg';
	}

	return '<svg role="img" ' . $class . '><use xlink:href="' . $url . '#' . $icon . '"></use></svg>';
}

function mw_content_icon($icon, $file = 'content-icons.svg')
{
	return '<svg role="img"><use xlink:href="' . MW_ICONS_URL . $file . '#' . $icon . '"></use></svg>';
}

function mw_content_icon_file($icon, $file = '')
{
	return '<svg><use href="' . $file . '#icon-' . $icon . '"></use></svg>';
}

function mw_content_icon_set($icon, $set = 'feather', $class = '')
{
	if ($class) {
		$class = 'class="' . $class . '"';
	}

	return '<svg role="img" ' . $class . '><use xlink:href="' . MW_ICONS_URL . $set . '/symbol-defs.svg#icon-' . $icon . '"></use></svg>';
}

function get_template_url_image()
{
	return str_replace(home_url(), '', get_bloginfo('template_url'));
}

function remove_editor_buttons_style()
{
	// If not on wp-admin
	if (!is_admin()) {
		wp_deregister_style('editor-buttons');
	}
}

function get_array_field($array, $key, $subkey = '')
{
	if (isset($array[$key])) {
		return $subkey && isset($array[$key][$subkey]) ? $array[$key][$subkey] : $array[$key];
	}

	return '';
}

add_action('wp_enqueue_scripts', 'remove_editor_buttons_style');

function field_type_event_category_select($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? 0);

	$items = get_categories(['taxonomy' => MW_EVENT_CAT_SLUG, 'hide_empty' => 0]);
	$options = [];
	$options[] = [
		'value' => '',
		'name' => __('- Všechny kategorie -', 'cms_ve'),
	];
	foreach ($items as $val) {
		$options[] = [
			'value' => $val->term_id,
			'name' => $val->name,
		];
	}
	$field['options'] = $options;

	cms_generate_field_select(
		$group_name . '[' . $field['id'] . ']',
		$group_id . '_' . $field['id'],
		$content,
		$field
	);
}


function field_type_page_statistics($field, $meta, $group_id, $tagid, $post_id)
{
	$con_meta = get_post_meta($post_id, 'page_conversion_rate', true);
	if ($con_meta && is_array($con_meta)) {
		?>
		<table class="mw_table mw_table_style_2 ve_page_statistic_field">
			<tr>
				<th><?php echo __('Stránka', 'cms_ve'); ?></th>
				<th><?php echo __('Počet zobrazení stránky', 'cms_ve'); ?></th>
				<th><?php echo __('Počet zobrazení cíle', 'cms_ve'); ?></th>
				<th><?php echo __('Konverzní poměr', 'cms_ve'); ?></th>
			</tr>
		<?php

		$i = 1;
		foreach ($con_meta as $id => $con) {
			$conversion = isset($con['con_target']) && $con['con_target'] > 0 && isset($con['con_source']) && $con['con_source'] > 0 ? $con['con_target'] / $con['con_source'] * 100 : 0;

			$class = $i ? 'class="odd"' : '';

			?>

				<tr <?php echo $class; ?>>
					<td>
			<?php if (get_page($id)) { ?>
							<a target="_blank"
							   href="<?php echo get_permalink($id); ?>"><?php echo get_the_title($id); ?></a>
			<?php } else {
				echo __('Stránka byla smazána.', 'cms_ve');
			} ?>
					</td>
					<td><?php echo $con['con_source'] ?? 0; ?></td>
					<td><?php echo $con['con_target'] ?? 0; ?></td>
					<td><?php echo number_format($conversion, 3, ',', ' ') . '%'; ?></td>
				</tr>

			<?php
			$i = $i ? 0 : 1;
		}
		?>
		</table>
		<button id="ve_reset_page_statistics" class="cms_button_secondary"
				data-id="<?php echo $post_id; ?>"><?php echo __('Vynulovat výsledky', 'cms_ve'); ?></button>
		<?php
	} else {
		?>
		<div><?php echo __('Momentálně nejsou k dispozici žádná data.', 'cms_ve'); ?></div>
		<?php
	}
}

function ve_reset_page_statistics_ajax()
{
	delete_post_meta($_POST['post_id'], 'page_conversion_rate');
	die();
}

add_action('wp_ajax_ve_reset_page_statistics', 've_reset_page_statistics_ajax');

function field_type_pagecheck($field, $meta, $group_name, $group_id)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');

	$pages = mw_get_pages();

	foreach ($pages as $page) {
		?>
		<div>
			<input type="checkbox" name="<?php echo $group_name . '[' . $field['id'] . '][' . $page->ID . ']'; ?>"
				   id="<?php echo $group_id . '_' . $field['id'] . '_' . $page->ID; ?>"
				   value="<?php echo $page->ID; ?>">
			<label
				for="<?php echo $group_id . '_' . $field['id'] . '_' . $page->ID; ?>"><?php echo $page->post_title ?></label>
		</div>
		<?php
	}
}

/* Simple feature
************************************************************************** */

function field_type_simple_feature($field, $meta, $group_id, $group_name)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');
	$feature_fields = $field['fields'] ?? ['text' => ['title' => __('Vlastnost', 'cms_ve')]];
	$name = $group_id . '[' . $field['id'] . ']';
	$id = $group_name . '_' . $field['id'];
	$isSortable = $field['sortable'] ?? false;

	$add_but_text = $field['text_add'] ?? __('Přidat vlastnost', 'cms_ve');

	?>
	<div class="ve_items_feature_container">
		<div class="ve_features_container <?php echo $isSortable ? 've_sortable_items' : ''; ?>">
	<?php
	$i = 0;
	if (!empty($content)) {
		foreach ($content as $key => $feature) {
			?>
			<div class="ve_item_feature_<?php echo $i; ?> ve_item_feature_container mw_flex_field">
			<?php ve_generate_simple_feature($name . '[' . $i . ']', $id . '_' . $i, $feature, $feature_fields); ?>
			</div>
			<?php
			$i++;
		}
	}
	?>
	</div>
	<?php

	echo mwAdminComponents::button([
		'icon' => 'plus',
		'button_text' => $add_but_text,
		'style' => 'secondary',
		'attrs' => 'data-fields="' . base64_encode(serialize($feature_fields)) . '" data-id="' . $i . '" data-name="' . $name . '" data-tagid="' . $id . '"',
	], 've_add_simple_feature');
	?>
	</div>
	<?php
}

function ve_generate_simple_feature($name, $id, $feature, $feature_fields)
{
	echo mwAdminComponents::icon(['icon' => 'move'], 've_sortable_handler');

	foreach ($feature_fields as $fId => $field) {
		$type = $field['type'] ?? 'text';

		if ($fId) {
			$fieldName = $name . '[' . $fId . ']';
			$value = $feature[$fId] ?? ($field['empty'] ?? '');
		} else {
			$fieldName = $name;
			$value = $feature;
		}


		if ($type === 'textarea') {
			echo mwAdminComponents::textarea([
				'name' => $fieldName,
			], $value);
		} elseif ($type === 'pageselect') {
			echo mwAdminComponents::selectPage([
			   'name' => $fieldName,
			   'show_empty' => false,
			], $value);
		} elseif ($type === 'hidden') {
			echo mwAdminComponents::input([
				'name' => $fieldName,
				'type' => 'hidden',
			], $value);
		} elseif ($type === 'select') {
			$options = $field['options'] ?? [];

			echo mwAdminComponents::select([
					'name' => $fieldName,
					'show_empty' => false,
					'options' => $options,
			], $value);
		} elseif ($type === 'upload_file') {
			cms_generate_field_upload_file($fieldName, '', $value);
		} else {
			echo mwAdminComponents::input([
				'name' => $fieldName,
				'placeholder' => $field['title'],
			], $value);
		}
	}

	echo mwAdminComponents::iconLink([
		'icon' => 'trash-2',
		'title' => __('Opravdu chcete položku smazat?', 'cms_ve'),
	], 'mw_icon_button ve_delete_feature');
}

function ve_generate_simple_feature_ajax()
{
	$item = [];
	ve_generate_simple_feature($_POST['tagname'] . '[' . $_POST['id'] . ']', $_POST['tagid'] . '_' . $_POST['id'], $item, unserialize(base64_decode($_POST['fields'])));
	die();
}

add_action('wp_ajax_ve_generate_simple_feature_ajax', 've_generate_simple_feature_ajax');

// Gallery lightbox
function my_get_attachment_link($html)
{
	$pattern = "/<a(.*?)href=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
	$replacement = '<a$1href=$2$3.$4$5 class="open_lightbox" rel="gallery"$6>';

	return preg_replace($pattern, $replacement, $html);
}

add_filter('wp_get_attachment_link', 'my_get_attachment_link', 10, 1);

function add_lightbox($content)
{
	$pattern = "/<a(.*?)href=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
	$replacement = '<a$1href=$2$3.$4$5 class="open_lightbox"$6>';

	return preg_replace($pattern, $replacement, $content);
}

// Paste plain text in editor
/*
function plainpaste_tinymce_settings($settings)
{
$settings['paste_text_sticky'] = 'true';
$settings['setup'] = 'function(ed) { ed.onInit.add(function(ed) { ed.pasteAsPlainText = true; }); }';

return $settings;
}*/
//add_filter('tiny_mce_before_init','plainpaste_tinymce_settings');


/* buttons editor
************************************************************************** */

function field_type_buttons_editor($field, $meta, $name_id, $tag_id)
{
	$content = $meta != '' ? $meta : ($field['content'] ?? '');

	$name = $name_id . '[' . $field['id'] . ']';
	$id = $tag_id . '_' . $field['id'];

	echo '<div class="mw_buttons_editor">';

	echo '<div class="mw_buttons_editor_items">';
	$last_key = 1;
	foreach ($content as $bkey => $button) {
		ftb_button_item($button, $bkey, $name . '[' . $bkey . ']', $id . '_' . $bkey);
		if ($bkey !== 'basic' && $bkey !== 'inverse') {
			$last_key = $bkey + 1;
		}
	}
	echo '</div>';

	echo mwAdminComponents::button([
		'icon' => 'plus',
		'button_text' => __('Přidat styl tlačítka', 'cms_ve'),
		'style' => 'secondary',
		'link' => '#',
		'attrs' => 'data-bkey="' . $last_key . '" data-name="' . $name . '"  data-id="' . $id . '"',
	], 'mw_buttons_editor_add');

	echo '</div>';
}

function ftb_button_item($content, $button_id, $name, $id, $added = false, $type = 'basic', $single_name = '')
{
	$height_p = $content['height_padding'] ?? '1';
	$width_p = $content['width_padding'] ?? '1.2';
	$border_w = $content['border_width'] ?? '';
	$corner = $content['corner'] ?? 0;
	$borderColor = $content['border-color'] ?? '#1a1a1a';
	$hoverColor = $content['hover_color'] ?? ['color1' => '', 'color2' => ''];
	$borderHoverColor = $content['border_hover-color'] ?? '';
	$font = $content['font'] ?? ['font-family' => '', 'weight' => ''];

	?>
	<div class="mw_setting_box ftb_button_item ftb_button_item_t_<?php echo $type; ?> <?php if ($added) {
			echo 'ftb_button_item_added ftb_button_item_opened';
																 } ?>"
		data-id="<?php echo $button_id; ?>">
		<div class="ftb_button_item_head ftb_button_item_head_<?php echo $button_id; ?>">
	<?php /*
	<div class="sublabel">

	if($button_id==="inverse") echo __('Inverzní','cms_ve');
	else if($button_id==="basic") echo __('Základní','cms_ve');
	else if(isset($content['name']) && $content['name']) echo $content['name'];
	else echo __('Vlatní','cms_ve').' '.($button_id+1);

	</div>
	*/ ?>
			<div class="ftb_button_item_button_container">
				<button
					class="mw_ftb_button_item_edit ve_content_button ve_content_button_type_<?php echo $content['style']; ?>  ve_content_button_style_<?php echo $button_id; ?>"><?php echo __('Text tlačítka', 'cms_ve') ?></button>
			</div>
			<div class="ftb_button_item_editbar">
	<?php
	if ($type == 'basic') {
		echo '<a href="#" class="mw_ftb_button_item_edit" title="' . __('Editovat', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-edit-2"></use></svg></a>';
		echo '<a href="#" class="mw_ftb_button_item_duplicate" title="' . __('Duplikovat', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-copy"></use></svg></a>';
		if ($button_id !== 'basic' && $button_id !== 'inverse') {
			echo '<a href="#" class="mw_ftb_button_item_delete" title="' . __('Smazat', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-trash-2"></use></svg></a>';
		}

		echo '<a href="#" class="mw_ftb_button_item_close mw_ftb_button_item_edit" title="' . __('Zavřít', 'cms_ve') . '"><svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-x"></use></svg></a>';
	} elseif ($type == 'single') {
		echo '<a href="#" class="mw_ftb_button_item_save" data-name="' . $single_name . '" title="' . __('Uložit do globálních tlačítek', 'cms_ve') . '">'
		. '<span>' . __('Uložit do globálních tlačítek', 'cms_ve') . '</span>'
		. '<svg role="img"><use xlink:href="' . MW_UI_ICONS_DEF . '#icon-save"></use></svg>'
		. '</a>';
		echo '<div class="mw_ftb_button_item_saved">'
		. '<span>' . __('Uloženo', 'cms_ve') . '</span>'
		. '<svg role="img"><use xlink:href="' . MW_UI_ICONS_URL . 'loading.svg#icon-loading"></use></svg>'
		. '</div>';
	}
	?>
			</div>
			<div class="cms_clear"></div>
	<?php

	global $vePage;
	$button_styles = $vePage->builder->css->createCssContainer();

	$button_styles->addStyles(
		[
			'padding-top' => $height_p . 'em',
			'padding-bottom' => $height_p . 'em',
			'padding-left' => $width_p . 'em',
			'padding-right' => $width_p . 'em',
			'font' => $font,
			'color' => $content['font-color'] ?? '',
			'border-width' => $content['style'] == '12' || $content['style'] == '4' ? $border_w . 'px' : '',
			'border-color' => $content['style'] == '12' || $content['style'] == '4' ? $borderColor : '',
			'bg' => ['background_color' => $content['background_color']],
		],
		'.ftb_button_item .ve_content_button_style_' . $button_id
	);

	$button_styles->addVariableStyles(
		[
			'.ftb_button_item .ve_content_button_style_' . $button_id => ['corner'],
		],
		'--button-corner-' . $button_id,
		$corner . 'px'
	);

	echo $vePage->builder->css->printCss($button_styles, 'button_' . $button_id . '_style', true);

	?>
		</div>
		<div class="ftb_button_item_setting <?php if (!$added && $type != 'single') {
			echo 'cms_nodisp';
											} ?>">

	<?php // tabs ?>
			<ul class="ftb_button_setting_tabs">
				<li>
					<a class="active" href="#ftb_button_tab_button"><?php echo __('Tlačítko', 'cms') ?></a>
				</li>
				<li>
					<a href="#ftb_button_tab_hover"><?php echo __('Hover', 'cms') ?></a>
				</li>
			</ul>

	<?php // tabs ?>
			<div id="ftb_button_tab_button" class="ftb_button_tab">

				<div class="ftb_button_set ftb_button_item_style">
					<div class="label"><?php echo __('Styl tlačítka', 'cms'); ?></div>
	<?php
	$button_options = [
		'1' => VS_DIR . 'images/image_select/button_1.jpg',
		'12' => VS_DIR . 'images/image_select/button_12.jpg',
		'2' => VS_DIR . 'images/image_select/button_2.jpg',
		'3' => VS_DIR . 'images/image_select/button_3.jpg',
		'4' => VS_DIR . 'images/image_select/button_4.jpg',
		'5' => VS_DIR . 'images/image_select/button_5.jpg',
		'6' => VS_DIR . 'images/image_select/button_6.jpg',
		'7' => VS_DIR . 'images/image_select/button_7.jpg',
		'8' => VS_DIR . 'images/image_select/button_8.jpg',
		'9' => VS_DIR . 'images/image_select/button_9.jpg',
		'10' => VS_DIR . 'images/image_select/button_10.jpg',
		'11' => VS_DIR . 'images/image_select/button_11.jpg',
		'13' => VS_DIR . 'images/image_select/button_13.jpg',
	];

	cms_generate_field_imageselect($name . '[style]', $id . '_style', $button_options, $content['style']);

	?>
				</div>

				<div
					class="ftb_button_set ftb_button_item_background_color ftb_button_optional_set ftb_button_optional_set_background">
					<div class="label"><?php echo __('Pozadí tlačítka', 'cms'); ?></div>
	<?php cms_generate_field_background($name . '[background_color]', $id . '_background_color', $content['background_color'], ['content' => ['gradient' => 1], 'hide_transparency' => true]); ?>
				</div>

				<div class="ftb_button_set ftb_button_item_font_color">
					<div class="label"><?php echo __('Barva písma', 'cms'); ?></div>
	<?php echo cms_generate_field_color($name . '[font-color]', $id . '_font-color', $content['font-color']) ?>
				</div>

				<div class="cms_clear"></div>

				<div
					class="ftb_button_set ftb_button_item_corner ftb_button_optional_set ftb_button_optional_set_corner">
					<div class="label"><?php echo __('Zakulacení rohů', 'cms'); ?></div>
					<?php
					//cms_generate_field_slider($name.'[corner]',$id.'_corner',$corner, array('setting'=>array('min'=>'0','max'=>'90','unit'=>'px')));
					$corners_options = [
						'0' => [
							'icon' => 'sharp_corner',
							'text' => __('Ostré', 'cms_ve'),
						],
						'8' => [
							'icon' => 'rounded_corner',
							'text' => __('Zakulacené', 'cms_ve'),
						],
						'9999' => [
							'icon' => 'round_corner',
							'text' => __('Kulaté', 'cms_ve'),
						],
					];
					cms_generate_field_imageoption_custom($name . '[corner]', $id . '_corner', $corners_options, $corner);
					?>
				</div>

				<div
					class="ftb_button_set ftb_button_item_height_padding ftb_button_optional_set ftb_button_optional_set_padding">
					<div class="label"><?php echo __('Výška', 'cms'); ?></div>
					<?php
					cms_generate_field_slider($name . '[height_padding]', $id . '_height_padding', $height_p, ['setting' => ['min' => '0.3', 'max' => '1.5', 'step' => '0.1', 'unit' => 'em']]); ?>
				</div>

				<div
					class="ftb_button_set ftb_button_item_width_padding ftb_button_optional_set ftb_button_optional_set_padding">
					<div class="label"><?php echo __('Šířka', 'cms'); ?></div>
					<?php
					cms_generate_field_slider($name . '[width_padding]', $id . '_width_padding', $width_p, ['setting' => ['min' => '0.4', 'max' => '3', 'step' => '0.1', 'unit' => 'em']]); ?>
				</div>

				<div class="cms_clear"></div>

				<div class="ftb_button_set ftb_button_item_font">
					<div class="label"><?php echo __('Font tlačítka', 'cms'); ?></div>
					<?php
					// text font
					$font_set = [
						'content' => [
							'font-family' => '',
							'weight' => '',
						],
						'visible' => ['font-family', 'weight'],
						'title' => 'Font tlačítka',
					];
					echo cms_generate_field_font($name . '[font]', $id . '_font', $font, $font_set, 'button_font');
					?>
				</div>

				<div
					class="ftb_button_set ftb_button_item_border_color ftb_button_optional_set ftb_button_optional_set_border">
					<div class="label"><?php echo __('Barva ohraničení', 'cms'); ?></div>
					<input class="button_border mw_input cms_color_input cms_change_button" type="text"
						   name="<?php echo $name . '[border-color]'; ?>"
						   value="<?php echo $borderColor; ?>"/>
				</div>

				<div
					class="ftb_button_set ftb_button_item_border_width ftb_button_optional_set ftb_button_optional_set_border">
					<div class="label"><?php echo __('Tloušťka ohraničení', 'cms'); ?></div>
					<?php
					cms_generate_field_slider($name . '[border_width]', $id . '_border_width', $border_w, ['setting' => ['min' => '0', 'max' => '5', 'step' => '1', 'unit' => 'px']]); ?>
				</div>

				<div class="cms_clear"></div>
			</div>


	<?php // ***** hover ******* ?>
			<div id="ftb_button_tab_hover" class="ftb_button_tab cms_nodisp">
				<div class="ftb_button_set ftb_button_item_hover">
					<div class="label"><?php echo __('Efekt po najetí myši', 'cms'); ?></div>
					<?php
					$select_efect = [
						'options' => [
							['name' => __('Zesvětlení', 'cms'), 'value' => 'lighter'],
							['name' => __('Zvětšení', 'cms'), 'value' => 'scale'],
							['name' => __('Ztmavení', 'cms'), 'value' => 'darker'],
							['name' => __('Vlastní', 'cms'), 'value' => ''],
						],
					];
					echo cms_generate_field_select($name . '[hover_effect]', $id . '_hover_effect', $content['hover_effect'] ?? '', $select_efect); ?>
				</div>

				<div class="cms_clear"></div>

				<div class="ftb_button_hover_setting <?php if ($content['hover_effect'] != '') {
					echo 'cms_nodisp';
													 } ?>">
					<div
						class="ftb_button_set ftb_button_item_hover_background_color ftb_button_optional_set ftb_button_optional_set_hover_background">
						<div class="label"><?php echo __('Pozadí po najetí myši', 'cms'); ?></div>
						<?php
						//background hover
						cms_generate_field_background($name . '[hover_color]', $id . '_hover_color', $hoverColor, ['content' => ['gradient' => 1], 'hide_transparency' => true]);
						?>
					</div>

					<div class="ftb_button_set ftb_button_item_hover_font_color">
						<div class="label"><?php echo __('Barva písma po najetí myši', 'cms'); ?></div>
						<?php
						echo cms_generate_field_color($name . '[hover_font_color]', $id . '_hover_font_color', ($content['hover_font_color'] ?? ''));
						?>
					</div>

					<div
						class="ftb_button_set ftb_button_item_hover_border_color ftb_button_optional_set ftb_button_optional_set_border">
						<div class="label"><?php echo __('Barva ohraničení po najetí myši', 'cms'); ?></div>
						<?php echo cms_generate_field_color($name . '[border_hover-color]', $id . '_border_hover-color', $borderHoverColor); ?>
					</div>

					<div class="cms_clear"></div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function mw_add_button_item_ajax()
{
	$button = [
		'style' => '1',
		'font-color' => '#ffffff',
		'background_color' => ['color1' => '#ef2546', 'rgba1' => 'rgba(239, 37, 70, 1)', 'transparency' => '1', 'color2' => '', 'rgba2' => '', 'transparency' => '2'],
		'hover_effect' => 'darker',
	];
	ftb_button_item($button, $_POST['bkey'], $_POST['name'] . '[' . $_POST['bkey'] . ']', $_POST['id'] . '_' . $_POST['bkey'], true);
	die();
}

add_action('wp_ajax_mw_add_button_item', 'mw_add_button_item_ajax');

function mw_duplicate_button_item_ajax()
{
	$button = $_POST['setting']['buttons'][$_POST['duplicate']];
	ftb_button_item($button, $_POST['bkey'], $_POST['name'] . '[' . $_POST['bkey'] . ']', $_POST['id'] . '_' . $_POST['bkey'], true);
	die();
}

add_action('wp_ajax_mw_duplicate_button_item', 'mw_duplicate_button_item_ajax');

function mw_add_button_from_custom_ajax()
{
	global $vePage;

	$buttons = get_option('ve_buttons');
	end($buttons['buttons']);
	$key = key($buttons['buttons']);
	$newkey = intval($key) + 1;
	foreach ($_POST as $p) {
		if (isset($p[$_POST['name']])) {
			$buttons['buttons'][$newkey] = $p[$_POST['name']]['custom_setting'];
		}
	}
	update_option('ve_buttons', $buttons);

	// print new button
	$return['button_item'] = print_button_selector_item($newkey, $buttons['buttons'][$newkey], ['style' => ''], 've_style[' . $_POST['name'] . ']');
	$styles = Button::getButtonStyles($buttons['buttons'][$newkey], '.ve_content_button_style_' . $newkey);
	$button_styles = $vePage->builder->css->createCssContainer();
	foreach ($styles as $cbs_key => $cbs_val) {
		$button_styles->addStyles($cbs_val, $cbs_key);
	}
	$return['button_css'] = $vePage->builder->css->printCss($button_styles, '', true);

	wp_send_json($return);
	die();
}

add_action('wp_ajax_mw_add_button_from_custom', 'mw_add_button_from_custom_ajax');

function mwisset(&$var, $id, $after = '')
{
	return isset($var[$id]) && $var[$id] != '' ? $var[$id] . $after : '';
}

function mwisset_array(&$var)
{
	return isset($var) && !empty($var) ? $var : [];
}
