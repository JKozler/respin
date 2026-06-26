<?php
/**
 * Template Title: Děkovací stránka s videem
 * Template Description:
 */
__('Děkovací stránka s videem', 'cms_ve');
if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
