<?php
class MwFields
{

	public static function print($fieldType, $fieldSetting, $meta, $fieldName, $fieldId = ''): string
	{
		$val = $meta != '' ? $meta : ($fieldSetting['content'] ?? null);

		$name = $fieldName . '[' . $fieldSetting['id'] . ']';
		$id = $fieldId . '_' . $fieldSetting['id'];

		return self::$fieldType($fieldSetting, $val, $name, $id);
	}

	public static function multiElement($args, $val, $class = ''): string
	{
		$default = [
			'open' => 'inline',
			'tagname' => '',
			'tagid' => '',
			'sortable' => true,
			'keep_id' => false,
			'setting' => [],
			'texts' => [
				'add' => __('Přidat', 'cms'),
				'empty' => __('Nový', 'cms'),
			],
			'title_function' => null,
			'content_function' => null,
			'valid_function' => null,
		];
		$args = array_merge($default, $args);

		if (isset($args['style'])) {
			$class .= 've_items_container_style_' . $args['style'];
		}

		$content = '<div class="ve_multielement_container ve_items_container_open_' . $args['open'] . ' ve_sortable_items ' . $class . '" data-open="' . $args['open'] . '">';

		$i = 0;
		if (isset($val) && is_array($val)) {
			foreach ($val as $key => $item) {
				$id = $args['keep_id'] ? $key : $i;
				$content .= self::multiElementItem($args, $item, $id);

				if ($args['keep_id']) {
					if ($i <= $id) {
						$i = $id + 1;
					}
				} else {
					$i++;
				}
			}
		}

		$content .= '</div>';

		$content .= mwAdminComponents::button([
			'button_text' => $args['texts']['add'],
			'icon' => 'plus',
			'style' => 'secondary',
			'attrs' => 'data-id="' . $i . '" data-set="' . base64_encode(serialize($args)) . '"',
		], $args['button_class'] ?? 've_add_multielement');

		return $content;
	}

