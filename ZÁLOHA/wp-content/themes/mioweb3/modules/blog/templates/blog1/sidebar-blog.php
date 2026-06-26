<?php
if (isset(mwBlog()->appearance['blog_sidebar'])) { ?>
	<div id="blog-sidebar">
	<?php
	$sidebars = get_option('blog_sidebars');
	if (is_single()) {
		$sidebar = $sidebars['sidebar_post'];
	} elseif (is_home()) {
		$sidebar = $sidebars['sidebar_blog'];
	} elseif (is_author()) {
		$sidebar = $sidebars['sidebar_author'];
	} elseif (is_category()) {
		$sidebar = $sidebars['sidebar_category'];
	} elseif (is_archive()) {
		$sidebar = $sidebars['sidebar_category'];
	} elseif (is_tag()) {
		$sidebar = $sidebars['sidebar_tag'];
	} elseif (is_search()) {
		$sidebar = $sidebars['sidebar_search'];
	}

	if (is_active_sidebar($sidebar)) : ?>
			<ul>
		<?php dynamic_sidebar($sidebar); ?>
			</ul>
	<?php endif; ?>
	</div>
<?php } ?>
