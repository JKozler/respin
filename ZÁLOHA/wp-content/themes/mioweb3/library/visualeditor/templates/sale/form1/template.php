<?php
/**
 * Template Title: Stránka s prodejním formulářem 1
 * Template Description: Jednoduchá jednosloupcová prodejní stránka s prodejním FAPI formulářem.
 */
__('Stránka s prodejním formulářem 1', 'cms_ve');
__('Jednoduchá jednosloupcová prodejní stránka s prodejním FAPI formulářem.', 'cms_ve');
global $vePage;

if (have_posts()) {
	while (have_posts()) :
		the_post();
		the_content();
	endwhile;
}