	public static function multiElementItem($args, $item, $i, $added = false): string
	{
		if (isset($args['valid_function']) && is_callable($args['valid_function']) && !$args['valid_function']($args, $item)) {
			 return '';
		}

		$title = '';

		if (isset($args['title_function'])) {
			$fnc = $args['title_function'];
			$title = $fnc($args, $item, $i);
		} else {
			$title_id = 0;

			if (is_array($item)) {
				$title = array_values($item);
				$title = $title[0] ?? '';
			}
			if (isset($item['icon'])) {
				$icon = isset($item['icon']['code']) ? stripslashes($item['icon']['code']) : '';
				$title = '<span class="ve_item_head_icon">' . $icon . '</span>';
			}

			if (isset($item['title'])) {
				$title = stripslashes($item['title']);
			} elseif (isset($item['name'])) {
				$title = $item['name'] ?? '';
			}
			if (!$title) {
				$title = $item['text'] ?? '';
			}

			if (is_array($title)) {
				if (isset($title['imageid'])) {
					$title_id = $title['imageid'];
				} else {
					$title = '';
				}
			}
			if (isset($item['product_id']) && $item['product_id']) {
				$title_id = $item['product_id'];
			} elseif (isset($item['slider_content']) && $item['slider_content']) {
				$title_id = $item['slider_content'];
			}
			if ($title_id) {
				$post = get_post($title_id);
				if ($post) {
					$title = $post->post_title;
				}
			}
			if (!$title) {
				$title = $args['texts']['empty'] ?? '';
			}
		}

		$content = '<div class="ve_multielement-' . $i . ' ve_item_container ' . ($args['sortable'] ? 've_sortable_item' : '') . ' ' . ($added ? 'added' : '') . '">';
		$content .= '<div class="ve_item_head">';
		if ($args['sortable']) {
			$content .= mwAdminComponents::icon(['icon' => 'move'], 've_sortable_handler');
		}
		$content .= '<div class="ve_item_head_title">' . $title . '</div>';
		$content .= '<div class="ve_item_head_edit">';
		$content .= mwAdminComponents::iconLink([
			'icon' => 'edit-2',
			'title' => __('Editovat', 'cms'),
		], 've_edit_setting');
		$content .= mwAdminComponents::iconLink([
			'icon' => 'trash-2',
			'title' => __('Smazat', 'cms'),
		], 've_delete_setting');
		$content .= '</div>';
		$content .= '</div>';
		$content .= '<div class="ve_item_body">';

		if ($args['open'] == 'inline') {
			$content .= '<div class="ve_item_body_head">';
			$content .= mwAdminComponents::iconLink([
				'icon' => 'arrow-left',
				'text' => __('Zpět', 'cms'),
			], 've_item_close mw_setting_back_link');
			$content .= '</div>';
		}
		$content .= '<div class="ve_item_body_setting ' . ($args['open'] == 'inline' ? 'mw_scroll' : '') . '">';

		if (isset($args['content_function'])) {
			$fnc = $args['content_function'];
			$content .= $fnc($args, $item, $i);
		} else {
			ob_start();
			write_meta($args['setting'], $item, $args['tagname'] . '[' . $i . ']', $args['tagid'] . '_' . $i, '', 'setting', true);
			$content .= ob_get_contents();
			ob_end_clean();
		}


		$content .= '</div>';
		if ($args['open'] == 'inline') {
			$content .= '<div class="mw_editor_panel_bottom">';
			$content .= '<a href="#" class="ve_item_close mw_storno_button">' . __('HOTOVO', 'cms') . '</a>';
			$content .= '</div>';
		}
		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	public static function itemSet($fieldSetting, $val = '', $fieldName = '', $fieldId = '', $itemId = 0)
	{
		$object = mwSetting()->getObject($fieldSetting['object_id'] ?? 'page');
		$item = $itemId ? $object->service()->getItem($itemId) : null;

		$content = '';

		foreach ($fieldSetting['fields'] as $fKey => $field) {
			$content .= '<div class="set_form_row">';
			$content .= mwAdminComponents::inputLabel([
				'label' => $field['label'],
				'tooltip' => $field['tooltip'] ?? '',
			]);

			// title
			if ($fKey == 'post_title') {
				$content .= self::itemTitle($item, $field);
			} elseif ($fKey == 'post_excerpt') {
				// description
				$content .= mwAdminComponents::textarea([
					'name' => 'post_excerpt',
				], ($item ? $item->getExcerpt() : ''));
			} elseif ($fKey == 'post_content') {
				if (isset($field['editor']) && $field['editor']) {
					ob_start();
					wp_editor(($item ? $item->getContent() : ''), 'mw_post_content', [
						'textarea_name' => 'post_content',
						'media_buttons' => true,
						'quicktags' => false,
						'editor_class' => '',
						'tinymce' => [
							'plugins' => 'lists, paste, wordpress, link, wpdialogs, charmap',
							'toolbar1' => 'formatselect | bold italic strikethrough underline | alignleft aligncenter alignright | link unlink | bullist numlist | superscript subscript | outdent indent charmap',
							'toolbar2' => '',
							'init_instance_callback' => "function (editor) {
								editor.on('change', function (editor) {
						            tinymce.triggerSave();
						        });
							}",
						],
					]);
					$content .= ob_get_clean();
				} else {
					$content .= mwAdminComponents::textarea([
						'name' => 'post_content',
					], ($item ? $item->getContent() : ''));
				}
			} elseif ($fKey == 'post_parent') {
				$content .= mwAdminComponents::selectPage([
					'name' => 'post_parent',
				], ($item ? $item->getParentId() : 0));
			} elseif ($fKey == 'post_author') {
				// author
				$content .= MwFields::authorSelect([], ($item ? $item->getAuthorId() : 0), 'post_author');
			} elseif ($fKey == 'menu_order') {
				// order
				$content .= mwAdminComponents::inputNumber([
					'name' => 'menu_order',
				], ($item ? $item->getOrder() : 0));
			} elseif ($fKey == 'post_format') {
				// post format
				$formats = get_theme_support('post-formats');

				$options = [];
				$options[] = [
					'name' => __('Standardní', 'cms'),
					'value' => '',
				];
				foreach ($formats[0] as $format) {
					$options[] = [
						'name' => get_post_format_string($format),
						'value' => $format,
					];
				}
				$content .= mwAdminComponents::select([
					'name' => 'post_format',
					'options' => $options,
				], $item ? $item->getPostFormat() : '');
			} elseif ($fKey == 'stick_post') {
				// stick post
				$content .= mwAdminComponents::switch([
					'name' => 'stick_post',
					'switch_label' => __('Připnout článek na začátek výpisu článků', 'cms'),
				], $item ? ($item->isSticky() ? 1 : 0) : 0);
			} elseif ($fKey == 'term_title') {
				// term title
				$field['title_name'] = 'term[name]';
				$field['slug_name'] = 'term[slug]';
				$content .= self::itemTitle($item, $field);
			} elseif ($fKey == 'term_parent' && $object) {
				// term parent
				$content .= MwFields::termSelect([
					'term_id' => $object->getId(),
					'exclude' => $item ? $item->getId() : [],
					'empty_text' => '-',
				], ($item ? $item->getParentId() : -1), 'term[parent]');
			} elseif ($fKey == 'term_description') {
				// term description
				$content .= mwAdminComponents::textarea([
					'name' => 'term[description]',
				], ($item ? $item->getDescription() : ''));
			} elseif ($fKey == 'comment_content') {
				// comment title
				$content .= mwAdminComponents::textarea([
					'name' => 'comment[comment_content]',
					'rows' => 12,
				], ($item ? $item->getContent() : ''));
			} elseif ($fKey == 'comment_author') {
				// comment author
				$content .= mwAdminComponents::input([
					'name' => 'comment[comment_author]',
				], ($item ? $item->getAuthorName(true) : ''));
			} elseif ($fKey == 'comment_email') {
				// comment email
				$content .= mwAdminComponents::input([
					'name' => 'comment[comment_author_email]',
				], ($item ? $item->getAuthorEmail() : ''));
			} elseif ($fKey == 'comment_url') {
				// comment email
				$content .= mwAdminComponents::input([
					'name' => 'comment[comment_author_url]',
				], ($item ? $item->getAuthorWeb() : ''));
			}
			$content .= '</div>';
		}

		return $content;
	}

