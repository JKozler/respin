<?php

class cmsWEditor
{

	public $window_setting;

	public $edit_mode;

	public $popup;

	public $popups_onpage = [];

	public $popup_script;

	function __construct()
	{
		$this->edit_mode = current_user_can('edit_pages') ? true : false;

		if ($this->edit_mode) {
			add_action('wp_enqueue_scripts', [$this, 'load_admin_scripts']);
			add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);

			add_action('wp_ajax_ve_open_weditor_setting', [$this, 'open_weditor_setting']);
			add_action('wp_ajax_ve_save_weditor_setting', [$this, 'save_weditor_setting']);

			add_action('wp_ajax_ve_change_weditor_title', [$this, 'change_weditor_title']);
		}
	}

	function load_admin_scripts()
	{
		wp_enqueue_script('ve_weditor_admin_script');
	}

	function change_weditor_title()
	{
		if (isset($_POST['postid']) && isset($_POST['title']) && $_POST['title'] && $_POST['postid']) {
			wp_update_post(
				[
					'ID' => $_POST['postid'],
					'post_title' => $_POST['title'],
				]
			);
		}
		die();
	}

	function create_content($id, $pre, $option = '', $key = '', $post_id = '', $editable = false)
	{
		global $vePage;
		$content = '';
		if (get_post($id)) {
			$layer = $vePage->display->get_layer($id, $pre);
			$content = '';
			if ($editable && $this->edit_mode) {
				$content .= '<div class="edit_weditor_content_container">';
				$content .= '<a class="mw_edit_but ve_open_weditor_setting" data-postid="' . $post_id . '" data-type="' . $pre . '" data-option="' . $option . '" data-key="' . $key . '" title="' . __('Editovat obsah', 'cms_ve') . '" href="#">' . mw_icon('icon-edit-2') . '</a>';
			}
			$content .= $vePage->display->write_content($layer, false, $pre . '_' . $id, false);
			if ($editable && $this->edit_mode) {
				$content .= '</div>';
			}
		}

		return $content;
	}

	function weditor_content($id, $args = [])
	{
		global $vePage;

		$defaults = [
			'key' => '',
			'option' => '',
			'post_id' => '',
			'type' => 'weditor',
		];

		$r = wp_parse_args($args, $defaults);

		$content = '';
		if ($this->edit_mode) {
			$content .= '<div class="weditor_content_container">';
		}
		if ($id) {
			$content .= $this->create_content($id, $r['type'], $r['option'], $r['key'], $r['post_id'], true);
		} elseif ($this->edit_mode) {
			$content .= '<div class="row_edit_container admin_feature">';

			$content .= '<div class="row_add_container">';
			$content .= '<a class="ve_add_content_button ve_open_weditor_setting" data-postid="' . $r['post_id'] . '" data-type="' . $r['type'] . '" data-option="' . $r['option'] . '" data-key="' . $r['key'] . '" href="#" title="' . __('Vyberte nebo vytvořte obsah který chcete zobrazit', 'cms_ve') . '">' . __('Přidat obsah', 'cms_ve') . '</a>';
			$content .= '</div>';
			$content .= '</div>';
		}
		if ($this->edit_mode) {
			$content .= '</div>';
		}

		return $content;
	}
	function open_weditor_setting()
	{
		$setting = [
			[
				'id' => 'id',
				'title' => __('Obsah', 'cms_ve'),
				'type' => 'weditor',
				'setting' => [
					'post_type' => $_POST['type'] ?? 'weditor',
					'texts' => [
						'empty' => __(' - Bez obsahu - ', 'cms_ve'),
						'edit' => __('Upravit vybraný obsah', 'cms_ve'),
						'duplicate' => __('Duplikovat vybraný obsah', 'cms_ve'),
						'create' => __('Vytvořit nový obsah', 'cms_ve'),
						'delete' => __('Smazat vybraný obsah', 'cms_ve'),
					],
				],
			],
		];

		$option = get_option($_POST['option']);

		?>
		<div class="mw_admin_setting_container">
			<div class="mw_setting_padding_content">
		<?php echo write_meta($setting, ['id' => $option[$_POST['key']]], 'weditor', 'weditor', ''); ?>
				<input type="hidden" name="post_id" value="<?php echo $_POST['postid']; ?>"/>
				<input type="hidden" name="option_key" value="<?php echo $_POST['key']; ?>"/>
				<input type="hidden" name="option_name" value="<?php echo $_POST['option']; ?>"/>
				<input type="hidden" name="post_type" value="<?php echo $_POST['type']; ?>"/>
			</div>
		</div>
		<?php
		die();
	}
	function save_weditor_setting()
	{
		global $vePage;

		$id = $_POST['weditor']['id'];

		if ($_POST['post_id']) {
		} else {
			$option = get_option($_POST['option_name']);
			$option[$_POST['option_key']] = $id;
			update_option($_POST['option_name'], $option);
		}

		$wfonts = get_post_meta($id, 've_google_fonts', true);
		if (count($wfonts) > 0) {
			$fonts = [];
			foreach ($wfonts as $key => $val) {
				$fonts[] = str_replace(' ', '+', $key) . ':' . implode(',', array_keys($val));
			}

			$return['font'] = implode('|', $fonts);
		} else {
			$return['font'] = '';
		}

		//$return['content']=$vePage->weditor->create_content($id,$_POST['post_type'],$_POST['option_name'],$_POST['option_key'],$_POST['post_id'],true);

		$args = [
			'key' => $_POST['option_key'],
			'option' => $_POST['option_name'],
			'type' => $_POST['post_type'],
			'post_id' => $_POST['post_id'],
		];
		$return['content'] = $vePage->display->weditor->weditor_content($id, $args);

		wp_send_json($return);
		die();
	}

}

