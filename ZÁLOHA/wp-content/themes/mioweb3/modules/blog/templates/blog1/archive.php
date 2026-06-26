<?php
get_header(); ?>
<div id="blog_top_panel">
	<div id="blog_top_panel_container" class="mw_transparent_header_padding"><h1
			class="<?php echo mwBlog()->blog_title_class; ?> archive-title"><?php
			if (is_day()) :
				printf(__('Archiv dne: %s', 'cms_blog'), get_the_date());
			elseif (is_month()) :
				printf(__('Archiv měsíce: %s', 'cms_blog'), get_the_date(_x('F Y', '', 'cms_blog')));
			elseif (is_year()) :
				printf(__('Archiv roku: %s', 'cms_blog'), get_the_date(_x('Y', '', 'cms_blog')));
			else :
				__('Archiv', 'cms_blog');
			endif;
			?></h1></div>
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
