<?php

use Mioweb\VisualEditor\Lib\Colors;
use Mioweb\VisualEditor\Lib\GDPR;
use Mioweb\VisualEditor\Lib\Link;
use Mioweb\VisualEditor\Lib\Image;

function mwBlog()
{
   return MwBlog::instance();
}

class MwBlog
{

	protected static $_instance = null;

	public $edit_mode;

	public $builder_mode;

	public $appearance;

	public $setting;

	public $templates = [];

	public $template;

	public $template_path;

	public $template_directory;

	public $script_version;

	public $top_panel = [];

	public $blog_title_class;

	public $article_title_class;

	function __construct()
	{
		$this->edit_mode = current_user_can('edit_pages') ? true : false;
		$this->builder_mode = $this->edit_mode && !isset($_GET['mw_preview']) ? true : false;

		$this->check_version();

		$this->appearance = get_option('blog_appearance');

		if ($this->edit_mode) {
			//after save global or local setting
			add_filter('mw_change_switch_option', [$this, 'change_switch_option'], 20, 2);
		}

		add_action('wp', [$this, 'init']); //init

		//visual setting
		if (!$this->builder_mode) {
			add_action('ve_global_setting', [$this, 'useBlogVisual']);
			add_action('mw_global_styles', [$this, 'addBlogStyles']);

			add_filter('pre_get_posts', [$this, 'search_filter']);

			// load scripts
			add_action('wp_enqueue_scripts', [$this, 'load_front_scripts']);

			// Excerpt
			add_filter('excerpt_more', [$this, 'new_excerpt_more']);
			add_filter('excerpt_length', [$this, 'new_excerpt_length'], 999);

			add_filter('render_block', [$this, 'modifyRenderedBlocks'], 10, 2);
		}

		add_action('widgets_init', 'mw_register_widgets');

		// coments
		add_filter('comment_form_submit_field', [$this, 'add_accept_field'], 999);
		//add_action('pre_comment_on_post', array($this, 'checkPost'));
		add_action('comment_post', [$this, 'add_accepted_to_comment_meta']);

		// post format support
		add_theme_support('post-formats', ['video', 'quote']);

		// for template post thumbnails on url
		add_filter('post_thumbnail_html', [$this, 'get_custom_post_thumbnail_html'], 10, 5);

		add_filter('nav_menu_css_class', [$this, 'fix_blog_link_on_cpt'], 10, 3);

		add_filter('is_protected_meta', [$this, 'mw_protected_meta_filter'], 10, 2);

		add_filter('home_template_hierarchy', [$this, 'hookGetHomeTemplate'], 10);
		add_filter('single_template_hierarchy', [$this, 'hookGetSingleTemplate'], 10);
		add_filter('archive_template_hierarchy', [$this, 'hookGetArchiveTemplate'], 10);
		add_filter('category_template_hierarchy', [$this, 'hookGetCategoryTemplate'], 10);
		add_filter('tag_template_hierarchy', [$this, 'hookGetTagTemplate'], 10);
		add_filter('date_template_hierarchy', [$this, 'hookGetDateTemplate'], 10);
		add_filter('search_template_hierarchy', [$this, 'hookGetSearchTemplate'], 10);
		add_filter('author_template_hierarchy', [$this, 'hookGetAuthorTemplate'], 10);

		add_filter('use_default_gallery_style', '__return_false');

		// disable author page for members
		add_action('template_redirect', [$this, 'disableAuthorPageForMemberRole']);
		// remove members author page from sitemap
		add_filter('wp_sitemaps_users_query_args', [$this, 'removeMembersUserFromSitemap']);

		// html tags to user description
		remove_filter('pre_user_description', 'wp_filter_kses');
	}