	public static function itemTitle($item = null, $field = [])
	{
		$content = '<div class="mw_post_title_field_container">';
		$afterTitle = isset($_GET['copy']) || isset($_POST['copy']) ? '_kopie' : '';
		$content .= mwAdminComponents::input([
			'type' => 'text',
			'name' => $field['title_name'] ?? 'post_title',
		], ($item ? $item->getName() . $afterTitle : ''), 'mw_post_title_field required');

		$showSlug = $field['slug'] ?? true;
		$slugType = $field['slug_type'] ?? 'visible';

		if ($showSlug) {
			if ($item && $slugType == 'visible' && !isset($_GET['copy']) && !isset($_POST['copy'])) {
				$slug = $item->getSlug();
				$url = $item->getObjectId() == 'post' ? $item->getSampleUrl() : $item->getUrl();
				$baseUrl = preg_replace('/' . $slug . '\/$/', '', $url);

				$content .= '<div class="mw_post_title_field_slug_url">';
				$content .= '<div class="mw_post_title_field_label">' . __('Trvalý odkaz:', 'cms') . '&nbsp;</div>';
				$content .= '<div class="mw_post_title_field_url">' . $baseUrl . '</div>';

				$content .= '<div class="mw_post_title_field_slug">';
				$content .= '<span>' . $slug . '</span>';
				$content .= mwAdminComponents::input([
					'type' => 'text',
					'name' => $field['slug_name'] ?? 'post_name',
				], $slug, 'mw_post_slug_field norewrite');
				$content .= '/</div>';

				$content .= mwAdminComponents::button([
					//'icon' => 'edit-2',
					'button_text' => __('Upravit', 'cms'),
					'style' => 'secondary_gray',
				], 'mw_post_title_field_but_edit');
				$content .= mwAdminComponents::button([
					//'icon' => 'edit-2',
					'button_text' => __('Potvrdit', 'cms'),
					'style' => 'secondary',
					'attrs' => 'data-itemid="' . $item->getId() . '" data-objectid="' . $item->getObjectId() . '"',
				], 'mw_post_title_field_but_ok');
				$content .= '</div>';
			} else {
				$content .= mwAdminComponents::input([
					'type' => 'hidden',
					'name' => $field['slug_name'] ?? 'post_name',
				], '', 'mw_post_slug_field');
			}
		}
		$content .= '</div>';

		return $content;
	}

