<?php

class cmsPopups
{

	public $popups_setting;

	public $edit_mode;

	public $popup_mode;

	public $popup;

	public $popups_onpage = [];

	public $footer_code = '';

	private $builder_mode;

	function __construct()
	{
		$this->edit_mode = current_user_can('edit_pages') ? true : false;

		$this->popup_mode = isset($_GET['window_editor']) && $_GET['window_editor'] == 'cms_popup';

		$this->builder_mode = $this->edit_mode && !isset($_GET['mw_preview']) ? true : false;

		if ($this->edit_mode) {
			add_action('wp_enqueue_scripts', [$this, 'load_admin_scripts']);
			add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);
		}

		if (!$this->builder_mode) {
			if ($this->popup_mode) {
				add_action('wp', [$this, 'get_popup_setting'], 100);
			}
		}
	}

	function load_admin_scripts()
	{
		wp_register_style('ve_popup_admin_style', get_bloginfo('template_url') . '/library/visualeditor/lib/weditor/popups_admin.css');

		wp_enqueue_script('ve_weditor_admin_script');
		wp_enqueue_style('ve_popup_admin_style');
	}

	function get_popup_setting()
	{
		global $vePage;
		$this->get_popup_setting_by_id($_GET['id']);
	}

	function get_popup_setting_by_id($id)
	{
		global $vePage;

		$this->popup = get_post_meta($id, 've_popup', true);

		$vePage->display->body_styles->addStyles([
			'max-width' => isset($this->popup['width']) ? $this->popup['width']['size'] . $this->popup['width']['unit'] : '800px',
			'background-color' => '#fff',
		], '.cms_popup_content_container .visual_content');

		$vePage->display->body_styles->addVariableStyles(
			[
				'.cms_popup_content_container .visual_content' => ['corner'],
			],
			'--popup-rounded-corners',
			($this->popup['corner'] ?? '0') . 'px'
		);

		$vePage->display->page_setting['background_image'] = [];
		$vePage->display->page_setting['background_color'] = $this->popup['background'] ?? '#000000';
	}

	function generate_popups()
	{
		if (!$this->popup_mode) {
			global $vePage;
			$popups = false;
			$content = '';

			if (isset($this->popups_setting['clasic_popup']) && $this->popups_setting['clasic_popup'] && get_post($this->popups_setting['clasic_popup']) && !$vePage->display->is_mobile) {
				$this->popups_onpage[$this->popups_setting['clasic_popup']] = 1;

				$content .= 'var show;
                show=$("#ve_popup_container_' . $this->popups_setting['clasic_popup'] . '").attr("data-show");';
				if ($this->popups_setting['popup_type'] == 'onload') {
					$content .= 'if(show=="0") { ve_show_popup(' . $this->popups_setting['clasic_popup'] . ');}';
				} else {
					if ($this->popups_setting['time'] > 0) {
						$content .= 'if(show=="0") { setTimeout(function() { ve_show_popup(' . $this->popups_setting['clasic_popup'] . ');}, ' . ($this->popups_setting['time'] * 1000) . '); }';
					}

					$scroll = '';
					$show_scroll = false;

					if ($this->popups_setting['scroll']['size'] > 0) {
						if ($this->popups_setting['scroll']['unit'] == 'px') {
							$content .= 'var height=' . $this->popups_setting['scroll']['size'] . ';';
						} else {
							$content .= 'var height=($( document ).height()/100)*' . $this->popups_setting['scroll']['size'] . ';';
						}
						$show_scroll = true;
					}
					if ($this->popups_setting['selector']) {
						$content .= 'var height=0; if($("' . $this->popups_setting['selector'] . '").length > 0) height=$("' . $this->popups_setting['selector'] . '").offset().top;';
						$show_scroll = true;
					}
					if ($show_scroll) {
						$scroll .= 'if ($(this).scrollTop() >= height || (($(this).scrollTop() + $(window).height()) >= $(document).height())) {
                                show=$("#ve_popup_container_' . $this->popups_setting['clasic_popup'] . '").attr("data-show");
                                if(show=="0") {
                                    ve_show_popup(' . $this->popups_setting['clasic_popup'] . ');
                                    $("#ve_popup_container_' . $this->popups_setting['clasic_popup'] . '").attr("data-show","1");
                                }}';
					}
					if ($scroll) {
						$content .= '$(window).scroll(function() {' . $scroll . '});';
					}
				}
			}

			if (isset($this->popups_setting['exit_popup']) && $this->popups_setting['exit_popup'] && get_post($this->popups_setting['exit_popup'])) {
				$this->popups_onpage[$this->popups_setting['exit_popup']] = 1;

				$content .= '$(document).mousemove(function(e) {
                    if(e.clientY <= 5) {
                        var show=$("#ve_popup_container_' . $this->popups_setting['exit_popup'] . '").attr("data-show");
                        if(show=="0") {
                            ve_show_popup(' . $this->popups_setting['exit_popup'] . ');
                        }
                        $("#ve_popup_container_' . $this->popups_setting['exit_popup'] . '").attr("data-show","1");
                    }
                });';
			}

			$return = '';

			if (count($this->popups_onpage)) {
				while ($this->popups_onpage) {
					$key = $this->mw_array_key_first($this->popups_onpage);
					unset($this->popups_onpage[$key]);
					$return .= $this->create_popup($key);
				}
				$this->popups_onpage = [];
				$return .= '<script type="text/javascript"> jQuery(document).ready(function($) { ' . $content . ' });</script>';
			}

			$this->footer_code = $return;
		}
	}

	/**
	 * @param mixed[] $array
	 * @return int|string|null
	 */
	function mw_array_key_first(array $array)
	{
		// since PHP 7.3
		if (function_exists('array_key_first')) {
			return array_key_first($array);
		}

		// Fallback for PHP < 7.3
		foreach (array_keys($array) as $key) {
			return $key;
		}

		return null;
	}

	function print_popups()
	{
		if (count($this->popups_onpage)) {
			foreach ($this->popups_onpage as $key => $val) {
				$this->footer_code .= $this->create_popup($key);
			}
		}

		if ($this->footer_code) {
			echo $this->footer_code;
			wp_enqueue_script('ve_lightbox_script');
			wp_enqueue_style('ve_lightbox_style');
		}
	}

	function create_popup($key)
	{
		$content = '';
		if (get_post($key)) {
			global $vePage;
			$setting = get_post_meta($key, 've_popup', true) ?: [];
			$layer = $vePage->display->get_layer($key, 'cms_popup');

			$popup_css = $vePage->display->css->createCssContainer();

			if (!isset($setting['corner'])) {
				$setting['corner'] = 0;
			}
			if (!isset($setting['background'])) {
				$setting['background'] = '#000000';
			}

			$popup_css->addStyles([
				'corner' => ($setting['corner'] ?? '0') . 'px',
			], '#ve_popup_container_' . $key);

			$content = $vePage->display->css->printCss($popup_css, 'popup_' . $key . '_style', $this->edit_mode);

			$vePage->display->disableLazyLoading();
			$content .= '<div style="display: none;"><div id="ve_popup_container_' . $key . '" class="ve_popup_container" data-delay="' . ($setting['delay'] ?? 2) . '" data-bg="' . $setting['background'] . '" data-width="' . $setting['width']['size'] . $setting['width']['unit'] . '" data-show="' . (isset($_COOKIE['ve_popup_' . $key]) ? 1 : 0) . '">' . $vePage->display->write_content($layer, false, 'popup_' . $key, false) . '</div></div>';
			$vePage->display->enableLazyLoading();
		}

		return $content;
	}

	function get_popup_to_content($key, $added = false, $selector = '', $edit_mode = false)
	{
		$content = '';

		global $vePage;

		if ($key !== '' && get_post($key) && get_post_type($key) == 'cms_popup') {
			if ($edit_mode) {
				$el_styles = $vePage->display->element_css;
				$content .= $this->create_popup($key);
				$vePage->display->element_css = $el_styles;

				wp_enqueue_script('ve_lightbox_script');
				wp_enqueue_style('ve_lightbox_style');

				if ($added) {
					$content .= "<script>
                    jQuery(function() {
                      mwGetIframeContent().mw_init_mw_popup('" . $selector . "');
                    });
                  </script>";
				}
			} else {
				$this->popups_onpage[$key] = 1;
			}
		} else {
			$vePage->display->add_element_info(__('Pop-up nastavený v tomto elementu již neexistuje. Pravděpodobně byl smazán. Prosím, nastavte jiný.', 'cms_ve'), 'error');
		}

		return $content;
	}

	public static function registerPopupPostType()
	{
		$labels = [
			'name' => __('Pop-upy', 'cms_ve'),
			'singular_name' => __('Pop-up', 'cms_ve'),
			'menu_name' => __('Pop-upy', 'cms_ve'),
			'name_admin_bar' => __('Pop-up', 'cms_ve'),
			'add_new' => __('Přidat pop-up', 'cms_ve'),
			'add_new_item' => __('Přidat nový pop-up', 'cms_ve'),
			'new_item' => __('Nový pop-up', 'cms_ve'),
			'edit_item' => __('Upravit pop-up', 'cms_ve'),
			'view_item' => __('Zobrazit pop-up', 'cms_ve'),
			'all_items' => __('Všechny pop-upy', 'cms_ve'),
			'search_items' => __('Hledat pop-upy', 'cms_ve'),
			'parent_item_colon' => ':',
			'not_found' => __('Pop-up nenalezen', 'cms_ve'),
			'not_found_in_trash' => __('Pop-up nenalezen', 'cms_ve'),
		];

		$wp_args = [
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'rewrite' => ['slug' => 'cms_popup'],
			'capability_type' => 'page',
			'has_archive' => false,
			'hierarchical' => false,
			'supports' => ['title', 'revisions'],
		];

		$mw_args = [
			'class' => 'mwWeditor',
			'supports' => ['visualeditor'],
			'public' => false,
			'weditor' => true,
			'labels' => [],
		];

		mwSetting()->registerPostType('cms_popup', $mw_args, $wp_args);
	}

}

/* Field type popup
************************************************************************** */

// selectpopup field type
function field_type_popupselect($field, $meta, $group_id)
{
	$content = $meta ?? ($field['content'] ?? '');
	$pages = get_posts(['post_type' => 'cms_popup', 'posts_per_page' => '1000']);
	$texts = [
		'empty' => __(' - Žádný pop-up - ', 'cms_ve'),
		'edit' => __('Upravit pop-up', 'cms_ve'),
		'duplicate' => __('Duplikovat pop-up', 'cms_ve'),
		'create' => __('Vytvořit nový pop-up', 'cms_ve'),
		'delete' => __('Smazat pop-up', 'cms_ve'),
	];
	cms_generate_field_weditor($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $content, $pages, 'cms_popup', $texts, 'weditorWithTemplate');
}
