<?php
/**
 * Template Title: Jednoduchá video stránka
 * Template Description: Video stránka s menu nad videem.
 */
__('Jednoduchá video stránka', 'mw_funnels');
__('Video stránka s menu nad videem.', 'mw_funnels');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
