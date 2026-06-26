<?php
/**
 * Template Title: Jednoduchá děkovací stránka
 * Template Description:
 */
__('Jednoduchá děkovací stránka', 'cms_ve');
if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
