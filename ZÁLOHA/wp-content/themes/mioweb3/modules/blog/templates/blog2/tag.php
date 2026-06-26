<?php
get_header();
$description = tag_description();
?>
<div id="blog_top_panel" class="<?php if (!mwBlog()->top_panel['image']->isEmpty()) {
	echo 'blog_top_panel_wbg';
								} ?>">
	<div id="blog_top_panel_container" class="mw_transparent_header_padding">
		<h1 class="<?php echo mwBlog()->blog_title_class; ?>"><?php echo single_cat_title('', false); ?></h1>
		<?php
		if ($description) {
			echo '<div class="blog_top_panel_text">' . $description . '</div>';
		} else {
			echo '<div class="blog_top_panel_subtext">' . __('Články pro štítek', 'cms_blog') . ' ' . single_cat_title('', false) . '</div>';
		}
		?>
	</div>
</div>
<div id="blog-container">
	<div id="blog-content">
		<?php get_blog_part('content', 'loop'); ?>
		<div class="cms_clear"></div>

	</div>

	<?php get_blog_sidebar('blog'); ?>

	<div class="cms_clear"></div>
</div>


<?php get_footer(); ?>
