<?php
/**
 * Template Title: Univerzální osobní stránka
 * Template Description: Univerzální domovská stránka osobního webu.
 */
__('Univerzální osobní stránka', 'cms_ve');
__('Univerzální domovská stránka osobního webu.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
