<?php

function MWInstallator()
{
	return mwInstallator::instance();
}

class mwInstallator
{

	/** @var mwInstallator Single instance holder. */
	protected static $_instance = null;

	private $installSteps = [];

	private $templates = [];

	function __construct()
	{
		add_action('wp_ajax_mwOpenInstallator', [$this, 'ajaxOpenInstallator']);
	}

	function ajaxOpenInstallator()
	{
		$steps = $this->installSteps[$_POST['install']]['steps'];

		$content = '<div class="mw_installator_container">';

		$content .= '<div class="mw_installator_head">';

		$content .= '<div class="mw_installator_head_title">';
		$content .= $this->installSteps[$_POST['install']]['title'] ?? '';
		$content .= '</div>';

		if (!isset($this->installSteps[$_POST['install']]['hide_steps'])) {
			$content .= $this->printProgress(count($steps));
		}

		$content .= '<a href="#" class="mw_close_icon mw_installator_close">' . mw_icon('icon-x') . '</a>';

		$content .= '</div>';

		$i = 1;
		foreach ($steps as $step) {
			$content .= '<div class="mw_installator_step mw_installator_step_' . $i . '">';
			$content .= '<h2>' . $step['title'] . '</h2>';
			$content .= '<div class="mw_installator_step_container">';
			$content .= $this->printStep($step, $_POST['install']);
			$content .= '</div>';
			$content .= '</div>';

			$i++;
		}

		if (isset($_POST['objectid'])) {
			$content .= '<input type="hidden" name="object_id" value="' . $_POST['objectid'] . '">';
			$content .= '<input type="hidden" name="redirect_to_front" value="' . ($_POST['front_redirect'] ?? 0) . '">';
			$content .= wp_nonce_field('mw_save_setting_nonce', 'mw_save_setting_nonce', true, false);
		}

		$content .= mwAdminComponents::iconLink([
			'icon' => 'arrow-left',
			'text' => __('Krok zpět', 'cms_ve'),
		], 'mw_installator_go_back');

		$content .= '</div>';

		echo $content;
		die();
	}

	function printStep($step, $toInstall)
	{
		$content = '';
		if ($step['type'] == 'select_type') {
			$content .= '<div class="mw_install_select_type">';
			if (isset($step['templates'])) {
				foreach ($this->templates[$step['templates']] as $template_name => $template_path) {
					$install = require_once($template_path . 'install.php');
					$install['value'] = $template_name;
					$content .= mwAdminComponents::installatorTypeSelectItem($install, $step['id']);
				}
			} elseif (isset($step['content'])) {
				foreach ($step['content'] as $type) {
					$content .= mwAdminComponents::installatorTypeSelectItem($type, $step['id']);
				}
			}

			$content .= '</div>';
			if ($step['custom_option']) {
				$content .= '<div class="mw_install_select_type_empty">';
				$content .= __('nebo...', 'cms_ve') . ' <a href="#" class="mw_installator_go_next mw_installator_select_empty_input">' . $step['custom_option_text'] . '</a>';
				$content .= mwAdminComponents::input([
					'type' => __('radio', 'cms'),
					'name' => $step['id'],
				], '');
				$content .= '</div>';
			}
		} elseif ($step['type'] == 'name' || $step['type'] == 'post_title') {
			$content .= '<div class="mw_installator_form mw_admin_setting_container">';

			$content .= '<div class="set_form_row">';
			$content .= mwAdminComponents::input([
				'name' => $step['type'],
				'placeholder' => $step['content']['input_placeholder'],
				'desc' => $step['content']['desc'],
			], '', 'required');
			$content .= '</div>';

			$content .= '<div class="mw_installator_button_area">';
			$content .= mwAdminComponents::button([
				'button_text' => $step['content']['button_text'],
				'style' => 'big',
			], 'mw_installator_install');
			$content .= '</div>';

			$content .= '<div class="mw_installator_error_area"></div>';

			$content .= '</div>';
		} elseif ($step['type'] == 'objectForm') {
			$content .= $this->objectForm();
		} elseif ($step['type'] == 'itemSelect') {
			$content .= '<div class="mw_installator_form mw_admin_setting_container">';

			$content .= '<div class="set_form_row">';
			$content .= MwFields::itemSelect([
				'only_published' => true,
				'object_id' => $step['object_id'],
				'whisperer' => true,
			], '', 'itemId');
			$content .= '</div>';

			$content .= '<div class="mw_installator_button_area">';
			$content .= mwAdminComponents::button([
				'button_text' => $step['button_text'],
				'style' => 'big',
			], 'mw_installator_go_next');
			$content .= '</div>';

			$content .= '<div class="mw_installator_error_area"></div>';

			$content .= '</div>';
		} elseif ($step['type'] == 'productSelect') {
			$content .= '<div class="mw_installator_form mw_admin_setting_container">';

			$content .= '<div class="set_form_row">';
			$content .= MwShopFields::productSelect([
				'hide_variants' => true,
				'whisperer' => true,
			], 0, 'itemId');
			$content .= '</div>';

			$content .= '<div class="mw_installator_button_area">';
			$content .= mwAdminComponents::button([
				'button_text' => $step['button_text'],
				'style' => 'big',
			], 'mw_installator_go_next');
			$content .= '</div>';

			$content .= '<div class="mw_installator_error_area"></div>';

			$content .= '</div>';
		} elseif ($step['type'] == 'select_ab_page') {
			$content .= $this->abTestPage();
		} elseif ($step['type'] == 'select_template') {
			$import = isset($step['import']) && $step['import'] ? true : false;
			$but_class = isset($step['click']) && $step['click'] === 'next' ? 'mw_installator_go_next' : 'mw_installator_select_template_install';
			$content .= $this->templateSelector($_POST['objectid'], [], $import, $but_class);
		}

		return $content;
	}

