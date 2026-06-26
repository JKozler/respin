<?php
get_header();
get_blog_sidebar('home'); ?>
<div id="blog-container">
	<div id="blog-content">

		<?php get_blog_part('content', 'loop'); ?>
		<div class="cms_clear"></div>

	</div>

	<?php get_blog_sidebar('blog'); ?>

	<div class="cms_clear"></div>
</div>


<?php get_footer(); ?>
