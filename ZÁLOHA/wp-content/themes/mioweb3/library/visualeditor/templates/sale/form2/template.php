<?php
/**
 * Template Title: Stránka s prodejním formulářem 2
 * Template Description: Dvousloupcová prodejní stránka s doplňujícími informacemi na pravé straně.
 */
__('Stránka s prodejním formulářem 2', 'cms_ve');
__('Dvousloupcová prodejní stránka s doplňujícími informacemi na pravé straně.', 'cms_ve');
global $vePage;

if (have_posts()) {
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
}
