<?php
/**
 * Template Title: Sale Letter 3
 * Template Description: Prodejní dopis s videem a textem. Obsah je rozdělen do samostatných bloků.
 */
__('Sale Letter 3', 'cms_ve');
__('Prodejní dopis s videem a textem. Obsah je rozdělen do samostatných bloků.', 'cms_ve');
if (have_posts()) {
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
}
