<?php
/**
 * Template Title: Děkovací stránka se stažením ebooku
 * Template Description:
 */
__('Děkovací stránka se stažením ebooku', 'cms_ve');
if (have_posts()) {
	while (have_posts()) {
		the_post();
		the_content();
	}
}
