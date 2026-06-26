<?php

function mw_register_widgets()
{
	register_widget('cms_posts_widget');
	register_widget('cms_authors_widget');
	register_widget('cms_optin_widget');
}

// last posts widget
// *****************************************************************************

class cms_posts_widget extends WP_Widget
{

	function __construct()
	{
		$widget_ops = ['classname' => 'widget_recent_entries widget_recent_entries_thumbs', 'description' => __('Zobrazí nejnovější nebo nejčtenější příspěvky s obrázkem.', 'cms_blog')];
		parent::__construct('cms_posts_widget', __('Nejnovější/Nejčtenější příspěvky s obrázkem', 'cms_blog'), $widget_ops);
	}

	/** @inheritDoc */
	function form($instance)
	{
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		$show_date = isset($instance['show_date']) ? (bool) $instance['show_date'] : false;
		$posts = $instance['posts'] ?? 'last';
		$image = $instance['image'] ?? 'thumbnail';

		?>
		<div class="widget_setting_row">
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
		</div>
		<div class="widget_setting_row">
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
			<input id="<?php echo $this->get_field_id('number'); ?>"
				   name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>"
				   size="3"/>
		</div>

		<div class="widget_setting_row">
			<input class="checkbox" type="checkbox" <?php checked($show_date); ?>
				  id="<?php echo $this->get_field_id('show_date'); ?>"
				  name="<?php echo $this->get_field_name('show_date'); ?>"/>
			<label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Display post date?'); ?></label>
		</div>
		<div class="widget_setting_row">
		<p><?php echo __('Náhledový obrázek:', 'cms_blog'); ?></p>
		<input class="radio" type="radio" <?php checked($image, 'thumbnail'); ?>
			   id="<?php echo $this->get_field_id('image'); ?>_thumbnail"
			   name="<?php echo $this->get_field_name('image'); ?>" value="thumbnail"/>
		<label
			for="<?php echo $this->get_field_id('image'); ?>_thumbnail"><?php echo __('Čtvercové náhledy', 'cms_blog'); ?></label>
		<br>
		<input class="radio" type="radio" <?php checked($image, 'mio_columns_5'); ?>
			   id="<?php echo $this->get_field_id('image'); ?>_mio_columns_5"
			   name="<?php echo $this->get_field_name('image'); ?>" value="mio_columns_5"/>
		<label
			for="<?php echo $this->get_field_id('image'); ?>_mio_columns_5"><?php echo __('Náhledy v poměru 4:3', 'cms_blog'); ?></label>
		<br>
		<input class="radio" type="radio" <?php checked($image, 'nothumb'); ?>
			   id="<?php echo $this->get_field_id('image'); ?>_nothumb"
			   name="<?php echo $this->get_field_name('image'); ?>" value="nothumb"/>
		<label
			for="<?php echo $this->get_field_id('image'); ?>_nothumb"><?php echo __('Skrýt náhledové obrázky', 'cms_blog'); ?></label>
		</div>
		<div class="widget_setting_row">
		<p><?php echo __('Typ článku:', 'cms_blog'); ?></p>
		<input class="radio" type="radio" <?php checked($posts, 'last'); ?>
			   id="<?php echo $this->get_field_id('posts'); ?>_last"
			   name="<?php echo $this->get_field_name('posts'); ?>" value="last"/>
		<label
			for="<?php echo $this->get_field_id('posts'); ?>_last"><?php echo __('Nejnovější články', 'cms_blog'); ?></label>
		<br>
		<input class="radio" type="radio" <?php checked($posts, 'mostviewed'); ?>
			   id="<?php echo $this->get_field_id('posts'); ?>_viewed"
			   name="<?php echo $this->get_field_name('posts'); ?>" value="mostviewed"/>
		<label
			for="<?php echo $this->get_field_id('posts'); ?>_viewed"><?php echo __('Nejčtenější články', 'cms_blog'); ?></label>
		</div>
		<?php
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = (bool) ($new_instance['show_date'] ?? false);
		$instance['image'] = $new_instance['image'];
		$instance['posts'] = $new_instance['posts'];

		$alloptions = wp_cache_get('alloptions', 'options');
		if (isset($alloptions['widget_recent_entries'])) {
			delete_option('widget_recent_entries');
		}

		return $instance;
	}