	public static function userContactInfo($args, $val, $fieldName, $fieldId = '', $userId = 0)
	{
		$user = $userId ? mwUser::getOneById($userId) : null;
		$contactMethods = mwUser::getContactMethods();

		$content = '';

		foreach ($contactMethods as $mKey => $method) {
			$content .= '<div class="set_form_row">';
			$content .= mwAdminComponents::inputLabel([
				'label' => $method,
			]);
			$content .= mwAdminComponents::input([
				'name' => $fieldName . '[' . $mKey . ']',
			], $user !== null ? $user->getContactInfo($mKey) : '');
			$content .= '</div>';
		}

		return $content;
	}

	// termSelect
	public static function termSelect($args, $val, $fieldName, $fieldId = '', $itemId = 0)
	{
		$terms = mwTerm::getAll($args['term_id'], [
			'exclude_tree' => $args['exclude'] ?? [],
		]);
		$hTerms = mwTerm::sortHierarchical($terms);

		$options = [];
		if (isset($args['empty_text'])) {
			$options[] = [
				'name' => $args['empty_text'],
				'value' => -1,
			];
		}
		mwTerm::getHiearchicalOptions($hTerms, $options);

		return mwAdminComponents::select([
			'name' => $fieldName,
			'tag_id' => $fieldId,
			'options' => $options,
		], $val);
	}

	// itemSelect
	public static function itemSelect($args, $val, $fieldName, $fieldId = '')
	{
		$object = mwSetting()->getObject($args['object_id']);
		$getAllArgs = $args['args'] ?? [
			'post_status' => isset($args['only_published']) && $args['only_published'] ? 'publish' : 'any',
		];
		$items = $object->service()->getAll($getAllArgs, false);

		$options = [];
		if (isset($args['empty_text'])) {
			$options[] = [
				'name' => $args['empty_text'],
				'value' => $args['empty_value'] ?? 0,
			];
		}

		foreach ($items as $item) {
			$options[] = [
				'value' => $item->getID(),
				'name' => $item->getName(),
				'attrs' => 'data-url="' . $object->getEditUrl($item->getID()) . '"',
			];
		}

		$class = '';
		$whisperer = $args['whisperer'] ?? true;
		$class .= $whisperer ? ' mw_whisperer' : '';

		$content = '<div class="mw_item_selector mw_flex_field ' . ($val ? 'selected' : '') . '">';

		$content .= mwAdminComponents::select([
			'name' => $fieldName,
			'tag_id' => $fieldId,
			'options' => $options,
		], $val, $class);

		if (isset($args['edit_button']) && $args['edit_button']) {
			$content .= mwAdminComponents::iconLink([
				'icon' => 'edit-2',
				'title' => __('Upravit', 'cms_ve'),
				'target' => '_blank',
				'link' => $val ? $object->getEditUrl($val) : '',
			], 'mw_icon_button mw_icon_button_edit');
		}
		if (isset($args['add_button']) && $args['add_button']) {
			$content .= mwAdminComponents::iconLink([
				'icon' => 'plus',
				'attrs' => 'data-object="' . $object->getId() . '"',
				'title' => __('Přidat', 'cms_ve'),
			], 'mw_icon_button mw_icon_button_add');
		}

		$content .= '</div>';

		return $content;
	}

