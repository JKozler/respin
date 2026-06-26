<?php
define('MW_CURRENT_TUTORIAL_OPTION', 'mw_current_tutorial');
define('MW_TUTORIALS_OPTION', 'mw_tutorials');

class mwTutorials
{

	public $tutorial;

	public $start_step = 0;

	public $current_tut = [];

	private $tutorials = [];

	function __construct()
	{
		add_action('wp_ajax_mw_get_modal_content', [$this, 'get_modal_content']);
		add_action('wp_ajax_mw_end_tutorial', [$this, 'end_tutorial']);
		add_action('init', [$this, 'init_tutorial']);

		/*
		if(isset($_GET['new_onboarding'])) {
		delete_option(MW_CURRENT_TUTORIAL_OPTION);
		delete_option(MW_TUTORIALS_OPTION);
		delete_option('ve_installed_web');
		wp_redirect(get_home_url());
		die();
		} */
	}

	function setTutorial($tutorial_name)
	{
		$this->tutorial = $tutorial_name;
		$this->tutorials = get_option(MW_TUTORIALS_OPTION);
		$this->current_tut = get_option(MW_CURRENT_TUTORIAL_OPTION);

		if (!isset($this->tutorials[$this->tutorial])) {
			if ($tutorial_name == 'game') {
				MwWebInstall()->webs = [
					'mia' => get_template_directory() . '/library/visualeditor/web_templates/mia/',
					'mio' => get_template_directory() . '/library/visualeditor/web_templates/mio/',
				];
				MwWebInstall()->tags = [
					'tuts' => __('Tutoriálové', 'cms_ve'),
				];

				if (MW()->installedWeb()) {
					$this->start_step = 1;
				}
			}
			if (!$this->current_tut) {
				$template = MW()->installedWeb();

				$this->current_tut = [
					'tut' => $this->tutorial,
					'step' => $this->start_step,
					'template' => ($template['web_theme'] ?? ''),
					'start' => 0,
					'time' => '',
				];

				update_option(MW_CURRENT_TUTORIAL_OPTION, $this->current_tut);
			}

			add_action('wp_footer', [$this, 'add_footer_code']);
			add_action('wp_enqueue_scripts', [$this, 'load_scripts'], 3);

			return true;
		}

		return false;
	}

	function init_tutorial()
	{
		$cur_tut = get_option(MW_CURRENT_TUTORIAL_OPTION);
		if ($cur_tut && isset($cur_tut['tut']) && $cur_tut['tut']) {
			$this->setTutorial($cur_tut['tut']);
		}
	}

	function load_scripts()
	{
		$script_version = filemtime(get_template_directory() . '/style.css');
		wp_register_script('mw_tutorials_script', get_bloginfo('template_url') . '/library/visualeditor/lib/tutorials/tutorials.js', ['jquery'], $script_version, true);
		wp_register_style('mw_tutorials_styles', get_bloginfo('template_url') . '/library/visualeditor/lib/tutorials/tutorials.css', [], $script_version);
		wp_enqueue_script('mw_tutorials_script');
		wp_enqueue_style('mw_tutorials_styles');
		wp_enqueue_style('mw_tutorials_font', 'https://fonts.googleapis.com/css?family=Caveat:700&display=swap');
	}

	function get_modal_content()
	{
		require_once(__DIR__ . '/tutorials/' . $_POST['tutorial'] . '.php');

		$modal = $_POST['type'] . '_modal';

		echo $$modal;

		die();
	}

	function add_footer_code()
	{
		$name = '';
		$name = $this->current_tut['template'] == 'mia' ? 'Katka' : 'Lukáš';

		require_once(__DIR__ . '/tutorials/' . $this->tutorial . '.php');

		echo '<div class="mw_intro_info_container mw_intro_info_container_t_' . $this->current_tut['template'] . '">'
		. '<div class="mw_intro_info_steps"></div>'
		. '<div class="mw_intro_info_inn">'
		. '<h3 class="mw_intro_info_title"></h3>'
		. '<div class="mw_intro_info_text"></div>'
		. '<a href="#" class="mw_intro_info_skip_link mw_intro_skip">' . __('Přeskočit hru', 'cms_ve') . '</a>'
		. '</div>'
		. '<div class="mw_intro_info_avatar"></div>'
		. '</div>';

		echo '<div class="mw_intro_modal_container">'
		. '<div class="mw_intro_modal_overlay">'
		. '<div class="mw_intro_modal"></div>'
		. '</div>'
		. '</div>';

		echo '<div class="mw_intro_cheer"><span></span></div>';

		// start time after reload
		$setting['start'] = $this->current_tut['start'];
		$setting['temp'] = $this->current_tut['template'];

		echo '<script>'
		. 'jQuery(function($) {'
		. '$(".mw_page_builder").mwIntro(' . json_encode($setting) . ',' . json_encode($steps) . ',' . $this->start_step . ');'
		. '});'
		. '</script>';
	}

	function end_tutorial()
	{
		$current_tut = get_option(MW_CURRENT_TUTORIAL_OPTION);
		$current_tut['time'] = $_POST['time'];
		$current_tut['step'] = $_POST['step'];
		$tutorials = get_option(MW_TUTORIALS_OPTION);
		delete_option(MW_CURRENT_TUTORIAL_OPTION);

		$installed_web = get_option('ve_installed_web');
		if ($installed_web) {
			if (isset($installed_web['pages']) && is_array($installed_web['pages'])) {
				foreach ($installed_web['pages'] as $del) {
					wp_delete_post($del);
				}
			}
			if (isset($installed_web['images']) && is_array($installed_web['images'])) {
				foreach ($installed_web['images'] as $del) {
					wp_delete_attachment($del, true);
				}
			}
		}
		delete_option('ve_installed_web');

		$tutorials[$current_tut['tut']] = $current_tut;

		update_option(MW_TUTORIALS_OPTION, $tutorials);
	}

}
