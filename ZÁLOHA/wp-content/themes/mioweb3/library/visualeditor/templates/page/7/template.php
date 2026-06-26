<?php
/**
 * Template Title: Stránka kontakty
 * Template Description: Stránka s kontaktním formulářem a základními kontakty.
 */
__('Stránka kontakty', 'cms_ve');
__('Stránka s kontaktním formulářem a základními kontakty.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
