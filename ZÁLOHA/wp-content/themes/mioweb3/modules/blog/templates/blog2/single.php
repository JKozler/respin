<?php
global $posts, $vePage, $post;

$blog_setting = get_option('blog_comments');
$comments = get_comments_number();

// post_detail_look
$detail_look = mwBlog()->appearance['post_detail_look'] ?? 1;

$blogPost = mwBlogPost::createNew($post);
$image = $blogPost->getThumbnail();

if ($detail_look == 2) {
	$vePage->display->css->addGlobalStyle('.single_blog_title_container', [
		'background-image' => 'url(' . $image->getUrl('full') . ')',
	]);
	$vePage->display->css->addGlobalStyle('.single_blog_title_container', [
			'background-image' => 'url(' . $image->getUrl('large') . ')',
	], 'tablet');
}

get_header();

// article meta
$article_meta = '';
if ($detail_look == 2) {
	if (!isset($blog_setting['hide']['date'])) {
		$article_meta = '<span class="date">' . mwBlog()->get_blog_icon('date') . mwBlog()->get_post_date() . '</span>';
	}
	if (!isset($blog_setting['hide']['autorbox'])) {
		$article_meta .= '<a class="user" href="' . get_author_posts_url($post->post_author) . '">' . mwBlog()->get_blog_icon('user') . get_the_author_meta('display_name', $post->post_author) . '</a>';
	}
	if (isset($blog_setting['show']['visitors'])) {
		$article_meta .= '<span class="visitors">' . mwBlog()->get_blog_icon('visitors') . mwBlog()->get_visit_number($post->ID) . 'x</span>';
	}
	if (isset($blog_setting['comments']['wordpress'])) {
		$article_meta .= '<a class="comments" href="' . get_comments_link() . '">' . mwBlog()->get_blog_icon('comments') . $comments . '</a>';
	}
} else {
	if (!isset($blog_setting['hide']['autorbox'])) {
		$article_meta = '<a class="user" href="' . get_author_posts_url($post->post_author) . '">' . get_avatar($post->post_author, 24) . get_the_author_meta('display_name', $post->post_author) . '</a>';
	}
	if (!isset($blog_setting['hide']['date'])) {
		$article_meta .= '<span class="date">' . mwBlog()->get_blog_icon('date') . mwBlog()->get_post_date() . '</span>';
	}
	if (isset($blog_setting['comments']['wordpress'])) {
		$article_meta .= '<a class="comments" href="' . get_comments_link() . '">' . mwBlog()->get_blog_icon('comments') . $comments . '</a>';
	}
	if (isset($blog_setting['show']['visitors'])) {
		$article_meta .= '<span class="visitors">' . mwBlog()->get_blog_icon('visitors') . mwBlog()->get_visit_number($post->ID) . 'x</span>';
	}
}
// blog header 1
if ($detail_look == 1) {
	?>
	<div id="blog_top_panel" class="single_blog_top_panel single_blog_top_panel_<?php echo $detail_look; ?>">
		<div id="blog_top_panel_container" class="mw_transparent_header_padding">
			<h1 class="<?php echo mwBlog()->blog_title_class; ?>"><?php echo get_the_title(); ?></h1>
		</div>
	</div>
	<?php
}

// blog header 2
if ($detail_look == 2) {
	?>
	<div class="single_blog_title_container">
		<div class="single_blog_title_overlay"></div>
		<div class="mw_transparent_header_padding">
			<div class="single_blog_title_container_inner row_fix_width">
				<h1 class="<?php echo mwBlog()->blog_title_class; ?>"><?php echo get_the_title(); ?></h1>
				<?php echo '<div class="single_title_meta">' . $article_meta . '</div>'; ?>
			</div>
		</div>

	</div>
	<?php
}
?>

