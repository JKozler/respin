<?php

class mwTutorials
{

	public $option;

	function __construct()
	{
		add_action('wp_ajax_intro_save_tutorial', [$this, 'save_tutorial']);
		add_action('wp_ajax_intro_open_tutorial', [$this, 'open_tutorial']);

		$this->option = get_option('cms_intro_tutorials');
	}

	function save_tutorial()
	{
		$options = get_option('cms_intro_tutorials');
		$options['tutorials'][$_POST['id']] = 1;
		update_option('cms_intro_tutorials', $options);
	}

	function open_tutorial()
	{
		echo '<div class="mw_tutorial_content">';
		echo '<h3>' . __('Rychlé seznámení s Mioweb editorem', 'cms_ve') . '</h3>';
		echo '<div class="mw_tutorial_video"><iframe src="//www.youtube.com/embed/B4kcQa8_TDU?wmode=transparent&enablejsapi=1&rel=0autoplay=0controls=1&autohide=1&mute=0" frameborder="0" allowfullscreen=""></iframe></div>';
		echo '<a class="mw_tutorial_close_but cms_button" href="#">Pokračovat k editaci webu</a>';
		echo '</div>';
		die();
	}


}
