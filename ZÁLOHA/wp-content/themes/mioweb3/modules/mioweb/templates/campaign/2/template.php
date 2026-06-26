<?php
/**
 * Template Title: Jednoduchá video stránka
 * Template Description: Video stránka s menu nad videem.
 */
__('Jednoduchá video stránka', 'cms_mioweb');
__('Video stránka s menu nad videem.', 'cms_mioweb');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
