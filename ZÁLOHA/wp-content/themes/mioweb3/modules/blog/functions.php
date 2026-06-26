<?php

function blog_post_nav()
{
	global $wp_query;
	if ($wp_query->max_num_pages < 2) {
		return;
	}
	?>
	<nav class="navigation paging-navigation blog-box" role="navigation">
		<div class="nav-links">

	<?php if (get_next_posts_link()) : ?>
				<div
					class="nav-previous"><?php next_posts_link('<span class="meta-nav">&larr;</span> ' . __('Starší příspěvky', 'cms_blog')); ?></div>
	<?php endif; ?>

	<?php if (get_previous_posts_link()) : ?>
				<div
					class="nav-next"><?php previous_posts_link(__('Novější příspěvky', 'cms_blog') . ' <span class="meta-nav">&rarr;</span>'); ?></div>
	<?php endif; ?>
			<div class="cms_clear"></div>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}

//related_posts function
/*
function print_related_posts($related_posts, $first=false, $exclude=array()) {
$i=1;
while( $related_posts->have_posts() ) {
$related_posts->the_post();
$thumb=(has_post_thumbnail())? true : false;
?>
<div class="related_post col col-three <?php if($first && $i==1) echo 'col-first'; ?>">
<a class="related_post_thumb" title="<?php the_title()?>" href="<?php the_permalink()?>"><?php if($thumb) the_post_thumbnail('blog_element'); else echo '<img src="'.BLOG_DIR.'images/blank_image.png" alt="" />'; ?></a>
<h3><a class="related_post_title" title="<?php the_title()?>" href="<?php the_permalink()?>"><?php the_title(); ?></a></h3>
</div>
<?php
$exclude[]=get_the_ID();
$i++;
}
return $exclude;
}
*/
function get_related_posts($desc = true)
{
	global $post;

	$tags = wp_get_post_tags($post->ID);
	$exclude = [$post->ID];
	$max = 3;
	$articles = [];

	// from same tag
	if ($tags) {
		$tag_ids = [];

		foreach ($tags as $individual_tag) {
			$tag_ids[] = $individual_tag->term_id;
		}

		$args = [
			'tag__in' => $tag_ids,
			'post__not_in' => $exclude,
			'posts_per_page' => $max, // Number of related posts to display.
			'post_type' => 'post',
		];

		$posts = new WP_Query($args);

		if ($posts->found_posts) {
			foreach ($posts->posts as $article) {
				$exclude[] = $article->ID;
				$articles[] = mwBlogPost::createNew($article);
			}
			$max -= $posts->found_posts;
		}
	}

	// from same category
	if ($max > 0 && $categories = get_the_category($post->ID)) {
		foreach ($categories as $category) {
			$cat_ids[] = $category->term_id;
		}

		$args = [
			'category__in' => $cat_ids,
			'post__not_in' => $exclude,
			'posts_per_page' => $max,
		];

		$posts = new WP_Query($args);

		if ($posts->found_posts) {
			foreach ($posts->posts as $article) {
				$exclude[] = $article->ID;
				$articles[] = mwBlogPost::createNew($article);
			}
			$max -= $posts->found_posts;
		}
	}

	// most readed
	if ($max > 0) {
		$args = [
			'post__not_in' => $exclude,
			'posts_per_page' => $max,
		];

		$posts = new WP_Query($args);

		if ($posts->found_posts) {
			foreach ($posts->posts as $article) {
				$articles[] = mwBlogPost::createNew($article);
			}
		}
	}

	if (count($articles)) {
		?>
		<div class="related_posts">
			<div
				class="related_posts_title title_element_container"><?php echo __('Podobné články', 'cms_blog'); ?></div>
			<div class="related_posts_container">
			<?php print_related_posts($articles, $desc); ?>
			</div>
		</div>
		<?php
	}
}

