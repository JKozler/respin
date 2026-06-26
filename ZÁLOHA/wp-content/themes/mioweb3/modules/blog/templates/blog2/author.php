<?php
get_header();
global $authordata;
$author = mwUser::createNew($authordata);

$desc = $author->getDescription();

?>
<div id="blog_top_panel">
	<div id="blog_top_panel_container" class="mw_transparent_header_padding">
		<div class="blog_top_author_title">
			<?php echo $author->getAvatar(100); ?>
			<small><?php echo __('Články autora', 'cms_blog'); ?></small>
			<h1 class="<?php echo mwBlog()->blog_title_class; ?>"><?php echo $author->getDisplayName(); ?></h1>
		</div>
		<?php
		if ($desc) {
			echo '<div class="blog_top_author_desc">' . $desc . '</div>';
		}

		$contactMethods = mwUser::getContactMethods();

		$content = '';

		if ($author->getWebsite()) {
			$content .= '<a class="author_web" target="_blank" href="' . $author->getWebsite() . '" title="' . __('Webová stránka', 'cms_blog') . '">' . mw_content_icon_set('globe') . '</a>';
		}

		foreach ($contactMethods as $mKey => $method) {
			if ($author->getContactInfo($mKey)) {
				$content .= '<a class="author_' . $mKey . '" target="_blank" href="' . $author->getContactInfo($mKey) . '" title="' . $method . '">' . mw_content_icon_file($mKey, BLOG_DIR . 'templates/blog1/images/social-icons.svg') . '</a>';
			}
		}

		if ($content) {
			echo '<div class="blog_top_author_links">' . $content . '</div>';
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