	function objectForm()
	{
		$content = '<div class="mw_installator_form mw_admin_setting_container">';

		$content .= MwFields::itemSet([
			'object_id' => $_POST['objectid'],
			'fields' => [
				'post_title' => [
					'label' => __('Název stránky', 'cms'),
				],
				'post_parent' => [
					'label' => __('Nadřazená stránka', 'cms'),
				],
			],
		]);

		$content .= '<div class="mw_installator_button_area">';
		$content .= mwAdminComponents::button([
			'button_text' => __('Vytvořit stránku', 'cms_ve'),
			'style' => 'big',
		], 'mw_installator_install');
		$content .= '</div>';

		$content .= '<div class="mw_installator_error_area"></div>';

		$content .= '</div>';

		return $content;
	}

	function abTestPage()
	{
		$content = '<div class="mw_installator_form mw_admin_setting_container mw_ab_select_container">';

		$content .= '<div class="set_form_row">';
			$content .= mwAdminComponents::select([
				'name' => 'ab_page_type',
				'tag_id' => '',
				'options' => [
					[
						'name' => __('Vytvořit kopii aktuální stránky', 'cms_ve'),
						'value' => 'copy',
					],
					[
						'name' => __('Vytvořit novou stránku', 'cms_ve'),
						'value' => 'new',
					],
					[
						'name' => __('Použít existující stránku', 'cms_ve'),
						'value' => 'existing',
					],
				],
			], 'copy', 'mw_select_ab_page_type');
		$content .= '</div>';

		$content .= '<div class="set_form_row mw_select_ab_page_container">';
			$content .= mwAdminComponents::inputLabel(['label' => __('Vyberte stránku, kterou chcete použít', 'cms_ve')]);
			$content .= mwAdminComponents::selectPage([
				'name' => 'ab_page_id',
				'style' => 'big',
				'empty' => ' - ',
			], '', 'mw_select_ab_page_id');

		$content .= '</div>';

		$content .= '<div class="mw_installator_button_area">';
			$content .= mwAdminComponents::button([
				'button_text' => __('Vytvořit A/B test', 'cms_ve'),
				'style' => 'big',
			], 'mw_installator_install mw_ab_test_create_but');

			$content .= mwAdminComponents::button([
				'button_text' => __('Pokračovat', 'cms_ve'),
				'style' => 'big',
			], 'mw_installator_go_next mw_ab_test_continue_but');
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	function templateSelector($post_type = 'page', $template = [], $import = false, $but_class = 'mw_installator_select_template_install')
	{
		$content = '<div class="mw_installator_template_selector">';
		/*
		if ($post_id) {
			$template = get_post_meta($post_id, 've_page_template', true);
		}*/

		if (!isset($template) || !$template) {
			$template = ['type' => $post_type, 'directory' => 'page/1'];
		}
		$temp = explode('/', $template['directory']);

		$allow_templates = [];
		foreach (MW()->p_templates as $key => $tmpl) {
			if (!isset($tmpl['cat']) && ((isset($tmpl['type']) && $tmpl['type'] == $post_type) || ($post_type == 'page' && !isset($tmpl['type'])))) {
				if (!mw_is_lite_editor() || (isset($tmpl['lite']) && $tmpl['lite'])) { // lite version
					$allow_templates[$key] = $tmpl;
				}
			}
		}

		if (count($allow_templates)) {
			$current = isset($allow_templates[$temp[0]]) ? $temp[0] : MW()->p_templates[$temp[0]]['cat'] ?? '';

			// tabs
			if (count($allow_templates) > 1) {
				$tabs = [];
				foreach ($allow_templates as $key => $tmpl) {
					$tabs[] = [
						'id' => $key,
						'icon' => $tmpl['icon'] ?? 'file',
						'name' => $tmpl['name'],
					];
				}
				if ($import) {
					$tabs[] = [
						'id' => 'import',
						'icon' => 'download',
						'name' => __('Importovat', 'cms_ve'),
					];
				}
				$content .= mwAdminComponents::tabs([
					'tabs' => $tabs,
					'group' => 'mw_templates_tab',
				], $current, 'mw_category_tabs');
			}
			$content .= '<input type="hidden" name="template_type" value="' . $post_type . '">';

			$content .= '<div class="mw_templates_container">';

			foreach ($allow_templates as $key => $tmpl) {
				$dir = MW()->get_template_dir($key) . $tmpl['path'];
				$url = MW()->get_template_url($key) . $tmpl['path'];

				$content .= '<div id="mw_templates_tab_' . $key . '" class="mw_tab mw_templates_tab_container ' . ($current == $key || count($allow_templates) == 1 ? 'active' : '') . '">';

				if (isset($tmpl['list']) && count($tmpl['list'])) {
					foreach ($tmpl['list'] as $tmpl_category) {
						if ($tmpl_category['name']) {
							$content .= '<h3 class="mw_template_category_title">' . $tmpl_category['name'] . '</h3>';
						}

						$content .= '<div class="mw_template_pages_container">';

						$tmpl_category_list = mw_is_lite_editor() && isset($tmpl_category['lite_list']) ? $tmpl_category['lite_list'] : $tmpl_category['list'];

						foreach ($tmpl_category_list as $tmpl_template) {
							if (is_array($tmpl_template)) {
								$temp_dir = MW()->get_template_dir($tmpl_template['cat']) . MW()->p_templates[$tmpl_template['cat']]['path'] . $tmpl_template['folder'] . '/';
								$temp_url = MW()->get_template_url($tmpl_template['cat']) . MW()->p_templates[$tmpl_template['cat']]['path'] . $tmpl_template['folder'] . '/';

								$directory = $tmpl_template['cat'] . '/' . $tmpl_template['folder'] . '/';
							} else {
								$temp_dir = $dir . $tmpl_template . '/';
								$temp_url = $url . $tmpl_template . '/';

								$directory = $key . '/' . $tmpl_template . '/';
							}

							$template_data = implode('', file($temp_dir . 'template.php'));
							preg_match('| Template Title:(.*)|i', $template_data, $name);
							//preg_match('| Template Description:(.*)|i', $template_data, $description);

							$language = get_locale();
							$thumb_name = 'thumb.jpg';
							if ($language == 'en_US') {
								if (file_exists($temp_dir . 'thumb_en.jpg')) {
									$thumb_name = 'thumb_en.jpg';
								}
							}
							$lang_domain = 'cms_ve';
							if ($key == 'funnel') {
								$lang_domain = 'mw_funnels';
							}
							if ($key == 'campaign') {
								$lang_domain = 'cms_mioweb';
							} elseif ($key == 'member') {
								$lang_domain = 'cms_member';
							}

							$content .= mwAdminComponents::templateItem([
								'id' => $key . $tmpl_template,
								'title' => __(trim($name[1]), $lang_domain),
								'thumb_url' => $temp_url . $thumb_name,
								'selected' => $directory == $template['directory'] ? true : false,
								'value' => $directory,
								'button_class' => $but_class,
							]);
						}
						$content .= '</div>';
					}
				} else {
					$content .= '<div class="mw_template_empty_info">' . __('Tato kategorie neobsahuje žádné šablony.', 'cms_ve') . '</div>';
				}
				$content .= '</div>';
			}
			if ($import) {
				$content .= '<div id="mw_templates_tab_import" class="mw_tab mw_templates_tab_container">';
					$content .= '<h2 class="ve_template_category_title">' . __('Naimportovat šablonu ze souboru:', 'cms_ve') . '</h2>';
					$content .= '<div class="ve_template_import_container mw_admin_setting_container">';
						$content .= mwAdminComponents::messageBox(__('Nahrajte zip soubor obsahující exportovanou stránku z Miowebu.', 'cms_ve'), [
							'type' => 'info_gray',
						]);
						$content .= '<div class="set_form_row">';
						$content .= tus()->initInput('import_template_upload', null, ['application/zip', 'application/x-zip-compressed'], '.mw_installator_import_but', true, false);
						$content .= '</div>';
						$content .= '<div class="set_form_row">';
						if ($but_class === 'mw_installator_go_next') {
					$content .= mwAdminComponents::button([
				'button_text' => __('Pokračovat', 'mwshop'),
					], 'mw_installator_go_next mw_installator_import_but');
						}
						$content .= '</div>';
					$content .= '</div>';
				$content .= '</div>';
			}
			$content .= '</div>';
		}
		$content .= '</div>';

		return $content;
	}

	public static function printWebTemplateSelector()
	{
		echo mwAdminComponents::title([
			'text' => __('Změnit šablonu webu', 'cms_ve') . mwSetting()->getHelpLink('mw_web_template'),
		], 'h2');
		echo MwWebInstall()->write_web_selector(1);
	}

	public static function printImportExport()
	{
		$tabs = [
			[
				'id' => 'import',
				'name' => __('Importovat web', 'cms_ve'),
			],
			[
				'id' => 'export',
				'name' => __('Exportovat web', 'cms_ve'),
			],
		];

		echo '<div class="mw_setting_tabs_container mw_onedit_action" data-type="tabs">';
		echo mwAdminComponents::tabs([
			'tabs' => $tabs,
			'group' => 'mw_tab_import_group',
		], '', 'mw_setting_tabs');
		echo '</div>';

		echo '<div id="mw_tab_import_group_import" class="mw_tab mw_tab_import_group_container active">';

		$import_setting = [
			[
				'id' => 'info',
				'content' => __('Nahrajte zip soubor obsahující export webu z Miowebu. Váš hosting může mít omezení na velikost nahrávaných souborů. Pokud je importovaný soubor větší než je povolená velikost, požádejte svůj hosting o zvýšení limitu velikosti nahrávaných souborů.', 'cms_ve'),
				'type' => 'info',
				'color' => 'gray',
			],
			[
				'id' => 'import_file',
				'title' => __('Nahrajte zip soubor, který chcete naimportovat:', 'cms_ve'),
				'type' => 'file',
			],
		];

		write_meta($import_setting, [], 'import', 'import');

		echo '<div class="set_form_row">';
		echo '</div>';

		echo '</div>';

		echo '<div id="mw_tab_import_group_export" class="mw_tab mw_tab_import_group_container">';
		echo '<form action="" method="post">';

		$export_setting = [
			[
				'id' => 'info',
				'content' => __('Z Miowebu lze exportovat pouze nastavení vzhledu webu a stránky (včetně jejich nastavení). Nelze exportovat kampaně, členské sekce ani jiné nastavení.', 'cms_ve'),
				'type' => 'info',
				'color' => 'gray',
			],
			[
				'id' => 'web_look',
				'title' => __('Nastavení vzhledu webu', 'cms_ve'),
				'label' => __('Exportovat nastavení vzhledu webu', 'cms_ve'),
				'type' => 'switch',
				'content' => 1,
			],
			[
				'id' => 'all_pages',
				'title' => __('Stránky webu', 'cms_ve'),
				'label' => __('Exportovat všechny stránky', 'cms_ve'),
				'type' => 'switch',
				'content' => 1,
				'show' => 'allpages',
				'show_type' => 'hide',
			],
			[
				'id' => 'pages',
				'title' => __('Exportovat vybrané stránky', 'cms_ve'),
				'type' => 'pagecheck',
				'show_group' => 'allpages',
				'show_val' => '0',
			],
			[
				'id' => 'export_blog',
				'title' => __('Exportovat blog', 'cms_ve'),
				'type' => 'multiple_checkbox',
				'options' => [
					[
						'name' => __('Exportovat vzhled a nastavení blogu', 'cms_ve'),
						'value' => 'setting',
					],
				],
			],
		];

		write_meta($export_setting, [], 'export', 'export');

		echo '<div class="set_form_row">';
		echo '<input type="submit" class="mw_button" name="export_web_from_mw" value="' . __('Exportovat', 'cms_ve') . '"/>';
		echo '</div>';
		echo '</form>';
		echo '</div>';
	}

	function printProgress($num)
	{
		return '<div class="mw_installator_progress">'
			. __('Krok', 'cms_ve') . ' <span>1</span>/' . $num
			. '</div>';
	}

	function addInstallSteps($toInstall, $steps)
	{
		$this->installSteps[$toInstall] = $steps;
	}

	function addTemplates($toInstall, $templates)
	{
		$this->templates[$toInstall] = isset($this->templates[$toInstall]) ? array_merge($this->templates[$toInstall], $templates) : $templates;
	}
	function getTemplate($type, $template)
	{
		return $this->templates[$type] && isset($this->templates[$type][$template]) ? $this->templates[$type][$template] : false;
	}

	/** @return mwInstallator Returns singleton instance of Installator. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}
}