	// author select
	public static function authorSelect($args, $val, $fieldName, $fieldId = '', $itemId = 0)
	{
		$users = mwUser::getAll();
		$options = [];
		if (isset($args['empty_text'])) {
			$options[] = [
				'name' => $args['empty_text'],
				'value' => 0,
			];
		}
		foreach ($users as $user) {
			$options[] = [
				'name' => $user->getDisplayName(),
				'value' => $user->getId(),
			];
		}

		return mwAdminComponents::select([
			'name' => $fieldName,
			'tag_id' => $fieldId,
			'options' => $options,
		], $val);
	}

	// item Multi Select
	public static function itemMultiSelect($args, $val, $fieldName, $fieldId = '')
	{
		$object = mwSetting()->getObject($args['object_id']);
		$items = $object->service()->getAll([
			'post_status' => isset($args['only_published']) && $args['only_published'] ? 'publish' : 'any',
		], false);
		$content = '';
		foreach ($items as $item) {
			$content .= '<div class="set_form_subrow">';
			$content .= mwAdminComponents::checkbox([
				'name' => $fieldName . '[' . $item->getId() . ']',
				'label' => $item->getName(),
			], isset($val[$item->getId()]) ? 1 : 0);
			$content .= '</div>';
		}

		return $content;
	}

	public static function timeZoneSelect($args, $val, $fieldName, $fieldId = '', $itemId = 0)
	{
		$current_offset = get_option('gmt_offset');
		$tzstring = get_option('timezone_string');

		// Remove old Etc mappings. Fallback to gmt_offset.
		if (strpos($tzstring, 'Etc/GMT') !== false) {
			$tzstring = '';
		}

		if (empty($tzstring)) { // Create a UTC+- zone if no timezone string exists.
			$check_zone_info = false;
			if ($current_offset == 0) {
				$tzstring = 'UTC+0';
			} elseif ($current_offset < 0) {
				$tzstring = 'UTC' . $current_offset;
			} else {
				$tzstring = 'UTC+' . $current_offset;
			}
		}

		$content = '<select class="mw_select" autocomplete="off" name="' . $fieldName . '">';
		$content .= wp_timezone_choice($tzstring, get_user_locale());
		$content .= '</select>';

		return $content;
	}

	public static function dateTimeFormatSelect($args, $val, $fieldName, $fieldId = '', $itemId = 0)
	{
		$formats = $args['formats'];

		$options = [];
		foreach ($formats as $format) {
			$options[] = [
				'name' => current_time($format) . ' (' . $format . ')',
				'value' => $format,
			];
		}

		if (!in_array($val, $formats)) {
			$options[] = [
				'name' => current_time($val) . ' (' . $val . ')',
				'value' => $val,
			];
		}

		$content = mwAdminComponents::select([
			'name' => $fieldName,
			'tag_id' => $fieldId,
			'options' => $options,
		], $val);

		return $content;
	}

	public static function conversionCode($value, $name, $id, $field)
	{
		$content = mwAdminComponents::textarea([
			'name' => $name,
			'rows' => $field['rows'] ?? '4',
		], stripslashes($value));

		/*
		foreach(MwVariables::getVariableList() as $variable)
		{
			echo '%%'.$variable['code'].'%% ';
		} */

		$content .= MwVariables::variableListPop('conversion', __('Následující proměnné budou v konverzním kódu nahrazeny skutečnými daty konkrétní objednávky, pro kterou bude konverze vytvořena. Můžete tak do konverzního kódu vložit např. skutečnou cenu objednávky.', 'mwshop'));

		return $content;
	}