	function widget($args, $instance)
	{
		$cache = wp_cache_get('widget_recent_posts', 'widget');

		if (!is_array($cache)) {
			$cache = [];
		}

		if (!isset($args['widget_id'])) {
			$args['widget_id'] = $this->id;
		}

		if (isset($cache[$args['widget_id']])) {
			echo $cache[$args['widget_id']];

			return;
		}

		ob_start();
		extract($args);

		$title = !empty($instance['title']) ? $instance['title'] : __('Recent Posts');
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);

		$number = !empty($instance['number']) ? absint($instance['number']) : 10;
		if (!$number) {
			$number = 10;
		}
		$show_date = $instance['show_date'] ?? false;

		$image = $instance['image'] ?? 'thumbnail';

		$ratio = $image === 'thumbnail' ? '11' : '43';

		$filter = $instance['posts'] == 'mostviewed' ? [
				'posts_per_page' => $number,
				'post_type' => 'post',
				'post_status' => 'publish',
				'no_found_rows' => true,
				'ignore_sticky_posts' => true,
				'meta_key' => 'mioweb_post_visited',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
		] : [
				'posts_per_page' => $number,
				'post_type' => 'post',
				'post_status' => 'publish',
				'no_found_rows' => true,
				'ignore_sticky_posts' => true,
		];

		$rposts = mwBlogPost::getAll(apply_filters('widget_posts_args', $filter), false);
		if (count($rposts)) :
			?>
			<?php echo $before_widget; ?>
			<?php if ($title) {
				echo $before_title . $title . $after_title;
			} ?>
			<ul <?php if ($image == 'nothumb') {
				echo 'class="recent_post_list_nothumb"';
				} ?>>
			<?php foreach ($rposts as $rpost) {
				\assert($rpost instanceof mwBlogPost);

				?>
					<li>
				<?php
				if ($image != 'nothumb') {
					?>
							<div class="recent_post_thumb">
								<a class=" mw_image_ratio mw_image_ratio_<?php echo $ratio; ?> <?php echo $rpost->hasThumbnail() ? '' : 'recent_post_nothumb'; ?>"
									href="<?php echo $rpost->getUrl(); ?>"
									title="<?php echo esc_attr($rpost->getName()); ?>">
									<?php
									echo $rpost->getThumbnail()->printImg([
											'size' => 'mio_columns_c5',
											'max_width' => 85,
									]);
									?>
								</a>
							</div>
					<?php
				}
				?>
						<div class="mw_recent_post_body">
							<a class="mw_recent_post_title title_element_container" href="<?php echo $rpost->getUrl() ?>"
							   title="<?php echo esc_attr($rpost->getName()); ?>"><?php echo $rpost->getName(); ?></a>
				<?php if ($show_date) {
					?>
								<span class="post-date"><?php echo $rpost->getArticleDate(true); ?></span>
				<?php } ?>
						</div>
					</li>
			<?php } ?>
			</ul>
			<?php echo $after_widget; ?>
			<?php
		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_posts', $cache, 'widget');
	}

}

// authors widget
// *****************************************************************************

class cms_authors_widget extends WP_Widget
{

	function __construct()
	{
		//parent::__construct( false, 'Výpis článků s obrázkem' );
		$widget_ops = ['classname' => 'widget_authors', 'description' => __('Zobrazí seznam autorů blogu.', 'cms_blog')];
		parent::__construct('cms_authors_widget', __('Seznam autorů', 'cms_blog'), $widget_ops);
	}

	function form($instance)
	{
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		?>
		<div class="widget_setting_row">
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
				   name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
		</div>

		<?php
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		$alloptions = wp_cache_get('alloptions', 'options');
		if (isset($alloptions['widget_recent_entries'])) {
			delete_option('widget_recent_entries');
		}

		return $instance;
	}