	function get_custom_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr)
	{
		return strpos($post_thumbnail_id, 'http') !== false ? '<img src="' . $post_thumbnail_id . '" alt="" />' : $html;
	}

	function init()
	{
		if (!isset($this->appearance['post_look'])) {
			$this->appearance['post_look'] = 1;
		}
		if (!isset($this->appearance['post_detail_look'])) {
			$this->appearance['post_detail_look'] = 1;
		}
		if (isset($this->appearance['masonry']) && $this->appearance['post_look'] != 3) {
			unset($this->appearance['masonry']);
		}

		$this->template = $this->appearance['appearance'];
		$this->template_path = get_bloginfo('template_url') . '/' . $this->templates[$this->template]['path'];
		$this->template_directory = get_template_directory() . '/' . $this->templates[$this->template]['path'];
		//require_once(get_blog_directory() . 'loop.php');

		if ($this->is_blog()) {
			global $vePage, $post;
			//if(is_home()) $post->ID=0;
			$vePage->modul_type = 'blog';

			$this->blog_title_class = $vePage->display->get_font_class($this->appearance['tb_font'], 'title');
			$this->article_title_class = $vePage->display->get_font_class($this->appearance['article_font'], 'title');

			add_filter('body_class', [$this, 'add_bodyclass']); //add body class
			add_filter('the_content', [$this, 'blog_content_filter']);

			$blog_id = get_option('page_for_posts');
			$this->setting = get_option('blog_comments');

			if (isset($this->setting['blog_logolink']) && $this->setting['blog_logolink'] == 'blog') {
				if ($blog_id) {
					$vePage->display->home_url = get_permalink($blog_id);
					$vePage->display->home_id = $blog_id;
				}
			}
			//add blog setting codes
			MwCodes()->addCodesFromOption('mw_blog_codes', false);

			if (is_single() && isset($post->ID)) {
				$this->addPostVisit($post->ID);
			}
		}
	}

	function isPostVisited(int $postId): bool
	{
		if (isset($_SESSION['mioweb_post_visited_' . $postId])) {
			if (MwCookies()->isPermitted('analytics')) {
				setcookie('mioweb_post_visited_' . $postId, 1, time() + (60 * 60 * 24 * 2), COOKIEPATH, COOKIE_DOMAIN);
				unset($_SESSION['mioweb_post_visited_' . $postId]);
			}

			return true;
		}

		return isset($_COOKIE['mioweb_post_visited_' . $postId]);
	}

	function addPostVisit(int $postId): void
	{
		if (!$this->isPostVisited($postId)) {
			$post_visited = get_post_meta($postId, 'mioweb_post_visited', true);

			if ($post_visited) {
				global $wpdb;
				$wpdb->query('UPDATE ' . $wpdb->postmeta . " SET meta_value=meta_value+1 WHERE post_id='" . $postId . "' AND meta_key='mioweb_post_visited'");
			} else {
				$post_visited = 1;
				update_post_meta($postId, 'mioweb_post_visited', $post_visited);
			}

			if (MwCookies()->isPermitted('analytics')) {
				setcookie('mioweb_post_visited_' . $postId, 1, time() + (60 * 60 * 24 * 2), COOKIEPATH, COOKIE_DOMAIN);
			} else {
				$_SESSION['mioweb_post_visited_' . $postId] = 1;
			}
		}
	}

	function hookGetHomeTemplate($templates)
	{
		global $post;

		//if ($this->getHomePage() == $post->ID)
		$templates = isset($_GET['window_editor']) ? ['window_editor.php'] : [$this->get_locale_path() . 'home.php'];

		return $templates;
	}

	function hookGetSingleTemplate($templates)
	{
		$obj = get_queried_object();
		if ($obj && ($obj->post_type) == 'post') {
			$templates = [];
			$templates[] = $this->get_locale_path() . 'single.php';
		}

		return $templates;
	}

	function hookGetArchiveTemplate($templates)
	{
		$templates = [$this->get_locale_path() . 'archive.php'];

		return $templates;
	}

	function hookGetAuthorTemplate($templates)
	{
		$templates = [$this->get_locale_path() . 'author.php'];

		return $templates;
	}

	function hookGetCategoryTemplate($templates)
	{
		$templates = [$this->get_locale_path() . 'category.php'];

		return $templates;
	}

	function hookGetDateTemplate($templates)
	{
		$templates = [$this->get_locale_path() . 'date.php'];

		return $templates;
	}

	function hookGetSearchTemplate($templates)
	{
		$templates = [$this->get_locale_path() . 'search.php'];

		return $templates;
	}

	function hookGetTagTemplate($templates)
	{
		$templates = [$this->get_locale_path() . 'tag.php'];

		return $templates;
	}

	function get_locale_path()
	{
		return $this->templates[$this->template]['path'] . $this->templates[$this->template]['folder'] . '/';
	}

	function mw_protected_meta_filter($protected, $meta_key)
	{
		return $meta_key == 'mioweb_post_visited' ? true : $protected; // protect meta key with number of visitors of blog post
	}

	function load_front_scripts()
	{
		$this->script_version = filemtime(get_template_directory() . '/style.css');

		if ($this->is_blog()) {
			wp_enqueue_style('blog_content_css', $this->get_blog_url() . $this->templates[$this->template]['style'] . '.css', [], $this->script_version);
			wp_enqueue_script('ve_lightbox_script');
			wp_enqueue_style('ve_lightbox_style');

			if (isset($this->appearance['masonry'])) {
				wp_enqueue_script('ve_masonry_script');
			}
		}
	}

	function blog_content_filter($content)
	{
		return add_lightbox($content);
	}

	function add_bodyclass($classes)
	{
		$classes[] = isset($this->appearance['blog_sidebar']) ? 'blog-structure-sidebar-' . $this->appearance['structure'] : 'blog-structure-sidebar-none';
		$classes[] = 'blog-appearance-' . $this->appearance['appearance'];
		$classes[] = 'blog-posts-list-style-' . $this->appearance['post_look'];
		$classes[] = 'blog-single-style-' . $this->appearance['post_detail_look'];

		return $classes;
	}

	function search_filter($query)
	{
		if ($query->is_search && !is_admin()) {
			$query->set('post_type', 'post');
		}

		return $query;
	}

	function get_blog_directory()
	{
		return $this->template_directory . $this->templates[$this->template]['folder'] . '/';
	}

	function get_blog_url()
	{
		return $this->template_path . $this->templates[$this->template]['folder'] . '/';
	}

	function get_blog_icon($icon)
	{
		return mw_icon('mwbi-' . $icon, '', $this->get_blog_url() . 'images/icons.svg');
	}

	function useBlogVisual()
	{
		if ($this->is_blog()) {
			global $vePage;

			if (!isset($this->appearance['custom_blog_fonts'])) {
				$this->appearance['title_font'] = $vePage->display->page_setting['title_font'];
				$this->appearance['font'] = $vePage->display->page_setting['font'];
				$this->appearance['link_color'] = $vePage->display->page_setting['link_color'];
			}
			if ($this->appearance['appearance'] == 'style3') {
				$this->appearance['background_color'] = '#f1f1f1';
				$this->appearance['background_image'] = [];
			} elseif ($this->appearance['appearance'] == 'style4') {
				$this->appearance['background_color'] = '#fff';
				$this->appearance['background_image'] = [];
			} else {
				$this->appearance['use_page_background'] = 1;
			}
			$vePage->display->page_setting = $this->appearance;

			$setting = get_option('blog_header');
			if (isset($setting['show']) && $setting['show'] != 'global') {
				$vePage->display->header_setting = $setting;
				$vePage->display->used_header = 'blog_header';
			}
			$setting = get_option('blog_footer');
			if (isset($setting['show']) && $setting['show'] != 'global') {
				$vePage->display->footer_setting = $setting;
			}

			$vePage->display->popups->popups_setting = get_option('blog_popups');

			if (is_category() || is_tag()) {
				$mwTerm = new mwTerm(get_queried_object());
				$this->top_panel['image'] = $mwTerm->getThumbnail();
				\assert($this->top_panel['image'] instanceof Image);
			}
		}
	}

	function addBlogStyles()
	{
		if ($this->is_blog()) {
			global $vePage;

			if (isset($this->top_panel['image']) && !$this->top_panel['image']->isEmpty()) {
				$vePage->display->css->addGlobalStyle('#blog_top_panel', [
					'bg' => [
						'background_color' => [
							'rgba1' => $vePage->display->page_setting['tb_background']['color1'],
							'rgba2' => $vePage->display->page_setting['tb_background']['color2'] ?? '',
						],
						'background_image' => [
							'image' => $this->top_panel['image']->getUrl('full'),
						],
					],
				]);

				$vePage->display->css->addGlobalStyle('#blog_top_panel', [
					'bg' => [
						'background_image' => [
							'image' => $this->top_panel['image']->getUrl('large'),
						],
					],
				], 'tablet');

				unset($vePage->display->page_setting['tb_font']['color']);
			} else {
				$vePage->display->css->addGlobalStyle('#blog_top_panel', [
					'bg' => [
						'background_color' => [
							'rgba1' => $vePage->display->page_setting['tb_background']['color1'],
							'rgba2' => $vePage->display->page_setting['tb_background']['color2'] ?? '',
						],
					],
				]);
			}

			$vePage->display->css->addGlobalStyle('#blog_top_panel h1', [
				'font' => $vePage->display->page_setting['tb_font'],
			]);

			if (isset($vePage->display->page_setting['tb_font']['color'])) {
				$vePage->display->css->addGlobalStyle('#blog_top_panel .blog_top_panel_text, #blog_top_panel .blog_top_panel_subtext, #blog_top_panel .blog_top_author_title small, #blog_top_panel .blog_top_author_desc', [
					'font' => ['color' => $vePage->display->page_setting['tb_font']['color']],
				]);
			}

			$vePage->display->css->addGlobalStyle('#blog-sidebar .widgettitle', [
				'font' => $vePage->display->page_setting['sidebar_font'],
			]);
			$vePage->display->css->addGlobalStyle('.article h2 a', [
				'font' => $vePage->display->page_setting['article_font'],
			]);
			$vePage->display->css->addGlobalStyle('.entry_content', [
				'line-height' => isset($vePage->display->page_setting['font']['line-height']) && $vePage->display->page_setting['font']['line-height'] ? $vePage->display->page_setting['font']['line-height'] : '',
			]);
			$vePage->display->css->addGlobalStyle('.article_body .excerpt', [
				'font' => $vePage->display->page_setting['article_font_text'] ?? '',
			]);

			// article button
			$vePage->display->css->addGlobalStyle('.article .article_button_more', [
				'background-color' => $vePage->display->page_setting['button_color'] ?? '',
			]);
			$vePage->display->css->addGlobalStyle('.article .article_button_more:hover', [
				'background-color' => isset($vePage->display->page_setting['button_color']) ? Colors::shiftColor($vePage->display->page_setting['button_color'], 0.8) : '',
			]);
		}
	}

	function print_blog_comments($style = 1)
	{
		global $post;
		$blog_setting = $this->setting;
		$page_comments = get_post_meta($post->ID, 'page_comments', true);
		$wordpress = isset($blog_setting['comments']['wordpress']) && (!isset($page_comments['hide_comments']) || !isset($page_comments['hide_comments']['wordpress'])) ? true : false;
		$facebook = isset($blog_setting['comments']['facebook']) && (!isset($page_comments['hide_comments']) || !isset($page_comments['hide_comments']['facebook'])) ? true : false;
		$order = isset($blog_setting['comments_order']) && $blog_setting['comments_order'] ? $blog_setting['comments_order'] : 'wordpress';
		$order = isset($page_comments['comments_order']) && $page_comments['comments_order'] ? $page_comments['comments_order'] : $order;
		if (((comments_open() || get_comments_number()) && $wordpress) || $facebook) {
			$mw_comment_set = [
				'comment_style' => $style,
			];
			echo '<div class="commenttitle title_element_container">' . __('Komentáře', 'cms_blog') . '</div>';
			if ($order == 'wordpress') {
				if ($wordpress) {
					echo '<div class="element_comment_' . $style . ' blog_comments">';
					comments_template('/comments.php');
					echo '</div>';
				}
				if ($facebook) {
					echo cms_facebook_comments(get_permalink());
				}
			} else {
				if ($facebook) {
					echo cms_facebook_comments(get_permalink());
				}
				if ($wordpress) {
					echo '<div class="element_comment_' . $style . ' blog_comments">';
					comments_template('/comments.php');
					echo '</div>';
				}
			}
		}
	}

	function is_blog()
	{
		global $post;
		$posttype = get_post_type($post);

		return ((is_archive() && $posttype == 'post') || (is_author()) || (is_category()) || (is_home()) || (is_tag()) || (is_search()) || (is_single() && ($posttype == 'post'))) && !isset($_GET['window_editor']) ? true : false;
	}

	function change_switch_option($option)
	{
		$opt = $option;
		if ($_POST['modul_type'] == 'blog') {
			if ($option == 've_header') {
				$setting = get_option('blog_header');

				if (isset($setting['show']) && $setting['show'] != 'global') {
					$opt = 'blog_header';
				}
			} elseif ($option == 've_footer') {
				$setting = get_option('blog_footer');
				if (isset($setting['show']) && $setting['show'] != 'global') {
					$opt = 'blog_footer';
				}
			}
		}

		return $opt;
	}

	function fix_blog_link_on_cpt($classes, $item, $args)
	{
		if (!$this->is_blog()) {
			$blog_page_id = intval(get_option('page_for_posts'));
			if ($blog_page_id != 0 && $item->object_id == $blog_page_id) {
				unset($classes[array_search('current_page_parent', $classes)]);
			}
		}

		return $classes;
	}

	function get_visit_number($post_id)
	{
		$post_visited = get_post_meta($post_id, 'mioweb_post_visited', true);
		if (!$post_visited) {
			$post_visited = 1;
		}

		return $post_visited;
	}

	function add_template($id, $set)
	{
		$this->templates[$id] = $set;
	}

	/* Excerpt
	************************************************************************** */

	function new_excerpt_more($more)
	{
		return '...';
	}

	function new_excerpt_length($more)
	{
		return isset($this->appearance['excerpt_length']) && $this->appearance['excerpt_length']['size'] ? $this->appearance['excerpt_length']['size'] : $more;
	}

	// @TODO replace with mwBlogPost::getArticleDate();
	function get_post_date($only_modify = false)
	{
		$date = get_the_date('j.n. Y');
		$modif_date = get_the_modified_date('j.n. Y');

		$content = $date;
		if ($date != $modif_date && isset($this->setting['show']['updated'])) {
			if ($only_modify) {
				$content = $modif_date;
			} else {
				$content .= ' (' . __('Aktualizováno', 'cms_blog') . ': ' . $modif_date . ')';
			}
		}

		return $content;
	}

	/* Aktivace šablony
	************************************************************************** */


	function check_version()
	{
		$versions = get_option('cms_versions');
		if (isset($versions['blog']) && $versions['blog'] != BLOG_VERSION) {
			if (version_compare($versions['blog'], '1.0', '<')) {
				// blog seo
				$home_seo = get_option('home_seo');
				update_option('mw_blog_seo', $home_seo);

				// blog fonts
				$blog_appearance = get_option('blog_appearance');
				$blog_appearance['custom_blog_fonts'] = 1;

				// sidebar
				$blog_appearance['blog_sidebar'] = $blog_appearance['structure'] == 'nosidebar' ? 0 : 1;

				update_option('blog_appearance', $blog_appearance);
			}

			if (version_compare($versions['blog'], '3.1.1', '<')) {
				// blog codes
				$oldCodes = get_option('blog_codes');
				$newCodes = MwCodes::convertCodesFromOldData($oldCodes, 'head_scripts', null, 'footer_scripts', 'css_scripts');
				update_option('mw_blog_codes', $newCodes);
			}

			$versions['blog'] = BLOG_VERSION;
			update_option('cms_versions', $versions);
		}
	}

	/* gdpr */
	function add_accept_field($submitField = '')
	{
		return GDPR::printConsent('comment') . $submitField;
	}

	function add_accepted_to_comment_meta($comment_id = 0)
	{
		if (isset($_POST['mw_gdpr_consent']) && !empty($comment_id)) {
			$val = [
				'time' => current_time('timestamp', 0),
				'text' => $_POST['mw_gdpr_consent'],
			];
			add_comment_meta($comment_id, '_mw_comment_gdpr', $val);
		}
	}

	/* dashboard */
	public static function dashboard()
	{
		$content = mwAdminComponents::title([
			'text' => __('Celkové statistiky', 'cms_blog'),
			//'onright' => mwAdminComponents::rangeSelect([]),
		], 'h2');

		// statistics
		$content .= '<div class="mw_dashboard_statistics">';

		global $wpdb;
		$visits = $wpdb->get_col("SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = 'mioweb_post_visited' AND p.post_type = 'post'");

		$content .= mwAdminComponents::statisticsMainBox([
			'value' => number_format($visits[0] ?? 0, 0, '.', ' '),
			'text' => __('Návštěv článků', 'cms_blog'),
			'icon' => 'eye',
		]);

		$content .= mwAdminComponents::statisticsBox([
			'value' => number_format(wp_count_posts()->publish, 0, '.', ' '),
			'text' => __('Publikovaných článků', 'cms_blog'),
			'icon' => 'file-text',
		]);

		$comments_count = get_comments([
			'post_type' => 'post',
			'count' => true,
		]);

		$content .= mwAdminComponents::statisticsBox([
			'value' => number_format($comments_count, 0, '.', ' '),
			'text' => __('Komentářů', 'cms_blog'),
			'icon' => 'message-square',
		]);
		$content .= '</div>';

		$object = mwSetting()->getObject('post');

		$content .= mwAdminComponents::title([
			'text' => __('Nejčtenější články', 'cms_blog'),
		], 'h2');

		$listArgs = [
			'rows' => [],
			'empty_content' => $object->getLabel('empty'),
			'head' => [
				[
					'content' => __('Název', 'cms'),
				],
				[
					'content' => __('Autor', 'cms'),
				],
				[
					'content' => __('Návštěv', 'cms'),
				],
				[
					'content' => __('Komentářů', 'cms'),
				],
				[
					'content' => __('Akce', 'cms'),
					'align' => 'right',
				],
			],
		];

		$query = mwBlogPost::getAll([
			'post_status' => 'any',
			'posts_per_page' => 10,
			'no_found_rows' => true,
			'ignore_sticky_posts' => true,
			'meta_key' => 'mioweb_post_visited',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
		], true);

		foreach ($query['items'] as $item) {
			$author = $item->getAuthor();
			$listArgs['rows'][] = [
				'cols' => [
					[
						'content' => '<a class="mw_link" target="_blank" href="' . $object->getEditWPUrl($item->getId()) . '">' . $item->getName() . '</a>',
					],
					[
						'content' => $author !== null ? $author->getName() : '',
					],
					[
						'content' => mwAdminComponents::icon(['icon' => 'eye', 'text' => number_format($item->getVisitsCount(), 0, '.', ' ')], 'mw_table_statistics'),
					],
					[
						'content' => mwAdminComponents::iconLink([
							'icon' => 'message-square',
							'text' => number_format($item->getCommentCount(), 0, '.', ' '),
							'target' => '_blank',
							'link' => mwSetting()->getObject('comments')->getUrl() . '&source=' . $item->getId(),
						], 'mw_table_statistics'),
					],
					[
						'content' => mwSetting::printSettingActions(['wp_edit', 'show_page', 'delete'], $item->getId(), $object),
						'align' => 'right',
					],
				],
			];
		}

		$content .= '<div class="mw_setting_list_container">';
		$content .= mwAdminComponents::table($listArgs, 'mw_table_list');
		$content .= '</div>';

		echo $content;
	}

	function modifyRenderedBlocks($block_content, $block)
	{
		if (
			 $block['blockName'] === 'core/embed' &&
			 isset($block['attrs']['url']) &&
			 $block['attrs']['url'] &&
			 !is_admin() &&
			 !wp_is_json_request()
		) {
			$infoBar = MwCookies()->printVideoInfo($block['attrs']['url']);
			if ($infoBar) {
				$block_content = str_replace('</figure>', $infoBar . '</figure>', $block_content);
				$block_content = str_replace('www.youtube.com/', 'www.youtube-nocookie.com/', $block_content);
			}
		}

		return $block_content;
	}

	public function disableAuthorPageForMemberRole()
	{
		if (!is_author()) {
			return;
		}
		$author = get_queried_object();
		if (!in_array('member', (array) $author->roles)) {
			return;
		}
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
		nocache_headers();
	}
	public function removeMembersUserFromSitemap($args)
	{
		$args['role__not_in'] = 'member';

		return $args;
	}

	/** @return MwBlog Returns singleton instance of blog module. */
	public static function instance()
	{
		$var = static::$_instance;
		if ($var === null) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}
}
