<?php
/**
 * Template Title: Děkovací stránka se stažením souboru
 * Template Description:
 */
__('Děkovací stránka se stažením souboru', 'cms_ve');
if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