function print_related_posts($related_posts, $desc)
{
	$i = 1;

	foreach ($related_posts as $rpost) {
		$thumb = $rpost->getThumbnail();
		?>
		<div class="related_post col col-three <?php if ($i == 1) { echo 'col-first'; } ?>">
			<?php
			$class = '';
			if (isset(mwBlog()->appearance['blog_thumbnail']) && mwBlog()->appearance['blog_thumbnail'] && mwBlog()->appearance['blog_thumbnail'] != 'original') {
				$class = 'mw_image_ratio mw_image_ratio_' . mwBlog()->appearance['blog_thumbnail'];
			}
			?>
			<a class="related_post_thumb <?php echo $class; ?> <?php if ($thumb->isEmpty()) { echo 'related_post_nothumb'; } ?>"
			   title="<?php echo $rpost->getName(); ?>"
			   href="<?php echo $rpost->getUrl(); ?>"><?php if (!$thumb->isEmpty()) {
					echo $thumb->printImg([
						'size' => 'mio_columns_c3',
						'col_divisor' => 3,
					]);
					//get_the_post_thumbnail($post->ID, 'mio_columns_c2');
					 } ?></a>
			<a class="related_post_title <?php echo mwBlog()->article_title_class; ?>"
			   title="<?php echo $rpost->getName(); ?>"
			   href="<?php echo $rpost->getUrl(); ?>"><?php echo $rpost->getName(); ?></a>
		<?php
		if ($desc) {
			?>
				<p><?php echo $rpost->getExcerpt(12, true); ?></p>
			<?php
		}
		?>
		</div>
		<?php
		$i++;
	}
}

function get_blog_sidebar($name = null)
{
	$templates = [];
	if (isset($name)) {
		$templates[] = mwBlog()->get_locale_path() . "sidebar-{$name}.php";
	}
	$templates[] = mwBlog()->get_locale_path() . 'sidebar.php';

	locate_template($templates, true);
}

function get_blog_part($slug, $name = null)
{
	$templates = [];
	$name = (string) $name;
	if ($name !== '') {
		$templates[] = mwBlog()->get_locale_path() . "{$slug}-{$name}.php";
	}

	$templates[] = mwBlog()->get_locale_path() . "{$slug}.php";

	locate_template($templates, true, false);
}