	public static function codeList($value, $name, $id, $field): string
	{
		$args = [
			'tagid' => $id,
			'tagname' => $name,
			'texts' => [
				'add' => isset($field['list_type']) && $field['list_type'] === 'conversion' ? __('Přidat konverzní kód', 'cms') : __('Přidat kód', 'cms'),
			],
			'open' => 'under',
			'title_function' => 'MwFields::codeListItemHead',
			'content_function' => 'MwFields::codeListItemContent',
			'list_type' => $field['list_type'] ?? '',
		];
		$content = '<input type="hidden" autocomplete="off" name="' . $name . '" value="">';
		$content .= self::multiElement($args, $value);

		return $content;
	}

	public static function codeListItemHead($args, $item, $i): string
	{
		$content = '<div class="mw_codes_item_title">';
		$content .= isset($item['title']) && $item['title'] ? $item['title'] : __('(bez názvu)', 'cms');
		$content .= '</div>';

		$disabledStatusText = __('Kód je deaktivován', 'cms');
		$content .= '<div class="mw_codes_item_disabled_status" data-disabled-text="' . $disabledStatusText . '">';

		if ($item['disabled'] ?? false) {
			$content .= $disabledStatusText;
		}

		$content .= '</div>';

		if ($args['list_type'] !== 'conversion') {
			$content .= '<div class="mw_codes_item_position">';

			if (!isset($item['position'])) {
				$item['position'] = 'header';
			}

			switch ($item['position']) {
				case 'header':
					$content .= __('V hlavičce', 'cms');

					break;
				case 'body':
					$content .= __('V těle', 'cms');

					break;
				case 'footer':
					$content .= __('V patičce', 'cms');

					break;
			}

			$content .= '</div>';
			$content .= '<div class="mw_codes_item_type">';

			if (!isset($item['type'])) {
				$item['type'] = 'necessary';
			}

			switch ($item['type']) {
				case 'necessary':
					$content .= __('Nezbytný', 'cms');

					break;
				case 'marketing':
					$content .= __('Marketingový', 'cms');

					break;
				case 'preferences':
					$content .= __('Preferenční', 'cms');

					break;
				case 'analytics':
					$content .= __('Statistický', 'cms');

					break;
			}

			$content .= '</div>';
		}

		return $content;
	}

