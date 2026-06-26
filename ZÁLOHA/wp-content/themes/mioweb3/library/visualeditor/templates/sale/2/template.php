<?php
/**
 * Template Title: Sale Letter 2
 * Template Description: Prodejní dopis s videem a textem přes celou šířku obrazovky.
 */
__('Sale Letter 2', 'cms_ve');
__('Prodejní dopis s videem a textem přes celou šířku obrazovky.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
}
