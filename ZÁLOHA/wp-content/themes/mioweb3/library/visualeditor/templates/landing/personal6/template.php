<?php
/**
 * Template Title: Osobní stránka pro fotografy
 * Template Description: Domovská stránka fotografického osobního webu.
 */
__('Osobní stránka pro fotografy', 'cms_ve');
__('Domovská stránka fotografického osobního webu.', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
