<?php
get_header();
global $authordata;
$author = mwUser::createNew($authordata);
?>
<div id="blog_top_panel">
	<div id="blog_top_panel_container" class="mw_transparent_header_padding">
		<h1 class="<?php echo mwBlog()->blog_title_class; ?>"><?php echo __('Autor', 'cms_blog') . ' ' . $author->getDisplayName(); ?></h1>
	</div>
</div>
<div id="blog-container">
	<div id="blog-content">
		<div class="blog-box blog-author-box">
			<div class="author_photo"><?php echo $author->getAvatar(100); ?></div>
			<div class="author_box_content">
				<h2><?php echo __('O autorovi', 'cms_blog'); ?></h2>
				<div class="author_box_description"><?php echo $author->getDescription(); ?></div>
				<?php
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
					echo '<div class="author_box_links">' . $content . '</div>';
				}
				?>
			</div>
			<div class="cms_clear"></div>
		</div>
		<?php get_blog_part('content', 'loop'); ?>
		<div class="cms_clear"></div>

	</div>

	<?php get_blog_sidebar('blog'); ?>

	<div class="cms_clear"></div>
</div>


<?php get_footer(); ?>