	function widget($args, $instance)
	{
		global $wpdb;
		$cache = wp_cache_get('widget_authors', 'widget');

		if (!is_array($cache)) {
			$cache = [];
		}

		if (!isset($args['widget_id'])) {
			$args['widget_id'] = $this->id;
		}

		if (isset($cache[$args['widget_id']])) {
			echo $cache[$args['widget_id']];

			return;
		}

		ob_start();
		extract($args);

		$title = !empty($instance['title']) ? $instance['title'] : __('Autoři', 'cms_blog');
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);

		$authors = get_users([
				'role__not_in' => ['member','subscriber'],
		]);
		?>
		<?php echo $before_widget; ?>
		<?php if ($title) {
			echo $before_title . $title . $after_title;
		} ?>
		<ul>
		<?php

		foreach ($authors as $author) {
			$count = count_user_posts($author->ID);
			if (isset($count) && $count) {
				echo '<li><a  title="' . $author->display_name . '" href="' . get_author_posts_url($author->ID) . '">
               <div class="recent_post_thumb">' . get_avatar($author->ID, 60) . '</div>
               <div class="widget_author_name title_element_container">' . $author->display_name . '</div>
               <span class="post-date">' . $count . ' ' . __('článků', 'cms_blog') . '</span></a></li>';
			}
			//print_r($author );
		}

		?>
		</ul>
		<?php echo $after_widget;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_authors', $cache, 'widget');
	}

}


//SE optin widget
// *****************************************************************************

class cms_optin_widget extends WP_Widget
{

	function __construct()
	{
		$widget_ops = ['classname' => 'widget_optin', 'description' => __('Vykreslí formulář z vašeho e-mailmarketingového nástroje.', 'cms_blog')];
		parent::__construct('cms_optin_widget', __('Formulář', 'cms_blog'), $widget_ops);
	}

