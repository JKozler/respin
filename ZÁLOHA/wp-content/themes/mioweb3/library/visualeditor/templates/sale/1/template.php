<?php
/**
 * Template Title: Sale Letter 1
 * Template Description: Prodejní dopis s videem a textem. Obsah je ohraničen pozadím.
 */
__('Sale Letter 1', 'cms_ve');
__('Prodejní dopis s videem a textem. Obsah je ohraničen pozadím.', 'cms_ve');
if (have_posts()) {
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
}