	public static function codeListItemContent($args, $value, $i): string
	{
		$name = $args['tagname'] . '[' . $i . ']';
		$id = $args['tagid'] . '_' . $i;

		// title
		$content = '<div class="set_form_row">';
		$content .= mwAdminComponents::inputLabel([
			'label' => __('Název kódu', 'cms'),
			'tooltip' => __('Název slouží pouze pro vaší orientaci.', 'cms'),
		]);
		$content .= mwAdminComponents::input([
			'name' => $name . '[title]',
		], $value['title'] ?? '', 'mw_code_list_title_input');
		$content .= '</div>';

		if ($args['list_type'] === 'conversion') {
			// conversion code\
			$content .= '<div class="set_form_row">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Konverzní kód', 'cms'),
				'tooltip' => __('Pokud chcete do kódu dynamicky umístit hodnotu z URL adresy, vložte do kódu na místo, kde chcete hodnotu vypsat, řetězec ve tvaru: %%nazev_promenne%%. Pokud tedy budete chtít do kódu vložit například e-mailovou adresu z atributu e-mail (URL adresa bude obsahovat řetězec ve tvaru email=jmeno@poskytovatel.cz), vložte do kódu proměnnou %%email%%. V případě konverzního kódu AFFILBOXU, můžete nechat v konverzním kódu proměnné CENA a ID_TRANSAKCE. CENA se nahradí cenou staženou z faktury FAPI (pokud máte zadané propojení s FAPI a objednávka je vytvořena skrz FAPI) a ID_TRANSAKCE se nahradí variabilním symbolem objednávky nebo emailovou adresou (pokud je v url zadaná).', 'cms'),
			]);
			$content .= self::conversionCode($value['code'] ?? '', $name . '[code]', $id . '_code', ['rows' => 8]);
			$content .= '</div>';
		} else {
			// position
			$content .= '<div class="set_form_row">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Umístění kódu', 'cms'),
				'tooltip' => __('Umístění určí v jaké části stránky bude tento kód vykreslován. Doporučené umístění se dozvíte u poskytovatele kódu.', 'cms'),
			]);
			$content .= mwAdminComponents::select([
				'name' => $name . '[position]',
				'options' => [
					['name' => __('V hlavičce - uvnitř tagu &lt;/head&gt;', 'cms'), 'value' => 'header', 'attrs' => 'data-title="' . __('V hlavičce', 'cms') . '"'],
					['name' => __('V těle - hned za tagem &lt;body&gt;', 'cms'), 'value' => 'body', 'attrs' => 'data-title="' . __('V těle', 'cms') . '"'],
					['name' => __('V patičce - před tagem &lt;/body&gt;', 'cms'), 'value' => 'footer', 'attrs' => 'data-title="' . __('V patičce', 'cms') . '"'],
				],
			], $value['position'] ?? 'header', 'mw_code_list_select_position');
			$content .= '</div>';

			// code
			$content .= '<div class="set_form_row">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Kód', 'cms'),
			]);
			$content .= mwAdminComponents::textarea([
				'name' => $name . '[code]',
				'rows' => 8,
			], $value['code'] ?? '');
			$content .= '</div>';

			// type
			$content .= '<div class="set_form_row">';
			$content .= mwAdminComponents::inputLabel([
				'label' => __('Účel kódu', 'cms'),
				'tooltip' => __('Účel je důležitý pro GDPR a rozhoduje, zda se kód na stránce bude nebo nebude vykreslovat podle preferencí návštěvníka, které si zvolil při souhlasu s použitím cookies.', 'cms'),
			]);
			$content .= mwAdminComponents::gdprPurposeSelect([
				'name' => $name . '[type]',
			], $value['type'] ?? 'necessary', 'mw_code_list_select_type');
			$content .= '</div>';
		}

		// enabled
		$content .= '<div class="set_form_row">';
		$content .= mwAdminComponents::switch([
			'name' => $name . '[disabled]',
//			'true_val' => '1',
//			'false_val' => '0',
			'switch_label' => __('Dočasně deaktivovat tento kód', 'cms'),
		], (string) ($value['disabled'] ?? '0'), 'mw_code_list_checkbox_disabled');
		$content .= '</div>';

		return $content;
	}

	public static function pluginBlocker($args, $val, $fieldName, $fieldId = '', $itemId = 0): string
	{
		$content = '';
		if (! function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$listArgs = [
			'rows' => [],
			'empty_content' => __('Nemáte nahrané žádné pluginy.', 'cms'),
			'head' => [
				[
					'content' => __('Plugin', 'cms'),
				],
				[
					'content' => __('Povolit&nbsp;blokování', 'cms') . mwAdminComponents::tooltip(['text' => __('Povolit blokování znamená, že plugin bude blokován v případě že návštěvník nebude souhlasit s ukládáním cookies s účelem nastaveným u pluginu.', 'cms')]),
				],
				[
					'content' => __('Účel', 'cms'),
				],
			],
		];

		foreach (get_plugins() as $key => $plugin) {
			$listArgs['rows'][] = [
				'cols' => [
					[
						'content' => mwAdminComponents::statusPoint([
							'content' => $plugin['Name'] . mwAdminComponents::tooltip(['text' => $plugin['Description'], 'icon' => 'i']),
							'status' => is_plugin_active($key) ? 1 : 0,
						]),
					],
					[
						'content' => mwAdminComponents::switch([
							'name' => $fieldName . '[' . $key . '][block]',
						], $val[$key]['block'] ?? 0),
					],
					[
						'content' => mwAdminComponents::gdprPurposeSelect([
							'name' => $fieldName . '[' . $key . '][type]',
						], $val[$key]['type'] ?? 'necessary'),
					],
				],
			];
		}

		$content = mwAdminComponents::table($listArgs, 'mw_table_list2');

		return $content;
	}

}