	function form($instance)
	{
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$text = isset($instance['text']) ? esc_attr($instance['text']) : '';
		$button_text = isset($instance['button_text']) ? esc_attr($instance['button_text']) : '';
		$form = $instance['form'] ?? '';
		$bg = $instance['bg'] ?? ['color1' => '#219ED1', 'color2' => '#1c93c4'];
		$font = $instance['font'] ?? ['font-size' => '20', 'color' => '#fff'];
		// popup
		$show_popup = isset($instance['show_popup']) ? (bool) $instance['show_popup'] : false;
		$popup_button_text = isset($instance['popup_button_text']) ? esc_attr($instance['popup_button_text']) : __('Odebírat novinky', 'cms_blog');
		$popup_text = isset($instance['popup_text']) ? esc_attr($instance['popup_text']) : '';
		$popup_title = isset($instance['popup_title']) ? esc_attr($instance['popup_title']) : '';

		?>
		<div class="mw_admin_setting_container widget_setting_form_container">
		<div class="widget_setting_row">
			<label for="<?php echo $this->get_field_id('form'); ?>"><?php echo __('Formulář:', 'cms_blog'); ?></label>
		<?php
		echo mwEmailingApi()->generate_api_select($this->get_field_name('form'), $this->get_field_id('form'), $form, 'forms');

		?>
		</div>

		<div class="widget_setting_row mw_admin_set">
			<label
				for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Nadpis nad formulářem:', 'cms_blog'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
				   name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
		</div>

		<div class="widget_setting_row">
			<label
				for="<?php echo $this->get_field_id('text'); ?>"><?php echo __('Text nad formulářem:', 'cms_blog'); ?></label>
			<textarea id="<?php echo $this->get_field_id('text'); ?>" class="widefat"
					  name="<?php echo $this->get_field_name('text'); ?>" cols="20"
					  rows="10"><?php echo $text; ?></textarea>
		</div>

		<div class="form_look_setting">
			<div class="widget_setting_row"><label
					for="<?php echo $this->get_field_id('button_text'); ?>"><?php echo __('Text tlačítka:', 'cms_blog'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('button_text'); ?>"
					   name="<?php echo $this->get_field_name('button_text'); ?>" type="text"
					   value="<?php echo $button_text; ?>"/></div>

			<div class="widget_setting_row widget_form_font"><label
					for="<?php echo $this->get_field_id('font'); ?>"><?php echo __('Font tlačítka formuláře:', 'cms_blog'); ?></label>
		<?php cms_generate_field_font($this->get_field_name('font'), $this->get_field_id('font'), $font, ['content' => ['font-size' => 17, 'color' => '#ffffff'], 'visible' => ['color', 'font-size']]); ?>
			</div>

			<div class="widget_setting_row"><label
					for="<?php echo $this->get_field_id('bg'); ?>"><?php echo __('Barva tlačítka formuláře:', 'cms_blog'); ?></label>
		<?php cms_generate_field_background($this->get_field_name('bg'), $this->get_field_id('bg'), $bg, ['content' => ['gradient' => '0'], 'hide_transparency' => 1]); ?>
			</div>
		</div>

		<div class="form_popup_setting">

			<div class="widget_setting_row">
				<input class="checkbox mw_form_widget_setting_show_popup_setting" autocomplete="none" type="checkbox" <?php checked($show_popup); ?>
					   id="<?php echo $this->get_field_id('show_popup'); ?>"
					   name="<?php echo $this->get_field_name('show_popup'); ?>"
					   value="1"/>
				<label
					for="<?php echo $this->get_field_id('show_popup'); ?>"><?php _e('Zobrazit formulář v popupu', 'cms_blog'); ?></label>
			</div>
			<div class="form_popup_setting_container <?php if (!$show_popup) {
				echo 'cms_nodisp';
													 } ?>">
				<div class="widget_setting_row">
					<label
						for="<?php echo $this->get_field_id('popup_button_text'); ?>"><?php echo __('Text popup tlačítka:', 'cms_blog'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('popup_button_text'); ?>"
						   name="<?php echo $this->get_field_name('popup_button_text'); ?>" type="text"
						   value="<?php echo $popup_button_text; ?>"/>
				</div>
				<div class="widget_setting_row">
					<label
						for="<?php echo $this->get_field_id('popup_title'); ?>"><?php echo __('Nadpis nad formulářem v popup:', 'cms_blog'); ?></label>
					<input id="<?php echo $this->get_field_id('popup_title'); ?>" class="widefat"
						   name="<?php echo $this->get_field_name('popup_title'); ?>" type="text"
						   value="<?php echo $popup_title; ?>"/>
				</div>
				<div class="widget_setting_row">
					<label
						for="<?php echo $this->get_field_id('popup_text'); ?>"><?php echo __('Text nad formulářem v popup:', 'cms_blog'); ?></label>
					<textarea id="<?php echo $this->get_field_id('popup_text'); ?>" class="widefat"
							  name="<?php echo $this->get_field_name('popup_text'); ?>" cols="20"
							  rows="10"><?php echo $popup_text; ?></textarea>
				</div>
			</div>


		</div>
		</div>

		<?php

		if ($this->number === '__i__') {
		?>
		<script>
			jQuery(document).ready(function ($) {
				$('#widgets-right .widget_setting_form_container').mwFormWidget();
			});
		</script>
		<?php
		}
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['text'] = $new_instance['text'];
		$instance['button_text'] = $new_instance['button_text'];
		$instance['form'] = $new_instance['form'];
		$instance['font'] = $new_instance['font'];
		$instance['bg'] = $new_instance['bg'];

		$instance['show_popup'] = $new_instance['show_popup'] ?? false;
		$instance['popup_button_text'] = $new_instance['popup_button_text'];
		$instance['popup_text'] = $new_instance['popup_text'];
		$instance['popup_title'] = $new_instance['popup_title'];

		//$instance['image'] = $new_instance['image'];

		$alloptions = wp_cache_get('alloptions', 'options');
		if (isset($alloptions['widget_recent_entries'])) {
			delete_option('widget_recent_entries');
		}

		return $instance;
	}

	function widget($args, $instance)
	{
		global $vePage;

		$cache = wp_cache_get('widget_recent_posts', 'widget');

		if (!is_array($cache)) {
			$cache = [];
		}

		if (!isset($args['widget_id'])) {
			$args['widget_id'] = $this->id;
		}

		if (isset($cache[$args['widget_id']])) {
			echo $cache[$args['widget_id']];

			return;
		}

		ob_start();
		extract($args);

		$title = !empty($instance['title']) ? $instance['title'] : '';
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		$text = !empty($instance['text']) ? $instance['text'] : '';
		$text = apply_filters('widget_text', $text, $instance, $this);
		$button_text = !empty($instance['button_text']) ? $instance['button_text'] : '';
		$form_id = !empty($instance['form']) ? $instance['form'] : '';
		// back compatibility (temporary)
		$form_id = mwEmailingApi()->repair_content_val($form_id);
		// end temporary
		$font = !empty($instance['font']) ? $instance['font'] : [];
		$background = !empty($instance['bg']) ? $instance['bg'] : [];

		$popup_text = !empty($instance['popup_text']) ? $instance['popup_text'] : '';
		$popup_text = apply_filters('widget_text', $popup_text, $instance, $this);
		$popup_title = !empty($instance['popup_title']) ? $instance['popup_title'] : '';
		$popup_title = apply_filters('widget_title', $popup_title, $instance, $this);
		$popup_button_text = !empty($instance['popup_button_text']) ? $instance['popup_button_text'] : __('Odebírat novinky', 'cms_blog');
		$show_popup = isset($instance['show_popup']) ? (bool) $instance['show_popup'] : false;

		echo $before_widget;
		if ($title) {
			echo $before_title . $title . $after_title;
		} ?>

		<div class="widget_optin_content">
		<?php
		if ($text) {
			echo '<p>' . $text . '</p>';
		}

		$element = [
			'style' => [
				'form-look' => 3,
				'form-style' => 1,
				'button_text' => $button_text,
				'background' => '',
				'form-font' => '',
				'button' => [//'style' => 1
				],
			],
		];

		$vePage->display->element_css = $vePage->display->css->createCssContainer();

		if (isset($form_id['id']) && $form_id['id']) {
			$form = mwEmailingApi()->get_form($form_id, false);
			$form_html = mwEmailingApi()->print_form($form_id['api'], $element, $form, $args['widget_id'], false);
		} else {
			// default for templates
			$form = [];
			$form['url'] = '';
			$form['submit'] = __('Odeslat', 'cms_ve');
			$form['fields']['field[df_emailaddress]'] = [
				'label' => __('Vložte svůj e-mail.', 'cms_blog'),
				'fieldname' => 'cms_email',
				'defaultfield' => '',
				'required' => '',
			];

			$form_html = $vePage->print_form($element, $form, $args['widget_id']);
		}

		if ($show_popup) {
			wp_enqueue_script('ve_lightbox_script');
			wp_enqueue_style('ve_lightbox_style');

			$but_content = '<a class="ve_content_button ve_content_button_1 open_lightbox_form" href="#">' . $popup_button_text . '</a>';

			$content = '
                  <script>
                  jQuery(document).ready(function($) {
                      $("#' . $args['widget_id'] . ' .open_lightbox_form").colorbox({inline:true,href:"#' . $args['widget_id'] . '_form",width:"90%",maxWidth:"600px"});
                  });
                  </script>
                  <div class="ve_center">' . $but_content . '<div class="cms_clear"></div></div>
                  <div style="display: none;">
                      <div id="' . $args['widget_id'] . '_form" class="popup_form_container">
                          ' . ($popup_title ? '<p class="popup_form_title title_element_container">' . $popup_title . '</p>' : '') . '
                          ' . ($popup_text ? '<p class="popup_form_text">' . nl2br($popup_text) . '</p>' : '') . '
                          <div id="' . $args['widget_id'] . '_form_container">' . $form_html . '</div>
                      </div>
                  </div>';

			echo $content;
		} else {
			echo '<div id="' . $args['widget_id'] . '_form_container">' . $form_html . '</div>';
		}

		//print_r($background);
		echo '<style>#' . $args['widget_id'] . '_form_container .ve_form_button_row button, #' . $args['widget_id'] . ' a.ve_content_button ' . '{' . $vePage->generate_style_atribut(['font' => $font, 'background_color' => $background]) . '}</style>';

		?>
		</div>
		<?php echo $after_widget; ?>
		<?php
		// Reset the global $the_post as this query will have stomped on it

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_posts', $cache, 'widget');
	}

}
