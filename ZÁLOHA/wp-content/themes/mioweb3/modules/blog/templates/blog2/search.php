<?php
get_header(); ?>
<div id="blog_top_panel">
	<div id="blog_top_panel_container" class="mw_transparent_header_padding">
		<h1 class="<?php echo mwBlog()->blog_title_class; ?>"><?php echo __('Výsledek vyhledávání', 'cms_blog'); ?></h1>
		<?php echo '<div class="blog_top_panel_subtext">' . __('Vyhledávání slova', 'cms_blog') . ' "' . esc_html($_GET['s']) . '"</div>'; ?>
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
