<?php
/**
 * Template Title: Prodej knihy
 * Template Description: Stránka zaměřenená na prodej Vaší knihy.
 */
__('Prodej knihy', 'cms_ve');
__('Stránka zaměřenená na prodej Vaší knihy.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
