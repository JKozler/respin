<?php
/**
 * Template Title: Sale Letter 4
 * Template Description: Prodejní dopis s videem a textem. Obsah je rozdělen do samostatných bloků s kulatými rohy a stíny.
 */
__('Sale Letter 4', 'cms_ve');
__('Prodejní dopis s videem a textem. Obsah je rozdělen do samostatných bloků s kulatými rohy a stíny.', 'cms_ve');
if (have_posts()) {
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
}