/*
add_filter( 'home_template', 'get_skin_home_template');
function get_skin_home_template($template) {
if(isset($_GET['window_editor'])) {
$templates[] = 'window_editor.php';
return locate_skin_template($templates);
} else {
$templates = array( 'home.php', 'index.php' );
return locate_blog_template($templates);
}
}

add_filter( 'date_template', 'get_skin_date_template' );
function get_skin_date_template($template) {
$templates = array( 'date.php' );
return locate_blog_template($templates);
}
add_filter( 'search_template', 'get_skin_search_template' );
function get_skin_search_template($template) {
$templates = array( 'search.php' );
return locate_blog_template($templates);
}

add_filter( 'archive_template', 'get_skin_archive_template' );
function get_skin_archive_template($template) {
$post_types = array_filter( (array) get_query_var( 'post_type' ) );
$templates = array();
if ( count( $post_types ) == 1 ) {
$post_type = reset( $post_types );
$templates[] = "archive-{$post_type}.php";
}
$templates[] = 'archive.php';
return locate_blog_template($templates);
}
//add_filter( 'single_template', 'get_skin_single_template' );
function get_skin_single_template($template) {
$object = get_queried_object();
//$skintemplate = get_skin_template_slug();
$templates = array();
if ( $object )
$templates[] = "single-{$object->post_type}.php";
$templates[] = "single.php";
return locate_blog_template($templates);
}
add_filter( 'author_template', 'get_skin_author_template' );
function get_skin_author_template() {
$author = get_queried_object();
$templates = array();
if ( $author ) {
$templates[] = "author-{$author->user_nicename}.php";
$templates[] = "author-{$author->ID}.php";
}
$templates[] = 'author.php';
return locate_blog_template($templates);
}

add_filter( 'category_template', 'get_skin_category_template' );
function get_skin_category_template() {
$category = get_queried_object();
$templates = array();
if ( $category ) {
$templates[] = "category-{$category->slug}.php";
$templates[] = "category-{$category->term_id}.php";
}
$templates[] = 'category.php';
return locate_blog_template($templates);
}

add_filter( 'tag_template', 'get_skin_tag_template' );
function get_skin_tag_template() {
$tag = get_queried_object();
$templates = array();
if ( $tag ) {
$templates[] = "tag-{$tag->slug}.php";
$templates[] = "tag-{$tag->term_id}.php";
}
$templates[] = 'tag.php';
return locate_blog_template($templates);
}
add_filter( 'comments_template', 'get_skin_comments_file' );
function get_skin_comments_file() {
return get_blog_directory().'/comments.php';
}



function locate_blog_template($template_names, $load = false, $require_once = true )
{
if ( !is_array($template_names) )
return '';

$located = '';

$skin_dir = get_blog_directory();

foreach ( $template_names as $template_name ) {
if ( !$template_name )
continue;
/**
* Make possible to use different file from other modules.
* @param $located string Located template file. Can be preserved or modified.
* @param $template_name string Name of the searched template file.
* @return string Value of $located, if it should be preserved or custom value.
* @since 2016-02-19

$located = apply_filters_ref_array('mw_locate_template', array($located, $template_name));
if (file_exists($located)) {
break;
} else
if ( file_exists( $skin_dir . '/' .  $template_name) ) {
$located =  $skin_dir . '/' . $template_name;
break;
}
else if ( file_exists(STYLESHEETPATH . '/' . $template_name)) {
$located = STYLESHEETPATH . '/' . $template_name;
break;
} else if ( file_exists(TEMPLATEPATH . '/' . $template_name) ) {
$located = TEMPLATEPATH . '/' . $template_name;
break;
}
}

if ( $load && '' != $located )
load_template( $located, $require_once );

return $located;
}
*/

function field_type_blog_selectpage($field, $meta, $group_id)
{
	$on_front = get_option('show_on_front');
	foreach ($field['options'] as $key => $option) {
		echo '<div class="cms_radio_container"><input type="radio" id="', $group_id, '_', $field['id'], '_', $key, '" name="', $group_id, '[', $field['id'], '][show_on_front]" value="', $key, '"', $key == $on_front ? ' checked="checked"' : '', ' />';
		echo '<label for="', $group_id, '_', $field['id'], '_', $key, '"> ', $option, '</label></div>';
	}
	echo '<div class="cms_clear"></div>';

	$blog_page = get_option('show_on_front') == 'page' ? get_option('page_for_posts') : '';
	echo '<div class="cms_show_group_blogpage cms_show_group_blogpage_page ' . ($on_front !== 'page' ? 'cms_nodisp' : '') . '">';
	echo mwAdminComponents::selectPage([
		'name' => $group_id . '[' . $field['id'] . '][page_for_posts]',
		'tag_id' => $group_id . '_' . $field['id'],
		'empty' => ' - ' . __('Vyberte stránku blogu', 'cms_blog') . ' - ',
	], $blog_page);
	echo '</div>';
}

function field_type_blogselect($field, $meta, $group_id, $tagid)
{
	$content = $meta ?? ($field['content'] ?? '');

	$options = [];
	foreach (mwBlog()->templates as $key => $template) {
		$options[$key] = $template['thumb'];
	}
	cms_generate_field_imageselect($group_id . '[' . $field['id'] . ']', $group_id . '_' . $field['id'], $options, $content);
}

function field_type_category_select($field, $meta, $group_name, $group_id)
{
	$content = $meta ?? ($field['content'] ?? 0);

	$items = get_categories(['taxonomy' => 'category', 'hide_empty' => 0]);
	$options = [];
	$options[] = [
		'value' => '',
		'name' => __('- Všechny kategorie -', 'cms_blog'),
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