<div id="blog-container">
	<?php if ($detail_look == 3 || $detail_look == 4 || $detail_look == 5) {
		echo '<div class="mw_transparent_header_padding">';
	} ?>
	<div id="blog-content">
		<?php
		if ($detail_look == 3 || $detail_look == 4 || $detail_look == 5) {
			echo '<div class="single_blog_title_incontent">';
			if ($detail_look != 4) {
				echo '<h1 class="' . mwBlog()->blog_title_class . '">' . get_the_title() . '</h1>';
			}
			if (has_post_thumbnail($post->ID) && $detail_look != 5) {
				$img = $image->printImg([
					'size' => 'mio_columns_c1',
				]);

				echo '<div class="responsive_image single_block_article_image">';
				if (isset(mwBlog()->appearance['blog_thumbnail']) && mwBlog()->appearance['blog_thumbnail'] && mwBlog()->appearance['blog_thumbnail'] != 'original') {
					echo '<div class="mw_image_ratio mw_image_ratio_' . mwBlog()->appearance['blog_thumbnail'] . '">' . $img . '</div>';
				} else {
					echo $img;
				}

				if ($detail_look == 4) {
					echo '<h1 class="' . mwBlog()->blog_title_class . '">' . get_the_title() . '</h1>';
				}
				echo '</div>';
			} elseif ($detail_look == 4) {
				echo '<h1 class="' . mwBlog()->blog_title_class . '">' . get_the_title() . '</h1>';
			}
			echo '<div class="article_meta">' . $article_meta . '<div class="cms_clear"></div></div>';
			echo '</div>';
		}
		?>
		<div class="blog-box blog-singlebox article-detail">
			<?php
			while (have_posts()) :
				the_post();

				// article meta inside page
				if ($detail_look == 1) {
					echo '<div class="article_meta">' . $article_meta . '<div class="cms_clear"></div></div>';
				}


				unset($blog_setting['show_share']['is_saved']);
				if (isset($blog_setting['show_share']) && !empty($blog_setting['show_share']) && MwCookies()->isPermitted('marketing')) {
					?>
					<div class="in_share_element in_share_element_1 blog_share_buttons blog_share_buttons_top">
					<?php if (isset($blog_setting['show_share']['facebook'])) {
						$share = isset($blog_setting['show_share']['facebook_share']) ? 'true' : 'false';
						?>
							<div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="button_count"
								 data-action="like" data-show-faces="false" data-share="<?php echo $share; ?>"></div>

						<?php
					} elseif (isset($blog_setting['show_share']['facebook_share'])) {
						?>
							<div class="fb-share-button" data-href="<?php the_permalink(); ?>"
								 data-layout="button_count"></div>
						<?php
					}
					if (isset($blog_setting['show_share']['twitter'])) { ?>
							<div class="twitter-like"><a href="https://twitter.com/share" class="twitter-share-button"
														 data-url="<?php the_permalink(); ?>'" data-count="horizontal"
														 data-lang="cs">Tweet</a>
								<script>!function (d, s, id) {
										var js, fjs = d.getElementsByTagName(s)[0],
											p = /^http:/.test(d.location) ? 'http' : 'https';
										if (!d.getElementById(id)) {
											js = d.createElement(s);
											js.id = id;
											js.src = p + '://platform.twitter.com/widgets.js';
											fjs.parentNode.insertBefore(js, fjs);
										}
									}(document, 'script', 'twitter-wjs');</script>
							</div>
						<?php
					}
					if (isset($blog_setting['show_share']['linkedin'])) { ?>
							<script src="https://platform.linkedin.com/in.js"
									type="text/javascript">lang: <?php echo get_locale(); ?></script>
							<script type="IN/Share" data-url="<?php the_permalink(); ?>"></script>
					<?php } ?>
					</div>
					<?php
				}
				?>
				<div
					class="entry_content blog_entry_content element_text_li<?php echo mwBlog()->appearance['li']; ?>">
				<?php
				the_content();
				do_action('cms_singleloop');
				?>
				</div>

				<?php
				echo get_the_tag_list('<div class="single_tags">' . __('Tagy:', 'cms_blog') . ' ', '', '</div>');

				if (isset($blog_setting['show_share']) && !empty($blog_setting['show_share']) && MwCookies()->isPermitted('marketing')) {
					?>
					<div class="in_share_element in_share_element_1 blog_share_buttons">
					<?php if (isset($blog_setting['show_share']['facebook'])) { ?>
							<div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="button_count"
								 data-action="like" data-show-faces="false" data-share="<?php echo $share; ?>"></div>

						<?php
					} elseif (isset($blog_setting['show_share']['facebook_share'])) {
						?>
							<div class="fb-share-button" data-href="<?php the_permalink(); ?>"
								 data-layout="button_count"></div>
						<?php
					}
					if (isset($blog_setting['show_share']['twitter'])) { ?>
							<div class="twitter-like"><a href="https://twitter.com/share" class="twitter-share-button"
														 data-url="<?php the_permalink(); ?>'" data-count="horizontal"
														 data-lang="cs">Tweet</a>
								<script>!function (d, s, id) {
										var js, fjs = d.getElementsByTagName(s)[0],
											p = /^http:/.test(d.location) ? 'http' : 'https';
										if (!d.getElementById(id)) {
											js = d.createElement(s);
											js.id = id;
											js.src = p + '://platform.twitter.com/widgets.js';
											fjs.parentNode.insertBefore(js, fjs);
										}
									}(document, 'script', 'twitter-wjs');</script>
							</div>
						<?php
					}
					if (isset($blog_setting['show_share']['linkedin'])) { ?>
							<script src="https://platform.linkedin.com/in.js"
									type="text/javascript">lang: <?php get_locale(); ?></script>
							<script type="IN/Share" data-url="<?php the_permalink(); ?>"></script>
					<?php } ?>
					</div>
					<?php
				}
			endwhile;

			if (!isset($blog_setting['hide']['autorbox'])) {
				global $authordata;
				if ($authordata) {
					$author = mwUser::createNew($authordata);

					?>
					<div class="author-box">
						<div class="author_head">
							<div class="author_photo"><?php echo $author->getAvatar(); ?></div>
							<div class="author_head_content">
								<a class="author_name" href="<?php echo $author->getUrl(); ?>"><?php echo $author->getDisplayName(); ?></a>
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
						</div>
						<?php
						$desc = $author->getDescription();
						if ($desc) {
							echo '<div class="author_box_description">' . $desc . '</div>';
						}
						?>
					</div>
					<?php
				}
			}

			if (isset($blog_setting['content_after_post']) && $blog_setting['content_after_post']) {
				$args = [
					'key' => 'content_after_post',
					'option' => 'blog_comments',
				];
				echo $vePage->display->weditor->weditor_content($blog_setting['content_after_post'], $args);
			}

			if (!isset($blog_setting['hide']['related_posts'])) {
				$desc = isset($blog_setting['hide']['related_posts_text']) ? false : true;
				get_related_posts($desc);
			}

			?>

			<div id="blog_comments_container"><?php mwBlog()->print_blog_comments(3); ?></div>
			<div class="cms_clear"></div>
		</div>
	</div>

	<?php get_blog_sidebar('blog'); ?>

	<div class="cms_clear"></div>

	<?php if ($detail_look == 3 || $detail_look == 4 || $detail_look == 5) {
		echo '</div>';
	} ?>
</div>
<?php
get_footer();
?>
