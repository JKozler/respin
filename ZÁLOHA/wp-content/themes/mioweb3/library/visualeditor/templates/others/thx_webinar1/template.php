<?php
/**
 * Template Title: Děkovací stránka po registraci na webinář
 * Template Description:
 */
__('Děkovací stránka po registraci na webinář', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