/* Field type weditor
************************************************************************** */

function cms_generate_field_weditor($name, $id, $value, $pages, $type, $texts, $install = 'weditor')
{
	echo '<div class="ve_windowselect_container ' . ($value && get_post($value) ? 'selected' : '') . '" data-install="' . $install . '" data-type="' . $type . '" data-url="' . home_url() . '/?window_editor=' . $type . '">';

	echo '<div class="mw_flex_field">';

	$options = [];
	$options[] = [
		'value' => '',
		'name' => $texts['empty'] ?? '-',
		'attrs' => 'data-title=""',
	];

	foreach ($pages as $page) {
		$options[] = [
			'value' => $page->ID,
			'name' => $page->post_title ?: __('(bez názvu)', 'cms_ve'),
			'attrs' => 'data-title="' . $page->post_title . '"',
		];
	}

	echo mwAdminComponents::select([
		'options' => $options,
		'name' => $name,
		'tag_id' => $id,
		'empty' => $texts['empty'],
	], $value, 've_windowselect_selector');

	echo mwAdminComponents::iconLink([
		'icon' => 'edit-2',
		'title' => $texts['edit'],
	], 'mw_icon_button open_window_editor edit_window_editor');

	echo mwAdminComponents::iconLink([
		'icon' => 'copy',
		'title' => $texts['duplicate'],
	], 'mw_icon_button create_copy_window_editor');

	echo mwAdminComponents::iconLink([
		'icon' => 'trash-2',
		'attrs' => 'data-text="' . __('Opravdu chcete tuto položku smazat?', 'cms_ve') . '"',
	], 'mw_icon_button delete_window_editor');

	echo '</div>';

	echo mwAdminComponents::button([
		'icon' => 'plus',
		'button_text' => $texts['create'],
		'style' => 'secondary',
	], 'add_new_object_item');

	echo '</div>';
}

function field_type_weditor($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	$pages = get_posts(['post_type' => $field['setting']['post_type'], 'posts_per_page' => '1000']);
	cms_generate_field_weditor($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $pages, $field['setting']['post_type'], $field['setting']['texts'], $field['setting']['install'] ?? 'weditor');
}


?>
