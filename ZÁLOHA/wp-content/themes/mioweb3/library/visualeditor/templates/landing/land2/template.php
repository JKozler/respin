<?php
/**
 * Template Title: Univerzální domovská stránka 2
 * Template Description: Domovská stránka s jednoduchým univerzálním vzhledem
 */
__('Univerzální domovská stránka 2', 'cms_ve');
__('Domovská stránka s jednoduchým univerzálním vzhledem', 'cms_ve');

if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
